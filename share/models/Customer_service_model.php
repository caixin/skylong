<?php defined('BASEPATH') || exit('No direct script access allowed');

class Customer_service_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'customer_service';
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
        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
        }
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
        if (isset($this->_where['account'])) {
            $this->db->like('t.account', $this->_where['account'], 'both');
            unset($this->_where['account']);
        }
    }

    public static $typeList = [
        1 => '在线客服',
        2 => '微信',
        3 => 'QQ',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'        => '编号',
        'type'      => '类别',
        'name'      => '名称',
        'image_url' => '图片',
        'account'   => '帐号',
    ];
}
