<?php defined('BASEPATH') || exit('No direct script access allowed');

class Advertise_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'advertise';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'name', 'label' => '广告名称', 'rules' => 'trim|required'],
            ['field' => 'key_word', 'label' => 'key_word', 'rules' => 'trim|required'],
            ['field' => 'sort', 'label' => '排序', 'rules' => 'trim|required|integer'],
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
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
        }
        if (isset($this->_where['key_word'])) {
            $this->db->where('t.key_word', $this->_where['key_word']);
            unset($this->_where['key_word']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }
    }

    public static $typeList = [
        1 => 'Wap-首页上方LOGO',
        2 => 'Wap-首页弹窗广告',
        101 => 'PC模板1-左门神',
        102 => 'PC模板1-右门神',
        103 => 'PC模板1-首页大弹窗',
        104 => 'PC模板1-首页LOGO',
        105 => 'PC模板1-首页大背景',
        106 => 'PC模板1-首页人像图',
        107 => 'PC模板1-首页游戏类别圆形图',
        108 => 'PC模板1-首页平台优点长条图',
        109 => 'PC模板1-首页证书狮子图',
        110 => 'PC模板1-优惠活动标题图',
        111 => 'PC模板1-手机QRCode',
        201 => 'PC模板2-首页QRCode',
    ];

    public static $statusList = [
        1 => '开启',
        0 => '关闭',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'          => '编号',
        'operator_id' => '运营商编号',
        'name'        => '广告名称',
        'type'        => '广告位置',
        'pic'         => '广告图片',
        'pic_url'     => '广告图片指向地址',
        'key_word'    => 'key_word',
        'sort'        => '排序',
        'status'      => '状态',
    ];
}
