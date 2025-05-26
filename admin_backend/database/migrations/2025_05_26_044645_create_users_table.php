<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('two_factor_secret')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};