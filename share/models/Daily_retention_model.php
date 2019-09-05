<?php defined('BASEPATH') || exit('No direct script access allowed');

class Daily_retention_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'daily_retention';
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
        1 => '1日內有登入',
        2 => '3日內有登入',
        3 => '7日內有登入',
        4 => '15日內有登入',
        5 => '30日內有登入',
        6 => '31日以上未登入',
    ];
    
    public static $typeColor = [
        1 => 'green',
        2 => 'yellow',
        3 => 'blue',
        4 => 'orange',
        5 => 'red',
        6 => 'gray',
    ];
    
    public static $typeLight = [
        1 => '绿灯(Green)',
        2 => '黄灯(Yellow)',
        3 => '蓝灯(Blue)',
        4 => '橘灯(Orange)',
        5 => '红灯(Red)',
        6 => '灰灯(Gray)',
    ];
    
    public static $analysis_typeList = [
        1 => '创帐号当日有登入',
        2 => '创帐号1日以上有登入',
        3 => '创帐号3日以上有登入',
        4 => '创帐号7日以上有登入',
        5 => '创帐号15日以上有登入',
        6 => '创帐号30日以上有登入',
        7 => '创帐号60日以上有登入',
        8 => '创帐号90日以上有登入',
        9 => '创帐号在今天往前7日内还有登入',
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
