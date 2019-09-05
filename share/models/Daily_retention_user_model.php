<?php defined('BASEPATH') || exit('No direct script access allowed');

class Daily_retention_user_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'daily_retention_user';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'day_time', 'label' => '日期', 'rules' => "trim|required"],
        ];
    }

    public function _do_where()
    {
        unset($this->_where['sidebar']);
        if (isset($this->_where['operator_id'])) {
            $this->db->where('t.operator_id', $this->_where['operator_id']);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            $this->db->where_in("t.operator_id", $this->session->userdata('show_operator'));
        }

        if (isset($this->_where['day_time'])) {
            $this->db->where('t.day_time', $this->_where['day_time']);
            unset($this->_where['day_time']);
        }
        if (isset($this->_where['day_time1'])) {
            $this->db->where('t.day_time >=', $this->_where['day_time1']);
            unset($this->_where['day_time1']);
        }
        if (isset($this->_where['day_time2'])) {
            $this->db->where('t.day_time <=', $this->_where['day_time2']);
            unset($this->_where['day_time2']);
        }
        return $this;
    }

    public static $typeList = [
        1 => '1天前新帐号，计算日有登入 / 1天前新帐号数',
        2 => '3天前新帐号，计算日有登入 / 1天前新帐号数',
        3 => '7天前新帐号，计算日有登入 / 1天前新帐号数',
        4 => '14天前新帐号，计算日有登入 / 1天前新帐号数',
        5 => '28天前新帐号，计算日有登入 / 1天前新帐号数',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'          => '编号',
        'operator_id' => '营运商ID',
        'day_time'    => '日期',
        'type'        => '类型',
        'all_count'   => '总数',
        'day_count'   => '人数',
    ];
}
