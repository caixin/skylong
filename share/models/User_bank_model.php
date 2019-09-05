<?php defined('BASEPATH') || exit('No direct script access allowed');

class User_bank_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'user_bank';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'user_name', 'label' => '用户名称', 'rules' => "trim|required|callback_user_name_check"],
            ['field' => 'bank_real_name', 'label' => '银行卡姓名', 'rules' => "trim|required"],
            ['field' => 'bank_name', 'label' => '银行名称', 'rules' => "trim|required"],
            ['field' => 'bank_account', 'label' => '银行卡账号', 'rules' => "trim|required"],
            ['field' => 'bank_address', 'label' => '开户地址', 'rules' => "trim|required"],
        ];
    }

    public function edit_rules()
    {
        return [
            ['field' => 'bank_real_name', 'label' => '银行卡姓名', 'rules' => "trim|required"],
            ['field' => 'bank_name', 'label' => '银行名称', 'rules' => "trim|required"],
            ['field' => 'bank_account', 'label' => '银行卡账号', 'rules' => "trim|required"],
            ['field' => 'bank_address', 'label' => '开户地址', 'rules' => "trim|required"],
        ];
    }

    public function _do_where()
    {
        if (isset($this->_where['operator_id'])) {
            $this->db->where('t1.operator_id', $this->_where['operator_id']);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            //篩選運營商
            foreach ($this->_join as $arr) {
                if (strpos($arr[0], $this->table_.'user ') !== false) {
                    $table = trim(str_replace($this->table_.'user ', '', $arr[0]));
                    $this->db->where_in("$table.operator_id", $this->session->userdata('show_operator'));
                    break;
                }
            }
        }
        if ($this->session->userdata('is_agent') == 1) {
            $this->db->where('t1.agent_id', $this->session->userdata('id'));
        }
        if (isset($this->_where['user_name'])) {
            $this->db->where('t1.user_name', $this->_where['user_name']);
            unset($this->_where['user_name']);
        }
        
        if (isset($this->_where['mobile'])) {
            $this->db->where('t1.mobile', $this->_where['mobile']);
            unset($this->_where['mobile']);
        }
        if (isset($this->_where['bank_account'])) {
            $this->db->where('t.bank_account', $this->_where['bank_account']);
            unset($this->_where['bank_account']);
        }
        return $this;
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'             => '编号',
        'uid'            => '用户ID',
        'bank_real_name' => '银行卡姓名',
        'bank_name'      => '银行名称',
        'bank_account'   => '银行卡账号',
        'bank_address'   => '开户地址',
    ];
}
