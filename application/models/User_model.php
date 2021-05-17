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

    public function update_user_limit_remaining($nik, $limit_remaining)
    {
        $this->db->where('nik', $nik);
        $this->db->update($this->table_name, ['sisa_limit' => $limit_remaining]);
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
 