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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('store_id')->unique();
            $table->string('name');
            $table->string('address');
            $table->integer('level');
            $table->boolean('has_mechanic_role');
            $table->foreignId('owner_id')->constrained(table: 'users', indexName: 'stores_owner_id');
            $table->foreignId('region_id')->nullable()->constrained(table: 'regions', indexName: 'stores_region_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
