<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_classic_odds_control_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'ettm_classic_odds_control';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'lottery_id', 'label' => '彩种ID', 'rules' => 'trim|required'],
            ['field' => 'qishu', 'label' => '期数', 'rules' => 'trim|required'],
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

        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        if (isset($this->_where['wanfa_detail_id'])) {
            $this->db->where('t.wanfa_detail_id', $this->_where['wanfa_detail_id']);
            unset($this->_where['wanfa_detail_id']);
        }
        if (isset($this->_where['qishu'])) {
            $this->db->where('t.qishu', $this->_where['qishu']);
            unset($this->_where['qishu']);
        }
        if (isset($this->_where['is_special'])) {
            $this->db->where('t.is_special', $this->_where['is_special']);
            unset($this->_where['is_special']);
        }
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
    }

    /**
     * 設定控盤
     * @param int $operator_id 營運商ID
     * @param int $lottery_id 彩種ID
     * @param int $qishu 期數
     * @param int $wanfa_detail_id 玩法明細ID
     * @param int $is_special 是否為特殊賠率
     * @param int $interval 降賠區間
     * @param int $value 調整值
     */
    public function setAdject($operator_id, $lottery_id, $qishu, $wanfa_detail_id, $is_special, $interval, $value)
    {
        $where = [
            'operator_id'     => $operator_id,
            'lottery_id'      => $lottery_id,
            'qishu'           => $qishu,
            'wanfa_detail_id' => $wanfa_detail_id,
            'is_special'      => $is_special,
            'interval'        => $interval
        ];
        $row = $this->where($where)->result_one();
        
        if ($row !== null) {
            $this->update([
                'id'     => $row['id'],
                'adjust' => bcadd($row['adjust'], $value, 3),
            ]);
        } else {
            $insert = $where;
            $insert['adjust'] = $value;
            $this->insert($insert);
        }
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'              => '编号',
        'lottery_id'      => '彩种ID',
        'wanfa_detail_id' => '玩法明細ID',
        'qishu'           => '期数',
        'is_special'      => '是否特殊赔率',
        'interval'        => '降赔区间',
        'adjust'          => '调整赔率',
    ];
}
