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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->string('name');
            $table->string('phone_number')->unique();
            $table->string('bank');
            $table->string('bank_account_number')->unique();
            $table->enum('account_type', ['store', 'mechanic']);
            $table->enum('validation_status', ['approved', 'rejected', 'pending'])->default('pending');
            $table->timestamp('validation_date')->nullable();
            $table->string('password');
            $table->string('profile_picture')->nullable();
            $table->integer('points')->default(0);
            $table->integer('pending_points')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('phone_number')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('password_reset_logs', function (Blueprint $table) {
            $table->id();
            $table->string('token');
            $table->boolean('is_used')->default(false);
            $table->timestamp('expired_at')->nullable();
            $table->foreignId('user_id')->constrained(table: 'users', indexName: 'password_reset_logs');
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('password_reset_logs');
        Schema::dropIfExists('sessions');
    }
};
