<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates stored procedures for vital statistics reporting:
     * - Birth statistics by region, year, gender
     * - Death statistics by age group, cause
     * - Marriage statistics by region, year
     * - Population demographics
     *
     * These procedures are used for government submissions and annual reports
     */
    public function up(): void
    {
        // ====== PROCEDURE 1: Birth Statistics by Region & Year ======
        DB::unprepared("
            CREATE OR REPLACE PROCEDURE sp_birth_statistics_by_region (
                p_year IN NUMBER DEFAULT NULL,
                p_cursor OUT SYS_REFCURSOR
            )
            IS
            BEGIN
                OPEN p_cursor FOR
                SELECT
                    ro.region,
                    TO_CHAR(br.registration_date, 'YYYY') as registration_year,
                    COUNT(*) as total_births,
                    SUM(CASE WHEN br.gender = 'M' THEN 1 ELSE 0 END) as male_births,
                    SUM(CASE WHEN br.gender = 'F' THEN 1 ELSE 0 END) as female_births,
                    br.status as record_status
                FROM birth_records br
                JOIN registration_offices ro ON br.registration_office_id = ro.id
                WHERE (p_year IS NULL OR TO_CHAR(br.registration_date, 'YYYY') = TO_CHAR(p_year))
                GROUP BY ro.region, TO_CHAR(br.registration_date, 'YYYY'), br.status
                ORDER BY ro.region, registration_year DESC, record_status;
            END sp_birth_statistics_by_region;
        ");

        // ====== PROCEDURE 2: Death Statistics by Age Group ======
        DB::unprepared("
            CREATE OR REPLACE PROCEDURE sp_death_statistics_by_age (
                p_cursor OUT SYS_REFCURSOR
            )
            IS
            BEGIN
                OPEN p_cursor FOR
                SELECT
                    CASE
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 1 THEN 'Under 1'
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 5 THEN '1-4'
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 18 THEN '5-17'
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 35 THEN '18-34'
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 50 THEN '35-49'
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 65 THEN '50-64'
                        ELSE '65+'
                    END as age_group,
                    COUNT(*) as total_deaths,
                    ro.region,
                    dr.cause_of_death as leading_death_cause
                FROM death_records dr
                JOIN birth_records br ON dr.deceased_birth_id = br.id
                JOIN registration_offices ro ON br.registration_office_id = ro.id
                WHERE dr.status IN ('registered', 'pending')
                GROUP BY
                    CASE
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 1 THEN 'Under 1'
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 5 THEN '1-4'
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 18 THEN '5-17'
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 35 THEN '18-34'
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 50 THEN '35-49'
                        WHEN TRUNC((SYSDATE - br.date_of_birth) / 365.25) < 65 THEN '50-64'
                        ELSE '65+'
                    END,
                    ro.region,
                    dr.cause_of_death
                ORDER BY age_group, total_deaths DESC;
            END sp_death_statistics_by_age;
        ");

        // ====== PROCEDURE 3: Marriage Statistics by Region & Year ======
        DB::unprepared("
            CREATE OR REPLACE PROCEDURE sp_marriage_statistics_by_region (
                p_year IN NUMBER DEFAULT NULL,
                p_cursor OUT SYS_REFCURSOR
            )
            IS
            BEGIN
                OPEN p_cursor FOR
                SELECT
                    ro.region,
                    TO_CHAR(mr.date_of_marriage, 'YYYY') as marriage_year,
                    COUNT(*) as total_marriages,
                    mr.status as record_status,
                    TO_CHAR(mr.date_of_marriage, 'MONTH') as month_registered
                FROM marriage_records mr
                JOIN registration_offices ro ON mr.registration_office_id = ro.id
                WHERE (p_year IS NULL OR TO_CHAR(mr.date_of_marriage, 'YYYY') = TO_CHAR(p_year))
                GROUP BY ro.region, TO_CHAR(mr.date_of_marriage, 'YYYY'), mr.status, TO_CHAR(mr.date_of_marriage, 'MONTH')
                ORDER BY ro.region, marriage_year DESC, month_registered;
            END sp_marriage_statistics_by_region;
        ");

        // ====== PROCEDURE 4: Population Demographics Report ======
        DB::unprepared("
            CREATE OR REPLACE PROCEDURE sp_population_demographics (
                p_region IN VARCHAR2 DEFAULT NULL,
                p_cursor OUT SYS_REFCURSOR
            )
            IS
            BEGIN
                OPEN p_cursor FOR
                SELECT
                    ro.region,
                    COUNT(c.id) as total_citizens,
                    SUM(CASE WHEN c.gender = 'M' THEN 1 ELSE 0 END) as male_count,
                    SUM(CASE WHEN c.gender = 'F' THEN 1 ELSE 0 END) as female_count,
                    ROUND(AVG(TRUNC((SYSDATE - c.date_of_birth) / 365.25)), 2) as average_age,
                    SUM(CASE WHEN c.is_married = 1 THEN 1 ELSE 0 END) as married_count,
                    SUM(CASE WHEN c.is_dead = 1 THEN 1 ELSE 0 END) as deceased_count
                FROM citizens c
                JOIN registration_offices ro ON c.registration_office_id = ro.id
                WHERE (p_region IS NULL OR ro.region = p_region)
                GROUP BY ro.region
                ORDER BY ro.region;
            END sp_population_demographics;
        ");

        // ====== PROCEDURE 5: Annual Vital Statistics Summary ======
        DB::unprepared("
            CREATE OR REPLACE PROCEDURE sp_annual_vital_summary (
                p_year IN NUMBER,
                p_cursor OUT SYS_REFCURSOR
            )
            IS
            BEGIN
                OPEN p_cursor FOR
                SELECT
                    p_year as report_year,
                    (SELECT COUNT(*) FROM birth_records WHERE TO_CHAR(registration_date, 'YYYY') = TO_CHAR(p_year) AND status IN ('registered', 'pending')) as births,
                    (SELECT COUNT(*) FROM death_records WHERE TO_CHAR(registration_date, 'YYYY') = TO_CHAR(p_year) AND status IN ('registered', 'pending')) as deaths,
                    (SELECT COUNT(*) FROM marriage_records WHERE TO_CHAR(date_of_marriage, 'YYYY') = TO_CHAR(p_year) AND status IN ('registered', 'pending')) as marriages,
                    (SELECT COUNT(*) FROM birth_records WHERE TO_CHAR(registration_date, 'YYYY') = TO_CHAR(p_year) AND status = 'pending') as pending_births,
                    (SELECT COUNT(*) FROM birth_records WHERE TO_CHAR(registration_date, 'YYYY') = TO_CHAR(p_year) AND status = 'rejected') as rejected_births,
                    SYSDATE as report_generated_date
                FROM DUAL;
            END sp_annual_vital_summary;
        ");

        // ====== PROCEDURE 6: Birth Completeness Report (for government compliance) ======
        DB::unprepared("
            CREATE OR REPLACE PROCEDURE sp_birth_registration_completeness (
                p_cursor OUT SYS_REFCURSOR
            )
            IS
            BEGIN
                OPEN p_cursor FOR
                SELECT
                    ro.region,
                    COUNT(br.id) as total_registrations,
                    SUM(CASE WHEN br.status = 'registered' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN br.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN br.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    ROUND(
                        (SUM(CASE WHEN br.status = 'registered' THEN 1 ELSE 0 END) / COUNT(br.id)) * 100,
                        2
                    ) as completion_percentage
                FROM birth_records br
                JOIN registration_offices ro ON br.registration_office_id = ro.id
                GROUP BY ro.region
                ORDER BY completion_percentage DESC;
            END sp_birth_registration_completeness;
        ");

        // ====== PROCEDURE 7: Certificates Issued Report ======
        DB::unprepared("
            CREATE OR REPLACE PROCEDURE sp_certificates_issued_report (
                p_year IN NUMBER DEFAULT NULL,
                p_cursor OUT SYS_REFCURSOR
            )
            IS
            BEGIN
                OPEN p_cursor FOR
                SELECT
                    c.record_type,
                    TO_CHAR(c.issue_date, 'YYYY') as issue_year,
                    COUNT(*) as certificates_issued,
                    SUM(c.copies_issued) as total_copies
                FROM certificates c
                WHERE c.status = 'issued' AND (p_year IS NULL OR TO_CHAR(c.issue_date, 'YYYY') = TO_CHAR(p_year))
                GROUP BY c.record_type, TO_CHAR(c.issue_date, 'YYYY')
                ORDER BY issue_year DESC, record_type;
            END sp_certificates_issued_report;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all procedures
        DB::unprepared('DROP PROCEDURE sp_birth_statistics_by_region');
        DB::unprepared('DROP PROCEDURE sp_death_statistics_by_age');
        DB::unprepared('DROP PROCEDURE sp_marriage_statistics_by_region');
        DB::unprepared('DROP PROCEDURE sp_population_demographics');
        DB::unprepared('DROP PROCEDURE sp_annual_vital_summary');
        DB::unprepared('DROP PROCEDURE sp_birth_registration_completeness');
        DB::unprepared('DROP PROCEDURE sp_certificates_issued_report');
    }
};
