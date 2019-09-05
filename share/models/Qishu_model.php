<?php defined('BASEPATH') || exit('No direct script access allowed');

class Qishu_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'ettm_lottery';
        $this->_key = 'id';
    }

    /**
     * 期數錄入
     *
     * @param integer $lottery_id 彩種ID
     * @param string $date 日期
     * @return void
     */
    public function writeQishu($lottery_id, $date)
    {
        $nowtime = date('Y-m-d H:i:s');
        $lottery = $this->row($lottery_id);

        Monolog::writeLogs('WriteQishu', 200, "$nowtime - 日期:$date 彩种:$lottery[name] 开始执行!");

        $qishulist = $this->getDayQishu($lottery_id, $date);
        //無資料則跳過
        if ($qishulist == []) {
            Monolog::writeLogs('WriteQishu', 200, "$nowtime - 日期:$date 彩种:$lottery[name] 无资料-略过");
            return;
        }

        //判斷是否已有資料
        $result = [];
        foreach ($qishulist as $qishu => $lottery_time) {
            $result = $this->ettm_lottery_record_db->where([
                't.lottery_id' => $lottery_id,
                't.qishu'      => $qishu
            ])->result();
            break;
        }
        //資料庫有資料則跳過
        if ($result !== []) {
            Monolog::writeLogs('WriteQishu', 200, "$nowtime - 日期:$date 彩种:$lottery[name] 资料库已有资料-略过");
            return;
        }

        $insert = [];
        foreach ($qishulist as $qishu => $lottery_time) {
            $insert[] = [
                'lottery_id'   => $lottery_id,
                'qishu'        => $qishu,
                'numbers'      => '',
                'lottery_time' => $lottery_time,
                'status'       => 0,
                'create_time'  => $nowtime,
                'create_by'    => 'Crontab',
            ];
        }
        Monolog::writeLogs('WriteQishu', 200, $insert);
        $this->trans_start();
        $this->ettm_lottery_record_db->insert_batch($insert);
        $this->trans_complete();
        Monolog::writeLogs('WriteQishu', 200, "$nowtime - 日期:$date 彩种:$lottery[name]---------------------END---------------------");
    }

    /**
     * 取得各彩種期數錄入
     *
     * @param integer $lottery_id 彩種ID
     * @param string $date 日期
     * @return array
     */
    public function getDayQishu($lottery_id, $date = '')
    {
        //六合彩 另外處理 略過
        if (in_array($lottery_id, [22])) {
            return [];
        }

        if (in_array($lottery_id, [10, 14, 33])) {
            //流水號期數
            return $this->daySerialQishu($lottery_id, $date);
        } elseif (in_array($lottery_id, [16, 17])) {
            //低頻彩期數
            return $this->dayLowQishu($lottery_id, $date);
        } elseif ($lottery_id == 7) {
            //重慶時時彩
            return $this->dayCqtatQishu($lottery_id, $date);
        } elseif ($lottery_id == 9) {
            //加拿大PC28
            return $this->dayCndpc28Qishu($lottery_id, $date);
        } else {
            //一般有日期期數
            return $this->dayQishu($lottery_id, $date);
        }
    }

    /**
     * 依日期取得當日所有期數開獎時間(期數有日期)
     *
     * @param integer $lottery_id 彩種ID
     * @param string $date 日期
     * @return array
     */
    public function dayQishu($lottery_id, $date = '')
    {
        $lottery = $this->row($lottery_id);
        $date = $date == '' ? date('Y-m-d') : $date;
        $starttime = strtotime("$date $lottery[open_start]") - strtotime($date);
        $endtime = strtotime("$date $lottery[open_end]") - strtotime($date);
        //結束時間小於開始時間 表示有跨日 結束時間加一天
        if ($endtime < $starttime) {
            $endtime += 86400;
        }

        $period = 1;
        $data = [];
        for ($i = $starttime; $i <= $endtime; $i += $lottery['interval']) {
            $qishu_pre = date('Ymd', strtotime($date));
            $qishu = $qishu_pre . str_pad($period, $lottery['digit'], '0', STR_PAD_LEFT);
            $data[$qishu] = date('Y-m-d H:i:s', strtotime($date) + $i);
            $period++;
        }

        return $data;
    }

    /**
     * 依日期取得當日所有期數開獎時間(流水號)
     *
     * @param integer $lottery_id 彩種ID
     * @param string $date 日期
     * @return array
     */
    public function daySerialQishu($lottery_id, $date = '')
    {
        $lottery = $this->row($lottery_id);
        $date = $date == '' ? date('Y-m-d') : $date;
        $interval = $lottery['interval'];
        $benchmark = $lottery['benchmark']; //基準期數
        $start_day = strtotime($lottery['benchmark_date']); //基準日期

        $starttime = strtotime("$date $lottery[open_start]") - strtotime($date);
        $endtime = strtotime("$date $lottery[open_end]") - strtotime($date);
        //結束時間小於開始時間 表示有跨日 結束時間加一天
        if ($endtime < $starttime) {
            $endtime += 86400;
        }

        //計算時間差-天數
        $day_number = (strtotime($date) - $start_day) / 86400;
        //計算最大期數
        $max_qishu = 0;
        for ($i = $starttime; $i <= $endtime; $i += $interval) {
            $max_qishu++;
        }

        $period = 1;
        $data = [];
        for ($i = $starttime; $i <= $endtime; $i += $interval) {
            $qishu = $benchmark + ($max_qishu * $day_number) + $period;
            $data[$qishu] = date('Y-m-d H:i:s', strtotime($date) + $i);
            $period++;
        }

        return $data;
    }

    /**
     * 依日期取得當日所有期數開獎時間(低頻彩)
     *
     * @param integer $lottery_id 彩種ID
     * @param string $date 日期
     * @return array
     */
    public function dayLowQishu($lottery_id, $date = '')
    {
        $lottery = $this->row($lottery_id);
        $date = $date == '' ? date('Y-m-d') : $date;
        $year = date('Y', strtotime($date));
        $year_start = strtotime($year . '-01-01');
        $period = ((strtotime($date) - $year_start) / 86400) + 1;

        //不開獎日期
        $this->base_model->setTable($this->table_ . 'ettm_lottery_dayoff');
        $result = $this->base_model->where([
            'lottery_id' => $lottery['id'],
            'dayoff >='  => $year . '-01-01',
            'dayoff <='  => $year . '-12-31',
        ])->result();
        $dayoff = array_column($result, 'dayoff');
        //過濾不開獎日期
        foreach ($dayoff as $val) {
            if ($date > $val) {
                $period--;
            }
        }

        $data = [];
        if (!in_array($date, $dayoff)) {
            $qishu = $year . str_pad($period, $lottery['digit'], '0', STR_PAD_LEFT);
            $data[$qishu] = "$date $lottery[open_start]";
        }

        return $data;
    }

    /**
     * 依日期取得當日所有期數開獎時間(重慶時時彩)
     *
     * @param integer $lottery_id 彩種ID
     * @param string $date 日期
     * @return array
     */
    public function dayCqtatQishu($lottery_id, $date = '')
    {
        $lottery = $this->row($lottery_id);
        $date = $date == '' ? date('Y-m-d') : $date;

        $starttime = strtotime("$date $lottery[open_start]") - strtotime($date);
        $endtime = strtotime("$date $lottery[open_end]") - strtotime($date);
        $halftime_start = strtotime("$date $lottery[halftime_start]") - strtotime($date);
        $halftime_end = strtotime("$date $lottery[halftime_end]") - strtotime($date);
        $interval = $lottery['interval'];

        $period = 1;
        $data = [];
        for ($i = $starttime; $i <= $endtime; $i += $interval) {
            //排除中場休息時間
            if ($i > $halftime_start && $i < $halftime_end) {
                continue;
            }

            $qishu_pre = date('Ymd', strtotime($date));
            $qishu = $qishu_pre . str_pad($period, $lottery['digit'], '0', STR_PAD_LEFT);
            $data[$qishu] = date('Y-m-d H:i:s', strtotime($date) + $i);
            $period++;
        }

        return $data;
    }

    /**
     * 依日期取得當日所有期數開獎時間(加拿大PC28)
     *
     * @param integer $lottery_id 彩種ID
     * @param string $date 日期
     * @return array
     */
    public function dayCndpc28Qishu($lottery_id, $date = '')
    {
        $lottery = $this->row($lottery_id);
        $this->base_model->setTable($this->table_ . 'ettm_lottery_record');
        $record = $this->base_model->where(['lottery_id' => $lottery_id])->order(['qishu', 'desc'])->result_one();
        $date = $date == '' ? date('Y-m-d') : $date;
        $benchmark = $lottery['benchmark']; //基準期數
        $start_day = strtotime(date('Y-m-d'));
        $max_qishu = 395;

        //計算時間差-天數
        $day_number = (strtotime($date) - $start_day) / 86400;
        $last_qishu = isset($record['qishu']) ? $record['qishu'] : $benchmark + ($max_qishu * $day_number);

        $data = [];
        for ($i = 1; $i <= $max_qishu; $i++) {
            $qishu = $last_qishu + $i;
            $data[$qishu] = $date;
        }

        return $data;
    }

    /**
     * 設定加拿大PC28開獎時間
     *
     * @return void
     */
    public function setQishuLotteryTime()
    {
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $lottery = $this->row(9);
        $date = date('Y-m-d', time() - 3600 * 18);
        $starttime = strtotime($date . ' ' . $lottery['open_start']);
        $benchmark = $lottery['benchmark']; //基準期數
        $interval = $lottery['interval'];

        $result = $this->ettm_lottery_record_db->where([
            't.lottery_id' => 9,
            't.qishu >='   => $benchmark,
        ])->order(['qishu', 'asc'])->limit([0, 395])->result();
        $record = array_column($result, 'id', 'qishu');
        //最大期數
        $period = 0;
        $update = [];
        while ($period < 395) {
            $lottery_time = $starttime + ($period * $interval);
            $qishu = $benchmark + $period;

            $update[] = [
                'id' => $record[$qishu],
                'lottery_time' => date('Y-m-d H:i:s', $lottery_time),
            ];
            $period++;
        }
        $this->trans_start();
        $this->ettm_lottery_record_db->update_batch($update, 'id');
        $this->trans_complete();
    }

    /**
     * 取得當前期數資訊
     *
     * @param integer $mode 1經典 2官方 3特色
     * @param integer $lottery_id 彩種ID
     * @param integer $time 時間
     * @return array
     */
    public function getQishu($mode, $lottery_id, $time = 0)
    {
        if (in_array($lottery_id, [10, 14, 33])) {
            //流水號期數
            return $this->serialQishu($mode, $lottery_id, $time);
        } elseif (in_array($lottery_id, [16, 17])) {
            //低頻彩期數
            return $this->lowQishu($mode, $lottery_id, $time);
        } elseif ($lottery_id == 7) {
            //重慶時時彩
            return $this->cqtatQishu($mode, $time);
        } elseif ($lottery_id == 9) {
            //加拿大PC28
            return $this->cndpc28Qishu($mode, $time);
        } elseif ($lottery_id == 22) {
            //六合彩
            return $this->hkmk6Qishu($mode, $time);
        } else {
            //一般有日期期數
            return $this->dateQishu($mode, $lottery_id, $time);
        }
    }

    /**
     * 通用期数計算(期數有日期)
     *
     * @param integer $mode 1經典 2官方 3特色
     * @param integer $lottery_id 彩種ID
     * @param integer $time 時間
     * @return array
     */
    public function dateQishu($mode, $lottery_id, $time = 0)
    {
        $lottery = $this->row($lottery_id);
        $interval = $lottery['interval'];
        $strpad = $lottery['digit'];
        $time = $time == 0 ? time() : $time;

        $nowtime = $time - strtotime(date('Y-m-d', $time));
        $starttime = strtotime($lottery['open_start']) - strtotime(date('Y-m-d', time())) - $interval;
        $endtime = strtotime($lottery['open_end']) - strtotime(date('Y-m-d', time()));
        $endtime = $endtime == 0 ? 86400 : $endtime;
        //官方減掉調整時間
        if ($mode == 2) {
            $starttime -= $lottery['adjust'];
            $endtime   -= $lottery['adjust'];
        }
        $day_start_time = $day_close_time = 0;
        if ($endtime > $starttime) {
            $date = date('Y-m-d', $time + 86400 - $endtime);
            //計算從開盤到現在的分鐘數
            $date_sec = $nowtime - $starttime;
            $max_qishu = ($endtime - $starttime) / $interval;
        } else {
            $date = date('Y-m-d', $time - $endtime);
            //計算從開盤到現在的分鐘數
            $date_sec = $nowtime <= $endtime ? 86400 + $nowtime - $starttime : $nowtime - $starttime;
            $max_qishu = (86400 + $endtime - $starttime) / $interval;
        }
        $qishu_pre = date('Ymd', strtotime($date));
        $date_sec = $date_sec < 0 ? 0 : $date_sec;
        //當日期數
        $period = floor($date_sec / $interval) >= $max_qishu ? 0 : floor($date_sec / $interval);
        if ($period == 0) {
            $qishu = date('Ymd', strtotime($date) - 86400) . str_pad($max_qishu, $strpad, '0', STR_PAD_LEFT);
        } else {
            $qishu = $qishu_pre . str_pad($period, $strpad, '0', STR_PAD_LEFT);
        }
        //計算起始時間及結束時間
        $day_start_time = strtotime($date) + $starttime + $interval;
        $day_close_time = strtotime($date) + $endtime;
        if ($endtime <= $starttime) {
            $day_close_time += 86400;
        }

        //經典彩開盤及關盤時間
        if ($mode == 1) {
            $day_start = strtotime($lottery['day_start']) - strtotime(date('Y-m-d', time()));
            $day_end = strtotime($lottery['day_end']) - strtotime(date('Y-m-d', time()));

            $day_start_time = strtotime($date) + $day_start;
            $day_close_time = strtotime($date) + $day_end;
            if ($day_end <= $day_start) {
                $day_close_time += 86400;
            }
        }

        return [
            "qishu"          => $qishu,
            "next_qishu"     => $qishu_pre . str_pad($period + 1, $strpad, '0', STR_PAD_LEFT),
            "day_max_qishu"  => $qishu_pre . str_pad($max_qishu, $strpad, '0', STR_PAD_LEFT),
            "lottery_time"   => strtotime($date) + $starttime + $period * $interval,
            "count_down"     => strtotime($date) + $starttime + ($period + 1) * $interval,
            "interval"       => (int) $interval,
            "adjust"         => (int) $lottery['adjust'],
            "day_start_time" => $day_start_time,
            "day_close_time" => $day_close_time,
        ];
    }

    /**
     * 流水號期数計算
     *
     * @param integer $mode 1經典 2官方 3特色
     * @param integer $lottery_id 彩種ID
     * @param integer $time 時間
     * @return array
     */
    public function serialQishu($mode, $lottery_id, $time = 0)
    {
        $lottery = $this->row($lottery_id);
        $interval = $lottery['interval'];
        $start_qishu = $lottery['benchmark']; //基準期數
        $start_day = strtotime($lottery['benchmark_date']); //基準日期
        $time = $time == 0 ? time() : $time;

        $nowtime = $time - strtotime(date('Y-m-d', $time));
        $starttime = strtotime($lottery['open_start']) - strtotime(date('Y-m-d', time())) - $interval;
        $endtime = strtotime($lottery['open_end']) - strtotime(date('Y-m-d', time()));
        $endtime = $endtime == 0 ? 86400 : $endtime;
        //減掉調整時間
        if ($mode == 2) {
            $starttime -= $lottery['adjust'];
            $endtime -= $lottery['adjust'];
        }

        //計算時間差
        $day_number = floor(($time - $start_day) / 86400);

        if ($endtime > $starttime) {
            $date = date('Y-m-d', $time + 86400 - $endtime);
            //計算從開盤到現在的分鐘數
            $date_sec = $nowtime - $starttime;
            $max_qishu = ($endtime - $starttime) / $interval;
        } else {
            $date = date('Y-m-d', $time - $endtime);
            //計算從開盤到現在的分鐘數
            $date_sec = $nowtime <= $endtime ? 86400 + $nowtime - $starttime : $nowtime - $starttime;
            $max_qishu = (86400 + $endtime - $starttime) / $interval;
        }
        $date_sec = $date_sec < 0 ? 0 : $date_sec;
        //當日期數
        $period = floor($date_sec / $interval);
        $qishu = $start_qishu + ($day_number * $max_qishu) + $period;
        $count_down = $period == $max_qishu ? strtotime($date) + ($starttime + $interval) : strtotime($date) + $starttime + ($period + 1) * $interval;
        $lottery_time = ($nowtime < ($starttime + $interval) || $nowtime >= $endtime) ? strtotime($date) - 86400 + $endtime : strtotime($date) + $starttime + $period * $interval;

        //計算起始時間及結束時間
        $day_start_time = strtotime($date) + $starttime + $interval;
        $day_close_time = strtotime($date) + $endtime;
        if ($endtime < $starttime) {
            $day_close_time += 86400;
        }

        //經典彩開盤及關盤時間
        if ($mode == 1) {
            $day_start = strtotime($lottery['day_start']) - strtotime(date('Y-m-d', time()));
            $day_end = strtotime($lottery['day_end']) - strtotime(date('Y-m-d', time()));

            $day_start_time = strtotime($date) + $day_start;
            $day_close_time = strtotime($date) + $day_end;
            if ($day_end <= $day_start) {
                $day_close_time += 86400;
            }
        }

        return [
            "qishu"          => $qishu,
            "next_qishu"     => $qishu + 1,
            "day_max_qishu"  => $start_qishu + (floor(($time + 86400 - $endtime - $start_day) / 86400) * $max_qishu) + $max_qishu,
            "lottery_time"   => $lottery_time,
            "count_down"     => $count_down,
            "interval"       => (int) $interval,
            "adjust"         => (int) $lottery['adjust'],
            "day_start_time" => $day_start_time,
            "day_close_time" => $day_close_time,
        ];
    }

    /**
     * 低頻彩期数計算
     *
     * @param integer $mode 1經典 2官方 3特色
     * @param integer $lottery_id 彩種ID
     * @param integer $time 時間
     * @return array
     */
    public function lowQishu($mode, $lottery_id, $time = 0)
    {
        $time = $time == 0 ? time() : $time;

        $lottery = $this->row($lottery_id);
        $strpad = $lottery['digit'];
        $starttime = strtotime($lottery['open_start']) - strtotime(date('Y-m-d', time()));
        //減掉調整時間
        if ($mode == 2) {
            $starttime -= $lottery['adjust'];
        }
        $date = strtotime(date('Y-m-d', $time - $starttime));
        $year = date('Y', $time - $starttime);
        $year_start = strtotime($year . '-01-01');
        $period = (($date - $year_start) / 86400) + 1;

        //不開獎日期
        $this->base_model->setTable($this->table_ . 'ettm_lottery_dayoff');
        $result = $this->base_model->where([
            'lottery_id' => $lottery['id'],
            'dayoff >='  => $year . '-01-01',
            'dayoff <='  => $year . '-12-31',
        ])->result();
        $dayoff = array_column($result, 'dayoff');
        //過濾不開獎日期
        foreach ($dayoff as $val) {
            if ($time > $val) {
                $period--;
            }
        }

        //計算lottery_time
        $dayoff_count = 0;
        while (true) {
            if (in_array($date - ($lottery['interval'] * $dayoff_count), $dayoff)) {
                $dayoff_count++;
            } else {
                break;
            }
        }
        $lottery_time = $date - ($lottery['interval'] * $dayoff_count) + $starttime;

        //計算count_down
        $dayoff_count = 1;
        while (true) {
            if (in_array($date + ($lottery['interval'] * $dayoff_count), $dayoff)) {
                $dayoff_count++;
            } else {
                break;
            }
        }
        $count_down = $date + ($lottery['interval'] * $dayoff_count) + $starttime;

        //隔年期數歸0
        if (date('m-d', $date + 86400) == '01-01') {
            $next_qishu = ($year + 1) . str_pad(1, $strpad, '0', STR_PAD_LEFT);
        } else {
            $next_qishu = $year . str_pad($period + 1, $strpad, '0', STR_PAD_LEFT);
        }

        return [
            "qishu"          => $year . str_pad($period, $strpad, '0', STR_PAD_LEFT),
            "next_qishu"     => $next_qishu,
            "day_max_qishu"  => $next_qishu,
            "lottery_time"   => $lottery_time,
            "count_down"     => $count_down,
            "interval"       => (int) $lottery['interval'],
            "adjust"         => (int) $lottery['adjust'],
            "day_start_time" => $date + $starttime,
            "day_close_time" => $date + $starttime + 86400,
        ];
    }

    /**
     * 重庆时时彩期数-特殊算法有中場休息
     *
     * @param integer $mode 1經典 2官方 3特色
     * @param integer $time 時間
     * @return array
     */
    public function cqtatQishu($mode = 1, $time = 0)
    {
        $lottery = $this->row(7);
        $interval = $lottery['interval'];
        $time = $time == 0 ? time() : $time;
        $strpad = 3;

        $nowtime = $time - strtotime(date('Y-m-d', $time));
        $starttime = strtotime($lottery['open_start']) - strtotime(date('Y-m-d', time()));
        $endtime = strtotime($lottery['open_end']) - strtotime(date('Y-m-d', time()));
        $halftime_start = strtotime($lottery['halftime_start']) - strtotime(date('Y-m-d', time()));
        $halftime_end = strtotime($lottery['halftime_end']) - strtotime(date('Y-m-d', time()));
        $halftime = $nowtime >= $halftime_start ? $halftime_end - $halftime_start - $interval : 0;
        //減掉調整時間
        if ($mode == 2) {
            $starttime      -= $lottery['adjust'];
            $endtime        -= $lottery['adjust'];
            $halftime_start -= $lottery['adjust'];
            $halftime_end   -= $lottery['adjust'];
        }
        $date = date('Y-m-d', $time + 86400 - $endtime);
        $qishu_pre = date('Ymd', $time + 86400 - $endtime);
        $period = $max_qishu = 0;
        for ($i = $starttime; $i <= $endtime; $i += $interval) {
            //排除中場休息時間
            if ($i > $halftime_start && $i < $halftime_end) {
                continue;
            }

            if ($nowtime >= $i) {
                $period++;
            }
            $max_qishu++;
        }

        if ($period == $max_qishu) {
            $period = 0;
            $halftime = 0;
        }

        //當日期數
        if ($period == 0) {
            $qishu = date('Ymd', strtotime($date) - 86400) . str_pad($max_qishu, $strpad, '0', STR_PAD_LEFT);
            $lottery_time = strtotime($date) - 86400 + $endtime;
        } else {
            $qishu = $qishu_pre . str_pad($period, $strpad, '0', STR_PAD_LEFT);
            $lottery_time = strtotime($date) + $starttime + $halftime + (($period - 1) * $interval);
            if ($halftime_start <= $nowtime && $nowtime < $halftime_end) {
                $lottery_time = $lottery_time - $halftime;
            }
        }

        //計算起始時間及結束時間
        $day_start_time = strtotime($date) + $starttime;
        $day_close_time = strtotime($date) + $endtime;
        if ($endtime < $starttime) {
            $day_close_time += 86400;
        }

        //經典彩開盤及關盤時間
        if ($mode == 1) {
            $day_start = strtotime($lottery['day_start']) - strtotime(date('Y-m-d', time()));
            $day_end = strtotime($lottery['day_end']) - strtotime(date('Y-m-d', time()));

            $date_tmp = date('Y-m-d', $time - $day_end);
            $day_start_time = strtotime($date_tmp) + $day_start;
            $day_close_time = strtotime($date_tmp) + $day_end;
            if ($day_end < $day_start) {
                $day_close_time += 86400;
            }
        }

        return [
            "qishu"          => $qishu,
            "next_qishu"     => $qishu_pre . str_pad($period + 1, $strpad, '0', STR_PAD_LEFT),
            "day_max_qishu"  => $qishu_pre . str_pad($max_qishu, $strpad, '0', STR_PAD_LEFT),
            "lottery_time"   => $lottery_time,
            "count_down"     => strtotime($date) + $starttime + $halftime + ($period * $interval),
            "interval"       => (int) $interval,
            "adjust"         => (int) $lottery['adjust'],
            "day_start_time" => $day_start_time,
            "day_close_time" => $day_close_time,
        ];
    }


    /**
     * 加拿大PC28期数
     *
     * @param integer $mode 1經典 2官方 3特色
     * @param integer $time 時間
     * @return array
     */
    public function cndpc28Qishu($mode = 1, $time = 0)
    {
        $lottery = $this->row(9);
        $interval = $lottery['interval'];
        $time = $time == 0 ? time() : $time;

        $start_qishu = $lottery['benchmark']; //資料庫是昨天最後一期
        $nowtime = $time - strtotime(date('Y-m-d', $time));
        $starttime = strtotime($lottery['open_start']) - strtotime(date('Y-m-d', time()));
        $endtime = strtotime($lottery['open_end']) - strtotime(date('Y-m-d', time()));
        //減掉調整時間
        if ($mode == 2) {
            $starttime -= $lottery['adjust'];
            $endtime   -= $lottery['adjust'];
        }

        $date = $nowtime < $starttime - 3600 ? strtotime(date("Y-m-d", $time)) - 86400 : strtotime(date("Y-m-d", $time));
        $start_datetime = $date + $starttime;

        $period = floor(($time - $start_datetime) / $interval);
        $period = $period < 0 ? -1 : $period;

        //計算起始時間及結束時間
        $day_start_time = $date + $starttime;
        $day_close_time = $date + $endtime;
        if ($endtime < $starttime) {
            $day_close_time += 86400;
        }

        //經典彩開盤及關盤時間
        if ($mode == 1) {
            $day_start = strtotime($lottery['day_start']) - strtotime(date('Y-m-d', time()));
            $day_end = strtotime($lottery['day_end']) - strtotime(date('Y-m-d', time()));

            $day_start_time = $date + $day_start;
            $day_close_time = $date + $day_end;
            if ($day_end < $day_start) {
                $day_close_time += 86400;
            }
        }

        return [
            "qishu"          => $start_qishu + $period,
            "next_qishu"     => $start_qishu + $period + 1,
            "day_max_qishu"  => $start_qishu + 395,
            "lottery_time"   => $start_datetime + ($period * $interval),
            "count_down"     => $start_datetime + (($period + 1) * $interval),
            "interval"       => (int) $interval,
            "adjust"         => (int) $lottery['adjust'],
            "day_start_time" => $day_start_time,
            "day_close_time" => $day_close_time,
        ];
    }

    /**
     * 六合彩期数
     *
     * @param integer $mode 1經典 2官方 3特色
     * @param integer $time 時間
     * @return array
     */
    public function hkmk6Qishu($mode = 1, $time = 0)
    {
        $time = $time == 0 ? time() : $time;

        $lottery = $this->row(22);
        $starttime = strtotime($lottery['open_start']) - strtotime(date('Y-m-d', time()));
        //減掉調整時間
        if ($mode == 2) {
            $starttime -= $lottery['adjust'];
        }

        $lottery_time = date('Y-m-d H:i:s', $time - $starttime);
        $this->base_model->setTable($this->table_ . "ettm_lottery_record");
        //下一期
        $row = $this->base_model->where([
            't.lottery_id'     => 22,
            't.lottery_time >' => $lottery_time,
            't.status'         => 0,
        ])->order(['lottery_time', 'asc'])->result_one();
        $next_qishu = isset($row['qishu']) ? $row['qishu'] : 0;
        //當前期數
        $row_now = $this->base_model->where([
            't.lottery_id' => 22,
            't.qishu <'    => $next_qishu,
        ])->order(['lottery_time', 'desc'])->result_one();

        return [
            'qishu'          => $row_now['qishu'],
            'next_qishu'     => $next_qishu,
            'day_max_qishu'  => $next_qishu,
            'lottery_time'   => strtotime($row_now['lottery_time']),
            'count_down'     => strtotime($row['lottery_time']),
            "interval"       => strtotime($row['lottery_time']) - strtotime($row_now['lottery_time']),
            "adjust"         => (int) $lottery['adjust'],
            "day_start_time" => strtotime(date('Y-m-d', $time)) + $starttime,
            "day_close_time" => strtotime(date('Y-m-d', $time)) + $starttime + 86400,
        ];
    }
}
