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
        // Schema::create('admin_activity_logs', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('admin_id')->nullable()->index();
        //     $table->string('action', 100)->index();
        //     $table->string('module', 100)->nullable()->index();
        //     $table->text('description')->nullable();

        //     // JSON snapshots & metadata
        //     $table->json('before_values')->nullable();
        //     $table->json('after_values')->nullable();
        //     $table->json('changed_fields')->nullable(); // array of field names
        //     $table->json('metadata')->nullable();

        //     // Polymorphic target that was affected (optional)
        //     $table->string('related_type', 120)->nullable();
        //     $table->unsignedBigInteger('related_id')->nullable();

        //     // Request / environment context
        //     $table->boolean('success')->default(true)->index();
        //     $table->string('ip_address', 45)->nullable();
        //     $table->text('user_agent')->nullable();
        //     $table->string('request_method', 10)->nullable();
        //     $table->string('url', 255)->nullable();
        //     $table->smallInteger('status_code')->nullable();

        //     // Correlation / tracing
        //     $table->uuid('correlation_id')->nullable()->index();

        //     // Explicit action time (separate from created_at for flexibility)
        //     $table->timestamp('performed_at')->useCurrent()->index();

        //     $table->timestamps();

        //     // Foreign key referencing admins table
        //     $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        //     $table->index(['related_type', 'related_id']);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
