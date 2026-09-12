<?php
/**
 * Flipkart Master CI/CD Test Suite Runner
 * Executes all platform test suites sequentially:
 * 1. Authentication & JWT Tokens (tests/test_auth.php)
 * 2. Catalog, Search & Filtering (tests/test_catalog.php)
 * 3. Platform End-to-End Workflow (tests/test_e2e_backend.php)
 * 4. 20-Point Negative Security & Tampering (tests/test_security_negative.php)
 * 5. Property-Based & Invariant Fuzzing (tests/test_property_based.php)
 */

echo "=================================================================\n";
echo "   FLIPKART PLATFORM MASTER CI/CD TEST RUNNER (ALL SUITES)      \n";
echo "=================================================================\n\n";

$php = 'C:\\xampp\\php\\php.exe';
$suites = [
    'Auth & Token Suite'        => 'tests/test_auth.php',
    'Catalog & Facet Suite'     => 'tests/test_catalog.php',
    'Platform E2E Flow Suite'   => 'tests/test_e2e_backend.php',
    'Negative Security Suite'   => 'tests/test_security_negative.php',
    'Property & Fuzzing Suite'  => 'tests/test_property_based.php',
    'Super Admin & Gateway Suite' => 'tests/test_env_and_gateways.php'
];

$allPassed = true;
$results = [];

foreach ($suites as $name => $file) {
    echo "▶ Running {$name} ({$file})...\n";
    $startTime = microtime(true);
    
    $cmd = "{$php} " . escapeshellarg(__DIR__ . '/../' . $file);
    exec($cmd, $output, $exitCode);
    $elapsed = round(microtime(true) - $startTime, 2);

    $status = ($exitCode === 0) ? 'PASS' : 'FAIL';
    $results[$name] = ['status' => $status, 'time' => $elapsed, 'code' => $exitCode];
    
    if ($exitCode === 0) {
        echo "  ✔ {$name}: PASSED ({$elapsed}s)\n\n";
    } else {
        $allPassed = false;
        echo "  ✖ {$name}: FAILED (Exit Code {$exitCode}) ({$elapsed}s)\n";
        echo "  Last output: " . implode("\n", array_slice($output, -4)) . "\n\n";
    }
}

echo "=================================================================\n";
echo "   CI/CD EXECUTION SUMMARY MATRIX                                \n";
echo "=================================================================\n";
printf("%-30s | %-10s | %-10s\n", "Test Suite", "Status", "Duration");
echo str_repeat("-", 56) . "\n";
foreach ($results as $name => $r) {
    printf("%-30s | %-10s | %-8ss\n", $name, $r['status'], $r['time']);
}
echo str_repeat("-", 56) . "\n";

if ($allPassed) {
    echo "\n🎉 ALL 5 TEST SUITES PASSED (100% SUCCESS) - BUILD GREEN\n";
    exit(0);
} else {
    echo "\n❌ BUILD FAILED - ONE OR MORE TEST SUITES REPORTED ERRORS\n";
    exit(1);
}
