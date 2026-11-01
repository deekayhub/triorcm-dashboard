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
        Schema::table('providers', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('middle_name');
            $table->unsignedBigInteger('team_id')->nullable()->index()->after('user_id');
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
            $table->unsignedBigInteger('created_by')->nullable()->index()->after('team_id');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('updated_by')->nullable()->index()->after('created_by');
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['display_name', 'team_id', 'created_by', 'updated_by']);
        });
    }
};
