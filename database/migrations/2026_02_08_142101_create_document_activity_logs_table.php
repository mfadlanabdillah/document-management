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
        Schema::create('document_activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('document_id');
            $table->string('action');
            $table->uuid('performed_by');
            $table->jsonb('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_activity_logs');
    }
};
