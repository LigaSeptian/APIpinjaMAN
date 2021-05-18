<?php
class User_model extends CI_Model
{
    private $table_name = 'users';
    function __construct()
    {
        parent::__construct();
    }

    public function add_user($data)
    {
        $this->db->insert($this->table_name, $data);
    }

    public function get_user_by_email($email)
    {
        return $this->db->get_where($this->table_name, ['email' => $email])->row_array();
    }

    public function get_user_by_email_phone($email,$phone)
    {
        return $this->db->get_where($this->table_name, ['email' => $email, 'no_telepon' => $phone])->row_array();
    }

    public function update_user_limit_remaining($nik, $limit_remaining)
    {
        $this->db->where('nik', $nik);
        $this->db->update($this->table_name, ['sisa_limit' => $limit_remaining]);
    }

    public function update_user_email($nik, $email)
    {
        $this->db->where('nik', $nik);
        $this->db->update($this->table_name, ['email' => $email]);
    }

    public function update_user_pin($nik, $pin)
    {
        $this->db->where('nik', $nik);
        $this->db->update($this->table_name, ['pin' => $pin]);
    }

    public function get_user_by_email_otp ($email, $otp)
    {
        return $this->db->get_where($this->table_name, ['email' => $email, 'otp' => $otp, 'otp_expired >=' => date('Y-m-d H:i:s') ])->row_array();
    }

    public function update_otp($email, $phone, $otp, $otp_expired)
    {
        $this->db->where(['email' => $email, 'no_telepon' => $phone]);
        $this->db->update($this->table_name, ['otp' => $otp, 'otp_expired' => $otp_expired]);
    }

    public function get_users_pending(){
        return $this->db->get_where($this->table_name, ['status' => 'waiting'])->result_array();
    }

    public function get_users_accepted(){
        return $this->db->get_where($this->table_name, ['status' => 'accepted','role' => 'user'])->result_array();
    }
    public function get_users_rejected(){
        return $this->db->get_where($this->table_name, ['status' => 'rejected','role' => 'user'])->result_array();
    }
}
 