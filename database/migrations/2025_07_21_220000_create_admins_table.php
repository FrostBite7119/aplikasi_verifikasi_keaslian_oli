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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('admin_id')->unique();
            $table->string('name');
            $table->string('phone_number')->unique();
            $table->string('role');
            $table->string('profile_picture')->nullable();
            $table->string('password');
            $table->string('last_login_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('validators', function (Blueprint $table) {
            $table->id();
            $table->string('address');
            $table->foreignId('city_id')->constrained(table: 'regions', indexName: 'validators_city_id');
            $table->string('job');
            $table->string('id_card_photo');
            $table->foreignId('admin_id')->constrained(table: 'admins', indexName: 'validators_admin_id')->onDelete('cascade');
            $table->softDeletes();
        });

        Schema::create('admin_password_reset_tokens', function (Blueprint $table) {
            $table->string('phone_number')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Schema::create('sessions', function (Blueprint $table) {
        //     $table->string('id')->primary();
        //     $table->foreignId('user_id')->nullable()->index();
        //     $table->string('ip_address', 45)->nullable();
        //     $table->text('user_agent')->nullable();
        //     $table->longText('payload');
        //     $table->integer('last_activity')->index();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
