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
        Schema::create('provider_education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('institution_name');
            $table->string('degree');
            $table->enum('education_type', ['medical_school', 'residency', 'fellowship', 'internship', 'other_training']);
            $table->string('specialty')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->string('country')->default('US');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->enum('verification_status', ['unverified', 'pending', 'verified', 'failed'])->default('unverified');
            $table->foreignId('document_id')->nullable()->constrained('provider_documents')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_education');
    }
};
