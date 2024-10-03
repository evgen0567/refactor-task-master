<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoyaltyAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loyalty_account', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->string('card')->unique();
            $table->string('email')->unique();
            $table->boolean('email_notification')->default(true);
            $table->boolean('phone_notification')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loyalty_account');
    }
}
