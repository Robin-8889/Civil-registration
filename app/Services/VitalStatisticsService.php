<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class VitalStatisticsService
{
    /**
     * Get birth statistics by region and year
     */
    public static function getBirthStatisticsByRegion($year = null)
    {
        $sql = "SELECT
                    ro.region,
                    TO_CHAR(br.registration_date, 'YYYY') as registration_year,
                    COUNT(*) as total_births,
                    SUM(CASE WHEN br.gender = 'M' THEN 1 ELSE 0 END) as male_births,
                    SUM(CASE WHEN br.gender = 'F' THEN 1 ELSE 0 END) as female_births,
                    br.status as record_status
                FROM birth_records br
                JOIN registration_offices ro ON br.registration_office_id = ro.id
                WHERE (1=1 " . ($year ? "AND TO_CHAR(br.registration_date, 'YYYY') = ?" : "") . ")
                GROUP BY ro.region, TO_CHAR(br.registration_date, 'YYYY'), br.status
                ORDER BY ro.region, registration_year DESC, record_status";

        $bindings = $year ? [$year] : [];
        return DB::select($sql, $bindings);
    }

    /**
     * Get death statistics grouped by age groups
     */
    public static function getDeathStatisticsByAge()
    {
        $sql = "SELECT
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
                ORDER BY age_group, total_deaths DESC";
        return DB::select($sql);
    }

    /**
     * Get marriage statistics by region and year
     */
    public static function getMarriageStatisticsByRegion($year = null)
    {
        $sql = "SELECT
                    ro.region,
                    TO_CHAR(mr.date_of_marriage, 'YYYY') as marriage_year,
                    COUNT(*) as total_marriages,
                    mr.status as record_status
                FROM marriage_records mr
                JOIN registration_offices ro ON mr.registration_office_id = ro.id
                WHERE (1=1 " . ($year ? "AND TO_CHAR(mr.date_of_marriage, 'YYYY') = ?" : "") . ")
                GROUP BY ro.region, TO_CHAR(mr.date_of_marriage, 'YYYY'), mr.status
                ORDER BY ro.region, marriage_year DESC";

        $bindings = $year ? [$year] : [];
        return DB::select($sql, $bindings);
    }

    /**
     * Get population demographics for a specific region or all regions
     */
    public static function getPopulationDemographics($region = null)
    {
        $sql = "SELECT
                    ro.region,
                    COUNT(c.id) as total_citizens,
                    SUM(CASE WHEN c.gender = 'M' THEN 1 ELSE 0 END) as male_count,
                    SUM(CASE WHEN c.gender = 'F' THEN 1 ELSE 0 END) as female_count,
                    ROUND(AVG(TRUNC((SYSDATE - c.date_of_birth) / 365.25)), 2) as average_age,
                    SUM(CASE WHEN c.is_married = 1 THEN 1 ELSE 0 END) as married_count,
                    SUM(CASE WHEN c.is_dead = 1 THEN 1 ELSE 0 END) as deceased_count
                FROM citizens c
                JOIN registration_offices ro ON c.registration_office_id = ro.id
                WHERE (1=1 " . ($region ? "AND ro.region = ?" : "") . ")
                GROUP BY ro.region
                ORDER BY ro.region";

        $bindings = $region ? [$region] : [];
        return DB::select($sql, $bindings);
    }

    /**
     * Get annual vital statistics summary for a specific year
     */
    public static function getAnnualVitalSummary($year)
    {
        $sql = "SELECT
                    ? as report_year,
                    (SELECT COUNT(*) FROM birth_records WHERE TO_CHAR(registration_date, 'YYYY') = TO_CHAR(?) AND status IN ('registered', 'pending')) as births,
                    (SELECT COUNT(*) FROM death_records WHERE TO_CHAR(registration_date, 'YYYY') = TO_CHAR(?) AND status IN ('registered', 'pending')) as deaths,
                    (SELECT COUNT(*) FROM marriage_records WHERE TO_CHAR(date_of_marriage, 'YYYY') = TO_CHAR(?) AND status IN ('registered', 'pending')) as marriages,
                    (SELECT COUNT(*) FROM birth_records WHERE TO_CHAR(registration_date, 'YYYY') = TO_CHAR(?) AND status = 'pending') as pending_births,
                    (SELECT COUNT(*) FROM birth_records WHERE TO_CHAR(registration_date, 'YYYY') = TO_CHAR(?) AND status = 'rejected') as rejected_births,
                    SYSDATE as report_generated_date
                FROM DUAL";

        return DB::select($sql, [$year, $year, $year, $year, $year, $year]);
    }

    /**
     * Get birth registration completeness report by region
     */
    public static function getBirthRegistrationCompleteness()
    {
        $sql = "SELECT
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
                ORDER BY completion_percentage DESC";

        return DB::select($sql);
    }

    /**
     * Get certificates issued report
     */
    public static function getCertificatesIssuedReport($year = null)
    {
        $sql = "SELECT
                    ro.region,
                    TO_CHAR(c.issue_date, 'YYYY') as certificate_year,
                    COUNT(*) as certificates_issued,
                    COUNT(DISTINCT c.record_type) as record_types
                FROM certificates c
                JOIN registration_offices ro ON c.registration_office_id = ro.id
                WHERE (1=1 " . ($year ? "AND TO_CHAR(c.issue_date, 'YYYY') = ?" : "") . ")
                GROUP BY ro.region, TO_CHAR(c.issue_date, 'YYYY')
                ORDER BY ro.region, certificate_year DESC";

        $bindings = $year ? [$year] : [];
        return DB::select($sql, $bindings);
    }
}
