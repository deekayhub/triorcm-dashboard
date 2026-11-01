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
        Schema::create('provider_identifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->enum('identifier_type', ['dea', 'taxonomy', 'clia', 'cds', 'other']);
            $table->string('identifier_value');
            $table->string('issuing_state')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->enum('verification_status', ['unverified', 'pending', 'verified', 'failed'])->default('unverified');
            $table->text('verification_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['provider_id', 'identifier_type', 'identifier_value'], 'provider_identifiers_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_identifiers');
    }
};
