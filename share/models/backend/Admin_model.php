<?php defined('BASEPATH') || exit('No direct script access allowed');

class Admin_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'admin';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'username', 'label' => '用户名称', 'rules' => "trim|required|callback_username_check"],
            ['field' => 'password', 'label' => '用户密码', 'rules' => 'trim|required|min_length[6]|max_length[12]'],
            ['field' => 'mobile', 'label' => '手机号码', 'rules' => "trim|required|min_length[11]|max_length[11]|is_unique[$this->_table_name.mobile]"],
            ['field' => 'roleid', 'label' => '角色群组', 'rules' => 'trim|required'],
        ];
    }

    public function insert($row, $return_string = false)
    {
        if (isset($row['password'])) {
            if ($row['password'] != '') {
                $row['password'] = strtoupper(md5($row['password']));
            } else {
                unset($row['password']);
            }
        }

        return parent::insert($row, $return_string);
    }

    public function update($row, $return_string = false)
    {
        if (isset($row['password'])) {
            if ($row['password'] != '') {
                $row['password'] = strtoupper(md5($row['password']));
            } else {
                unset($row['password']);
            }
        }

        return parent::update($row, $return_string);
    }

    //查詢
    public function _do_where()
    {
        if (isset($this->_where['operator'])) {
            $this->db->where('find_in_set(' . $this->_where['operator'] . ',t1.allow_operator) >', 0, false);
            unset($this->_where['operator']);
        }
        if (isset($this->_where['username'])) {
            $this->db->where('t.username', $this->_where['username']);
            unset($this->_where['username']);
        }

        if (isset($this->_where['mobile'])) {
            $this->db->where('t.mobile', $this->_where['mobile']);
            unset($this->_where['mobile']);
        }

        if (isset($this->_where['create_time1'])) {
            $this->db->where('t.create_time >=', $this->_where['create_time1'] . ' 00:00:00');
            unset($this->_where['create_time1']);
        }

        if (isset($this->_where['create_time2'])) {
            $this->db->where('t.create_time <=', $this->_where['create_time2'] . ' 23:59:59');
            unset($this->_where['create_time2']);
        }

        return $this;
    }

    public function getAgentList()
    {
        $result = $this->where(['is_agent' => 1])->result();

        return array_column($result, 'username', 'id');
    }

    public static $is_agentList = [
        1 => '是',
        0 => '否',
    ];

    public static $otp_checkList = [
        1 => '开启',
        0 => '关闭',
    ];

    public static $statusList = [
        1 => '开启',
        0 => '关闭',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'        => '编号',
        'username'  => '用户名称',
        'mobile'    => '手机号码',
        'roleid'    => '角色群组',
        'status'    => '状态',
        'otp_check' => 'OTP',
    ];
}
