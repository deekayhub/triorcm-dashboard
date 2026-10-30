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
        Schema::create('provider_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_application_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['primary_source', 'education', 'training', 'work_history', 'malpractice', 'sanctions', 'other'])->default('primary_source');
            $table->string('verification_source')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'verified', 'unable_to_verify', 'discrepancy'])->default('pending');
            $table->date('requested_at')->nullable();
            $table->date('received_at')->nullable();
            $table->date('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->text('findings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_verifications');
    }
};
