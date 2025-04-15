<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Consultation extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        // untuk mengecek status login
        checking_session($this->username, $this->role, ['admin']);
    }

    // untuk default
    public function index()
    {
        // untuk load view
        $this->template->load('admin', 'Consultation', 'consultation', 'view');
    }

    // untuk get data
    public function get_data_dt()
    {
        $this->m_consultation->get_all_data_dt();
    }

    // untuk proses
    public function process()
    {
        $image = add_picture('image');

        if ($image['status']) {

            $data = [
                'id_users' => $this->id_users,
                'image'    => $image['data']['file_name'],
            ];

            $this->db->trans_start();
            $this->crud->i('tb_consultation', $data);
            $id = $this->db->insert_id();
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                $response = ['title' => 'Gagal!', 'text' => 'Gagal Simpan!', 'type' => 'error', 'button' => 'Ok!'];
            } else {
                $response = ['title' => 'Berhasil!', 'text' => 'Berhasil Simpan!', 'type' => 'success', 'button' => 'Ok!', 'id' => $id];
            }
        } else {
            $response = ['title' => 'Gagal!', 'text' => $image['message'], 'type' => 'error', 'button' => 'Ok!'];
        }
        $this->_response_message($response);
    }

    public function results($id)
    {
        $get_consultation    = $this->crud->gda('tb_consultation', ['id_consultation' => $id]);
        $image_loc           = upload_path('gambar') . $get_consultation['image'];
        $data_classification = $this->m_classification->get_all();
        $get_basis           = $this->m_datatraining->get_all();

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
        foreach ($get_basis->result() as $key => $value) {
            $data_training[$value->id_datatraining]  = $value;
        }

        $data = [
            'ini'                 => $this,
            'id_consultation'     => $id,
            'data_training'       => $data_training,
            'data_test'           => $data_test,
            'data_classification' => $data_classification->result(),
        ];
        // untuk load view
        $this->template->load('admin', 'Hasil Consultation', 'consultation', 'result', $data);
    }

    // untuk proses hapus data
    public function process_del()
    {
        $post = $this->input->post(NULL, TRUE);

        $get = $this->crud->gda('tb_consultation', ['id_consultation' => $post['id']]);

        $check = checking_data($this->db->database, 'tb_consultation', 'id_consultation', $post['id']);

        if ($check > 0) {
            $response = ['title' => 'Gagal!', 'text' => 'Maaf data yang Anda hapus masih digunakan!', 'type' => 'error', 'button' => 'Ok!'];
        } else {
            $this->db->trans_start();
            
            del_picture($get['image']);

            $this->crud->d('tb_consultation', $post['id'], 'id_consultation');
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                $response = ['title' => 'Gagal!', 'text' => 'Gagal Hapus!', 'type' => 'error', 'button' => 'Ok!'];
            } else {
                $response = ['title' => 'Berhasil!', 'text' => 'Berhasil Hapus!', 'type' => 'success', 'button' => 'Ok!'];
            }
        }

        $this->_response_message($response);
    }

    public function processEuclidianDistance($data_training, $data_test)
    {
        $result = [];

        foreach ($data_training as $key => $value) {
            $count = round(sqrt(pow($data_test['contrast'] - $value->contrast, 2) + pow($data_test['correlation'] - $value->correlation, 2) + pow($data_test['energy'] - $value->energy, 2) + pow($data_test['homogeneity'] - $value->homogeneity, 2) + pow($data_test['r'] - $value->r, 2) + pow($data_test['g'] - $value->g, 2) + pow($data_test['b'] - $value->b, 2)), 2);

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
                    'id'    => $value->id_classification,
                    'label' => $value->nama,
                    'count' => $counts[$value->nama]
                ];
            }
        }

        foreach ($result as $k => $v) {
            $sort[$k] = $v['count'];
        }

        array_multisort($sort, SORT_DESC, $result);

        return $result[0];
    }

    public function processValiditasKToN($data_sort, $data_training, $classification_name, $n)
    {
        $result = [];

        foreach ($data_sort as $key => $value) {
            $check = ($data_training[$key]->nama === $classification_name ? 1 : 0);
            $count = round((1 / $n) * $check, 2);

            $result[$key] = $count;
        }

        return $result;
    }

    public function processWeightVoting($data_sort, $data_validitas)
    {
        $result = [];

        foreach ($data_sort as $key => $value) {
            $count = round($data_validitas[$key] * (1 / ($value + 0.5)), 2);

            $result[$key] = [
                'euclidian' => $value,
                'validitas' => $data_validitas[$key],
                'weight'    => $count
            ];
        }

        return $result;
    }
}
