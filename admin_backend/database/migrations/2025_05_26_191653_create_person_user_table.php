<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_user', function (Blueprint $table) {
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->primary(['person_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_user');
    }
};