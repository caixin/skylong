<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_lottery_record_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('ettm_lottery_record_change_model', 'ettm_lottery_record_change_db');
        $this->_table_name = $this->table_ . 'ettm_lottery_record';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'numbers', 'label' => '开奖号码', 'rules' => 'trim']
        ];
    }

    public function _do_where()
    {
        unset($this->_where['operator_id']);

        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        if (isset($this->_where['qishu'])) {
            $this->db->where('t.qishu', $this->_where['qishu']);
            unset($this->_where['qishu']);
        }

        if (isset($this->_where['year'])) {
            $this->db->where('t.lottery_time >=', $this->_where['year'] . '-01-01')
                ->where('t.lottery_time <', ($this->_where['year'] + 1) . '-01-01');
            unset($this->_where['year']);
        }

        if (isset($this->_where['lottery_time1'])) {
            $this->db->where('t.lottery_time >=', $this->_where['lottery_time1'] . ' 00:00:00');
            unset($this->_where['lottery_time1']);
        }

        if (isset($this->_where['lottery_time2'])) {
            $this->db->where('t.lottery_time <=', $this->_where['lottery_time2'] . ' 23:59:59');
            unset($this->_where['lottery_time2']);
        }
    }

    public function row($id, $reset = true)
    {
        $row = parent::row($id, $reset);

        //判斷是否需要替換開獎號碼
        if ($row !== null && $this->operator_id > 0) {
            $this->load->model('ettm_lottery_record_change_model', 'ettm_lottery_record_change_db');
            $change = $this->ettm_lottery_record_change_db->where([
                'operator_id' => $this->operator_id,
                'record_id'   => $row['id'],
            ])->result_one();
            if ($change !== null) {
                $row['numbers']     = $change['numbers'];
                $row['status']      = $change['status'];
                $row['update_time'] = $change['update_time'];
            }
        }

        return $row;
    }

    public function result_one()
    {
        $row = parent::result_one();

        //判斷是否需要替換開獎號碼
        if ($row !== null && $this->operator_id > 0) {
            $this->load->model('ettm_lottery_record_change_model', 'ettm_lottery_record_change_db');
            $change = $this->ettm_lottery_record_change_db->where([
                'operator_id' => $this->operator_id,
                'record_id'   => $row['id'],
            ])->result_one();
            if ($change !== null) {
                $row['numbers']     = $change['numbers'];
                $row['status']      = $change['status'];
                $row['update_time'] = $change['update_time'];
            }
        }

        return $row;
    }

    public function result()
    {
        $result = parent::result();
        $ids = array_column($result, 'id');

        //判斷是否需要替換開獎號碼
        if ($result != [] && $this->operator_id > 0) {
            $this->load->model('ettm_lottery_record_change_model', 'ettm_lottery_record_change_db');
            $change = $this->ettm_lottery_record_change_db->where([
                'operator_id' => $this->operator_id,
                'record_ids'  => $ids,
            ])->result();
            $change = array_column($change, null, 'record_id');
            foreach ($result as $key => $row) {
                if (isset($change[$row['id']])) {
                    $row['numbers'] = $change[$row['id']]['numbers'];
                    $row['status'] = $change[$row['id']]['status'];
                    $row['update_time'] = $change[$row['id']]['update_time'];
                    $result[$key] = $row;
                }
            }
        }

        return $result;
    }

    /**
     * 開獎號碼補0
     *
     * @param int $lottery_type_id 彩種大類
     * @param array|string $numbers 開獎號碼
     * @return array|string
     */
    public function padLeft($lottery_type_id, $numbers)
    {
        if (is_array($numbers)) {
            if (in_array($lottery_type_id, [5,6,8])) {
                return array_map(function ($row) {
                    return sprintf("%02d", $row);
                }, $numbers);
            }
        } else {
            if (in_array($lottery_type_id, [5,6,8])) {
                return string_Pad_Zero_Left($numbers, 2);
            }
        }
        return $numbers;
    }

    /**
     * 取得自營彩開獎號碼
     * @param int $lottery_type_id 彩票大類
     * @return array 開獎號碼
     */
    public function getCustomNumber($lottery_type_id)
    {
        $number = [];
        //依大類開獎
        switch ($lottery_type_id) {
            case 1:
                $number = [rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9)];
                break;
            case 2:
                $number = range(1, 20);
                shuffle($number);
                $number = array_slice($number, 0, 8);
                break;
            case 3:
                $number = [rand(0, 9), rand(0, 9), rand(0, 9)];
                break;
            case 4:
                $number = [rand(1, 6), rand(1, 6), rand(1, 6)];
                break;
            case 5:
                $number = range(1, 10);
                shuffle($number);
                break;
            case 6:
                $number = range(1, 11);
                shuffle($number);
                $number = array_slice($number, 0, 5);
                break;
            case 7:
                $number = [rand(0, 9), rand(0, 9), rand(0, 9)];
                break;
            case 8:
                $number = range(1, 49);
                shuffle($number);
                $number = array_slice($number, 0, 7);
                break;
        }

        return $number;
    }

    /**
     * 取得開獎號碼計算結果
     *
     * @param integer $type_id 彩種大類ID
     * @param string $numbers 開獎號碼
     * @param string $lottery_time 開獎時間
     * @return array
     */
    public function getValue($type_id, $numbers, $lottery_time = '')
    {
        if ($numbers == '') {
            return [];
        }
        $numbers = explode(',', $numbers);
        $total = array_sum($numbers);
        $data = [];

        switch ($type_id) {
            case 2: //快10
                $mantissa = (int) substr($total, -1);
                //總大小
                if ($total > 84) {
                    $data[] = '总大';
                } elseif ($total < 84) {
                    $data[] = '总小';
                } else {
                    $data[] = '和';
                }
                $data[] = $total % 2 == 0 ? '双' : '单'; //單雙
                $data[] = $mantissa >= 5 ? '尾大' : '尾小'; //尾數
                $data[] = $numbers[0] > $numbers[7] ? '龙' : '虎'; //龍虎
                break;
            case 3: //PC28
                $data[] = $total > 13 ? '大' : '小'; //大小
                $data[] = $total % 2 == 1 ? '单' : '双'; //單雙
                $data[] = $numbers[0] > $numbers[2] ? '龙' : ($numbers[0] < $numbers[2] ? '虎' : '和'); //龍虎
                $data[] = $data[0] . $data[1]; //大小單雙
                //極大小
                if ($total >= 22) {
                    $data[] = '极大';
                } elseif ($total <= 5) {
                    $data[] = '极小';
                }
                //牌型
                sort($numbers);
                if (count(array_flip($numbers)) == 1) {
                    $data[] = '豹子';
                } elseif (count(array_flip($numbers)) == 2) {
                    $data[] = '对子';
                } elseif ($numbers[2] - $numbers[1] == 1 && $numbers[1] - $numbers[0] == 1) {
                    $data[] = '顺子';
                }
                break;
            case 4: //快3
                $data[] = $total > 10 ? '大' : '小'; //大小
                $data[] = $total % 2 == 1 ? '单' : '双'; //單雙
                $data[] = $total;
                //牌型
                if (count(array_flip($numbers)) == 1) {
                    $data[] = '豹子';
                }
                break;
            case 5: //PK10
                $data[] = ($numbers[0] + $numbers[1]) > 11 ? '大' : '小'; //大小
                $data[] = ($numbers[0] + $numbers[1]) % 2 == 1 ? '单' : '双'; //單雙
                $data[] = $numbers[0] + $numbers[1]; //冠亞和
                $data[] = $numbers[0] > $numbers[9] ? '龙' : '虎'; //0-9龍虎
                $data[] = $numbers[1] > $numbers[8] ? '龙' : '虎'; //1-8龍虎
                $data[] = $numbers[2] > $numbers[7] ? '龙' : '虎'; //2-7龍虎
                $data[] = $numbers[3] > $numbers[6] ? '龙' : '虎'; //3-6龍虎
                $data[] = $numbers[4] > $numbers[5] ? '龙' : '虎'; //4-5龍虎
                break;
            case 8: //六合彩
                $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
                $data = $this->ettm_classic_wanfa_detail_db->getZodiacCorrespond($numbers, $lottery_time);
                break;
        }

        return $data;
    }

    /**
     * 時時彩計算結果
     */
    public function tat($numbers)
    {
        $top3 = array_slice($numbers, 0, 3);
        $medium3 = array_slice($numbers, 1, 3);
        $after3 = array_slice($numbers, 2, 3);
        sort($top3);
        sort($medium3);
        sort($after3);

        $data = [];
        if (count(array_flip($top3)) == 1) {
            $data['front'] = 1;
        } elseif (($top3[2] - $top3[1] == 1 && $top3[1] - $top3[0] == 1) || $top3 == [0, 1, 9] || $top3 == [0, 8, 9]) {
            $data['front'] = 2;
        } elseif (count(array_flip($top3)) == 2) {
            $data['front'] = 3;
        } elseif ($top3[1] - $top3[0] == 1 || $top3[2] - $top3[1] == 1 || $top3[2] - $top3[0] == 9) {
            $data['front'] = 4;
        } else {
            $data['front'] = 5;
        }

        if (count(array_flip($medium3)) == 1) {
            $data['medium'] = 1;
        } elseif (($medium3[2] - $medium3[1] == 1 && $medium3[1] - $medium3[0] == 1) || $medium3 == [0, 1, 9] || $medium3 == [0, 8, 9]) {
            $data['medium'] = 2;
        } elseif (count(array_flip($medium3)) == 2) {
            $data['medium'] = 3;
        } elseif ($medium3[1] - $medium3[0] == 1 || $medium3[2] - $medium3[1] == 1 || $medium3[2] - $medium3[0] == 9) {
            $data['medium'] = 4;
        } else {
            $data['medium'] = 5;
        }

        if (count(array_flip($after3)) == 1) {
            $data['back'] = 1;
        } elseif (($after3[2] - $after3[1] == 1 && $after3[1] - $after3[0] == 1) || $after3 == [0, 1, 9] || $after3 == [0, 8, 9]) {
            $data['back'] = 2;
        } elseif (count(array_flip($after3)) == 2) {
            $data['back'] = 3;
        } elseif ($after3[1] - $after3[0] == 1 || $after3[2] - $after3[1] == 1 || $after3[2] - $after3[0] == 9) {
            $data['back'] = 4;
        } else {
            $data['back'] = 5;
        }

        return $data;
    }

    public function kl10($numbers)
    {
        $total = array_sum($numbers);
        //大小
        if ($total > 84) {
            $data['value_one'] = '总大';
        }
        if ($total < 84) {
            $data['value_one'] = '总小';
        }
        if ($total == 84) {
            $data['value_one'] = '和';
        }
        //單雙
        $data['value_two'] = ($total % 2 == 0) ? '双' : '单';
        //尾數
        $mantissa = (int) substr($total, -1);
        $data['value_three'] = $mantissa >= 5 ? '尾大' : '尾小';
        //龍虎
        $data['value_four'] = $numbers[0] > $numbers[7] ? '龙' : '虎';

        return $data;
    }

    /**
     * 露珠走勢
     * @param int $lottery_id 彩種ID
     * @return array 露珠資料
     */
    public function trendChart($lottery_id)
    {
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $lottery = $this->ettm_lottery_db->row($lottery_id);

        $where['t.lottery_id'] = $lottery_id;
        $where['t.status'] = 1;
        $where['t.numbers <>'] = '';
        if ($lottery_id == 22) {
            $this->ettm_lottery_record_db->limit([0, 200]);
        } else {
            $this->load->model('qishu_model');
            $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);
            $where['t.lottery_time >='] = date('Y-m-d H:i:s', $qishu_arr['day_start_time']);
        }
        $result = $this->ettm_lottery_record_db->where($where)
            ->order(['qishu', 'desc'])
            ->result();
        $data = $record = [];
        $rank = $tmp = [];
        //初始化
        for ($i = 1; $i <= 3; $i++) {
            if ($i <= 2) {
                if ($lottery['lottery_type_id'] == 5) {
                    $data[$i]['AB']['count_1'] = 0;
                    $data[$i]['AB']['count_2'] = 0;
                }
                for ($j = 1; $j <= self::$numberLength[$lottery['lottery_type_id']]; $j++) {
                    $data[$i][$j]['count_1'] = 0;
                    $data[$i][$j]['count_2'] = 0;
                }
            } else {
                if ($lottery['lottery_type_id'] == 5) {
                    for ($j = 1; $j <= 5; $j++) {
                        $data[$i][$j]['count_1'] = 0;
                        $data[$i][$j]['count_2'] = 0;
                    }
                }
            }
        }
        //大小單雙龍虎
        foreach (array_reverse($result) as $row) {
            $numbers = explode(',', $row['numbers']);
            $all_ball = self::getLotteryTypeBall()[$lottery['lottery_type_id']];
            $median = round(array_sum($all_ball) / count($all_ball));
            $number_arr = [];
            //PK10才有冠亞和
            if ($lottery['lottery_type_id'] == 5) {
                $key = 'AB';
                $win1 = isset($numbers[0]) ? $numbers[0] : 0;
                $win2 = isset($numbers[1]) ? $numbers[1] : 0;
                $record[1][$key][] = ($win1 + $win2) >= 12 ? 'count_1' : 'count_2';
                $win = ($win1 + $win2) >= 12 ? '大' : '小';
                if (!isset($tmp[1][$key]) || $tmp[1][$key] != $win) {
                    $tmp[1][$key] = $win;
                    $rank[1][$key] = isset($rank[1][$key]) ? $rank[1][$key] + 1 : 1;
                }
                $data[1][$key]['detail'][$rank[1][$key]][] = $win;
                $record[2][$key][] = ($win1 + $win2) % 2 == 1 ? 'count_1' : 'count_2';
                $win = ($win1 + $win2) % 2 == 1 ? '单' : '双';
                if (!isset($tmp[2][$key]) || $tmp[2][$key] != $win) {
                    $tmp[2][$key] = $win;
                    $rank[2][$key] = isset($rank[2][$key]) ? $rank[2][$key] + 1 : 1;
                }
                $data[2][$key]['detail'][$rank[2][$key]][] = $win;
            }
            foreach ($numbers as $key => $val) {
                $number_arr[$key + 1] = $val;
            }
            foreach ($number_arr as $key => $val) {
                if ($val != 49) { //六合彩才有49 49不列入
                    //大小露珠
                    $record[1][$key][] = $val >= $median ? 'count_1' : 'count_2';
                    $win = $val >= $median ? '大' : '小';
                    if (!isset($tmp[1][$key]) || $tmp[1][$key] != $win) {
                        $tmp[1][$key] = $win;
                        $rank[1][$key] = isset($rank[1][$key]) ? $rank[1][$key] + 1 : 1;
                    }
                    $data[1][$key]['detail'][$rank[1][$key]][] = $win;
                    //單雙露珠
                    $record[2][$key][] = $val % 2 == 1 ? 'count_1' : 'count_2';
                    $win = $val % 2 == 1 ? '单' : '双';
                    if (!isset($tmp[2][$key]) || $tmp[2][$key] != $win) {
                        $tmp[2][$key] = $win;
                        $rank[2][$key] = isset($rank[2][$key]) ? $rank[2][$key] + 1 : 1;
                    }
                    $data[2][$key]['detail'][$rank[2][$key]][] = $win;
                }
                //龍虎 - PK10才有
                if ($lottery['lottery_type_id'] == 5 && $key > 0 && $key <= 5) {
                    $record[3][$key][] = $val > $number_arr[9 - $key] ? 'count_1' : 'count_2';
                    $win = $val > $number_arr[9 - $key] ? '龙' : '虎';
                    if (!isset($tmp[3][$key]) || $tmp[3][$key] != $win) {
                        $tmp[3][$key] = $win;
                        $rank[3][$key] = isset($rank[3][$key]) ? $rank[3][$key] + 1 : 1;
                    }
                    $data[3][$key]['detail'][$rank[3][$key]][] = $win;
                }
            }
        }
        //寫入各球位大小單雙龍虎總數
        foreach ($record as $type => $row) {
            foreach ($row as $ball => $arr) {
                $arr = array_count_values($arr);
                $data[$type][$ball]['count_1'] = isset($arr['count_1']) ? $arr['count_1']:0;
                $data[$type][$ball]['count_2'] = isset($arr['count_2']) ? $arr['count_2']:0;
            }
        }
        //長龍遺漏
        if ($lottery['lottery_type_id'] == 8) {
            //六合彩系列
            $data[4]['ZL'] = $this->getLongMk6($lottery_id, $result);
            $data[4]['YL'] = $this->getMissingMk6($lottery_id, $result);
        } else {
            //其他經典系列
            $data[4]['ZL'] = $this->getLong($lottery_id, $result);
            $data[4]['YL'] = $this->getMissing($lottery_id, $result);
        }

        return $data;
    }

    /**
     * 各彩種長龍
     *
     * @param integer $lottery_id 彩種ID
     * @param array $record 開獎資料
     * @param integer $long_min 最少長龍次數
     * @return array
     */
    public function getLong($lottery_id, $record = [], $long_min = 4)
    {
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $lottery = $this->ettm_lottery_db->row($lottery_id);

        if ($record == []) {
            $where['t.lottery_id'] = $lottery_id;
            $where['t.status'] = 1;
            $where['t.numbers <>'] = '';
            if ($lottery_id == 22) {
                $this->ettm_lottery_record_db->limit([0, 200]);
            } else {
                $this->load->model('qishu_model');
                $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);
                $where['t.lottery_time >='] = date('Y-m-d H:i:s', $qishu_arr['day_start_time']);
            }
            $record = $this->ettm_lottery_record_db->where($where)
                ->order(['qishu', 'desc'])
                ->result();
        }
        //找出長龍
        $continuous = $win = [];
        $i = 0;
        foreach ($record as $row) {
            if ($i > 0 && $win == []) {
                break;
            }
            $numbers = explode(',', $row['numbers']);
            $all_ball = self::getLotteryTypeBall()[$lottery['lottery_type_id']];
            $median = round(array_sum($all_ball) / count($all_ball));
            $wanfa_win = [];
            //PK10冠亞和
            if ($lottery['lottery_type_id'] == 5) {
                $wanfa_win[] = ($numbers[0] + $numbers[1]) >= 12 ? "bigsmall-AB-1" : "bigsmall-AB-0";
                $wanfa_win[] = ($numbers[0] + $numbers[1]) % 2 == 1 ? "oddeven-AB-1" : "oddeven-AB-0";
            }
            foreach ($numbers as $key => $number) {
                $key++;
                foreach ($all_ball as $val) {
                    if ($number == $val) {
                        $wanfa_win[] = "number-$key-$val";
                    }
                }
                $wanfa_win[] = $number >= $median ? "bigsmall-$key-1" : "bigsmall-$key-0";
                $wanfa_win[] = $number % 2 == 1 ? "oddeven-$key-1" : "oddeven-$key-0";
                //龍虎 - PK10才有
                if ($lottery['lottery_type_id'] == 5 && $key < 5) {
                    $wanfa_win[] = $number > $numbers[9 - $key] ? "longhu-$key-1" : "longhu-$key-0";
                }
            }
            //寫入長龍次數
            $continuous[$i] = array_diff($win, $wanfa_win);
            $win = $win == [] ? $wanfa_win : array_intersect($win, $wanfa_win);
            $i++;
        }
        //排序-由大到小
        krsort($continuous);
        $data = [];
        foreach ($continuous as $count => $row) {
            if ($count >= $long_min) {
                foreach ($row as $val) {
                    $val_arr = explode('-', $val);
                    $arr = [
                        'keyword' => $val,
                        'name'    => $lottery['lottery_type_id'] == 5 ?
                                        self::$numbersKeyPK10[$val_arr[1]]:
                                        self::$numbersKey[$val_arr[1]],
                        'count'   => $count,
                    ];
                    switch ($val_arr[0]) {
                        case 'number':
                            $arr['type'] = "$val_arr[2]号";
                            break;
                        case 'bigsmall':
                            $arr['type'] = $val_arr[2] == 1 ? '大' : '小';
                            break;
                        case 'oddeven':
                            $arr['type'] = $val_arr[2] == 1 ? '单' : '双';
                            break;
                        case 'longhu':
                            $arr['type'] = $val_arr[2] == 1 ? '龙' : '虎';
                            break;
                    }
                    $data[] = $arr;
                }
            }
        }
        return $data;
    }

    /**
     * 各彩種遺漏
     * @param int $lottery_id 彩種ID
     * @param array $record 開獎資料
     */
    public function getMissing($lottery_id, $record = [])
    {
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $lottery = $this->ettm_lottery_db->row($lottery_id);

        if ($record == []) {
            $where['t.lottery_id'] = $lottery_id;
            $where['t.status'] = 1;
            $where['t.numbers <>'] = '';
            if ($lottery_id == 22) {
                $this->ettm_lottery_record_db->limit([0, 200]);
            } else {
                $this->load->model('qishu_model');
                $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);
                $where['t.lottery_time >='] = date('Y-m-d H:i:s', $qishu_arr['day_start_time']);
            }
            $record = $this->ettm_lottery_record_db->where($where)
                ->order(['qishu', 'desc'])
                ->result();
        }
        //找出遺漏
        $continuous = $lose = [];
        $i = 0;
        foreach ($record as $row) {
            if ($i > 0 && $lose == []) {
                break;
            }
            $numbers = explode(',', $row['numbers']);
            $all_ball = self::getLotteryTypeBall()[$lottery['lottery_type_id']];
            $median = round(array_sum($all_ball) / count($all_ball));
            $wanfa_lose = [];
            foreach ($numbers as $key => $number) {
                $key++;
                foreach ($all_ball as $val) {
                    if ($number != $val) {
                        $wanfa_lose[] = "0-$key-$val";
                    }
                }
                $wanfa_lose[] = $number >= $median ? "1-$key-0" : "1-$key-1";
                $wanfa_lose[] = $number % 2 == 1 ? "2-$key-0" : "2-$key-1";
                //龍虎 - PK10才有
                if ($lottery['lottery_type_id'] == 5 && $key < 5) {
                    $wanfa_lose[] = $number > $numbers[9 - $key] ? "3-$key-0" : "3-$key-1";
                }
            }

            //寫入遺漏次數
            $continuous[$i] = array_diff($lose, $wanfa_lose);
            $lose = $lose == [] ? $wanfa_lose : array_intersect($lose, $wanfa_lose);
            $i++;
        }
        //排序-由大到小
        krsort($continuous);
        $data = [];
        foreach ($continuous as $count => $row) {
            if ($count >= 4) {
                foreach ($row as $val) {
                    $val_arr = explode('-', $val);
                    $arr = [
                        'name'    => $lottery['lottery_type_id'] == 5 ?
                                        self::$numbersKeyPK10[$val_arr[1]]:
                                        self::$numbersKey[$val_arr[1]],
                        'count' => $count,
                    ];
                    switch ($val_arr[0]) {
                        case 0:
                            $arr['type'] = "$val_arr[2]号";
                            break;
                        case 1:
                            $arr['type'] = $val_arr[2] == 1 ? '大' : '小';
                            break;
                        case 2:
                            $arr['type'] = $val_arr[2] == 1 ? '单' : '双';
                            break;
                        case 3:
                            $arr['type'] = $val_arr[2] == 1 ? '龙' : '虎';
                            break;
                    }
                    $data[] = $arr;
                }
            }
        }
        return $data;
    }

    /**
     * 六合彩系列的長龍
     * @param int $lottery_id 彩種ID
     * @param array $record 開獎資料
     */
    public function getLongMk6($lottery_id, $record = [], $long_min = 4)
    {
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
        if ($record == []) {
            $where['t.lottery_id'] = $lottery_id;
            $where['t.status'] = 1;
            $where['t.numbers <>'] = '';
            $record = $this->ettm_lottery_record_db->where($where)
                ->order(['qishu', 'desc'])
                ->limit([0, 200])->result();
        }
        $join[] = [$this->table_ . 'ettm_classic_wanfa t1', 't.wanfa_id = t1.id', 'left'];
        $result = $this->ettm_classic_wanfa_detail_db->select('t.*,t1.name')->where([
            't.lottery_type_id' => 8,
            'trend_mode'        => 8,
        ])->join($join)->result();
        //過濾掉號碼
        $wanfa_result = [];
        foreach ($result as $row) {
            $formula = json_decode($row['formula'], true);
            if ($formula['type'] != 'number') {
                $wanfa_result[] = $row;
            }
        }
        $wanfa_result = array_column($wanfa_result, null, 'id');
        $continuous = $win = $draw = [];
        $i = 0;
        foreach ($record as $row) {
            if ($i > 0 && $win == []) {
                break;
            }
            $numbers = explode(',', $row['numbers']);
            $wanfa = $this->ettm_classic_wanfa_detail_db->getWanfaRecordMk62($numbers, $row['lottery_time'], $wanfa_result);
            //寫入長龍次數
            $continuous[$i] = array_diff($win, array_keys($wanfa['win']), array_keys($wanfa['draw']));
            $win = $win == [] ?
                    array_keys((array)$wanfa['win'] + (array)$wanfa['draw']) :
                    array_intersect($win, array_keys((array)$wanfa['win'] + (array)$wanfa['draw']));
            $draw = array_merge($draw, array_intersect($win, array_keys($wanfa['draw'])));
            $i++;
        }
        if ($win != []) {
            $continuous[$i] = $win;
        }
        //扣除和局次數
        $drawCount = array_count_values($draw);
        $data_arr = [];
        foreach ($continuous as $cnt => $wanfa_id) {
            foreach ($wanfa_id as $id) {
                $count = isset($drawCount[$id]) ? $cnt - $drawCount[$id] : $cnt;
                $wd = $wanfa_result[$id];
                $formula = json_decode($wd['formula'], true);
                $type = isset($formula['sub']) ? "$formula[type]|$formula[sub]" : $formula['type'];
                $ball = is_array($formula['ball']) ? implode('|', $formula['ball']) : $formula['ball'];
                $data_arr[$count][] = [
                    'keyword' => "$type-$ball-$formula[value]",
                    'name'  => $wd['name'],
                    'type'  => $wd['values'],
                    'count' => $count,
                ];
            }
        }
        //排序-由大到小
        krsort($data_arr);
        $data = [];
        foreach ($data_arr as $cnt => $row) {
            if ($cnt >= $long_min) {
                foreach ($row as $arr) {
                    $data[] = $arr;
                }
            }
        }
        return $data;
    }

    /**
     * 六合彩系列的遺漏
     * @param int $lottery_id 彩種ID
     * @param array $record 開獎資料
     */
    public function getMissingMk6($lottery_id, $record = [])
    {
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
        if ($record == []) {
            $where['t.lottery_id'] = $lottery_id;
            $where['t.status'] = 1;
            $where['t.numbers <>'] = '';
            $record = $this->ettm_lottery_record_db->where($where)
                ->order(['qishu', 'desc'])
                ->limit([0, 200])->result();
        }
        $join[] = [$this->table_ . 'ettm_classic_wanfa t1', 't.wanfa_id = t1.id', 'left'];
        $result = $this->ettm_classic_wanfa_detail_db->select('t.*,t1.name')->where([
            't.lottery_type_id' => 8,
            'trend_mode'        => 16,
        ])->join($join)->result();
        //只保留特碼 正特 正碼數字部分
        $wanfa_result = [];
        foreach ($result as $row) {
            $formula = json_decode($row['formula'], true);
            if ($formula['type'] == 'number') {
                $wanfa_result[] = $row;
            }
        }
        $name = array_column($wanfa_result, 'name', 'id');
        $type = array_column($wanfa_result, 'values', 'id');
        $continuous = $lose = $draw = [];
        $i = 0;
        foreach ($record as $row) {
            if ($i > 0 && $lose == []) {
                break;
            }
            $numbers = explode(',', $row['numbers']);
            $wanfa = $this->ettm_classic_wanfa_detail_db->getWanfaRecordMk62($numbers, $row['lottery_time'], $wanfa_result);
            //寫入遺漏次數
            $continuous[$i] = array_diff($lose, array_keys($wanfa['lose']), array_keys($wanfa['draw']));
            $lose = $lose == [] ?
                        array_keys((array)$wanfa['lose'] + (array)$wanfa['draw']) :
                        array_intersect($lose, array_keys((array)$wanfa['lose'] + (array)$wanfa['draw']));
            $draw = array_merge($draw, array_intersect($lose, array_keys($wanfa['draw'])));
            $i++;
        }
        if ($lose != []) {
            $continuous[$i] = $lose;
        }
        //扣除和局次數
        $drawCount = array_count_values($draw);
        $data_arr = [];
        foreach ($continuous as $cnt => $wanfa_id) {
            foreach ($wanfa_id as $id) {
                $count = isset($drawCount[$id]) ? $cnt - $drawCount[$id] : $cnt;
                $data_arr[$count][] = [
                    'name'  => $name[$id],
                    'type'  => $type[$id],
                    'count' => $count,
                ];
            }
        }
        //排序-由大到小
        krsort($data_arr);
        $data = [];
        foreach ($data_arr as $cnt => $row) {
            if ($cnt >= 40) {
                foreach ($row as $arr) {
                    $data[] = $arr;
                }
            }
        }
        return $data;
    }

    public static function getLotteryTypeBall()
    {
        return [
            1 => range(0, 9),
            2 => range(1, 20),
            3 => range(0, 9),
            4 => range(1, 6),
            5 => range(1, 10),
            6 => range(1, 11),
            7 => range(0, 9),
            8 => range(1, 49),
        ];
    }

    public static $numberLength = [
        1 => 5,
        2 => 8,
        3 => 3,
        4 => 3,
        5 => 10,
        6 => 5,
        7 => 3,
        8 => 7,
    ];

    public static $numbersKey = [
        1 => '第一球',
        2 => '第二球',
        3 => '第三球',
        4 => '第四球',
        5 => '第五球',
        6 => '第六球',
        7 => '第七球',
        8 => '第八球',
        9 => '第九球',
        10 => '第十球',
    ];

    public static $numbersKeyPK10 = [
        'AB' => '冠亚和',
        1 => '第一名',
        2 => '第二名',
        3 => '第三名',
        4 => '第四名',
        5 => '第五名',
        6 => '第六名',
        7 => '第七名',
        8 => '第八名',
        9 => '第九名',
        10 => '第十名',
    ];

    public static $statusList = [
        0 => '待开奖',
        1 => '已开奖',
        2 => '开奖失败(已退款)',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'         => '编号',
        'lottery_id' => '彩种编号',
        'qishu'      => '期数',
        'numbers'    => '开奖号码',
        'status'     => '狀態',
    ];
}
