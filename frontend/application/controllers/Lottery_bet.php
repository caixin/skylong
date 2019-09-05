<?php defined('BASEPATH') || exit('No direct script access allowed');

class Lottery_bet extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_sort_model', 'ettm_lottery_sort_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_official_bet_record_model', 'ettm_official_bet_record_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');
        $this->load->model('ettm_classic_wanfa_model', 'ettm_classic_wanfa_db');
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
        $this->load->model('ettm_official_wanfa_model', 'ettm_official_wanfa_db');
        $this->load->model('qishu_model');
        $this->load->library('Lottery_Permutation_combination/Common_combination');
        bcscale(3);
    }

    /**
     * @OA\Post(
     *   path="/lottery_bet/getWanfaList",
     *   summary="彩種玩法",
     *   tags={"LotteryBet"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="category",
     *                   description="玩法類別 1:經典 2:官方",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="string",
     *                   example="1",
     *               ),
     *               required={"category","lottery_id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getWanfaList()
    {
        try {
            $category = $this->input->post("category");
            if ($category === null) {
                throw new Exception("缺少必要参数", 300);
            }
            switch ($category) {
                case 1:
                    $this->_classicWanfaList();
                    break;
                case 2:
                    $this->_officialWanfaList();
                    break;
            }
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 經典玩法
     */
    private function _classicWanfaList()
    {
        try {
            $lottery_id = $this->input->post("lottery_id");
            if ($lottery_id === null) {
                throw new Exception("缺少必要参数", 300);
            }

            $lottery = $this->ettm_lottery_db->row($lottery_id);
            if ($lottery === null) {
                throw new Exception("请选择正确的彩种", 300);
            }
            if ($lottery['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }
            $lottery_sort = $this->ettm_lottery_sort_db->where([
                't.operator_id' => $this->operator_id,
                't.lottery_id'  => $lottery_id,
            ])->result_one();
            if ($lottery_sort !== null && $lottery_sort['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }

            $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);

            // 玩法列表
            $wanfa = $this->ettm_classic_wanfa_db->where([
                'lottery_type_id' => $lottery['lottery_type_id']
            ])->result();
            //玩法详细列表
            $wanfa_detail = $this->ettm_classic_wanfa_detail_db->oddsCalculation($lottery_id, $qishu_arr['next_qishu'], $this->uid);
            //六合彩-官方玩法需要
            if ($lottery['lottery_type_id'] == 8) {
                $zodiac = $this->ettm_classic_wanfa_detail_db->getZodiacNumber($qishu_arr['count_down'], 48); //扣除49號
                $zodiac2 = $this->ettm_classic_wanfa_detail_db->getZodiacNumber($qishu_arr['count_down']); //包含49號
                $mantissa = $this->ettm_classic_wanfa_detail_db->getMantissaNumber();
                $year_zodiac = $zodiac[-1];
                unset($zodiac[-1], $zodiac2[-1]);
                //將數值轉成兩位數
                foreach ($zodiac as $key => $numbers) {
                    $zodiac[$key] = array_map(function ($row) {
                        return str_pad($row, 2, '0', STR_PAD_LEFT);
                    }, $numbers);
                }
                foreach ($zodiac2 as $key => $numbers) {
                    $zodiac2[$key] = array_map(function ($row) {
                        return str_pad($row, 2, '0', STR_PAD_LEFT);
                    }, $numbers);
                }
                foreach ($mantissa as $key => $numbers) {
                    $mantissa[$key] = array_map(function ($row) {
                        return str_pad($row, 2, '0', STR_PAD_LEFT);
                    }, $numbers);
                }
            }

            $wanfa_arr = [];
            foreach ($wanfa as $row) {
                if ($row['pid'] == 0) {
                    $wanfa_arr[$row['id']]['lottery_type_id'] = (int) $row['lottery_type_id'];
                    $wanfa_arr[$row['id']]['wanfa_pid']       = (int) $row['id'];
                    $wanfa_arr[$row['id']]['name']            = $row['name'];
                    continue;
                }

                $row["wanfa_dealil_list"] = [];
                foreach ($wanfa_detail as $val) {
                    if ($val['wanfa_id'] == $row['id']) {
                        $arr = [
                            'id'            => (int) $val['id'],
                            'values'        => $val['values'],
                            'values_sup'    => explode(',', $val['values_sup']),
                            'odds'          => (float) $val['odds'],
                            'odds_special'  => (float) $val['odds_special'],
                            'bet_min_money' => (int) $val['bet_min_money'],
                            'bet_max_money' => (int) $val['bet_max_money'],
                            'max_number'    => (int) $val['max_number'],
                        ];
                        //六合彩系列特殊處理
                        if ($lottery['lottery_type_id'] == 8) {
                            $formula = json_decode($val['formula'], true);
                            //下注限制
                            if (isset($formula['bet_min'])) {
                                $arr['bet_min'] = $formula['bet_min'];
                            }
                            if (isset($formula['bet_max'])) {
                                $arr['bet_max'] = $formula['bet_max'];
                            }
                            //顏色 - 排除正特尾的數字都要加顏色
                            $arr['bo_color'] = '';
                            if (is_numeric($val['values']) && $val['wanfa_id'] != 196) {
                                foreach (ettm_classic_wanfa_detail_model::$colorBall as $color => $balls) {
                                    if (in_array((int) $arr['values'], $balls)) {
                                        $arr['bo_color'] = $color;
                                    }
                                }
                            }
                            if (strpos($val['values'], '红') !== false) {
                                $arr['bo_color'] = 'r';
                            } elseif (strpos($val['values'], '蓝') !== false) {
                                $arr['bo_color'] = 'b';
                            } elseif (strpos($val['values'], '绿') !== false) {
                                $arr['bo_color'] = 'g';
                            }

                            $arr['data'] = [];
                            //一肖特肖
                            if ($formula['type'] == 'zodiac') {
                                $arr['values_sup'] = $zodiac2[$formula['value']];
                                $arr['show_special'] = $year_zodiac == $formula['value'] ? true : false;
                            }
                            //合肖
                            if ($formula['type'] == 'HeXiao') {
                                $arr['values_sup'] = implode(',', $arr['values_sup']);
                                foreach ($zodiac as $k => $numbers) {
                                    $arr['data'][] = [
                                        'key'     => $k,
                                        'name'    => ettm_classic_wanfa_detail_model::$zodiacType[$k],
                                        'numbers' => $numbers,
                                    ];
                                }
                            }
                            //生肖連
                            if ($formula['type'] == 'ShengXiaoLian') {
                                foreach ($zodiac2 as $k => $numbers) {
                                    $arr['data'][] = [
                                        'key'     => $k,
                                        'name'    => ettm_classic_wanfa_detail_model::$zodiacType[$k],
                                        'numbers' => $numbers,
                                        'odds'    => $year_zodiac == $k ? $val['odds_special'] : $val['odds'],
                                    ];
                                }
                            }
                            //尾數連
                            if ($formula['type'] == 'WeiShuLian') {
                                foreach ($mantissa as $k => $numbers) {
                                    $arr['data'][] = [
                                        'key'     => $k,
                                        'name'    => $k . "尾",
                                        'numbers' => $numbers,
                                        'odds'    => $k == 0 ? $val['odds_special'] : $val['odds'],
                                    ];
                                }
                            }
                        }
                        $row["wanfa_dealil_list"][] = $arr;
                    }
                }

                $wanfa_arr[$row['pid']]['wanfa_list'][] = [
                    "wanfa_id"          => (int) $row['id'],
                    "name"              => $row['name'],
                    "wanfa_dealil_list" => $row["wanfa_dealil_list"],
                ];
            }

            ApiHelp::response(1, 200, 'success', array_values($wanfa_arr));
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 官方玩法
     */
    private function _officialWanfaList()
    {
        try {
            $lottery_id = $this->input->post("lottery_id");
            if ($lottery_id === null) {
                throw new Exception("缺少必要参数", 300);
            }

            $lottery = $this->ettm_lottery_db->row($lottery_id);
            if ($lottery === null) {
                throw new Exception("请选择正确的彩种", 300);
            }
            if ($lottery['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }
            $lottery_sort = $this->ettm_lottery_sort_db->where([
                't.operator_id' => $this->operator_id,
                't.lottery_id'  => $lottery_id,
            ])->result_one();
            if ($lottery_sort !== null && $lottery_sort['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }

            //玩法列表
            $wanfa = $this->ettm_official_wanfa_db->oddsCalculation($lottery_id, $this->uid);

            $wanfa_arr = [];
            foreach ($wanfa as $row) {
                if ($row['pid'] == 0) {
                    $wanfa_arr[$row['id']]['lottery_type_id'] = (int) $row['lottery_type_id'];
                    $wanfa_arr[$row['id']]['wanfa_pid']       = (int) $row['id'];
                    $wanfa_arr[$row['id']]['name']            = $row['name'];
                    continue;
                }

                if (array_key_exists($row['pid'], $wanfa_arr)) {
                    $data = [];
                    if ($row['payload'] != null) {
                        $payload = json_decode($row['payload'], true);
                        foreach ($payload['name'] as $name) {
                            $data[] = [
                                'name'    => $name,
                                'numbers' => $payload['value'],
                            ];
                        }
                    }
                    $wanfa_arr[$row['pid']]['wanfa_list'][] = [
                        "wanfa_id"         => (int) $row['id'],
                        "name"             => $row['name'],
                        "min_odds"         => (float) $row['min_odds'],
                        "max_odds"         => (float) $row['max_odds'],
                        "max_bet_number"   => (float) $row['max_bet_number'],
                        "max_bet_money"    => (float) $row['max_bet_money'],
                        "max_return"       => (float) $row['max_return'],
                        'data'             => $data,
                    ];
                }
            }

            ApiHelp::response(1, 200, 'success', array_values($wanfa_arr));
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery_bet/checkBet",
     *   summary="確認注單",
     *   tags={"LotteryBet"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="string",
     *                   example="8",
     *               ),
     *               @OA\Property(
     *                   property="qishu",
     *                   description="期數",
     *                   type="string",
     *                   example="20190409026",
     *               ),
     *               @OA\Property(
     *                   property="wanfa_pid",
     *                   description="玩法PID",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="value_list[0][id]",
     *                   description="注單-玩法ID",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="value_list[0][bet_money]",
     *                   description="注單-下注金額",
     *                   type="string",
     *                   example="10",
     *               ),
     *               @OA\Property(
     *                   property="value_list[0][odds]",
     *                   description="注單-賠率",
     *                   type="string",
     *                   example="1.975",
     *               ),
     *               @OA\Property(
     *                   property="value_list[0][odds_special]",
     *                   description="注單-特殊賠率",
     *                   type="string",
     *                   example="0",
     *               ),
     *               required={"lottery_id","qishu","wanfa_pid","value_list"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function checkBet()
    {
        try {
            $this->form_validation->set_rules($this->ettm_classic_bet_record_db->rules());
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $lottery_id = $this->input->post("lottery_id");
            //驗證彩種ID
            $lottery = $this->ettm_lottery_db->row($lottery_id);
            if ($lottery === null) {
                throw new Exception("请选择正确的彩种", 300);
            }
            if ($lottery['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }
            $lottery_sort = $this->ettm_lottery_sort_db->where([
                't.operator_id' => $this->operator_id,
                't.lottery_id'  => $lottery_id,
            ])->result_one();
            if ($lottery_sort !== null && $lottery_sort['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }

            $wanfa_pid = $this->input->post("wanfa_pid");
            $wanfa = $this->ettm_classic_wanfa_db->getListByLottery($lottery['lottery_type_id']);
            if (!in_array($wanfa_pid, array_column($wanfa, 'id'))) {
                throw new Exception("请选择正确的娱乐玩法", 300);
            }
            $wanfa = array_column($wanfa, 'name', 'id');
            $qishu = $this->input->post("qishu");
            $value_list = $this->input->post("value_list");
            if ($value_list === null) {
                throw new Exception("注单格式错误", 300);
            }
            $wanfa_detail = $this->ettm_classic_wanfa_detail_db->oddsCalculation($lottery_id, $qishu, $this->uid, $value_list);
            $wanfa_detail = array_column($wanfa_detail, null, 'id');

            $record = $this->ettm_lottery_record_db->where([
                'lottery_id' => $lottery_id,
                'qishu'      => $qishu,
            ])->result_one();
            $zodiac = $this->ettm_classic_wanfa_detail_db->getZodiacNumber(strtotime($record['lottery_time']));

            $total_bet_money = $total_bet_number = $is_change = 0;
            foreach ($value_list as $key => $row) {
                //驗證玩法
                if (!isset($wanfa_detail[$row['id']])) {
                    throw new Exception("请选择正确的娱乐玩法", 300);
                }
                $row['odds'] = (float) $row['odds'];
                $row['odds_special'] = (float) $row['odds_special'];
                $wfdetail = $wanfa_detail[$row['id']];
                if ((float) $wfdetail['odds'] != $row['odds'] || (float) $wfdetail['odds_special'] != $row['odds_special']) {
                    $is_change = 1;
                    $row['odds'] = (float) $wfdetail['odds'];
                    $row['odds_special'] = (float) $wfdetail['odds_special'];
                }
                $bet_number = 1;
                $row['odds_str'] = (string)$row['odds'];
                //PC28系列
                if ($lottery['lottery_type_id'] == 3) {
                    if ($row['odds_special'] != 0) {
                        $row['odds_str'] = "@$row[odds]<br>@$row[odds_special]";
                        $row['odds_str'] .= in_array((int) $row['id'], [1153, 1154, 1157]) ? '(13)' : '(14)';
                    }
                }
                //六合彩-官方玩法注數計算
                if ($lottery['lottery_type_id'] == 8) {
                    $formula = json_decode($wfdetail['formula'], true);
                    //組合注數
                    if (in_array($formula['type'], ['LianMa', 'ShengXiaoLian', 'WeiShuLian', 'QuanBuZhong'])) {
                        $bet_number = count(combinations_str($row['values'], $formula['bet_min']));
                    }
                    //三中二
                    if ($formula['type'] == 'LianMa' && $formula['value'] == '3bingo2') {
                        $row['odds_str'] = "中二@$row[odds]<br>中三@$row[odds_special]";
                    }
                    //二中特
                    if ($formula['type'] == 'LianMa' && $formula['value'] == 'bingo2_special') {
                        $row['odds_str'] = "中特@$row[odds]<br>中二@$row[odds_special]";
                    }
                    //生肖連
                    if ($formula['type'] == 'ShengXiaoLian' && in_array($zodiac[-1], explode(',', $row['values']))) {
                        $row['odds_str'] = "@$row[odds]<br>@$row[odds_special](含年肖)";
                    }
                    //尾數連
                    if ($formula['type'] == 'WeiShuLian' && in_array(0, explode(',', $row['values']))) {
                        $row['odds_str'] = "@$row[odds]<br>@$row[odds_special](含0尾)";
                    }
                    //一肖特肖
                    if ($formula['type'] == 'zodiac' && $zodiac[-1] == $formula['value']) {
                        $row['odds_str'] = (string)$row['odds_special'];
                    }
                }

                $total_bet_number += $bet_number;
                $total_bet_money += (float) bcmul($row['bet_money'], $bet_number, 2);
                $row['id'] = (int) $row['id'];
                $row['bet_money'] = (int) $row['bet_money'];
                $row['wanfa_pname'] = $wanfa[$wanfa_pid];
                $row['wanfa_name'] = $wanfa[$wfdetail['wanfa_id']];
                $row['wanfa_detail_name'] = $wfdetail['values'];
                $value_list[$key] = $row;
            }

            $data = [];
            //六合彩專用
            if ($lottery['lottery_type_id'] == 8) {
                //合肖 生肖連
                if (in_array($wanfa_pid, [177, 178])) {
                    $data = ettm_classic_wanfa_detail_model::$zodiacType;
                }
            }

            ApiHelp::response(1, 200, 'success', [
                'is_change'        => $is_change,
                'total_bet_money'  => sprintf("%.2f", $total_bet_money),
                'total_bet_number' => $total_bet_number,
                'value_list'       => $value_list,
                'data'             => $data,
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery_bet/betAction",
     *   summary="下注",
     *   tags={"LotteryBet"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="source",
     *                   description="來源 wap,pc,android,ios",
     *                   type="string",
     *                   example="wap",
     *                   enum={"wap","pc","android","ios"}
     *               ),
     *               @OA\Property(
     *                   property="category",
     *                   description="分類 1:經典 2:官方",
     *                   type="int",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="string",
     *                   example="8",
     *               ),
     *               @OA\Property(
     *                   property="qishu",
     *                   description="期數",
     *                   type="string",
     *                   example="20190409026",
     *               ),
     *               @OA\Property(
     *                   property="wanfa_pid",
     *                   description="玩法PID",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="value_list",
     *                   description="注單(經典範例:[{'id':1,'bet_money':10,'values':'0'}])",
     *                   type="json",
     *                   example="[{'id':132,'ettm_wanfa_pid':23,'values':'02,09,11|02,04,06,07|02,04','return_point':1.235,'bet_multiple':10,'bet_number':14,'bet_money':2,'bet_company':'元'}]",
     *               ),
     *               required={"category","lottery_id","qishu","wanfa_pid","source","value_list"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function betAction()
    {
        try {
            $category = $this->input->post("category");
            if ($category === null) {
                throw new Exception("缺少必要参数", 300);
            }
            switch ($category) {
                case 1:
                    $this->_classicBetAction();
                    break;
                case 2:
                    $this->_officialBetAction();
                    break;
            }
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    public function _classicBetAction()
    {
        try {
            $this->form_validation->set_rules($this->ettm_classic_bet_record_db->rules());
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }

            if ($this->uid == 0) {
                throw new Exception("缺少必要参数(001)", 300);
            }
            //驗證USER帳戶
            $user = $this->user_db->row($this->uid);
            if ($user === null) {
                throw new Exception("用户不存在(002)", 300);
            }
            if ($user['status'] == 2) {
                throw new Exception("账户已经冻结,请联系客服", 452);
            }
            $lottery_id = $this->input->post("lottery_id");
            //驗證彩種ID
            $lottery = $this->ettm_lottery_db->row($lottery_id);
            if ($lottery === null || $lottery['mode'] & 1 == 0) {
                throw new Exception("请选择正确的彩种", 300);
            }
            if ($lottery['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }
            $lottery_sort = $this->ettm_lottery_sort_db->where([
                't.operator_id' => $this->operator_id,
                't.lottery_id'  => $lottery_id,
            ])->result_one();
            if ($lottery_sort !== null && $lottery_sort['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }

            $wanfa_pid = $this->input->post("wanfa_pid");
            $wanfa = $this->ettm_classic_wanfa_db->getListByLottery($lottery['lottery_type_id']);
            if (!in_array($wanfa_pid, array_column($wanfa, 'id'))) {
                throw new Exception("请选择正确的娱乐玩法", 300);
            }
            $qishu = $this->input->post("qishu");
            $value_list = $this->input->post("value_list");
            if ($value_list === null) {
                throw new Exception("注单格式错误", 300);
            }

            $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);
            //驗證期數
            $time = time();
            if ($qishu > $qishu_arr['day_max_qishu']) {
                throw new Exception("封盘时间，停止下注", 300);
            }
            if (($qishu_arr['count_down'] - $time) <= $qishu_arr['adjust']) {
                throw new Exception("该期投注已截止", 300);
            }
            if ($qishu != $qishu_arr['next_qishu']) {
                throw new Exception("该期投注已截止", 300);
            }
            if ($lottery['lottery_type_id'] != 8) {
                //非六合彩有關盤
                if ($time < ($qishu_arr['day_start_time'] - $qishu_arr['adjust'])) {
                    throw new Exception("待开盘，停止下注", 300);
                }
            }

            //判斷有無提早開獎 判斷下注當下號碼已開出
            $record = $this->ettm_lottery_record_db->where([
                'lottery_id' => $lottery_id,
                'qishu'      => $qishu,
            ])->result_one();

            if ($record !== null && $record['status'] == 1) {
                //發送Telegram訊息
                $bot = new \TelegramBot\Api\BotApi($this->config->item('telegram_bot_token'));
                $chatid = $this->config->item('telegram_chatid_'.ENVIRONMENT);
                $message = "{$this->operator['name']}-$lottery[name]经典下注:下注时开奖号码已开出，请检查该彩票是否提前开奖";
                $bot->sendMessage($chatid, $message);

                throw new Exception("期数已开奖，下注失败", 300);
            }
            //下單狀態
            $resultType = ['type' => 0, 'feedback' => '恭喜你下注成功'];
            $isOrderStatus = 0;
            if ($user['status'] == 3) {
                $resultType = ['type' => 1, 'feedback' => '订单已提交'];
                $isOrderStatus = -1;
            }

            //核算下注
            $zodiac = $this->ettm_classic_wanfa_detail_db->getZodiacNumber($qishu_arr['count_down']);
            $pvalue = $this->ettm_classic_bet_record_db->getPValueByWanfa($lottery_id, $qishu, $this->uid);
            $wanfa_detail = $this->ettm_classic_wanfa_detail_db->oddsCalculation($lottery_id, $qishu, $this->uid, $value_list);
            $wanfa_detail = array_column($wanfa_detail, null, 'id');
            $wanfa = array_column($wanfa, null, 'id');
            $order_sn = create_order_sn('CB');
            $bet_total_money = 0; //投注總額
            $insert = [];
            foreach ($value_list as $row) {
                //驗證玩法
                if (!isset($wanfa_detail[$row['id']]) || $wanfa_detail[$row['id']]['lottery_type_id'] != $lottery['lottery_type_id']) {
                    throw new Exception("请选择正确的娱乐玩法", 300);
                }
                $wfdetail = $wanfa_detail[$row['id']];
                //驗證下注額
                $bet_money = (int) $row['bet_money'];
                if ($bet_money <= 0) {
                    throw new Exception("单注下注额不能低于1", 300);
                }
                $odds = $wfdetail['odds'];
                $bet_values_str = $wanfa[$wfdetail['wanfa_id']]['name'] . '-' . $wfdetail['values'];
                $bet_number = 1;
                $total_p_value = isset($pvalue[$row['id']]) ? (int) $pvalue[$row['id']] : 0;
                if ($bet_money < $wfdetail['bet_min_money']) {
                    throw new Exception($bet_values_str . " 单注最小下注额为" . $wfdetail['bet_min_money'], 300);
                }
                if ($bet_money > $wfdetail['bet_max_money']) {
                    throw new Exception($bet_values_str . " 单注最大下注额为" . $wfdetail['bet_max_money'], 300);
                }
                if (($total_p_value + $bet_money) > $wfdetail['qishu_max_money']) {
                    throw new Exception($bet_values_str . " 单期累计最大下注额为" . $wfdetail['qishu_max_money'], 300);
                }
                //六合彩系列-特殊處理
                if ($lottery['lottery_type_id'] == 8) {
                    $formula = json_decode($wfdetail['formula'], true);
                    $values_arr = explode(',', $row['values']);
                    $values_count = count($values_arr);
                    if (isset($formula['bet_min']) && isset($formula['bet_max'])) {
                        $bet_min = (int) $formula['bet_min'];
                        $bet_max = (int) $formula['bet_max'];
                        if ($values_count < $bet_min) {
                            throw new Exception("「$wfdetail[values]」至少选 {$bet_min} 个号码", 300);
                        }
                        if ($values_count > $bet_max) {
                            throw new Exception("「$wfdetail[values]」最多选 {$bet_max} 个号码", 300);
                        }
                    }
                    //官方玩法-bet_values_str處理
                    if (in_array($formula['type'], ['LianMa', 'HeXiao', 'ShengXiaoLian', 'WeiShuLian', 'QuanBuZhong'])) {
                        if (in_array($formula['type'], ['HeXiao', 'ShengXiaoLian'])) {
                            $zodiac_str = $this->ettm_classic_wanfa_detail_db->zodiacToValue($row['values']);
                            $bet_values_str = $wanfa[$wanfa[$wfdetail['wanfa_id']]['pid']]['name'] . '-' . $wfdetail['values'] . $wanfa[$wfdetail['wanfa_id']]['name'] . " " . $zodiac_str;
                        } elseif (in_array($formula['type'], ['WeiShuLian'])) {
                            $arr = [];
                            foreach ($values_arr as $val) {
                                $arr[] = $val . '尾';
                            }
                            $bet_values_str = $wanfa[$wanfa[$wfdetail['wanfa_id']]['pid']]['name'] . '-' . $wfdetail['values'] . $wanfa[$wfdetail['wanfa_id']]['name'] . " " . implode(',', $arr);
                        } elseif (in_array($formula['type'], ['LianMa', 'QuanBuZhong'])) {
                            $bet_values_str .= " " . string_Pad_Zero_Left($row['values']);
                        } else {
                            $bet_values_str .= " $row[values]";
                        }
                    }
                    //一肖 特肖
                    if ($formula['type'] == 'zodiac' && $zodiac[-1] == $formula['value']) {
                        $odds = $wfdetail['odds_special'];
                    }
                    //組合注數
                    if (in_array($formula['type'], ['LianMa', 'ShengXiaoLian', 'WeiShuLian', 'QuanBuZhong'])) {
                        $bet_number = count(combinations_str($row['values'], $formula['bet_min']));
                        //生肖連
                        if ($formula['type'] == 'ShengXiaoLian') {
                            if (in_array($zodiac[-1], $values_arr)) {
                                $odds = $wfdetail['odds_special'];
                            }
                        }
                        //尾數連
                        if ($formula['type'] == 'WeiShuLian') {
                            if (in_array(0, $values_arr)) {
                                $odds = $wfdetail['odds_special'];
                            }
                        }
                    }
                }

                $total_p_value = bcmul($bet_money, $bet_number, 2);
                $bet_total_money = bcadd($bet_total_money, $total_p_value, 2);

                $payload = json_encode([
                    'odds'         => (float) $wfdetail['odds'],
                    'odds_special' => (float) $wfdetail['odds_special'],
                ]);
                $insert[] = [
                    'lottery_id'      => $lottery_id,
                    'qishu'           => $qishu,
                    'uid'             => $this->uid,
                    'wanfa_pid'       => $wanfa_pid,
                    'wanfa_id'        => $wfdetail['wanfa_id'],
                    'wanfa_detail_id' => $wfdetail['id'],
                    'order_sn'        => $order_sn,
                    'p_value'         => $bet_money,
                    'bet_number'      => $bet_number,
                    'total_p_value'   => $total_p_value,
                    'odds'            => $odds,
                    'formula'         => $wfdetail['formula'],
                    'payload'         => $payload,
                    'bet_values'      => $row['values'],
                    'bet_values_str'  => $bet_values_str,
                    'status'          => $isOrderStatus,
                ];
            }

            //判斷餘額是否足夠
            if ($user['money'] < $bet_total_money) {
                throw new Exception("余额不足，本次投注失败", 300);
            }

            //寫入注單
            $this->base_model->trans_start();
            foreach ($insert as $data) {
                $this->ettm_classic_bet_record_db->insert($data);
            }
            //帳變明細
            $this->user_db->addMoney($this->uid, $order_sn, 5, $bet_total_money * -1, "经典-$lottery[name]-下注", 1, $lottery_id);
            $this->base_model->trans_complete();

            ApiHelp::response(1, 200, "success", $resultType);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 官方下注
     */
    public function _officialBetAction()
    {
        try {
            $this->form_validation->set_rules($this->ettm_official_bet_record_db->rules());
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }

            if ($this->uid == 0) {
                throw new Exception("缺少必要参数(001)", 300);
            }
            //驗證USER帳戶
            $user = $this->user_db->row($this->uid);
            if ($user === null) {
                throw new Exception("用户不存在(002)", 300);
            }
            if ($user['status'] == 2) {
                throw new Exception("账户已经冻结,请联系客服", 452);
            }
            $lottery_id = $this->input->post("lottery_id");
            //驗證彩種ID
            $lottery = $this->ettm_lottery_db->row($lottery_id);
            if ($lottery === null || $lottery['mode'] & 2 == 0) {
                throw new Exception("请选择正确的彩种", 300);
            }
            if ($lottery['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }
            $lottery_sort = $this->ettm_lottery_sort_db->where([
                't.operator_id' => $this->operator_id,
                't.lottery_id'  => $lottery_id,
            ])->result_one();
            if ($lottery_sort !== null && $lottery_sort['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }

            $qishu = $this->input->post("qishu");
            $value_list = $this->input->post("value_list");
            if ($value_list === null) {
                throw new Exception("注单格式错误", 300);
            }
            if (empty($value_list)) {
                throw new Exception("目前無待付款投注單", 300);
            }

            $qishu_arr = $this->qishu_model->getQishu(2, $lottery_id);
            //驗證期數
            $time = time();
            if ($qishu_arr['count_down'] <= $time || $qishu != $qishu_arr['next_qishu']) {
                throw new Exception("该期投注已截止", 300);
            }

            //判斷有無提早開獎 判斷下注當下號碼已開出
            $record = $this->ettm_lottery_record_db->where([
                'lottery_id' => $lottery_id,
                'qishu'      => $qishu,
            ])->result_one();

            if ($record !== null && $record['status'] == 1) {
                //發送Telegram訊息
                $bot = new \TelegramBot\Api\BotApi($this->config->item('telegram_bot_token'));
                $chatid = $this->config->item('telegram_chatid_'.ENVIRONMENT);
                $message = "{$this->operator['name']}-$lottery[name]官方下注:下注时开奖号码已开出，请检查该彩票是否提前开奖";
                $bot->sendMessage($chatid, $message);

                throw new Exception("期数已开奖，下注失败", 300);
            }
            //下單狀態
            $resultType = ['type' => 0, 'feedback' => '恭喜你下注成功'];
            $isOrderStatus = 0;
            if ($user['status'] == 3) {
                $resultType = ['type' => 1, 'feedback' => '订单已提交'];
                $isOrderStatus = -1;
            }

            //核算下注
            $wanfa_list = $this->ettm_official_wanfa_db->oddsCalculation($lottery_id, $this->uid);
            $wanfa_list = array_column($wanfa_list, null, 'id');
            $order_sn = create_order_sn('OB');
            $bet_total_money = 0; //投注總額
            $insert = [];
            foreach ($value_list as $row) {
                //驗證玩法
                if (!isset($wanfa_list[$row['wanfa_id']]) || $wanfa_list[$row['wanfa_id']]['lottery_type_id'] != $lottery['lottery_type_id']) {
                    throw new Exception("请选择正确的娱乐玩法", 300);
                }
                $wanfa = $wanfa_list[$row['wanfa_id']];
                if (!isset($wanfa_list[$wanfa['pid']])) {
                    throw new Exception("不是正确的娱乐玩法", 300);
                }
                $wanfa_p = $wanfa_list[$wanfa['pid']];
                //核算注數
                $bet_number = 0;
                switch ($lottery['lottery_type_id']) {
                    case 1:
                        $bet_number = $this->tatCompound($row['values'], $wanfa_p['key_word'], $wanfa['key_word']);
                        break;
                    case 5:
                        $bet_number = $this->pkTenCompound($row['values'], $wanfa_p['key_word'], $wanfa['key_word']);
                        break;
                    case 6:
                        if (preg_match('/Single/i', $wanfa['key_word'])) { //核算單式注數及重組下注值
                            $exfSingleValue = $this->exfSingle($row['values'], $wanfa_p['key_word'], $wanfa['key_word'], 'arr');
                            $bet_number = $exfSingleValue['count'];
                            $row['values'] = $exfSingleValue['bet_value'];
                        } else { //核算複式注數
                            $bet_number = $this->esfCompound($row['values'], $wanfa_p['key_word'], $wanfa['key_word']);
                        }
                        break;
                    case 7:
                        $bet_number = $this->lowCompound($row['values'], $wanfa_p['key_word'], $wanfa['key_word']);
                        break;
                }
                if ($bet_number > $wanfa['max_bet_number'] || $bet_number < 1) {
                    throw new Exception('超过最大投注数量或低于最低投注数量', 300);
                }
                //驗證倍數
                if ($row['bet_multiple'] < 1) {
                    throw new Exception('低于最小倍数', 300);
                }
                //驗證返點
                if ($row['return_point'] > $wanfa['max_return'] || $row['return_point'] < 0) {
                    throw new Exception('超过最大返点或低于最低返点', 300);
                }
                //驗證下注額
                if ((float) $row['bet_money'] <= 0) {
                    throw new Exception("投注金额有误", 300);
                }
                //玩法下注總額
                $total_p_value = (float) bcmul(bcmul($row['bet_money'], $bet_number, 2), $row['bet_multiple'], 2);
                if ($total_p_value <= 0 || $total_p_value > (float) $wanfa['max_bet_money']) {
                    throw new Exception('投注总额超出上限', 300);
                }
                //賠率計算
                //$odds = (1 - ($row['return_point'] / $wanfa['max_return'])) * ($wanfa['max_odds'] - $wanfa['min_odds']) + $wanfa['min_odds'];
                $odds = bcadd(bcmul(bcsub(1, bcdiv($row['return_point'], $wanfa['max_return'], 5), 5), bcsub($wanfa['max_odds'], $wanfa['min_odds'], 3), 3), $wanfa['min_odds'], 3);
                $insert[] = [
                    'lottery_id'     => $lottery_id,
                    'qishu'          => $qishu,
                    'uid'            => $this->uid,
                    'wanfa_pid'      => $wanfa['pid'],
                    'wanfa_id'       => $wanfa['id'],
                    'order_sn'       => $order_sn,
                    'p_value'        => $row['bet_money'],
                    'bet_number'     => $bet_number,
                    'total_p_value'  => $total_p_value,
                    'odds'           => $odds,
                    'bet_values'     => $row['values'],
                    'bet_values_str' => "$wanfa_p[name]-$wanfa[name]",
                    'return_point'   => $row['return_point'],
                    'bet_multiple'   => $row['bet_multiple'],
                    'status'         => $isOrderStatus,
                ];
                $bet_total_money = bcadd($bet_total_money, $total_p_value, 2);
            }

            //判斷餘額是否足夠
            if ($user['money'] < $bet_total_money) {
                throw new Exception("余额不足，本次投注失败", 300);
            }

            //寫入注單
            $this->base_model->trans_start();
            foreach ($insert as $data) {
                $this->ettm_official_bet_record_db->insert($data);
            }
            //帳變明細
            $this->user_db->addMoney($this->uid, $order_sn, 5, $bet_total_money * -1, "官方-$lottery[name]-下注", 2, $lottery_id);
            $this->base_model->trans_complete();

            ApiHelp::response(1, 200, "success", $resultType);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery_bet/getBetList",
     *   summary="投注列表",
     *   tags={"LotteryBet"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="category",
     *                   description="類別 0:全部 1:經典 2:官方 3:特色",
     *                   type="string",
     *                   example="0",
     *               ),
     *               @OA\Property(
     *                   property="type",
     *                   description="類別 0:全部 1:已中獎 2:待開獎",
     *                   type="string",
     *                   example="0",
     *               ),
     *               @OA\Property(
     *                   property="page",
     *                   description="頁數",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="per_page",
     *                   description="一頁幾筆",
     *                   type="string",
     *                   example="20",
     *               ),
     *               @OA\Property(
     *                   property="money_type",
     *                   description="貨幣類型 0:現金帳戶 1:特色棋牌帳戶",
     *                   type="string",
     *                   example="0",
     *               ),
     *               required={"category","type","page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getBetList()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'category', 'label' => 'category', 'rules' => 'trim|required'],
                ['field' => 'type', 'label' => 'type', 'rules' => 'trim|required'],
                ['field' => 'page', 'label' => 'page', 'rules' => 'trim|required'],
                ['field' => 'per_page', 'label' => 'per_page', 'rules' => 'trim|required'],
                ['field' => 'money_type', 'label' => 'money_type', 'rules' => 'trim'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $category = $this->input->post('category');
            $type = $this->input->post('type');
            $money_type = $this->input->post("money_type");
            $money_type = $money_type === null ? 0 : $money_type;
            $page = $this->input->post('page');
            $per_page = $this->input->post('per_page');
            $offset = ($page - 1) * $per_page;
            //條件
            $where['t.uid'] = $this->uid;
            $where['t.money_type'] = $money_type;
            if ($type == 1) {
                $where['t.c_value >'] = 0;
                $where['t.status'] = 1;
            }
            if ($type == 2) {
                $where['t.status'] = 0;
            }

            //生成經典注單語法
            $join[] = [$this->table_ . 'ettm_lottery t1', 't.lottery_id = t1.id', 'left'];
            $join[] = [$this->table_ . 'ettm_classic_wanfa t2', 't.wanfa_pid = t2.id', 'left'];
            $classic_count = $this->ettm_classic_bet_record_db->where($where)->group('t.lottery_id,t.qishu,t.wanfa_pid')->count();
            $classic_sql = $this->ettm_classic_bet_record_db->where($where)->join($join)->escape(false)
                ->select('1 category,0 bet_id,t.lottery_id,t.qishu,t.wanfa_pid,SUM(t.total_p_value) total_p_value,SUM(t.c_value) c_value,
                            MIN(t.status) status,MAX(t.create_time) create_time,t1.name,t2.name wanfa_name,MIN(t.is_lose_win) is_lose_win')
                ->group('t.lottery_id,t.qishu,t.wanfa_pid')
                ->get_compiled_select();
            //生成官方注單語法
            $join = [];
            $join[] = [$this->table_ . 'ettm_lottery t1', 't.lottery_id = t1.id', 'left'];
            $official_count = $this->ettm_official_bet_record_db->where($where)->count();
            $official_sql = $this->ettm_official_bet_record_db->where($where)->join($join)->escape(false)
                ->select('2 category,t.id bet_id,t.lottery_id,t.qishu,t.wanfa_pid,t.total_p_value,t.c_value,
                            t.status,t.create_time,t1.name,t.bet_values_str wanfa_name,1 is_lose_win')
                ->get_compiled_select();
            //生成特色注單語法
            $join = [];
            $join[] = [$this->table_ . 'ettm_lottery t1', 't.lottery_id = t1.id', 'left'];
            $join[] = [$this->table_ . 'ettm_special t2', 't.special_id = t2.id', 'left'];
            $special_count = $this->ettm_special_bet_record_db->where($where)->group('t.special_id,t.qishu')->count();
            $special_sql = $this->ettm_special_bet_record_db->where($where)->join($join)->escape(false)
                ->select("3 category,t.special_id bet_id,t.lottery_id,t.qishu,0 wanfa_pid,SUM(t.total_p_value) total_p_value,SUM(t.c_value) c_value,
                            MIN(t.status) status,MAX(t.create_time) create_time,t1.name,t2.type wanfa_name,MIN(t.is_lose_win) is_lose_win")
                ->group('t.special_id,t.qishu')
                ->get_compiled_select();

            switch ($category) {
                case 1: //經典
                    $count = $classic_count;
                    $result = $this->base_model->query($classic_sql . " ORDER BY create_time DESC LIMIT $offset,$per_page")->result_array();
                    break;
                case 2: //官方
                    $count = $official_count;
                    $result = $this->base_model->query($official_sql . " ORDER BY create_time DESC LIMIT $offset,$per_page")->result_array();
                    break;
                case 3: //特色
                    $count = $special_count;
                    $result = $this->base_model->query($special_sql . " ORDER BY create_time DESC LIMIT $offset,$per_page")->result_array();
                    break;
                default: //全部
                    $count = $classic_count + $official_count + $special_count;
                    $result = $this->base_model->query("$classic_sql UNION ALL $official_sql UNION ALL $special_sql ORDER BY create_time DESC LIMIT $offset,$per_page")->result_array();
                    break;
            }

            foreach ($result as $key => $row) {
                unset($row['create_time']);
                $row['category']      = (int) $row['category'];
                $row['bet_id']        = (int) $row['bet_id'];
                $row['lottery_id']    = (int) $row['lottery_id'];
                $row['qishu']         = (int) $row['qishu'];
                $row['wanfa_pid']     = (int) $row['wanfa_pid'];
                $row['total_p_value'] = sprintf("%.2f", $row['total_p_value']);
                $row['c_value']       = sprintf("%.2f", $row['c_value']);
                $row['status']        = $row['is_lose_win'] == 2 ? 9:(int)$row['status'];
                if ($category == 3) {
                    $row['wanfa_name'] = ettm_special_model::$typeList[$row['wanfa_name']];
                }
                $result[$key] = $row;
            }

            ApiHelp::response(1, 200, 'success', [
                'page'     => $page,
                'per_page' => $per_page,
                'count'    => $count,
                'list'     => $result,
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery_bet/getBetInfo",
     *   summary="投注詳情",
     *   tags={"LotteryBet"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="category",
     *                   description="類別 1:經典 2:官方 3:特色",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="bet_id",
     *                   description="注單ID",
     *                   type="string",
     *                   example="0",
     *               ),
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="string",
     *                   example="8",
     *               ),
     *               @OA\Property(
     *                   property="qishu",
     *                   description="期數",
     *                   type="string",
     *                   example="20190409026",
     *               ),
     *               @OA\Property(
     *                   property="wanfa_pid",
     *                   description="玩法PID",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="money_type",
     *                   description="貨幣類型 0:現金帳戶 1:特色棋牌帳戶",
     *                   type="string",
     *                   example="0",
     *               ),
     *               required={"category","bet_id","lottery_id","qishu","wanfa_pid"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getBetInfo()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'category', 'label' => 'category', 'rules' => 'trim|required'],
                ['field' => 'lottery_id', 'label' => 'lottery_id', 'rules' => 'trim|required'],
                ['field' => 'qishu', 'label' => 'qishu', 'rules' => 'trim|required'],
                ['field' => 'wanfa_pid', 'label' => 'wanfa_pid', 'rules' => 'trim|required'],
                ['field' => 'money_type', 'label' => 'money_type', 'rules' => 'trim'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $category = $this->input->post('category');
            $bet_id = $this->input->post('bet_id'); //特色棋牌為special_id
            $lottery_id = $this->input->post('lottery_id');
            $qishu = $this->input->post('qishu');
            $wanfa_pid = $this->input->post('wanfa_pid');
            $money_type = $this->input->post("money_type");
            $money_type = $money_type === null ? 0 : $money_type;

            $lottery = $this->ettm_lottery_db->row($lottery_id);
            //開獎資料
            $record = $this->ettm_lottery_record_db->where([
                'lottery_id' => $lottery_id,
                'qishu'      => $qishu,
            ])->result_one();

            $data = [
                'lottery_type_id' => (int) $lottery['lottery_type_id'],
                'lottery_name'    => $lottery['name'],
                'pic_icon'        => $lottery['pic_icon'],
                'qishu'           => $qishu,
                'numbers'         => $this->ettm_lottery_record_db->padLeft($lottery['lottery_type_id'], $record['numbers']),
                'value_str'       => $this->ettm_lottery_record_db->getValue($lottery['lottery_type_id'], $record['numbers'], $record['lottery_time']),
                'status'          => (int) $record['status'],
                'bet_number'      => 0,
                'total_p_value'   => '0.00',
                'total_c_value'   => '0.00',
            ];
            if ($category == 1) { //經典
                //六合彩
                if ($lottery['lottery_type_id'] == 8) {
                    $zodiac = $this->ettm_classic_wanfa_detail_db->getZodiacNumber(strtotime($record['lottery_time']));
                    $wanfa_detail = $this->ettm_classic_wanfa_detail_db->where([
                        'lottery_type_id' => 8,
                    ])->result();
                    $wanfa_detail = array_column($wanfa_detail, null, 'id');
                }
                $wanfa = $this->ettm_classic_wanfa_db->row($wanfa_pid);
                $data['wanfa_name'] = $wanfa['name'];
                $result = $this->ettm_classic_bet_record_db->where([
                    't.uid'        => $this->uid,
                    't.lottery_id' => $lottery_id,
                    't.qishu'      => $qishu,
                    't.wanfa_pid'  => $wanfa_pid,
                ])->order(['create_time', 'asc'])->result();
                
                $detail_list = [];
                $alldraw = true;
                foreach ($result as $row) {
                    $payload = json_decode($row['payload'], true);
                    $odds = sprintf("%.3f", $payload['odds']);
                    $odds_special = sprintf("%.3f", $payload['odds_special']);
                    //退款
                    $c_value = $row['status'] > 1 ? '0.00':sprintf("%.2f", $row['c_value']);
                    //平局
                    $total_p_value = (float)$row['total_p_value'];
                    if ($row['is_lose_win'] == 2) {
                        $c_value = '0.00';
                        $total_p_value .= '(已返还)';
                    } else {
                        $alldraw = false;
                    }

                    $data['bet_number']    += $row['bet_number'];
                    $data['total_p_value']  = (float) bcadd($data['total_p_value'], $row['total_p_value'], 2);
                    $data['total_c_value']  = bcadd($data['total_c_value'], $c_value, 2);
                    $data['status']         = (int) $row['status'];
                    $data['create_time']    = $row['create_time'];
                    
                    if ($lottery['lottery_type_id'] == 8) { //六合彩
                        $formula = json_decode($row['formula'], true);
                        if (in_array($formula['type'], ['LianMa','ShengXiaoLian','WeiShuLian','QuanBuZhong'])) { //官方玩法
                            $detail = [];
                            if (isset($payload['c_odds'])) {
                                //已派彩則顯示明細
                                $detail = $payload['c_odds']['detail'];
                            } else {
                                //未派彩無明細資料
                                $numbers = explode(',', $row['bet_values']);
                                $combination = [];
                                if ($formula['type'] == 'LianMa') {
                                    $combination = combination($numbers, $formula['bet_min']);
                                } else {
                                    $combination = combination($numbers, $formula['value']);
                                }
                                foreach ($combination as $val) {
                                    $detail[] = [
                                        'bet_value' => $val,
                                        'odds'      => $odds,
                                        'c_value'   => 0,
                                    ];
                                }
                            }
                            //寫入明細
                            foreach ($detail as $val) {
                                $arr = [];
                                $arr['p_value'] = (float)$row['p_value'];
                                $arr['c_value'] = $row['status'] > 1 ? '0.00':sprintf("%.2f", $val['c_value']);
                                $arr['odds'] = sprintf("%.3f", $val['odds']);
                                $values_str = [];
                                if ($formula['type'] == 'LianMa') { //連碼
                                    switch ($formula['value']) {
                                        case '3bingo2': //三中二
                                            $arr['odds'] = "中二@{$odds}<br>中三@{$odds_special}";
                                            break;
                                        case 'bingo2_special': //二中特
                                            $arr['odds'] = "中特@{$odds}<br>中二@{$odds_special}";
                                            break;
                                    }
                                    $values_str = array_map(function ($row) {
                                        return str_pad($row, 2, '0', STR_PAD_LEFT);
                                    }, $val['bet_value']);
                                } elseif ($formula['type'] == 'ShengXiaoLian') { //生肖連
                                    $arr['odds'] = in_array($zodiac[-1], $val['bet_value']) ? $odds_special.'(含年肖)':$odds;
                                    $values_str = $this->ettm_classic_wanfa_detail_db->zodiacToValue($val['bet_value']);
                                } elseif ($formula['type'] == 'WeiShuLian') { //尾數連
                                    $arr['odds'] = in_array(0, $val['bet_value']) ? $odds_special.'(含0尾)':$odds;
                                    $values_str = array_map(function ($row) {
                                        return $row.'尾';
                                    }, $val['bet_value']);
                                } elseif ($formula['type'] == 'QuanBuZhong') { //全不中
                                    $values_str = array_map(function ($row) {
                                        return str_pad($row, 2, '0', STR_PAD_LEFT);
                                    }, $val['bet_value']);
                                }
                                $arr['bet_values_str'] = explode(" ", $row['bet_values_str'])[0].' '.implode(',', $values_str);
                                $arr['create_time'] = $row['create_time'];
                                $detail_list[] = $arr;
                            }
                        } else {
                            $detail_list[]  = [
                                'bet_values_str' => $row['bet_values_str'],
                                'p_value'        => $total_p_value,
                                'c_value'        => $c_value,
                                'odds'           => sprintf("%.3f", $row['odds']),
                                'create_time'    => $row['create_time'],
                            ];
                        }
                    } elseif ($lottery['lottery_type_id'] == 3) { //PC28
                        $arr = [
                            'bet_values_str' => $row['bet_values_str'],
                            'p_value'        => $total_p_value,
                            'c_value'        => $c_value,
                            'odds'           => sprintf("%.3f", $row['odds']),
                            'create_time'    => $row['create_time'],
                        ];
                        if ($payload['odds_special'] != 0) {
                            $arr['odds'] = "@$odds<br>@$odds_special";
                        }
                        $detail_list[]  = $arr;
                    } else {
                        $detail_list[]  = [
                            'wanfa_id'        => $row['wanfa_id'],
                            'wanfa_detail_id' => $row['wanfa_detail_id'],
                            'bet_values_str'  => $row['bet_values_str'],
                            'p_value'         => $total_p_value,
                            'c_value'         => $c_value,
                            'odds'            => sprintf("%.3f", $row['odds']),
                            'create_time'     => $row['create_time'],
                        ];
                    }
                }
                $data['bet_detail_list'] = $detail_list;
                if ($alldraw) {
                    $data['status'] = 9;
                }
            } elseif ($category == 2) { //官方
                $row = $this->ettm_official_bet_record_db->row($bet_id);
                $data['bet_values_str'] = $row['bet_values_str'];
                $data['bet_number']     = (int) $row['bet_number'];
                $data['total_p_value']  = (float) $row['total_p_value'];
                $data['total_c_value']  = $row['status'] > 1 ? '0.00':sprintf("%.2f", $row['c_value']);
                $data['bet_values']     = $row['bet_values'];
                $data['bet_multiple']   = (int) $row['bet_multiple'];
                $data['odds']           = (float) $row['odds'];
                $data['return_point']   = (float) $row['return_point'];
                $data['create_time']    = $row['create_time'];
                $data['order_sn']       = $row['order_sn'];
                $data['status']         = (int) $row['status'];
            } elseif ($category == 3) { //特色
                $data = $this->ettm_special_bet_record_db->getNiuBetDetail($bet_id, $qishu, $this->uid, $money_type);
            }

            ApiHelp::response(1, 200, 'success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery_bet/getRandomCombination",
     *   summary="官方彩隨機下注",
     *   tags={"LotteryBet"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="wanfa_id",
     *                   description="玩法ID",
     *                   type="int",
     *                   example="45",
     *               ),
     *               @OA\Property(
     *                   property="bet_number",
     *                   description="注數",
     *                   type="int",
     *                   example="1",
     *               ),
     *               required={"wanfa_id","bet_number"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getRandomCombination()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'wanfa_id', 'label' => 'wanfa_id', 'rules' => 'trim|required'],
                ['field' => 'bet_number', 'label' => 'bet_number', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $this->load->model('ettm_official_random_model');
            $wanfa_id = $this->input->post('wanfa_id');
            $bet_number = $this->input->post('bet_number');
            $data = $this->ettm_official_random_model->getWanfaRandomData($wanfa_id, $bet_number);

            ApiHelp::response(1, 200, 'success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 官方时时彩系列投注注数核算
     * @param $value_list
     * @param $p_key_word
     * @param $key_word
     * @return int
     */
    private function tatCompound($value_list, $p_key_word, $key_word)
    {
        $this->load->library('Lottery_Permutation_combination/Tat/' . $p_key_word, strtolower($p_key_word));
        $p_key_word = strtolower($p_key_word);
        //此玩法的投注内容需要特殊处理
        if ($key_word == 'three_Back_T_Diff' || $key_word == 'three_Back_T_Sum' || $key_word == 'two_Front_Sum' || $key_word == 'two_Back_Sum') {
            $bet_arr['bet_number'] = $value_list;
        //此玩法的投注内容需要特殊处理
        } elseif ($key_word == 'dw_Gall' || $key_word == 'arbitrary_Choice_Direct_Two' || $key_word == 'arbitrary_Choice_Direct_Three' || $key_word == 'arbitrary_Choice_Direct_Four') {
            $bet_values = explode('|', $value_list);
            foreach ($bet_values as $i => &$value) {
                if (strlen($value) <= 0) {
                    unset($bet_values[$i]);
                    continue;
                }
            }
            $bet_arr = implode('|', $bet_values);
        } else {
            $bet_arr = $value_list;
        }
        if ($key_word == 'three_Back_T_Sum') {
            //计算时时采系列three_Back_T_Sum和值尾数注数
            $result = $this->$p_key_word->$key_word($bet_arr);
            return count($result) / 100;
        } else {
            $result = $this->$p_key_word->$key_word($bet_arr);
            return count($result);
        }
    }

    /**
     * 官方北京PK拾 投注注数核算
     * @param $value_list
     * @param $p_key_word
     * @param $key_word
     * @return int
     */
    private function pkTenCompound($value_list, $p_key_word, $key_word)
    {
        $this->load->library('Lottery_Permutation_combination/Pk_Ten/' . $p_key_word, strtolower($p_key_word));
        $p_key_word = strtolower($p_key_word);
        //此玩法的投注内容需要特殊处理
        if ($key_word == 'three_Back_T_Diff' || $key_word == 'three_Back_T_Sum' || $key_word == 'two_Front_Sum' || $key_word == 'two_Back_Sum') {
            $bet_arr['bet_number'] = $value_list;
        //此玩法的投注内容需要特殊处理
        } elseif ($key_word == 'dw_Gall' || $key_word == 'arbitrary_Choice_Direct_Two' || $key_word == 'arbitrary_Choice_Direct_Three' || $key_word == 'arbitrary_Choice_Direct_Four') {
            $bet_values = explode('|', $value_list);
            foreach ($bet_values as $i => &$value) {
                if (strlen($value) <= 0) {
                    unset($bet_values[$i]);
                    continue;
                }
            }
            $bet_arr = implode('|', $bet_values);
        } else {
            $bet_arr = $value_list;
        }
        if ($key_word == 'three_Back_T_Sum') {
            //计算时时采系列three_Back_T_Sum和值尾数注数
            $result = $this->$p_key_word->$key_word($bet_arr);
            return count($result) / 100;
        } else {
            $result = $this->$p_key_word->$key_word($bet_arr);
            return count($result);
        }
    }

    /**
     * 官方11选5 系列 單式核算注數及重組下注資訊
     * @param $value_list                =>  下注資訊
     * @param $p_key_word                =>  父層玩法
     * @param $key_word                  =>  玩法
     * @param string $mode               =>  'arr' 輸出 注數及重組下注資訊 , 'val' 回傳重組過後的下注值, ''預設回傳注數
     * @return array|int|mixed|string
     */
    public function exfSingle($value_list, $p_key_word, $key_word, $mode = '')
    {
        $this->load->library('Lottery_Permutation_combination/Eleven_Choice_Five/' . $p_key_word, strtolower($p_key_word));
        $p_key_word = strtolower($p_key_word);

        $keyWordOne = ['eleven_Front_Two_Direct_Single', 'eleven_Front_Three_Direct_Single',];
        $keyWordTwo = ['eleven_Front_Two_Group_Single', 'eleven_Front_Three_Group_Single',];

        $bet_value = "";
        $bet_value_arr = [];

        if (in_array($key_word, $keyWordOne)) { //直選单式

            $bet_value = str_replace(',', '|', trim($value_list));
            $bet_value = str_replace('，', '|', $bet_value);
            $bet_value_arr = explode('|', $bet_value);
            $bet_value_arr = array_unique($bet_value_arr); //去掉数组中重复对数值

            $bet_value = str_replace(' ', ',', $bet_value);
            foreach ($bet_value_arr as $key => $value) {
                $bet_arr = str_replace(' ', '|', $value);
                $value = explode(' ', $value);
                $chkBetValue = [];
                foreach ($value as $v) {
                    if (preg_match('/^0[1-9]$|^1[0-1]$/', $v)) { //判斷使用者輸入的單數是否為01~11的格式
                        $chkBetValue[] = $v;
                    }
                }

                $chkBetValueStr = implode(' ', $chkBetValue);

                $result = $this->$p_key_word->$key_word($bet_arr);
                if (in_array($chkBetValueStr, $result)) {
                    $arr[] = $chkBetValueStr;
                }
            }
            $bet_value = ""; //重置变量
            $bet_value = implode(',', $arr);
        }

        if (in_array($key_word, $keyWordTwo)) { //組選单式
            $bet_value = str_replace('，', ',', trim($value_list)); //英文,替换中文，号
            $bet_value_arr = explode(',', $bet_value);
            $bet_value = str_replace(' ', ',', $bet_value); //空格替换英文,
            $bet_value = explode(',', $bet_value); //转换为1唯数组

            $chkBetValue = [];
            foreach ($bet_value as $v) { //判斷使用者輸入的單數是否為01~11的格式
                if (preg_match('/^0[1-9]$|^1[0-1]$/', $v)) {
                    $chkBetValue[] = $v;
                }
            }

            $bet_value = array_unique($bet_value); //去掉数组中重复对数值
            $bet_value = implode(',', $bet_value); //将数组转为","隔开对字符串
            $bet_arr = $bet_value;
            $result = $this->$p_key_word->$key_word($bet_arr);
            foreach ($bet_value_arr as $key => &$value) {
                $value = explode(' ', $value);
                sort($value); //与算法排序一致
                $value = implode(' ', $value);
                if (in_array($value, $result)) {
                    $arr[] = $value;
                }
            }

            $bet_value = ""; //重置变量
            $bet_value = implode(',', $arr);
        }

        if ($p_key_word == 'arbitrary_single_choice') { //任選单式 系列
            $bet_value = str_replace('，', ',', trim($value_list)); //英文,替换中文，号
            $bet_value_arr = explode(',', $bet_value);
            $bet_value = str_replace(' ', ',', $bet_value); //空格替换英文,
            $bet_value = explode(',', $bet_value); //转换为1唯数组

            $bet_value = array_unique($bet_value); //去掉数组中重复对数值

            $chkBetValue = [];
            foreach ($bet_value as $v) { //判斷使用者輸入的單數是否為01~11的格式
                if (preg_match('/^0[1-9]$|^1[0-1]$/', $v)) {
                    $chkBetValue[] = $v;
                }
            }

            $bet_value = implode(',', $chkBetValue); //将数组转为","隔开对字符串;
            $bet_arr = $bet_value;
            $result = $this->$p_key_word->$key_word($bet_arr);


            foreach ($bet_value_arr as $key => $value) {
                $value = explode(' ', $value);
                sort($value); //与算法排序一致
                $value = implode(' ', $value);
                if (in_array($value, $result)) {
                    $arr[] = $value;
                }
            }
            if ($key_word == 'eleven_Arbitrary_Single_One_To_One') {
                $arr = array_unique($arr); //去掉数组中重复对数值
            }
            $bet_value = "";
            $bet_value = implode(',', $arr);
        }

        if ($mode == 'arr') {
            return [
                "count"     => count($arr),
                "bet_value" => $bet_value
            ];
        }

        if ($mode == 'val') {
            return $bet_value;
        }

        return count($arr);
    }

    /**
     * 官方 11选5系列 投注注数核算
     * @param $value_list
     * @param $p_key_word
     * @param $key_word
     * @return int
     */
    private function esfCompound($value_list, $p_key_word, $key_word)
    {
        $this->load->library('Lottery_Permutation_combination/Eleven_Choice_Five/' . $p_key_word, strtolower($p_key_word));
        $p_key_word = strtolower($p_key_word);
        //此玩法的投注内容需要特殊处理
        $key_word_one = ['three_Back_T_Diff', 'three_Back_T_Sum', 'two_Front_Sum', 'two_Back_Sum'];
        $key_word_two = ['eleven_dw_Gamll', 'arbitrary_Choice_Direct_Two', 'arbitrary_Choice_Direct_Three', 'arbitrary_Choice_Direct_Four'];
        if (in_array($key_word, $key_word_one)) {
            $bet_arr['bet_number'] = $value_list;
        //此玩法的投注内容需要特殊处理
        } elseif (in_array($key_word, $key_word_two)) {
            $bet_values = explode('|', $value_list);
            foreach ($bet_values as $i => &$value) {
                if (strlen($value) <= 0) {
                    unset($bet_values[$i]);
                    continue;
                }
            }
            $bet_arr = implode('|', $bet_values);
        } else {
            $bet_arr = $value_list;
        }
        $result = $this->$p_key_word->$key_word($bet_arr);
        return count($result);
    }

    /**
     * 官方低頻彩系列投注注数核算
     * @param $value_list
     * @param $p_key_word
     * @param $key_word
     * @return int
     */
    private function lowCompound($value_list, $p_key_word, $key_word)
    {
        $this->load->library('Lottery_Permutation_combination/Low/' . $p_key_word, strtolower($p_key_word));
        $p_key_word = strtolower($p_key_word);
        /**********注意***********/
        //此玩法的投注内容需要特殊处理
        if ($key_word == 'low_Three_Direct_Sum') {
            $bet_arr['bet_number'] = $value_list;
        //此玩法的投注内容需要特殊处理
        } elseif ($key_word == 'low_Dw') {
            $bet_values = explode('|', $value_list);
            foreach ($bet_values as $i => &$value) {
                if (strlen($value) <= 0) {
                    unset($bet_values[$i]);
                    continue;
                }
            }
            $bet_arr = implode('|', $bet_values);
        } else {
            $bet_arr = $value_list;
        }
        /*********************/
        $result = $this->$p_key_word->$key_word($bet_arr);
        return count($result);
    }
}
