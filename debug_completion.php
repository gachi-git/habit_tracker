<?php
require_once 'vendor/autoload.php';

use Carbon\Carbon;

// Test the diffInMonths calculation
$startOfMonth = Carbon::now()->startOfMonth();
$endOfMonth = Carbon::now()->endOfMonth();

echo "Start of month: " . $startOfMonth->format('Y-m-d H:i:s') . "\n";
echo "End of month: " . $endOfMonth->format('Y-m-d H:i:s') . "\n";
echo "diffInMonths: " . $startOfMonth->diffInMonths($endOfMonth) . "\n";
echo "diffInMonths + 1: " . ($startOfMonth->diffInMonths($endOfMonth) + 1) . "\n";

// For target_frequency = 10, what should be the targetCount?
$targetFreq = 10;
$months = $startOfMonth->diffInMonths($endOfMonth) + 1;
$targetCount = $months * $targetFreq;
echo "Target frequency: $targetFreq\n";
echo "Months: $months\n";
echo "Target count: $targetCount\n";

// If we have 5 completed records
$completedCount = 5;
$completionRate = $targetCount > 0 ? ($completedCount / $targetCount) * 100 : 0;
echo "Completed count: $completedCount\n";
echo "Completion rate: $completionRate\n";

echo "\n--- Testing the zero target frequency case ---\n";
$targetFreqZero = 0;
$targetCountZero = $months * $targetFreqZero;
$completionRateZero = $targetCountZero > 0 ? ($completedCount / $targetCountZero) * 100 : 0;
echo "Target frequency (zero): $targetFreqZero\n";
echo "Target count (zero): $targetCountZero\n";
echo "Completion rate (zero case): $completionRateZero\n";