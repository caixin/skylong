<?php defined('BASEPATH') || exit('No direct script access allowed');

class Api_log_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'api_log';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'url', 'label' => 'API網址', 'rules' => "trim|required"],
        ];
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

    public function update($row, $return_string = false)
    {
        $data = $row;
        unset($data[$this->_key]);

        if ($return_string) {
            return $this->db->update_string($this->_table_name, $data, [$this->_key => $row[$this->_key]]);
        } else {
            $this->db->update($this->_table_name, $data, [$this->_key => $row[$this->_key]]);
            return $this->db->affected_rows(); //影響列數
        }
    }

    public function _do_where()
    {
        if (isset($this->_where['uid'])) {
            $this->db->where('t.uid', $this->_where['uid']);
            unset($this->_where['uid']);
        }

        if (isset($this->_where['exec_time'])) {
            $this->db->where('t.exec_time >=', $this->_where['exec_time']);
            unset($this->_where['exec_time']);
        }

        if (isset($this->_where['create_time1'])) {
            $this->db->where('t.create_time >=', $this->_where['create_time1'] . ' 00:00:00');
            unset($this->_where['create_time1']);
        }

        if (isset($this->_where['create_time2'])) {
            $this->db->where('t.create_time <=', $this->_where['create_time2'] . ' 23:59:59');
            unset($this->_where['create_time2']);
        }
        return $this;
    }
}
