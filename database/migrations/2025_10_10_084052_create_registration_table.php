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
        Schema::create('registration', function (Blueprint $table) {
            $table->id();
            $table->string('registration_code')->unique();
            $table->enum('registration_type', ['store', 'mechanic']);
            $table->timestamp('expired_date');
            $table->boolean('is_used')->default(false);
            $table->string('name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('bank')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->timestamps();
            $table->foreignId('user_id')->nullable()->constrained(table: 'users', indexName: 'registration_user_id');
            $table->foreignId('created_by')->constrained(table: 'admins', indexName: 'registration_created_by');
        });

        Schema::create('store_registration', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->boolean('has_mechanic_role');
            $table->integer('level');
            $table->foreignId('upline_id')->nullable()->constrained(table: 'stores', indexName: 'store_registration_id_upline');
            $table->foreignId('registration_id')->constrained(table: 'registration', indexName: 'store_registration_registration_id')->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained(table: 'stores', indexName: 'store_registration_store_id');
            $table->timestamps();
        });

        Schema::create('mechanic_registration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained(table: 'stores', indexName: 'mechanic_registration_store_id');
            $table->foreignId('registration_id')->constrained(table: 'registration', indexName: 'mechanic_registration_registration_id')->onDelete('cascade');
            $table->foreignId('mechanic_id')->nullable()->constrained(table: 'mechanics', indexName: 'mechanic_registration_mechanic_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_registration');
        Schema::dropIfExists('mechanic_registration');
        Schema::dropIfExists('registration');
    }
};
