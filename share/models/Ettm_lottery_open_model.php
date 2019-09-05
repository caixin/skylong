<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_lottery_open_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model', 'user_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('ettm_official_wanfa_model', 'ettm_official_wanfa_db');
        $this->load->model('ettm_special_model', 'ettm_special_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');
        $this->load->model('agent_return_point_model', 'agent_return_point_db');
        $this->load->model('prediction_model', 'prediction_db');
        $this->load->model('prediction_buy_model', 'prediction_buy_db');
        $this->load->library('Lottery_Permutation_combination/Common_combination');
    }

    /**
     * 依開獎號碼取得該期彩種獲利
     * @param int $lottery_id 彩種ID
     * @param int $qishu 期數
     * @param array $numbers 開獎號碼
     */
    public function getProfit($lottery_id, $qishu, $numbers)
    {
        $lottery = $this->ettm_lottery_db->row($lottery_id);
        $now = date('Y-m-d H:i:s');
        $profit = 0;
        if ($lottery['mode'] & 1) {
            $classic = $this->classicOpenAction($lottery, $qishu, $numbers, $now, true);
            $profit = bcadd($profit, $classic, 2);
        }
        if ($lottery['mode'] & 2) {
            $official = $this->officialOpenAction($lottery, $qishu, $numbers, true);
            $profit = bcadd($profit, $official, 2);
        }
        /*
        if ($lottery['mode'] & 4) {
            $special = $this->specialOpenAction($lottery, $qishu, $numbers, true);
            $profit = bcadd($profit,$special,2);
        }*/
        return (float) $profit;
    }

    /**
     * 彩種派獎
     * @param  integer $lottery_id 彩種ID
     * @param  integer $qishu 期數
     * @return integer|bool
     */
    public function openAction($lottery_id, $qishu)
    {
        $lottery = $this->ettm_lottery_db->row($lottery_id);
        $Monolog_dir = $lottery['key_word'] . 'BetOpen';
        $return_point_dir = $lottery['key_word'] . 'ReturnPoint';
        $prediction_dir = $lottery['key_word'] . 'Prediction';
        //判斷是否已開獎
        $record = $this->ettm_lottery_record_db->where([
            'lottery_id' => $lottery_id,
            'qishu'      => $qishu,
            'status'     => 1,
        ])->result_one();
        Monolog::writeLogs($Monolog_dir, 200, $this->base_model->last_query());
        if ($record === null) {
            Monolog::writeLogs($Monolog_dir, 200, "Open Numbers is not Exists");
            return false;
        }
        $numbers = explode(',', $record['numbers']);
        $status = 0;
        //經典注單派獎
        if ($lottery['mode'] & 1) {
            Monolog::writeLogs($Monolog_dir, 200, "经典-$lottery[name]-期数:$qishu-开始执行!");
            $status = $this->classicOpenAction($lottery, $qishu, $numbers, $record['lottery_time']);
            Monolog::writeLogs($return_point_dir, 200, "经典-$lottery[name]-期数:$qishu-执行結果:$status-结束!");
            //代理中心
            Monolog::writeLogs($return_point_dir, 200, "经典-$lottery[name]-期数:$qishu-开始执行!");
            $result = $this->agent_return_point_db->settlement(1, $lottery_id, $qishu);
            Monolog::writeLogs($return_point_dir, 200, "经典-$lottery[name]-期数:$qishu-执行結果:$result-结束!");
            //熱門預測
            $prediction_lottery = $this->prediction_db->getPredictionLottery();
            if (in_array($lottery_id,$prediction_lottery)) {
                Monolog::writeLogs($prediction_dir, 200, "经典-$lottery[name]-期数:$qishu-开始执行!");
                $result = $this->prediction_buy_db->settlement($lottery_id, $qishu, $numbers, $record['lottery_time']);
                Monolog::writeLogs($prediction_dir, 200, "经典-$lottery[name]-期数:$qishu-执行結果:$result-结束!");
            }
        }
        //官方注單派獎
        if ($lottery['mode'] & 2) {
            Monolog::writeLogs($Monolog_dir, 200, "官方-$lottery[name]-期数:$qishu-开始执行!");
            $status = $this->officialOpenAction($lottery, $qishu, $numbers);
            Monolog::writeLogs($Monolog_dir, 200, "官方-$lottery[name]-期数:$qishu-執行結果:$status-结束!");
            //代理中心
            Monolog::writeLogs($return_point_dir, 200, "官方-$lottery[name]-期数:$qishu-开始执行!");
            $result = $this->agent_return_point_db->settlement(2, $lottery_id, $qishu);
            Monolog::writeLogs($return_point_dir, 200, $result);
            Monolog::writeLogs($return_point_dir, 200, "官方-$lottery[name]-期数:$qishu-结束!");
        }
        //特色注單派獎
        if ($lottery['mode'] & 4) {
            $status = $this->specialOpenAction($lottery, $qishu, $numbers);
        }
        //香港六合彩結算後 要把前五天報表統計重新計算
        if ($lottery_id == 22) {
            $this->load->model('daily_user_report_model', 'daily_user_report_db');
            for ($i = 1; $i <= 5; $i++) {
                $date = date('Y-m-d', time() - (86400 * $i));
                $this->daily_user_report_db->statistics($date);
            }
        }

        return $status;
    }

    /**
     * 經典注單派獎
     * @param array $lottery 彩種資料
     * @param int $qishu 期數
     * @param array $numbers 開獎號碼
     * @param string $lottery_time 開獎時間
     * @param boolean $return_profit 回傳該期獲利
     */
    private function classicOpenAction($lottery, $qishu, $numbers, $lottery_time = '', $return_profit = false)
    {
        $Monolog_dir = $lottery['key_word'] . 'BetOpen';
        //取出該期注單
        $where = [
            't.lottery_id' => $lottery['id'],
            't.qishu'      => $qishu,
            't.status <='  => 0,
        ];
        if ($this->operator_id > 0) {
            $where['operator_id'] = $this->operator_id;
        }
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $bet = $this->ettm_classic_bet_record_db->select('t.*')->where($where)->join($join)->result();
        if ($bet === []) {
            Monolog::writeLogs($Monolog_dir, 200, "No One Bet");
            return 0;
        }
        //取得玩法
        if ($lottery['lottery_type_id'] == 8) { //六合彩
            $wanfa = $this->ettm_classic_wanfa_detail_db->getWanfaRecordMk6($numbers, $lottery_time);
        } else {
            $wanfa = $this->ettm_classic_wanfa_detail_db->where([
                'lottery_type_id' => $lottery['lottery_type_id']
            ])->select('id,formula')->result();
            $formula = array_column($wanfa, 'formula', 'id');
        }

        $date = date('Y-m-d H:i:s');
        $total_p_value = $total_c_value = 0;
        $update = $user_update = [];
        foreach ($bet as $row) {
            $special = false;
            $odds = $row['odds'];
            //判斷是否中獎
            $is_lose_win = 0;
            $data = [];
            switch ($lottery['lottery_type_id']) {
                case 1:
                    $is_lose_win = $this->betLoseWinTat($numbers, $formula[$row['wanfa_detail_id']]);
                    break;
                case 2:
                    $is_lose_win = $this->betLoseWinKl10($numbers, $formula[$row['wanfa_detail_id']]);
                    break;
                case 3:
                    $is_lose_win = $this->betLoseWinPc28($numbers, $formula[$row['wanfa_detail_id']], $special);
                    $payload = json_decode($row['payload'], true);
                    $odds = $special && (float)$payload['odds_special'] != 0 ? $payload['odds_special'] : $payload['odds'];
                    break;
                case 4:
                    $is_lose_win = $this->betLoseWinF3($numbers, $formula[$row['wanfa_detail_id']], $row['bet_values']);
                    break;
                case 5:
                    $is_lose_win = $this->betLoseWinPk10($numbers, $formula[$row['wanfa_detail_id']], $row['bet_values']);
                    break;
                case 8:
                    $is_lose_win = $this->betLoseWinMk6($wanfa, $row, $data);
                    break;
            }
            $c_value = 0;
            $arr = [
                'id'          => $row['id'],
                'odds'        => $odds,
                'is_lose_win' => $is_lose_win,
                'status'      => 1,
                'update_time' => $date,
                'update_by'   => 'OpenAction',
            ];
            if ($lottery['lottery_type_id'] == 8) { //六合彩
                $payload = json_decode($row['payload'], true);
                $payload['c_odds'] = $data['c_odds'];
                $c_value = $data['c_value'];
                $arr['c_value'] = $c_value;
                $arr['odds'] = $data['odds'];
                $arr['payload'] = json_encode($payload);
            } else {
                if ($is_lose_win == 1) {
                    //中獎
                    $c_value = bcmul($row['total_p_value'], $odds, 2);
                } elseif ($is_lose_win == 2) {
                    //平手
                    $c_value = $row['total_p_value'];
                }
                $arr['c_value'] = $c_value;
            }
            $update[] = $arr;
            $total_p_value = bcadd($total_p_value, $row['total_p_value'], 2);
            $total_c_value = bcadd($total_c_value, $c_value, 2);

            if ($c_value > 0) { //賠付
                $user_update[$row['uid']] = isset($user_update[$row['uid']]) ? bcadd($user_update[$row['uid']], $c_value, 2) : $c_value;
            }
        }
        //回傳該期獲利
        if ($return_profit) {
            return bcsub($total_p_value, $total_c_value, 2);
        }

        Monolog::writeLogs($Monolog_dir, 200, $update);
        $this->base_model->trans_start();
        //寫入開獎結果
        $this->ettm_classic_bet_record_db->update_batch($update, 'id');
        foreach ($user_update as $uid => $money) {
            $this->user_db->addMoney($uid, $qishu, 6, $money, "经典-$lottery[name]赔付", 1, $lottery['id']);
        }
        $this->base_model->trans_complete();

        if ($this->base_model->trans_status()) {
            Monolog::writeLogs($Monolog_dir, 200, 'Result : Success');
            return 1;
        } else {
            Monolog::writeLogs($Monolog_dir, 200, 'Result : Fail');
            return 0;
        }
    }

    /**
     * 官方注單派獎
     * @param array $lottery 彩種資料
     * @param int $qishu 期數
     * @param array $numbers 開獎號碼
     * @param boolean $return_profit 回傳該期獲利
     */
    private function officialOpenAction($lottery, $qishu, $numbers, $return_profit = false)
    {
        $Monolog_dir = $lottery['key_word'] . 'BetOpen';
        //取出該期注單
        $where = [
            't.lottery_id' => $lottery['id'],
            't.qishu'      => $qishu,
            't.status <='  => 0,
        ];
        if ($this->operator_id > 0) {
            $where['operator_id'] = $this->operator_id;
        }
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $bet = $this->ettm_official_bet_record_db->select('t.*')->where($where)->join($join)->result();
        Monolog::writeLogs($Monolog_dir, 200, $this->base_model->last_query());
        if ($bet === []) {
            Monolog::writeLogs($Monolog_dir, 200, "No One Bet");
            return 0;
        }

        $wanfa = $this->ettm_official_wanfa_db->where([
            'lottery_type_id' => $lottery['lottery_type_id']
        ])->result();
        $wanfa = array_column($wanfa, 'key_word', 'id');

        $date = date('Y-m-d H:i:s');
        $total_p_value = $total_c_value = 0;
        $update = $user_update = $user_return_update = [];
        foreach ($bet as $row) {
            //判斷是否中獎
            $is_lose_win = 0;
            switch ($lottery['lottery_type_id']) {
                case 1:
                    $is_lose_win = $this->officialBetTat($numbers, $wanfa[$row['wanfa_pid']], $wanfa[$row['wanfa_id']], $row['bet_values']);
                    break;
                case 5:
                    $is_lose_win = $this->officialBetPk10($numbers, $wanfa[$row['wanfa_pid']], $wanfa[$row['wanfa_id']], $row['bet_values']);
                    break;
                case 6:
                    $is_lose_win = $this->officialBet11x5($numbers, $wanfa[$row['wanfa_pid']], $wanfa[$row['wanfa_id']], $row['bet_values']);
                    break;
                case 7:
                    $is_lose_win = $this->officialBetDpc($numbers, $wanfa[$row['wanfa_pid']], $wanfa[$row['wanfa_id']], $row['bet_values']);
                    break;
            }

            $c_value = $return_money = 0;
            $arr = [
                'id'          => $row['id'],
                'is_lose_win' => $is_lose_win,
                'status'      => 1,
                'update_time' => $date,
                'update_by'   => 'OpenAction',
            ];

            if ($is_lose_win > 0) {
                $c_value = (float) bcmul(bcmul(bcmul($row['p_value'], $row['odds'], 3), $is_lose_win, 3), $row['bet_multiple'], 2);
            } else {
                $return_money = (float) bcmul($row['total_p_value'], bcdiv($row['return_point'], 100, 5), 2);
            }
            $arr['c_value'] = $c_value;
            $arr['return_money'] = $return_money;
            $update[] = $arr;
            $total_p_value = bcadd($total_p_value, $row['total_p_value'], 2);
            $total_c_value = bcadd($total_c_value, bcadd($c_value, $return_money, 2), 2);

            if ($c_value > 0) { //賠付
                $user_update[$row['uid']] = isset($user_update[$row['uid']]) ? bcadd($user_update[$row['uid']], $c_value, 2) : $c_value;
            }
            if ($return_money > 0) { //返利
                $user_return_update[$row['uid']] = isset($user_return_update[$row['uid']]) ? bcadd($user_return_update[$row['uid']], $return_money, 2) : $return_money;
            }
        }
        //回傳該期獲利
        if ($return_profit) {
            return bcsub($total_p_value, $total_c_value, 2);
        }

        Monolog::writeLogs($Monolog_dir, 200, $update);
        $this->base_model->trans_start();
        //寫入開獎結果
        $this->ettm_official_bet_record_db->update_batch($update, 'id');
        foreach ($user_update as $uid => $money) {
            $this->user_db->addMoney($uid, $qishu, 12, $money, "官方-$lottery[name]赔付", 2, $lottery['id']);
        }
        foreach ($user_return_update as $uid => $money) {
            $this->user_db->addMoney($uid, $qishu, 13, $money, "官方-$lottery[name]返利", 2, $lottery['id']);
        }
        $this->base_model->trans_complete();

        if ($this->base_model->trans_status()) {
            Monolog::writeLogs($Monolog_dir, 200, 'Result : Success');
            return 1;
        } else {
            Monolog::writeLogs($Monolog_dir, 200, 'Result : Fail');
            return 0;
        }
    }

    /**
     * 特色棋牌-派奖
     * @param array $lottery 彩種資料
     * @param int $qishu 期數
     * @param array $numbers 開獎號碼
     */
    public function specialOpenAction($lottery, $qishu, $numbers)
    {
        $Monolog_dir = $lottery['key_word'] . 'BetOpen';
        $special = $this->ettm_special_db->where([
            'lottery_id' => $lottery['id'],
        ])->result();
        foreach ($special as $row) {
            Monolog::writeLogs($Monolog_dir, 200, "特色-$lottery[name]-" . ettm_special_model::$typeList[$row['type']] . "-期数:$qishu-开始执行!");
            switch ($row['type']) {
                case 1:
                    $result = $this->specialCNN($lottery, $row, $qishu, $numbers);
                    break;
                case 2:
                    $result = $this->specialCCNN($lottery, $row, $qishu, $numbers);
                    break;
                default:
                    $result = 0;
                    break;
            }
            if (class_exists('swoole_client')) {
                $this->swooleCat($row, $qishu);
            } else {
                Monolog::writeLogs('SwooleCat', 200, 'Error:尚未安装Swoole扩展!');
            }
            Monolog::writeLogs($Monolog_dir, 200, "特色-$lottery[name]-" . ettm_special_model::$typeList[$row['type']] . "-期数:$qishu-结束!");
        }
        return $result;
    }

    /**
     * 時時彩中獎公式計算
     * @param array $numbers 開獎號碼
     * @param float $formula 公式
     * @return int 輸贏 0:輸 1:贏 2:平
     */
    private function betLoseWinTat($numbers, $formula)
    {
        $record = $this->ettm_lottery_record_db->tat($numbers);
        $numbers = array_map('intval', $numbers);
        $formula_arr = [];
        $is_lose_win = 0;
        //两面盘 数字盘 单数
        preg_match("/\(\d\)(==|>|<|>=|<=|%)\d{1,2}/", $formula, $formula_arr);
        if (count($formula_arr) == 2) {
            preg_match("/\(\d\){1,2}/", $formula_arr[0], $arr_a);
            preg_match("/\d{1,2}/", $arr_a[0], $arr_b);
            $_str_value = preg_replace("/\([\d]{1,2}\)/", $numbers[$arr_b[0] - 1], $formula_arr[0], 2);
            $_str = explode($formula_arr[0], $formula);
            $str = $_str_value . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }

        //整合盘总和   总大总小总单总双
        preg_match("/\(\d\+\d\+\d\+\d\+\d\)(==\d|>=\d{2}|<=\d{2}|%\d)/", $formula, $formula_arr);
        if (count($formula_arr) == 2) {
            preg_match("/\d\+\d\+\d\+\d\+\d/", $formula_arr[0], $arr_a);
            $arr_a = explode('+', $arr_a[0]);
            $_sum_value = 0;
            foreach ($arr_a as $key => $value) {
                $_sum_value += $numbers[$key];
            }
            $_str = explode($formula_arr[0], $formula);
            $str = $_sum_value . $formula_arr[1] . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }

        //整合盤對子 (必須在整合盤龍虎前面)
        preg_match("/\(\(\(\d\=\=\d\)\&\&\(\d\!\=\d\)\)\|\|\(\(\d\=\=\d\)\&\&\(\d\!\=\d\)\)\|\|\(\(\d\=\=\d\)\&\&\(\d\!\=\d\)\)\)/", $formula, $formula_arr);
        if (count($formula_arr) == 1) {
            preg_match('/\(\(\(\d/', $formula_arr[0], $arr_a);
            preg_match('/\d/', $arr_a[0], $arr_a);
            $arr_a = $arr_a[0];
            $arr = [];
            if ($arr_a == 1) {
                return $record['front'] == 3 ? 1 : 0;
            } elseif ($arr_a == 2) {
                return $record['medium'] == 3 ? 1 : 0;
            } elseif ($arr_a == 3) {
                return $record['back'] == 3 ? 1 : 0;
            }
        }

        //整合盤 龍虎
        preg_match("/\(\d[=|>|<]{1,2}\d\)/", $formula, $formula_arr);
        if (count($formula_arr) == 1) {
            $_str_value = preg_replace("/\([\d]{1}/", $numbers[0], $formula_arr[0], 1);
            $_str_value = preg_replace("/[\d]{1}\)/", $numbers[4], $_str_value, 1);
            $_str = explode($formula_arr[0], $formula);
            $str = $_str_value . $_str[1];
            eval("\$is_lose_win =$str;");
            //龍虎平局 算和局 退還本金
            if (in_array($formula, ['(1<5)?1:0', '(1>5)?1:0'])) {
                if ($numbers[0] == $numbers[4]) {
                    $is_lose_win = 2;
                }
            }
            return $is_lose_win;
        }

        //整合盘 前 中 后 豹子
        preg_match("/\(\d==\d==\d\)/", $formula, $formula_arr);
        if (count($formula_arr) == 1) {
            $arr_a = trim($formula_arr[0], '(');
            $arr_a = trim($arr_a, ')');
            $arr_a = explode('==', $arr_a);
            $arr = [];
            foreach ($arr_a as $value) {
                $arr[] = $numbers[$value - 1];
            }
            $is_lose_win = count(array_count_values($arr)) == 1 ? 1 : 0;

            return $is_lose_win;
        }

        //整合盘 前 中 后 顺子
        preg_match("/\(\(\(\d\-\d\)\+\d\)==\d\)/", $formula, $formula_arr);
        if (count($formula_arr) == 1) {
            $arr_a = preg_replace('/\(|\)/', '', $formula_arr[0]);
            $arr_b = explode('==', $arr_a);
            $arr_c = preg_replace('/\-|\+/', '|', $arr_b[0]);
            if ($arr_b[1] == 4) {
                return $record['back'] == 2 ? 1 : 0;
            } elseif ($arr_b[1] == 3) {
                return $record['medium'] == 2 ? 1 : 0;
            } elseif ($arr_b[1] == 2) {
                return $record['front'] == 2 ? 1 : 0;
            }
        }

        //整合盘 数字盘 前中后  半顺
        preg_match("/\(\d\-\d\=\=\d\|\|\d\-\d\=\=\d\)\[\d\]/", $formula, $formula_arr);
        if (count($formula_arr) == 1) {
            preg_match('/\[\d\]/', $formula_arr[0], $arr_a);
            $arr_a = trim($arr_a[0], '[');
            $arr_a = trim($arr_a, ']');
            if ($arr_a == 1) {
                return $record['front'] == 4 ? 1 : 0;
            } elseif ($arr_a == 2) {
                return $record['medium'] == 4 ? 1 : 0;
            } elseif ($arr_a == 3) {
                return $record['back'] == 4 ? 1 : 0;
            }
        }
        //整合盘 数字盘 前中后  杂六
        preg_match("/\(\d\!\=\d\&\&\d\!\=\d\&\&\(\d\-\d\+\d\)\!\=\d\)\[\d\]/", $formula, $formula_arr);
        if (count($formula_arr) == 1) {
            preg_match('/\[\d\]/', $formula_arr[0], $arr_a);
            $arr_a = trim($arr_a[0], '[');
            $arr_a = trim($arr_a, ']');
            if ($arr_a == 1) {
                return $record['front'] == 5 ? 1 : 0;
            } elseif ($arr_a == 2) {
                return $record['medium'] == 5 ? 1 : 0;
            } elseif ($arr_a == 3) {
                return $record['back'] == 5 ? 1 : 0;
            }
        }
        return 0;
    }

    /**
     * 快10中獎公式計算
     * @param array $numbers 開獎號碼
     * @param float $formula 公式
     * @return int 輸贏 0:輸 1:贏 2:平
     */
    private function betLoseWinKl10($numbers, $formula)
    {
        $formula_arr = [];
        $is_lose_win = 0;
        //数字盘 两面盘
        preg_match("/\(\d\)(\={2}|>=|<=|%)\d{1,2}/", $formula, $formula_arr);
        if (count($formula_arr) == 2) {
            preg_match("/\(\d\){1,2}/", $formula_arr[0], $arr_a);
            preg_match("/\d{1,2}/", $arr_a[0], $arr_b);
            $_str_value = preg_replace("/\([\d]{1,2}\)/", '(' . $numbers[$arr_b[0] - 1] . ')', $formula_arr[0], 2);
            $_str = explode($formula_arr[0], $formula);
            $str = $_str_value . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }

        //两面总和 大小单双
        preg_match("/\(\d\+\d\+\d\+\d\+\d\+\d\+\d\+\d\)(==\d|>=\d{2}|>\d{1,2}|<\d{1,2}|<={2}|%\d)/", $formula, $formula_arr);
        if (count($formula_arr) == 2) {
            preg_match("/\d\+\d\+\d\+\d\+\d\+\d\+\d\+\d/", $formula_arr[0], $arr_a);
            $arr_a = explode('+', $arr_a[0]);
            $_sum_value = 0;
            foreach ($arr_a as $key => $value) {
                $_sum_value += $numbers[$key];
            }

            if (strpos($formula,'84') !== false && $_sum_value == 84) {
                //總和大小和局
                $is_lose_win = 2;
            } else {
                $_str = explode($formula_arr[0], $formula);
                $str = $_sum_value . $formula_arr[1] . $_str[1];
                eval("\$is_lose_win =$str;");
            }
            return $is_lose_win;
        }

        //东西南北中发白
        preg_match("/\(\d\)\==[\(\d{1,2}\|{1,2}]*\)/", $formula, $formula_arr);
        if (count($formula_arr) == 1) {
            $arr_a = explode('==', $formula_arr[0]);
            preg_match("/\d/", $arr_a[0], $arr_b);
            $arr_c = trim($arr_a[1], '(');
            $arr_c = trim($arr_c, ')');
            $arr = explode('||', $arr_c);
            if (in_array($numbers[$arr_b[0] - 1], $arr)) {
                return 1;
            } else {
                return 0;
            }
        }

        //总和 尾大 尾小
        preg_match("/\(\d\+\d\+\d\+\d\+\d\+\d\+\d\+\d\)\[\d\](>=\d|<=\d)/", $formula, $formula_arr);
        if (count($formula_arr) == 2) {
            preg_match("/\d\+\d\+\d\+\d\+\d\+\d\+\d\+\d/", $formula_arr[0], $arr_a);
            $arr_a = explode('+', $arr_a[0]);
            $_sum_value = 0;
            foreach ($arr_a as $key => $value) {
                $_sum_value += $numbers[$key];
            }
            $_str = explode($formula_arr[0], $formula);
            $str = ($_sum_value % 10) . $formula_arr[1] . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }

        //单球 尾大 尾小
        preg_match("/\(\d\)\[\d\](>=\d|<=\d)/", $formula, $formula_arr);
        if (count($formula_arr) == 2) {
            preg_match("/\(\d\)/", $formula_arr[0], $arr_a);
            preg_match("/\d/", $arr_a[0], $arr_b);
            $_str = explode($formula_arr[0], $formula);
            $str = ($numbers[$arr_b[0] - 1] % 10) . $formula_arr[1] . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }
        //单球 和单 和双
        preg_match("/\(\d\)\[\d\]\+\[\d\](%\d)/", $formula, $formula_arr);
        if (count($formula_arr) == 2) {
            preg_match("/\(\d\)/", $formula_arr[0], $arr_a);
            preg_match("/\d/", $arr_a[0], $arr_b);
            $_str = explode($formula_arr[0], $formula);
            $str = (($numbers[$arr_b[0] - 1] % 10) + ($numbers[$arr_b[0] - 1] / 10 % 10)) . $formula_arr[1] . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }

        //龙虎
        preg_match("/\(\d{1,2}[>|<|\=]{1,2}\d{1,2}\)/", $formula, $formula_arr);
        if (count($formula_arr) == 1) {
            preg_match_all('/[^+]|[^<]|[^>]*/', $formula_arr[0], $arr_a);
            if (isset($arr_a[0][4]) && $arr_a[0][4] == 0) {
                $arr_a[0][3] = $arr_a[0][3] . $arr_a[0][4];
            }
            $arr_b = array_filter($arr_a[0]);
            $_str = explode($formula_arr[0], $formula);
            $str = $numbers[$arr_a[0][1] - 1] . $arr_a[0][2] . $numbers[$arr_a[0][3] - 1] . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }
        return 0;
    }

    /**
     * PC28中獎公式計算
     * @param array $numbers 開獎號碼
     * @param float $formula 公式
     * @param bool &$special 是否特殊賠率
     * @return int 輸贏 0:輸 1:贏 2:平
     */
    private function betLoseWinPc28($numbers, $formula, &$special)
    {
        $formula_arr = [];
        $is_lose_win = 0;
        //特码大单小单 大双小双
        $number = preg_match("/\(\d\+\d\+\d\)(>=\d{2}|<=\d{2})\&\&\(\d\+\d\+\d\)\%\d/", $formula, $formula_arr);
        if (count($formula_arr) == 2) {
            $_str = explode($formula_arr[0], $formula);
            $sum_number = $numbers[0] + $numbers[1] + $numbers[2];
            $str = '(' . ($sum_number) . $formula_arr[1] . '&&' . ($sum_number) . '%2' . $_str[1];
            eval("\$is_lose_win =$str;");

            $special = in_array($sum_number, [13, 14]) ? true : false;
            return $is_lose_win;
        }

        //特码和值 大小 单双 极大 极小
        $number = preg_match("/\(\d\+\d\+\d\)(==\d|>=\d{2}|<=\d{1,2}|%\d)/", $formula, $formula_arr);
        if (count($formula_arr) == 2) {
            $_str = explode($formula_arr[0], $formula);
            $sum_number = $numbers[0] + $numbers[1] + $numbers[2];
            $str = $sum_number . $formula_arr[1] . $_str[1];
            eval("\$is_lose_win =$str;");

            $special = in_array($sum_number, [13, 14]) ? true : false;
            return $is_lose_win;
        }

        //豹子
        $number = preg_match("/\(\d\==\d\==\d\)/", $formula, $formula_arr);

        if (count($formula_arr) == 1) {
            $str = "($numbers[0]==$numbers[1]&&$numbers[0]==$numbers[2])?1:0";
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }

        //顺子
        $number = preg_match("/\(\(\d\-\d\)\+\d\)\==\d/", $formula, $formula_arr);
        if (count($formula_arr) == 1) {
            sort($numbers);
            //$str="(($numbers[2]-$numbers[1])+$numbers[0])==$numbers[1]?1:0";
            //eval("\$is_lose_win =$str;");
            $is_lose_win = empty(getconsecutive($numbers, 3)) ? 0 : 1;

            return $is_lose_win;
        }

        //对子
        $number = preg_match("/\(\d==\d\)&&\(\d\!=\d\)/", $formula, $formula_arr);
        if ($number) {
            $unique_arr = array_unique($numbers);
            $arr = array_diff_assoc($numbers, $unique_arr);
            if (count($arr) == 1) {
                return 1;
            } else {
                return 0;
            }
        }
        //红蓝绿波
        $number = preg_match("/(\d{1,2}\|\|\d{1,2}\|\|\d{1,2}\|\|\d{1,2}\|\|\d{1,2}\|\|\d{1,2}\|\|\d{1,2}\|\|\d{1,2}\|\|\d{1,2})/", $formula, $formula_arr);
        if ($number) {
            $arr = explode('||', $formula_arr[0]);
            $sum_number = $numbers[0] + $numbers[1] + $numbers[2];
            if (in_array($sum_number, $arr)) {
                return 1;
            } else {
                return 0;
            }
        }

        //数字盘  单球 大小 单双
        $number = preg_match("/\(\d\)(==\d|>=\d{1}|<=\d{1}|%\d)/", $formula, $formula_arr);
        if ($number) {
            preg_match('/\(\d\)/', $formula_arr[0], $arr_a);
            preg_match('/\d/', $arr_a[0], $arr_b);
            $_str = explode($arr_a[0], $formula);
            $value = $numbers[$arr_b[0] - 1];
            $str = $value . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }
        //龙虎和
        $number = preg_match("/\(\d[>|<|\=]{1,2}\d\)/", $formula, $formula_arr);
        if ($number) {
            preg_match('/[>|<|\=]{1,2}/', $formula_arr[0], $arr_a);
            $_str = explode($formula_arr[0], $formula);
            $str = $numbers[0] . $arr_a[0] . $numbers[2] . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }
        return 0;
    }

    /**
     * 快3中獎公式計算
     * @param array $numbers 開獎號碼
     * @param float $formula 公式
     * @param float $bet_values 下注值
     * @return int 輸贏 0:輸 1:贏 2:平
     */
    private function betLoseWinF3($numbers, $formula, $bet_values)
    {
        $formula_arr = [];
        $is_lose_win = 0;
        //豹子通殺
        $pass_kill = $numbers[2] == $numbers[1] && $numbers[1] == $numbers[0] ? 1 : 0;
        //豹子
        if ($pass_kill && (implode(',',$numbers) == $bet_values || $bet_values == '全骰')) {
            return 1;
        }

        //三军单选
        $search = "/\((.*)\)/";
        $number = preg_match("/==\d\|\|/", $formula);
        if ($number) {
            if (preg_match($search, $formula, $formula_arr)) {
                preg_match_all('/==\d/', $formula_arr[1], $arr_a);
                $_str = explode($formula_arr[0], $formula);
                $str = '(' . $numbers[0] . $arr_a[0][0] . '||' . $numbers[1] . $arr_a[0][0] . '||' . $numbers[2] . $arr_a[0][0] . ')' . $_str[1];
                if ($pass_kill) {
                    return 0;
                }
                eval("\$is_lose_win =$str;");

                return $is_lose_win;
            }
        }

        //三军大小
        $number = preg_match("/<|>\=/", $formula, $arr);
        if ($number) {
            if (preg_match($search, $formula, $formula_arr)) {
                $_str = explode($formula_arr[0], $formula);
                $str = '(' . $numbers[0] . '+' . $numbers[1] . '+' . $numbers[2] . ')' . $_str[1];
                if ($pass_kill) {
                    return 0;
                }
                eval("\$is_lose_win =$str;");

                return $is_lose_win;
            }
        }

        //点数=和值
        $number = preg_match("/\(\d\+\d\+\d\)==\d/", $formula);
        if ($number) {
            if (preg_match($search, $formula, $formula_arr)) {
                $_str = explode($formula_arr[0], $formula);
                $str = '(' . $numbers[0] . '+' . $numbers[1] . '+' . $numbers[2] . ')' . $_str[1];
                if ($pass_kill) {
                    return 0;
                }
                eval("\$is_lose_win =$str;");

                return $is_lose_win;
            }
        }
        //长牌
        $number = preg_match("/\==\d,\d/", $formula);
        if ($number) {
            if (preg_match("/\d,\d/", $formula, $formula_arr)) {
                $_str = ['?1:0'];
                if ($pass_kill) {
                    return 0;
                }
                $numbers = array_unique($numbers);
                $numbers = array_values($numbers);
                $return_arr = combination($numbers, 2);
                $is_lose_win = 0;
                foreach ($return_arr as $value) {
                    $value_1 = $value[0] . ',' . $value[1];
                    $value_2 = $value[1] . ',' . $value[0];

                    $is_lose_win = ($value_1 == $bet_values || $value_2 == $bet_values) ? 1 : 0;
                    if ($is_lose_win) {
                        return $is_lose_win;
                    }
                }
            }
        }
        //短牌
        $number = preg_match("/\==\d{2}/", $formula, $arr);
        if ($number) {
            if (preg_match("/\d{2}/", $formula, $formula_arr)) {
                $_str = ['?1:0'];
                if ($pass_kill) {
                    return 0;
                }
                $unique_arr = array_unique($numbers);
                $arr = array_diff_assoc($numbers, $unique_arr);
                if ($arr) {
                    $arr = current($arr);
                    $is_lose_win = $arr . ',' . $arr == $bet_values ? 1 : 0;

                    return $is_lose_win;
                } else {
                    return 0;
                }
            }
        }
        return 0;
    }

    /**
     * PK10中獎公式計算
     * @param array $numbers 開獎號碼
     * @param float $formula 公式
     * @return int 輸贏 0:輸 1:贏 2:平
     */
    private function betLoseWinPk10($numbers, $formula, $bet_values)
    {
        $formula_arr = [];
        $is_lose_win = 0;
        $number = preg_match('/-/', $bet_values);
        if ($number) {
            if (preg_match("/\((.*)\)/", $formula, $formula_arr)) {
                preg_match_all('/\d{1,2}\|\|\d{1,2}/', $formula_arr[0], $arr_b);
                preg_match_all('/\d{1,2}/', $arr_b[0][0], $str_arr);
                $_formula_str = explode($formula_arr[0], $formula);
                $_formula_str = array_filter($_formula_str)[1];
                if (trim($_formula_str) == '?1:0') {
                    //冠亚军组合中奖公式
                    $str = '((' . $numbers[0] . '==' . $str_arr[0][0] . '||' . $numbers[0] . '==' . $str_arr[0][1] . ')&&(' . $numbers[1] . '==' . $str_arr[0][0] . '||' . $numbers[1] . '==' . $str_arr[0][1] . '))' . $_formula_str;
                    eval("\$is_lose_win =$str;");

                    return $is_lose_win;
                }
            }
        }

        //两面盘 数字盘 单数
        preg_match("/\(\d{1,2}\)(==|>|<|>=|<=|%|\!\=)\d{1,2}/", $formula, $formula_arr);
        if (count($formula_arr) == 2) {
            preg_match("/\(\d{1,2}\){1,2}/", $formula_arr[0], $arr_a);
            preg_match("/\d{1,2}/", $arr_a[0], $arr_b);

            $_str_value = preg_replace("/\([\d]{1,2}\)/", $numbers[$arr_b[0] - 1], $formula_arr[0], 2);

            $_str = explode($formula_arr[0], $formula);
            $str = $_str_value . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }

        //冠亚大小单双
        preg_match("/\(\d[\+]\d\)/", $formula, $formula_arr);
        if (count($formula_arr) == 1) {
            $_str_value = preg_replace("/\([\d]{1}/", $numbers[0], $formula_arr[0], 1);
            $_str_value = preg_replace("/[\d]{1}\)/", $numbers[1], $_str_value, 1);
            $_str_value = '(' . $_str_value . ')';
            $_str = explode($formula_arr[0], $formula);
            $str = $_str_value . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }

        //龙虎
        preg_match("/\(\d{1,2}[>|<|\=]{1,2}\d{1,2}\)/", $formula, $formula_arr);
        if (count($formula_arr) == 1) {
            preg_match_all('/[^+]|[^<]|[^>]*/', $formula_arr[0], $arr_a);
            if (isset($arr_a[0][4]) && $arr_a[0][4] == 0) {
                $arr_a[0][3] = $arr_a[0][3] . $arr_a[0][4];
            }
            $arr_b = array_filter($arr_a[0]);
            $_str = explode($formula_arr[0], $formula);
            $str = $numbers[$arr_a[0][1] - 1] . $arr_a[0][2] . $numbers[$arr_a[0][3] - 1] . $_str[1];
            eval("\$is_lose_win =$str;");

            return $is_lose_win;
        }
        return 0;
    }

    /**
     * 六合彩中獎公式計算
     * @param array $numbers 開獎號碼
     * @param float $lottery_time 開獎時間
     * @param array $data 資料回傳
     * @return int 輸贏 0:輸 1:贏 2:平
     */
    private function betLoseWinMk6($wanfa, $bet, &$data)
    {
        $win = $wanfa['win'];
        $draw = $wanfa['draw'];
        $lose = $wanfa['lose'];
        $official = $wanfa['official'];

        $data['odds'] = $bet['odds'];
        $data['c_odds'] = [];
        //贏
        if (isset($win[$bet['wanfa_detail_id']])) {
            $data['c_value'] = bcmul($bet['total_p_value'], $bet['odds'], 2);
            return 1;
        }
        //和
        if (isset($draw[$bet['wanfa_detail_id']])) {
            $data['c_value'] = $bet['total_p_value'];
            return 2;
        }
        //輸
        if (isset($lose[$bet['wanfa_detail_id']])) {
            $data['c_value'] = 0;
            return 0;
        }
        //官方玩法 算法不適用經典玩法
        if (isset($official[$bet['wanfa_detail_id']])) {
            $wanfa = $official[$bet['wanfa_detail_id']];
            $formula = json_decode($wanfa['formula'], true);
            $payload = json_decode($bet['payload'], true);
            $odds = $bet['odds'];
            $odds_normal = $payload['odds'];
            $odds_special = $payload['odds_special'] != 0 ? $payload['odds_special'] : $bet['odds'];
            $bet_values = array_map('intval', explode(',', $bet['bet_values']));

            //預設值
            $c_value = 0;
            $c_odds['draw']    = ['win' => 0, 'odds' => 1];
            $c_odds['normal']  = ['win' => 0, 'odds' => $odds_normal];
            $c_odds['special'] = ['win' => 0, 'odds' => $odds_special];
            $c_odds['detail']  = [];

            if (is_array($wanfa['combination'])) {
                $bet_values_arr = combination($bet_values, $wanfa['combination_number']);

                foreach ($bet_values_arr as $betValue) {
                    $record = -1;
                    //官方彩輸贏判斷 guess=1表示猜中 guess=0表示猜不中
                    if ($wanfa['guess'] == 1) {
                        //連碼3中2，中2/中3
                        if ((string) $formula['value'] == '3bingo2') {
                            //正常賠率(中2)
                            foreach ($wanfa['combination'] as $combination) {
                                if (count(array_diff($betValue, $combination)) == 1) {
                                    $record = 1;
                                }
                            }
                            //特殊賠率(中3)
                            if ($wanfa['combination_special'] != []) {
                                foreach ($wanfa['combination_special'] as $combination) {
                                    if (array_diff($betValue, $combination) == []) {
                                        $record = 2;
                                    }
                                }
                            }
                        } else {
                            //正常賠率
                            foreach ($wanfa['combination'] as $combination) {
                                if (array_diff($betValue, $combination) == []) {
                                    $record = 1;
                                }
                            }
                            //特殊賠率
                            if ($wanfa['combination_special'] != [] && ($record == -1 || ($record == 1 && $c_odds['special']['odds'] > $c_odds['normal']['odds']))) {
                                foreach ($wanfa['combination_special'] as $combination) {
                                    if (array_diff($betValue, $combination) == []) {
                                        $record = 2;
                                    }
                                }
                            }
                        }
                    } else {
                        //正常賠率
                        if (array_diff($betValue, $wanfa['combination']) == $betValue) {
                            $record = 1;
                        }
                        //特殊賠率 生肖連和尾數連不中
                        if ($record == 1 && !is_array($wanfa['combination_special']) && in_array($wanfa['combination_special'], $betValue)) {
                            $record = 2;
                        }
                    }
                    $c_val = 0;
                    //中獎注數
                    if ($record == 1) { //正常賠率
                        $odds = $odds_normal;
                        $c_val = bcmul($bet['p_value'], $odds, 2); //精度数字乘法计算
                        $c_value = bcadd($c_value, $c_val, 2);
                        ++$c_odds['normal']['win'];
                    }
                    if ($record == 2) { //特殊賠率
                        $odds = $odds_special;
                        $c_val = bcmul($bet['p_value'], $odds, 2); //精度数字乘法计算
                        $c_value = bcadd($c_value, $c_val, 2);
                        ++$c_odds['special']['win'];
                    }
                    //寫入派彩明細
                    $c_odds['detail'][] = [
                        'bet_value' => $betValue,
                        'odds'      => $odds,
                        'c_value'   => $c_val,
                    ];
                }
            } else {
                //合肖 玩法不同於經典與官方 特別拉出來判斷
                if ($wanfa['combination'] == '') {
                    //空白表示和局
                    $c_value += $bet['p_value'];
                    ++$c_odds['draw']['win'];
                } else {
                    if ($wanfa['guess'] == 1) {
                        if (in_array($wanfa['combination'], $bet_values)) {
                            $c_value = bcadd($c_value, bcmul($bet['p_value'], $bet['odds'], 3), 2); //精度数字乘法计算
                            ++$c_odds['normal']['win'];
                        }
                    } else {
                        if (!in_array($wanfa['combination'], $bet_values)) {
                            $c_value = bcadd($c_value, bcmul($bet['p_value'], $bet['odds'], 3), 2); //精度数字乘法计算
                            ++$c_odds['normal']['win'];
                        }
                    }
                }
            }

            $data = [
                'odds'    => $odds,
                'c_value' => $c_value,
                'c_odds'  => $c_odds,
            ];

            return $c_value == $bet['total_p_value'] ? 2 : ($c_value > 0 ? 1 : 0);
        }
        return 0;
    }

    /**
     * 官方时时彩系列中奖公式计算.
     * @param array $numbers 開獎號碼
     * @param float $p_key_word 父層Keyword
     * @param array $key_word Keyword
     * @return int 輸贏 0:輸 1以上=贏的注數
     */
    public function officialBetTat($numbers, $p_key_word, $key_word, $bet_values)
    {
        $bet_arr = [];
        $numbers_str = '';
        foreach ($numbers as $number) {
            $numbers_str .= $number;
        }

        //此玩法的投注内容需要特殊处理
        if ($key_word == 'three_Back_T_Diff' || $key_word == 'three_Back_T_Sum' || $key_word == 'two_Front_Sum' || $key_word == 'two_Back_Sum') {
            $bet_arr['bet_number'] = $bet_values;
            $bet_arr['open_number'] = implode(',', $numbers);
            //此玩法的投注内容需要特殊处理
        } elseif ($key_word == 'dw_Gall' || $key_word == 'arbitrary_Choice_Direct_Two' || $key_word == 'arbitrary_Choice_Direct_Three' || $key_word == 'arbitrary_Choice_Direct_Four') {
            $bet_values = explode('|', $bet_values);
            foreach ($bet_values as $i => &$value) {
                if (strlen($value) <= 0) {
                    unset($bet_values[$i]);
                    continue;
                }
                if ($i == 0) {
                    $value = str_replace(',', '-w,', $value);
                    $value = $value . '-w';
                }
                if ($i == 1) {
                    $value = str_replace(',', '-q,', $value);
                    $value = $value . '-q';
                }
                if ($i == 2) {
                    $value = str_replace(',', '-b,', $value);
                    $value = $value . '-b';
                }
                if ($i == 3) {
                    $value = str_replace(',', '-s,', $value);
                    $value = $value . '-s';
                }
                if ($i == 4) {
                    $value = str_replace(',', '-g,', $value);
                    $value = $value . '-g';
                }
            }
            $bet_arr = implode('|', $bet_values);
        } else {
            $bet_arr = '';
            $bet_arr = $bet_values;
        }

        $result = $this->compound($bet_arr, $p_key_word, $key_word, 'Tat');
        switch ($p_key_word) {
            case 'Five_stars': //五星
                //五星直选复
                if ($key_word == 'five_Direct_Compound' && in_array($numbers_str, $result)) {
                    return 1;
                }
                //五星组选120
                if ($key_word == 'five_Group120') {
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //五星组选60
                if ($key_word == 'five_Group60') {
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //五星组选30
                if ($key_word == 'five_Group30') {
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //五星组选20
                if ($key_word == 'five_Group20') {
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //五星组选10
                if ($key_word == 'five_Group10') {
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //五星组选5
                if ($key_word == 'five_Group5') {
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                break;
            case 'Four_stars': //四星
                //四星前四直选复
                if ($key_word == 'four_Front_F_Compound') {
                    $numbers_str = substr($numbers_str, 0, -1);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //四星后四直选复
                if ($key_word == 'four_Back_F_Compound') {
                    $numbers_str = substr($numbers_str, 1);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //四星 后4组选24
                if ($key_word == 'four_Back_Group24') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_str = str_split($numbers_str);
                    sort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                // 四星 后4组选12
                if ($key_word == 'four_Back_Group12') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //四星 后4组选6
                if ($key_word == 'four_Back_Group6') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //四星 后4组选4
                if ($key_word == 'four_Back_Group4') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                break;
            case 'Three_stars': //三星
                //三星前三直选复
                if ($key_word == 'three_Front_T_Compound') {
                    $numbers_str = trim(substr($numbers_str, 0, -2));
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //三星中三直选复
                if ($key_word == 'three_In_T_Compound') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_str = substr($numbers_str, 0, -1);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //三星后三直选复
                if ($key_word == 'three_Back_T_Compound') {
                    $numbers_str = substr($numbers_str, 2);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                // 三星后3直选跨度
                if ($key_word == 'three_Back_T_Diff') {
                    $numbers_str = substr($numbers_str, 2);
                    if ($result) {
                        foreach ($result as $key => $value) {
                            if (in_array($numbers_str, $value)) {
                                return 1;
                            }
                        }
                    }
                    return 0;
                }
                //三星后3和值尾数
                if ($key_word == 'three_Back_T_Sum') {
                    $numbers_str = substr($numbers_str, 2);
                    if ($result) {
                        foreach ($result as $key => $value) {
                            if (in_array($numbers_str, $value)) {
                                return 1;
                            }
                        }
                    }
                    return 0;
                }
                //三星前3组3
                if ($key_word == 'three_Front_T_Group3') {
                    $numbers_str = substr($numbers_str, 0, -2);
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //三星前3组6
                if ($key_word == 'three_Front_T_Group6') {
                    $numbers_str = substr($numbers_str, 0, -2);
                    $numbers_str = str_split($numbers_str);
                    sort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //三星中3组3
                if ($key_word == 'three_In_T_Group3') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_str = substr($numbers_str, 0, -1);
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //三星中3组6
                if ($key_word == 'three_In_T_Group6') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_str = substr($numbers_str, 0, -1);
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //三星后3组3
                if ($key_word == 'three_Back_T_Group3') {
                    $numbers_str = substr($numbers_str, 2);
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //三星后3组6
                if ($key_word == 'three_Back_T_Group6') {
                    $numbers_str = substr($numbers_str, 2);
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                break;
            case 'Two_stars': //二星
                //二星前2直选复
                if ($key_word == 'two_Front_Direct_Compound') {
                    $numbers_str = trim(substr($numbers_str, 0, -3));
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //二星后2直选复
                if ($key_word == 'two_Back_Direct_Compound') {
                    $numbers_str = trim(substr($numbers_str, 3));
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //二星前2直选和值
                if ($key_word == 'two_Front_Sum') {
                    $numbers_str = trim(substr($numbers_str, 0, -3));
                    foreach ($result as $value) {
                        if (in_array($numbers_str, $value)) {
                            return 1;
                        }
                    }
                    return 0;
                }
                // 二星后2直选和值
                if ($key_word == 'two_Back_Sum') {
                    $numbers_str = substr($numbers_str, 3);
                    if ($result) {
                        foreach ($result as $key => $value) {
                            if (in_array($numbers_str, $value)) {
                                return 1;
                            }
                        }
                    }
                    return 0;
                }
                //二星前组选复
                if ($key_word == 'two_Front_Group_Compound') {
                    $numbers_str = trim(substr($numbers_str, 0, -3));
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //二星后组选复
                if ($key_word == 'two_Back_Group_Compound') {
                    $numbers_str = substr($numbers_str, 3);
                    $numbers_str = str_split($numbers_str);
                    rsort($numbers_str);
                    $numbers_str_desc = join('', $numbers_str);
                    if (in_array($numbers_str_desc, $result)) {
                        return 1;
                    }
                    return 0;
                }
                break;
            case 'Arbitrary_choice':
                //任选2直选
                if ($key_word == 'arbitrary_Choice_Direct_Two') {
                    $numbers_str = str_split($numbers_str);
                    $numbers_str[0] = $numbers_str[0] . '-w';
                    $numbers_str[1] = $numbers_str[1] . '-q';
                    $numbers_str[2] = $numbers_str[2] . '-b';
                    $numbers_str[3] = $numbers_str[3] . '-s';
                    $numbers_str[4] = $numbers_str[4] . '-g';
                    $arr = $this->common_combination->combinations($numbers_str, 2);
                    $number = 0;
                    foreach ($arr as $key => $value) {
                        $val = implode('', $value);
                        if (in_array($val, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //任选3直选
                if ($key_word == 'arbitrary_Choice_Direct_Three') {
                    $numbers_str = str_split($numbers_str);
                    $numbers_str[0] = $numbers_str[0] . '-w';
                    $numbers_str[1] = $numbers_str[1] . '-q';
                    $numbers_str[2] = $numbers_str[2] . '-b';
                    $numbers_str[3] = $numbers_str[3] . '-s';
                    $numbers_str[4] = $numbers_str[4] . '-g';
                    $arr = $this->common_combination->combinations($numbers_str, 3);
                    $number = 0;
                    foreach ($arr as $key => $value) {
                        $val = implode('', $value);
                        if (in_array($val, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //任选4直选
                if ($key_word == 'arbitrary_Choice_Direct_Four') {
                    $numbers_str = str_split($numbers_str);
                    $numbers_str[0] = $numbers_str[0] . '-w';
                    $numbers_str[1] = $numbers_str[1] . '-q';
                    $numbers_str[2] = $numbers_str[2] . '-b';
                    $numbers_str[3] = $numbers_str[3] . '-s';
                    $numbers_str[4] = $numbers_str[4] . '-g';
                    $arr = $this->common_combination->combinations($numbers_str, 4);
                    $number = 0;
                    foreach ($arr as $key => $value) {
                        $val = implode('', $value);
                        if (in_array($val, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            case 'Interest': //趣味
                //一帆风顺
                if ($key_word == 'everything_Is_Going_Smoothly') {
                    $numbers_str = str_split($numbers_str);
                    $number = 0;
                    $numbers_str = array_unique($numbers_str);
                    foreach ($numbers_str as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            case 'Location_gall': //定位膽
                if ($key_word == 'dw_Gall') {
                    $numbers_str = str_split($numbers_str);
                    $numbers_str[0] = $numbers_str[0] . '-w';
                    $numbers_str[1] = $numbers_str[1] . '-q';
                    $numbers_str[2] = $numbers_str[2] . '-b';
                    $numbers_str[3] = $numbers_str[3] . '-s';
                    $numbers_str[4] = $numbers_str[4] . '-g';
                    $number = 0;
                    foreach ($numbers_str as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            case 'Location_no_gall': //不定膽
                //不定位三星前3 2码不定位
                if ($key_word == 'bdw_Three_Front_Two_Gall') {
                    $numbers_str = trim(substr($numbers_str, 0, -2));
                    $numbers_str = str_split($numbers_str);
                    sort($numbers_str);
                    $arr = $this->common_combination->combinations($numbers_str, 2);
                    $number = 0;
                    $new_arr = [];
                    foreach ($arr as $key => $val) {
                        $new_arr[] = implode('', $val);
                    }
                    $new_arr = array_unique($new_arr);
                    foreach ($new_arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //不定位三星中3 2码不定位
                if ($key_word == 'bdw_Three_In_Two_Gall') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_str = substr($numbers_str, 0, -1);
                    $numbers_str = str_split($numbers_str);
                    sort($numbers_str);
                    $arr = $this->common_combination->combinations($numbers_str, 2);
                    $number = 0;
                    $new_arr = [];
                    foreach ($arr as $key => $val) {
                        $new_arr[] = implode('', $val);
                    }
                    $new_arr = array_unique($new_arr);
                    foreach ($new_arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //不定位三星后3 2码不定位
                if ($key_word == 'bdw_Three_Back_Two_Gall') {
                    $numbers_str = substr($numbers_str, 2);
                    $numbers_str = str_split($numbers_str);
                    sort($numbers_str);
                    $arr = $this->common_combination->combinations($numbers_str, 2);
                    $number = 0;
                    $new_arr = [];
                    foreach ($arr as $key => $val) {
                        $new_arr[] = implode('', $val);
                    }
                    $new_arr = array_unique($new_arr);
                    foreach ($new_arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //不定位 五星2码不定位
                if ($key_word == 'bdw_Five_Two_Gall') {
                    $numbers_str = str_split($numbers_str);
                    //$numbers_str=array_unique($numbers_str);
                    sort($numbers_str);
                    $arr = $this->common_combination->combinations($numbers_str, 2);
                    $number = 0;
                    $new_arr = [];
                    foreach ($arr as $key => $val) {
                        $new_arr[] = implode('', $val);
                    }
                    $new_arr = array_unique($new_arr);
                    foreach ($new_arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //不定位四星后4 2码不定位
                if ($key_word == 'bdw_Four_Back_Two_Gall') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_str = str_split($numbers_str);
                    sort($numbers_str);
                    $arr = $this->common_combination->combinations($numbers_str, 2);
                    $number = 0;
                    $new_arr = [];
                    foreach ($arr as $key => $val) {
                        $new_arr[] = implode('', $val);
                    }
                    $new_arr = array_unique($new_arr);
                    foreach ($new_arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                // 不定位四星后4 1码不定位
                if ($key_word == 'bdw_Four_Back_One_Gall') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_str = str_split($numbers_str);
                    sort($numbers_str);
                    $number = 0;
                    $numbers_str = array_unique($numbers_str);
                    foreach ($numbers_str as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //不定位三星前3 1码不定位
                if ($key_word == 'bdw_Three_Front_One_Gall') {
                    $numbers_str = substr($numbers_str, 0, -2);
                    $numbers_str = str_split($numbers_str);
                    sort($numbers_str);
                    $number = 0;
                    $numbers_str = array_unique($numbers_str);
                    foreach ($numbers_str as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //不定位三星中3 1码不定位
                if ($key_word == 'bdw_Three_In_One_Gall') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_str = substr($numbers_str, 0, -1);
                    $numbers_str = str_split($numbers_str);
                    sort($numbers_str);
                    $number = 0;
                    $numbers_str = array_unique($numbers_str);
                    foreach ($numbers_str as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //不定位三星后3 1码不定位
                if ($key_word == 'bdw_Three_Back_One_Gall') {
                    $numbers_str = substr($numbers_str, 2);
                    $numbers_str = str_split($numbers_str);
                    sort($numbers_str);
                    $number = 0;
                    $numbers_str = array_unique($numbers_str);
                    foreach ($numbers_str as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //不定位 五星3码 3码不定位
                if ($key_word == 'bdw_Five_Stars_Three_Gall') {
                    $numbers_str = str_split($numbers_str);
                    //$numbers_str=array_unique($numbers_str);
                    sort($numbers_str);
                    $arr = $this->common_combination->combinations($numbers_str, 3);
                    $number = 0;
                    $new_arr = [];
                    foreach ($arr as $key => $val) {
                        $new_arr[] = implode('', $val);
                    }
                    $new_arr = array_unique($new_arr);
                    foreach ($new_arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            case 'Size_dan_shuang': //大小单双
                //前2
                if ($key_word == 'tow_Front_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 0, -3));
                    $numbers_str = str_split($numbers_str);
                    $arr_one = [$numbers_str[0] >= 5 ? '大' : '小', $numbers_str[0] % 2 == 0 ? '双' : '单'];
                    $arr_two = [$numbers_str[1] >= 5 ? '大' : '小', $numbers_str[1] % 2 == 0 ? '双' : '单'];
                    $numbers_str = [$arr_one, $arr_two];
                    $arr = $this->common_combination->compound_Combinations($numbers_str)[0];
                    $number = 0;
                    foreach ($arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //后2
                if ($key_word == 'tow_Back_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 3));
                    $numbers_str = str_split($numbers_str);
                    $arr_one = [$numbers_str[0] >= 5 ? '大' : '小', $numbers_str[0] % 2 == 0 ? '双' : '单'];
                    $arr_two = [$numbers_str[1] >= 5 ? '大' : '小', $numbers_str[1] % 2 == 0 ? '双' : '单'];
                    $numbers_str = [$arr_one, $arr_two];
                    $arr = $this->common_combination->compound_Combinations($numbers_str)[0];
                    $number = 0;
                    foreach ($arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //  前3大小单双
                if ($key_word == 'three_Front_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 0, -2));
                    $numbers_str = str_split($numbers_str);
                    $arr_one = [$numbers_str[0] >= 5 ? '大' : '小', $numbers_str[0] % 2 == 0 ? '双' : '单'];
                    $arr_two = [$numbers_str[1] >= 5 ? '大' : '小', $numbers_str[1] % 2 == 0 ? '双' : '单'];
                    $arr_three = [$numbers_str[2] >= 5 ? '大' : '小', $numbers_str[2] % 2 == 0 ? '双' : '单'];
                    $numbers_str = [$arr_one, $arr_two, $arr_three];
                    $arr = $this->common_combination->compound_Combinations($numbers_str)[0];
                    $number = 0;
                    foreach ($arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //  后3大小单双
                if ($key_word == 'three_Back_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 2));
                    $numbers_str = str_split($numbers_str);
                    $arr_one = [$numbers_str[0] >= 5 ? '大' : '小', $numbers_str[0] % 2 == 0 ? '双' : '单'];
                    $arr_two = [$numbers_str[1] >= 5 ? '大' : '小', $numbers_str[1] % 2 == 0 ? '双' : '单'];
                    $arr_three = [$numbers_str[2] >= 5 ? '大' : '小', $numbers_str[2] % 2 == 0 ? '双' : '单'];
                    $numbers_str = [$arr_one, $arr_two, $arr_three];
                    $arr = $this->common_combination->compound_Combinations($numbers_str)[0];
                    $number = 0;
                    foreach ($arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            default:
                return 0;
        }
        return 0;
    }

    /**
     * 官方-PK10中獎公式計算
     * @param array $numbers 開獎號碼
     * @param float $p_key_word 父層Keyword
     * @param array $key_word Keyword
     * @return int 輸贏 0:輸 1以上=贏的注數
     */
    private function officialBetPk10($numbers, $p_key_word, $key_word, $bet_values)
    {
        $bet_arr = [];
        $numbers_str = '';
        foreach ($numbers as $number) {
            $numbers_str .= str_pad($number, 2, '0', STR_PAD_LEFT);
        }

        //此玩法的投注内容需要特殊处理
        if ($key_word == 'low_Three_Direct_Sum') {
            $bet_arr['bet_number'] = $bet_values;
            $bet_arr['open_number'] = implode(',', $numbers);
            //此玩法的投注内容需要特殊处理
        } elseif ($key_word == 'dw_Gall') {
            $bet_values = explode('|', $bet_values);
            foreach ($bet_values as $i => &$value) {
                if (strlen($value) <= 0) {
                    unset($bet_values[$i]);
                    continue;
                }
                if ($i == 0) {
                    $value = str_replace(',', '-a,', $value);
                    $value = $value . '-a';
                }
                if ($i == 1) {
                    $value = str_replace(',', '-b,', $value);
                    $value = $value . '-b';
                }
                if ($i == 2) {
                    $value = str_replace(',', '-c,', $value);
                    $value = $value . '-c';
                }
                if ($i == 3) {
                    $value = str_replace(',', '-d,', $value);
                    $value = $value . '-d';
                }
                if ($i == 4) {
                    $value = str_replace(',', '-e,', $value);
                    $value = $value . '-e';
                }
                if ($i == 5) {
                    $value = str_replace(',', '-f,', $value);
                    $value = $value . '-f';
                }
                if ($i == 6) {
                    $value = str_replace(',', '-g,', $value);
                    $value = $value . '-g';
                }
                if ($i == 7) {
                    $value = str_replace(',', '-h,', $value);
                    $value = $value . '-h';
                }
                if ($i == 8) {
                    $value = str_replace(',', '-i,', $value);
                    $value = $value . '-i';
                }
                if ($i == 9) {
                    $value = str_replace(',', '-j,', $value);
                    $value = $value . '-j';
                }
            }
            $bet_arr = implode('|', $bet_values);
        } else {
            $bet_arr = '';
            $bet_arr = $bet_values;
        }
        $result = $this->compound($bet_arr, $p_key_word, $key_word, 'Pk_Ten');
        switch ($p_key_word) {
            case 'One_front': //前一
                if ($key_word == 'one_Front_Compound') {
                    $numbers_str = substr($numbers_str, 0, 2);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                break;
            case 'Two_front': //前二
                if ($key_word == 'Two_Front_Compound') {
                    $numbers_str = substr($numbers_str, 0, 4);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                break;
            case 'Three_front': //前三
                if ($key_word == 'Three_Front_Compound') {
                    $numbers_str = substr($numbers_str, 0, 6);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                break;
            case 'Location_gall': //定位胆
                if ($key_word == 'dw_Gall') {
                    $numbers_arr = str_split($numbers_str, 2);
                    $numbers_arr[0] = $numbers_arr[0] . '-a';
                    $numbers_arr[1] = $numbers_arr[1] . '-b';
                    $numbers_arr[2] = $numbers_arr[2] . '-c';
                    $numbers_arr[3] = $numbers_arr[3] . '-d';
                    $numbers_arr[4] = $numbers_arr[4] . '-e';
                    $numbers_arr[5] = $numbers_arr[5] . '-f';
                    $numbers_arr[6] = $numbers_arr[6] . '-g';
                    $numbers_arr[7] = $numbers_arr[7] . '-h';
                    $numbers_arr[8] = $numbers_arr[8] . '-i';
                    $numbers_arr[9] = $numbers_arr[9] . '-j';
                    $number = 0;
                    foreach ($result as $value) {
                        if (in_array($value[0], $numbers_arr)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            case 'Size_dan_shuang': //大小单双
                //官方 PK10 冠军
                if ($key_word == 'one_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 0, 2));
                    $arr_one = [(int) $numbers_str >= 6 ? '大' : '小', (int) $numbers_str % 2 == 0 ? '双' : '单'];
                    $number = 0;
                    foreach ($arr_one as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //官方 PK10 亚军
                if ($key_word == 'two_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 2, 2));
                    $arr_one = [(int) $numbers_str >= 6 ? '大' : '小', (int) $numbers_str % 2 == 0 ? '双' : '单'];
                    $number = 0;
                    foreach ($arr_one as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //官方 PK10 季军
                if ($key_word == 'three_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 4, -14));
                    $arr_one = [(int) $numbers_str >= 6 ? '大' : '小', (int) $numbers_str % 2 == 0 ? '双' : '单'];
                    $number = 0;
                    foreach ($arr_one as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //官方 PK10 第四名
                if ($key_word == 'four_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 6, -12));
                    $arr_one = [(int) $numbers_str >= 6 ? '大' : '小', (int) $numbers_str % 2 == 0 ? '双' : '单'];
                    $number = 0;
                    foreach ($arr_one as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //官方 PK10 第五名
                if ($key_word == 'five_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 8, -10));
                    $arr_one = [(int) $numbers_str >= 6 ? '大' : '小', (int) $numbers_str % 2 == 0 ? '双' : '单'];
                    $number = 0;
                    foreach ($arr_one as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //官方 PK10 第六名
                if ($key_word == 'six_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 10, -8));
                    $arr_one = [(int) $numbers_str >= 6 ? '大' : '小', (int) $numbers_str % 2 == 0 ? '双' : '单'];
                    $number = 0;
                    foreach ($arr_one as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //官方 PK10 第七名
                if ($key_word == 'seven_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 12, -6));
                    $arr_one = [(int) $numbers_str >= 6 ? '大' : '小', (int) $numbers_str % 2 == 0 ? '双' : '单'];
                    $number = 0;
                    foreach ($arr_one as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //官方 PK10 第八名
                if ($key_word == 'eight_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 14, -4));
                    $arr_one = [(int) $numbers_str >= 6 ? '大' : '小', (int) $numbers_str % 2 == 0 ? '双' : '单'];
                    $number = 0;
                    foreach ($arr_one as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //官方 PK10 第九名
                if ($key_word == 'nine_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 16, -2));
                    $arr_one = [(int) $numbers_str >= 6 ? '大' : '小', (int) $numbers_str % 2 == 0 ? '双' : '单'];
                    $number = 0;
                    foreach ($arr_one as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //官方 PK10 第十名
                if ($key_word == 'ten_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 18));
                    $arr_one = [(int) $numbers_str >= 6 ? '大' : '小', (int) $numbers_str % 2 == 0 ? '双' : '单'];
                    $number = 0;
                    foreach ($arr_one as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //官方 PK10 冠亚季
                if ($key_word == 'one_two_three_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 0, -14));
                    $numbers_arr = str_split($numbers_str, 2);
                    $numbers_sum = array_sum($numbers_arr);
                    $arr_one = [$numbers_sum >= 17 ? '大' : '小', $numbers_sum % 2 == 0 ? '双' : '单'];
                    $number = 0;
                    foreach ($arr_one as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            case 'Dragon_tiger': //龙虎
                //官方 PK10 冠军
                if ($key_word == 'one_Dragon_Tiger') {
                    $numbers_arr = str_split($numbers_str, 2);
                    $numbers_str = $numbers_arr[0] > $numbers_arr[9] ? '龙' : '虎';
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //官方 PK10 亚军
                if ($key_word == 'two_Dragon_Tiger') {
                    $numbers_arr = str_split($numbers_str, 2);
                    $numbers_str = $numbers_arr[1] > $numbers_arr[8] ? '龙' : '虎';
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //官方 PK10 季军
                if ($key_word == 'three_Dragon_Tiger') {
                    $numbers_arr = str_split($numbers_str, 2);
                    $numbers_str = $numbers_arr[2] > $numbers_arr[7] ? '龙' : '虎';
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //官方 PK10 第四名
                if ($key_word == 'four_Dragon_Tiger') {
                    $numbers_arr = str_split($numbers_str, 2);
                    $numbers_str = $numbers_arr[3] > $numbers_arr[6] ? '龙' : '虎';
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //官方 PK10 第五名
                if ($key_word == 'five_Dragon_Tiger') {
                    $numbers_arr = str_split($numbers_str, 2);
                    $numbers_str = $numbers_arr[4] > $numbers_arr[5] ? '龙' : '虎';
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //官方 PK10 冠亚
                if ($key_word == 'one_Two_Dragon_Tiger') {
                    $numbers_arr = str_split($numbers_str, 2);
                    $numbers_str = ((int) $numbers_arr[0] + (int) $numbers_arr[1]) > ((int) $numbers_arr[8] + (int) $numbers_arr[9]) ? '龙' : '虎';
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //官方 PK10 冠亚季
                if ($key_word == 'one_Two_Three_Dragon_Tiger') {
                    $numbers_arr = str_split($numbers_str, 2);
                    $numbers_str = ((int) $numbers_arr[0] + (int) $numbers_arr[1] + (int) $numbers_arr[2]) > ((int) $numbers_arr[7] + (int) $numbers_arr[8] + (int) $numbers_arr[9]) ? '龙' : '虎';
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                break;
            case 'One_two_three_select': //冠亚季选一
                if ($key_word == 'one_Two_Three_Select') {
                    $numbers_arr = str_split($numbers_str, 2);
                    $numbers_arr = array_splice($numbers_arr, 0, 3);
                    $number = 0;
                    foreach ($numbers_arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            default:
                return 0;
        }
        return 0;
    }

    /**
     * 官方11选5系列中奖公式计算.
     * @param array $numbers 開獎號碼
     * @param float $p_key_word 父層Keyword
     * @param array $key_word Keyword
     * @return int 輸贏 0:輸 1以上=贏的注數
     */
    public function officialBet11x5($numbers, $p_key_word, $key_word, $bet_values)
    {
        $bet_arr = [];
        $numbers_str = '';
        foreach ($numbers as $number) {
            $numbers_str .= str_pad($number, 2, '0', STR_PAD_LEFT);
        }

        //此玩法的投注内容需要特殊处理
        if ($key_word == 'eleven_dw_Gamll') {
            $bet_values = explode('|', $bet_values);
            foreach ($bet_values as $i => &$value) {
                if (strlen($value) <= 0) {
                    unset($bet_values[$i]);
                    continue;
                }
                if ($i == 0) {
                    $value = str_replace(',', '-w,', $value);
                    $value = $value . '-w';
                }
                if ($i == 1) {
                    $value = str_replace(',', '-q,', $value);
                    $value = $value . '-q';
                }
                if ($i == 2) {
                    $value = str_replace(',', '-b,', $value);
                    $value = $value . '-b';
                }
                if ($i == 3) {
                    $value = str_replace(',', '-s,', $value);
                    $value = $value . '-s';
                }
                if ($i == 4) {
                    $value = str_replace(',', '-g,', $value);
                    $value = $value . '-g';
                }
            }
            $bet_arr = implode('|', $bet_values);
        } else {
            $bet_arr = '';
            $bet_arr = $bet_values;
        }

        $getElevenFrontDirectGroupSingle = [
            'eleven_Front_Two_Direct_Single',
            'eleven_Front_Three_Direct_Single',
            'eleven_Front_Two_Group_Single',
            'eleven_Front_Three_Group_Single'
        ];
        if (in_array($key_word, $getElevenFrontDirectGroupSingle) || $p_key_word == 'Arbitrary_Single_choice') {
            $result = explode(',', $bet_values);
        } else {
            $result = $this->compound($bet_arr, $p_key_word, $key_word, 'Eleven_Choice_Five');
        }
        switch ($p_key_word) {
            case 'Front_one': //前一
                //前一 官方11选5系列 直选复选 复式
                if ($key_word == 'eleven_Front_One_Direct_Compound') {
                    $numbers_str = trim(substr($numbers_str, 0, -8));
                    foreach ($result as $key => $value) {
                        if (in_array($numbers_str, $value)) {
                            return 1;
                        }
                    }
                    return 0;
                }
                break;
            case 'Front_two': //前二
                //前二 官方11选5系列 直选 复式
                if ($key_word == 'eleven_Front_Two_Direct_Compound') {
                    $numbers_str = trim(substr($numbers_str, 0, -6));
                    foreach ($result as $key => $value) {
                        if ($numbers_str == implode('', $value)) {
                            return 1;
                        }
                    }
                    return 0;
                }
                //前二 官方11选5系列 组选 复式
                if ($key_word == 'eleven_Front_Two_Group_Compound') {
                    $numbers_str = trim(substr($numbers_str, 0, -6));
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $numbers_str = implode('', $numbers_arr);
                    foreach ($result as $key => $value) {
                        if ($numbers_str == implode('', $value)) {
                            return 1;
                        }
                    }
                    return 0;
                }
                //前二 官方11选5系列 直选 单式
                if ($key_word == 'eleven_Front_Two_Direct_Single') {
                    $numbers_str = trim(substr($numbers_str, 0, -6));
                    foreach ($result as $key => $value) {
                        $value = explode(' ', $value);
                        if ($numbers_str == implode('', $value)) {
                            return 1;
                        }
                    }
                    return 0;
                }
                //前二 官方11选5系列 组选 单式
                /*
                * 因单式為使用者手動key入，故只要合法輸入即可算一注
                * 如果使用者下三注為右形式例 01 03,07 09,03 01
                * 開獎號為例 03 01 02 07 08 ，玩家可中兩注
                * 故組選单式可中好幾注
                */
                if ($key_word == 'eleven_Front_Two_Group_Single') {
                    $numbers_str = trim(substr($numbers_str, 0, -6));
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $numbers_str = implode('', $numbers_arr);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $value = explode(' ', $value);
                        sort($value);
                        if ($numbers_str == implode('', $value)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            case 'Front_three': //前三
                //前三 官方11选5系列 直选复选 复式
                if ($key_word == 'eleven_Front_Three_Direct_Compound') {
                    $numbers_str = trim(substr($numbers_str, 0, -4));
                    foreach ($result as $key => $value) {
                        if ($numbers_str == implode('', $value)) {
                            return 1;
                        }
                    }
                    return 0;
                }
                //前三 官方11选5系列 组选 复式
                if ($key_word == 'eleven_Front_Three_Group_Compound') {
                    $numbers_str = trim(substr($numbers_str, 0, -4));
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $numbers_str = implode('', $numbers_arr);
                    foreach ($result as $key => $value) {
                        if ($numbers_str == implode('', $value)) {
                            return 1;
                        }
                    }
                    return 0;
                }
                //前三 官方11选5系列 直选 单式
                if ($key_word == 'eleven_Front_Three_Direct_Single') {
                    $numbers_str = trim(substr($numbers_str, 0, -4));
                    $numbers_arr = str_split($numbers_str, 2);
                    $numbers_str = implode(' ', $numbers_arr);
                    foreach ($result as $key => $value) {
                        if ($numbers_str == $value) {
                            return 1;
                        }
                    }
                    return 0;
                }
                //前三 官方11选5系列 组选 单式
                /*
                * 因单式為使用者手動key入，故只要合法輸入即可算一注
                * 如果使用者下三注為右形式例 01 02 03,05 07 09,02 01 03
                * 開獎號為例 03 01 02 07 08 ，玩家可中兩注
                * 故組選单式可中好幾注
                */
                if ($key_word == 'eleven_Front_Three_Group_Single') {
                    $numbers_str = trim(substr($numbers_str, 0, -4));
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $numbers_str = implode('', $numbers_arr);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $value = explode(' ', $value);
                        sort($value);
                        if ($numbers_str == implode('', $value)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            case 'Location_no_gall': //不定胆
                if ($key_word == 'eleven_First_Three_bdw_Gamll') {
                    $numbers_str = substr($numbers_str, 0, -4);
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        if (in_array($value[0], $numbers_arr)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            case 'Location_gall': //定位胆
                if ($key_word == 'eleven_dw_Gamll') {
                    $numbers_str = str_split($numbers_str, 2);
                    $numbers_str[0] = $numbers_str[0] . '-w';
                    $numbers_str[1] = $numbers_str[1] . '-q';
                    $numbers_str[2] = $numbers_str[2] . '-b';
                    $numbers_str[3] = $numbers_str[3] . '-s';
                    $numbers_str[4] = $numbers_str[4] . '-g';
                    $number = 0;
                    foreach ($numbers_str as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            case 'Arbitrary_choice': //任選
                //任选 官方11选5系列 一中一
                if ($key_word == 'eleven_Arbitrary_One_To_One') {
                    $numbers_arr = str_split($numbers_str, 2);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        if (in_array($value[0], $numbers_arr)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 二中二
                if ($key_word == 'eleven_Arbitrary_Two_To_Two') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $numbers_arr = $this->common_combination->combinations($numbers_arr, 2);    //列出下注號碼的排列組合
                    $number = 0;
                    foreach ($result as $key => $value) {
                        foreach ($numbers_arr as $keyTwo => $valueTwo) {
                            if (implode('', $value) == implode('', $valueTwo)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 三中三
                if ($key_word == 'eleven_Arbitrary_Three_To_Three') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $numbers_arr = $this->common_combination->combinations($numbers_arr, 3);    //列出下注號碼的排列組合
                    $number = 0;
                    foreach ($result as $key => $value) {
                        foreach ($numbers_arr as $keyTwo => $valueTwo) {
                            if (implode('', $value) == implode('', $valueTwo)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 四中四
                if ($key_word == 'eleven_Arbitrary_Four_To_Four') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $numbers_arr = $this->common_combination->combinations($numbers_arr, 4);    //列出下注號碼的排列組合
                    $number = 0;
                    foreach ($result as $key => $value) {
                        foreach ($numbers_arr as $keyTwo => $valueTwo) {
                            if (implode('', $value) == implode('', $valueTwo)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 五中五
                if ($key_word == 'eleven_Arbitrary_Five_To_Five') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        if (implode('', $value) == implode('', $numbers_arr)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 六中五
                if ($key_word == 'eleven_Arbitrary_Six_To_Five') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $resultCombinations5 = $this->common_combination->combinations($value, 5);
                        foreach ($resultCombinations5 as $keyTwo => $valueTwo) {
                            if (implode('', $valueTwo) == implode('', $numbers_arr)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 七中五
                if ($key_word == 'eleven_Arbitrary_Seven_To_Five') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $resultCombinations5 = $this->common_combination->combinations($value, 5);
                        foreach ($resultCombinations5 as $keyTwo => $valueTwo) {
                            if (implode('', $valueTwo) == implode('', $numbers_arr)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 八中五
                if ($key_word == 'eleven_Arbitrary_Eight_To_Five') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $resultCombinations5 = $this->common_combination->combinations($value, 5);
                        foreach ($resultCombinations5 as $keyTwo => $valueTwo) {
                            if (implode('', $valueTwo) == implode('', $numbers_arr)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                break;
            case 'Arbitrary_Single_choice': //任選 单式
                //任选 官方11选5系列 一中一
                if ($key_word == 'eleven_Arbitrary_Single_One_To_One') {
                    $numbers_arr = str_split($numbers_str, 2);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        if (in_array($value, $numbers_arr)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 二中二
                if ($key_word == 'eleven_Arbitrary_Single_Two_To_Two') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $numbers_arr = $this->common_combination->combinations($numbers_arr, 2);    //列出下注號碼的排列組合
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $value = explode(' ', $value);
                        sort($value);
                        foreach ($numbers_arr as $keyTwo => $valueTwo) {
                            if (implode('', $value) == implode('', $valueTwo)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 三中三
                if ($key_word == 'eleven_Arbitrary_Single_Three_To_Three') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $numbers_arr = $this->common_combination->combinations($numbers_arr, 3);    //列出下注號碼的排列組合
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $value = explode(' ', $value);
                        sort($value);
                        foreach ($numbers_arr as $keyTwo => $valueTwo) {
                            if (implode('', $value) == implode('', $valueTwo)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 四中四
                if ($key_word == 'eleven_Arbitrary_Single_Four_To_Four') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $numbers_arr = $this->common_combination->combinations($numbers_arr, 4);    //列出下注號碼的排列組合
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $value = explode(' ', $value);
                        sort($value);
                        foreach ($numbers_arr as $keyTwo => $valueTwo) {
                            if (implode('', $value) == implode('', $valueTwo)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 五中五
                if ($key_word == 'eleven_Arbitrary_Single_Five_To_Five') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $value = explode(' ', $value);
                        sort($value);
                        if (implode('', $value) == implode('', $numbers_arr)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 六中五
                if ($key_word == 'eleven_Arbitrary_Single_Six_To_Five') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $value = explode(' ', $value);
                        sort($value);
                        $resultCombinations5 = $this->common_combination->combinations($value, 5);
                        foreach ($resultCombinations5 as $keyTwo => $valueTwo) {
                            if (implode('', $valueTwo) == implode('', $numbers_arr)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 七中五
                if ($key_word == 'eleven_Arbitrary_Single_Seven_To_Five') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $value = explode(' ', $value);
                        sort($value);
                        $resultCombinations5 = $this->common_combination->combinations($value, 5);
                        foreach ($resultCombinations5 as $keyTwo => $valueTwo) {
                            if (implode('', $valueTwo) == implode('', $numbers_arr)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                //任选 官方11选5系列 八中五
                if ($key_word == 'eleven_Arbitrary_Single_Eight_To_Five') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);
                    $number = 0;
                    foreach ($result as $key => $value) {
                        $value = explode(' ', $value);
                        sort($value);
                        $resultCombinations5 = $this->common_combination->combinations($value, 5);
                        foreach ($resultCombinations5 as $keyTwo => $valueTwo) {
                            if (implode('', $valueTwo) == implode('', $numbers_arr)) {
                                ++$number;
                            }
                        }
                    }
                    return $number;
                }
                break;
            case 'Interest': //趣味
                //趣味 官方11选5系列 猜中位
                if ($key_word == 'eleven_Interest') {
                    $numbers_arr = str_split($numbers_str, 2);
                    sort($numbers_arr);    //排序由小到大
                    $numbers_str = $numbers_arr[2];    //取開獎排序後的中位
                    foreach ($result as $key => $value) {
                        if (in_array($numbers_str, $value)) {
                            return 1;
                        }
                    }
                    return 0;
                }
                break;
            default:
                return 0;
        }
        return 0;
    }

    /**
     * 官方低频彩系列中奖公式计算.
     * 
     * @param array $numbers 開獎號碼
     * @param float $p_key_word 父層Keyword
     * @param array $key_word Keyword
     * @param string $bet_values 注單
     * @return int 輸贏 0:輸 1以上=贏的注數
     */
    public function officialBetDpc($numbers, $p_key_word, $key_word, $bet_values)
    {
        $bet_arr = [];
        $numbers_str = '';
        foreach ($numbers as $number) {
            $numbers_str .= $number;
        }

        //此玩法的投注内容需要特殊处理
        if ($key_word == 'low_Three_Direct_Sum') {
            $bet_arr['bet_number'] = $bet_values;
            $bet_arr['open_number'] = implode(',', $numbers);
        } else {
            $bet_arr = $bet_values;
        }

        $result = $this->compound($bet_arr, $p_key_word, $key_word, 'Low');
        switch ($p_key_word) {
            case 'Three_code': //三码
                //低頻彩 直选复式
                if ($key_word == 'low_Three_Direct_Compound' && in_array($numbers_str, $result)) {
                    return 1;
                }
                //低頻彩 直选和值
                if ($key_word == 'low_Three_Direct_Sum') {
                    $numbers_str = str_replace(',', '', "$numbers_str");
                    foreach ($result as $key => $value) {
                        if (in_array($numbers_str, $value)) {
                            return 1;
                        }
                    }
                    return 0;
                }
                //低頻彩 组三
                if ($key_word == 'low_Three_Group3') {
                    $numbers_arr = str_split($numbers_str);
                    sort($numbers_arr);
                    $numbers_str = implode($numbers_arr);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //低頻彩 直选和值
                if ($key_word == 'low_Three_Group6') {
                    $numbers_arr = str_split($numbers_str);
                    sort($numbers_arr);
                    $numbers_str = implode($numbers_arr);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                break;
            case 'Two_code': //二码
                //低頻彩 后二 直选复式
                if ($key_word == 'low_Two_Back_Direct_Compound') {
                    $numbers_str = substr($numbers_str, 1);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //低頻彩 后二 组选复式
                if ($key_word == 'low_Two_Back_Group_Compound') {
                    $numbers_str = substr($numbers_str, 1);
                    $numbers_arr = str_split($numbers_str);
                    sort($numbers_arr);
                    $numbers_str = implode($numbers_arr);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //低頻彩 前二 直选复式
                if ($key_word == 'low_Two_Front_Direct_Compound') {
                    $numbers_str = substr($numbers_str, 0, -1);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                //低頻彩 前二 组选复式
                if ($key_word == 'low_Two_Front_Group_Compound') {
                    $numbers_str = substr($numbers_str, 0, -1);
                    $numbers_arr = str_split($numbers_str);
                    sort($numbers_arr);
                    $numbers_str = implode('', $numbers_arr);
                    if (in_array($numbers_str, $result)) {
                        return 1;
                    }
                    return 0;
                }
                break;
            case 'Location_no_gall': //不定胆
                //不定位 低频彩系列 一码不定胆
                if ($key_word == 'low_One_bdw') {
                    $numbers_arr = str_split($numbers_str);
                    $numbers_arr = array_unique($numbers_arr);
                    $number = 0;
                    foreach ($numbers_arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //不定位 低频彩系列 二码不定胆
                if ($key_word == 'low_Two_bdw') {
                    $numbers_arr = str_split($numbers_str);
                    sort($numbers_arr);
                    $numbers_arr = $this->common_combination->combinations($numbers_arr, 2);
                    $number = 0;
                    foreach ($numbers_arr as $key => $value) {
                        $numbers_arr[$key] = implode($value);
                    }
                    $numbers_arr = array_unique($numbers_arr);
                    foreach ($numbers_arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            case 'Location_gall': //定位胆
                //定位 低频彩系列 定位胆
                if ($key_word == 'low_Dw') {
                    foreach (explode('|', $bet_values) as $key => $values) {
                        if (in_array($numbers[$key],explode(',', $values))) {
                            $number++;
                        }
                    }
                    return $number;
                }
                break;
            case 'Size_dan_shuang': //大小单双
                //官方 低频彩系列 前二大小单双
                if ($key_word == 'low_Front_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, 0, -1));
                    $numbers_str = str_split($numbers_str);
                    $arr_one = [$numbers_str[0] >= 5 ? '大' : '小', $numbers_str[0] % 2 == 0 ? '双' : '单'];
                    $arr_two = [$numbers_str[1] >= 5 ? '大' : '小', $numbers_str[1] % 2 == 0 ? '双' : '单'];
                    $numbers_str = [$arr_one, $arr_two];
                    $arr = $this->common_combination->compound_Combinations($numbers_str)[0];
                    $number = 0;
                    foreach ($arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                //官方 低频彩系列 后二大小单双
                if ($key_word == 'low_Back_Big_Smll_Single_Pair') {
                    $numbers_str = trim(substr($numbers_str, -2, 2));
                    $numbers_str = str_split($numbers_str);
                    $arr_one = [$numbers_str[0] >= 5 ? '大' : '小', $numbers_str[0] % 2 == 0 ? '双' : '单'];
                    $arr_two = [$numbers_str[1] >= 5 ? '大' : '小', $numbers_str[1] % 2 == 0 ? '双' : '单'];
                    $numbers_str = [$arr_one, $arr_two];
                    $arr = $this->common_combination->compound_Combinations($numbers_str)[0];
                    $number = 0;
                    foreach ($arr as $key => $value) {
                        if (in_array($value, $result)) {
                            ++$number;
                        }
                    }
                    return $number;
                }
                break;
            default:
                return 0;
        }
        return 0;
    }

    /**
     * 官方-投注注數核算
     * @param array $value_list 注單
     * @param string $p_key_word
     * @param string $key_word
     */
    private function compound($value_list, $p_key_word, $key_word, $dir)
    {
        $this->load->library("Lottery_Permutation_combination/$dir/$p_key_word", strtolower($p_key_word));
        $p_key_word = strtolower($p_key_word);
        $result = $this->$p_key_word->$key_word($value_list);
        return $result;
    }

    /**
     * 經典牛牛
     * @param array $lottery 彩種資料
     * @param array $special 特色棋牌資料
     * @param int   $qishu   期數
     * @param array $number  開獎號碼
     * @return int 狀態
     */
    private function specialCNN($lottery, $special, $qishu, $numbers)
    {
        $Monolog_dir = $lottery['key_word'] . 'BetOpen';
        //取出該期注單
        $where = [
            't.special_id' => $special['id'],
            't.qishu'      => $qishu,
            't.status'     => 0,
        ];
        if ($this->operator_id > 0) {
            $where['operator_id'] = $this->operator_id;
        }
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->ettm_special_bet_record_db->select('t.*')->where($where)->join($join)->result();
        if ($result == []) {
            Monolog::writeLogs($Monolog_dir, 200, "没有注单");
            return 0;
        }

        $commission = bcsub(1, bcdiv($special['commission'], 100, 4), 4);
        $commission1 = bcsub(1, bcdiv($special['commission1'], 100, 4), 4);
        $card_arr = $this->ettm_special_db->getNiuCard($numbers);
        $date = date('Y-m-d H:i:s');
        $user = $update = [];
        foreach ($result as $row) {
            if (!isset($card_arr[$row['bet_values']])) {
                continue;
            }
            $banker_card = $card_arr[0];
            $card = $card_arr[$row['bet_values']];
            $c_value = 0;
            $odds = 1;
            if ($row['money_type'] == 0) {
                //下注主帳戶為正常公式
                if ($card['is_win'] == 1) {
                    $odds = $row['bet_multiple'] > 1 ? $card['multiple']:1;
                    $c_value = bcadd($row['total_p_value'], bcmul(bcmul($row['p_value'], $odds, 2), $commission, 2), 2);
                } else {
                    if ($row['bet_multiple'] > 1) {
                        $c_value = bcsub($row['total_p_value'], bcmul($row['p_value'], $banker_card['multiple'], 2), 2);
                        $odds = $banker_card['multiple'];
                    }
                }
            } else {
                //下注牛牛帳戶為坑人公式
                //同牛數 莊家贏 所以閒家一定要大於莊家
                if ($card['point'] > $banker_card['point']) {
                    //莊沒牛 閒有牛 閒家只能贏一半
                    $multiple = $banker_card['point'] == 0 && $card['point'] > 0 ? 0.5 : 1;
                    $odds = $row['bet_multiple'] > 1 ? bcmul($card['multiple'], $multiple, 2) : $multiple;
                    $c_value = bcadd($row['total_p_value'], bcmul(bcmul($row['p_value'], $odds, 2), $commission1, 2), 2);
                } else {
                    if ($row['bet_multiple'] > 1) {
                        $c_value = bcsub($row['total_p_value'], bcmul($row['p_value'], $banker_card['multiple'], 2), 2);
                        $odds = $banker_card['multiple'];
                    }
                }
            }

            $user[$row['uid']][$row['money_type']] = isset($user[$row['uid']][$row['money_type']]) ? bcadd($user[$row['uid']][$row['money_type']], $c_value, 2) : $c_value;
            $update[] = [
                'id'          => $row['id'],
                'c_value'     => $c_value,
                'odds'        => $odds,
                'is_lose_win' => $card['is_win'],
                'status'      => 1,
                'update_time' => $date,
                'update_by'   => 'OpenAction',
            ];
        }

        Monolog::writeLogs($Monolog_dir, 200, $update);
        $this->base_model->trans_start();
        //寫入開獎結果
        $this->ettm_special_bet_record_db->update_batch($update, 'id');
        foreach ($user as $uid => $money_type_arr) {
            foreach ($money_type_arr as $money_type => $money) {
                $this->user_db->addMoney($uid, $qishu, 16, $money, "$lottery[name]经典牛牛赔付", 3, $lottery['id'], $special['id'], $money_type);
            }
        }
        $this->base_model->trans_complete();

        if ($this->base_model->trans_status()) {
            Monolog::writeLogs($Monolog_dir, 200, 'Result : Success');
            return 1;
        } else {
            Monolog::writeLogs($Monolog_dir, 200, 'Result : Fail');
            return 0;
        }
    }

    /**
     * 搶莊牛牛
     * @param array $lottery 彩種資料
     * @param array $special 特色棋牌資料
     * @param int   $qishu   期數
     * @param array $number  開獎號碼
     * @return int 狀態
     */
    private function specialCCNN($lottery, $special, $qishu, $numbers)
    {
        $Monolog_dir = $lottery['key_word'] . 'BetOpen';
        //取出該期注單
        $where = [
            't.special_id' => $special['id'],
            't.qishu'      => $qishu,
            't.status'     => 0,
        ];
        if ($this->operator_id > 0) {
            $where['operator_id'] = $this->operator_id;
        }
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->ettm_special_bet_record_db->select('t.*')->where($where)->join($join)->result();
        if ($result == []) {
            Monolog::writeLogs($Monolog_dir, 200, "No One Bet");
            return 0;
        }

        $commission = bcsub(1, bcdiv($special['commission'], 100, 4), 4);
        $card_arr = $this->ettm_special_db->getNiuCard($numbers);
        $date = date('Y-m-d H:i:s');
        $user = $update = [];
        $banker_income = 0; //莊家輸贏
        $player_bet = false;
        //先算閒家輸贏
        foreach ($result as $row) {
            if ($row['bet_values'] == 0) {
                continue;
            }
            if (!isset($card_arr[$row['bet_values']])) {
                continue;
            }
            $player_bet = true;
            $banker_card = $card_arr[0];
            $card = $card_arr[$row['bet_values']];
            $c_value = 0;
            $odds = 1;
            if ($card['is_win'] == 1) {
                if ($row['bet_multiple'] > 1) {
                    $c_value = bcadd($row['total_p_value'], bcmul($row['p_value'], $card['multiple'], 2), 2);
                    $odds = $card['multiple'];
                    $banker_income = bcsub($banker_income, bcmul($row['p_value'], $card['multiple']), 2);
                } else {
                    $c_value = bcmul($row['p_value'], 2, 2);
                    $banker_income = bcsub($banker_income, $row['p_value'], 2);
                }
            } else {
                //莊家贏一半
                if ($card['point'] == 0 && $banker_card['point'] == 0) {
                    $c_value = bcmul($row['total_p_value'], 0.5, 2);
                    $odds = 0.5;
                    $banker_income = bcadd($banker_income, bcmul($row['total_p_value'], 0.5), 2);
                } else {
                    if ($row['bet_multiple'] > 1) {
                        $c_value = bcsub($row['total_p_value'], bcmul($row['p_value'], $banker_card['multiple'], 2), 2);
                        $odds = $banker_card['multiple'];
                        $banker_income = bcadd($banker_income, bcmul($row['p_value'], $banker_card['multiple'], 2), 2);
                    } else {
                        $banker_income = bcadd($banker_income, $row['p_value'], 2);
                    }
                }
            }

            $user[$row['uid']][$row['money_type']] = isset($user[$row['uid']][$row['money_type']]) ? bcadd($user[$row['uid']][$row['money_type']], $c_value, 2) : $c_value;
            $update[] = [
                'id'          => $row['id'],
                'c_value'     => $c_value,
                'odds'        => $odds,
                'is_lose_win' => $card['is_win'],
                'status'      => 1,
                'update_time' => $date,
                'update_by'   => 'OpenAction',
            ];
        }

        //再算莊家輸贏
        $user_refund = [];
        foreach ($result as $row) {
            if ($row['bet_values'] != 0) {
                continue;
            }
            if (!isset($card_arr[$row['bet_values']])) {
                continue;
            }

            $banker_card = $card_arr[0];
            $odds = 1;
            $status = 1;
            $is_lose_win = $banker_income > 0 ? 1 : 0;
            $ratio = bcdiv($row['total_p_value'], $special['banker_limit'], 2);
            $profit = $banker_income > 0 ? bcmul(bcmul($banker_income, $ratio, 2), $commission, 2) : bcmul($banker_income, $ratio, 2);
            $c_value = bcadd($row['total_p_value'], $profit, 2);
            //閒家無人下注則訂單無效 退回該款項
            if ($player_bet == false) {
                $user_refund[$row['uid']][$row['money_type']] = isset($user_refund[$row['uid']][$row['money_type']]) ? bcadd($user_refund[$row['uid']][$row['money_type']], $c_value, 2) : $c_value;
                $status = 2;
            } else {
                $user[$row['uid']][$row['money_type']] = isset($user[$row['uid']][$row['money_type']]) ? bcadd($user[$row['uid']][$row['money_type']], $c_value, 2) : $c_value;
            }

            $update[] = [
                'id'          => $row['id'],
                'c_value'     => $c_value,
                'odds'        => $odds,
                'is_lose_win' => $is_lose_win,
                'status'      => $status,
                'update_time' => $date,
                'update_by'   => 'OpenAction',
            ];
        }

        Monolog::writeLogs($Monolog_dir, 200, $update);
        $this->base_model->trans_start();
        //寫入開獎結果
        $this->ettm_special_bet_record_db->update_batch($update, 'id');
        foreach ($user as $uid => $money_type_arr) {
            foreach ($money_type_arr as $money_type => $money) {
                $this->user_db->addMoney($uid, $qishu, 16, $money, "$lottery[name]抢庄牛牛赔付", 3, $lottery['id'], $special['id'], $money_type);
            }
        }
        foreach ($user_refund as $uid => $money_type_arr) {
            foreach ($money_type_arr as $money_type => $money) {
                $this->user_db->addMoney($uid, $qishu, 9, $money, "$lottery[name]抢庄牛牛退款", 3, $lottery['id'], $special['id'], $money_type);
            }
        }
        $this->base_model->trans_complete();

        if ($this->base_model->trans_status()) {
            Monolog::writeLogs($Monolog_dir, 200, 'Result : Success');
            return 1;
        } else {
            Monolog::writeLogs($Monolog_dir, 200, 'Result : Fail');
            return 0;
        }
    }

    /**
     * 向Swoole發送開獎訊息
     * @param array $special 特色棋牌資料
     * @param int $qishu 期數
     * @return int|void
     */
    public function swooleCat($special, $qishu)
    {
        try {
            Monolog::writeLogs('SwooleCat', 200, "SpecialID:$special[id], Qishu:$qishu");
            //取出開獎號
            $record = $this->ettm_lottery_record_db->where([
                'lottery_id' => $special['lottery_id'],
                'qishu'      => $qishu,
                'status'     => 1,
            ])->result_one();
            if ($record === null) {
                return -1;
            }

            $message = json_encode([
                'type'     => 'pai',
                'gameType' => $special['id'],
                'data'     => [
                    'operator_id' => $this->operator_id,
                    'qishu'       => $qishu,
                    'numbers'     => explode(',', $record['numbers']),
                ],
            ]);
            Monolog::writeLogs('SwooleCat', 200, $message);

            $host = $this->site_config['swoole_ip'];
            $port = $this->site_config['swoole_port'] + $special['type'];
            Monolog::writeLogs('SwooleCat', 200, 'HOST:' . $host . ' PORT:' . $port);
            $this->load->library('websocket',[
                'host' => $host,
                'port' => $port
            ]);
            
            if (!$this->websocket->connect()) {
                Monolog::writeLogs('SwooleCat', 200, '连接Server失败!');
                return -1;
            }
            $this->websocket->send($message);
            $this->websocket->disconnect();
            Monolog::writeLogs('SwooleCat', 200, 'END qishu:' . $qishu);
        } catch (Exception $e) {
            Monolog::writeLogs('SwooleCat', 200, 'ERROR:' . $e->getMessage());
            Monolog::writeLogs('SwooleCat', 200, 'Result:Fail');
        }
    }
}
