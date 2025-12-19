<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('game_type')->default('slot');
            $table->decimal('bet_amount', 16, 4);
            $table->decimal('payout_amount', 16, 4)->default(0);
            $table->json('result_data')->nullable(); 
            $table->string('status')->default('completed');
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // bet, win, deposit, withdraw
            $table->decimal('amount', 16, 4);
            $table->decimal('balance_before', 16, 4);
            $table->decimal('balance_after', 16, 4);
            $table->unsignedBigInteger('reference_id')->nullable(); // game_round_id
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('game_rounds');
    }
};
