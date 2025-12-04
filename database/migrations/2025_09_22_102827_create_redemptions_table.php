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
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->string('redemption_id')->unique();
            $table->unsignedInteger('points');
            $table->enum('status', ['pending', 'processed', 'rejected', 'done'])->default('pending');
            $table->string('bank');
            $table->string('bank_account_number');
            $table->longText('note')->nullable();
            $table->string('transfer_receipt')->nullable();
            $table->foreignId('user_id')->constrained(table: 'users', indexName: 'redemptions_user_id');
            $table->foreignId('transaction_id')->constrained(table: 'point_ledger', indexName: 'redemptions_transaction_id');
            $table->foreignId('refunded_transaction_id')->nullable()->constrained(table: 'point_ledger', indexName: 'redemptions_refunded_transaction_id');
            $table->foreignId('processed_by')->nullable()->constrained(table: 'admins', indexName: 'redemptions_processed_by');
            $table->foreignId('done_by')->nullable()->constrained(table: 'admins', indexName: 'redemptions_done_by');
            $table->timestamp('transfered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redemptions');
    }
};
