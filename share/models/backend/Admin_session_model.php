<?php defined('BASEPATH') || exit('No direct script access allowed');

class Admin_session_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'admin_session';
        $this->_key = 'adminid';
    }

    public function insert($row, $return_string = false)
    {
        $date = date('Y-m-d H:i:s');
        $data = $row;
        $data['create_time'] = $date;

        if ($return_string) {
            return $this->db->insert_string($this->_table_name, $data);
        } else {
            $this->db->insert($this->_table_name, $data);
            return $this->db->insert_id();
        }
    }
}
