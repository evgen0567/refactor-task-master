<?php

namespace App\Http\Controllers;

use App\Mail\LoyaltyPointsReceived;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointsTransaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class LoyaltyPointsController extends Controller
{
    public function deposit(Request $request): JsonResponse|LoyaltyPointsTransaction
    {
        Log::info('Deposit transaction input: ' . print_r($request->all(), true));

        try {
            $account = $this->getLoyaltyAccount($request);
        } catch (ModelNotFoundException $e) {
            Log::info('Account is not found');
            return response()->json(['message' => 'Account is not found'], 400);
        }

        if (!$account->active) {
            Log::info('Account is not active');
            return response()->json(['message' => 'Account is not active'], 400);
        }

        $loyaltyPointsRule = ($request->filled("loyalty_points_rule")) ? $request->loyalty_points_rule : throw new \InvalidArgumentException('Wrong parameters');
        $description = ($request->filled("description")) ? $request->description : throw new \InvalidArgumentException('Wrong parameters');
        $paymentId = ($request->filled("payment_id")) ? $request->payment_id : throw new \InvalidArgumentException('Wrong parameters');
        $paymentAmount = ($request->filled("payment_amount")) ? $request->payment_amount : throw new \InvalidArgumentException('Wrong parameters');
        $paymentTime = ($request->filled("payment_time")) ? $request->payment_time : throw new \InvalidArgumentException('Wrong parameters');

        $transaction =  LoyaltyPointsTransaction::performPaymentLoyaltyPoints($account->id, $loyaltyPointsRule, $description, $paymentId, $paymentAmount, $paymentTime);
        Log::info($transaction);
        $this->sendNotifications($account, $transaction);
        return $transaction;
    }

    private function getLoyaltyAccount(Request $request): LoyaltyAccount
    {
        $type = ($request->filled("account_type") && preg_match('/phone|card|email/ui', $request->account_type)) ?
            $request->account_type : throw new \InvalidArgumentException('Wrong account parameters');
        $id = ($request->filled("account_type")) ? $request->account_id : throw new \InvalidArgumentException('Wrong account parameters');
        return LoyaltyAccount::where($type, '=', $id)->firstOrFail();
    }

    private function sendNotifications(LoyaltyAccount $account, LoyaltyPointsTransaction $transaction): void
    {
        if ($account->email !== '' && $account->email_notification) {
            Mail::to($account)->send(new LoyaltyPointsReceived($transaction->points_amount, $account->getBalance()));
        }
        if ($account->phone !== '' && $account->phone_notification) {
            // instead SMS component
            Log::info('You received' . $transaction->points_amount . 'Your balance' . $account->getBalance());
        }
    }

    public function cancel(Request $request): ?JsonResponse
    {
        $reason = ($request->has("cancellation_reason")) ? $request->cancellation_reason : '';
        $transactionId = ($request->filled("transaction_id")) ? $request->transaction_id : throw new \InvalidArgumentException('Wrong parameters');

        if ($reason === '') {
            return response()->json(['message' => 'Cancellation reason is not specified'], 400);
        }

        if ($transaction = LoyaltyPointsTransaction::where('id', '=', $transactionId)->where('canceled', '=', 0)->first()) {
            $transaction->canceled = time();
            $transaction->cancellation_reason = $reason;
            $transaction->save();
        } else {
            return response()->json(['message' => 'Transaction is not found'], 400);
        }
    }

    public function withdraw(Request $request)
    {
        Log::info('Withdraw loyalty points transaction input: ' . print_r($request->all(), true));

        try {
            $account = $this->getLoyaltyAccount($request);
        } catch (ModelNotFoundException $e) {
            Log::info('Account is not found');
            return response()->json(['message' => 'Account is not found'], 400);
        }

        if (!$account->active) {
            Log::info('Account is not active');
            return response()->json(['message' => 'Account is not active'], 400);
        }

        $pointsAmount = ($request->filled("points_amount")) ? $request->points_amount : throw new \InvalidArgumentException('Wrong parameters');
        $description = ($request->filled("description")) ? $request->description : throw new \InvalidArgumentException('Wrong parameters');

        if ($pointsAmount <= 0) {
            Log::info('Wrong loyalty points amount: ' . $pointsAmount);
            return response()->json(['message' => 'Wrong loyalty points amount'], 400);
        }
        if ($account->getBalance() < $pointsAmount) {
            Log::info('Insufficient funds: ' . $pointsAmount);
            return response()->json(['message' => 'Insufficient funds'], 400);
        }

        $transaction = LoyaltyPointsTransaction::withdrawLoyaltyPoints($account->id, $pointsAmount, $description);
        Log::info($transaction);
        return $transaction;
    }
}
