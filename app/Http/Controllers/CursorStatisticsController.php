<?php

namespace App\Http\Controllers;

use App\Services\VitalStatisticsService;
use Illuminate\Http\Request;

/**
 * Cursor-Based Statistics Controller
 *
 * This controller uses stored procedures with cursors instead of direct SQL.
 * It demonstrates the cursor implementation without affecting existing functionality.
 *
 * The existing VitalStatisticsController continues to use direct SQL queries.
 * This controller provides an alternative approach using Oracle stored procedures.
 *
 * Routes can be added separately for testing:
 * Route::prefix('api/statistics/cursor')->group(function () {
 *     Route::get('/births/region', [CursorStatisticsController::class, 'birthStatisticsByRegion']);
 *     Route::get('/deaths/age', [CursorStatisticsController::class, 'deathStatisticsByAge']);
 *     ... etc
 * });
 */
class CursorStatisticsController extends Controller
{
    /**
     * Get birth statistics by region and year using CURSOR
     * Uses: sp_birth_statistics_by_region stored procedure
     */
    public function birthStatisticsByRegion(Request $request)
    {
        $year = $request->query('year');

        return response()->json([
            'data' => VitalStatisticsService::getBirthStatisticsByRegionCursor($year),
            'type' => 'birth_statistics',
            'year' => $year,
            'implementation' => 'cursor_based'
        ]);
    }

    /**
     * Get death statistics by age group using CURSOR
     * Uses: sp_death_statistics_by_age stored procedure
     */
    public function deathStatisticsByAge()
    {
        return response()->json([
            'data' => VitalStatisticsService::getDeathStatisticsByAgeCursor(),
            'type' => 'death_statistics_by_age',
            'implementation' => 'cursor_based'
        ]);
    }

    /**
     * Get marriage statistics by region and year using CURSOR
     * Uses: sp_marriage_statistics_by_region stored procedure
     */
    public function marriageStatisticsByRegion(Request $request)
    {
        $year = $request->query('year');

        return response()->json([
            'data' => VitalStatisticsService::getMarriageStatisticsByRegionCursor($year),
            'type' => 'marriage_statistics',
            'year' => $year,
            'implementation' => 'cursor_based'
        ]);
    }

    /**
     * Get population demographics using CURSOR
     * Uses: sp_population_demographics stored procedure
     */
    public function populationDemographics(Request $request)
    {
        $region = $request->query('region');

        return response()->json([
            'data' => VitalStatisticsService::getPopulationDemographicsCursor($region),
            'type' => 'population_demographics',
            'region' => $region,
            'implementation' => 'cursor_based'
        ]);
    }

    /**
     * Get annual vital statistics summary using CURSOR
     * Uses: sp_annual_vital_summary stored procedure
     */
    public function annualVitalSummary(Request $request)
    {
        $year = $request->query('year', now()->year);

        return response()->json([
            'data' => VitalStatisticsService::getAnnualVitalSummaryCursor($year),
            'type' => 'annual_vital_summary',
            'year' => $year,
            'implementation' => 'cursor_based'
        ]);
    }

    /**
     * Get birth registration completeness report using CURSOR
     * Uses: sp_birth_registration_completeness stored procedure
     */
    public function birthRegistrationCompleteness()
    {
        return response()->json([
            'data' => VitalStatisticsService::getBirthRegistrationCompletenessCursor(),
            'type' => 'birth_registration_completeness',
            'implementation' => 'cursor_based'
        ]);
    }

    /**
     * Get certificates issued report using CURSOR
     * Uses: sp_certificates_issued_report stored procedure
     */
    public function certificatesIssuedReport(Request $request)
    {
        $year = $request->query('year');

        return response()->json([
            'data' => VitalStatisticsService::getCertificatesIssuedReportCursor($year),
            'type' => 'certificates_issued',
            'year' => $year,
            'implementation' => 'cursor_based'
        ]);
    }

    /**
     * Get comprehensive vital statistics dashboard data using CURSOR
     * Uses all cursor-based stored procedures
     */
    public function dashboard(Request $request)
    {
        $year = $request->query('year', now()->year);
        $region = $request->query('region');

        return response()->json([
            'annual_summary' => VitalStatisticsService::getAnnualVitalSummaryCursor($year),
            'birth_statistics' => VitalStatisticsService::getBirthStatisticsByRegionCursor($year),
            'death_statistics' => VitalStatisticsService::getDeathStatisticsByAgeCursor(),
            'marriage_statistics' => VitalStatisticsService::getMarriageStatisticsByRegionCursor($year),
            'population_demographics' => VitalStatisticsService::getPopulationDemographicsCursor($region),
            'registration_completeness' => VitalStatisticsService::getBirthRegistrationCompletenessCursor(),
            'certificates_issued' => VitalStatisticsService::getCertificatesIssuedReportCursor($year),
            'generated_at' => now(),
            'implementation' => 'cursor_based'
        ]);
    }
}
