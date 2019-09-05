<?php defined('BASEPATH') || exit('No direct script access allowed');

class Bank_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'bank';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'name', 'label' => '名称', 'rules' => 'trim|required'],
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
    }

    public function getList()
    {
        $result = $this->result();
        return array_column($result, 'name', 'id');
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'        => '编号',
        'name'      => '名称',
        'image_url' => '图片',
    ];
}
