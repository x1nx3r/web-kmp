<?php
// Usage: php scripts/map_bahan_spesifikasi.php /path/to/tracking.csv /path/to/specs.csv [output.csv]
if ($argc < 3) {
    echo "Usage: php scripts/map_bahan_spesifikasi.php /path/to/tracking.csv /path/to/specs.csv [output.csv]\n";
    exit(1);
}

$trackingFile = $argv[1];
$specFile = $argv[2];
$outFile = $argv[3] ?? null;

if (!is_readable($trackingFile)) {
    echo "Tracking file not readable: {$trackingFile}\n";
    exit(2);
}
if (!is_readable($specFile)) {
    echo "Specs file not readable: {$specFile}\n";
    exit(3);
}

function normalize($s) {
    $s = trim(mb_strtolower($s));
    $s = preg_replace('/[^a-z0-9\s]/u', ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return $s;
}

// Read specs CSV and build map: normalized name -> spec rows (may be multiple)
$specs = [];
if (($h = fopen($specFile, 'r')) !== false) {
    $header = fgetcsv($h, 0, ",", '"', "\\");
    while (($row = fgetcsv($h, 0, ",", '"', "\\")) !== false) {
        if (count($row) < 2) continue;
        // Try to find the material name in the row: prefer RM column if exists
        $name = null;
        // find header index for RM or RM name
        if ($header) {
            $idx = null;
            foreach ($header as $i => $col) {
                $c = mb_strtolower($col);
                if (strpos($c, 'rm') !== false || strpos($c, 'bahan') !== false || strpos($c, 'rm,') !== false) { $idx = $i; break; }
            }
            if ($idx !== null && isset($row[$idx])) $name = $row[$idx];
        }
        if (!$name) $name = $row[1] ?? $row[0];
        $norm = normalize($name);
        $specText = $row[2] ?? ($row[1] ?? '');
        $pabrik = $row[3] ?? '';
        $specs[$norm][] = ['raw_name' => $name, 'spec' => $specText, 'pabrik' => $pabrik];
    }
    fclose($h);
}

// Read tracking CSV and collect unique bahan baku names
$materials = [];
if (($h = fopen($trackingFile, 'r')) !== false) {
    $header = fgetcsv($h, 0, ",", '"', "\\");
    // find column index for 'Bahan Baku' or similar
    $colIdx = null;
    if ($header) {
        foreach ($header as $i => $col) {
            $c = mb_strtolower($col);
            if (strpos($c, 'bahan') !== false && strpos($c, 'baku') !== false) { $colIdx = $i; break; }
            if (strpos($c, 'rm') !== false) { $colIdx = $i; }
        }
    }
    // If not found, assume second column (index 1)
    if ($colIdx === null) $colIdx = 1;

    while (($row = fgetcsv($h, 0, ",", '"', "\\")) !== false) {
        if (!isset($row[$colIdx])) continue;
        $name = trim($row[$colIdx]);
        if ($name === '' || mb_strtolower($name) === 'bahan baku') continue;
        $materials[] = $name;
    }
    fclose($h);
}

$materials = array_values(array_unique($materials));

$results = [];
foreach ($materials as $mat) {
    $norm = normalize($mat);
    $matched = [];
    // Exact normalized match
    if (isset($specs[$norm])) {
        $matched = $specs[$norm];
    } else {
        // try substring matches
        foreach ($specs as $sname => $rows) {
            if ($sname === '') continue;
            // if one contains the other
            if (strpos($sname, $norm) !== false || strpos($norm, $sname) !== false) {
                $matched = $rows; break;
            }
            // loose word intersection
            $a = explode(' ', $sname);
            $b = explode(' ', $norm);
            $common = array_intersect($a, $b);
            if (count($common) >= 1) { $matched = $rows; break; }
        }
    }

    if (!empty($matched)) {
        foreach ($matched as $mrow) {
            $results[] = ['material' => $mat, 'spec' => $mrow['spec'], 'pabrik' => $mrow['pabrik'], 'matched_name' => $mrow['raw_name']];
        }
    } else {
        $results[] = ['material' => $mat, 'spec' => '', 'pabrik' => '', 'matched_name' => ''];
    }
}

// Print results
echo "Found " . count($materials) . " unique materials in tracking CSV.\n\n";
foreach ($results as $r) {
    echo "Material: {$r['material']}\n";
    if ($r['matched_name']) {
        echo "  Matched as: {$r['matched_name']}\n";
        echo "  Pabrik: {$r['pabrik']}\n";
        echo "  Spesifikasi: " . trim($r['spec']) . "\n";
    } else {
        echo "  Spec: MISSING\n";
    }
    echo "-----------------------------------------\n";
}

if ($outFile) {
    $fh = fopen($outFile, 'w');
    fputcsv($fh, ['material','matched_name','pabrik','spec'], ",", '"', "\\");
    foreach ($results as $r) {
        fputcsv($fh, [$r['material'],$r['matched_name'],$r['pabrik'], $r['spec']], ",", '"', "\\");
    }
    fclose($fh);
    echo "Wrote mapping to {$outFile}\n";
}

echo "Done.\n";
