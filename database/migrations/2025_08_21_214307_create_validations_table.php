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
        Schema::create('validations', function (Blueprint $table) {
            $table->id();
            $table->string('validation_id')->unique();
            $table->json('before_values');
            $table->json('after_values');
            $table->string('id_card_photo')->nullable();
            $table->string('selfie_photo')->nullable();
            $table->longText('note')->nullable();
            $table->string('phone_number');
            $table->enum('status', ['approved', 'rejected']);
            $table->enum('validation_type', ['store', 'mechanic']);
            $table->foreignId('user_id')->constrained(table: 'users', indexName: 'validations_user_id');
            $table->foreignId('validated_by')->constrained(table: 'admins', indexName: 'validations_validated_by');
            $table->timestamps();
        });

        Schema::create('store_validations', function (Blueprint $table){
            $table->id();
            $table->string('store_name');
            $table->string('store_photo')->nullable();
            $table->string('old_address')->nullable();
            $table->string('new_address')->nullable();
            $table->boolean('has_mechanic_role')->default(false);
            $table->foreignId('store_id')->constrained(table: 'stores', indexName: 'store_validations_store_id');
            $table->foreignId('validation_id')->constrained(table: 'validations', indexName: 'store_validations_validation_id')->onDelete('cascade');
        });

        Schema::create('mechanic_validations', function (Blueprint $table){
            $table->id();
            $table->foreignId('mechanic_id')->constrained(table: 'mechanics', indexName: 'mechanic_validations_mechanic_id');
            $table->foreignId('store_id')->constrained(table: 'stores', indexName: 'mechanic_validations_store_id');
            $table->foreignId('validation_id')->constrained(table: 'validations', indexName: 'mechanic_validations_validation_id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validations');
        Schema::dropIfExists('store_validations');
        Schema::dropIfExists('mechanic_validations');
    }
};
