<?php defined('BASEPATH') || exit('No direct script access allowed');

class Code_amount_assign_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'code_amount_assign';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'code_amount_id', 'label' => '', 'rules' => "trim|required"],
        ];
    }

    public function _do_where()
    {
        unset($this->_where['sidebar']);
        if (isset($this->_where['code_amount_id'])) {
            $this->db->where('t.code_amount_id', $this->_where['code_amount_id']);
            unset($this->_where['code_amount_id']);
        }

        if (isset($this->_where['code_amount_log_id'])) {
            $this->db->where('t.code_amount_log_id', $this->_where['code_amount_log_id']);
            unset($this->_where['code_amount_log_id']);
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

    //操作日誌欄位轉換
    public static $columnList = [
        'id'                 => '编号',
        'code_amount_id'     => '打码量ID',
        'code_amount_log_id' => '打码量LogID',
        'code_amount_use'    => '有效打码量',
    ];
}
