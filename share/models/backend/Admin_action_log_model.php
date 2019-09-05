<?php defined('BASEPATH') || exit('No direct script access allowed');

class Admin_action_log_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'admin_action_log';
        $this->_key = 'id';
    }

    public function insert($row, $return_string = false)
    {
        $row['adminid'] = $this->session->userdata('id');
        $row['url']     = $this->cur_url;
        $row['ip']      = $this->input->ip_address();

        return parent::insert($row, $return_string);
    }

    //查詢
    public function _do_where()
    {
        if (isset($this->_where['create_by'])) {
            $this->db->like('t.create_by', $this->_where['create_by'], 'both');
            unset($this->_where['create_by']);
        }

        if (isset($this->_where['ip'])) {
            $this->db->where('t.ip', $this->_where['ip']);
            unset($this->_where['ip']);
        }

        if (isset($this->_where['url'])) {
            $this->db->like('t.url', $this->_where['url'], 'both');
            unset($this->_where['url']);
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

    public static $statusList = [
        1 => '成功',
        0 => '失败',
    ];
}
