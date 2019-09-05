<?php defined('BASEPATH') || exit('No direct script access allowed');

class Daily_analysis_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'daily_analysis';
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
        1 => '每日添加用户数(NUU)',
        2 => '每日不重覆登入用户数(DAU)',
        3 => '一周不重覆登入用户数(WAU)',
        4 => '当月不重覆登入用户数(MAU)',
        5 => 'DAU - NUU',
        6 => '每日最大在线用户数(PCU)',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'          => '编号',
        'operator_id' => '营运商ID',
        'day_time'    => '日期',
        'type'        => '类型',
        'count'       => '人数',
    ];
}
