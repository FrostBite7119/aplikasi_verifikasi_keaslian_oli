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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_id')->unique();
            $table->string('name');
            $table->string('phone_number');
            $table->text('description');
            $table->string('image')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('province');
            $table->string('latitude');
            $table->string('longitude');
            $table->foreignId('product_id')->nullable()->constrained(table: 'products', indexName: 'reports_product_id');
            $table->foreignId('authenticity_qr_code_scan_id')->nullable()->constrained(table: 'authenticity_qr_code_scans', indexName: 'reports_authenticity_qr_code_scan_id');
            $table->timestamps();
        });

        Schema::create('report_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('report_reason_id')->unique();
            $table->string('reason');
            $table->timestamps();
        });

        Schema::create('report_report_reason', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained(table: 'reports', indexName: 'report_report_reason_report_id')->onDelete('cascade');
            $table->foreignId('report_reason_id')->constrained(table: 'report_reasons', indexName: 'report_report_reason_report_reason_id')->onDelete('cascade');
            $table->unique(['report_id', 'report_reason_id'], 'report_report_reason_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_report_reason');
        Schema::dropIfExists('report_reasons');
        Schema::dropIfExists('reports');
    }
};
