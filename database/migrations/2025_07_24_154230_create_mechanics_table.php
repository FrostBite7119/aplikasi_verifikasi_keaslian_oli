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
        Schema::create('mechanics', function (Blueprint $table) {
            $table->id();
            $table->string('mechanic_id')->unique();
            $table->integer('level')->nullable();
            $table->foreignId('user_id')->constrained(table: 'users', indexName: 'mechanics_user_id');
            $table->foreignId('store_id')->nullable()->constrained(table: 'stores', indexName: 'mechanics_store_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('mechanic_store_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mechanic_id')->constrained(table: 'mechanics', indexName: 'mechanic_store_histories_mechanic_id');
            $table->foreignId('store_id')->constrained(table: 'stores', indexName: 'mechanic_store_histories_store_id');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mechanic_store_histories');
        Schema::dropIfExists('mechanics');
    }
};
