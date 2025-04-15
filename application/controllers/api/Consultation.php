<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Consultation extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function detail($id)
    {
        $get = $this->m_consultation->get_all_user($id);
        $num = $get->num_rows();

        if ($num == 0) {
            $response = ['status' => false, 'data' => []];
        } else {
            $result = [];
            foreach ($get->result() as $key => $value) {
                $result[] = [
                    'id_consultation' => $value->id_consultation,
                    'id_users'        => $value->id_users,
                    'nama'            => $value->users,
                    'image'           => $value->image,
                    'created_at'      => tgl_indo($value->created_at)
                ];
            }

            $response = ['status' => true, 'data' => $result];
        }

        $this->_response($response);
    }

    public function save()
    {
        $post = $this->input->post(NULL, TRUE);

        $parseImage = base64_decode($post['loc_image']);

        file_put_contents(upload_path('gambar') . '/' . $post['image'], $parseImage);

        $data = [
            'id_users' => $post['id_users'],
            'image'    => $post['image'],
        ];

        $this->db->trans_start();
        $this->crud->i('tb_consultation', $data);
        $id = $this->db->insert_id();
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            $response = ['status' => false, 'title' => 'Gagal!', 'text' => 'Gagal Simpan!', 'type' => 'error', 'button' => 'Ok!'];
        } else {
            $response = ['status' => true, 'title' => 'Berhasil!', 'text' => 'Berhasil Simpan!', 'type' => 'success', 'button' => 'Ok!', 'id' => $id];
        }

        $this->_response($response);
    }

    public function result($id)
    {
        $get_consultation    = $this->m_consultation->get_detail($id)->row_array();
        $image_loc           = upload_path('gambar') . $get_consultation['image'];
        $data_classification = $this->m_classification->get_all()->result();
        $get_basis           = $this->m_datatraining->get_all()->result();

        $this->imagesampler->image($image_loc);
        $this->imagesampler->set_steps(1);
        $this->imagesampler->init();

        // untuk menghitung GLCM
        $glcm        = $this->imagesampler->calculateGLCM();
        $contrast    = $this->imagesampler->calculateContrast($glcm);
        $correlation = $this->imagesampler->calculateCorrelation($glcm);
        $energy      = $this->imagesampler->calculateEnergy($glcm);
        $homogeneity = $this->imagesampler->calculateHomogeneity($glcm);

        // untuk rgb
        $rgb       = $this->imagesampler->getRGB();
        $objek     = $rgb[0][0];

        $data_test = [
            'image'       => $get_consultation['image'],
            'contrast'    => $contrast,
            'correlation' => $correlation,
            'energy'      => $energy,
            'homogeneity' => $homogeneity,
            'r'           => $objek[0],
            'g'           => $objek[1],
            'b'           => $objek[2],
        ];

        $data_training = [];
        foreach ($get_basis as $key => $value) {
            $data_training[$value->id_datatraining]  = $value;
        }

        $a = $this->processEuclidianDistance($data_training, $data_test);

        $b = $this->processMinToMaxEuclidianDistance($a);

        $k1 = $this->processClasificationKToN($b, $data_training, 1);

        $r1 = $this->processClasification($k1, $data_classification);

        // untuk update hasil consultation
        $this->db->update(
            'tb_consultation',
            [
                'id_classification' => $r1['id'],
            ],
            [
                'id_consultation' => $id
            ]
        );

        $consultation = [
            'nama'           => $get_consultation['users'],
            'classification' => $get_consultation['nama'] ?? $r1['label'],
            'descripton'     => $get_consultation['deskripsi'] ?? $r1['deskripsi'],
            'image'          => $get_consultation['image'],
            'contrast'       => $contrast,
            'correlation'    => $correlation,
            'energy'         => $energy,
            'homogeneity'    => $homogeneity,
            'r'              => $objek[0],
            'g'              => $objek[1],
            'b'              => $objek[2],
        ];

        $this->_response($consultation);
    }

    public function img($id)
    {
        $get_consultation = $this->m_consultation->get_detail($id)->row_array();

        $url = upload_path('gambar') . $get_consultation['image'];

        if (getExtension($get_consultation['image']) === 'png') {
            header("Content-Type: image/png");
        } else {
            header("Content-Type: image/jpeg");
        }

        readfile($url);

        exit();
    }

    public function processEuclidianDistance($data_training, $data_test)
    {
        $result = [];

        foreach ($data_training as $key => $value) {
            $count = round(sqrt(pow($data_test['contrast'] - $value->contrast, 2) + pow($data_test['correlation'] - $value->correlation, 2) + pow($data_test['energy'] - $value->energy, 2) + pow($data_test['homogeneity'] - $value->homogeneity, 2)), 2);

            $result[$value->id_datatraining] = $count;
        }

        return $result;
    }

    public function processMinToMaxEuclidianDistance($data)
    {
        asort($data);

        return $data;
    }

    public function processClasificationKToN($data_sort, $data_training, $n)
    {
        $k = 1;
        $result = [];

        foreach ($data_sort as $key_r => $val_r) {
            $check = ($k++ <= $n ? $data_training[$key_r]->nama : '-');

            $result[] = $check;
        }

        return $result;
    }

    public function processClasification($data, $classification)
    {
        $counts = array_count_values($data);
        $result = [];

        foreach ($classification as $key => $value) {
            if (!empty($counts[$value->nama])) {
                $result[] = [
                    'id'        => $value->id_classification,
                    'label'     => $value->nama,
                    'deskripsi' => $value->deskripsi,
                    'count'     => $counts[$value->nama]
                ];
            }
        }

        foreach ($result as $k => $v) {
            $sort[$k] = $v['count'];
        }

        array_multisort($sort, SORT_DESC, $result);

        return $result[0];
    }
}
