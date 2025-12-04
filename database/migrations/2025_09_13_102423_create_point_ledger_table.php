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
        Schema::create('point_ledger', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('user_id')->constrained(table: 'users', indexName: 'point_ledger_user_id');
            $table->enum('event_type', ['store_scan', 'store_mechanic_scan', 'mechanic_scan', 'mechanic_reward', 'promo_reward', 'referral_reward', 'referral_promo_reward', 'redeem', 'redemption_reversal']);
            $table->enum('account_type', ['store', 'mechanic']);
            $table->integer('points');
            $table->string('description');
            $table->foreignId('store_id')->nullable()->constrained(table: 'stores', indexName: 'point_ledger_store_id');
            $table->foreignId('source_store_id')->nullable()->constrained(table: 'stores', indexName: 'point_ledger_source_store_id');
            $table->integer('level')->nullable();
            $table->foreignId('mechanic_id')->nullable()->constrained(table: 'mechanics', indexName: 'point_ledger_mechanic_id');
            $table->foreignId('product_id')->nullable()->constrained(table: 'products', indexName: 'point_ledger_product_id');
            $table->foreignId('promotion_id')->nullable()->constrained(table: 'promotions', indexName: 'point_ledger_promotion_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_ledger');
    }
};
