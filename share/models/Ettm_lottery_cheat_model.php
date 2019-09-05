<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_lottery_cheat_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'ettm_lottery_cheat';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'lottery_id', 'label' => '彩种名称', 'rules' => 'trim|required'],
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['operator_id'])) {
            $this->db->where('t.operator_id', $this->_where['operator_id']);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            $this->db->where_in("t.operator_id", $this->session->userdata('show_operator'));
        }
        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
        }
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
    }

    public function groupList($result)
    {
        $data = [];
        foreach (self::$typeList as $key => $val) {
            $data[$key] = [];
        }
        foreach ($result as $row) {
            $data[$row['type']][] = $row;
        }
        ksort($data);
        return $data;
    }

    public static $typeUrl = [
        0 => 'index',
        1 => 'triple',
        2 => 'numbers',
        3 => 'probability',
    ];

    public static $typeList = [
        0 => '控制获利',
        1 => '控制不开豹子',
        2 => '控制开奖号码',
        3 => '控制必赢机率',
    ];

    public static $status0List = [
        0 => '关闭',
        1 => '吃大赔小',
        2 => '吃小赔大',
    ];

    public static $statusList = [
        0 => '关闭',
        1 => '开启',
    ];

    public static $status2List = [
        0 => '未使用',
        1 => '已使用',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'         => '编号',
        'type'       => '类型',
        'lottery_id' => '彩种ID',
        'qishu'      => '期数',
        'numbers'    => '开奖号码',
        'starttime'  => '起始时间',
        'endtime'    => '结束时间',
        'percent'    => '機率(%)',
        'status'     => '狀態',
    ];
}
