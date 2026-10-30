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
        Schema::create('provider_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('license_number');
            $table->string('state', 2);
            $table->enum('license_type', ['md', 'do', 'np', 'pa', 'rn', 'other'])->default('md');
            $table->date('issue_date')->nullable();
            $table->date('expiration_date');
            $table->enum('status', ['active', 'expired', 'suspended', 'revoked'])->default('active');
            $table->string('issuing_board')->nullable();
            $table->timestamps();
            $table->unique(['provider_id', 'license_number', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_licenses');
    }
};
