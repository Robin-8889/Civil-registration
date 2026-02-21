<?php

namespace App\Http\Controllers;

use App\Services\VitalStatisticsService;
use Illuminate\Http\Request;

class VitalStatisticsController extends Controller
{
    /**
     * Get birth statistics by region and year
     */
    public function birthStatisticsByRegion(Request $request)
    {
        $year = $request->query('year');

        return response()->json([
            'data' => VitalStatisticsService::getBirthStatisticsByRegion($year),
            'type' => 'birth_statistics',
            'year' => $year
        ]);
    }

    /**
     * Get death statistics by age group
     */
    public function deathStatisticsByAge()
    {
        return response()->json([
            'data' => VitalStatisticsService::getDeathStatisticsByAge(),
            'type' => 'death_statistics_by_age'
        ]);
    }

    /**
     * Get marriage statistics by region and year
     */
    public function marriageStatisticsByRegion(Request $request)
    {
        $year = $request->query('year');

        return response()->json([
            'data' => VitalStatisticsService::getMarriageStatisticsByRegion($year),
            'type' => 'marriage_statistics',
            'year' => $year
        ]);
    }

    /**
     * Get population demographics
     */
    public function populationDemographics(Request $request)
    {
        $region = $request->query('region');

        return response()->json([
            'data' => VitalStatisticsService::getPopulationDemographics($region),
            'type' => 'population_demographics',
            'region' => $region
        ]);
    }

    /**
     * Get annual vital statistics summary for a specific year
     */
    public function annualVitalSummary(Request $request)
    {
        $year = $request->query('year', now()->year);

        return response()->json([
            'data' => VitalStatisticsService::getAnnualVitalSummary($year),
            'type' => 'annual_vital_summary',
            'year' => $year
        ]);
    }

    /**
     * Get birth registration completeness report
     */
    public function birthRegistrationCompleteness()
    {
        return response()->json([
            'data' => VitalStatisticsService::getBirthRegistrationCompleteness(),
            'type' => 'birth_registration_completeness'
        ]);
    }

    /**
     * Get certificates issued report
     */
    public function certificatesIssuedReport(Request $request)
    {
        $year = $request->query('year');

        return response()->json([
            'data' => VitalStatisticsService::getCertificatesIssuedReport($year),
            'type' => 'certificates_issued',
            'year' => $year
        ]);
    }

    /**
     * Get comprehensive vital statistics dashboard data
     */
    public function dashboard(Request $request)
    {
        $year = $request->query('year', now()->year);
        $region = $request->query('region');

        return response()->json([
            'annual_summary' => VitalStatisticsService::getAnnualVitalSummary($year),
            'birth_statistics' => VitalStatisticsService::getBirthStatisticsByRegion($year),
            'death_statistics' => VitalStatisticsService::getDeathStatisticsByAge(),
            'marriage_statistics' => VitalStatisticsService::getMarriageStatisticsByRegion($year),
            'population_demographics' => VitalStatisticsService::getPopulationDemographics($region),
            'registration_completeness' => VitalStatisticsService::getBirthRegistrationCompleteness(),
            'certificates_issued' => VitalStatisticsService::getCertificatesIssuedReport($year),
            'generated_at' => now()
        ]);
    }
}
