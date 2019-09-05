<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_lottery_record_change_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_db_default = 'ettm_lottery_record_db';
        $this->load->model('ettm_lottery_record_model', $this->_db_default);

        $this->_table_default = $this->table_ . 'ettm_lottery_record';
        $this->_related_key = 'record_id';
        $this->_table_name = $this->table_ . 'ettm_lottery_record_change';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'status', 'label' => '狀態', 'rules' => 'trim|required']
        ];
    }

    public function update($row, $return_string = false)
    {
        $data = $this->where([
            'operator_id' => $row['operator_id'],
            'record_id'   => $row['record_id'],
        ])->result_one();

        if ($data === null) {
            return parent::insert($row, $return_string);
        } else {
            $row['id'] = $data['id'];
            return parent::update($row, $return_string);
        }
    }

    public function _do_where()
    {
        unset($this->_where['operator_id']);

        if (isset($this->_where['qishu'])) {
            $this->db->where('default.qishu', $this->_where['qishu']);
            unset($this->_where['qishu']);
        }

        if (isset($this->_where['record_id'])) {
            $this->db->where('t.record_id', $this->_where['record_id']);
            unset($this->_where['record_id']);
        }

        if (isset($this->_where['record_ids'])) {
            $this->db->where_in('t.record_id', $this->_where['record_ids']);
            unset($this->_where['record_ids']);
        }
    }

    /**
     * 取得更換號碼
     */
    public function getNumber($operator_id, $record_id)
    {
        $row = $this->where([
            'operator_id' => $operator_id,
            'record_id'   => $record_id,
        ])->result_one;

        return $row === null ? '':$row['numbers'];
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'          => '编号',
        'operator_id' => '运营商ID',
        'record_id'   => '开奖结果ID',
        'numbers'     => '开奖号码',
    ];
}
