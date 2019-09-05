<?php defined('BASEPATH') || exit('No direct script access allowed');

class Header_action_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'header_action';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'title', 'label' => '标题', 'rules' => 'trim|required'],
            ['field' => 'icon', 'label' => '图片', 'rules' => 'trim|required'],
            ['field' => 'jump_url', 'label' => '跳转地址', 'rules' => 'trim|required'],
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['mode'])) {
            $this->db->where('t.mode &', $this->_where['mode']);
            unset($this->_where['mode']);
        }
        if (isset($this->_where['title'])) {
            $this->db->like('t.title', $this->_where['title'], 'both');
            unset($this->_where['title']);
        }
        if (isset($this->_where['jump_url'])) {
            $this->db->like('t.jump_url', $this->_where['jump_url'], 'both');
            unset($this->_where['jump_url']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }
    }

    public static $modeList = [
        1 => '浏览器',
        2 => 'APP',
    ];

    public static $statusList = [
        0 => '关闭',
        1 => '开启',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'       => '编号',
        'title'    => '标题',
        'icon'     => '图片',
        'jump_url' => '跳转地址',
        'status'   => '状态',
    ];
}
