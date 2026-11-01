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
        Schema::table('provider_documents', function (Blueprint $table) {
            $table->date('issue_date')->nullable()->after('type');
            $table->date('expiration_date')->nullable()->after('issue_date');
            $table->string('checksum', 64)->nullable()->after('file_size');
            $table->string('file_disk', 50)->default('local')->after('file_path');
            $table->unsignedInteger('version')->default(1)->after('file_size');
            $table->uuid('version_group')->nullable()->after('version');
            $table->boolean('is_current')->default(true)->after('version_group');
            $table->foreignId('supersedes_id')->nullable()->after('is_current')
                ->constrained('provider_documents')->nullOnDelete();
            $table->timestamp('archived_at')->nullable()->after('uploaded_by');
            $table->foreignId('archived_by')->nullable()->after('archived_at')
                ->constrained('users')->nullOnDelete();

            $table->index(['provider_id', 'is_current'], 'provider_documents_provider_current_index');
            $table->index('version_group', 'provider_documents_version_group_index');
            $table->index('expiration_date', 'provider_documents_expiration_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provider_documents', function (Blueprint $table) {
            $table->dropForeign(['supersedes_id']);
            $table->dropForeign(['archived_by']);
            $table->dropIndex('provider_documents_provider_current_index');
            $table->dropIndex('provider_documents_version_group_index');
            $table->dropIndex('provider_documents_expiration_index');

            $table->dropColumn([
                'issue_date',
                'expiration_date',
                'checksum',
                'file_disk',
                'version',
                'version_group',
                'is_current',
                'supersedes_id',
                'archived_at',
                'archived_by',
            ]);
        });
    }
};
