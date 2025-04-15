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

// ===== MAIN PROGRAM =====
$banyakPakaian = 50;
$tingkatKotoran = 58;

// Hitung nilai z (representatif untuk defuzzifikasi)
$lambatZ = hitungZ($rules['lambat'], 1200, 500);
$cepatZ  = hitungZ($rules['cepat'], 1200, 500);


echo $rules['lambat'] . "<br>";
echo $rules['cepat'] . "<br>";

echo $lambatZ . "<br>";
echo $cepatZ . "<br>";

$m1 = ($rules['lambat'] * pow($lambatZ, 2) / 2) - ($rules['lambat'] * pow(0, 2) / 2);
$m2 = 0;
$m3 = ($rules['cepat'] * pow(1200, 2) / 2) - ($rules['cepat'] * pow($cepatZ, 2) / 2);

echo $m1 . "<br>";
echo $m2 . "<br>";
echo $m3 . "<br>";






// function newFuzzy($z, $get_lambat, $get_cepat,  $from, $to)
// {
//     $lambat = $cepat = $from;
//     if ($z <= $get_lambat) {
//         $lambat = 1;
//     } elseif ($z > $get_lambat && $z < $get_cepat) {
//         $lambat = ($get_cepat - $z) / 700;
//         $cepat = ($z - $get_lambat) / 700;
//     } else {
//         $cepat = $to;
//     }
//     return [
//         'lambat' => $lambat,
//         'cepat' => $cepat
//     ];
// }



// $new_lambat = newFuzzy($lambat, $rules['lambat'], $rules['cepat'], 1200, 500);
// $new_cepat  = newFuzzy($cepat, $rules['lambat'], $rules['cepat'], 1200, 500);

// echo $lambat . "<br>";
// echo $cepat . "<br>";

// echo $new_lambat['lambat'] . "<br>";
// echo $new_cepat['cepat'] . "<br>";

// // Defuzzifikasi dengan rata-rata tertimbang
// $total_alpha_z = ($rules['lambat'] * $lambat['z']) + ($rules['cepat'] * $cepat['z']);
// $total_alpha   = $rules['lambat'] + $rules['cepat'];

// $defuzzifikasi = ($total_alpha == 0) ? 0 : $total_alpha_z / $total_alpha;

// // Output
// echo "α Lambat : " . $rules['lambat'] . "<br>";
// echo "α Cepat : " . $rules['cepat'] . "<br>";

// echo "Lambat (a1: {$lambat['a1']}, a2: {$lambat['a2']}, z: {$lambat['z']})<br>";
// echo "Cepat  (a1: {$cepat['a1']}, a2: {$cepat['a2']}, z: {$cepat['z']})<br>";

// echo "Nilai Z (Defuzzifikasi) = " . round($defuzzifikasi, 2) . "<br>";

echo "<pre>";
// print_r($fuzzyPakaian);
// print_r($fuzzyKotoran);
// print_r($rules);
// print_r($n);




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