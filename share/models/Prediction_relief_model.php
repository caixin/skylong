<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction_relief_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'prediction_relief';
        $this->_key = 'id';
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
        if (isset($this->_where['ids'])) {
            $this->db->where_in('t.id', $this->_where['ids']);
            unset($this->_where['ids']);
        }
        if (isset($this->_where['uid'])) {
            $this->db->where('t.uid', $this->_where['uid']);
            unset($this->_where['uid']);
        }
        if (isset($this->_where['prediction_id'])) {
            $this->db->where('t.prediction_id', $this->_where['prediction_id']);
            unset($this->_where['prediction_id']);
        }
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t2.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        if (isset($this->_where['qishu'])) {
            $this->db->where('t.qishu', $this->_where['qishu']);
            unset($this->_where['qishu']);
        }
        if (isset($this->_where['user_name'])) {
            $this->db->like('t1.user_name', $this->_where['user_name'], 'both');
            unset($this->_where['user_name']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }
    }

    /**
     * 更新救濟金狀態，到期的改成已失效
     *
     * @return void
     */
    public function updateInvalid()
    {
        $this->where([
            'status !='     => 3,
            'expire_time <' => date("Y-m-d H:i:s")
        ])->update_where([
            'status' => 3
        ]);
    }

    /**
     * 充值救濟金
     *
     * @param integer $uid 用戶ID
     * @param integer $recharge_order_id 充值ID
     * @param integer $reacharge_money 充值金額
     * @return void
     */
    public function recharge($uid, $recharge_order_id, $reacharge_money)
    {
        $this->load->model('prediction_assign_model', 'prediction_assign_db');
        $this->updateInvalid();

        $result = $this->where([
            't.uid'    => $uid,
            't.status' => 0,
        ])->order(['create_time','asc'])->result();
        $this->trans_start();
        foreach ($result as $row) {
            $need = $row['bet_money'] - $row['recharge'];
            $use = $reacharge_money > $need ? $need:$reacharge_money;
            $reacharge_money -= $use;
            $this->update([
                'id'       => $row['id'],
                'recharge' => $row['recharge'] + $use,
                'status'   => $need == $use ? 1:0,
            ]);
            $this->prediction_assign_db->insert([
                'recharge_order_id'    => $recharge_order_id,
                'prediction_relief_id' => $row['id'],
                'reacharge_use'        => $use,
            ]);
            //分配完畢則跳離
            if ($reacharge_money == 0) {
                break;
            }
        }
        $this->trans_complete();
    }

    public static $statusList = [
        0 => '待激活',
        1 => '激活成功',
        2 => '已提取',
        3 => '已失效',
    ];
}
