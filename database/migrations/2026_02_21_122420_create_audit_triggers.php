<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates comprehensive audit triggers for all sensitive operations:
     * - Birth Records: INSERT, UPDATE, DELETE
     * - Marriage Records: INSERT, UPDATE, DELETE
     * - Death Records: INSERT, UPDATE, DELETE
     * - Certificates: DELETE (prevent deletion of issued)
     *
     * Automatically logs changes to audit_logs table with:
     * - Action (created, updated, deleted)
     * - Module name
     * - Description
     * - What changed (changes column)
     */
    public function up(): void
    {
        // ====== BIRTH RECORDS AUDIT TRIGGERS ======

        // Log birth record creation
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_audit_birth_insert
            AFTER INSERT ON birth_records
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (user_id, action, module, description, changes, created_at, updated_at)
                VALUES (
                    1,
                    'created',
                    'birth_records',
                    'Birth record created: ' || :NEW.child_first_name || ' ' || :NEW.child_last_name || ' (Certificate: ' || :NEW.birth_certificate_no || ')',
                    'New record. Child: ' || :NEW.child_first_name || ' ' || :NEW.child_last_name || ', DOB: ' || TO_CHAR(:NEW.date_of_birth, 'YYYY-MM-DD') || ', Gender: ' || :NEW.gender,
                    SYSDATE,
                    SYSDATE
                );
            END;
        ");

        // Log birth record updates (status changes, etc.)
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_audit_birth_update
            AFTER UPDATE ON birth_records
            FOR EACH ROW
            BEGIN
                IF :OLD.status != :NEW.status OR :OLD.child_first_name != :NEW.child_first_name THEN
                    INSERT INTO audit_logs (user_id, action, module, description, changes, created_at, updated_at)
                    VALUES (
                        1,
                        'updated',
                        'birth_records',
                        'Birth record updated: ' || :NEW.child_first_name || ' ' || :NEW.child_last_name || ' (Certificate: ' || :NEW.birth_certificate_no || ')',
                        'Status: ' || :OLD.status || ' → ' || :NEW.status ||
                        CASE WHEN :OLD.child_first_name != :NEW.child_first_name THEN ' | Name: ' || :OLD.child_first_name || ' → ' || :NEW.child_first_name ELSE '' END,
                        SYSDATE,
                        SYSDATE
                    );
                END IF;
            END;
        ");

        // Log birth record deletion
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_audit_birth_delete
            AFTER DELETE ON birth_records
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (user_id, action, module, description, changes, created_at, updated_at)
                VALUES (
                    1,
                    'deleted',
                    'birth_records',
                    'Birth record DELETED: ' || :OLD.child_first_name || ' ' || :OLD.child_last_name || ' (Certificate: ' || :OLD.birth_certificate_no || ')',
                    'Deleted child: ' || :OLD.child_first_name || ' ' || :OLD.child_last_name || ', DOB: ' || TO_CHAR(:OLD.date_of_birth, 'YYYY-MM-DD') || ', Status was: ' || :OLD.status,
                    SYSDATE,
                    SYSDATE
                );
            END;
        ");

        // ====== MARRIAGE RECORDS AUDIT TRIGGERS ======

        // Log marriage record creation
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_audit_marriage_insert
            AFTER INSERT ON marriage_records
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (user_id, action, module, description, changes, created_at, updated_at)
                VALUES (
                    1,
                    'created',
                    'marriage_records',
                    'Marriage record created (Certificate: ' || :NEW.marriage_certificate_no || ')',
                    'New marriage record. Groom ID: ' || :NEW.groom_id || ', Bride ID: ' || :NEW.bride_id || ', Date: ' || TO_CHAR(:NEW.date_of_marriage, 'YYYY-MM-DD') || ', Location: ' || :NEW.place_of_marriage,
                    SYSDATE,
                    SYSDATE
                );
            END;
        ");

        // Log marriage record updates (status changes)
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_audit_marriage_update
            AFTER UPDATE ON marriage_records
            FOR EACH ROW
            BEGIN
                IF :OLD.status != :NEW.status THEN
                    INSERT INTO audit_logs (user_id, action, module, description, changes, created_at, updated_at)
                    VALUES (
                        1,
                        'updated',
                        'marriage_records',
                        'Marriage record updated (Certificate: ' || :NEW.marriage_certificate_no || ')',
                        'Status: ' || :OLD.status || ' → ' || :NEW.status || ' | Certificate: ' || :NEW.marriage_certificate_no,
                        SYSDATE,
                        SYSDATE
                    );
                END IF;
            END;
        ");

        // Log marriage record deletion
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_audit_marriage_delete
            AFTER DELETE ON marriage_records
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (user_id, action, module, description, changes, created_at, updated_at)
                VALUES (
                    1,
                    'deleted',
                    'marriage_records',
                    'Marriage record DELETED (Certificate: ' || :OLD.marriage_certificate_no || ')',
                    'Deleted marriage between Groom ID: ' || :OLD.groom_id || ' and Bride ID: ' || :OLD.bride_id || ', Date: ' || TO_CHAR(:OLD.date_of_marriage, 'YYYY-MM-DD') || ', Status was: ' || :OLD.status,
                    SYSDATE,
                    SYSDATE
                );
            END;
        ");

        // ====== DEATH RECORDS AUDIT TRIGGERS ======

        // Log death record creation
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_audit_death_insert
            AFTER INSERT ON death_records
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (user_id, action, module, description, changes, created_at, updated_at)
                VALUES (
                    1,
                    'created',
                    'death_records',
                    'Death record created (Certificate: ' || :NEW.death_certificate_no || ')',
                    'New death record. Deceased Birth ID: ' || :NEW.deceased_birth_id || ', Date of Death: ' || TO_CHAR(:NEW.date_of_death, 'YYYY-MM-DD') || ', Location: ' || :NEW.place_of_death || ', Cause: ' || :NEW.cause_of_death,
                    SYSDATE,
                    SYSDATE
                );
            END;
        ");

        // Log death record updates (status changes)
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_audit_death_update
            AFTER UPDATE ON death_records
            FOR EACH ROW
            BEGIN
                IF :OLD.status != :NEW.status THEN
                    INSERT INTO audit_logs (user_id, action, module, description, changes, created_at, updated_at)
                    VALUES (
                        1,
                        'updated',
                        'death_records',
                        'Death record updated (Certificate: ' || :NEW.death_certificate_no || ')',
                        'Status: ' || :OLD.status || ' → ' || :NEW.status || ' | Certificate: ' || :NEW.death_certificate_no,
                        SYSDATE,
                        SYSDATE
                    );
                END IF;
            END;
        ");

        // Log death record deletion
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_audit_death_delete
            AFTER DELETE ON death_records
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (user_id, action, module, description, changes, created_at, updated_at)
                VALUES (
                    1,
                    'deleted',
                    'death_records',
                    'Death record DELETED (Certificate: ' || :OLD.death_certificate_no || ')',
                    'Deleted death record. Deceased Birth ID: ' || :OLD.deceased_birth_id || ', Date was: ' || TO_CHAR(:OLD.date_of_death, 'YYYY-MM-DD') || ', Cause was: ' || :OLD.cause_of_death || ', Status was: ' || :OLD.status,
                    SYSDATE,
                    SYSDATE
                );
            END;
        ");

        // ====== CERTIFICATE AUDIT TRIGGERS ======

        // Prevent deletion of issued certificates
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

        // Log certificate deletion (only if not issued)
        DB::unprepared("
            CREATE OR REPLACE TRIGGER trg_audit_cert_delete
            AFTER DELETE ON certificates
            FOR EACH ROW
            BEGIN
                INSERT INTO audit_logs (user_id, action, module, description, changes, created_at, updated_at)
                VALUES (
                    1,
                    'deleted',
                    'certificates',
                    'Certificate deleted: ' || :OLD.record_type || ' record ' || :OLD.record_id,
                    'Deleted certificate. Type: ' || :OLD.record_type || ', Record ID: ' || :OLD.record_id || ', Status was: ' || :OLD.status,
                    SYSDATE,
                    SYSDATE
                );
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all audit triggers in reverse order
        DB::unprepared('DROP TRIGGER trg_audit_birth_insert');
        DB::unprepared('DROP TRIGGER trg_audit_birth_update');
        DB::unprepared('DROP TRIGGER trg_audit_birth_delete');

        DB::unprepared('DROP TRIGGER trg_audit_marriage_insert');
        DB::unprepared('DROP TRIGGER trg_audit_marriage_update');
        DB::unprepared('DROP TRIGGER trg_audit_marriage_delete');

        DB::unprepared('DROP TRIGGER trg_audit_death_insert');
        DB::unprepared('DROP TRIGGER trg_audit_death_update');
        DB::unprepared('DROP TRIGGER trg_audit_death_delete');

        DB::unprepared('DROP TRIGGER trg_prevent_cert_delete');
        DB::unprepared('DROP TRIGGER trg_audit_cert_delete');
    }
};
