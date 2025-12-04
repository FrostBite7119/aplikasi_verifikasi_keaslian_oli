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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_first_referral')->default(false);
            $table->foreignId('upline_id')->constrained(table: 'stores', indexName: 'referrals_upline_id');
            $table->foreignId('downline_id')->constrained(table: 'stores', indexName: 'referrals_donwline_id');
            $table->integer('depth');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
