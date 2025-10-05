<?php
require_once 'vendor/autoload.php';

use Carbon\Carbon;

// More detailed test
$now = Carbon::parse('2025-10-05'); // Fix the date for consistent testing
$startOfMonth = $now->copy()->startOfMonth();
$endOfMonth = $now->copy()->endOfMonth();

echo "Now: " . $now->format('Y-m-d H:i:s') . "\n";
echo "Start of month: " . $startOfMonth->format('Y-m-d H:i:s') . "\n";
echo "End of month: " . $endOfMonth->format('Y-m-d H:i:s') . "\n";

echo "\n--- Different diffInMonths calculations ---\n";
echo "startOfMonth->diffInMonths(endOfMonth): " . $startOfMonth->diffInMonths($endOfMonth) . "\n";
echo "(int) startOfMonth->diffInMonths(endOfMonth): " . (int)$startOfMonth->diffInMonths($endOfMonth) . "\n";
echo "startOfMonth->diffInMonths(endOfMonth) + 1: " . ($startOfMonth->diffInMonths($endOfMonth) + 1) . "\n";

// Let's test cross-month periods
echo "\n--- Cross-month testing ---\n";
$start = Carbon::parse('2025-09-15');
$end = Carbon::parse('2025-11-15');
echo "Start: " . $start->format('Y-m-d') . "\n";
echo "End: " . $end->format('Y-m-d') . "\n";
echo "diffInMonths: " . $start->diffInMonths($end) . "\n";
echo "Expected months spanning: 3 (Sep, Oct, Nov)\n";

// The correct approach for monthly calculation
echo "\n--- Better approach for monthly calculation ---\n";
$startMonth = $startOfMonth->copy()->startOfMonth();
$endMonth = $endOfMonth->copy()->startOfMonth();
$monthsDiff = $startMonth->diffInMonths($endMonth) + 1;
echo "Better months calculation: $monthsDiff\n";