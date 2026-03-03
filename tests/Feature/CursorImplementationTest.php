<?php

/**
 * CURSOR IMPLEMENTATION TEST SUITE
 *
 * This file demonstrates how to test the cursor implementation.
 * Run from Laravel Tinker or as a PHP script.
 *
 * Usage:
 * php artisan tinker
 * > include 'tests/CursorImplementationTest.php'
 * > testCursorMethods()
 */

namespace Tests\Feature;

use App\Services\VitalStatisticsService;
use PHPUnit\Framework\TestCase;

class CursorImplementationTest extends TestCase
{
    /**
     * Test that cursor method for births returns same format as direct SQL
     */
    public function testBirthStatisticsByRegionCursorReturnsValidData()
    {
        // Get results from cursor method
        $cursorResults = VitalStatisticsService::getBirthStatisticsByRegionCursor(2026);

        // Get results from direct SQL method
        $directResults = VitalStatisticsService::getBirthStatisticsByRegion(2026);

        // Both should be arrays
        $this->assertIsArray($cursorResults);
        $this->assertIsArray($directResults);

        // Both should have same structure
        if (count($cursorResults) > 0 && count($directResults) > 0) {
            $this->assertEquals(
                count((array) $cursorResults[0]),
                count((array) $directResults[0]),
                'Cursor and direct SQL should return same number of fields'
            );
        }

        echo "✅ testBirthStatisticsByRegionCursor PASSED\n";
    }

    /**
     * Test death statistics cursor method
     */
    public function testDeathStatisticsByAgeCursorReturnsValidData()
    {
        $cursorResults = VitalStatisticsService::getDeathStatisticsByAgeCursor();

        $this->assertIsArray($cursorResults);
        echo "✅ testDeathStatisticsByAgeCursor PASSED\n";
    }

    /**
     * Test marriage statistics cursor method
     */
    public function testMarriageStatisticsByRegionCursorReturnsValidData()
    {
        $cursorResults = VitalStatisticsService::getMarriageStatisticsByRegionCursor(2026);

        $this->assertIsArray($cursorResults);
        echo "✅ testMarriageStatisticsByRegionCursor PASSED\n";
    }

    /**
     * Test population demographics cursor method
     */
    public function testPopulationDemographicsCursorReturnsValidData()
    {
        $cursorResults = VitalStatisticsService::getPopulationDemographicsCursor();

        $this->assertIsArray($cursorResults);
        echo "✅ testPopulationDemographicsCursor PASSED\n";
    }

    /**
     * Test annual vital summary cursor method
     */
    public function testAnnualVitalSummaryCursorReturnsValidData()
    {
        $cursorResults = VitalStatisticsService::getAnnualVitalSummaryCursor(2026);

        $this->assertIsArray($cursorResults);
        echo "✅ testAnnualVitalSummaryCursor PASSED\n";
    }

    /**
     * Test birth registration completeness cursor method
     */
    public function testBirthRegistrationCompletenessCursorReturnsValidData()
    {
        $cursorResults = VitalStatisticsService::getBirthRegistrationCompletenessCursor();

        $this->assertIsArray($cursorResults);
        echo "✅ testBirthRegistrationCompletenessCursor PASSED\n";
    }

    /**
     * Test certificates issued cursor method
     */
    public function testCertificatesIssuedReportCursorReturnsValidData()
    {
        $cursorResults = VitalStatisticsService::getCertificatesIssuedReportCursor(2026);

        $this->assertIsArray($cursorResults);
        echo "✅ testCertificatesIssuedReportCursor PASSED\n";
    }

    /**
     * Test that cursor methods handle null parameters correctly
     */
    public function testCursorMethodsHandleNullParameters()
    {
        // These should not throw errors
        $birth = VitalStatisticsService::getBirthStatisticsByRegionCursor(null);
        $marriage = VitalStatisticsService::getMarriageStatisticsByRegionCursor(null);
        $demo = VitalStatisticsService::getPopulationDemographicsCursor(null);
        $cert = VitalStatisticsService::getCertificatesIssuedReportCursor(null);

        $this->assertIsArray($birth);
        $this->assertIsArray($marriage);
        $this->assertIsArray($demo);
        $this->assertIsArray($cert);

        echo "✅ testCursorMethodsHandleNullParameters PASSED\n";
    }

    /**
     * Test that both implementations return same data structure
     */
    public function testBothImplementationsMatchDataStructure()
    {
        $cursor = VitalStatisticsService::getBirthStatisticsByRegionCursor(2026);
        $direct = VitalStatisticsService::getBirthStatisticsByRegion(2026);

        // If we have results from both
        if (count($cursor) > 0 && count($direct) > 0) {
            $cursorKeys = array_keys((array) $cursor[0]);
            $directKeys = array_keys((array) $direct[0]);

            // Same keys (case-insensitive comparison)
            $cursorKeysLower = array_map('strtolower', $cursorKeys);
            $directKeysLower = array_map('strtolower', $directKeys);

            sort($cursorKeysLower);
            sort($directKeysLower);

            $this->assertEquals($cursorKeysLower, $directKeysLower);
            echo "✅ testBothImplementationsMatchDataStructure PASSED\n";
        }
    }
}

/**
 * Quick test function for Tinker
 * Usage in Tinker: testCursorMethods()
 */
if (!function_exists('testCursorMethods')) {
    function testCursorMethods() {
        echo "\n🔄 CURSOR IMPLEMENTATION TEST SUITE\n";
        echo "====================================\n\n";

        try {
            // Test 1: Birth Statistics
            echo "Test 1: Birth Statistics by Region (Cursor)\n";
            $births = VitalStatisticsService::getBirthStatisticsByRegionCursor(2026);
            echo "  ✅ Returned: " . count($births) . " records\n";
            if (count($births) > 0) {
                echo "  ✅ First record keys: " . implode(', ', array_keys((array) $births[0])) . "\n";
            }
            echo "\n";

            // Test 2: Death Statistics
            echo "Test 2: Death Statistics by Age (Cursor)\n";
            $deaths = VitalStatisticsService::getDeathStatisticsByAgeCursor();
            echo "  ✅ Returned: " . count($deaths) . " records\n";
            echo "\n";

            // Test 3: Marriage Statistics
            echo "Test 3: Marriage Statistics by Region (Cursor)\n";
            $marriages = VitalStatisticsService::getMarriageStatisticsByRegionCursor(2026);
            echo "  ✅ Returned: " . count($marriages) . " records\n";
            echo "\n";

            // Test 4: Population Demographics
            echo "Test 4: Population Demographics (Cursor)\n";
            $demographic = VitalStatisticsService::getPopulationDemographicsCursor();
            echo "  ✅ Returned: " . count($demographic) . " records\n";
            echo "\n";

            // Test 5: Annual Vital Summary
            echo "Test 5: Annual Vital Summary (Cursor)\n";
            $summary = VitalStatisticsService::getAnnualVitalSummaryCursor(2026);
            echo "  ✅ Returned: " . count($summary) . " records\n";
            if (count($summary) > 0) {
                echo json_encode($summary[0], JSON_PRETTY_PRINT);
            }
            echo "\n";

            // Test 6: Birth Completeness
            echo "Test 6: Birth Registration Completeness (Cursor)\n";
            $completeness = VitalStatisticsService::getBirthRegistrationCompletenessCursor();
            echo "  ✅ Returned: " . count($completeness) . " records\n";
            echo "\n";

            // Test 7: Certificates Issued
            echo "Test 7: Certificates Issued (Cursor)\n";
            $certificates = VitalStatisticsService::getCertificatesIssuedReportCursor(2026);
            echo "  ✅ Returned: " . count($certificates) . " records\n";
            echo "\n";

            // Comparison Test
            echo "Test 8: Comparing Cursor vs Direct SQL\n";
            $cursorBirths = VitalStatisticsService::getBirthStatisticsByRegionCursor(2026);
            $directBirths = VitalStatisticsService::getBirthStatisticsByRegion(2026);
            echo "  Cursor method returned: " . count($cursorBirths) . " records\n";
            echo "  Direct SQL returned: " . count($directBirths) . " records\n";
            if (count($cursorBirths) === count($directBirths)) {
                echo "  ✅ MATCH! Both methods return same number of records\n";
            } else {
                echo "  ⚠️  Different record counts\n";
            }
            echo "\n";

            echo "====================================\n";
            echo "🎉 ALL CURSOR TESTS COMPLETED!\n";
            echo "====================================\n\n";

        } catch (\Exception $e) {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
            echo "Stack trace:\n";
            echo $e->getTraceAsString() . "\n";
        }
    }
}
