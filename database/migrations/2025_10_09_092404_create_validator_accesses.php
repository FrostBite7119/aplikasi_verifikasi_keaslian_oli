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
        Schema::create('validator_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('validator_id')->constrained(table: 'validators', indexName: 'validator_store_accesses_validator_id');
            $table->foreignId('granted_by')->constrained(table: 'admins', indexName: 'validator_store_accesses_granted_by');
            $table->foreignId('store_id')->constrained(table: 'stores', indexName: 'validator_store_accesses_store_id')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['validator_id','store_id'], 'validator_store_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validator_accesses');
    }
};
