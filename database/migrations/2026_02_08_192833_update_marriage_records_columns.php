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
        Schema::table('marriage_records', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['groom_name', 'bride_name', 'witnesses']);

            // Add new witness columns
            $table->string('witness1_name')->nullable();
            $table->string('witness2_name')->nullable();
        });

        // Drop old status enum and add new one
        Schema::table('marriage_records', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('marriage_records', function (Blueprint $table) {
            $table->enum('status', ['registered', 'pending', 'rejected'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marriage_records', function (Blueprint $table) {
            // Add back old columns
            $table->string('groom_name');
            $table->string('bride_name');
            $table->text('witnesses')->nullable();

            // Drop new witness columns
            $table->dropColumn(['witness1_name', 'witness2_name']);
        });

        // Revert status enum
        Schema::table('marriage_records', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('marriage_records', function (Blueprint $table) {
            $table->enum('status', ['married', 'pending', 'annulled'])->default('pending');
        });
    }
};
