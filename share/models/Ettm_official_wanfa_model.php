<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_official_wanfa_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'ettm_official_wanfa';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'lottery_type_id', 'label' => '彩种类别', 'rules' => 'trim|required'],
            ['field' => 'name', 'label' => '玩法名称', 'rules' => 'trim|required'],
        ];
    }

    public function _do_where()
    {
        //預設排除刪除資料
        if (isset($this->_where['is_delete'])) {
            $this->db->where('t.is_delete', $this->_where['is_delete']);
            unset($this->_where['is_delete']);
        } else {
            $this->db->where('t.is_delete', 0);
        }

        if (isset($this->_where['lottery_type_id'])) {
            $this->db->where('t.lottery_type_id', $this->_where['lottery_type_id']);
            unset($this->_where['lottery_type_id']);
        }
        if (isset($this->_where['pid'])) {
            if ($this->_where['pid'] == -1) {
                $this->db->where('t.pid >', 0);
            } else {
                $this->db->where('t.pid', $this->_where['pid']);
            }
            unset($this->_where['pid']);
        }
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
    }

    public function getList($pid = 0)
    {
        $result = $this->where([
            'pid' => $pid
        ])->result();

        return array_column($result, 'name', 'id');
    }

    /**
     * 賠率運算
     * @param int $lottery_id 彩種ID
     * @param int $uid 用戶ID
     */
    public function oddsCalculation($lottery_id, $uid)
    {
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_classic_odds_control_model', 'ettm_classic_odds_control_db');
        $this->load->model('ettm_reduce_model', 'ettm_reduce_db');
        $this->load->model('agent_code_model', 'agent_code_db');
        $lottery = $this->ettm_lottery_db->row($lottery_id);

        //代理抽水
        $up_data = $this->agent_code_db->getUpReturnPoint($uid, $lottery_id);
        $agent = bcdiv(array_sum($up_data), 100, 5);

        $result = $this->where(['lottery_type_id' => $lottery['lottery_type_id']])
            ->order(['pid' => 'asc', 'sort' => 'asc'])->result();
        foreach ($result as $key => $row) {
            //A盤獲利
            $adjustment = $this->operator == [] ? 1:$this->operator['official_adjustment'];
            $line_a_profit = bcdiv(bcmul($row['line_a_profit'], $adjustment, 3), 100, 5);
            //公式: 滿盤 * (1 - (A盤獲利+代理返水))
            $max_return = bcdiv($row['max_return'], 100, 5);
            $row['min_odds'] = bcmul($row['odds'], bcsub(1, bcadd(bcadd($line_a_profit, $max_return, 5), $agent, 5), 5), 3);
            $row['max_odds'] = bcmul($row['odds'], bcsub(1, bcadd($line_a_profit, $agent, 5), 5), 3);

            $result[$key] = $row;
        }
        return $result;
    }

    public static $is_deleteList = [
        1 => '正常',
        0 => '已删除',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'              => '编号',
        'lottery_type_id' => '彩种类别',
        'pid'             => '父級ID',
        'name'            => '玩法名称',
        'line_a_profit'   => 'A盘获利(%)',
        'odds'            => '满盘赔率',
        'max_return'      => '最大返点',
        'max_bet_number'  => '最大注数',
        'max_bet_money'   => '最大投注额',
        'key_word'        => 'Keyword',
        'sort'            => '排序',
        'is_delete'       => '是否删除',
    ];
}
