<?php defined('BASEPATH') || exit('No direct script access allowed');

class Recharge_offline_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'recharge_offline';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'nickname', 'label' => '昵称', 'rules' => "trim|required"],
            ['field' => 'account', 'label' => '账号', 'rules' => "trim|required|callback_account_check"],
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
        if (isset($this->_where['channel'])) {
            $this->db->where('t.channel', $this->_where['channel']);
            unset($this->_where['channel']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }
        return $this;
    }

    public static $channelList = [
        1 => '银行卡',
        2 => '微信',
        3 => '支付宝',
    ];

    public static $channelIcon = [
        1 => 'https://cpdd.oss-cn-beijing.aliyuncs.com/chongzhi/yl.png',
        2 => 'https://cpdd.oss-cn-beijing.aliyuncs.com/chongzhi/wx.png',
        3 => 'https://cpdd.oss-cn-beijing.aliyuncs.com/chongzhi/zfb.png',
    ];

    public static $channelTip = [
        1 => 'line_recharge_bank_tip',
        2 => 'line_recharge_weixin_tip',
        3 => 'line_recharge_zhifubao_tip',
    ];

    public static $statusList = [
        1 => '开启',
        0 => '关闭',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'              => '编号',
        'user_group_ids'  => '用户分层',
        'channel'         => '渠道',
        'nickname'        => '昵称',
        'bank_id'         => '银行ID',
        'account'         => '账号/卡号',
        'qrcode'          => '二维码图片',
        'handsel_percent' => '赠送彩金比例',
        'handsel_max'     => '赠送彩金上限',
        'multiple'        => '打码量倍数',
        'min_money'       => '单笔最小限额',
        'max_money'       => '单笔最大限额',
        'day_max_money'   => '单日最大限额',
        'status'          => '状态',
        'is_delete'       => '是否删除',
    ];
}
