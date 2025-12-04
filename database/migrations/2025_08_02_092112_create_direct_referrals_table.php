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
        Schema::create('direct_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('upline_id')->nullable()->constrained(table: 'stores', indexName: 'direct_referrals_upline_id')->onDelete('cascade');
            $table->foreignId('downline_id')->nullable()->constrained(table: 'stores', indexName: 'direct_referrals_downline_id')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['upline_id', 'downline_id'], 'unique_upline_downline_pair');
        });

        Schema::create('direct_referral_reward_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('direct_referral_id')->constrained(table: 'direct_referrals', indexName: 'direct_referral_reward_periods_direct_referral_id')->onDelete('cascade');
            $table->unsignedInteger('month');
            $table->timestamps();
            $table->unique(['direct_referral_id', 'month'], 'unique_direct_referral_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_referrals');
        Schema::dropIfExists('direct_referral_reward_periods');
    }
};
