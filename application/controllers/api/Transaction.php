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
}
