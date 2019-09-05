<?php defined('BASEPATH') || exit('No direct script access allowed');

class Apps_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'apps';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'name', 'label' => '应用名称', 'rules' => 'trim|required'],
            ['field' => 'jump_url', 'label' => '跳转URL(H5网页地址)', 'rules' => 'trim|required|callback_jump_url_check'],
            ['field' => 'download_url', 'label' => '下载URL', 'rules' => 'trim|required'],
        ];
    }

    //查詢
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
        if (isset($this->_where['is_vip'])) {
            $this->db->where('t.is_vip', $this->_where['is_vip']);
            unset($this->_where['is_vip']);
        }

        if ($this->is_login) {
            $this->db->where_in('t.operator_id', $this->session->userdata('show_operator'));
        }

        return $this;
    }

    public static $typeList = [
        1 => 'android',
        2 => 'ios',
    ];

    public static $is_vipList = [
        1 => '是',
        0 => '否',
    ];

    public static $statusList = [
        0 => '关闭',
        1 => '开启',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'           => '编号',
        'operator_id'  => '运营商名称',
        'type'         => '应用类型',
        'name'         => '应用名称',
        'jump_url'     => '跳转URL(H5网页地址)',
        'download_url' => '下载URL',
        'downloads'    => '下载次数',
        'is_vip'       => '是否为VIP包',
        'status'       => '状态',
        'update_time'  => '更新时间',
    ];
}
