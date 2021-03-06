<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Transaction extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('transaction_model');
    }

    public function pay_post($id)
    {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $this->user_model->get_user_by_email($_SERVER['PHP_AUTH_USER']);
            if (password_verify($_SERVER['PHP_AUTH_PW'], $user['pin'])) {
                $config = [
                    'upload_path' => './upload/payment_proof',
                    'allowed_types' => 'gif|jpg|png|webp|jpeg|jpe|jif|jfif|jfi|jp2|j2k|jpf|jpx|jpm|mj2|tif|tiff|bmp',
                    'file_name' => 'transaction_' . $id . '.png'
                ];
                $this->load->library('upload');
                $this->upload->initialize($config);

                if ($this->upload->do_upload('payment_proof')) {
                    $payment_time = time();
                    $this->transaction_model->set_transaction_pending($id, date('Y-m-d H:i:s', $payment_time));
                    $this->response([
                        'message' => 'Upload success',
                        'data' => [
                            'transaction_id' => $id,
                            'payment_time' => date('Y/m/d')
                        ]
                    ]);
                } else {
                    $this->response([
                        'message' => 'Upload failed'
                    ]);
                }
            }
        }
        $this->response([
            'status' => 'Authorization failed'
        ], REST_Controller::HTTP_FORBIDDEN);
    }

    public function details_get($id)
    {
        $transaction = $this->transaction_model->get_transaction_detail_by_id($id);
        $this->response([
            'receiver' => [
                'bank' => $transaction['bank'],
                'account_number' => $transaction['no_rekening'],
            ],
            'loan' => [
                'amount' => $transaction['total_pinjaman'],
                'deadline' => $transaction['tenggat_waktu'],
                'admin_fee' => $transaction['biaya_admin'],
                'total' => $transaction['total_pinjaman'],
            ],
            'payment_proof' => base_url('upload/payment_proof/transaction_' . $id . '.png')
        ]);
    }

    public function payment_post($id)
    {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $admin = $this->user_model->get_admin_by_email($_SERVER['PHP_AUTH_USER']);
            if ($admin['pin'] == $_SERVER['PHP_AUTH_PW']) {
                $json_data = json_decode($this->input->raw_input_stream, true);
                $status = $json_data['status'];
                $transaction = $this->transaction_model->get_transaction_detail_by_id($id);
                if ($status == 'accepted') {
                    $data = [
                        'status' => 'dibayar'
                    ];
                } else if ($status == 'rejected') {
                    $data = [
                        'status' => 'pembayaran ditolak'
                    ];
                } else {
                    $this->response([
                        'status' => 'Error',
                        'message' => 'Invalid data'
                    ], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
                }
                $this->transaction_model->update_payment_status($id, $data);
                $this->response([
                    'message' => 'Request successfully executed',
                    'data' => [
                        'nik' => $transaction['nik'],
                        'status' => $status
                    ]
                ]);
            }
        }
        $this->response([
            'status' => 'Authorization failed'
        ], REST_Controller::HTTP_FORBIDDEN);
    }
}
