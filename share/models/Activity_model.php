<?php defined('BASEPATH') || exit('No direct script access allowed');

class Activity_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'activity';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'name', 'label' => '活动标题', 'rules' => 'trim|required'],
            ['field' => 'content', 'label' => '活动内容', 'rules' => 'trim|required'],
            ['field' => 'pic1', 'label' => '首页轮播图', 'rules' => 'trim|required'],
            ['field' => 'pic2', 'label' => '上传活动图(模板2)', 'rules' => 'trim|required'],
            ['field' => 'sort', 'label' => '排序', 'rules' => 'trim|required|integer'],
        ];
    }

    //查詢
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
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }

        return $this;
    }

    public static $typeList = [
        1 => 'Wap',
        2 => 'PC',
    ];

    public static $sourceType = [
        'wap'     => 1,
        'pc'      => 2,
        'android' => 1,
        'ios'     => 1,
    ];

    public static $statusList = [
        1 => '开启',
        0 => '关闭',
    ];

    public static $pic1_showList = [
        1 => '显示',
        0 => '隐藏',
    ];

    public static $pic2_showList = [
        1 => '显示',
        0 => '隐藏',
    ];

    public static $pic3_showList = [
        1 => '显示',
        0 => '隐藏',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'          => '编号',
        'operator_id' => '运营商编号',
        'name'        => '活动名称',
        'pic1'        => '首页轮播',
        'pic2'        => '活动图片(模板1)',
        'pic3'        => '活动图片(模板2)',
        'pic1_show'   => '是否顯示首页轮播',
        'pic2_show'   => '是否顯示活动图片(模板1)',
        'pic3_show'   => '是否顯示活动图片(模板2)',
        'brief'       => '简短描述',
        'type'        => '活动类型',
        'status'      => '活动状态',
        'sort'        => '排序',
        'content'     => '活动内容',
    ];
}
