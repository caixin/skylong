<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction_robot_setting_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'prediction_robot_setting';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'lottery_id', 'label' => '彩種', 'rules' => 'trim|required'],
            ['field' => 'axis_y', 'label' => 'Y轴上限金额', 'rules' => 'trim|required'],
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
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        return $this;
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'            => '编号',
        'operator_id'   => '运营商ID',
        'lottery_id'    => '彩种',
        'axis_y'        => 'Y轴上限金额',
        'total_formula' => '投注总额计算公式',
        'bet_formula'   => '下注公式',
    ];
}
