<?php defined('BASEPATH') || exit('No direct script access allowed');

class Code_amount_log_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'code_amount_log';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'user_name', 'label' => '用户名称', 'rules' => "trim|required|callback_user_name_check"],
            ['field' => 'code_amount', 'label' => '变动打码量', 'rules' => "trim|required"],
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
                if (strpos($arr[0], $this->table_ . 'user ') !== false) {
                    $table = trim(str_replace($this->table_ . 'user ', '', $arr[0]));
                    $this->db->where_in("$table.operator_id", $this->session->userdata('show_operator'));
                    break;
                }
            }
        }
        if ($this->session->userdata('is_agent') == 1) {
            $this->db->where('t1.agent_id', $this->session->userdata('id'));
        }

        if (isset($this->_where['money_type'])) {
            $this->db->where('t.money_type', $this->_where['money_type']);
            unset($this->_where['money_type']);
        }

        if (isset($this->_where['user_name'])) {
            $this->db->like('t1.user_name', $this->_where['user_name'], 'both');
            unset($this->_where['user_name']);
        }

        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
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

    public function getBetRecord($category, $bet_record_id)
    {
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');

        $lottery = array_column($this->ettm_lottery_db->result(), 'name', 'id');
        $bet = null;
        if ($category == 1) {
            $bet = $this->ettm_classic_bet_record_db->row($bet_record_id);
        } elseif ($category == 2) {
            $bet = $this->ettm_official_bet_record_db->row($bet_record_id);
        }

        if ($bet !== null) {
            $bet['lottery_name'] = isset($lottery[$bet['lottery_id']]) ? $lottery[$bet['lottery_id']] : '';
        }

        return $bet;
    }

    /**
     * 打碼量統計
     * @param string $starttime 起始時間
     * @param string $endtime 結束時間
     * @param int $money_type 貨幣類型 0:現金帳戶 1:特色棋牌帳戶
     */
    public function getCodeAmountStats($starttime, $endtime, $money_type = 0)
    {
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->escape(false)->select('t1.agent_code,SUM(t.code_amount) amount')->where([
            't1.type'      => 0,
            'create_time1' => $starttime,
            'create_time2' => $endtime,
            'money_type'   => $money_type,
        ])->join($join)->group('t1.agent_code')->result();

        return array_column($result, 'amount', 'agent_code');
    }

    public static $typeList = [
        0 => '下注',
        1 => '人工加码',
        2 => '人工减码',
        3 => '退款',
    ];

    public static $categoryList = [
        0 => '无',
        1 => '經典彩',
        2 => '官方彩',
        3 => '特色棋牌',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'             => '编号',
        'code_amount_id' => '打码量ID',
        'type'           => '類型',
        'category'       => '玩法类别',
        'bet_record_id'  => '下注ID',
        'code_amount'    => '有效打码量',
        'description'    => '描述',
    ];
}
