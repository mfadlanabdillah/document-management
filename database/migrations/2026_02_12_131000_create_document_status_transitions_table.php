<?php

use App\Models\DocumentStatus;
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
        Schema::create('document_status_transitions', function (Blueprint $table) {
            $table->uuid('from_status_id');
            $table->uuid('to_status_id');

            $table->primary(['from_status_id', 'to_status_id']);
            $table->foreign('from_status_id')->references('id')->on('document_statuses')->cascadeOnDelete();
            $table->foreign('to_status_id')->references('id')->on('document_statuses')->cascadeOnDelete();
        });

        $draft = DocumentStatus::query()->where('code', 'draft')->first();
        $active = DocumentStatus::query()->where('code', 'active')->first();
        $archived = DocumentStatus::query()->where('code', 'archived')->first();

        if ($draft && $active && $archived) {
            DB::table('document_status_transitions')->insert([
                ['from_status_id' => $draft->id, 'to_status_id' => $active->id],
                ['from_status_id' => $active->id, 'to_status_id' => $archived->id],
                ['from_status_id' => $archived->id, 'to_status_id' => $active->id],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_status_transitions');
    }
};
