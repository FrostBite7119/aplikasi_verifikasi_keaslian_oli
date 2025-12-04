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
        Schema::create('account_type_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('account_type', ['store', 'mechanic']);
            $table->foreignId('changed_by')->nullable()->constrained(table: 'admins', indexName: 'account_type_logs_changed_by');
            $table->foreignId('user_id')->constrained(table: 'users', indexName: 'account_type_logs_user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_type_logs');
    }
};
