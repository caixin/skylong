<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ipmanage_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'ipmanage';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'ip', 'label' => 'IP', 'rules' => 'trim|required'],
        ];
    }

    //查詢
    public function _do_where()
    {
        if (isset($this->_where['ip'])) {
            $this->db->like('t.ip', $this->_where['ip'], 'both');
            unset($this->_where['ip']);
        }
        if (isset($this->_where['note'])) {
            $this->db->like('t.note', $this->_where['note'], 'both');
            unset($this->_where['note']);
        }
        return $this;
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'   => '编号',
        'ip'   => 'IP',
        'note' => '备注',
    ];
}
