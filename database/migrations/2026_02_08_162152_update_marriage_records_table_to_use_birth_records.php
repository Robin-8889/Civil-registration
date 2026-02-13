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
            // Drop existing foreign keys
            $table->dropForeign(['groom_id']);
            $table->dropForeign(['bride_id']);

            // Add new foreign keys pointing to birth_records
            $table->foreign('groom_id')->references('id')->on('birth_records')->onDelete('cascade');
            $table->foreign('bride_id')->references('id')->on('birth_records')->onDelete('cascade');
        });

        // Update status enum values
        Schema::table('marriage_records', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('marriage_records', function (Blueprint $table) {
            $table->enum('status', ['registered', 'pending', 'rejected'])->default('pending')->after('registration_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marriage_records', function (Blueprint $table) {
            // Drop new foreign keys
            $table->dropForeign(['groom_id']);
            $table->dropForeign(['bride_id']);
        });

        // Revert status enum values
        Schema::table('marriage_records', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('marriage_records', function (Blueprint $table) {
            // Restore old foreign keys
            $table->foreign('groom_id')->references('id')->on('citizens')->onDelete('cascade');
            $table->foreign('bride_id')->references('id')->on('citizens')->onDelete('cascade');

            // Restore old status enum
            $table->enum('status', ['married', 'pending', 'annulled'])->default('pending')->after('registration_date');
        });
    }
};
