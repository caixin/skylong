<?php defined('BASEPATH') || exit('No direct script access allowed');

class Recharge_online_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'recharge_online';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'interface', 'label' => '接口', 'rules' => "trim|required"],
            ['field' => 'payment', 'label' => '付款类型', 'rules' => "trim|required"],
            ['field' => 'pay_url', 'label' => 'API网址', 'rules' => "trim|required"],
            ['field' => 'm_num', 'label' => '商户号', 'rules' => "trim|required"],
            ['field' => 'secret_key', 'label' => '密钥', 'rules' => "trim|required"],
            ['field' => 'moneys', 'label' => '面额', 'rules' => "trim|required"],
            ['field' => 'min_money', 'label' => '单笔最小限额', 'rules' => "trim|required|min_length[1]"],
            ['field' => 'max_money', 'label' => '单笔最大限额', 'rules' => "trim|required|min_length[1]"],
            ['field' => 'day_max_money', 'label' => '单日最大限额', 'rules' => "trim|required|min_length[1]"],
            ['field' => 'handsel_percent', 'label' => '赠送彩金比例', 'rules' => "trim|required|min_length[0]"],
        ];
    }

    public function _do_where()
    {
        unset($this->_where['sidebar']);
        if (isset($this->_where['user_group_ids'])) {
            $this->db->where('find_in_set(' . $this->_where['user_group_ids'] . ',t.user_group_ids) >', 0, false);
            unset($this->_where['user_group_ids']);
        }
        if (isset($this->_where['interface'])) {
            $this->db->where('t.interface', $this->_where['interface']);
            unset($this->_where['interface']);
        }
        if (isset($this->_where['payment'])) {
            $this->db->where('t.payment', $this->_where['payment']);
            unset($this->_where['payment']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }
        return $this;
    }

    public static $interfaceList = [
        1 => '橘子支付',
    ];

    public static $paymentList = [
        1 => '支付宝',
        2 => '微信',
    ];

    public static $paymentIcon = [
        1 => 'https://cpdd.oss-cn-beijing.aliyuncs.com/chongzhi/zfb.png',
        2 => 'https://cpdd.oss-cn-beijing.aliyuncs.com/chongzhi/wx.png',
    ];

    public static $statusList = [
        1 => '开启',
        0 => '关闭',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'              => '编号',
        'user_group_ids'  => '用户分层',
        'interface'       => '接口',
        'payment'         => '付款类型',
        'payment_logo'    => '付款类型LOGO',
        'pay_url'         => 'API网址',
        'm_num'           => '商户号',
        'secret_key'      => '密钥',
        'moneys'          => '面额',
        'handsel_percent' => '赠送彩金比例',
        'handsel_max'     => '赠送彩金上限',
        'multiple'        => '打码量倍数',
        'min_money'       => '单笔最小限额',
        'max_money'       => '单笔最大限额',
        'day_max_money'   => '单日最大限额',
        'remark'          => '备注',
        'status'          => '状态',
        'sort'            => '排序',
        'is_delete'       => '是否删除',
    ];
}
