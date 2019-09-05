<?php defined('BASEPATH') || exit('No direct script access allowed');

class Websocket_log_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'websocket_log';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'fd', 'label' => 'fd', 'rules' => "trim|required"],
        ];
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

        if (isset($this->_where['special_name'])) {
            $this->db->where('t2.type', $this->_where['special_name']);
            unset($this->_where['special_name']);
        }
        return $this;
    }
}
