<?php defined('BASEPATH') || exit('No direct script access allowed');

class Daily_user_report_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'daily_user_report';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'day_time', 'label' => '日期', 'rules' => "trim|required"],
        ];
    }

    public function _do_where()
    {
        unset($this->_where['sidebar']);
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

        if (isset($this->_where['uid'])) {
            $this->db->where('t.uid', $this->_where['uid']);
            unset($this->_where['uid']);
        }
        if (isset($this->_where['user_name'])) {
            $this->db->where('t1.user_name', $this->_where['user_name']);
            unset($this->_where['user_name']);
        }

        if (isset($this->_where['category'])) {
            $this->db->where('t.category', $this->_where['category']);
            unset($this->_where['category']);
        }

        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }

        if (isset($this->_where['day_time1'])) {
            $this->db->where('t.day_time >=', $this->_where['day_time1']);
            unset($this->_where['day_time1']);
        }
        if (isset($this->_where['day_time2'])) {
            $this->db->where('t.day_time <=', $this->_where['day_time2']);
            unset($this->_where['day_time2']);
        }
        return $this;
    }

    public function statistics($date)
    {
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');

        $now = date('Y-m-d H:i:s');
        $result = $this->where(['day_time'=>$date])->result();
        $report = [];
        foreach ($result as $row) {
            $report["$row[category]-$row[lottery_id]-$row[uid]"] = $row;
        }

        $insert = $update = [];
        $where['status'] = 1;
        $where['create_time1'] = $date . ' 00:00:00';
        $where['create_time2'] = $date . ' 23:59:59';
        //經典注單
        $result = $this->ettm_classic_bet_record_db->escape(false)
            ->select('lottery_id,uid,SUM(total_p_value) bet_money,SUM(bet_number) bet_number,SUM(c_value) c_value,
                SUM(CASE is_lose_win WHEN 0 THEN total_p_value ELSE (CASE WHEN c_value - total_p_value > total_p_value THEN total_p_value ELSE c_value - total_p_value END) END) bet_eff')
            ->where($where)->group('lottery_id,uid')->result();
        foreach ($result as $row) {
            if (isset($report["1-$row[lottery_id]-$row[uid]"])) {
                $update[] = [
                    'id'          => $report["1-$row[lottery_id]-$row[uid]"]['id'],
                    'bet_number'  => $row['bet_number'],
                    'bet_money'   => $row['bet_money'],
                    'c_value'     => $row['c_value'],
                    'bet_eff'     => $row['bet_eff'],
                    'create_time' => $now,
                ];
            } else {
                $insert[] = [
                    'day_time'    => $date,
                    'category'    => 1,
                    'lottery_id'  => $row['lottery_id'],
                    'uid'         => $row['uid'],
                    'bet_number'  => $row['bet_number'],
                    'bet_money'   => $row['bet_money'],
                    'c_value'     => $row['c_value'],
                    'bet_eff'     => $row['bet_eff'],
                    'create_time' => $now,
                ];
            }
        }
        //官方注單
        $result = $this->ettm_official_bet_record_db->escape(false)
            ->select('lottery_id,uid,SUM(total_p_value) bet_money,SUM(bet_number) bet_number,SUM(c_value) c_value,SUM(return_money) return_money')
            ->where($where)->group('lottery_id,uid')->result();
        foreach ($result as $row) {
            if (isset($report["2-$row[lottery_id]-$row[uid]"])) {
                $update[] = [
                    'id'          => $report["2-$row[lottery_id]-$row[uid]"]['id'],
                    'bet_number'  => $row['bet_number'],
                    'bet_money'   => $row['bet_money'],
                    'c_value'     => bcadd($row['c_value'], $row['return_money'], 2),
                    'bet_eff'     => $row['bet_money'],
                    'create_time' => $now,
                ];
            } else {
                $insert[] = [
                    'day_time'    => $date,
                    'category'    => 2,
                    'lottery_id'  => $row['lottery_id'],
                    'uid'         => $row['uid'],
                    'bet_number'  => $row['bet_number'],
                    'bet_money'   => $row['bet_money'],
                    'c_value'     => bcadd($row['c_value'], $row['return_money'], 2),
                    'bet_eff'     => $row['bet_money'],
                    'create_time' => $now,
                ];
            }
        }
        //特色注單
        $where['money_type'] = 0;
        $result = $this->ettm_special_bet_record_db->escape(false)
            ->select('lottery_id,uid,SUM(total_p_value) bet_money,SUM(bet_number) bet_number,SUM(c_value) c_value,
                SUM(CASE is_lose_win WHEN 0 THEN p_value ELSE (CASE WHEN c_value - total_p_value - p_value > p_value THEN p_value ELSE c_value - total_p_value - p_value END) END) bet_eff')
            ->where($where)->group('lottery_id,uid')->result();
        foreach ($result as $row) {
            if (isset($report["3-$row[lottery_id]-$row[uid]"])) {
                $update[] = [
                    'id'          => $report["3-$row[lottery_id]-$row[uid]"]['id'],
                    'bet_number'  => $row['bet_number'],
                    'bet_money'   => $row['bet_money'],
                    'c_value'     => $row['c_value'],
                    'bet_eff'     => $row['bet_eff'],
                    'create_time' => $now,
                ];
            } else {
                $insert[] = [
                    'day_time'    => $date,
                    'category'    => 3,
                    'lottery_id'  => $row['lottery_id'],
                    'uid'         => $row['uid'],
                    'bet_number'  => $row['bet_number'],
                    'bet_money'   => $row['bet_money'],
                    'c_value'     => $row['c_value'],
                    'bet_eff'     => $row['bet_eff'],
                    'create_time' => $now,
                ];
            }
        }
        //統計事務
        $this->trans_start();
        if ($insert != []) {
            $this->insert_batch($insert);
        }
        if ($update != []) {
            $this->update_batch($update, 'id');
        }
        $this->trans_complete();
    }

    public static $categoryList = [
        1 => '经典彩',
        2 => '官方彩',
        3 => '特色棋牌',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'         => '编号',
        'uid'        => '用户ID',
        'day_time'   => '日期',
        'category'   => '分类',
        'lottery_id' => '彩种',
    ];
}
