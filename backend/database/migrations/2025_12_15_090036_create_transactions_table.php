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
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('type')->comment('交易類型: deposit, withdrawal, bet, win, refund');
                $table->decimal('amount', 15, 2)->comment('交易金額');
                $table->decimal('balance_before', 15, 2)->default(0)->comment('交易前餘額');
                $table->decimal('balance_after', 15, 2)->default(0)->comment('交易後餘額');
                $table->string('status')->default('pending')->comment('狀態: pending, completed, failed, cancelled');
                $table->string('description')->nullable()->comment('交易描述');
                $table->string('reference_id')->nullable()->comment('外部參考單號');
                $table->timestamps();
                
                // 索引以優化查詢
                $table->index(['user_id', 'type']);
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
