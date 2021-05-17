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
}
