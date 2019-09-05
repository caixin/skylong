<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_lottery_dayoff_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'ettm_lottery_dayoff';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'lottery_id', 'label' => '彩种', 'rules' => 'trim|required'],
            ['field' => 'dayoff', 'label' => '未开奖日期', 'rules' => 'trim|required']
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        if (isset($this->_where['dayoff1'])) {
            $this->db->where('t.dayoff >=', $this->_where['dayoff1']);
            unset($this->_where['dayoff1']);
        }

        if (isset($this->_where['dayoff2'])) {
            $this->db->where('t.dayoff <=', $this->_where['dayoff2']);
            unset($this->_where['dayoff2']);
        }
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'         => '编号',
        'lottery_id' => '彩种',
        'dayoff'     => '未开奖日期',
    ];
}
