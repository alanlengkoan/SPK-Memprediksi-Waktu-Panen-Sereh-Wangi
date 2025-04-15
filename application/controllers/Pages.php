<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pages extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data = [
            ['minggu' => 1, 'permintaan' => 532, 'persediaan' => 288],
            ['minggu' => 2, 'permintaan' => 570, 'persediaan' => 296],
            ['minggu' => 3, 'permintaan' => 546, 'persediaan' => 300],
            ['minggu' => 4, 'permintaan' => 556, 'persediaan' => 279],
            ['minggu' => 5, 'permintaan' => 633, 'persediaan' => 317],
            ['minggu' => 6, 'permintaan' => 580, 'persediaan' => 284],
            ['minggu' => 7, 'permintaan' => 548, 'persediaan' => 343],
            ['minggu' => 8, 'permintaan' => 593, 'persediaan' => 263],
            ['minggu' => 9, 'permintaan' => 553, 'persediaan' => 271],
        ];


        foreach ($data as $item) {
            $fPermintaan = $this->fuzzyPermintaan($item['permintaan']);
            $fPersediaan = $this->fuzzyPersediaan($item['persediaan']);

            echo "<pre>";
            print_r($fPermintaan);
            // $rules = $this->fuzzyRule($fPermintaan, $fPersediaan);
            // $produksi = $this->defuzzifikasi($rules);
            // echo "Minggu ke-{$item['minggu']} -> Produksi: " . round($produksi) . "<br>";
        }
    }

    function fuzzyPermintaan($x)
    {
        if ($x <= 540) return ['turun' => 1, 'naik' => 0];
        elseif ($x >= 560) return ['turun' => 0, 'naik' => 1];
        else {
            $turun = (560 - $x) / 20;
            $naik = ($x - 540) / 20;
            return ['turun' => $turun, 'naik' => $naik];
        }
    }

    function fuzzyPersediaan($x)
    {
        if ($x <= 280) return ['sedikit' => 1, 'banyak' => 0];
        elseif ($x >= 300) return ['sedikit' => 0, 'banyak' => 1];
        else {
            $sedikit = (300 - $x) / 20;
            $banyak = ($x - 280) / 20;
            return ['sedikit' => $sedikit, 'banyak' => $banyak];
        }
    }

    function fuzzyRule($permintaan, $persediaan)
    {
        $rules = [];

        // Rule 1: IF permintaan TURUN AND persediaan BANYAK THEN produksi BERKURANG
        $rules[] = [
            'alpha' => min($permintaan['turun'], $persediaan['banyak']),
            'z' => function ($alpha) {
                return 700 - $alpha * 200;
            } // Produksi berkurang
        ];

        // Rule 2: IF permintaan TURUN AND persediaan SEDIKIT THEN produksi TETAP
        $rules[] = [
            'alpha' => min($permintaan['turun'], $persediaan['sedikit']),
            'z' => function ($alpha) {
                return 500 + $alpha * 100;
            } // Produksi tetap
        ];

        // Rule 3: IF permintaan NAIK AND persediaan BANYAK THEN produksi TETAP
        $rules[] = [
            'alpha' => min($permintaan['naik'], $persediaan['banyak']),
            'z' => function ($alpha) {
                return 500 + $alpha * 100;
            } // Produksi tetap
        ];

        // Rule 4: IF permintaan NAIK AND persediaan SEDIKIT THEN produksi BERTAMBAH
        $rules[] = [
            'alpha' => min($permintaan['naik'], $persediaan['sedikit']),
            'z' => function ($alpha) {
                return 500 + $alpha * 200;
            } // Produksi bertambah
        ];

        return $rules;
    }

    function defuzzifikasi($rules)
    {
        $numerator = 0;
        $denominator = 0;
        foreach ($rules as $rule) {
            $z = $rule['z']($rule['alpha']);
            $numerator += $rule['alpha'] * $z;
            $denominator += $rule['alpha'];
        }
        return $denominator != 0 ? $numerator / $denominator : 0;
    }































    // public function index()
    // {
    //     $data = [
    //         'title'   => 'Home',
    //         'content'   => 'pages/home/view',
    //         'css'       => '',
    //         'js'        => ''
    //     ];
    //     // untuk load view
    //     $this->load->view('pages/base', $data);
    // }

    public function tentang()
    {
        $data = [
            'title' => 'Tentang Kami',
            'content' => 'pages/tentang/view',
            'css'     => '',
            'js'      => ''
        ];
        // untuk load view
        $this->load->view('pages/base', $data);
    }
}
