<?php
// tzdebug.php — temporary diagnostics. Delete after use.

// Optional: clear OPcache if needed: tzdebug.php?reset=1
if (isset($_GET['reset']) && function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset\n\n";
}

header('Content-Type: text/plain');

// Basic environment info
echo "Host: " . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "ini date.timezone: " . (ini_get('date.timezone') ?: '(empty)') . "\n";
echo "date_default_timezone_get(): " . date_default_timezone_get() . "\n";
echo "now (date c): " . date('c') . "\n";
echo "now (gmdate c): " . gmdate('c') . "\n\n";

// Helper to diff with explicit IANA time zones (DST-safe)
function diff_minutes($depLocal, $depTz, $arrLocal, $arrTz) {
    $dep = new DateTimeImmutable($depLocal, new DateTimeZone($depTz));
    $arr = new DateTimeImmutable($arrLocal, new DateTimeZone($arrTz));
    return (int)(($arr->getTimestamp() - $dep->getTimestamp()) / 60);
}

function tz_offset($local, $tz) {
    $dt = new DateTimeImmutable($local, new DateTimeZone($tz));
    return $dt->getOffset() / 3600;
}

// Sample route used in your screenshots (NAN ↔ SYD, December)
$dep1 = '2025-12-02 18:00:00'; // NAN local
$arr1 = '2025-12-02 21:50:00'; // SYD local
$dep2 = '2025-12-08 06:00:00'; // SYD local
$arr2 = '2025-12-08 11:00:00'; // NAN local

echo "Offsets (Dec dates):\n";
echo "  Pacific/Fiji at $dep1: " . tz_offset($dep1, 'Pacific/Fiji') . "h\n";
echo "  Australia/Sydney at $arr1: " . tz_offset($arr1, 'Australia/Sydney') . "h\n\n";

$mins1 = diff_minutes($dep1, 'Pacific/Fiji', $arr1, 'Australia/Sydney');
$mins2 = diff_minutes($dep2, 'Australia/Sydney', $arr2, 'Pacific/Fiji');

echo "Computed durations (DST-aware):\n";
echo "  NAN 18:00 → SYD 21:50 = $mins1 min (" . floor($mins1/60) . "h " . ($mins1%60) . "m)\n";
echo "  SYD 06:00 → NAN 11:00 = $mins2 min (" . floor($mins2/60) . "h " . ($mins2%60) . "m)\n\n";

// Optional: accept ad‑hoc inputs to test any pair
if (isset($_GET['dep'], $_GET['deptz'], $_GET['arr'], $_GET['arrtz'])) {
    $mins = diff_minutes($_GET['dep'], $_GET['deptz'], $_GET['arr'], $_GET['arrtz']);
    echo "Custom diff: {$_GET['dep']} [{$_GET['deptz']}] → {$_GET['arr']} [{$_GET['arrtz']}] = "
       . $mins . " min (" . floor($mins/60) . "h " . ($mins%60) . "m)\n";
}

echo "\nTip: Both local and staging should show Fiji=+12, Sydney=+11 in December and durations 4h 50m and 4h.\n";