<?php

declare(strict_types=1);

$opts = getopt('h', ['ccn:', 'npath:', 'loc:', 'wmc:', 'help'], $optind);
$args = array_slice($argv, $optind);

if (isset($opts['h']) || isset($opts['help'])) {
    echo <<<'USAGE'
    Usage: php bin/pdepend-check.php [options] <summary-xml>

    Options:
      --ccn=N     Max cyclomatic complexity per method  (default: 8)
      --npath=N   Max NPath complexity per method       (default: 100)
      --loc=N     Max lines of code per method          (default: 40)
      --wmc=N     Max weighted methods per class         (default: 50)
      -h, --help  Show this help

    USAGE;
    exit(0);
}

$file = $args[0] ?? 'var/pdepend-summary.xml';

$xml = @simplexml_load_file($file);
if (!$xml) {
    fwrite(STDERR, "ERROR: Cannot read {$file}" . PHP_EOL);
    exit(2);
}

$maxCcn = (int)($opts['ccn'] ?? 8);
$maxNpath = (int)($opts['npath'] ?? 100);
$maxLoc = (int)($opts['loc'] ?? 40);
$maxWmc = (int)($opts['wmc'] ?? 50);

$methodViolations = [];
$classViolations = [];
$parsedFiles = [];

foreach ($xml->xpath('//file') as $f) {
    $parsedFiles[(string)$f['name']] = true;
}

foreach ($xml->xpath('//class') as $class) {
    $wmc = (int)$class['wmc'];
    $className = (string)($class['fqname'] ?? $class['name']);

    if ($wmc > $maxWmc) {
        $classViolations[] = [
            'class' => $className,
            'wmc' => $wmc,
        ];
    }

    foreach ($class->method as $method) {
        $ccn = (int)$method['ccn'];
        $npath = (int)$method['npath'];
        $loc = (int)$method['loc'];

        if ($ccn > $maxCcn || $npath > $maxNpath || $loc > $maxLoc) {
            $methodViolations[] = [
                'class' => $className,
                'method' => (string)$method['name'],
                'ccn' => $ccn,
                'npath' => $npath,
                'loc' => $loc,
            ];
        }
    }
}

// Count skipped files (not parsed by pdepend due to syntax not supported)
$srcDir = 'src/';
$totalFiles = 0;
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir));
foreach ($iterator as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $totalFiles++;
    }
}
$parsedCount = count($parsedFiles);
$skippedCount = $totalFiles - $parsedCount;

$hasViolations = $methodViolations !== [] || $classViolations !== [];

if ($classViolations !== []) {
    echo "PDepend: class violations (WMC > {$maxWmc}):" . PHP_EOL;
    foreach ($classViolations as $v) {
        echo sprintf("  %s — WMC=%d", $v['class'], $v['wmc']) . PHP_EOL;
    }
}

if ($methodViolations !== []) {
    echo "PDepend: method violations (CCN > {$maxCcn}, NPath > {$maxNpath}, LOC > {$maxLoc}):" . PHP_EOL;
    foreach ($methodViolations as $v) {
        echo sprintf(
            "  %s::%s — CCN=%d NPath=%d LOC=%d",
            $v['class'],
            $v['method'],
            $v['ccn'],
            $v['npath'],
            $v['loc'],
        ) . PHP_EOL;
    }
}

if ($skippedCount > 0) {
    echo "PDepend: WARNING: {$skippedCount}/{$totalFiles} files skipped (unsupported PHP syntax)" . PHP_EOL;
}

if (!$hasViolations && $skippedCount === 0) {
    echo "PDepend: OK ({$parsedCount} files)" . PHP_EOL;
} elseif (!$hasViolations) {
    echo "PDepend: OK ({$parsedCount}/{$totalFiles} files analyzed)" . PHP_EOL;
}

if ($hasViolations) {
    exit(1);
}
