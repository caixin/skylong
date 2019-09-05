<?php defined('BASEPATH') || exit('No direct script access allowed');

class Concurrent_user_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'concurrent_user';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'operator_id', 'label' => '營運商ID', 'rules' => 'trim|required'],
        ];
    }

    //查詢
    public function _do_where()
    {
        unset($this->_where['hidden']);
        if (isset($this->_where['operator_id'])) {
            $this->db->where('t.operator_id', $this->_where['operator_id']);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            $this->db->where_in("t.operator_id", $this->session->userdata('show_operator'));
        }

        if (isset($this->_where['minute_time1'])) {
            $this->db->where('t.minute_time >=', $this->_where['minute_time1']);
            unset($this->_where['minute_time1']);
        }
        if (isset($this->_where['minute_time2'])) {
            $this->db->where('t.minute_time <=', $this->_where['minute_time2']);
            unset($this->_where['minute_time2']);
        }
        if (isset($this->_where['per'])) {
            $this->db->where('t.per', $this->_where['per']);
            unset($this->_where['per']);
        }

        return $this;
    }

    public static $perList = [
        1  => '每分钟',
        5  => '每5分钟',
        10 => '每10分钟',
        30 => '每30分钟',
        60 => '每60分钟',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'          => '编号',
        'operator_id' => '运营商编号',
        'minute_time' => '分鐘',
        'count'       => '人数',
    ];
}
