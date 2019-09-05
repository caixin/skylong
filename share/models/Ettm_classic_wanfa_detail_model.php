<?php defined('BASEPATH') || exit('No direct script access allowed');

use Overtrue\ChineseCalendar\Calendar;

class Ettm_classic_wanfa_detail_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'ettm_classic_wanfa_detail';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'lottery_type_id', 'label' => '彩种类别', 'rules' => 'trim|required'],
            ['field' => 'values', 'label' => '玩法值', 'rules' => 'trim|required'],
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
        if (isset($this->_where['wanfa_pid'])) {
            $this->db->where('t1.pid', $this->_where['wanfa_pid']);
            unset($this->_where['wanfa_pid']);
        }
        if (isset($this->_where['wanfa_id'])) {
            $this->db->where('t.wanfa_id', $this->_where['wanfa_id']);
            unset($this->_where['wanfa_id']);
        }
        if (isset($this->_where['values'])) {
            $this->db->like('t.values', $this->_where['values'], 'both');
            unset($this->_where['values']);
        }
        if (isset($this->_where['trend_mode'])) {
            $this->db->where('t.trend_mode &', $this->_where['trend_mode']);
            unset($this->_where['trend_mode']);
        }
    }

    /**
     * 賠率運算
     * @param int $lottery_id 彩種ID
     * @param int $qishu 期數
     * @param int $uid 用戶ID
     * @param array $betlist 注單資料(可選)
     * @param array $param 額外條件
     * @param int $only_reduce_total 只比對全部降賠
     */
    public function oddsCalculation($lottery_id, $qishu, $uid = 0, $betlist = [], $param = [], $only_reduce_total = 0)
    {
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_classic_odds_control_model', 'ettm_classic_odds_control_db');
        $this->load->model('ettm_reduce_model', 'ettm_reduce_db');
        $this->load->model('agent_code_model', 'agent_code_db');
        $lottery = $this->ettm_lottery_db->row($lottery_id);
        $betlist = array_column($betlist, 'bet_money', 'id');
        //取得營運商ID
        if (isset($param['operator_id'])) {
            $operator_id = $param['operator_id'];
        } else {
            $operator_id = $this->operator_id;
        }
        //彩種控盤
        $result = $this->ettm_classic_odds_control_db->where([
            'operator_id' => $operator_id,
            'lottery_id'  => $lottery_id,
            'qishu'       => $qishu,
        ])->result();
        $odds_adjust = [];
        foreach ($result as $row) {
            $odds_adjust["$row[wanfa_detail_id]-$row[is_special]-$row[interval]"] = $row['adjust'];
        }

        //代理抽水
        $agent = 0;
        if ($uid != 0) {
            $up_data = $this->agent_code_db->getUpReturnPoint($uid, $lottery_id);
            $agent = bcdiv(array_sum($up_data), 100, 5);
        }

        //降賠
        $reduce = $this->ettm_reduce_db->getReduce($operator_id, $lottery['lottery_type_id'], $lottery_id, 1);
        $reduce_all = $this->ettm_reduce_db->getReduce($operator_id, $lottery['lottery_type_id'], $lottery_id, 0);
        $pvalue = $this->ettm_classic_bet_record_db->getPValueByWanfa($lottery_id, $qishu, $uid);
        $pvalue_all = $this->ettm_classic_bet_record_db->getPValueByWanfa($lottery_id, $qishu);

        $join[] = [$this->table_ . 'ettm_classic_wanfa t1', 't.wanfa_id = t1.id', 'left'];
        $select = 't.*, t1.pid, t1.name';
        $where['t.lottery_type_id'] = $lottery['lottery_type_id'];
        if (isset($param['wanfa_pid'])) {
            $where['t1.pid'] = $param['wanfa_pid'];
        }
        if (isset($param['wanfa_id'])) {
            $where['t.wanfa_id'] = $param['wanfa_id'];
        }
        if (isset($param['wanfa_detail_id'])) {
            $where['t.id'] = $param['wanfa_detail_id'];
        }
        $result = $this->select($select)->join($join)->where($where)->order(['sort', 'asc'])->result();
        foreach ($result as $key => $row) {
            //降賠%數
            $total = isset($pvalue[$row['id']]) ? $pvalue[$row['id']] : 0;
            $total = isset($betlist[$row['id']]) ? $total + $betlist[$row['id']] : $total; //加入注單金額
            $total_all = isset($pvalue_all[$row['id']]) ? $pvalue_all[$row['id']] : 0;
            $total_all = isset($betlist[$row['id']]) ? $total_all + $betlist[$row['id']] : $total_all; //加入注單金額
            
            $interval_self = $interval_all = 0;
            $reduce_adjust_self = $reduce_adjust_all = 0;
            //個人降賠
            $tmp = 0;
            foreach ($reduce as $arr) {
                for ($i = 0; $i < $arr['count']; $i++) {
                    $tmp += $arr['interval'];
                    if ($total < $tmp) {
                        break;
                    }
                    $reduce_adjust_self += $arr['value'];
                    $interval_self = $tmp;
                }
            }
            //全部降賠
            $tmp = 0;
            foreach ($reduce_all as $arr) {
                for ($i = 0; $i < $arr['count']; $i++) {
                    $tmp += $arr['interval'];
                    if ($total_all < $tmp) {
                        break;
                    }
                    $reduce_adjust_all += $arr['value'];
                    $interval_all = $tmp;
                }
            }

            if ($only_reduce_total == 0) {
                $reduce_adjust = max($reduce_adjust_self, $reduce_adjust_all);
                $interval = max($interval_self, $interval_all);
            } else {
                $reduce_adjust = $reduce_adjust_all;
                $interval = $interval_all;
            }
            $reduce_adjust = bcdiv($reduce_adjust, 100, 5);

            //A盤獲利
            $adjustment = $this->operator == [] ? 1 : $this->operator['classic_adjustment'];
            $line_a_profit = bcdiv(bcmul($row['line_a_profit'], $adjustment, 3), 100, 5);
            $line_a_special = bcdiv(bcmul($row['line_a_special'], $adjustment, 3), 100, 5);

            //公式: 滿盤 * (1 - (A盤獲利+代理返水+降賠%數)) - 控盤調整
            $row['odds'] = bcmul($row['odds'], bcsub(1, bcadd(bcadd($line_a_profit, $agent, 5), $reduce_adjust, 5), 5), 3);
            $row['odds_special'] = bcmul($row['odds_special'], bcsub(1, bcadd(bcadd($line_a_special, $agent, 5), $reduce_adjust, 5), 5), 3);

            //控盤調整
            if (isset($odds_adjust["$row[id]-0-$interval"])) {
                $row['odds'] = bcadd($row['odds'], $odds_adjust["$row[id]-0-$interval"], 3);
            }
            if (isset($odds_adjust["$row[id]-1-$interval"])) {
                $row['odds_special'] = bcadd($row['odds_special'], $odds_adjust["$row[id]-1-$interval"], 3);
            }

            $row['interval'] = $interval;
            $result[$key] = $row;
        }
        return $result;
    }

    /**
     * 取得六合彩號碼對應生肖
     *
     * @param array $numbers 號碼
     * @param string $lottery_time 開獎時間
     * @return array 對應生肖
     */
    public function getZodiacCorrespond($numbers, $lottery_time)
    {
        $calendar = new Calendar();
        $year = (int) date('Y', strtotime($lottery_time));
        $month = (int) date('m', strtotime($lottery_time));
        $day = (int) date('d', strtotime($lottery_time));
        $lunar = $calendar->solar($year, $month, $day);
        $zodiac = 0;
        foreach (self::$zodiacType as $key => $val) {
            if ($val == $lunar['animal']) {
                $zodiac = $key;
            }
        }
        $data = [];
        foreach ($numbers as $val) {
            $tmp = $zodiac - (($val - 1) % 12);
            $tmp = $tmp < 0 ? $tmp + 12 : $tmp;
            $data[] = self::$zodiacType[$tmp];
        }
        return $data;
    }

    /**
     * 取得生肖對應號碼
     *
     * @param int $lottery_time 開獎時間(時間戳記格式)
     * @param int $max_number 最多幾個號碼
     * @return array
     */
    public function getZodiacNumber($lottery_time, $max_number = 49)
    {
        $calendar = new Calendar();
        $year = (int) date('Y', $lottery_time);
        $month = (int) date('m', $lottery_time);
        $day = (int) date('d', $lottery_time);
        $lunar = $calendar->solar($year, $month, $day);
        $zodiac = 0;
        foreach (self::$zodiacType as $key => $val) {
            if ($val == $lunar['animal']) {
                $zodiac = $key;
            }
        }
        $data[-1] = $zodiac;
        for ($i = 1; $i <= $max_number; $i++) {
            $tmp = $zodiac - (($i - 1) % 12);
            $tmp = $tmp < 0 ? $tmp + 12 : $tmp;
            $data[$tmp][] = $i;
        }
        ksort($data);

        return $data;
    }

    /**
     * 取得尾數對應號碼
     */
    public function getMantissaNumber($max_number = 49)
    {
        $data = [];
        for ($i = 1; $i <= $max_number; $i++) {
            $mantissa = $i % 10;
            $data[$mantissa][] = $i;
        }

        return $data;
    }

    /**
     * 生肖key轉換value
     * @param string|array $data 轉換前key
     * @return string|array 轉換後value
     */
    public function zodiacToValue($data)
    {
        $arr = [];
        if (is_array($data)) {
            foreach ($data as $val) {
                $arr[] = self::$zodiacType[$val];
            }
            return $arr;
        } else {
            $data = explode(',', $data);
            foreach ($data as $val) {
                $arr[] = self::$zodiacType[$val];
            }
            return implode(',', $arr);
        }
    }

    /**
     * 六合彩玩法結果 - 結算用
     * @param array $numbers 開獎號碼
     * @param string $lottery_time 開獎時間
     */
    public function getWanfaRecordMk6($numbers, $lottery_time)
    {
        //生肖對應號碼
        $zodiac = $this->getZodiacNumber(strtotime($lottery_time));
        //取出所有玩法詳情
        $result = $this->select("id,wanfa_id,values,odds,odds_special,formula")
            ->where(['lottery_type_id' => 8])->result();

        $win = $draw = $lose = $official = [];
        foreach ($result as $row) {
            if ($row['formula'] == '') {
                continue;
            }

            $formula = json_decode($row['formula'], true);
            $record = -1;  //1:贏 0:和 -1:輸
            //數字
            if ($formula['type'] == 'number') {
                if (is_array($formula['ball'])) {
                    //正碼 6個號碼中一個即中獎
                    foreach ($formula['ball'] as $ball) {
                        if ($numbers[$ball] == $formula['value']) {
                            $record = 1;
                        }
                    }
                } else {
                    if ($numbers[$formula['ball']] == $formula['value']) {
                        $record = 1;
                    }
                }
            }
            //大小
            if ($formula['type'] == 'bigsmall') {
                if (isset($formula['sub'])) {
                    //半波
                    if ($formula['sub'] == 'halfwave') {
                        if ($formula['value'] == 1 && $numbers[$formula['ball']] >= 25 && in_array($numbers[$formula['ball']], self::$colorBall[$formula['color']])) {
                            $record = 1;
                        }
                        if ($formula['value'] == 0 && $numbers[$formula['ball']] <= 24 && in_array($numbers[$formula['ball']], self::$colorBall[$formula['color']])) {
                            $record = 1;
                        }
                    }
                } else {
                    if (is_array($formula['ball'])) {
                        $count = 0;
                        foreach ($formula['ball'] as $ball) {
                            $count += $numbers[$ball];
                        }

                        if ($formula['value'] == 1 && $count >= 175) {
                            $record = 1;
                        }
                        if ($formula['value'] == 0 && $count <= 174) {
                            $record = 1;
                        }
                    } else {
                        if ($formula['value'] == 1 && $numbers[$formula['ball']] >= 25) {
                            $record = 1;
                        }
                        if ($formula['value'] == 0 && $numbers[$formula['ball']] <= 24) {
                            $record = 1;
                        }
                        if ($numbers[$formula['ball']] == 49) {
                            $record = 0;
                        }
                    }
                }
            }
            //單雙
            if ($formula['type'] == 'oddeven') {
                if (isset($formula['sub'])) {
                    //半波
                    if ($formula['sub'] == 'halfwave') {
                        if ($numbers[$formula['ball']] % 2 == $formula['value'] && in_array($numbers[$formula['ball']], self::$colorBall[$formula['color']])) {
                            $record = 1;
                        }
                        if ($numbers[$formula['ball']] % 2 == $formula['value'] && in_array($numbers[$formula['ball']], self::$colorBall[$formula['color']])) {
                            $record = 1;
                        }
                    }
                } else {
                    if (is_array($formula['ball'])) {
                        $count = 0;
                        foreach ($formula['ball'] as $ball) {
                            $count += $numbers[$ball];
                        }

                        if ($count % 2 == $formula['value']) {
                            $record = 1;
                        }
                    } else {
                        if ($numbers[$formula['ball']] % 2 == $formula['value']) {
                            $record = 1;
                        }
                        if ($numbers[$formula['ball']] == 49) {
                            $record = 0;
                        }
                    }
                }
            }
            //合單合雙
            if ($formula['type'] == 'add_oddeven') {
                if ((($numbers[$formula['ball']] / 10) + ($numbers[$formula['ball']] % 10)) % 2 == $formula['value']) {
                    $record = 1;
                }
                if ($numbers[$formula['ball']] == 49) {
                    $record = 0;
                }
            }
            //尾大尾小
            if ($formula['type'] == 'mantissa') {
                if ($formula['value'] == 1 && $numbers[$formula['ball']] % 10 >= 5) {
                    $record = 1;
                }
                if ($formula['value'] == 0 && $numbers[$formula['ball']] % 10 <= 4) {
                    $record = 1;
                }
                if ($numbers[$formula['ball']] == 49) {
                    $record = 0;
                }
            }
            //紅藍綠波
            if ($formula['type'] == 'color') {
                if (in_array($numbers[$formula['ball']], self::$colorBall[$formula['value']])) {
                    $record = 1;
                }
            }
            //正特尾
            if ($formula['type'] == 'ball_mantissa') {
                foreach ($formula['ball'] as $ball) {
                    if ($numbers[$ball] % 10 == $formula['value']) {
                        $record = 1;
                    }
                }
            }
            //特肖一肖
            if ($formula['type'] == 'zodiac') {
                $ball_arr = $zodiac[$formula['value']];
                if (is_array($formula['ball'])) {
                    //一肖
                    foreach ($formula['ball'] as $ball) {
                        if (in_array($numbers[$ball], $ball_arr)) {
                            $record = 1;
                        }
                    }
                } else {
                    //特肖
                    if (in_array($numbers[$formula['ball']], $ball_arr)) {
                        $record = 1;
                    }
                }
            }
            //---------------------------------------------官方彩玩法-----------------------------------------
            $numbers = array_map('intval', $numbers);
            $no = $numbers;
            $sno = $no[6];
            unset($no[6]);
            //連碼
            if ($formula['type'] == 'LianMa') {
                $record = 2;
                $row['guess'] = 1;
                $row['combination_number'] = 2;
                $row['combination_special'] = [];
                if ($formula['value'] == 'bingo4') {
                    $row['combination_number'] = 4;
                    $row['combination'] = combination($no, 4);
                }
                if ($formula['value'] == 'bingo3') {
                    $row['combination_number'] = 3;
                    $row['combination'] = combination($no, 3);
                }
                if ($formula['value'] == 'bingo2') {
                    $row['combination_number'] = 2;
                    $row['combination'] = combination($no, 2);
                }
                if ($formula['value'] == '3bingo2') {
                    $row['combination_number'] = 3;
                    $row['combination_special'] = combination($no, 3);
                    $row['combination'] = combination($no, 2);
                }
                //特串組合
                $combination = [];
                foreach ($no as $val) {
                    $combination[] = [$sno, $val];
                }
                if ($formula['value'] == 'bingo2_special') {
                    $row['combination_number'] = 2;
                    $row['combination_special'] = combination($no, 2);
                    $row['combination'] = $combination;
                }
                if ($formula['value'] == 'special') {
                    $row['combination_number'] = 2;
                    $row['combination'] = $combination;
                }
            }
            //合肖
            if ($formula['type'] == 'HeXiao') {
                $record = 2;
                $row['guess'] = $formula['guess'];
                $row['combination_special'] = [];
                if ($sno == 49) {
                    $row['combination'] = '';
                } else {
                    foreach ($zodiac as $key => $val) {
                        if ($key == -1) {
                            continue;
                        }
                        if (in_array($sno, $val)) {
                            $row['combination'] = $key;
                        }
                    }
                }
            }
            //生肖連
            if ($formula['type'] == 'ShengXiaoLian') {
                $record = 2;
                $row['guess'] = $formula['guess'];
                $row['combination_number'] = $formula['value'];
                $row['combination'] = $row['combination_special'] = [];
                $open = [];
                foreach ($zodiac as $key => $val) {
                    if ($key == -1) {
                        continue;
                    }
                    foreach ($numbers as $number) {
                        if (in_array($number, $val)) {
                            $open[$key] = $key;
                        }
                    }
                }
                $open = array_values($open);
                if ($formula['guess'] == 1) {
                    $combination = combination($open, $formula['value']);
                    foreach ($combination as $arr) {
                        if (in_array($zodiac[-1], $arr)) {
                            $row['combination_special'][] = $arr;
                        } else {
                            $row['combination'][] = $arr;
                        }
                    }
                } else {
                    $row['combination'] = $open;
                    $row['combination_special'] = $zodiac[-1];
                }
            }
            //尾數連
            if ($formula['type'] == 'WeiShuLian') {
                $record = 2;
                $row['guess'] = $formula['guess'];
                $row['combination_number'] = $formula['value'];
                $row['combination'] = $row['combination_special'] = [];
                $open = [];
                foreach ($numbers as $number) {
                    $mantissa = $number % 10;
                    $open[$mantissa] = $mantissa;
                }
                $open = array_values($open);
                if ($formula['guess'] == 1) {
                    $combination = combination($open, $formula['value']);
                    foreach ($combination as $arr) {
                        if (in_array(0, $arr)) {
                            $row['combination_special'][] = $arr;
                        } else {
                            $row['combination'][] = $arr;
                        }
                    }
                } else {
                    $row['combination'] = $open;
                    $row['combination_special'] = 0;
                }
            }
            //全不中
            if ($formula['type'] == 'QuanBuZhong') {
                $record = 2;
                $row['guess'] = 0;
                $row['combination'] = $numbers;
                $row['combination_number'] = $formula['value'];
                $row['combination_special'] = [];
            }

            if ($record == 1) {
                $win[$row['id']] = $row;
            }
            if ($record == 0) {
                $draw[$row['id']] = $row;
            }
            if ($record == -1) {
                $lose[$row['id']] = $row;
            }
            if ($record == 2) {
                $official[$row['id']] = $row;
            }
        }

        return [
            'win'      => $win,
            'draw'     => $draw,
            'lose'     => $lose,
            'official' => $official,
        ];
    }

    //
    /**
     * 玩法結果 - 露珠用
     * @param array $numbers 開獎號碼
     * @param string $lottery_time 開獎時間
     * @param array $wanfa 玩法資料
     * @return array
     */
    public function getWanfaRecordMk62($numbers, $lottery_time, $wanfa)
    {
        //生肖對應號碼
        $zodiac = $this->getZodiacNumber(strtotime($lottery_time));
        $win = $draw = $lose = [];
        foreach ($wanfa as $row) {
            if ($row['formula'] == '') {
                continue;
            }

            $formula = json_decode($row['formula'], true);
            $record = -1;  //1:贏 0:和 -1:輸
            //數字
            if ($formula['type'] == 'number') {
                if (is_array($formula['ball'])) {
                    //正碼 6個號碼中一個即中獎
                    foreach ($formula['ball'] as $ball) {
                        if ($numbers[$ball] == $formula['value']) {
                            $record = 1;
                        }
                    }
                } else {
                    if ($numbers[$formula['ball']] == $formula['value']) {
                        $record = 1;
                    }
                }
            }
            //大小
            if ($formula['type'] == 'bigsmall') {
                if (isset($formula['sub'])) {
                    //半波
                    if ($formula['sub'] == 'halfwave') {
                        if ($formula['value'] == 1 && $numbers[$formula['ball']] >= 25 && in_array($numbers[$formula['ball']], self::$colorBall[$formula['color']])) {
                            $record = 1;
                        }
                        if ($formula['value'] == 0 && $numbers[$formula['ball']] <= 24 && in_array($numbers[$formula['ball']], self::$colorBall[$formula['color']])) {
                            $record = 1;
                        }
                    }
                } else {
                    if (is_array($formula['ball'])) {
                        $count = 0;
                        foreach ($formula['ball'] as $ball) {
                            $count += $numbers[$ball];
                        }

                        if ($formula['value'] == 1 && $count >= 175) {
                            $record = 1;
                        }
                        if ($formula['value'] == 0 && $count <= 174) {
                            $record = 1;
                        }
                    } else {
                        if ($formula['value'] == 1 && $numbers[$formula['ball']] >= 25) {
                            $record = 1;
                        }
                        if ($formula['value'] == 0 && $numbers[$formula['ball']] <= 24) {
                            $record = 1;
                        }
                        if ($numbers[$formula['ball']] == 49) {
                            $record = 0;
                        }
                    }
                }
            }
            //單雙
            if ($formula['type'] == 'oddeven') {
                if (isset($formula['sub'])) {
                    //半波
                    if ($formula['sub'] == 'halfwave') {
                        if ($numbers[$formula['ball']] % 2 == $formula['value'] && in_array($numbers[$formula['ball']], self::$colorBall[$formula['color']])) {
                            $record = 1;
                        }
                        if ($numbers[$formula['ball']] % 2 == $formula['value'] && in_array($numbers[$formula['ball']], self::$colorBall[$formula['color']])) {
                            $record = 1;
                        }
                    }
                } else {
                    if (is_array($formula['ball'])) {
                        $count = 0;
                        foreach ($formula['ball'] as $ball) {
                            $count += $numbers[$ball];
                        }

                        if ($count % 2 == $formula['value']) {
                            $record = 1;
                        }
                    } else {
                        if ($numbers[$formula['ball']] % 2 == $formula['value']) {
                            $record = 1;
                        }
                        if ($numbers[$formula['ball']] == 49) {
                            $record = 0;
                        }
                    }
                }
            }
            //合單合雙
            if ($formula['type'] == 'add_oddeven') {
                if ((($numbers[$formula['ball']] / 10) + ($numbers[$formula['ball']] % 10)) % 2 == $formula['value']) {
                    $record = 1;
                }
                if ($numbers[$formula['ball']] == 49) {
                    $record = 0;
                }
            }
            //尾大尾小
            if ($formula['type'] == 'mantissa') {
                if ($formula['value'] == 1 && $numbers[$formula['ball']] % 10 >= 5) {
                    $record = 1;
                }
                if ($formula['value'] == 0 && $numbers[$formula['ball']] % 10 <= 4) {
                    $record = 1;
                }
                if ($numbers[$formula['ball']] == 49) {
                    $record = 0;
                }
            }
            //紅藍綠波
            if ($formula['type'] == 'color') {
                if (in_array($numbers[$formula['ball']], self::$colorBall[$formula['value']])) {
                    $record = 1;
                }
            }
            //正特尾
            if ($formula['type'] == 'ball_mantissa') {
                foreach ($formula['ball'] as $ball) {
                    if ($numbers[$ball] % 10 == $formula['value']) {
                        $record = 1;
                    }
                }
            }
            //特肖一肖
            if ($formula['type'] == 'zodiac') {
                $ball_arr = $zodiac[$formula['value']];
                if (is_array($formula['ball'])) {
                    //一肖
                    foreach ($formula['ball'] as $ball) {
                        if (in_array($numbers[$ball], $ball_arr)) {
                            $record = 1;
                        }
                    }
                } else {
                    //特肖
                    if (in_array($numbers[$formula['ball']], $ball_arr)) {
                        $record = 1;
                    }
                }
            }

            if ($record == 1) {
                $win[$row['id']] = $row;
            }
            if ($record == 0) {
                $draw[$row['id']] = $row;
            }
            if ($record == -1) {
                $lose[$row['id']] = $row;
            }
        }

        return [
            'win'  => $win,
            'draw' => $draw,
            'lose' => $lose,
        ];
    }

    /**
     * 取得露珠玩法ID
     * @param int $mode 露珠模式
     */
    public function getTrendWanfaID($lottery_type_id, $mode)
    {
        $join[] = [$this->table_ . 'ettm_classic_wanfa t1', 't.wanfa_id = t1.id', 'left'];
        $wanfa = $this->select('t.*,t1.pid')->join($join)->where([
            'lottery_type_id' => $lottery_type_id,
            'trend_mode'      => $mode,
        ])->result();
        $related = [];
        foreach ($wanfa as $row) {
            $payload = $lottery_type_id == 8 ? json_decode($row['formula'], true) : json_decode($row['payload'], true);
            $type = isset($payload['sub']) ? "$payload[type]|$payload[sub]" : $payload['type'];
            $ball = is_array($payload['ball']) ? implode('|', $payload['ball']) : $payload['ball'];
            if (in_array($type, ['bigsmall', 'oddeven', 'longhu'])) {
                $related["$type-$ball"][] = $row['id'];
            }
        }
        $data = [];
        foreach ($wanfa as $row) {
            $payload = $lottery_type_id == 8 ? json_decode($row['formula'], true) : json_decode($row['payload'], true);
            $type = isset($payload['sub']) ? "$payload[type]|$payload[sub]" : $payload['type'];
            $ball = is_array($payload['ball']) ? implode('|', $payload['ball']) : $payload['ball'];
            if (in_array($type, ['bigsmall', 'oddeven', 'longhu'])) {
                $row['related'] = $related["$type-$ball"];
                $data["$type-$ball-$payload[value]"] = $row;
            }
        }
        return $data;
    }

    public static $colorBall = [
        'r' => [1, 2, 7, 8, 12, 13, 18, 19, 23, 24, 29, 30, 34, 35, 40, 45, 46],
        'b' => [3, 4, 9, 10, 14, 15, 20, 25, 26, 31, 36, 37, 41, 42, 47, 48],
        'g' => [5, 6, 11, 16, 17, 21, 22, 27, 28, 32, 33, 38, 39, 43, 44, 49],
    ];

    public static $zodiacType = [
        0  => '鼠',
        1  => '牛',
        2  => '虎',
        3  => '兔',
        4  => '龙',
        5  => '蛇',
        6  => '马',
        7  => '羊',
        8  => '猴',
        9  => '鸡',
        10 => '狗',
        11 => '猪',
    ];

    public static $trend_modeList = [
        1 => '大小',
        2 => '单双',
        4 => '龙虎',
        8 => '长龙',
        16 => '遗漏',
    ];

    public static $is_deleteList = [
        1 => '正常',
        0 => '已删除',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'              => '编号',
        'lottery_type_id' => '彩种类别',
        'wanfa_id'        => '玩法類型',
        'values'          => '玩法值',
        'values_sup'      => '輔助玩法值',
        'line_a_profit'   => 'A盘获利(%)',
        'line_a_special'  => 'A盘特殊(%)',
        'odds'            => '賠率',
        'odds_special'    => '特殊賠率',
        'qishu_max_money' => '单期最大限额',
        'bet_max_money'   => '单笔最大限额',
        'bet_min_money'   => '最小下注额',
        'formula'         => '中奖公式',
        'max_number'      => '玩法值选号上限',
        'sort'            => '排序',
        'is_delete'       => '是否删除',
    ];
}
