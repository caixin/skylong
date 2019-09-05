<?php defined('BASEPATH') || exit('No direct script access allowed');

class Agent extends MY_Controller
{
    public $user = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model', 'user_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_type_model', 'ettm_lottery_type_db');
        $this->load->model('agent_code_model', 'agent_code_db');
        $this->load->model('agent_code_detail_model', 'agent_code_detail_db');
        $this->load->model('agent_return_point_model', 'agent_return_point_db');
        $this->load->model('recharge_order_model', 'recharge_order_db');
        $this->load->model('user_withdraw_model', 'user_withdraw_db');
        $this->load->model('user_money_log_model', 'user_money_log_db');
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');

        $this->user = $this->user_db->getProfile($this->uid);
        if ($this->user['allow_agent'] == 0) {
            ApiHelp::response(0, 401, '无代理权限');
        }
    }

    /**
     * @OA\Post(
     *   path="/agent/getLotteryReturnPoint",
     *   summary="取得該玩家各彩種可設定返點值",
     *   tags={"Agent"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getLotteryReturnPoint()
    {
        try {
            $lottery_type = $this->ettm_lottery_type_db->result();
            $lottery_type = array_column($lottery_type, null, 'id');
            $lottery = $this->ettm_lottery_db->result();
            $return_point = $this->agent_code_db->getReturnPoint($this->uid);
            $wanfa = $this->ettm_classic_wanfa_detail_db->where([
                'preview >' => 0,
            ])->result();
            $odds1 = $odds2 = [];
            foreach ($wanfa as $row) {
                if ($row['preview'] == 1) {
                    $odds1[$row['lottery_type_id']] = [
                        'odds'          => $row['odds'],
                        'line_a_profit' => $row['line_a_profit'],
                    ];
                }
                if ($row['preview'] == 2) {
                    $odds2[$row['lottery_type_id']] = [
                        'odds'          => $row['odds'],
                        'line_a_profit' => $row['line_a_profit'],
                    ];
                }
            }

            $data = [];
            foreach ($lottery as $row) {
                $line_a_profit1 = isset($odds1[$row['lottery_type_id']]['line_a_profit']) ? $odds1[$row['lottery_type_id']]['line_a_profit']:0;
                $line_a_profit2 = isset($odds2[$row['lottery_type_id']]['line_a_profit']) ? $odds2[$row['lottery_type_id']]['line_a_profit']:0;
                $data[$row['lottery_type_id']]['name'] = $lottery_type[$row['lottery_type_id']]['name'];
                $data[$row['lottery_type_id']]['icon'] = $lottery_type[$row['lottery_type_id']]['pic_icon2'];
                $data[$row['lottery_type_id']]['lottery'][] = [
                    'id'                 => (int)$row['id'],
                    'name'               => $row['name'],
                    'max_return_point'   => (float)$this->site_config['agent_return_point_max'],
                    'allow_return_point' => (float)$return_point[$row['id']],
                    'line_a_profit1'     => (float)bcmul($line_a_profit1, $this->operator['classic_adjustment'], 3),
                    'line_a_profit2'     => (float)bcmul($line_a_profit2, $this->operator['classic_adjustment'], 3),
                    'odds1'              => isset($odds1[$row['lottery_type_id']]['odds']) ? (float)$odds1[$row['lottery_type_id']]['odds']:0,
                    'odds2'              => isset($odds2[$row['lottery_type_id']]['odds']) ? (float)$odds2[$row['lottery_type_id']]['odds']:0,
                ];
            }

            ApiHelp::response(1, 200, "success", array_values($data));
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/agent/create",
     *   summary="新增邀請碼",
     *   tags={"Agent"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="type",
     *                   description="類型 1:代理 2:玩家",
     *                   type="int",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="return_point",
     *                   description="代理返點",
     *                   type="json",
     *                   example="{1:1,2:1,3:1}",
     *               ),
     *               required={"type","return_point"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function create()
    {
        try {
            $this->form_validation->set_rules($this->agent_code_db->rules());
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $type = $this->input->post('type');
            $return_point = json_decode(stripslashes($this->input->post('return_point')), true);
            if ($return_point === null) {
                throw new Exception('JSON格式错误!', 500);
            }
            //允許的返點
            $allow = $this->agent_code_db->getReturnPoint($this->uid);
            foreach ($allow as $key => $val) {
                if (!isset($return_point[$key])) {
                    throw new Exception('缺少彩种设定值', 401);
                }
                if ($return_point[$key] > $val) {
                    throw new Exception('设定的返点超出最大值', 402);
                }
            }

            $this->agent_code_db->insert([
                'uid'          => $this->uid,
                'type'         => $type,
                'level'        => $this->user['level'] + 1,
                'return_point' => $return_point,
            ]);

            ApiHelp::response(1, 200, "success");
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/agent/getCodeList",
     *   summary="取得邀請碼列表",
     *   tags={"Agent"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="type",
     *                   description="類型 1:代理 2:玩家",
     *                   type="int",
     *                   example="1",
     *               ),
     *               required={"type"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getCodeList()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'type', 'label' => '类型', 'rules' => "trim|required"],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $type = $this->input->post('type');
            $result = $this->agent_code_db->where([
                'uid'  => $this->uid,
                'type' => $type,
            ])->result();
            $data = [];
            foreach ($result as $row) {
                $count = $this->user_db->where(['agent_code' => $row['code']])->count();
                $data[] = [
                    'code'        => $row['code'],
                    'count'       => $count,
                    'create_time' => date('Y-m-d', strtotime($row['create_time'])),
                ];
            }

            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/agent/delete",
     *   summary="刪除邀請碼",
     *   tags={"Agent"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="del_code",
     *                   description="邀請碼",
     *                   type="int",
     *                   example="aaaaa",
     *               ),
     *               required={"type"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function delete()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'del_code', 'label' => '邀请码', 'rules' => "trim|required"],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $del_code = $this->input->post('del_code');
            $count = $this->user_db->where(['agent_code' => $del_code])->count();
            if ($count > 0) {
                throw new Exception('已有下级注册，不可删除!', 401);
            }

            $this->agent_code_db->delete($del_code);

            ApiHelp::response(1, 200, "success");
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/agent/getUseCodeList",
     *   summary="查詢下級資料",
     *   tags={"Agent"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="uid",
     *                   description="玩家ID(非必要) 預設回傳自己下級",
     *                   type="int",
     *                   example="1",
     *               )
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getUseCodeList()
    {
        try {
            $uid = $this->input->post('uid');
            if ($uid === null || $uid == '') {
                $uid = $this->uid;
            }
            $result = $this->agent_code_db->getSubDataByUID($uid);
            $data = [];
            foreach ($result as $row) {
                $rs = $this->agent_code_db->getSubDataByUID($row['uid']);
                $data[] = [
                    'uid'             => (int)$row['uid'],
                    'user_name'       => $row['user_name'],
                    'code'            => $row['code'],
                    'type'            => agent_code_model::$typeList[$row['type']],
                    'sub_count'       => count($rs),
                    'online'          => strtotime($row['last_active_time']) + $this->site_config['online_status'] > time() ? 1 : 0,
                    'last_login_time' => date('Y-m-d', strtotime($row['last_login_time'])),
                    'money'           => sprintf("%.2f", $row['money']),
                ];
            }

            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/agent/getReturnPointList",
     *   summary="查詢返點明細",
     *   tags={"Agent"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="uid",
     *                   description="玩家ID(非必要) 預設回傳自己資料",
     *                   type="int",
     *                   example="1",
     *               )
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getReturnPointList()
    {
        try {
            $uid = $this->input->post('uid');
            if ($uid === null || $uid == '') {
                $uid = $this->uid;
            }

            $result = $this->agent_return_point_db->getReturnPointDetail($uid);
            $data = [];
            foreach ($result as $row) {
                $data[] = [
                    'uid'          => (int)$row['uid'],
                    'user_name'    => $row['user_name'],
                    'lottery_name' => $row['lottery_name'],
                    'amount'       => sprintf("%.2f", $row['amount']),
                ];
            }

            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/agent/getReturnPointSetting",
     *   summary="查詢返點設定",
     *   tags={"Agent"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="code",
     *                   description="邀請碼",
     *                   type="string",
     *                   example="aaaaa",
     *               ),
     *               required={"code"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getReturnPointSetting()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'code', 'label' => '邀请码', 'rules' => "trim|required"],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $code = $this->input->post('code');
            //允許的返點
            $agent_code = $this->agent_code_db->row($code);
            $allow = $this->agent_code_db->getReturnPoint($agent_code['uid']);
            $result = $this->agent_code_detail_db->getCodeSetting($code);
            $data = [];
            foreach ($result as $row) {
                $data[] = [
                    'lottery_name' => $row['lottery_name'],
                    'return_point' => (float)$row['return_point'],
                    'allow'        => (float)bcsub($allow[$row['lottery_id']], $row['return_point'], 3),
                ];
            }

            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/agent/getCodeNote",
     *   summary="查詢邀請碼備註",
     *   tags={"Agent"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="code",
     *                   description="邀請碼",
     *                   type="string",
     *                   example="aaaaa",
     *               ),
     *               required={"code"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getCodeNote()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'code', 'label' => '邀请码', 'rules' => "trim|required"],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $code = $this->input->post('code');
            $row = $this->agent_code_db->select('note')->where(['code' => $code])->result_one();

            ApiHelp::response(1, 200, 'success', $row);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/agent/editCodeNote",
     *   summary="修改邀請碼備註",
     *   tags={"Agent"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="code",
     *                   description="邀請碼",
     *                   type="string",
     *                   example="aaaaa",
     *               ),
     *               @OA\Property(
     *                   property="note",
     *                   description="備註",
     *                   type="string",
     *                   example="test",
     *               ),
     *               required={"code","note"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function editCodeNote()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'code', 'label' => '邀请码', 'rules' => "trim|required"],
                ['field' => 'note', 'label' => '备注', 'rules' => "trim"],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $code = $this->input->post('code');
            $note = $this->input->post('note');

            $this->agent_code_db->update([
                'code' => $code,
                'note' => $note,
            ]);

            ApiHelp::response(1, 200, 'success');
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/agent/getAgentReport",
     *   summary="代理報表資訊",
     *   tags={"Agent"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="day_type",
     *                   description="日期类型",
     *                   type="int",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="uid",
     *                   description="玩家ID(非必要) 預設回傳自己資料",
     *                   type="int",
     *                   example="",
     *               ),
     *               required={"day_type"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getAgentReport()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'day_type', 'label' => '日期类型', 'rules' => "trim|required"],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $day_type = $this->input->post('day_type');
            if (!in_array($day_type, [1, 2, 3, 4, 5])) {
                throw new Exception('参数错误', 401);
            }
            $uid = $this->input->post('uid');
            if ($uid === null || $uid == '') {
                $uid = $this->uid;
            }

            switch ($day_type) {
                case 1:
                    $starttime = date('Y-m-d') . ' 00:00:00';
                    $endtime = date('Y-m-d') . ' 23:59:59';
                    break;
                case 2:
                    $starttime = date('Y-m-d', time() - 86400) . ' 00:00:00';
                    $endtime = date('Y-m-d', time() - 86400) . ' 23:59:59';
                    break;
                case 3:
                    $starttime = date('Y-m-d', time() - 86400 * 2) . ' 00:00:00';
                    $endtime = date('Y-m-d', time() - 86400 * 2) . ' 23:59:59';
                    break;
                case 4:
                    $starttime = date('Y-m-d', time() - 86400 * 7) . ' 00:00:00';
                    $endtime = date('Y-m-d') . ' 23:59:59';
                    break;
                case 5:
                    $starttime = date('Y-m-d', time() - 86400 * 30) . ' 00:00:00';
                    $endtime = date('Y-m-d') . ' 23:59:59';
                    break;
            }

            $data = [];
            //直接下級人數
            $count = $this->user_db->where(['agent_pid' => $uid])->count();
            $data[] = ['name' => '直接下级人数', 'count' => (int)$count];
            //所有下級人數
            $uids = $this->user_db->getAgentAllSubUID($uid);
            $data[] = ['name' => '所有下级人数', 'count' => count($uids) - 1];
            //註冊人數
            $uids2 = $this->user_db->getAgentAllSubUID($uid, [], $starttime, $endtime);
            $data[] = ['name' => '注册人数', 'count' => count($uids2) - 1];
            //代理返點
            $return_point = $this->agent_return_point_db->getReturnPointUser($starttime, $endtime);
            $count = isset($return_point[$uid]) ? $return_point[$uid] : 0;
            $data[] = ['name' => '代理返点(元)', 'count' => (float)$count];
            //團隊返點
            $count = 0;
            foreach ($return_point as $key => $val) {
                if (in_array($key, $uids)) {
                    $count = bcadd($count, $val, 2);
                }
            }
            $data[] = ['name' => '团队返点(元)', 'count' => (float)$count];
            //團隊充值
            $recharge = $this->recharge_order_db->getRechargeUser($starttime, $endtime);
            $count = 0;
            foreach ($recharge as $key => $val) {
                if (in_array($key, $uids)) {
                    $count = bcadd($count, $val, 2);
                }
            }
            $data[] = ['name' => '团队充值(元)', 'count' => (float)$count];
            //團隊提現
            $withdraw = $this->user_withdraw_db->getWithdrawUser($starttime, $endtime);
            $count = 0;
            foreach ($withdraw as $key => $val) {
                if (in_array($key, $uids)) {
                    $count = bcadd($count, $val, 2);
                }
            }
            $data[] = ['name' => '团队提现(元)', 'count' => (float)$count];
            //團隊充值優惠
            $flow = $this->user_money_log_db->getMoneyLogUser($starttime, $endtime, [7]);
            $count = 0;
            foreach ($flow as $key => $val) {
                if (in_array($key, $uids)) {
                    $count = bcadd($count, $val, 2);
                }
            }
            $data[] = ['name' => '团队充值优惠(元)', 'count' => (float)$count];
            //團隊活動禮金
            $flow = $this->user_money_log_db->getMoneyLogUser($starttime, $endtime, [8]);
            $count = 0;
            foreach ($flow as $key => $val) {
                if (in_array($key, $uids)) {
                    $count = bcadd($count, $val, 2);
                }
            }
            $data[] = ['name' => '团队活动礼金(元)', 'count' => (float)$count];
            //團隊餘額
            $row = $this->user_db->escape(false)->select('IFNULL(SUM(money),0) money')->where([
                't.type' => 0,
                'id'     => $uids,
            ])->result_one();
            $data[] = ['name' => '团队余额(元)', 'count' => (float)$row['money']];
            //首充人數
            $count = 0;
            foreach ($recharge as $key => $val) {
                if (in_array($key, $uids)) {
                    $count++;
                }
            }
            $data[] = ['name' => '首充人数', 'count' => (float)$count];
            //投注人數
            $bet = $this->user_money_log_db->getMoneyLogUser($starttime, $endtime, [5, 10, 18]);
            $count = 0;
            foreach ($bet as $key => $val) {
                if (in_array($key, $uids)) {
                    $count++;
                }
            }
            $data[] = ['name' => '投注人数', 'count' => (int)$count];
            //中獎金額
            $win = $this->user_money_log_db->getMoneyLogUser($starttime, $endtime, [6, 11, 12, 16]);
            $count = 0;
            foreach ($win as $key => $val) {
                if (in_array($key, $uids)) {
                    $count = bcadd($count, $val, 2);
                }
            }
            $data[] = ['name' => '中奖金额(元)', 'count' => (float)$count];
            //投注金額
            $count = 0;
            foreach ($bet as $key => $val) {
                if (in_array($key, $uids)) {
                    $count = bcadd($count, $val, 2);
                }
            }
            $data[] = ['name' => '投注金额(元)', 'count' => (float)abs($count)];
            //彩票返水
            $win = $this->user_money_log_db->getMoneyLogUser($starttime, $endtime, [4]);
            $count = 0;
            foreach ($win as $key => $val) {
                if (in_array($key, $uids)) {
                    $count = bcadd($count, $val, 2);
                }
            }
            $data[] = ['name' => '彩票退水(元)', 'count' => (float)$count];
            //彩票盈虧
            $flowadd = $this->user_money_log_db->getMoneyLogUser($starttime, $endtime, [2]); //人工加款
            $flowdec = $this->user_money_log_db->getMoneyLogUser($starttime, $endtime, [3]); //人工減款
            $count = 0;
            foreach ($uids as $val) {
                $user_recharge = isset($recharge[$val]) ? $recharge[$val] : 0;
                $user_withdraw = isset($withdraw[$val]) ? $withdraw[$val] : 0;
                $user_flowadd = isset($flowadd[$val]) ? $flowadd[$val] : 0;
                $user_flowdec = isset($flowdec[$val]) ? $flowdec[$val] : 0;
                $count += sprintf("%.2f", (abs($user_withdraw) + abs($user_flowdec)) - (abs($user_recharge) + abs($user_flowadd)));
            }
            $data[] = ['name' => '彩票盈亏(元)', 'count' => (float)$count];
            //遊戲人數
            $data[] = ['name' => '游戏人数', 'count' => 0];
            //遊戲盈虧
            $data[] = ['name' => '游戏盈亏(元)', 'count' => 0];

            ApiHelp::response(1, 200, 'success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }
}
