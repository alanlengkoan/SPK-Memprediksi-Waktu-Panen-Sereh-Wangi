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
    $cepat[] = min($pakaian['sedikit'], $kotoran['tinggi']);
    $lambat[] = min($pakaian['banyak'], $kotoran['rendah']);
    $cepat[] = min($pakaian['banyak'], $kotoran['sedang']);
    $cepat[] = min($pakaian['banyak'], $kotoran['tinggi']);

    $result = [
        'rules' => $rules,
        'lambat' => max($lambat),
        'cepat' => max($cepat)
    ];

    return $result;
}

// function defuzzifikasi($rules) {
//     $num = 0;
//     $den = 0;
//     foreach ($rules as $rule) {
//         $num += $rule['alpha'] * $rule['rpm'];
//         $den += $rule['alpha'];
//     }
//     return $den != 0 ? $num / $den : 0;
// }

// Input data
$banyakPakaian = 50;
$tingkatKotoran = 58;

$fuzzyPakaian = fuzzyBanyakPakaian($banyakPakaian);
$fuzzyKotoran = fuzzyTingkatKotoran($tingkatKotoran);
$rules = fuzzyRules($fuzzyPakaian, $fuzzyKotoran);
// $hasilRPM = defuzzifikasi($rules);

// // Output
// echo "Banyak Pakaian: $banyakPakaian<br>";
// echo "Tingkat Kotoran: $tingkatKotoran<br>";
// echo "=> Kecepatan Putaran Mesin: " . round($hasilRPM, 3) . " RPM";

echo "<pre>";
print_r($fuzzyPakaian);
print_r($fuzzyKotoran);
print_r($rules);




// studi kasus

// output :
// berapa kecepatan putaran mesin dalam rpm

// kriteria :
// - banyak pakaian (0-100)
// - tingkat kotoran (0-100)

// sub kriteria :
// * banyak pakaian
// - sedikit (<= 40)
// - banyak (>= 80)

// * tingkat kotoran
// - rendah (0-40)
// - sedang (50)
// - tinggi (60-100)

// soal :
// banyak pakaian => 50
// tingkat kotoran => 58

// output :
// berapa kecepatan putaran mesin dalam rpm

// https://www.youtube.com/watch?v=qXv984DFxZ8