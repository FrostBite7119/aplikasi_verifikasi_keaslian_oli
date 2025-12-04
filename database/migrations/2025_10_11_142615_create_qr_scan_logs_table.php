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
        Schema::create('qr_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(table: 'users', indexName: 'scan_log_user_id');
            $table->enum('account_type', ['store', 'mechanic']);
            $table->foreignId('qr_code_id')->constrained(table: 'qr_codes', indexName: 'scan_log_qr_code_id');
            $table->foreignId('store_id')->nullable()->constrained(table: 'stores', indexName: 'scan_log_store_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_scan_logs');
    }
};
