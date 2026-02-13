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
        Schema::table('death_records', function (Blueprint $table) {
            // Drop old foreign key and column
            $table->dropForeign(['deceased_id']);
            $table->dropColumn('deceased_id');
            $table->dropColumn('deceased_name');

            // Add new columns referencing birth_records
            $table->foreignId('deceased_birth_id')->after('id')->constrained('birth_records')->onDelete('cascade');
            $table->foreignId('informant_birth_id')->nullable()->after('deceased_birth_id')->constrained('birth_records')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('death_records', function (Blueprint $table) {
            // Drop new columns
            $table->dropForeign(['deceased_birth_id']);
            $table->dropForeign(['informant_birth_id']);
            $table->dropColumn(['deceased_birth_id', 'informant_birth_id']);

            // Restore old columns
            $table->foreignId('deceased_id')->after('id')->constrained('citizens')->onDelete('cascade');
            $table->string('deceased_name')->after('death_certificate_no');
        });
    }
};
