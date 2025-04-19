<?php

function fuzzyBanyakPakaian($x)
{
    $sedikit = $banyak = 0;
    if ($x <= 40) {
        $sedikit = 1;
    } elseif ($x > 40 && $x < 80) {
        $sedikit = (80 - $x) / 40;
        $banyak = ($x - 40) / 40;
    } else {
        $banyak = 1;
    }

    return ['sedikit' => $sedikit, 'banyak' => $banyak];
}

function fuzzyTingkatKotoran($x)
{
    $rendah = $sedang = $tinggi = 0;
    if ($x <= 40) {
        $rendah = 1;
    }
    if ($x == 50) {
        $sedang = 1;
    }
    if ($x >= 60) {
        $tinggi = 1;
    }
    if ($x > 40 && $x < 50) {
        $rendah = (50 - $x) / 10;
        $sedang = ($x - 40) / 10;
    }
    if ($x > 50 && $x < 60) {
        $sedang = (60 - $x) / 10;
        $tinggi = ($x - 50) / 10;
    }

    return ['rendah' => $rendah, 'sedang' => $sedang, 'tinggi' => $tinggi];
}

function fuzzyRules($pakaian, $kotoran)
{
    $rules = [];
    $lambat = [];
    $cepat = [];

    $rules[] = min($pakaian['sedikit'], $kotoran['rendah']);
    $rules[] = min($pakaian['sedikit'], $kotoran['sedang']);
    $rules[] = min($pakaian['sedikit'], $kotoran['tinggi']);
    $rules[] = min($pakaian['banyak'], $kotoran['rendah']);
    $rules[] = min($pakaian['banyak'], $kotoran['sedang']);
    $rules[] = min($pakaian['banyak'], $kotoran['tinggi']);

    $lambat[] = min($pakaian['sedikit'], $kotoran['rendah']);
    $lambat[] = min($pakaian['sedikit'], $kotoran['sedang']);
    $cepat[]  = min($pakaian['sedikit'], $kotoran['tinggi']);
    $lambat[] = min($pakaian['banyak'], $kotoran['rendah']);
    $cepat[]  = min($pakaian['banyak'], $kotoran['sedang']);
    $cepat[]  = min($pakaian['banyak'], $kotoran['tinggi']);

    $result = [
        'rules'  => $rules,
        'lambat' => max($lambat),
        'cepat'  => max($cepat)
    ];

    return $result;
}

// Fungsi menghitung z dari bentuk segitiga
function hitungZ($alpha, $from, $to)
{
    $count = ($from - $to);
    return ($alpha * $count) + $to;
}

// Input data
$banyakPakaian = 50;
$tingkatKotoran = 58;

$fuzzyPakaian = fuzzyBanyakPakaian($banyakPakaian);
$fuzzyKotoran = fuzzyTingkatKotoran($tingkatKotoran);
$rules        = fuzzyRules($fuzzyPakaian, $fuzzyKotoran);
$lambatZ      = hitungZ($rules['lambat'], 1200, 500);
$cepatZ       = hitungZ($rules['cepat'], 1200, 500);

echo $rules['lambat'] . "<br>";
echo $rules['cepat'] . "<br>";
echo "<hr>";
echo $lambatZ . "<br>";
echo $cepatZ . "<br>";

// menghitung momen
$m1 = ($rules['lambat'] * pow($lambatZ, 2) / 2) - ($rules['lambat'] * pow(0, 2) / 2);
$m2 = 0;
$m3 = ($rules['cepat'] * pow(1200, 2) / 2) - ($rules['cepat'] * pow($cepatZ, 2) / 2);

// menghitung alpha
$a1 = $rules['lambat'] * ($lambatZ - 0);
$a2 = (($rules['lambat'] + $rules['cepat']) * ($cepatZ - $lambatZ)) / 2;
$a3 = $rules['cepat'] * (1200 - $cepatZ);

// defuzzifikasi
$defuzzifikasi = ($m1 + $m2 + $m3) / ($a1 + $a2 + $a3);

echo "<hr>";
echo "M1 : " . $m1 . "<br>";
echo "M2 : " . $m2 . "<br>";
echo "M3 : " . $m3 . "<br>";
echo "<hr>";
echo "A1 : " . $a1 . "<br>";
echo "A2 : " . $a2 . "<br>";
echo "A3 : " . $a3 . "<br>";
echo "<hr>";

echo $defuzzifikasi . "<br>";