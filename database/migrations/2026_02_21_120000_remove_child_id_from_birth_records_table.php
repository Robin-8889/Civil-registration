<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('birth_records', 'child_id')) {
            return;
        }

        try {
            DB::statement("BEGIN
                DECLARE
                    v_constraint_name VARCHAR2(200);
                BEGIN
                    SELECT constraint_name INTO v_constraint_name
                    FROM user_cons_columns
                    WHERE table_name = 'BIRTH_RECORDS'
                      AND column_name = 'CHILD_ID'
                      AND ROWNUM = 1;

                    EXECUTE IMMEDIATE 'ALTER TABLE birth_records DROP CONSTRAINT ' || v_constraint_name;
                EXCEPTION
                    WHEN NO_DATA_FOUND THEN
                        NULL;
                END;
            END;");
        } catch (\Exception $e) {
            // Constraint may not exist, continue
        }

        Schema::table('birth_records', function (Blueprint $table) {
            $table->dropColumn('child_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('birth_records', 'child_id')) {
            return;
        }

        Schema::table('birth_records', function (Blueprint $table) {
            $table->unsignedBigInteger('child_id')->nullable();
            $table->foreign('child_id')->references('id')->on('citizens')->onDelete('cascade');
        });
    }
};
