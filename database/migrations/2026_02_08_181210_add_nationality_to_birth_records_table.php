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
        Schema::table('birth_records', function (Blueprint $table) {
            // Make child_id nullable
            $table->unsignedBigInteger('child_id')->nullable()->change();
            // Add nationality field
            $table->string('nationality')->nullable()->after('child_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('birth_records', function (Blueprint $table) {
            // Drop nationality column
            $table->dropColumn('nationality');
            // Revert child_id to not nullable
            $table->unsignedBigInteger('child_id')->nullable(false)->change();
        });
    }
};
