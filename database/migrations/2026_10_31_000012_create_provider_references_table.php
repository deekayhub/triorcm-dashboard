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
        Schema::create('provider_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('reference_name');
            $table->string('organization')->nullable();
            $table->string('designation')->nullable();
            $table->string('relationship');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->date('reference_date')->nullable();
            $table->enum('verification_status', ['unverified', 'pending', 'verified', 'failed'])->default('unverified');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_references');
    }
};
