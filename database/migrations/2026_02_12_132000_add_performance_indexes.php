<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->index('status', 'documents_status_idx');
            $table->index('category_id', 'documents_category_id_idx');
            $table->index('created_by', 'documents_created_by_idx');
            $table->index('deleted_at', 'documents_deleted_at_idx');
            $table->index(['status', 'deleted_at'], 'documents_status_deleted_at_idx');
            $table->index(['category_id', 'deleted_at'], 'documents_category_deleted_at_idx');
            $table->index(['created_at', 'deleted_at'], 'documents_created_at_deleted_at_idx');
            $table->index(['updated_at', 'deleted_at'], 'documents_updated_at_deleted_at_idx');
        });

        Schema::table('document_versions', function (Blueprint $table) {
            $table->index('document_id', 'document_versions_document_id_idx');
            $table->index('uploaded_by', 'document_versions_uploaded_by_idx');
        });

        Schema::table('document_activity_logs', function (Blueprint $table) {
            $table->index('document_id', 'document_activity_logs_document_id_idx');
            $table->index('performed_by', 'document_activity_logs_performed_by_idx');
            $table->index('created_at', 'document_activity_logs_created_at_idx');
        });

        Schema::table('document_statuses', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'document_statuses_active_sort_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('slug', 'categories_slug_idx');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->index('slug', 'tags_slug_idx');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
            DB::statement('CREATE INDEX IF NOT EXISTS documents_title_trgm_idx ON documents USING gin (title gin_trgm_ops)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS documents_title_trgm_idx');
        }

        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex('tags_slug_idx');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_slug_idx');
        });

        Schema::table('document_statuses', function (Blueprint $table) {
            $table->dropIndex('document_statuses_active_sort_idx');
        });

        Schema::table('document_activity_logs', function (Blueprint $table) {
            $table->dropIndex('document_activity_logs_document_id_idx');
            $table->dropIndex('document_activity_logs_performed_by_idx');
            $table->dropIndex('document_activity_logs_created_at_idx');
        });

        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropIndex('document_versions_document_id_idx');
            $table->dropIndex('document_versions_uploaded_by_idx');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex('documents_status_idx');
            $table->dropIndex('documents_category_id_idx');
            $table->dropIndex('documents_created_by_idx');
            $table->dropIndex('documents_deleted_at_idx');
            $table->dropIndex('documents_status_deleted_at_idx');
            $table->dropIndex('documents_category_deleted_at_idx');
            $table->dropIndex('documents_created_at_deleted_at_idx');
            $table->dropIndex('documents_updated_at_deleted_at_idx');
        });
    }
};
