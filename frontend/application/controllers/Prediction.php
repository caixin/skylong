<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction extends CommonBase
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('prediction_model', 'prediction_db');
        $this->load->model('prediction_buy_model', 'prediction_buy_db');
        $this->load->model('prediction_relief_model', 'prediction_relief_db');
        $this->load->model('prediction_robot_bet_model', 'prediction_robot_bet_db');
        $this->load->model('prediction_robot_setting_model', 'prediction_robot_setting_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_sort_model', 'ettm_lottery_sort_db');
        $this->load->model('ettm_lottery_type_model', 'ettm_lottery_type_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_classic_wanfa_model', 'ettm_classic_wanfa_db');
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
        $this->load->model('qishu_model');
        $this->is_userlogin();

        if (!isset($this->module[1])) {
            ApiHelp::response(0, 400, '热门预测模组未开启!');
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/getHomeList",
     *   summary="取得首頁熱門預測",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="source",
     *                   description="來源 wap,pc,android,ios",
     *                   type="string",
     *                   example="ios",
     *                   enum={"wap","pc","android","ios"}
     *               ),
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="string",
     *                   example="",
     *               ),
     *               required={"source"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getHomeList()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'source', 'label' => '来源', 'rules' => 'trim|required']
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $source = $this->input->post('source', true);
            $lottery_id = $this->input->post('lottery_id', true);
            if (!array_key_exists($source, base_model::$sourceList)) {
                throw new Exception('参数错误', 401);
            }

            $digits = 3; //首頁統一顯示預測三碼
            $time = time();
            $free = 0;
            $buys = [];
            if ($this->uid > 0) {
                $user = $this->user_db->row($this->uid);
                //免費看號次數 - APP才能使用
                if (in_array($source, ['android', 'ios'])) {
                    $free = $user['free_prediction'];
                }

                $result = $this->prediction_buy_db->where([
                    't.uid'    => $this->uid,
                    't.digits' => $digits,
                    't.status' => 0,
                ])->order(['qishu','asc'])->result();
                foreach ($result as $row) {
                    $buys["$row[prediction_id]-$row[qishu]"] = $row;
                }
            }
            
            //取得熱門預測列表
            $where['t.is_home >'] = 0;
            if (!empty($lottery_id)) {
                $where['t.lottery_id'] = $lottery_id;
            }
            $result = $this->prediction_db->where($where)
                ->order(['is_home','asc'])
                ->result();
            $data = [];
            foreach ($result as $row) {
                $qishu_arr = $this->qishu_model->getQishu(1, $row['lottery_id']);
                $count_down = $qishu_arr['count_down'] - $time;

                $lottery = $this->ettm_lottery_db->row($row['lottery_id']);
                $lottery_sort = $this->ettm_lottery_sort_db->row_change($row['lottery_id']);
                $status = $lottery['status'] == 1 && $lottery_sort['status'] == 1 ? 1:0;

                if ($lottery['lottery_type_id'] != 8 && ($time < $qishu_arr['day_start_time'] || $time > $qishu_arr['day_close_time'])) {
                    $status = 2;
                    $count_down = $qishu_arr['day_start_time'] - $time;
                    $count_down = $count_down < 0 ? $count_down + 86400 : $count_down;
                }

                $is_buy = false;
                $price = $free > 0 ? 0:$row['price'];
                $numbers = '';
                if (isset($buys["$row[id]-$qishu_arr[next_qishu]"])) {
                    $buy = $buys["$row[id]-$qishu_arr[next_qishu]"];
                    $is_buy = true;
                    $price = $buy['price'];
                    $numbers = $buy['numbers'];
                }
                //取出玩法PID
                $wanfa_ids = explode(',', $row['wanfa_id']);
                $wanfa = $this->ettm_classic_wanfa_db->row($wanfa_ids[0]);

                $data[] = [
                    'prediction_id'   => (int)$row['id'],
                    'prediction_name' => $row['name'].prediction_buy_model::$digitsList[$digits],
                    'digits'          => $digits,
                    'lottery_type_id' => (int)$lottery['lottery_type_id'],
                    'lottery_id'      => (int)$row['lottery_id'],
                    'lottery_name'    => $lottery['name'],
                    'pic_icon'        => $lottery['pic_icon'],
                    'status'          => $status,
                    'price'           => (float)$price,
                    'is_buy'          => $is_buy,
                    'numbers'         => $numbers,
                    'qishu'           => $qishu_arr['next_qishu'],
                    'count_down'      => $count_down,
                    'close_time'      => $qishu_arr['adjust'],
                    'wanfa_pid'       => (int)$wanfa['pid'],
                ];
            }
            
            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/buyAction",
     *   summary="購買預測號",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="source",
     *                   description="來源 wap,pc,android,ios",
     *                   type="string",
     *                   example="ios",
     *                   enum={"wap","pc","android","ios"}
     *               ),
     *               @OA\Property(
     *                   property="prediction_id",
     *                   description="玩法預測ID",
     *                   type="int",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="qishu",
     *                   description="期數",
     *                   type="string",
     *                   example="735711",
     *               ),
     *               @OA\Property(
     *                   property="digits",
     *                   description="預測幾碼",
     *                   type="int",
     *                   example="3",
     *               ),
     *               required={"source","prediction_id","qishu","digits"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function buyAction()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'source', 'label' => '来源', 'rules' => 'trim|required'],
                ['field' => 'prediction_id', 'label' => '玩法预测ID', 'rules' => 'trim|required|is_natural'],
                ['field' => 'qishu', 'label' => '期数', 'rules' => 'trim|required'],
                ['field' => 'digits', 'label' => '預測幾碼', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            if ($this->uid == 0) {
                throw new Exception('没有登录，请您先登录~~', 401);
            }
            $source        = $this->input->post('source', true);
            $prediction_id = $this->input->post('prediction_id', true);
            $qishu         = $this->input->post('qishu', true);
            $digits        = $this->input->post('digits', true);

            if (!array_key_exists($source, base_model::$sourceList)) {
                throw new Exception('参数错误', 401);
            }

            $time = time();
            //熱門預測資訊
            $prediction = $this->prediction_db->row($prediction_id);
            if ($prediction === null) {
                throw new Exception("预测资料错误!", 300);
            }
            //判斷是否已購買
            $buy = $this->prediction_buy_db->where([
                'uid'           => $this->uid,
                'prediction_id' => $prediction_id,
                'qishu'         => $qishu,
                'digits'        => $digits,
                'status'        => 0,
            ])->result_one();
            if ($buy !== null) {
                throw new Exception("不可重复购买!!", 300);
            }
            $price = $prediction['price'];
            $user = $this->user_db->row($this->uid);
            //免費看號次數 - APP才能使用
            if (in_array($source, ['android', 'ios']) && $user['free_prediction'] > 0) {
                $price = 0;
            }
            
            $lottery = $this->ettm_lottery_db->row($prediction['lottery_id']);
            //驗證 lottery_id & qishu
            $qishu_arr = $this->qishu_model->getQishu(1, $lottery['id']);
            if (($qishu_arr['count_down'] - $time) <= $qishu_arr['adjust']) {
                throw new Exception("封盘时间，停止看号", 300);
            }
            if ($qishu != $qishu_arr['next_qishu']) {
                throw new Exception("该期投注已截止", 300);
            }
            if ($lottery['lottery_type_id'] != 8) {
                //非香港六合彩有關盤
                if ($time < ($qishu_arr['day_start_time'] - $qishu_arr['adjust'])) {
                    throw new Exception("待开盘，稍后看号", 300);
                }
            }
            //判斷帳戶餘額是否足夠
            if ($user['money'] < $price) {
                throw new Exception("馀额不足，请先储值!!", 300);
            }

            $this->base_model->trans_start();
            //更新
            if ($user['free_prediction'] > 0) {
                $this->user_db->update([
                    'id'              => $this->uid,
                    'free_prediction' => $user['free_prediction'] - 1,
                ]);
            }
            //隨機取得預測號
            $values = $this->prediction_db->getValues($prediction_id);
            shuffle($values);
            $numbers = array_slice($values, 0, $digits);
            sort($numbers);
            $numbers = implode(',', $numbers);
            //新增訂單
            $buy_id = $this->prediction_buy_db->insert([
                'uid'           => $this->uid,
                'prediction_id' => $prediction_id,
                'qishu'         => $qishu,
                'digits'        => $digits,
                'numbers'       => $numbers,
                'price'         => $price,
            ]);
            
            $digits = prediction_buy_model::$digitsList[$digits];
            $description = "$lottery[name]预测看号支付<br>$prediction[name]$digits-$numbers";
            //帳變明細
            $this->user_db->addMoney($this->uid, $qishu, 20, -$price, $description, 1, $lottery['id'], $buy_id);
            $this->base_model->trans_complete();

            if ($this->base_model->trans_status() === false) {
                throw new Exception("支付失败!!", 405);
            }
            ApiHelp::response(1, 200, '支付成功!');
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/getUserBuyList",
     *   summary="取得當前期數用戶購買記錄",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="source",
     *                   description="來源 wap,pc,android,ios",
     *                   type="string",
     *                   example="ios",
     *                   enum={"wap","pc","android","ios"}
     *               ),
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="int",
     *                   example="14",
     *               ),
     *               @OA\Property(
     *                   property="prediction_id",
     *                   description="預測ID",
     *                   type="int",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="digits",
     *                   description="幾位數",
     *                   type="int",
     *                   example="3",
     *               ),
     *               @OA\Property(
     *                   property="type",
     *                   description="回傳類型 0:全部 1:購買紀錄 2:投注熱度",
     *                   type="int",
     *                   example="0",
     *               ),
     *               required={"source","lottery_id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getUserBuyList()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'source', 'label' => '来源', 'rules' => 'trim|required'],
                ['field' => 'lottery_id', 'label' => '彩种ID', 'rules' => 'trim|required|is_natural'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $source        = $this->input->post('source', true);
            $lottery_id    = $this->input->post('lottery_id', true);
            $prediction_id = $this->input->post('prediction_id', true);
            $digits        = $this->input->post('digits', true);
            $type          = $this->input->post('type', true);
            if ($this->uid == 0) {
                throw new Exception("没有登录，请您先登录~~", 401);
            }
            if (!array_key_exists($source, base_model::$sourceList)) {
                throw new Exception("参数错误", 401);
            }
            
            $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);
            //彩種資訊
            $lottery = $this->ettm_lottery_db->row($lottery_id);
            //玩法整理
            $wanfa = $this->ettm_classic_wanfa_detail_db->where([
                't.lottery_type_id' => $lottery['lottery_type_id'],
            ])->result();
            $wanfa_arr = [];
            foreach ($wanfa as $row) {
                $wanfa_arr[$row['wanfa_id']][$row['id']] = $row['values'];
            }
            //購買預測
            $join[] = [$this->table_.'prediction t1', 't.prediction_id = t1.id', 'left'];
            $result = $this->prediction_buy_db->select('t.*,t1.wanfa_id')->where([
                't.uid'         => $this->uid,
                't1.lottery_id' => $lottery_id,
                't.qishu'       => $qishu_arr['next_qishu'],
            ])->join($join)->result();

            $data = null;
            $prediction_arr = [];
            foreach ($result as $row) {
                $wanfa_ids = explode(',', $row['wanfa_id']);
                $numbers = explode(',', $row['numbers']);
                
                foreach ($wanfa_ids as $key => $wanfa_id) {
                    if (!isset($wanfa_arr[$wanfa_id])) {
                        continue;
                    }
                    foreach ($wanfa_arr[$wanfa_id] as $wanfa_detail_id => $values) {
                        if (is_numeric($values)) {
                            $values = $this->ettm_lottery_record_db->padLeft($lottery['lottery_type_id'], $values);
                        }
                        //購買預測號寫入
                        if (in_array($type, [0,1]) && in_array($values, $numbers)) {
                            $data['buy'][] = [
                                'wanfa_detail_id' => $wanfa_detail_id,
                                'is_check'        => $row['prediction_id'] == $prediction_id &&
                                                     $row['digits'] == $digits &&
                                                     $key == 0,
                            ];
                        }
                        //整理有購買的位置所有的玩法
                        $prediction_arr[$row['prediction_id']][$wanfa_detail_id] = $values;
                    }
                }
            }

            //投注熱度
            if (in_array($type, [0,2])) {
                foreach ($prediction_arr as $prediction_id => $wanfa_detail) {
                    $rank = $this->prediction_robot_bet_db->getRankData($prediction_id, $qishu_arr['next_qishu']);
                    $rank = array_column($rank, 'sort', 'name');
                    foreach ($wanfa_detail as $wanfa_detail_id => $values) {
                        if (isset($rank[$values]) && $rank[$values] <= 3) {
                            $data['hot'][] = [
                                'wanfa_detail_id' => $wanfa_detail_id,
                                'rank'            => $rank[$values],
                            ];
                        }
                    }
                }
            }

            ApiHelp::response(1, 200, 'succsee', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/getMenu",
     *   summary="選單",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="source",
     *                   description="來源 wap,pc,android,ios",
     *                   type="string",
     *                   example="ios",
     *                   enum={"wap","pc","android","ios"}
     *               ),
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="int",
     *                   example="",
     *               ),
     *               required={"source"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getMenu()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'source', 'label' => '来源', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $source     = $this->input->post('source', true);
            $lottery_id = $this->input->post('lottery_id', true);
            if (!array_key_exists($source, base_model::$sourceList)) {
                throw new Exception("参数错误", 401);
            }

            $time = time();
            $where = [];
            if (!empty($lottery_id)) {
                $where['lottery_id'] = $lottery_id;
            }
            $result = $this->prediction_db->select('lottery_id,MAX(is_home) is_home')
                        ->where($where)->group('lottery_id')->order(['is_home','asc'])->result();
            $data = [];
            foreach ($result as $row) {
                $qishu_arr = $this->qishu_model->getQishu(1, $row['lottery_id']);
                $count_down = $qishu_arr['count_down'] - $time;

                $lottery = $this->ettm_lottery_db->row($row['lottery_id']);
                $lottery_sort = $this->ettm_lottery_sort_db->row_change($row['lottery_id']);
                $status = $lottery['status'] == 1 && $lottery_sort['status'] == 1 ? 1:0;

                if ($lottery['lottery_type_id'] != 8 && ($time < $qishu_arr['day_start_time'] || $time > $qishu_arr['day_close_time'])) {
                    $status = 2;
                    $count_down = $qishu_arr['day_start_time'] - $time;
                    $count_down = $count_down < 0 ? $count_down + 86400 : $count_down;
                }
                $lottery_type = $this->ettm_lottery_type_db->row($lottery['lottery_type_id']);
                $data[$lottery_type['id']]['lottery_type_id']        = (int)$lottery_type['id'];
                $data[$lottery_type['id']]['lottery_type_name']      = $lottery_type['name'];
                $data[$lottery_type['id']]['lottery_type_pic_icon'] = $lottery_type['pic_icon2'];
                $data[$lottery_type['id']]['data'][] = [
                    'lottery_id' => (int)$row['lottery_id'],
                    'name'       => $lottery['name'],
                    'pic_icon'   => $lottery['pic_icon'],
                    'status'     => $status,
                    'qishu'      => $qishu_arr['next_qishu'],
                    "count_down" => $count_down,
                    "close_time" => $qishu_arr['adjust'],
                ];
            }
            $data = array_values($data);

            ApiHelp::response(1, 200, 'succsee', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/getMoreList",
     *   summary="更多預測",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="source",
     *                   description="來源 wap,pc,android,ios",
     *                   type="string",
     *                   example="ios",
     *                   enum={"wap","pc","android","ios"}
     *               ),
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="int",
     *                   example="14",
     *               ),
     *               required={"source","lottery_id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getMoreList()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'source', 'label' => '来源', 'rules' => 'trim|required'],
                ['field' => 'lottery_id', 'label' => '彩种ID', 'rules' => 'trim|required|is_natural'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $source     = $this->input->post('source', true);
            $lottery_id = $this->input->post('lottery_id', true);
            if (!array_key_exists($source, base_model::$sourceList)) {
                throw new Exception("参数错误", 401);
            }

            $digits_arr = [3,5];
            $time = time();
            $free = 0;
            $buys = [];
            if ($this->uid > 0) {
                $user = $this->user_db->row($this->uid);
                //免費看號次數 - APP才能使用
                if (in_array($source, ['android', 'ios'])) {
                    $free = $user['free_prediction'];
                }

                $result = $this->prediction_buy_db->where([
                    't.uid'    => $this->uid,
                    't.status' => 0
                ])->order(['qishu','asc'])->result();
                foreach ($result as $row) {
                    $buys["$row[prediction_id]-$row[qishu]-$row[digits]"] = $row;
                }
            }
            
            //取得熱門預測列表
            $result = $this->prediction_db->where(['t.lottery_id'=>$lottery_id])
                ->order(['t.sort','asc'])
                ->result();
            $data = [];
            foreach ($result as $row) {
                $qishu_arr = $this->qishu_model->getQishu(1, $row['lottery_id']);
                $count_down = $qishu_arr['count_down'] - $time;

                $lottery = $this->ettm_lottery_db->row($row['lottery_id']);
                $lottery_sort = $this->ettm_lottery_sort_db->row_change($row['lottery_id']);
                $status = $lottery['status'] == 1 && $lottery_sort['status'] == 1 ? 1:0;

                if ($lottery['lottery_type_id'] != 8 && ($time < $qishu_arr['day_start_time'] || $time > $qishu_arr['day_close_time'])) {
                    $status = 2;
                    $count_down = $qishu_arr['day_start_time'] - $time;
                    $count_down = $count_down < 0 ? $count_down + 86400 : $count_down;
                }
                //取出玩法PID
                $wanfa_ids = explode(',', $row['wanfa_id']);
                $wanfa = $this->ettm_classic_wanfa_db->row($wanfa_ids[0]);

                foreach ($digits_arr as $digits) {
                    $is_buy = 0;
                    $price = $free > 0 ? 0:$row['price'];
                    $numbers = '密';
                    if (isset($buys["$row[id]-$qishu_arr[next_qishu]-$digits"])) {
                        $buy = $buys["$row[id]-$qishu_arr[next_qishu]-$digits"];
                        $is_buy = 1;
                        $price = $buy['price'];
                        $numbers = $buy['numbers'];
                    }
                    
                    $data[$digits]['digits']      = $digits;
                    $data[$digits]['digits_name'] = prediction_buy_model::$digitsList[$digits].'预测';
                    $data[$digits]['data'][] = [
                        'prediction_id'   => (int)$row['id'],
                        'prediction_name' => $row['name'].prediction_buy_model::$digitsList[$digits],
                        'lottery_type_id' => (int)$lottery['lottery_type_id'],
                        'lottery_id'      => (int)$row['lottery_id'],
                        'lottery_name'    => $lottery['name'],
                        'pic_icon'        => $lottery['pic_icon'],
                        'status'          => $status,
                        'price'           => (float)$price,
                        'is_buy'          => $is_buy,
                        'numbers'         => $numbers,
                        'qishu'           => $qishu_arr['next_qishu'],
                        'count_down'      => $count_down,
                        'close_time'      => $qishu_arr['adjust'],
                        'wanfa_pid'       => (int)$wanfa['pid'],
                    ];
                }
            }
            $data = array_values($data);

            ApiHelp::response(1, 200, 'succsee', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/getBuyDetailMenu",
     *   summary="預測號詳情-選單",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="source",
     *                   description="來源 wap,pc,android,ios",
     *                   type="string",
     *                   example="ios",
     *                   enum={"wap","pc","android","ios"}
     *               ),
     *               required={"source"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getBuyDetailMenu()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'source', 'label' => '来源', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $source = $this->input->post('source', true);
            if (!array_key_exists($source, base_model::$sourceList)) {
                throw new Exception("参数错误", 401);
            }

            $result = $this->prediction_db->select('lottery_id,MAX(is_home) is_home')
                    ->group('lottery_id')->order(['is_home','asc'])->result();
            $data = [];
            foreach ($result as $row) {
                $lottery = $this->ettm_lottery_db->row($row['lottery_id']);
                $data[] = [
                    'lottery_type_id' => (int)$lottery['lottery_type_id'],
                    'lottery_id'      => (int)$row['lottery_id'],
                    'name'            => $lottery['name'],
                ];
            }

            ApiHelp::response(1, 200, 'succsee', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/getBuyDetail",
     *   summary="預測號詳情",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="source",
     *                   description="來源 wap,pc,android,ios",
     *                   type="string",
     *                   example="ios",
     *                   enum={"wap","pc","android","ios"}
     *               ),
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="int",
     *                   example="14",
     *               ),
     *               required={"source","lottery_id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getBuyDetail()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'source', 'label' => '来源', 'rules' => 'trim|required'],
                ['field' => 'lottery_id', 'label' => '彩种ID', 'rules' => 'trim|required|is_natural'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $source     = $this->input->post('source', true);
            $lottery_id = $this->input->post('lottery_id', true);
            if ($this->uid == 0) {
                throw new Exception("没有登录，请您先登录~~", 401);
            }
            if (!array_key_exists($source, base_model::$sourceList)) {
                throw new Exception("参数错误", 401);
            }

            $data = null;
            //取得該彩種最後購買預測號且救濟金尚未過期
            $join[] = [$this->table_.'prediction t1','t.prediction_id = t1.id','left'];
            $buy = $this->prediction_buy_db->select('t.*')->where([
                't.uid'           => $this->uid,
                't1.lottery_id'   => $lottery_id,
                't.expire_time >' => date('Y-m-d H:i:s'),
            ])->join($join)->order(['qishu','desc'])->result_one();
            if ($buy !== null) {
                $qishu = $buy['qishu'];
                $lottery = $this->ettm_lottery_db->row($lottery_id);
                //取得玩法
                $wanfa = $this->ettm_classic_wanfa_db->getListByLottery($lottery['lottery_type_id']);
                $wanfa = array_column($wanfa, 'name', 'id');
                //取得該期注單
                $betlist = $this->ettm_classic_bet_record_db->where([
                    't.uid'        => $this->uid,
                    't.lottery_id' => $lottery_id,
                    't.qishu'      => $qishu,
                ])->result();
                //取得購買預測
                $buylist = $this->prediction_buy_db->select('t.*,t1.ball,t1.name,t1.wanfa_id')->where([
                    't.uid'         => $this->uid,
                    't1.lottery_id' => $lottery_id,
                    't.qishu'       => $qishu,
                ])->join($join)->order(['t1.ball'=>'asc','t.status'=>'desc'])->result();
                //整理購買預測
                $buy_arr = [];
                foreach ($buylist as $row) {
                    $key = "$row[prediction_id]-$row[status]";
                    $buy_arr[$key]['prediction_id'] = $row['prediction_id'];
                    $buy_arr[$key]['ball'] = $row['ball'];
                    $buy_arr[$key]['name'] = $row['name'];
                    $buy_arr[$key]['wanfa_id'] = $row['wanfa_id'];
                    $buy_arr[$key]['status'] = $row['status'];
                    $buy_arr[$key]['buy'][$row['digits']] = [
                        'name'    => $row['name'].Prediction_buy_model::$digitsList[$row['digits']],
                        'numbers' => $row['numbers'],
                        'price'   => (int)$row['price'],
                    ];
                    if (!isset($buy_arr[$key]['numbers'])) {
                        $buy_arr[$key]['numbers'] = [];
                    }
                    $buy_arr[$key]['numbers'] = array_merge($buy_arr[$key]['numbers'], explode(',', $row['numbers']));
                }
                $buy_arr = array_values($buy_arr);
                //整理預測注單
                $is_show = false;
                foreach ($buy_arr as $key => $row) {
                    $row['bet'] = $row['bet_values'] = $row['pred_bet_ids'] = [];
                    $row['bet_status'] = 0;
                    foreach ($betlist as $bet) {
                        if (in_array($bet['wanfa_id'], explode(',', $row['wanfa_id']))) {
                            //退款注單
                            if ($row['status'] == -1 && in_array($bet['status'], [2,3])) {
                                //注單是否在預測號
                                $bet['is_pred'] = false;
                                if (in_array($bet['bet_values'], $row['numbers'])) {
                                    $bet['is_pred'] = true;
                                }
                                $row['bet'][] = $bet;
                                $row['bet_status'] = (int)$bet['status'];
                            }
                            //正常注單
                            if ($row['status'] >= 0 && !in_array($bet['status'], [2,3])) {
                                //注單是否在預測號
                                $bet['is_pred'] = false;
                                if (in_array($bet['bet_values'], $row['numbers'])) {
                                    $row['bet_values'][] = $bet['bet_values'];
                                    $row['pred_bet_ids'][] = $bet['id'];
                                    $bet['is_pred'] = true;
                                }
                                $row['bet'][] = $bet;
                                $row['bet_status'] = (int)$bet['status'];
                            }
                            $is_show = true;
                        }
                    }
                    $buy_arr[$key] = $row;
                }
                //有下注預測的玩法才顯示
                if ($is_show) {
                    //救濟金
                    $relief = $this->prediction_relief_db->where([
                        't.uid'      => $this->uid,
                        't.qishu'    => $buy['qishu'],
                        't.status <' => 3,
                    ])->result();
                    $relief = array_column($relief, 'relief', 'prediction_id');
                    //開獎號碼
                    $record = $this->ettm_lottery_record_db->where([
                        'lottery_id' => $lottery_id,
                        'qishu'      => $buy['qishu'],
                    ])->result_one();
                    $numbers = $record['numbers'] == '' ? []:explode(',', $record['numbers']);
                    $data['lottery_type_id'] = (int)$lottery['lottery_type_id'];
                    $data['lottery_name']    = $lottery['name'];
                    $data['pic_icon']        = $lottery['pic_icon'];
                    $data['qishu']           = $buy['qishu'];
                    $data['numbers']         = $this->ettm_lottery_record_db->padLeft($lottery['lottery_type_id'], $numbers);
                    $data['value_str']       = $this->ettm_lottery_record_db->getValue($lottery['lottery_type_id'], $record['numbers'], $record['lottery_time']);
                    $data['status']          = (int)$record['status'];
                    $data['total_c_value']   = 0;
                    $data['relief']          = 0;

                    $list = [];
                    foreach ($buy_arr as $row) {
                        //過濾掉無下注預測號的資料
                        if ($row['bet'] != []) {
                            $arr = [];
                            //Top
                            $arr['name'] = $row['name'];
                            //status[0:待開獎 1:已開獎 2:已退款 3:非常規退款]
                            $arr['status'] = (int)$record['status'];
                            //status_sub(status=1時才判斷)[0:中獎 1:中獎而沒下注 2:沒下注預測號 3:未中獎有救濟金]
                            $arr['status_sub'] = 0;
                            $arr['message'] = '';
                            $arr['relief'] = 0;
                            //購買預測
                            $arr['prediction'] = array_values($row['buy']);
                            //判斷是否退款
                            if (in_array($row['bet_status'], [2,3])) {
                                $arr['status'] = $row['bet_status'];
                                switch ($row['bet_status']) {
                                    case 2: $arr['message'] = '该订单未接收成功，已退还本金'; break;
                                    case 3: $arr['message'] = '由于您网路不稳定，订单未处理成功，已退还本金'; break;
                                }
                            } else {
                                if ($record['status'] == 1) { //已開獎
                                    $arr['status'] = $row['bet_status'];
                                    $is_prediction = $is_pred_bet = false;
                                    $number = '';
                                    //六合彩特殊處理
                                    if ($lottery['lottery_type_id'] == 8 && $row['ball'] < 0) {
                                        switch ($row['ball']) {
                                            case -1: //特肖
                                                $number = isset($data['value_str'][6]) ? $data['value_str'][6]:'';
                                                $is_prediction = in_array($number, $row['numbers']);
                                                $is_pred_bet = in_array($number, $row['bet_values']);
                                                break;
                                            case -2: //一肖
                                                for ($i=0;$i<6;$i++) {
                                                    $number = isset($data['value_str'][$i]) ? $data['value_str'][$i]:'';
                                                    $is_prediction = in_array($number, $row['numbers']);
                                                    $is_pred_bet = in_array($number, $row['bet_values']);
                                                }
                                                break;
                                        }
                                    } else {
                                        $number_arr = explode(',', $record['numbers']);
                                        $number = isset($number_arr[$row['ball']-1]) ? $number_arr[$row['ball']-1]:'';
                                        $is_prediction = in_array($number, $row['numbers']);
                                        $is_pred_bet = in_array($number, $row['bet_values']);
                                    }
                                    //判斷開獎號是否有在預測號裡
                                    if ($is_prediction) {
                                        //無下注中獎預測號
                                        if (!$is_pred_bet) {
                                            $arr['status_sub'] = 1;
                                            $arr['message'] = "$row[name]位预测号为中奖状态，无救济金发放";
                                        }
                                    } else {
                                        if ($row['bet_values'] == []) {
                                            $arr['status_sub'] = 2;
                                            $arr['message'] = '您未投注预测号，无救济金发放';
                                        } elseif (!$is_pred_bet) {
                                            //有下注預測無中獎 有救濟金
                                            $arr['status_sub'] = 3;
                                            $arr['relief'] = isset($relief[$row['prediction_id']]) ? $relief[$row['prediction_id']]:0;
                                        }
                                    }
                                }
                            }
                            //投注總額
                            $arr['bet_total'] = $arr['prediction_total'] = $arr['bet_number'] = $arr['total_c_value'] = 0;
                            foreach ($row['bet'] as $bet) {
                                $arr['bet_total'] += $bet['total_p_value'];
                                //預測投注總額
                                if (in_array($bet['id'], $row['pred_bet_ids'])) {
                                    $arr['prediction_total'] += $bet['total_p_value'];
                                }
                                //注單
                                $arr['bet'][$bet['wanfa_pid']]['name'] = $wanfa[$bet['wanfa_pid']];
                                $arr['bet'][$bet['wanfa_pid']]['list'][] = [
                                    'wanfa'         => $wanfa[$bet['wanfa_id']],
                                    'bet_values'    => $bet['bet_values_str'],
                                    'odds'          => $bet['odds'],
                                    'total_p_value' => $bet['total_p_value'],
                                    'c_value'       => $bet['c_value'],
                                    'create_time'   => $bet['create_time'],
                                    'is_prediction' => in_array($bet['id'], $row['pred_bet_ids']),
                                ];
                                //筆數
                                $arr['bet_number']++;
                                //退款不需統計
                                if (!in_array($row['bet_status'], [2,3])) {
                                    //中獎額
                                    $arr['total_c_value'] += $bet['c_value'];
                                    //中獎總計
                                    $data['total_c_value'] += $bet['c_value'];
                                }
                            }
                            //退款不需統計
                            if (!in_array($row['bet_status'], [2,3])) {
                                $data['relief'] += isset($relief[$row['prediction_id']]) ? $relief[$row['prediction_id']]:0;
                            }
                            $arr['bet'] = array_values($arr['bet']);
                            $list[] = $arr;
                        }
                    }
                    $data['list'] = $list;
                }
            }

            ApiHelp::response(1, 200, 'succsee', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/reliefList",
     *   summary="取得救濟金列表",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="status",
     *                   description="狀態 0：未提取(未激活+已激活)、2：已提取、3：已過期",
     *                   type="string",
     *                   example="0",
     *               ),
     *               required={"status"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function reliefList()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'status', 'label' => '状态', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $status = $this->input->post("status", true);
            if ($this->uid == 0) {
                throw new Exception("没有登录，请您先登录~~", 401);
            }
            //更新失效的救濟金
            $this->prediction_relief_db->updateInvalid();

            $where['t.uid'] = $this->uid;
            if ($status == 0) {
                $where['t.status'] = [0,1];
            } else {
                $where['t.status'] = $status;
            }
            $join[] = [$this->table_ . 'prediction t1', 't.prediction_id = t1.id', 'left'];
            $join[] = [$this->table_ . 'ettm_lottery t2', 't1.lottery_id = t2.id', 'left'];
            $result = $this->prediction_relief_db->select('t.*,t1.name,t2.name lottery_name')
                        ->join($join)->where($where)->result();
            $list = [];
            $inactivation = $activation = $need_reacharge = 0;
            foreach ($result as $row) {
                $payload = json_decode($row['payload'], true);
                $formula = (float)$row['bet_money']."*$payload[alms]%";
                $buys = [];
                foreach ($payload['digits'] as $digits => $price) {
                    $buys[] = $row['name'].prediction_buy_model::$digitsList[$digits]."看号：{$price}元";
                    $formula .= "+$price";
                }
                $list[] = [
                    'id'            => (int)$row['id'],
                    'lottery_name'  => $row['lottery_name'],
                    'qishu'         => (int)$row['qishu'],
                    'relief'        => (float)$row['relief'],
                    'bet_money'     => (float)$row['bet_money'],
                    'need_recharge' => $row['bet_money'] - $row['recharge'],
                    'bet_status'    => '未中奖',
                    'buys'          => $buys,
                    'formula'       => "($formula)",
                    'status'        => (int)$row['status'],
                    'status_str'    => prediction_relief_model::$statusList[$row['status']],
                    'expire_time'   => $row['expire_time'],
                    'create_time'   => $row['create_time'],
                ];
                if ($row['status'] == 0) {
                    $inactivation += $row['relief'];
                    $need_reacharge += $row['bet_money'] - $row['recharge'];
                }
                if ($row['status'] == 1) {
                    $activation += $row['relief'];
                }
            }

            ApiHelp::response(1, 200, "success", [
                'inactivation'   => $inactivation,   //未激活救濟金
                'activation'     => $activation,     //已激活救濟金
                'need_reacharge' => $need_reacharge, //需充值金額
                'list'           => $list,
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/reliefWithdraw",
     *   summary="提取救濟金",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="id",
     *                   description="救濟金ID(多筆逗號分隔)",
     *                   type="string",
     *                   example="1,2,3",
     *               ),
     *               required={"id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function reliefWithdraw()
    {
        try {
            $this->load->model('user_model', 'user_db');
            $this->load->model('code_amount_model', 'code_amount_db');
            $this->load->model('module_operator_model', 'module_operator_db');

            $this->form_validation->set_rules([
                ['field' => 'id', 'label' => '救济金ID', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $id = $this->input->post("id", true);
            if ($this->uid == 0) {
                throw new Exception("没有登录，请您先登录~~", 401);
            }
            //更新失效的救濟金
            $this->prediction_relief_db->updateInvalid();

            $where = [
                'uid'    => $this->uid,
                'status' => 1,
            ];
            if ($id != 0) {
                $where['ids'] = explode(',', $id);
            }
            $result = $this->prediction_relief_db->where($where)->result();
            if ($result == []) {
                throw new Exception('救济金需激活才能提取', 400);
            }

            $this->base_model->trans_start();
            foreach ($result as $row) {
                $order_sn = create_order_sn('RW');
                //更新救濟金狀態
                $this->prediction_relief_db->update([
                    'id'            => $row['id'],
                    'status'        => 2,
                    'withdraw_time' => date("Y-m-d H:i:s"),
                ]);
                $description = '提取救济金(充值'.user_model::$moneyTypeList[1].')';
                //帳變明細
                $this->user_db->addMoney($this->uid, $order_sn, 0, $row['relief'], $description, 0, 0, 0, 1);
                //充值打碼
                $this->code_amount_db->insert([
                    'uid'              => $this->uid,
                    'money_type'       => 1,
                    'type'             => 1,
                    'related_id'       => $row['id'],
                    'money'            => $row['relief'],
                    'multiple'         => $this->module[1]['param']['code_amount_multiple'],
                    'code_amount_need' => bcmul($row['relief'], $this->module[1]['param']['code_amount_multiple'], 2),
                    'description'      => "{$description}打码",
                ]);
            }
            $this->base_model->trans_complete();

            if ($this->base_model->trans_status() === false) {
                throw new Exception('提取失败', 400);
            }
            ApiHelp::response(1, 200, '提取成功');
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/moneyExchange",
     *   summary="轉換牛牛帳戶到主帳戶",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="amount",
     *                   description="額度",
     *                   type="int",
     *                   example="0",
     *               ),
     *               required={"id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function moneyExchange()
    {
        try {
            $this->load->model('user_model', 'user_db');
            $this->load->model('code_amount_model', 'code_amount_db');

            $this->form_validation->set_rules([
                ['field' => 'amount', 'label' => '额度', 'rules' => 'trim|required|is_natural'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $amount = $this->input->post("amount", true);
            if ($this->uid == 0) {
                throw new Exception("没有登录，请您先登录~~", 401);
            }

            //判斷轉換金額是否有誤
            $info = $this->user_db->row($this->uid);
            if ($amount == 0) {
                $amount = $info['money1'];
            }
            if ($info['money1'] == 0) {
                throw new Exception('牛牛帳戶無金額', 400);
            }
            if ($info['money1'] < $amount) {
                throw new Exception('超出最大可转出额度', 400);
            }
            //判断所需打码量是否为零
            $money_get = $this->code_amount_db->getNeedByUid($this->uid, 1);
            if ($money_get != 0) {
                throw new Exception("牛牛打码量不够，\n还需于牛牛游戏\n打码{$money_get}", 400);
            }
            $this->base_model->trans_start();
            //修改所有的打码量状态为1
            $this->code_amount_db->where([
                'uid'        => $this->uid,
                'money_type' => 1,
                'status'     => 0
            ])->update_where(['status'=>1]);

            $order_sn = create_order_sn('ME');
            //帳變明細
            $description = '提现'.user_model::$moneyTypeList[1].'(充值'.user_model::$moneyTypeList[0].')';
            $this->user_db->addMoney($this->uid, $order_sn, 1, -$amount, $description, 0, 0, 0, 1);
            $this->user_db->addMoney($this->uid, $order_sn, 0, $amount, $description, 0, 0, 0, 0);
            $this->base_model->trans_complete();

            if ($this->base_model->trans_status() === false) {
                throw new Exception('资金转换失败', 400);
            }
            ApiHelp::response(1, 200, '资金转换成功');
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/getPrediction",
     *   summary="取得熱門預測位置",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="int",
     *                   example="14",
     *               ),
     *               required={"lottery_id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getPrediction()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'lottery_id', 'label' => '彩種ID', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $lottery_id = $this->input->post("lottery_id", true);
            if ($this->uid == 0) {
                throw new Exception("没有登录，请您先登录~~", 401);
            }
            $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);

            $join[] = [$this->table_.'prediction t1', 't.prediction_id = t1.id', 'left'];
            $buy = $this->prediction_buy_db->where([
                'uid'   => $this->uid,
                'qishu' => $qishu_arr['next_qishu'],
            ])->join($join)->result();
            $buy = array_column($buy, 'prediction_id');

            $result = $this->prediction_db->where([
                'lottery_id' => $lottery_id,
            ])->order(['id','asc'])->result();
            $data = [];
            foreach ($result as $row) {
                $data[] = [
                    'prediction_id' => (int)$row['id'],
                    'name'          => $row['name'],
                    'is_buy'        => in_array($row['id'], $buy),
                ];
            }

            ApiHelp::response(1, 200, 'success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/prediction/getPredictionChart",
     *   summary="投注熱度圖表資料",
     *   tags={"Prediction"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="prediction_id",
     *                   description="熱門預測ID",
     *                   type="int",
     *                   example="1",
     *               ),
     *               required={"prediction_id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getPredictionChart()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'prediction_id', 'label' => '熱門預測ID', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $prediction_id = $this->input->post("prediction_id", true);
            if ($this->uid == 0) {
                throw new Exception("没有登录，请您先登录~~", 401);
            }
            $prediction = $this->prediction_db->row($prediction_id);
            $lottery = $this->ettm_lottery_db->row($prediction['lottery_id']);
            $qishu_arr = $this->qishu_model->getQishu(1, $lottery['id']);
            $data = [];
            //判斷該期是否有買號
            $join[] = [$this->table_.'prediction t1', 't.prediction_id = t1.id', 'left'];
            $buy = $this->prediction_buy_db->where([
                'prediction_id' => $prediction_id,
                'uid'           => $this->uid,
                'qishu'         => $qishu_arr['next_qishu'],
            ])->join($join)->result_one();
            if ($buy !== null) {
                $data['name'] = $prediction['name'];
                $data['list'] = $this->prediction_robot_bet_db->getRankData($prediction_id, $qishu_arr['next_qishu']);
                $setting = $this->prediction_robot_setting_db->where([
                    'operator_id' => $this->operator_id,
                    'lottery_id'  => $lottery['id'],
                ])->result_one();
                $data['axis_y'] = (float)$setting['axis_y'];
            }

            ApiHelp::response(1, 200, 'success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }
}
