<?php defined('BASEPATH') || exit('No direct script access allowed');

class Home extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model', 'user_db');  //用戶註冊
        $this->load->model('user_login_log_model', 'user_login_log_db');  //用戶登錄
        $this->load->model('recharge_order_model', 'recharge_order_db');  //線上、線下充值
        $this->load->model('user_withdraw_model', 'user_withdraw_db');  //出款
        $this->load->model('user_money_log_model', 'user_money_log_db');  //帳變名細
        $this->load->model('daily_user_report_model', 'daily_user_report_db');  //下注報表10分鐘更新
        $this->load->model('operator_model', 'operator_db');  //運營商資料
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');  //經典彩投注
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');  //官方彩投注
    }

    public function index()
    {
        $this->load->library('pagination');

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['id', 'asc']);
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        if ($this->input->is_ajax_request()) {
            if ($where['action'] == 1) {
                $data['topResult'] = $this->_topDashboard();
                $data['downResult'] = $this->_downDashboard("$where[date_start] 00:00:00", "$where[date_end] 23:59:59");  //儀表板歷史資料
            } elseif ($where['action'] == 2) {
                $data['lineChart'] = $this->_highchartsLine('income', "$where[date_start] 00:00:00", "$where[date_end] 23:59:59");  //盈虧趨勢折線圖
                $operatorResult = $this->_highchartsPie("$where[date_start] 00:00:00", "$where[date_end] 23:59:59");  //盈虧趨勢圓餅圖
                $data['pieChart'] = $operatorResult['pieData'];
                $data['tableList'] = $this->tableList($operatorResult['operatorData']);  //盈虧趨勢表格
            } elseif ($where['action'] == 3) {
                $data['registerLine'] = $this->_highchartsLine('register_login', "$where[date_start] 00:00:00", "$where[date_end] 23:59:59", $where['source']);  //會員註冊登入趨勢折線圖
                $data['rechargeLine'] = $this->_highchartsLine('recharge_withdraw', "$where[date_start] 00:00:00", "$where[date_end] 23:59:59", $where['source']);  //充值提現趨勢折線圖
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($data));
            return;
        }

        $this->layout->view('index', [
            'where'      => $where,
            'params_uri' => $params_uri,
        ]);
    }

    /**
     * 儀表板上半部即時資訊
     *
     * @return array
     */
    private function _topDashboard()
    {
        $data = [];
        $start_time = date('Y-m-d') . ' 00:00:00';
        $end_time = date('Y-m-d') . ' 23:59:59';

        //今日注冊人數
        $count = $this->user_db->escape(false)->where([
            't.status'         => 0,
            't.type'           => 0,
            't.create_time >=' => $start_time,
            't.create_time <=' => $end_time
        ])->count();
        $data[] = [
            'title' => '今日注册人数：',
            'value' => $count
        ];

        //今日注冊存款
        $select = 'IFNULL(SUM(t.money), 0) money,IFNULL(COUNT(DISTINCT t.uid), 0) count';
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $row = $this->recharge_order_db->select($select)->where([
            't.status'          => 1,
            't1.status'         => 0,
            't1.type'           => 0,
            't1.create_time >=' => $start_time,
            't1.create_time <=' => $end_time
        ])->join($join)->result_one();
        $data[] = [
            'title' => '今日注册充值：',
            'value' => $row['money']
        ];
        $data[] = [
            'title' => '今日注册充值人数：',
            'value' => $row['count']
        ];

        //今日首存人數
        $where = [
            't.status'         => 1,
            't1.status'        => 0,
            't1.type'          => 0,
            't.create_time >=' => $start_time,
            't.create_time <=' => $end_time
        ];
        $count = $this->recharge_order_db->select('t.uid')->where($where)
            ->join($join)->group('t.uid')->count();
        $data[] = [
            'title' => '今日首存人数：',
            'value' => $count
        ];
        //今日二充人數
        $result = $this->recharge_order_db->select('count(*) count')->where($where)
            ->join($join)->group('t.uid')->having('count >= 2')->result();
        $data[] = [
            'title' => '今日二充人数：',
            'value' => count($result)
        ];

        //今日有效會員
        $where = [
            't1.status '             => 0,
            't1.type'                => 0,
            'date(t.create_time) >=' => $start_time,
            'date(t.create_time) <=' => $end_time
        ];

        //經典彩下注統計
        unset($join);
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $classic_sql = $this->ettm_classic_bet_record_db->select('t.uid')->join($join)->escape(false)->where($where)->get_compiled_select();
        //官方彩下注統計
        $official_sql = $this->ettm_official_bet_record_db->select('t.uid')->join($join)->escape(false)->where($where)->get_compiled_select();

        $count = count($this->base_model->query("select * from ( $classic_sql UNION ALL $official_sql ) v1 group by uid")->result());
        $data[] = [
            'title' => '今日有效会员：',
            'value' => $count,
            'link'  => site_url('report/user_effect')
        ];

        //會員總數
        $count = $this->user_db->where([
            't.status' => 0,
            't.type'   => 0
        ])->count();
        $data[] = [
            'title' => '会员总数：',
            'value' => $count
        ];

        //今日有效下注額
        unset($join);
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $row = $this->daily_user_report_db->select('IFNULL(SUM(t.bet_eff), 0) bet_eff')->where([
            't1.status'     => 0,
            't1.type'       => 0,
            't.day_time >=' => $start_time,
            't.day_time <=' => $end_time
        ])->join($join)->result_one();
        $data[] = [
            'title' => '有效下注额：',
            'value' => $row['bet_eff'],
        ];
        return $data;
    }

    /**
     * 儀表板下半部歷史資訊
     *
     * @param [string] $start_time 資料查詢開始時間
     * @param [string] $end_time 資料查詢結束時間
     * @return array
     */
    private function _downDashboard($start_time, $end_time)
    {
        $data = [];
        //線上充值
        $result = $this->recharge_order_db->getRechargeUser($start_time, $end_time, 1);
        $online = $result === [] ? 0 : array_sum($result);
        $arr[] = [
            'title' => '线上充值',
            'data'  => $online,
        ];
        //線下充值
        $result = $this->recharge_order_db->getRechargeUser($start_time, $end_time, 2);
        $offline = $result === [] ? 0 : array_sum($result);
        $arr[] = [
            'title' => '线下充值',
            'data'  => $offline,
        ];

        //人工存款
        $result = $this->user_money_log_db->getMoneyLogUser($start_time, $end_time, 2, 0);
        $manaual = $result === [] ? 0 : array_sum($result);
        $arr[] = [
            'title' => '人工存款',
            'data'  => $manaual,
        ];
        //總入款
        $income = $online + $offline + $manaual;
        $arr[] = [
            'title' => '总入款',
            'data'  => $income,
        ];
        $data[] = [
            'title' => '入款状况',
            'value' => $arr,
        ];
        //-------------------------------------------------------
        $arr = [];
        //會員出款
        $result = $this->user_withdraw_db->getWithdrawUser($start_time, $end_time);
        $withdraw = array_sum($result);
        $arr[] = [
            'title' => '会员出款',
            'data'  => $withdraw,
        ];

        //出入款盈虧
        $arr[] = [
            'title' => '出入款盈亏(总入款-会员出款)',
            'data'  => $income - $withdraw,
            'color' => 1,
        ];

        //有效下注額
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $row = $this->daily_user_report_db->select('IFNULL(SUM(t.bet_eff), 0) money')->where([
            't1.status'     => 0,
            't1.type'       => 0,
            't.day_time >=' => $start_time,
            't.day_time <=' => $end_time
        ])->join($join)->result_one();
        $arr[] = [
            'title' => '有效下注额',
            'data'  => $row['money'],
        ];
        $data[] = [
            'title' => '出款状况',
            'value' => $arr,
        ];
        //-----------------------------------------------------------------------------------
        $arr = [];
        //反水
        $result = $this->user_money_log_db->getMoneyLogUser($start_time, $end_time, 4, 0);
        $arr[] = [
            'title' => '反水',
            'data'  => array_sum($result),
        ];

        //反點
        $result = $this->user_money_log_db->getMoneyLogUser($start_time, $end_time, 19, 0);
        $arr[] = [
            'title' => '反点',
            'data'  => array_sum($result),
        ];

        //人工彩金
        $result = $this->user_money_log_db->getMoneyLogUser($start_time, $end_time, 8, 0);
        $arr[] = [
            'title' => '人工彩金',
            'data'  => array_sum($result),
        ];

        //充值彩金
        $result = $this->user_money_log_db->getMoneyLogUser($start_time, $end_time, 7, 0);
        $arr[] = [
            'title' => '充值彩金',
            'data'  => array_sum($result),
        ];
        $data[] = [
            'title' => '优惠状况',
            'value' => $arr,
        ];
        return $data;
    }

    private function tableList($operatorData)
    {
        $result = $operatorData;

        foreach ($operatorData as $key => $value) {
            if ($value['profit'] < 0) { // 負值不列入百分比計算
                unset($operatorData[$key]);
            }
        }
        $total = array_sum(array_column($result, 'profit'));
        $total_positive = array_sum(array_column($operatorData, 'profit'));
        $data = [];
        foreach ($result as $value) {
            if ($value['profit'] > 0) {
                $percentage = bcmul(bcdiv($value['profit'], $total_positive, 3), 100, 1);
            } elseif ($value['profit'] == 0) {
                $percentage = '0.0';
            } else {
                $percentage = '--';
            }
            $data[] = [
                'operator_name' => $value['operatorName'],
                'profit' => $value['profit'],
                'percentage' => $percentage,
                'color'  => 'green'
            ];
        }

        // 比例加總必須為100%，將多餘的加回去
        $sum = 0;
        foreach ($data as $key => $value) {
            if ($value['profit'] > 0) {
                $sum = bcadd($sum, $data[$key]['percentage'], 1);
                $data[$key]['color'] = 'red';
            }
        }
        $difference = bcsub(100, $sum, 1);
        if ($difference != 0) {
            foreach ($data as $key => $value) {
                if ($value['profit'] > 0) {
                    $data[$key]['percentage'] = bcadd($data[$key]['percentage'], $difference, 1);
                    break;
                }
            }
        }
        //加入總和資料
        $data[] = [
            'operator_name' => '总计：',
            'profit'        => bcdiv($total, 1, 2),
            'percentage'    => ($difference == 100) ? bcdiv($sum, 1, 1) : bcadd($sum, $difference, 1),
            'color'         => (bcdiv($total, 1, 2) > 0) ? 'red' : 'green'
        ];

        return $data;
    }

    private function _highchartsPie($start_time, $end_time)
    {
        $result = $operatorResult = $this->_highchartsPieData($start_time, $end_time);

        foreach ($result as $key => $value) {
            if ($value['profit'] < 0) { // 負值不列入百分比計算
                unset($result[$key]);
            }
        }
        $total = array_sum(array_column($result, 'profit')); // 計算總和
        $pie = [];
        if ($total > 0) {
            foreach ($result as $value) {
                $pie[] = [
                    $value['operatorName'],
                    ($value['profit'] > 0) ? (float) (bcmul(bcdiv($value['profit'], $total, 3), 100, 1)) : 0
                ];
            }

            // 比例加總必須為100%，將多餘的加回去
            $sum = array_sum(array_column($pie, 1));
            $difference = bcsub(100, $sum, 1);
            if ($difference != 0) {
                foreach ($pie as $key => $value) {
                    if ($value[1] > 0) {
                        $pie[$key][1] = (float) (bcadd($pie[$key][1], $difference, 1));
                        break;
                    }
                }
            }
        } else {
            $pie[] = ['无资料', 0];
        }

        // 圖表格式
        $chart = [
            'plotBorderColor' => "#000000",
            'plotBorderWidth' => 1
        ];
        $tooltip = ['pointFormat' => '{series.name}: <b>{point.percentage:.1f}%</b>'];
        $plotOptions = [
            'pie' => [
                'allowPointSelect' => true,
                'cursor'           => 'pointer',
                'dataLabels'       => [
                    'enabled' => true,
                    'format'  => '<b>{point.name}</b>: {point.percentage:.1f} %',
                    'style'   => [
                        'color' => "(Highcharts . theme && Highcharts . theme . contrastTextColor) || 'black'"
                    ]
                ]
            ]
        ];
        $series = [
            [
                'type'      => 'pie',
                'name'      => '比例',
                'innerSize' => '50%',
                'data'      => $pie
            ]
        ];
        $credits = ['enabled' => false];

        // 組合圖表
        $data['title']       = '';
        $data['chart']       = $chart;
        $data['tooltip']     = $tooltip;
        $data['plotOptions'] = $plotOptions;
        $data['series']      = $series;
        $data['credits']     = $credits;

        $result = [
            'operatorData' => $operatorResult,
            'pieData'      => $data
        ];

        return $result;
    }

    private function _highchartsPieData($start_time, $end_time)
    {
        //充值
        $recharge = $this->recharge_order_db->getOperatorRecharge($start_time, $end_time);
        //人工存款
        $manaual = $this->user_money_log_db->getOperatorMoneyLog($start_time, $end_time, [2]);
        //提現
        $withdraw = $this->user_withdraw_db->getOperatorWithdraw($start_time, $end_time);

        $operators = $this->operator_db->getList(0);
        $data = [];
        foreach ($operators as $id => $name) {
            $profit = 0;
            if (isset($recharge[$id])) {
                $profit = bcadd($profit, $recharge[$id], 2);
            }
            if (isset($manaual[$id])) {
                $profit = bcadd($profit, $manaual[$id], 2);
            }
            if (isset($withdraw[$id])) {
                $profit = bcsub($profit, $withdraw[$id], 2);
            }
            $data[] = [
                'operatorId'   => $id,
                'operatorName' => $name,
                'profit'       => $profit,
            ];
        }
        return $data;
    }

    /**
     * 顯示趨勢圖資訊
     *
     * @param [string] $type 趨勢圖類型(營虧、會員、登錄)
     * @param [string] $start_time 資料開始時間
     * @param [string] $end_time 資料結束時間
     * @param [string] $source 會員登錄裝置來源
     * @return array
     */
    private function _highchartsLine($type, $start_time, $end_time, $source = '')
    {
        $result = $this->_highchartsLineData($type, $start_time, $end_time, $source);

        //圖表框線
        $chart = [
            'plotBorderColor' => "#000000",
            'plotBorderWidth' => 1
        ];
        //X軸
        $xAxis = ['categories' => $result['categories']];
        //Y軸
        $yAxis = [
            'title' => ['text' => ''],
            'plotLines' => [
                [
                    'value' => 0,
                    'width' => 3,
                ]
            ]
        ];
        //折線圖之間的聯動
        $tooltip = [
            'crosshairs' => true,
            'shared' => true,
        ];
        //折線圖資料
        $series = $result['series'];
        //取消highcharts標籤
        $credits = ['enabled' => false];

        // 組合圖表
        $line['title'] = '';
        $line['chart'] = $chart;
        $line['xAxis'] = $xAxis;
        $line['yAxis'] = $yAxis;
        $line['tooltip'] = $tooltip;
        $line['legend'] = '';
        $line['series'] = $series;
        $line['credits'] = $credits;
        return $line;
    }

    /**
     * 取得趨勢圖資料
     *
     * @param [string] $type 趨勢圖類型(營虧、會員、登錄)
     * @param [string] $start_time 資料開始時間
     * @param [string] $end_time 資料結束時間
     * @param [string] $source 會員登錄裝置來源
     * @return array
     */
    private function _highchartsLineData($type, $start_time, $end_time, $source)
    {
        //相差天數
        $difference_day = bcdiv(bcsub(strtotime($end_time), strtotime($start_time)), 86400);
        //相差月數
        $tmp_end = explode('-', $end_time);
        $tmp_end = bcadd(bcmul($tmp_end[0], 12), $tmp_end[1]);
        $tmp_start = explode('-', $start_time);
        $tmp_start = bcadd(bcmul($tmp_start[0], 12), $tmp_start[1]);
        $difference_month = bcsub($tmp_end, $tmp_start);

        //預設參數
        if ($difference_day < 0) {
            return [
                'categories' => ['无资料'],
                'series' => [
                    [
                        'name' => '',
                        'data' => [0]
                    ]
                ]
            ];
        } elseif ($difference_month < 5) {
            $format_php = 'Y-m-d';
            $format_sql = '%Y-%m-%d';
            $interval = '+1 day';
            $difference = $difference_day;
        } else {
            $format_php = 'Y-m';
            $format_sql = '%Y-%m';
            $interval = '+1 month';
            $tmp_end = explode('-', $end_time);
            $tmp_end = bcadd(bcmul($tmp_end[0], 12), $tmp_end[1]);
            $tmp_start = explode('-', $start_time);
            $tmp_start = bcadd(bcmul($tmp_start[0], 12), $tmp_start[1]);
            $difference = $difference_month;
        }

        $series = [];
        $date[] = date($format_php, strtotime($start_time));
        //創建日期陣列
        for ($i = 0; $i < $difference; $i++) {
            $date[] = date($format_php, strtotime($interval, strtotime($date[$i])));
        }

        if (in_array($type, ['income', 'recharge_withdraw'])) {
            //充值
            $select = "DATE_FORMAT(t.check_time,'" . $format_sql . "') as date,SUM(t.money) as money ";
            $group = "DATE_FORMAT(t.check_time,'" . $format_sql . "')";
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $rechargeSum = $this->recharge_order_db->select($select)->where([
                't1.type'     => 0,
                't1.status'   => 0,
                'check_time1' => $start_time,
                'check_time2' => $end_time,
                't.status'    => 1,
            ])->join($join)->group($group)->result();
            $rechargeSum = array_column($rechargeSum, 'money', 'date');
            //提現
            $withdrawSum = $this->user_withdraw_db->select($select)->where([
                't1.type'     => 0,
                't1.status'   => 0,
                'check_time1' => $start_time,
                'check_time2' => $end_time,
                't.status'    => 1,
            ])->join($join)->group($group)->result();
            $withdrawSum = array_column($withdrawSum, 'money', 'date');
            //人工存款
            $select = "DATE_FORMAT(t.create_time,'" . $format_sql . "') date,SUM(t.money_add) money ";
            $group = "DATE_FORMAT(t.create_time,'" . $format_sql . "')";
            $manaual = $this->user_money_log_db->select($select)->where([
                't1.type'      => 0,
                't1.status'    => 0,
                'money_type'   => 0,
                'create_time1' => $start_time,
                'create_time2' => $end_time,
                't.type'       => 2,
            ])->join($join)->group($group)->result();
            $manaual = array_column($manaual, 'money', 'date');

            if ($type == 'income') {
                $income = [];
                foreach ($date as $value) {
                    $income_temp = 0;
                    if (isset($rechargeSum[$value])) {
                        $income_temp = bcadd($income_temp, $rechargeSum[$value], 2);
                    }
                    if (isset($manaual[$value])) {
                        $income_temp = bcadd($income_temp, $manaual[$value], 2);
                    }
                    if (isset($withdrawSum[$value])) {
                        $income_temp = bcsub($income_temp, $withdrawSum[$value], 2);
                    }
                    $income[] = (float) $income_temp;
                }
                $series[] = [
                    'name' => '',
                    'data' => $income
                ];
            } elseif (in_array($type, ['recharge_withdraw'])) {
                $recharge = [];
                $withdraw = [];
                foreach ($date as $value) {
                    $recharge_temp = 0;
                    if (isset($rechargeSum[$value])) {
                        $recharge_temp = bcadd($recharge_temp, $rechargeSum[$value], 2);
                    }
                    if (isset($manaual[$value])) {
                        $recharge_temp = bcadd($recharge_temp, $manaual[$value], 2);
                    }
                    $recharge[] = (float) $recharge_temp;

                    $withdraw_temp = 0;
                    if (isset($withdrawSum[$value])) {
                        $withdraw_temp = bcadd($withdraw_temp, $withdrawSum[$value], 2);
                    }
                    $withdraw[] = (float) $withdraw_temp;
                }
                $series[] = [
                    'name' => '充值',
                    'data' => $recharge
                ];
                $series[] = [
                    'name' => '提现',
                    'data' => $withdraw
                ];
            }
        } elseif (in_array($type, ['register_login'])) {
            //source：all,ios,android,wap,pc
            if (isset($source) && !empty($source)) {
                $source = explode(',', $source);
            }
            if (empty($source)) {
                return [
                    'categories' => ['无资料'],
                    'series' => [
                        [
                            'name' => '',
                            'data' => [0]
                        ]
                    ]
                ];
            }

            //註冊
            $select = "date_format(t.create_time,'" . $format_sql . "') as register_time,t.source,count(t.id) as count ";
            $where = [
                't.status'         => 0,
                't.type'           => 0,
                't.create_time >=' => $start_time,
                't.create_time <=' => $end_time,
            ];
            $group = "date(t.create_time),t.source";
            $registerSum = $this->user_db->select($select)->where($where)->group($group)->result();

            //登錄
            $select = " date_format(t1.create_time,'" . $format_sql . "') as login_time ,t.source ,count(t.id) as count ";
            $join[] = [$this->table_ . "user t1", "on t.uid = t1.id", "left"];
            $where = [
                't1.status'         => 0,
                't1.type'           => 0,
                't1.create_time >=' => $start_time,
                't1.create_time <=' => $end_time,
            ];
            $group = "date(t1.create_time),t.source";
            $loginSum = $this->user_login_log_db->select($select)->where($where)->join($join)->group($group)->result();

            //預設變數
            $series_data = [];
            foreach ($source as $v_source) {
                $series_data['register'][$v_source] = [];
                $series_data['login'][$v_source] = [];
            }

            //依日期先預設為0
            foreach ($date as $v_date) {
                foreach ($source as $v_source) {
                    $series_data['register'][$v_source][$v_date] = 0;
                    $series_data['login'][$v_source][$v_date] = 0;
                }
            }

            //比對日期寫入資料
            foreach ($registerSum as $v) {
                if (in_array('all', $source)) {
                    $series_data['register']['all'][$v['register_time']] += $v['count'];
                }
                if (in_array($v['source'], $source)) {
                    $series_data['register'][$v['source']][$v['register_time']] += $v['count'];
                }
            }
            foreach ($loginSum as $v) {
                if (in_array('all', $source)) {
                    $series_data['login']['all'][$v['login_time']] += $v['count'];
                }
                if (in_array($v['source'], $source)) {
                    $series_data['login'][$v['source']][$v['login_time']] += $v['count'];
                }
            }

            foreach ($source as $v_source) {
                $source_str = $v_source == 'all' ? '全部' : base_model::$sourceList[$v_source];
                $series[] = [
                    'name' => "{$source_str}注册",
                    'data' => array_values($series_data['register'][$v_source])
                ];
                $series[] = [
                    'name' => "{$source_str}登入",
                    'data' => array_values($series_data['login'][$v_source])
                ];
            }
        }
        return [
            'categories' => $date,
            'series'     => $series
        ];
    }
}
