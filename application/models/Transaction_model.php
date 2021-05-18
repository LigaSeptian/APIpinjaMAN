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

    public function get_transactions_history_by_nik($nik)
    {
        return $this->db->get_where($this->table_name, ['nik' => $nik, 'status' => 'dibayar'])->result_array();
    }

    public function get_transactions_pending()
    {
        return $this->db->get_where($this->table_name, ['status' => 'menunggu konfirmasi'])->result_array();
    }
    public function get_transactions_accepted()
    {
        return $this->db->get_where($this->table_name, ['status' => 'dibayar'])->result_array();
    }
    public function get_transactions_rejected()
    {
        return $this->db->get_where($this->table_name, ['status' => 'ditolak'])->result_array();
    }

    public function set_transaction_pending($id, $payment_time)
    {
        $this->db->where('id', $id);
        $this->db->update($this->table_name, [
            'status' => 'menunggu konfirmasi',
            'waktu_pembayaran' => $payment_time
        ]);
    }
}
