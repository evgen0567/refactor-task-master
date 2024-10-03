<?php

use App\Models\LoyaltyPointsRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoyaltyPointsRuleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loyalty_points_rule', function (Blueprint $table) {
            $table->id();
            $table->string('points_rule');
            $table->string('accrual_type');
            $table->float('accrual_value');
            $table->timestamps();
        });

        $rules = [
            [
                'points_rule' => 'Wine +5',
                'accrual_type' => LoyaltyPointsRule::ACCRUAL_TYPE_ABSOLUTE_POINTS_AMOUNT,
                'accrual_value' => 5,
            ],
            [
                'points_rule' => 'Coffee +1%',
                'accrual_type' => LoyaltyPointsRule::ACCRUAL_TYPE_RELATIVE_RATE,
                'accrual_value' => 1,
            ],
            [
                'points_rule' => 'Dog Food',
                'accrual_type' => LoyaltyPointsRule::ACCRUAL_TYPE_ABSOLUTE_POINTS_AMOUNT,
                'accrual_value' => 10,
            ],
            [
                'points_rule' => 'Vegetables +20%',
                'accrual_type' => LoyaltyPointsRule::ACCRUAL_TYPE_RELATIVE_RATE,
                'accrual_value' => 20,
            ],
        ];

        foreach ($rules as $rule) {
            $loyaltyPointsRule = new LoyaltyPointsRule();
            $loyaltyPointsRule->points_rule = $rule['points_rule'];
            $loyaltyPointsRule->accrual_type = $rule['accrual_type'];
            $loyaltyPointsRule->accrual_value = $rule['accrual_value'];
            $loyaltyPointsRule->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loyalty_points_rule');
    }
}
