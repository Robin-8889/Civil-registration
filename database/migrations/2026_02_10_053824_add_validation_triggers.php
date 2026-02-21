<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Trigger 1: Validate birth dates (Oracle 21c syntax)
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_birth_validate_dates
            BEFORE INSERT ON birth_records
            FOR EACH ROW
            BEGIN
                IF :NEW.date_of_birth > SYSDATE THEN
                    RAISE_APPLICATION_ERROR(-20001, 'Birth date cannot be in the future');
                END IF;

                IF :NEW.registration_date < :NEW.date_of_birth THEN
                    RAISE_APPLICATION_ERROR(-20002, 'Registration date cannot be before birth date');
                END IF;
            END;
        ");

        // Trigger 2: Validate marriage minimum age (18 years old)
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_marriage_validate_age
            BEFORE INSERT ON marriage_records
            FOR EACH ROW
            DECLARE
                v_groom_dob DATE;
                v_bride_dob DATE;
            BEGIN
                SELECT date_of_birth INTO v_groom_dob FROM birth_records WHERE id = :NEW.groom_id;
                SELECT date_of_birth INTO v_bride_dob FROM birth_records WHERE id = :NEW.bride_id;

                IF (MONTHS_BETWEEN(:NEW.date_of_marriage, v_groom_dob) / 12) < 18 OR
                   (MONTHS_BETWEEN(:NEW.date_of_marriage, v_bride_dob) / 12) < 18 THEN
                    RAISE_APPLICATION_ERROR(-20003, 'Both spouses must be at least 18 years old');
                END IF;

                IF :NEW.date_of_marriage > SYSDATE THEN
                    RAISE_APPLICATION_ERROR(-20004, 'Marriage date cannot be in the future');
                END IF;
            END;
        ");

        // Trigger 3: Validate death record sequence
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_death_validate_sequence
            BEFORE INSERT ON death_records
            FOR EACH ROW
            DECLARE
                v_birth_date DATE;
            BEGIN
                SELECT date_of_birth INTO v_birth_date FROM birth_records WHERE id = :NEW.deceased_birth_id;

                IF :NEW.date_of_death <= v_birth_date THEN
                    RAISE_APPLICATION_ERROR(-20005, 'Death date must be after birth date');
                END IF;

                IF :NEW.date_of_death > SYSDATE THEN
                    RAISE_APPLICATION_ERROR(-20006, 'Death date cannot be in the future');
                END IF;
            END;
        ");

        // Trigger 4: Audit log for birth record insertion
        // Note: Oracle uses '||' for string concatenation
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_audit_birth_insert
            AFTER INSERT ON birth_records
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (user_id, action, module, description, created_at, updated_at)
                VALUES (1, 'created', 'birth_records', 'Birth record created for ' || :NEW.child_first_name, SYSDATE, SYSDATE);
            END;
        ");

        // Trigger 5: Prevent deletion of issued certificates
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_prevent_cert_delete
            BEFORE DELETE ON certificates
            FOR EACH ROW
            BEGIN
                IF :OLD.status = 'issued' THEN
                    RAISE_APPLICATION_ERROR(-20007, 'Cannot delete issued certificates');
                END IF;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER trg_birth_validate_dates");
        DB::unprepared("DROP TRIGGER trg_marriage_validate_age");
        DB::unprepared("DROP TRIGGER trg_death_validate_sequence");
        DB::unprepared("DROP TRIGGER trg_audit_birth_insert");
        DB::unprepared("DROP TRIGGER trg_prevent_cert_delete");
    }
};
