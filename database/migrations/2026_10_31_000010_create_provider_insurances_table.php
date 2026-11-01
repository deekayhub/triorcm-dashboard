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
        Schema::create('provider_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('carrier');
            $table->string('policy_number');
            $table->enum('coverage_type', ['claims_made', 'occurrence'])->default('claims_made');
            $table->decimal('per_claim_amount', 15, 2)->nullable();
            $table->decimal('aggregate_amount', 15, 2)->nullable();
            $table->date('effective_date');
            $table->date('expiration_date');
            $table->string('agent_contact')->nullable();
            $table->string('agent_phone')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('provider_documents')->nullOnDelete();
            $table->enum('verification_status', ['unverified', 'pending', 'verified', 'failed'])->default('unverified');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_insurances');
    }
};
