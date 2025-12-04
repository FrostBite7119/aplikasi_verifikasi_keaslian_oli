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
        Schema::create('limits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['scan']);
            $table->enum('time_period', ['daily','weekly','monthly','annually']);
            $table->time('reset_time')->nullable();
            $table->unsignedInteger('max_count');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('limit_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(table: 'users', indexName: 'limit_counts_user_id');
            $table->foreignId('limit_id')->constrained(table: 'limits', indexName: 'limit_counts_limit_id');
            $table->integer('count')->default(0);
            $table->date('date');
            $table->timestamps();
            $table->unique(['user_id', 'limit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limit_counts');
        Schema::dropIfExists('limits');
    }
};
