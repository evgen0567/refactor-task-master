<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->all();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        return response()->json(['user' => $user, 'token' => $user->createToken('apiToken')->plainTextToken], 201);
    }

    public function login(Request $request)
    {
        $data = $request->all();
        $user = User::where('email', $data['email'])->first();

        if ($user && Hash::check($data['password'], $user->password)) {
            $token = $user->createToken('apiToken')->plainTextToken;
        } else {
            return response()->json(['message' => 'Bad credentials'], 401);
        }

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ['message' => 'Logged out'];
    }
}
