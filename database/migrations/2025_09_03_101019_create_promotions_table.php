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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('promotion_id')->unique();
            $table->string('name');
            $table->enum('mode', ['fixed', 'percent']);
            $table->unsignedInteger('value');
            $table->boolean('cascade_to_upline')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_exclusive')->default(false);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained(table: 'products', indexName: 'product_promotions_product_id')->onDelete('cascade');
            $table->foreignId('promotion_id')->constrained(table: 'promotions', indexName: 'product_promotions_promotion_id')->onDelete('cascade');
            $table->unique(['promotion_id','product_id']);
        });

        Schema::create('region_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained(table: 'regions', indexName: 'region_promotions_region_id')->onDelete('cascade');
            $table->foreignId('promotion_id')->constrained(table: 'promotions', indexName: 'region_promotions_promotion_id')->onDelete('cascade');
            $table->unique(['promotion_id','region_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('product_promotions');
        Schema::dropIfExists('region_promotions');
    }
};
