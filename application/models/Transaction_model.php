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
}
