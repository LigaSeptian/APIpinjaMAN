<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Admin extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('transaction_model');
    }

    public function pending_get()
    {
        function mapResultRegistration($array)
        {
            return [
                'type' => 'registration',
                'data' => [
                    'nik' => $array['nik'],
                    'registration_date' => $array['date_created']
                ]
            ];
        }
        function mapResultTransaction($array)
        {
            return [
                'type' => 'payment',
                'data' => [
                    'nik' => $array['nik'],
                    'transaction_id' => $array['id'],
                    'payment_deadline' => $array['waktu_pembayaran']
                ]
            ];
        }
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $this->user_model->get_user_by_email($_SERVER['PHP_AUTH_USER']);
            if ($user['role'] == 'admin' && password_verify($_SERVER['PHP_AUTH_PW'], $user['pin'])) {
                $transactions = $this->transaction_model->get_transactions_pending();
                $resultTransaction = array_map('mapResultTransaction', $transactions);
                $resultUser = array_map('mapResultRegistration', $this->user_model->get_users_pending());
                $result = array_merge($resultTransaction, $resultUser);
                $this->response($result);
            }
        }
        $this->response([
            'status' => 'Authorization failed'
        ], REST_Controller::HTTP_FORBIDDEN);
    }

    public function history_get()
    {
        function mapResultRegistrations($array)
        {
            return [
                'type' => 'registration',
                'data' => [
                    'nik' => $array['nik'],
                    'registration_date' => $array['date_created'],
                    'status' => $array['status']
                ]
            ];
        }
        function mapResultTransactions($array)
        {
            return [
                'type' => 'payment',
                'data' => [
                    'nik' => $array['nik'],
                    'transaction_id' => $array['id'],
                    'payment_deadline' => $array['waktu_pembayaran'],
                    'status' => $array['status']
                ]
            ];
        }
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $user = $this->user_model->get_user_by_email($_SERVER['PHP_AUTH_USER']);
            if ($user['role'] == 'admin' && password_verify($_SERVER['PHP_AUTH_PW'], $user['pin'])) {
                $transactionsAccepted = $this->transaction_model->get_transactions_accepted();
                $transactionsRejected = $this->transaction_model->get_transactions_rejected();
                $resultTransactionAccepted = array_map('mapResultTransactions', $transactionsAccepted);
                $resultTransactionRejected = array_map('mapResultTransactions', $transactionsRejected);

                $userAccepted = $this->user_model->get_users_accepted();
                $userRejected = $this->user_model->get_users_rejected();
                $resultUserAccepted = array_map('mapResultRegistrations', $userAccepted);
                $resultUserRejected = array_map('mapResultRegistrations', $userRejected);

                $result = array_merge($resultTransactionAccepted, $resultTransactionRejected, $resultUserAccepted, $resultUserRejected);

                $this->response($result);
            }
        }
        $this->response([
            'status' => 'Authorization failed'
        ], REST_Controller::HTTP_FORBIDDEN);
    }
}
