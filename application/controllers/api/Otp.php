<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//To Solve File REST_Controller not found
require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Otp extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
        $config = [
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'protocol'  => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_user' => '91rezao@gmail.com',  // Email gmail
            'smtp_pass'   => 'jao12345678',  // Password gmail
            'smtp_crypto' => 'ssl',
            'smtp_port'   => 465,
            'crlf'    => "\r\n",
            'newline' => "\r\n"
        ];
        $this->load->library('email', $config);
        $this->email->from('91rezao@gmail.com', 'APInjaMan');
    }

    public function request_post(){
        $data = [
            'phone' => $this->post('phone'),
            'email' => $this->post('email')
        ];

        foreach ($data as $key => $value) {
            if (!isset($value)) {
                $this->response([
                    'status' => 'Error',
                    'message' => 'Invalid data'
                ], REST_Controller::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $user = $this->user_model->get_user_by_email_phone($data['email'],$data['phone']);
        if ($user){
            $otp = mt_rand(100000, 999999);
            $otp_expired = new DateTime();
            $otp_expired=$otp_expired->modify('+20 minutes')->format('Y-m-d H:i:s');

            $emailUpdate = $this->user_model->update_otp($data['email'],$data['phone'], $otp, $otp_expired);
            
            // send email
            $this->email->to($user['email']);
            $this->email->subject("Your OTP code");
            $this->email->message("Your otp code: $otp");
            $message = "OTP sent";
            if(!$this->email->send()){
                $message = "OTP not sent";
            }
            $this->response([
                'message' => $message,
                'data'=> [
                    'nik' => $user['nik'],
                    'email' => $data['email']
                ]
            ]); 
            
        }else{
            $this->response([
                'message' => 'Not found',
            ]);
        }
    }

    
}
