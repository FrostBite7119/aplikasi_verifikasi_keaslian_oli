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
        Schema::create('base_rewards', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->unique();
            $table->unsignedInteger('store_points');
            $table->unsignedInteger('mechanic_points');
            $table->unsignedInteger('percentage_for_store');
            $table->timestamps();
        });

        Schema::create('upline_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->unique();
            $table->decimal('rate', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('base_rewards');
        Schema::dropIfExists('upline_rates');
    }
};
