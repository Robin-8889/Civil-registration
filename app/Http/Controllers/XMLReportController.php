<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BirthRecord;
use App\Models\MarriageRecord;
use App\Models\DeathRecord;
use App\Models\RegistrationOffice;
use App\Services\VitalStatisticsService;
use Illuminate\Support\Facades\DB;

class XMLReportController extends Controller
{
    /**
     * Generate citizen report based on birth record
     */
    public function citizenReport($birthRecordId)
    {
        $birth = BirthRecord::with('office')->findOrFail($birthRecordId);

        // Get related records
        $marriages = MarriageRecord::where('groom_id', $birthRecordId)
            ->orWhere('bride_id', $birthRecordId)
            ->with('office')
            ->get();

        $death = DeathRecord::where('deceased_birth_id', $birthRecordId)
            ->with('office')
            ->first();

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('CitizenReport');
            $xml->writeAttribute('generated', now()->toDateTimeString());

            // Birth Information
            $xml->startElement('BirthInformation');
                $xml->writeElement('CertificateNo', $birth->birth_certificate_no);
                $xml->writeElement('FirstName', $birth->child_first_name);
                $xml->writeElement('MiddleName', $birth->child_middle_name ?? '');
                $xml->writeElement('LastName', $birth->child_last_name);
                $xml->writeElement('Gender', $birth->gender);
                $xml->writeElement('DateOfBirth', $birth->date_of_birth);
                $xml->writeElement('PlaceOfBirth', $birth->place_of_birth);
                $xml->writeElement('Nationality', $birth->nationality ?? 'Tanzanian');
                $xml->writeElement('FatherName', $birth->father_name ?? '');
                $xml->writeElement('MotherName', $birth->mother_name ?? '');
                $xml->writeElement('RegistrationOffice', $birth->office->office_name);
                $xml->writeElement('Region', $birth->office->region);
                $xml->writeElement('RegistrationDate', $birth->registration_date);
            $xml->endElement(); // BirthInformation

            // Marriage Information
            if ($marriages->count() > 0) {
                $xml->startElement('MarriageRecords');
                    foreach ($marriages as $marriage) {
                        $xml->startElement('Marriage');
                            $xml->writeElement('CertificateNo', $marriage->marriage_certificate_no);
                            $xml->writeElement('DateOfMarriage', $marriage->date_of_marriage);
                            $xml->writeElement('PlaceOfMarriage', $marriage->place_of_marriage);
                            $xml->writeElement('GroomID', $marriage->groom_id);
                            $xml->writeElement('BrideID', $marriage->bride_id);
                            $xml->writeElement('RegistrationOffice', $marriage->office->office_name);
                            $xml->writeElement('Status', $marriage->status);
                        $xml->endElement(); // Marriage
                    }
                $xml->endElement(); // MarriageRecords
            }

            // Death Information
            if ($death) {
                $xml->startElement('DeathInformation');
                    $xml->writeElement('CertificateNo', $death->death_certificate_no);
                    $xml->writeElement('DateOfDeath', $death->date_of_death);
                    $xml->writeElement('PlaceOfDeath', $death->place_of_death);
                    $xml->writeElement('CauseOfDeath', $death->cause_of_death ?? '');
                    $xml->writeElement('RegistrationOffice', $death->office->office_name);
                $xml->endElement(); // DeathInformation
            }

        $xml->endElement(); // CitizenReport
        $xml->endDocument();

        return response($xml->outputMemory(), 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="citizen_report_' . $birthRecordId . '.xml"');
    }

    /**
     * Generate regional statistics report
     */
    public function regionalStatistics($region, $year = null)
    {
        $year = $year ?? date('Y');

        // Validate region exists
        $regionExists = RegistrationOffice::where('region', $region)->exists();
        if (!$regionExists) {
            return response()->json([
                'error' => 'Region not found',
                'region' => $region
            ], 404);
        }

        // Get statistics
        $birthStats = BirthRecord::whereHas('office', fn($q) => $q->where('region', $region))
            ->whereYear('registration_date', $year)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $marriageStats = MarriageRecord::whereHas('office', fn($q) => $q->where('region', $region))
            ->whereYear('registration_date', $year)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $deathStats = DeathRecord::whereHas('office', fn($q) => $q->where('region', $region))
            ->whereYear('registration_date', $year)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Check if any data exists
        if ($birthStats->isEmpty() && $marriageStats->isEmpty() && $deathStats->isEmpty()) {
            return response()->json([
                'error' => 'No records found',
                'region' => $region,
                'year' => $year
            ], 404);
        }

        // Monthly breakdown - using Laravel query builder with proper Oracle syntax
        $monthlyBirths = BirthRecord::whereHas('office', fn($q) => $q->where('region', $region))
            ->whereYear('registration_date', $year)
            ->selectRaw('EXTRACT(MONTH FROM registration_date) as month, COUNT(*) as count')
            ->groupByRaw('EXTRACT(MONTH FROM registration_date)')
            ->orderByRaw('EXTRACT(MONTH FROM registration_date)')
            ->get();

        $offices = RegistrationOffice::where('region', $region)->get();

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('RegionalStatistics');
            $xml->writeAttribute('region', $region);
            $xml->writeAttribute('year', $year);
            $xml->writeAttribute('generated', now()->toDateTimeString());

            // Summary
            $xml->startElement('Summary');
                $xml->writeElement('TotalBirths', $birthStats->sum('count'));
                $xml->writeElement('TotalMarriages', $marriageStats->sum('count'));
                $xml->writeElement('TotalDeaths', $deathStats->sum('count'));
                $xml->writeElement('TotalOffices', $offices->count());
            $xml->endElement(); // Summary

            // Birth Statistics
            $xml->startElement('BirthStatistics');
                foreach ($birthStats as $stat) {
                    $xml->startElement('Status');
                        $xml->writeAttribute('type', $stat->status);
                        $xml->writeAttribute('count', $stat->count);
                    $xml->endElement();
                }
            $xml->endElement(); // BirthStatistics

            // Marriage Statistics
            $xml->startElement('MarriageStatistics');
                foreach ($marriageStats as $stat) {
                    $xml->startElement('Status');
                        $xml->writeAttribute('type', $stat->status);
                        $xml->writeAttribute('count', $stat->count);
                    $xml->endElement();
                }
            $xml->endElement(); // MarriageStatistics

            // Death Statistics
            $xml->startElement('DeathStatistics');
                foreach ($deathStats as $stat) {
                    $xml->startElement('Status');
                        $xml->writeAttribute('type', $stat->status);
                        $xml->writeAttribute('count', $stat->count);
                    $xml->endElement();
                }
            $xml->endElement(); // DeathStatistics

            // Monthly Trends
            $xml->startElement('MonthlyTrends');
                foreach ($monthlyBirths as $month) {
                    $xml->startElement('Month');
                        $xml->writeAttribute('number', $month->month);
                        $xml->writeAttribute('births', $month->count);
                    $xml->endElement();
                }
            $xml->endElement(); // MonthlyTrends

            // Registration Offices
            $xml->startElement('RegistrationOffices');
                foreach ($offices as $office) {
                    $xml->startElement('Office');
                        $xml->writeElement('ID', $office->id);
                        $xml->writeElement('Name', $office->office_name);
                        $xml->writeElement('District', $office->district);
                        $xml->writeElement('ContactEmail', $office->contact_email);
                        $xml->writeElement('ContactPhone', $office->contact_phone);
                    $xml->endElement(); // Office
                }
            $xml->endElement(); // RegistrationOffices

        $xml->endElement(); // RegionalStatistics
        $xml->endDocument();

        return response($xml->outputMemory(), 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="regional_statistics_' . $region . '_' . $year . '.xml"');
    }

    /**
     * Generate monthly report for all records
     */
    public function monthlyReport($year, $month)
    {
        // Validate month
        if ($month < 1 || $month > 12) {
            return response()->json([
                'error' => 'Invalid month. Must be between 1 and 12',
                'month' => $month
            ], 400);
        }

        // Check if any records exist for the month
        $births = BirthRecord::with('office')
            ->whereYear('registration_date', $year)
            ->whereMonth('registration_date', $month)
            ->get();

        $marriages = MarriageRecord::with('office')
            ->whereYear('registration_date', $year)
            ->whereMonth('registration_date', $month)
            ->get();

        $deaths = DeathRecord::with('office')
            ->whereYear('registration_date', $year)
            ->whereMonth('registration_date', $month)
            ->get();

        if ($births->isEmpty() && $marriages->isEmpty() && $deaths->isEmpty()) {
            return response()->json([
                'error' => 'No records found for the specified month',
                'year' => $year,
                'month' => $month
            ], 404);
        }

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('MonthlyReport');
            $xml->writeAttribute('year', $year);
            $xml->writeAttribute('month', $month);
            $xml->writeAttribute('generated', now()->toDateTimeString());

            $xml->startElement('Summary');
                $xml->writeElement('TotalBirths', $births->count());
                $xml->writeElement('TotalMarriages', $marriages->count());
                $xml->writeElement('TotalDeaths', $deaths->count());
            $xml->endElement(); // Summary

            // Birth Records
            $xml->startElement('BirthRecords');
                foreach ($births as $birth) {
                    $xml->startElement('Birth');
                        $xml->writeElement('CertificateNo', $birth->birth_certificate_no);
                        $xml->writeElement('ChildName', $birth->child_first_name . ' ' . $birth->child_last_name);
                        $xml->writeElement('Gender', $birth->gender);
                        $xml->writeElement('DateOfBirth', $birth->date_of_birth);
                        $xml->writeElement('Office', $birth->office->office_name);
                        $xml->writeElement('Region', $birth->office->region);
                        $xml->writeElement('Status', $birth->status);
                    $xml->endElement();
                }
            $xml->endElement(); // BirthRecords

            // Marriage Records
            $xml->startElement('MarriageRecords');
                foreach ($marriages as $marriage) {
                    $xml->startElement('Marriage');
                        $xml->writeElement('CertificateNo', $marriage->marriage_certificate_no);
                        $xml->writeElement('DateOfMarriage', $marriage->date_of_marriage);
                        $xml->writeElement('Office', $marriage->office->office_name);
                        $xml->writeElement('Region', $marriage->office->region);
                        $xml->writeElement('Status', $marriage->status);
                    $xml->endElement();
                }
            $xml->endElement(); // MarriageRecords

            // Death Records
            $xml->startElement('DeathRecords');
                foreach ($deaths as $death) {
                    $xml->startElement('Death');
                        $xml->writeElement('CertificateNo', $death->death_certificate_no);
                        $xml->writeElement('DateOfDeath', $death->date_of_death);
                        $xml->writeElement('Office', $death->office->office_name);
                        $xml->writeElement('Region', $death->office->region);
                        $xml->writeElement('Status', $death->status);
                    $xml->endElement();
                }
            $xml->endElement(); // DeathRecords

        $xml->endElement(); // MonthlyReport
        $xml->endDocument();

        return response($xml->outputMemory(), 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="monthly_report_' . $year . '_' . $month . '.xml"');
    }

    /**
     * Export vital statistics as XML for government submission
     */
    public function vitalStatisticsExport(Request $request)
    {
        $year = $request->query('year', now()->year);
        $region = $request->query('region');

        // Check if any records exist for the year
        $hasRecords = BirthRecord::whereYear('registration_date', $year)->exists() ||
                      DeathRecord::whereYear('registration_date', $year)->exists() ||
                      MarriageRecord::whereYear('registration_date', $year)->exists();

        if (!$hasRecords) {
            return response()->json([
                'error' => 'No records found for the specified year',
                'year' => $year
            ], 404);
        }

        // Get statistics using stored procedures
        $birthStats = VitalStatisticsService::getBirthStatisticsByRegion($year);
        $deathStats = VitalStatisticsService::getDeathStatisticsByAge();
        $marriageStats = VitalStatisticsService::getMarriageStatisticsByRegion($year);
        $demographics = VitalStatisticsService::getPopulationDemographics($region);
        $completeness = VitalStatisticsService::getBirthRegistrationCompleteness();
        $annualSummary = VitalStatisticsService::getAnnualVitalSummary($year);

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('VitalStatisticsReport');
            $xml->writeAttribute('year', $year);
            $xml->writeAttribute('generated', now()->toDateTimeString());
            $xml->writeAttribute('nation', 'Tanzania');

            // Annual Summary
            if (!empty($annualSummary)) {
                $summary = $annualSummary[0];
                $xml->startElement('AnnualSummary');
                    $xml->writeElement('Year', $summary->report_year ?? $year);
                    $xml->writeElement('TotalBirths', $summary->births ?? 0);
                    $xml->writeElement('TotalDeaths', $summary->deaths ?? 0);
                    $xml->writeElement('TotalMarriages', $summary->marriages ?? 0);
                    $xml->writeElement('PendingBirths', $summary->pending_births ?? 0);
                    $xml->writeElement('RejectedBirths', $summary->rejected_births ?? 0);
                $xml->endElement(); // AnnualSummary
            }

            // Births by Region
            $xml->startElement('BirthStatisticsByRegion');
                foreach ($birthStats as $stat) {
                    $xml->startElement('Region');
                        $xml->writeElement('Name', $stat->REGION ?? $stat->region ?? 'Unknown');
                        $xml->writeElement('Year', $stat->REGISTRATION_YEAR ?? $stat->registration_year ?? $year);
                        $xml->writeElement('TotalBirths', $stat->TOTAL_BIRTHS ?? $stat->total_births ?? 0);
                        $xml->writeElement('MaleBirths', $stat->MALE_BIRTHS ?? $stat->male_births ?? 0);
                        $xml->writeElement('FemaleBirths', $stat->FEMALE_BIRTHS ?? $stat->female_births ?? 0);
                        $xml->writeElement('Status', $stat->RECORD_STATUS ?? $stat->record_status ?? '');
                    $xml->endElement();
                }
            $xml->endElement(); // BirthStatisticsByRegion

            // Deaths by Age Group
            $xml->startElement('DeathStatisticsByAgeGroup');
                foreach ($deathStats as $stat) {
                    $xml->startElement('AgeGroup');
                        $xml->writeElement('Range', $stat->AGE_GROUP ?? $stat->age_group ?? 'Unknown');
                        $xml->writeElement('TotalDeaths', $stat->TOTAL_DEATHS ?? $stat->total_deaths ?? 0);
                        $xml->writeElement('Region', $stat->REGION ?? $stat->region ?? 'Unknown');
                        $xml->writeElement('LeadingCause', $stat->LEADING_DEATH_CAUSE ?? $stat->leading_death_cause ?? 'Not specified');
                    $xml->endElement();
                }
            $xml->endElement(); // DeathStatisticsByAgeGroup

            // Marriages by Region
            $xml->startElement('MarriageStatisticsByRegion');
                foreach ($marriageStats as $stat) {
                    $xml->startElement('Region');
                        $xml->writeElement('Name', $stat->REGION ?? $stat->region ?? 'Unknown');
                        $xml->writeElement('Year', $stat->MARRIAGE_YEAR ?? $stat->marriage_year ?? $year);
                        $xml->writeElement('TotalMarriages', $stat->TOTAL_MARRIAGES ?? $stat->total_marriages ?? 0);
                        $xml->writeElement('Status', $stat->RECORD_STATUS ?? $stat->record_status ?? '');
                        $xml->writeElement('Month', $stat->MONTH ?? $stat->month_registered ?? '');
                    $xml->endElement();
                }
            $xml->endElement(); // MarriageStatisticsByRegion

            // Population Demographics
            $xml->startElement('PopulationDemographics');
                foreach ($demographics as $stat) {
                    $xml->startElement('Region');
                        $xml->writeElement('Name', $stat->REGION ?? $stat->region ?? 'Unknown');
                        $xml->writeElement('TotalCitizens', $stat->TOTAL_CITIZENS ?? $stat->total_citizens ?? 0);
                        $xml->writeElement('MaleCount', $stat->MALE_COUNT ?? $stat->male_count ?? 0);
                        $xml->writeElement('FemaleCount', $stat->FEMALE_COUNT ?? $stat->female_count ?? 0);
                        $xml->writeElement('AverageAge', $stat->AVERAGE_AGE ?? $stat->average_age ?? 0);
                        $xml->writeElement('MarriedCount', $stat->MARRIED_COUNT ?? $stat->married_count ?? 0);
                        $xml->writeElement('DeceasedCount', $stat->DECEASED_COUNT ?? $stat->deceased_count ?? 0);
                    $xml->endElement();
                }
            $xml->endElement(); // PopulationDemographics

            // Birth Registration Completeness
            $xml->startElement('RegistrationCompleteness');
                foreach ($completeness as $stat) {
                    $xml->startElement('Region');
                        $xml->writeElement('Name', $stat->REGION ?? $stat->region ?? 'Unknown');
                        $xml->writeElement('TotalRegistrations', $stat->TOTAL_REGISTRATIONS ?? $stat->total_registrations ?? 0);
                        $xml->writeElement('Completed', $stat->COMPLETED ?? $stat->completed ?? 0);
                        $xml->writeElement('Pending', $stat->PENDING ?? $stat->pending ?? 0);
                        $xml->writeElement('Rejected', $stat->REJECTED ?? $stat->rejected ?? 0);
                        $xml->writeElement('CompletionPercentage', $stat->COMPLETION_PERCENTAGE ?? $stat->completion_percentage ?? 0);
                    $xml->endElement();
                }
            $xml->endElement(); // RegistrationCompleteness

        $xml->endElement(); // VitalStatisticsReport
        $xml->endDocument();

        return response($xml->outputMemory(), 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="vital_statistics_' . $year . '.xml"');
    }
}
