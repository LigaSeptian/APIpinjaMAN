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
        $this->load->model('transaction_model');
    }

    public function register_post()
    {
        $nik = $this->post('nik');
        $name = $this->post('name');
        $email = $this->post('email');
        $pin = $this->post('pin');
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
            'penghasilan_per_bulan' => $this->post('monthly_income'),
            'pin' => password_hash($pin, PASSWORD_DEFAULT)
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
        $email = $this->post('email');
        $pin = $this->post('pin');
        $user = $this->user_model->get_user_by_email($email);
        if ($user) {
            if (password_verify($pin, $user['pin'])) {
                $data = [
                    'nik' => $user['nik'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'name' => $user['nama'],
                    'token' => base64_encode($email . ':' . $pin)
                ];
                $this->response([
                    'message' => 'Login success',
                    'data' => $data
                ]);
            } else {
                $this->response([
                    'message' => 'Login failed',
                    'data' => [
                        'auth_message' => 'Wrong password'
                    ]
                ]);
            }
        } else {
            $this->response([
                'message' => 'Login failed',
                'data' => [
                    'auth_message' => 'User not found'
                ]
            ]);
        }
    }

    public function limit_get($nik)
    {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $this->user_model->get_user_by_email($_SERVER['PHP_AUTH_USER']);
            if ($user['nik'] == $nik && password_verify($_SERVER['PHP_AUTH_PW'], $user['pin'])) {
                $this->response([
                    'nik' => $user['nik'],
                    'status' => $user['status'],
                    'data' => [
                        'limit' => $user['limit_pinjaman'],
                        'limit_remaining' => $user['sisa_limit']
                    ]
                ]);
            } else {
                $this->response([
                    'status' => 'Authorization failed'
                ], REST_Controller::HTTP_FORBIDDEN);
            }
        } else {
            $this->response([
                'status' => 'Authorization failed'
            ], REST_Controller::HTTP_FORBIDDEN);
        }
    }

    public function transaction_post($nik)
    {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $this->user_model->get_user_by_email($_SERVER['PHP_AUTH_USER']);
            if ($user['nik'] == $nik && password_verify($_SERVER['PHP_AUTH_PW'], $user['pin'])) {
                if ($user['status'] == 'accepted') {
                    $json_data = json_decode($this->input->raw_input_stream, true);
                    $data = [
                        'nik' => $user['nik'],
                        'jumlah' => $json_data['loan']['amount'],
                        'tenggat_waktu' => $json_data['loan']['deadline'],
                        'biaya_admin' => $json_data['loan']['admin_fee'],
                        'total_pinjaman' => $json_data['loan']['amount'] + $json_data['loan']['admin_fee'],
                        'bank' => $json_data['receiver']['bank'],
                        'no_rekening' => $json_data['receiver']['account_number']
                    ];

                    $limit_remaining = $user['sisa_limit'] - $json_data['loan']['amount'];

                    if ($limit_remaining >= 0) {
                        $this->user_model->update_user_limit_remaining($user['nik'], $limit_remaining);
                        $this->transaction_model->add_transactions($data);
                        $this->response([
                            'message' => 'Loan success',
                            'data' => [
                                'amount' => $json_data['loan']['amount'],
                                'limit_remaining' => $limit_remaining
                            ]
                        ]);
                    } else {
                        $this->response([
                            'message' => 'Loan failed',
                            'data' => [
                                'error_message' => 'Loan mount is bigger than your remaining loan limit'
                            ]
                        ]);
                    }
                } else {
                    $this->response([
                        'message' => 'Loan failed',
                        'data' => [
                            'error_message' => 'Your apllication status is not accepted yet'
                        ]
                    ]);
                }
            }
        }
        $this->response([
            'status' => 'Authorization failed'
        ], REST_Controller::HTTP_FORBIDDEN);
    }

    public function transactions_get($nik)
    {
        function mapResult($array)
        {
            return [
                'id' => $array['id'],
                'amount' => $array['jumlah'],
                'admin_fee' => $array['biaya_admin'],
                'total' => $array['total_pinjaman'],
                'deadline' => $array['tenggat_waktu']
            ];
        }
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $this->user_model->get_user_by_email($_SERVER['PHP_AUTH_USER']);
            if ($user['nik'] == $nik && password_verify($_SERVER['PHP_AUTH_PW'], $user['pin'])) {
                $transactions = $this->transaction_model->get_transactions_by_nik($nik);
                $result = array_map('mapResult', $transactions);
                $this->response($result);
            }
        }
        $this->response([
            'status' => 'Authorization failed'
        ], REST_Controller::HTTP_FORBIDDEN);
    }

    public function history_get($nik)
    {
        function mapResult($array)
        {
            return [
                'id' => $array['id'],
                'amount' => $array['jumlah'],
                'admin_fee' => $array['biaya_admin'],
                'total' => $array['total_pinjaman'],
                'paid_date' => $array['waktu_pembayaran']
            ];
        }
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $this->user_model->get_user_by_email($_SERVER['PHP_AUTH_USER']);
            if ($user['nik'] == $nik && password_verify($_SERVER['PHP_AUTH_PW'], $user['pin'])) {
                $transactions = $this->transaction_model->get_transactions_history_by_nik($nik);
                $result = array_map('mapResult', $transactions);
                $this->response($result);
            }
        }
        $this->response([
            'status' => 'Authorization failed'
        ], REST_Controller::HTTP_FORBIDDEN);
    }
    
}
