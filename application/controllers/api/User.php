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
                        'email' => $email,
                        'role' => 'user',
                        'token' => base64_encode($email . ':' . $pin)
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
        $admin = $this->user_model->get_admin_by_email($email);
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
        } else if ($admin) {
            if ($email == $admin['email'] && $pin == $admin['pin']) {
                $data = [
                    'nik' => '',
                    'email' => $admin['email'],
                    'role' => 'admin',
                    'name' => $admin['name'],
                    'token' => base64_encode($email . ':' . $pin)
                ];
                $this->response([
                    'message' => 'Login success',
                    'data' => $data
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
                'deadline' => $array['tenggat_waktu'],
                'bank' => $array['bank'],
                'account_number' => $array['no_rekening']
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
        function mapResults($array)
        {
            return [
                'id' => $array['id'],
                'amount' => $array['jumlah'],
                'admin_fee' => $array['biaya_admin'],
                'total' => $array['total_pinjaman'],
                'paid_date' => $array['waktu_pembayaran'],
                'bank' => $array['bank'],
                'account_number' => $array['no_rekening']
            ];
        }
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $this->user_model->get_user_by_email($_SERVER['PHP_AUTH_USER']);
            if ($user['nik'] == $nik && password_verify($_SERVER['PHP_AUTH_PW'], $user['pin'])) {
                $transactions = $this->transaction_model->get_transactions_history_by_nik($nik);
                $result = array_map('mapResults', $transactions);
                $this->response($result);
            }
        }
        $this->response([
            'status' => 'Authorization failed'
        ], REST_Controller::HTTP_FORBIDDEN);
    }

    public function details_get($nik)
    {
        $user = $this->user_model->get_user_by_nik($nik);
        $registration_info = [];
        if ($user['status'] == 'accepted') {
            $registration_info = [
                'loan_limit' => $user['limit_pinjaman'],
                'limit_remaining' => $user['sisa_limit']
            ];
        } else if ($user['status'] == 'rejected') {
            $registration_info = [
                'cause_of_rejection' => $user['alasan_penolakan']
            ];
        }

        $this->response([
            'name' => $user['nama'],
            'nik' => $user['nik'],
            'phone' => $user['no_telepon'],
            'email' => $user['email'],
            'parent_name' => $user['nama_orang_tua'],
            'education' => $user['pendidikan_terakhir'],
            'marriage_status' => $user['status_perkawinan'],
            'address' => $user['alamat'],
            'job' => [
                'company_name' => $user['nama_perusahaan'],
                'job_status' => $user['status_pekerjaan'],
                'position' => $user['posisi'],
                'work_length' => $user['lama_bekerja'],
                'monthly_income' => $user['penghasilan_per_bulan'],
            ],
            'registration' => [
                'status' => $user['status'],
                'data' => $registration_info
            ]
        ]);
    }

    public function registration_post($nik)
    {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $admin = $this->user_model->get_admin_by_email($_SERVER['PHP_AUTH_USER']);
            if ($admin['password'] == $_SERVER['PHP_AUTH_PW']) {
                $json_data = json_decode($this->input->raw_input_stream, true);
                $status = $json_data['status'];
                if ($status == 'accepted') {
                    $data = [
                        'status' => $status,
                        'limit_pinjaman' => $json_data['loan_limit'],
                        'sisa_limit' => $json_data['loan_limit']
                    ];
                } else if ($status == 'rejected') {
                    $data = [
                        'status' => $status,
                        'alasan_penolakan' => $json_data['reason'],
                        'limit_pinjaman' => 0,
                        'sisa_limit' => 0
                    ];
                } else {
                    $this->response([
                        'status' => 'Error',
                        'message' => 'Invalid data'
                    ], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
                }
                $this->user_model->update_registration_status($nik, $data);
                $this->response([
                    'message' => 'Request successfully executed',
                    'data' => [
                        'nik' => $nik,
                        'status' => $status
                    ]
                ]);
            }
        }
        $this->response([
            'status' => 'Authorization failed'
        ], REST_Controller::HTTP_FORBIDDEN);
    }
    public function email_post($nik)
    {
        $email = $this->post('email');

        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $this->user_model->get_user_by_email($_SERVER['PHP_AUTH_USER']);
            if ($user['nik'] == $nik && password_verify($_SERVER['PHP_AUTH_PW'], $user['pin'])) {

                $old_email = $user['email'];

                $emailUpdate = $this->user_model->update_user_email($user['nik'], $email);
                $this->response([
                    'status' => 'Success',
                    'message' => 'Email change is success',
                    'data' => [
                        'old_email' => $old_email,
                        'new_email' => $email
                    ]
                ]);
            }
        }
    }

    public function pin_post($nik)
    {
        $pin = $this->post('pin');

        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $this->user_model->get_user_by_email($_SERVER['PHP_AUTH_USER']);
            if ($user) {
                if ($user['nik'] == $nik && password_verify($_SERVER['PHP_AUTH_PW'], $user['pin'])) {

                    $pinUpdate = $this->user_model->update_user_pin($user['nik'], $pin);
                    $this->response([
                        'status' => 'Success',
                        'message' => 'Pin change is success',
                        'data' => [
                            'pin' => $pin
                        ]
                    ]);
                }
            }
        }
    }
}
