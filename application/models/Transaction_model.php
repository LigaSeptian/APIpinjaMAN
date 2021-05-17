<?php
class Transaction_model extends CI_Model
{
    private $table_name = 'transactions';
    function __construct()
    {
        parent::__construct();
    }

    public function add_transactions($data)
    {
        $this->db->insert($this->table_name, $data);
    }

    public function get_transactions_by_nik($nik)
    {
        return $this->db->get_where($this->table_name, ['nik' => $nik])->result_array();
    }
}
