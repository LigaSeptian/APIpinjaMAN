<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class User extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    public function register_post()
    {
        $nik = $this->post('nik');
        $name = $this->post('name');
        $email = $this->post('email');
        $data = [
            'nik' => $nik,
            'nama' => $name,
            'no_telepon' => $this->post('phone'),
            'email' => $email,
            'nama_orang_tua' => $this->post('parent_name'),
            'pendidikan_terakhir' => $this->post('education'),
            'status_perkawinan' => $this->post('marriage_status'),
            'alamat' => $this->post('address'),
            'nama_perusahaan' => $this->post('company_name'),
            'status_pekerjaan' => $this->post('job_status'),
            'posisi' => $this->post('position'),
            'lama_bekerja' => $this->post('work_length'),
            'penghasilan_per_bulan' => $this->post('monthly_income')
        ];

        foreach ($data as $key => $value) {
            if (!isset($value)) {
                $this->response([
                    'status' => 'Error',
                    'message' => 'Invalid data'
                ], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $config_ktp_image = [
            'upload_path' => './upload/ktp_image',
            'allowed_types' => 'gif|jpg|png|webp|jpeg|jpe|jif|jfif|jfi|jp2|j2k|jpf|jpx|jpm|mj2|tif|tiff|bmp',
            'file_name' => $nik . '.png'
        ];
        $this->load->library('upload');
        $this->upload->initialize($config_ktp_image);

        if ($this->upload->do_upload('ktp_image')) {
            $config_ktp_selfie = [
                'upload_path' => './upload/ktp_selfie',
                'allowed_types' => 'gif|jpg|png|webp|jpeg|jpe|jif|jfif|jfi|jp2|j2k|jpf|jpx|jpm|mj2|tif|tiff|bmp',
                'file_name' => $nik . '.png'
            ];
            $this->upload->initialize($config_ktp_selfie);
            if ($this->upload->do_upload('ktp_selfie')) {
                $this->user_model->add_user($data);
                $this->response([
                    'message' => 'Register success',
                    'data' => [
                        'nik' => $nik,
                        'name' => $name,
                        'email' => $email
                    ]
                ], REST_Controller::HTTP_CREATED);
            } else {
                $this->response([
                    'status' => 'Error',
                    'message' => 'Failed to upload'
                ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $this->response([
                'status' => 'Error',
                'message' => 'Failed to upload'
            ], REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function login_post()
    {
        $data = [
            'email' => $this->post('email'),
            'pin' => $this->post('pin')
        ];
        $auth = $this->user_model->login($data);

        if (!$auth){
            $this->response([
                'status' => 'Error',
                'message' => 'Login failed'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }else{
            $expired = date_add(new DateTime(date('Y-m-d')), date_interval_create_from_date_string("30 days"));
            
            $token = [
                'nik' => $auth['nik'],
                'email' => $auth['email'],
                'role' => $auth['role'],
                
                'expired_at' =>$expired->format('Y-m-d H:i:s'),
            ];
            $token = json_encode($token);
            $token = base64_encode($token);
            $this->response([
                'data'=> [
                    'nik' => $auth['nik'],
                    'email' => $auth['email'],
                    'role' => $auth['role'],
                    'token' => $token,
                ],
                'status' => 'Error',
                'message' => 'Login success'
            ], REST_Controller::HTTP_OK);
        }
    }
}
