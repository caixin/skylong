<?php defined('BASEPATH') || exit('No direct script access allowed');

class Crontabs extends AdminCommon
{
    public function __construct()
    {
        parent::__construct();
        $this->session->set_userdata('username', 'Crontabs');
        $this->load->model('qishu_model');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('ettm_lottery_record_change_model', 'ettm_lottery_record_change_db');
        $this->load->model('ettm_lottery_open_model');
    }

    /**
     * 寫入開獎號碼並派獎
     */
    public function setLotteryNumber()
    {
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $key_word  = $this->input->post('key_word', true);
            $qishu     = $this->input->post('qishu', true);
            $numbers   = $this->input->post('numbers', true);
            $update_by = $this->input->post('update_by', true);
            $this->session->set_userdata('username', $update_by);

            $join[] = [$this->table_.'ettm_lottery t1','t.lottery_id = t1.id','left'];
            $record = $this->ettm_lottery_record_db->select('t.*,t1.lottery_type_id,t1.name')->where([
                't1.key_word' => $key_word,
                't.qishu'     => $qishu,
            ])->join($join)->result_one();
            if ($record !== null && $record['status'] == 0) {
                //過濾開獎號碼
                $numberLength = Ettm_lottery_record_model::$numberLength[$record['lottery_type_id']];
                $numbers_arr = explode(',', $numbers);
                //開獎號碼少於預設碼數 發送警告訊息
                if (count($numbers_arr) < $numberLength) {
                    //發送Telegram訊息
                    $msg = "【{$key_word}】當期({$qishu}_{$numbers})_開獎號碼少於{$numberLength}碼，請確認是否有誤";
                    $bot = new \TelegramBot\Api\BotApi($this->config->item('telegram_bot_token'));
                    $chatid = $this->config->item('telegram_chatid_'.ENVIRONMENT);
                    $bot->sendMessage($chatid, $msg);
                }
                $numbers = implode(',', array_slice($numbers_arr, 0, $numberLength));
                //撈出上一期
                $last = $this->ettm_lottery_record_db->select('t.*,t1.name')->where([
                    't1.key_word' => $key_word,
                    't.qishu <'   => $qishu,
                ])->join($join)->order(['qishu', 'desc'])->result_one();
                //判斷開獎號
                if ($last !== null) {
                    $msg = '';
                    if ($last['status'] == 0) {
                        $msg = "【{$key_word}】當期($record[qishu]_{$numbers})_上期($last[qishu])尚未開獎，請確認是否有誤";
                    } elseif ($last['numbers'] == '') {
                        $msg = "【{$key_word}】當期($record[qishu]_{$numbers})_上期($last[qishu])開獎號碼為空，請確認是否有誤";
                    } elseif ($last['numbers'] == $numbers) {
                        $msg = "【{$key_word}】當期($record[qishu]_{$numbers})與上期($last[qishu]_$last[numbers])開獎結果相同，請確認是否有誤";
                    }
                    if ($msg != '') {
                        //發送Telegram訊息
                        $bot = new \TelegramBot\Api\BotApi($this->config->item('telegram_bot_token'));
                        $chatid = $this->config->item('telegram_chatid_'.ENVIRONMENT);
                        $bot->sendMessage($chatid, $msg);
                    }
                }
                //寫入開獎號碼
                $this->ettm_lottery_record_db->update([
                    'id'      => $record['id'],
                    'numbers' => $numbers,
                    'status'  => 1,
                ]);
                //派獎
                $this->ettm_lottery_open_model->openAction($record['lottery_id'], $qishu);
            }
            echo 'ok';
        }
    }

    /**
     * 彩種派獎
     */
    public function openAction($lottery_id, $qishu)
    {
        $this->ettm_lottery_open_model->openAction($lottery_id, $qishu);
    }

    /**
     * 彩種派獎
     */
    public function swooleCat($special_id, $qishu)
    {
        $this->load->model('ettm_special_model', 'ettm_special_db');
        $special = $this->ettm_special_db->row($special_id);
        $this->ettm_lottery_open_model->swooleCat($special, $qishu);
    }

    /**
     * 預寫隔天期數
     */
    public function writeQishu($date = '')
    {
        $date = $date == '' ? date('Y-m-d', time() + 86400) : $date;

        $lottery = $this->ettm_lottery_db->result();
        foreach ($lottery as $row) {
            $this->qishu_model->writeQishu($row['id'], $date);
        }
    }

    /**
     * 香港六合彩期數錄入
     */
    public function writeQishuHkmk6()
    {
        $date = date('Y-m-d H:i:s');
        $result = file_get_contents('https://is.hkjc.com/jcbw/marksix/fixturesC.xml');
        $data = simplexml_load_string($result);

        $data = $data->drawYear;
        if (count($data) == 0) {
            return;
        }

        $insert = [];
        foreach ($data as $row) {
            $year = $row['year'];
            $record = $this->ettm_lottery_record_db->where([
                'lottery_id' => 22,
                'year'       => $year,
            ])->order(['qishu', 'desc'])->result_one();
            $qishu = isset($record['qishu']) ? $record['qishu'] + 1 : intval($year . '001');
            $create_time = isset($record['lottery_time']) ? $record['lottery_time'] : '2000-01-01';

            foreach ($row->drawMonth as $arr) {
                $month = $arr['month'];

                foreach ($arr->drawDate as $val) {
                    $lottery_time = date('Y-m-d H:i:s', strtotime("$year-$month-$val[date] 21:30:00"));
                    if ($lottery_time > $create_time) {
                        $insert[] = [
                            'lottery_id'   => 22,
                            'qishu'        => $qishu++,
                            'lottery_time' => $lottery_time,
                            'create_time'  => $date,
                            'create_by'    => 'Crontab',
                        ];
                        $create_time = $date;
                    }
                }
            }
        }

        if ($insert !== []) {
            $this->ettm_lottery_record_db->insert_batch($insert);
        }
    }

    /**
     * 自訂彩種開獎
     */
    public function customLotteryOpen()
    {
        sleep(9); //延遲10秒開獎
        $this->load->model('operator_model', 'operator_db');
        $this->load->model('ettm_lottery_cheat_model', 'ettm_lottery_cheat_db');
        $this->load->model('ettm_lottery_cheat_log_model', 'ettm_lottery_cheat_log_db');
        $this->ettm_lottery_cheat_db->is_action_log = false;

        $cheat = [];
        //吃大賠小
        $result = $this->ettm_lottery_cheat_db->where(['t.type' => 0, 't.status >' => 0])->result();
        foreach ($result as $row) {
            $cheat[$row['operator_id']][$row['type']][$row['lottery_id']] = $row['status'];
        }
        //不開豹子
        $result = $this->ettm_lottery_cheat_db->where(['t.type' => 1, 't.status' => 1])->result();
        foreach ($result as $row) {
            $cheat[$row['operator_id']][$row['type']][$row['lottery_id']] = $row['status'];
        }
        //機率性必贏
        $result = $this->ettm_lottery_cheat_db->where(['t.type' => 3, 't.status' => 1])->result();
        foreach ($result as $row) {
            $cheat[$row['operator_id']][$row['type']][$row['lottery_id']] = $row;
        }
        //取得營運商
        $operator = $this->operator_db->getList();
        //取得所有自訂彩種
        $lottery_list = $this->ettm_lottery_db->where(['is_custom' => 1])->result();
        foreach ($lottery_list as $lottery) {
            //取得當前期數
            $qishu_arr = $this->qishu_model->getQishu(1, $lottery['id']);
            $record = $this->ettm_lottery_record_db->where([
                'lottery_id' => $lottery['id'],
                'qishu'      => $qishu_arr['qishu'],
            ])->result_one();
            //無資料或已開獎則跳出
            if ($record === null || $record['status'] != 0) {
                continue;
            }
            //寫入主表號碼
            $master_numbers = $this->ettm_lottery_record_db->getCustomNumber($lottery['lottery_type_id']);
            $this->ettm_lottery_record_db->update([
                'id'      => $record['id'],
                'numbers' => implode(',', $master_numbers),
                'status'  => 1
            ]);
            //控制各個運營商號碼
            foreach ($operator as $operator_id => $operator_name) {
                $this->operator_id = $operator_id;
                $numbers = $master_numbers;
                $is_change = false;
                //控制開獎號碼
                $cheat2 = $this->ettm_lottery_cheat_db->where([
                    'operator_id'  => $operator_id,
                    't.type'       => 2,
                    't.lottery_id' => $lottery['id'],
                    't.qishu'      => $qishu_arr['qishu'],
                    't.status'     => 0,
                ])->result_one();
                if ($cheat2 !== null) {
                    $is_change = true;
                    $numbers = $cheat2['numbers'];
                    $this->ettm_lottery_cheat_db->update([
                        'id'     => $cheat2['id'],
                        'status' => 1,
                    ]);
                } else {
                    //機率性吃大賠小
                    $must_win = false;
                    if (isset($cheat[$operator_id][3][$lottery['id']])) {
                        $cheat3 = $cheat[$operator_id][3][$lottery['id']];
                        $nowtime = date('H:i:s');
                        $starttime = $cheat3['starttime'];
                        $endtime = $cheat3['endtime'];
                        $percent = $cheat3['percent'];
                        if ($nowtime >= $starttime && $nowtime <= $endtime) {
                            if (rand(1, 100) <= (int) $percent) {
                                $must_win = true;
                            }
                        }
                    }

                    $change = false;
                    while (true) {
                        //變更號碼
                        if ($change) {
                            $change = false;
                            $is_change = true;
                            $numbers = $this->ettm_lottery_record_db->getCustomNumber($lottery['lottery_type_id']);
                        }
                        //不開豹子
                        if (isset($cheat[$operator_id][1][$lottery['id']])) {
                            if ($numbers[0] == $numbers[1] && $numbers[1] == $numbers[2]) {
                                $change = true;
                                $this->ettm_lottery_cheat_log_db->insert([
                                    'type'       => 1,
                                    'lottery_id' => $lottery['id'],
                                    'qishu'      => $qishu_arr['qishu'],
                                    'numbers'    => implode(',', $numbers),
                                ]);
                                continue;
                            }
                        }
                        if (isset($cheat[$operator_id][0][$lottery['id']]) || isset($cheat[$operator_id][3][$lottery['id']])) {
                            //取得開獎號碼總獲利
                            $profit = $this->ettm_lottery_open_model->getProfit($lottery['id'], $qishu_arr['qishu'], $numbers);
                            if (isset($cheat[$operator_id][3][$lottery['id']])) {
                                //機率性吃大賠小
                                if ($must_win && $profit < 0) {
                                    $change = true;
                                    $this->ettm_lottery_cheat_log_db->insert([
                                        'type'       => 3,
                                        'lottery_id' => $lottery['id'],
                                        'qishu'      => $qishu_arr['qishu'],
                                        'numbers'    => implode(',', $numbers),
                                        'profit'     => $profit,
                                    ]);
                                    continue;
                                }
                            } else {
                                //吃大賠小
                                $cheat0 = $cheat[$operator_id][0][$lottery['id']];
                                if ($cheat0 == 1 && $profit < 0) {
                                    $change = true;
                                    $this->ettm_lottery_cheat_log_db->insert([
                                        'type'       => 0,
                                        'lottery_id' => $lottery['id'],
                                        'qishu'      => $qishu_arr['qishu'],
                                        'numbers'    => implode(',', $numbers),
                                        'profit'     => $profit,
                                    ]);
                                    continue;
                                }
                                //吃小賠大
                                if ($cheat0 == 2 && $profit > 0) {
                                    $change = true;
                                    $this->ettm_lottery_cheat_log_db->insert([
                                        'type'       => 0,
                                        'lottery_id' => $lottery['id'],
                                        'qishu'      => $qishu_arr['qishu'],
                                        'numbers'    => implode(',', $numbers),
                                        'profit'     => $profit,
                                    ]);
                                    continue;
                                }
                            }
                        }
                        if (!$change) {
                            break;
                        }
                    }
                    $numbers = implode(',', $numbers);
                }
                //寫入號碼
                if ($is_change) {
                    $this->ettm_lottery_record_change_db->update([
                        'operator_id' => $operator_id,
                        'record_id'   => $record['id'],
                        'numbers'     => $numbers,
                        'status'      => 1
                    ]);
                }
                //派獎
                Monolog::writeLogs('CustomLotteryOpen', 200, "運營商:$operator_name 彩种:$lottery[name] 期数:$qishu_arr[qishu] 開獎號碼:$numbers");
                $this->ettm_lottery_open_model->openAction($lottery['id'], $qishu_arr['qishu']);
            }
        }
    }

    /**
     * 有效打碼量計算
     */
    public function codeAmount()
    {
        $this->load->model('code_amount_model', 'code_amount_db');
        $this->code_amount_db->setBetEffect(1); //經典
        $this->code_amount_db->setBetEffect(2); //官方
        $this->code_amount_db->setBetEffect(3); //特色-現金帳戶
        $this->code_amount_db->setBetEffect(3, 1); //特色-特色棋牌帳戶
    }

    /**
     * 統計用戶返水
     */
    public function userRakeback()
    {
        $this->load->model('user_rakeback_model', 'user_rakeback_db');
        $this->load->model('operator_model', 'operator_db');
        $operator = $this->operator_db->getList(1);
        foreach ($operator as $id => $name) {
            $this->operator_id = $id;
            $this->user_rakeback_db->rakeback();
        }
    }

    /**
     * 統計用戶下注報表
     */
    public function dailyUserReport($date = '')
    {
        $this->load->model('daily_user_report_model', 'daily_user_report_db');
        if ($date == '') {
            $date = date('Y-m-d', time() - 3600);
        }
        $this->daily_user_report_db->statistics($date);
    }

    /**
     * 每日執行排程
     */
    public function dailySchedule($date = '', $enforce = 0)
    {
        set_time_limit(0);
        if ($date == '') {
            $date = date('Y-m-d', time() - 86400);
        }
        $this->analysisChart($date, $enforce);
        $this->retention($date, $enforce);
        $this->retentionDaily($date, $enforce);
    }

    /**
     * 統計圖表
     */
    public function analysisChart($date = '', $enforce = 0)
    {
        if ($date == '') {
            $date = date('Y-m-d', time() - 86400);
        }
        $this->load->model('operator_model', 'operator_db');
        $this->load->model('user_model', 'user_db');
        $this->load->model('user_login_log_model', 'user_login_log_db');
        $this->load->model('concurrent_user_model', 'concurrent_user_db');
        $this->load->model('daily_analysis_model', 'daily_analysis_db');
        //強制執行 先刪除已存在的資料
        if ($enforce) {
            $this->daily_analysis_db->where(['day_time' => $date])->delete_where();
        }
        //判斷是否已執行過(有資料)
        if ($this->daily_analysis_db->where(['day_time' => $date])->count() == 0) {
            $operator = $this->operator_db->where(['status' => 1])->result();
            foreach ($operator as $row) {
                //NUU
                $NUU = $this->user_db->where([
                    'type'         => 0,
                    'operator_id'  => $row['id'],
                    'create_time1' => $date,
                    'create_time2' => $date,
                ])->count();
                $this->daily_analysis_db->insert([
                    'operator_id' => $row['id'],
                    'day_time'    => $date,
                    'type'        => 1,
                    'count'       => $NUU,
                ]);
                //DAU
                $DAU = $this->user_login_log_db->getLoginUser($date, $date, $row['id']);
                $this->daily_analysis_db->insert([
                    'operator_id' => $row['id'],
                    'day_time'    => $date,
                    'type'        => 2,
                    'count'       => $DAU,
                ]);
                //WAU
                $WAU = $this->user_login_log_db->getLoginUser(date('Y-m-d', strtotime($date) - 86400 * 6), $date, $row['id']);
                $this->daily_analysis_db->insert([
                    'operator_id' => $row['id'],
                    'day_time'    => $date,
                    'type'        => 3,
                    'count'       => $WAU,
                ]);
                //MAU
                $MAU = $this->user_login_log_db->getLoginUser(date('Y-m-01', strtotime($date)), $date, $row['id']);
                $this->daily_analysis_db->insert([
                    'operator_id' => $row['id'],
                    'day_time'    => $date,
                    'type'        => 4,
                    'count'       => $MAU,
                ]);
                //DAU-NUU
                $this->daily_analysis_db->insert([
                    'operator_id' => $row['id'],
                    'day_time'    => $date,
                    'type'        => 5,
                    'count'       => $DAU - $NUU,
                ]);
                //PCU
                $ccu = $this->concurrent_user_db->escape(false)->select('IFNULL(MAX(count),0) count')->where([
                    'operator_id'  => $row['id'],
                    'per'          => 1,
                    'minute_time1' => $date . ' 00:00:00',
                    'minute_time2' => $date . ' 23:59:59',
                ])->result_one();
                $this->daily_analysis_db->insert([
                    'operator_id' => $row['id'],
                    'day_time'    => $date,
                    'type'        => 6,
                    'count'       => $ccu['count'],
                ]);
            }
        }
    }

    /**
     * 留存率
     */
    public function retention($date = '', $enforce = 0)
    {
        if ($date == '') {
            $date = date('Y-m-d', time() - 86400);
        }
        $this->load->model('operator_model', 'operator_db');
        $this->load->model('user_model', 'user_db');
        $this->load->model('daily_retention_model', 'daily_retention_db');

        //強制執行 先刪除已存在的資料
        if ($enforce) {
            $this->daily_retention_db->where(['day_time' => $date])->delete_where();
        }
        //判斷是否已執行過(有資料)
        if ($this->daily_retention_db->where(['day_time' => $date])->count() == 0) {
            $operator = $this->operator_db->where(['status' => 1])->result();
            foreach ($operator as $row) {
                //總數
                $all = $this->user_db->where([
                    'type'        => 0,
                    'operator_id' => $row['id'],
                ])->count();
                for ($i = 1; $i <= 6; $i++) {
                    $data = $this->user_db->retention($i, $row['id']);
                    $this->daily_retention_db->insert([
                        'operator_id' => $row['id'],
                        'day_time'    => $date,
                        'type'        => $i,
                        'all_count'   => $all,
                        'day_count'   => isset($data['day_count']) ? $data['day_count'] : 0,
                        'avg_money'   => isset($data['avg_money']) ? $data['avg_money'] : 0,
                    ]);
                }
            }
        }
    }

    /**
     * 新帳號保留率
     */
    public function retentionDaily($date = '', $enforce = 0)
    {
        if ($date == '') {
            $date = date('Y-m-d', time() - 86400);
        }
        $this->load->model('operator_model', 'operator_db');
        $this->load->model('user_model', 'user_db');
        $this->load->model('daily_retention_user_model', 'daily_retention_user_db');

        //強制執行 先刪除已存在的資料
        if ($enforce) {
            $this->daily_retention_user_db->where(['day_time' => $date])->delete_where();
        }

        //判斷是否已執行過(有資料)
        if ($this->daily_retention_user_db->where(['day_time' => $date])->count() == 0) {
            $operator = $this->operator_db->where(['status' => 1])->result();
            foreach ($operator as $row) {
                for ($i = 1; $i <= 5; $i++) {
                    $arr = $this->user_db->retention_daily($i, $date, $row['id']);
                    $this->daily_retention_user_db->insert([
                        'operator_id' => $row['id'],
                        'day_time'    => $date,
                        'type'        => $i,
                        'all_count'   => $arr['all_count'],
                        'day_count'   => $arr['day_count'],
                        'percent'     => $arr['all_count'] == 0 ? 0 : round($arr['day_count'] / $arr['all_count'] * 100),
                    ]);
                }
            }
        }
    }

    /**
     * 資金匯總
     */
    public function digest($start_date = '', $end_date = '')
    {
        $this->load->model('daily_digest_model', 'daily_digest_db');

        if (empty($start_date) || empty($end_date)) {
            $start_date = $end_date = date("Y-m-d");
            //因為有六合彩，所以每天凌晨1點時要更新前3天的資料
            if (date("H") === '01') {
                $start_date = date("Y-m-d", strtotime("-3 day"));
            }
        }
        $result = $this->daily_digest_db->statistics($start_date, $end_date);
        echo $result;
    }

    /**
     * 投注熱度虛擬下注
     */
    public function predictionBet()
    {
        $this->load->model('prediction_robot_bet_model', 'prediction_robot_bet_db');

        for ($i=0;$i<10;$i++) {
            $this->prediction_robot_bet_db->robotBet();
            sleep(5);
        }
    }
}
