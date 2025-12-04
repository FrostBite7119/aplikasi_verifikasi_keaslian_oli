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
        Schema::create('authenticity_qr_code_scans', function (Blueprint $table) {
            $table->id();
            $table->string('qr_code');
            $table->string('ip_address')->nullable();
            $table->string('scan_location');
            $table->string('city');
            $table->string('province');
            $table->string('latitude');
            $table->string('longitude');
            $table->enum('scan_type', ['success', 'limit_exceeded', 'not_found'])->default('success');
            $table->foreignId('authenticity_qr_code_id')->nullable()->constrained(table: 'authenticity_qr_codes', indexName: 'authenticity_qr_code_scans_authenticity_qr_code_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authenticity_qr_code_scans');
    }
};
