<?php defined('BASEPATH') || exit('No direct script access allowed');

class Notice_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'notice';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'name', 'label' => '公告名称', 'rules' => 'trim|required'],
            ['field' => 'content', 'label' => '公告内容', 'rules' => 'trim|required'],
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
            $this->db->where('t.name', $this->_where['name']);
            unset($this->_where['name']);
        }

        return $this;
    }

    public function close($id)
    {
        $data = $this->row($id);
        $data['status'] = ($data['status'] == 0) ? 1 : 0;

        parent::update($data);
    }

    public static $typeList = [
        1  => 'Wap系统公告',
        2  => 'Wap跑马灯通知',
        11 => 'PC跑马灯通知',
        12 => 'PC首页公告',
    ];

    public static $statusList = [
        0 => '关闭',
        1 => '开启',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'          => '编号',
        'operator_id' => '运营商名称',
        'type'        => '公告类型',
        'name'        => '公告名称',
        'content'     => '公告内容',
        'sort'        => '排序',
        'status'      => '状态',
        'update_time' => '更新时间',
    ];
}
