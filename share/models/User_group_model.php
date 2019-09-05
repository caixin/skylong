<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_group_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'user_group';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'name', 'label' => '分层名称', 'rules' => "trim|required"],
            ['field' => 'min_extract_money', 'label' => '单次取款最小限额', 'rules' => "trim|required"],
            ['field' => 'max_extract_money', 'label' => '单次取款最大限额', 'rules' => "trim|required"],
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['operator_id'])) {
            if ($this->_where['operator_id'] != 0) {
                $this->db->where('t.operator_id', $this->_where['operator_id']);
            }
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            $this->db->where_in("t.operator_id", $this->session->userdata('show_operator'));
        }
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
        if (isset($this->_where['is_default'])) {
            $this->db->where('t.is_default', $this->_where['is_default']);
            unset($this->_where['is_default']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }
        return $this;
    }

    public function getList($operator_id=0)
    {
        $where['status'] = 1;
        if ($operator_id > 0) {
            $where['operator_id'] = $operator_id;
        }
        $result = $this->where($where)->result();
        return array_column($result, 'name', 'id');
    }

    public function getListGroup()
    {
        $result = $this->where(['status'=>1])->result();
        $data = [];
        foreach ($result as $row) {
            $data[$row['operator_id']][$row['id']] = $row['name'];
        }
        return $data;
    }

    /**
     * 將該分層會員設為默認分層
     *
     * @param integer $id 分層ID
     */
    public function setDefaultID($id)
    {
        $this->load->model('user_model', 'user_db');
        $row = $this->row($id);
        $arr = $this->where([
            'operator_id' => $row['operator_id'],
            'is_default'  => 1,
        ])->result_one();

        $this->user_db->where([
            'user_group_id' => $id
        ])->update_where([
            'user_group_id' => $arr['id']
        ]);
    }

    public static $statusList = [
        1 => '开启',
        0 => '关闭',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'                => '编号',
        'name'              => '分层名称',
        'min_extract_money' => '单次最小提现额度',
        'max_extract_money' => '单次最大提现额度',
        'remark'            => '备注',
        'status'            => '状态',
    ];
}
