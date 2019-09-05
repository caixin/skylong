<?php defined('BASEPATH') || exit('No direct script access allowed');

class News_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'news';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'type', 'label' => '文章类型', 'rules' => 'trim|required'],
            ['field' => 'title', 'label' => '标题', 'rules' => 'trim|required'],
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
        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
        }
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        if (isset($this->_where['title'])) {
            $this->db->where('t.title', $this->_where['title']);
            unset($this->_where['title']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }
    }

    public static $typeList = [
        1 => '经典玩法',
        2 => '官方玩法',
        3 => '特色玩法',
        4 => '其它',
    ];

    public static $statusList = [
        1 => '开启',
        0 => '关闭',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'          => '编号',
        'operator_id' => '运营商编号',
        'type'        => '文章类型',
        'lottery_id'  => '彩种ID',
        'title'       => '标题',
        'introduce'   => '简介',
        'content_wap' => 'Wap文章内容',
        'content_pc'  => 'Pc文章内容',
        'sort'        => '排序',
        'status'      => '状态',
        'is_delete'   => '是否删除',
    ];
}
