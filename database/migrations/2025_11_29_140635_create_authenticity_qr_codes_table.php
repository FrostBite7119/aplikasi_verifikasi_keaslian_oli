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
        Schema::create('authenticity_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('serial_number')->unique();
            $table->integer('total_scans')->default(0);
            $table->foreignId('product_id')->constrained(table: 'products', indexName: 'authenticity_qr_codes_product_id');
            $table->foreignId('created_by')->constrained(table: 'admins', indexName: 'authenticity_qr_codes_created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authenticity_qr_codes');
    }
};
