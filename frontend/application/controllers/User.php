<?php defined('BASEPATH') || exit('No direct script access allowed');

class User extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model', 'user_db');
        $this->load->model('user_bank_model', 'user_bank_db');
        $this->load->model('user_group_model', 'user_group_db');
        $this->load->model('user_money_log_model', 'user_money_log_db');
        $this->load->model('user_login_log_model', 'user_login_log_db');
        $this->load->model('user_withdraw_model', 'user_withdraw_db');
        $this->load->model('recharge_order_model', 'recharge_order_db');
        $this->load->model('recharge_offline_model', 'recharge_offline_db');
        $this->load->model('code_amount_model', 'code_amount_db');
        $this->load->model('apps_model', 'apps_db');
    }

    /**
     * @OA\Post(
     *   path="/user/profile",
     *   summary="取得用戶訊息",
     *   tags={"User"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function profile()
    {
        try {
            $user = $this->user_db->getProfile($this->uid);
            if ($user === null) {
                throw new Exception('服务器异常', 401);
            }

            ApiHelp::response(1, 200, "success", $user);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/updateUser",
     *   summary="更新用戶訊息",
     *   tags={"User"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="show_bet_hot",
     *                   description="投注熱度(0=不顯示,1=顯示)",
     *                   type="int",
     *                   example="0",
     *               ),
     *               required={"show_bet_hot"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function updateUser()
    {
        try {
            $user = $this->user_db->row($this->uid);
            if ($user === null) {
                throw new Exception('服务器异常', 401);
            }
            $show_bet_hot = $this->input->post('show_bet_hot', true);
            if ($show_bet_hot == 1) {
                $mode = $user['mode'] & 8 ? $user['mode']-8:$user['mode'];
            } else {
                $mode = $user['mode'] & 8 ? $user['mode']:$user['mode']+8;
            }
            $this->user_db->update([
                'id'   => $this->uid,
                'mode' => $mode,
            ]);
            ApiHelp::response(1, 200, "success");
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/getBankInfo",
     *   summary="用戶銀行卡資訊",
     *   tags={"User"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getBankInfo()
    {
        try {
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $row = $this->user_bank_db->select('t.*,t1.money')->join($join)
                ->where(['uid' => $this->uid])->result_one();

            ApiHelp::response(1, 200, "success", [
                'bank_name'      => $row['bank_name'],
                'bank_account'   => replace_middle($row['bank_account'], 6),
                'bank_address'   => $row['bank_address'],
                'bank_real_name' => $row['bank_real_name'],
                'money'          => (float)$row['money'],
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/bindBank",
     *   summary="綁定銀行卡",
     *   tags={"User"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="bank_name",
     *                   description="出款銀行",
     *                   type="string",
     *                   example="測試銀行",
     *               ),
     *               @OA\Property(
     *                   property="bank_account",
     *                   description="出款帳號",
     *                   type="string",
     *                   example="123321123321123",
     *               ),
     *               @OA\Property(
     *                   property="bank_address",
     *                   description="開戶地址",
     *                   type="string",
     *                   example="測試銀行開戶",
     *               ),
     *               @OA\Property(
     *                   property="security_pwd",
     *                   description="提現密碼",
     *                   type="string",
     *                   example="123456",
     *               ),
     *               required={"bank_name","bank_account","bank_address","security_pwd"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function bindBank()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'bank_name', 'label' => '出款银行', 'rules' => 'trim|required'],
                ['field' => 'bank_account', 'label' => '出款帐号', 'rules' => 'trim|required|min_length[16]|max_length[19]'],
                ['field' => 'bank_address', 'label' => '开户地址', 'rules' => 'trim|required'],
                ['field' => 'security_pwd', 'label' => '提现密码', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $bank_name = $this->input->post('bank_name');
            $bank_account = $this->input->post('bank_account');
            $bank_address = $this->input->post('bank_address');
            $security_pwd = $this->input->post('security_pwd');

            $user = $this->user_db->row($this->uid);
            if (userPwdEncode($security_pwd) != $user['security_pwd']) {
                throw new Exception('Password Error', 301);
            }
            $bank = $this->user_bank_db->where(['uid' => $this->uid])->result_one();
            if ($bank !== null) {
                throw new Exception('已绑定银行卡', 400);
            }

            $this->user_bank_db->insert([
                'uid'            => $this->uid,
                'bank_real_name' => $user['real_name'],
                'bank_name'      => $bank_name,
                'bank_account'   => $bank_account,
                'bank_address'   => $bank_address,
            ]);

            ApiHelp::response(1, 200, "success");
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/changePassword",
     *   summary="修改密碼",
     *   tags={"User"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="type",
     *                   description="修改類型 0:登入密碼 1:提現密碼",
     *                   type="string",
     *                   example="0",
     *               ),
     *               @OA\Property(
     *                   property="oldpwd",
     *                   description="原密碼",
     *                   type="string",
     *                   example="a123456",
     *               ),
     *               @OA\Property(
     *                   property="newpwd",
     *                   description="新密碼",
     *                   type="string",
     *                   example="a123456",
     *               ),
     *               required={"type","oldpwd","newpwd"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function changePassword()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'type', 'label' => '修改类型', 'rules' => 'trim|required'],
                ['field' => 'oldpwd', 'label' => '原密码', 'rules' => 'trim|required|min_length[6]|max_length[12]'],
                ['field' => 'newpwd', 'label' => '新密码', 'rules' => 'trim|required|min_length[6]|max_length[12]'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $type = $this->input->post('type');
            $oldpwd = $this->input->post('oldpwd');
            $newpwd = $this->input->post('newpwd');
            $field = $type == 0 ? 'user_pwd' : 'security_pwd';

            $user = $this->user_db->row($this->uid);
            if (userPwdEncode($oldpwd) != $user[$field]) {
                throw new Exception('原密码输入错误', 301);
            }

            $this->user_db->update([
                'id'   => $this->uid,
                $field => $newpwd,
            ]);

            ApiHelp::response(1, 200, "success");
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/loginStatus",
     *   summary="保持登入狀態",
     *   tags={"User"},
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
     *               required={"source"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function loginStatus()
    {
        try {
            $user = $this->user_db->row($this->uid);
            //更新最後活躍時間
            $this->user_db->update([
                'id'               => $this->uid,
                'last_active_time' => date('Y-m-d H:i:s'),
            ]);
            //判斷登入跨日在線-寫入登入資訊
            $login = $this->user_login_log_db->where(['uid'=>$this->uid])->order(['create_time','desc'])->result_one();
            if (date('Y-m-d') != date('Y-m-d', strtotime($login['create_time']))) {
                $this->user_login_log_db->insert([
                    'uid'        => $this->uid,
                    'source_url' => base_url($_SERVER['REQUEST_URI']),
                    'domain_url' => base_url(),
                ]);
            }

            ApiHelp::response(1, 200, "success", [
                'cookie'    => $this->cookie,
                'user_name' => $user['user_name'],
                'real_name' => $user['real_name'],
                'money'     => sprintf("%.2f", $user['money']),
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/userRecharge",
     *   summary="用戶充值",
     *   tags={"User"},
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
     *                   property="line_id",
     *                   description="充值源ID",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="bank_name",
     *                   description="銀行名稱",
     *                   type="string",
     *                   example="測試銀行",
     *               ),
     *               @OA\Property(
     *                   property="username",
     *                   description="姓名",
     *                   type="string",
     *                   example="test",
     *               ),
     *               @OA\Property(
     *                   property="money",
     *                   description="充值金額",
     *                   type="string",
     *                   example="500",
     *               ),
     *               @OA\Property(
     *                   property="pay_type",
     *                   description="充值方法",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="account",
     *                   description="充值帳號",
     *                   type="string",
     *                   example="111",
     *               ),
     *               required={"source","line_id","money"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function userRecharge()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'money', 'label' => '充值金额', 'rules' => 'trim|required'],
                ['field' => 'line_id', 'label' => 'line_id', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }

            $user = $this->user_db->row($this->uid);
            if ($user['status'] == 2) {
                throw new Exception("账户已经冻结,请联系客服", 452);
            }

            $line_id   = $this->input->post("line_id");
            $money     = $this->input->post("money");
            $bank_name = $this->input->post("bank_name") !== null ? $this->input->post("bank_name") : '';
            $username  = $this->input->post("username") !== null ? $this->input->post("username") : '';
            $pay_type  = $this->input->post("pay_type") !== null ? $this->input->post("pay_type") : '';
            $account   = $this->input->post("account") !== null ? $this->input->post("account") : '';
            $recharge  = $this->recharge_offline_db->row($line_id);

            //判斷充值通道是否開啟
            if ($recharge['status'] == 0) {
                throw new Exception("充值通道已关闭，请联系客服", 500);
            }

            //判斷金額是否在區間內
            if ($money < $recharge['min_money'] || $money > $recharge['max_money']) {
                throw new Exception("单笔充值额度必须在$recharge[min_money]元-$recharge[max_money]元之间", 505);
            }

            //當日充值金額
            $date = date('Y-m-d');
            $data = $this->recharge_order_db->escape(false)->where([
                't.uid' => $this->uid,
                'status' => 1,
                'create_time1' => $date,
                'create_time2' => $date,
            ])->select('SUM(t.money) money')->result_one();
            $day_money = $data['money'] === null ? 0 : $data['money'];
            //判斷是否超出單日最大限額
            if ($day_money + $money > $recharge['day_max_money']) {
                throw new Exception("已超出单日最大限额$recharge[day_max_money]，请选择其他充值渠道", 505);
            }

            //白名單或試玩帳號不可充值
            if ($user['type'] != 0) {
                throw new Exception("非正常用户不能充值", 502);
            }

            $this->base_model->trans_start();
            //寫入充值紀錄
            $order_sn = create_order_sn('L');
            $order_id = $this->recharge_order_db->insert([
                'uid'                    => $this->uid,
                'type'                   => 2,
                'order_sn'               => $order_sn,
                'money'                  => $money,
                'line_id'                => $line_id,
                'offline_channel'        => $recharge['channel'],
                'offline_account'        => $account,
                'offline_user_bank_name' => $bank_name,
                'offline_user_realname'  => $username,
                'offline_pay_type'       => $pay_type,
                'today_total'            => $day_money,
            ]);
            $this->base_model->trans_complete();

            ApiHelp::response(1, 200, "充值申请提交成功", [
                'order_id'    => $order_id,
                'channel'     => $recharge['channel'],
                'account'     => $account,
                'bank_name'   => $bank_name,
                'username'    => $username,
                'money'       => $money,
                'order_sn'    => $order_sn,
                'create_time' => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/getUserRechargeLog",
     *   summary="用戶充值紀錄",
     *   tags={"User"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="start_time",
     *                   description="起始時間",
     *                   type="string",
     *                   example="2019-07-01",
     *               ),
     *               @OA\Property(
     *                   property="end_time",
     *                   description="結束時間",
     *                   type="string",
     *                   example="2019-07-31",
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
     *               required={"page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getUserRechargeLog()
    {
        try {
            $start_time = $this->input->post("start_time");
            $end_time = $this->input->post("end_time");
            $page = $this->input->post("page");
            $page = $page === null ? 1 : $page;
            $per_page = $this->input->post("per_page");
            $per_page = $per_page === null ? 10 : $per_page;
            $offset = ($page - 1) * $per_page;

            $where['uid'] = $this->uid;
            if ($start_time !== null) {
                $where['create_time1'] = $start_time;
            }
            if ($end_time !== null) {
                $where['create_time2'] = $end_time;
            }

            $total = $this->recharge_order_db->where($where)->count();
            $result = $this->recharge_order_db->where($where)
                ->order(['create_time', 'desc'])
                ->limit([$offset, $per_page])
                ->result();
            $list = [];
            foreach ($result as $row) {
                $list[] = [
                    'type'        => recharge_order_model::$typeList[$row['type']],
                    'order_sn'    => $row['order_sn'],
                    'money'       => sprintf("%.2f", $row['money']),
                    'status'      => $row['status'],
                    'status_str'  => recharge_order_model::$statusList[$row['status']],
                    'create_time' => $row['create_time'],
                    'remark'      => $row['check_remarks'],
                ];
            }

            ApiHelp::response(1, 200, "success", [
                'page'  => $page,
                'total' => $total,
                'list'  => $list,
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/userWithdraw",
     *   summary="用戶提現",
     *   tags={"User"},
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
     *                   property="money",
     *                   description="提現金額",
     *                   type="string",
     *                   example="100",
     *               ),
     *               @OA\Property(
     *                   property="security_pwd",
     *                   description="提現密碼",
     *                   type="string",
     *                   example="123456",
     *               ),
     *               required={"source","money","security_pwd"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function userWithdraw()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'money', 'label' => '提现金额', 'rules' => 'trim|required'],
                ['field' => 'security_pwd', 'label' => '提现密码', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }

            $money = $this->input->post("money");
            $security_pwd = $this->input->post("security_pwd");
            //驗證提現密碼
            $user = $this->user_db->row($this->uid);
            if (userPwdEncode($security_pwd) != $user['security_pwd']) {
                throw new Exception('取款密码错误', 404);
            }
            if ($user['status'] == 2) {
                throw new Exception("账户已经冻结,请联系客服", 452);
            }
            //判斷金額是否在用戶分層區間內
            $group = $this->user_group_db->row($user['user_group_id']);
            if ($money < $group['min_extract_money'] || $money > $group['max_extract_money']) {
                throw new Exception("您的单次提现额度必须在$group[min_extract_money]元-$group[max_extract_money]元之间", 505);
            }
            //判斷是否綁定銀行卡
            $bank = $this->user_bank_db->where(['uid' => $this->uid])->result_one();
            if ($bank === null) {
                throw new Exception("请先绑定银行卡", 501);
            }
            //判斷餘額是否足夠
            if ($user['money'] < $money) {
                throw new Exception("余额不足", 502);
            }
            //白名單或試玩帳號不可提現
            if ($user['type'] != 0) {
                throw new Exception("非正常用户不能提现", 502);
            }
            //判斷所需打碼量是否為0
            $need = $this->code_amount_db->getNeedByUid($this->uid);
            if ($need > 0) {
                throw new Exception("打码量不够，还需打码{$need}", 502);
            }

            $this->base_model->trans_start();
            //寫入提現紀錄
            $order_sn = create_order_sn('TX');
            $this->user_withdraw_db->insert([
                'uid'           => $this->uid,
                'order_sn'      => $order_sn,
                'money'         => $money,
                'bank_realname' => $user['real_name'],
                'bank_name'     => $bank['bank_name'],
                'bank_account'  => $bank['bank_account'],
            ]);
            //帳變明細
            $this->user_db->addMoney($this->uid, $order_sn, 1, $money * -1, '提现');
            $this->base_model->trans_complete();

            ApiHelp::response(1, 200, "提现成功，请等待审核");
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/getUserWithdrawLog",
     *   summary="用戶提現紀錄",
     *   tags={"User"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="start_time",
     *                   description="起始時間",
     *                   type="string",
     *                   example="2019-07-01",
     *               ),
     *               @OA\Property(
     *                   property="end_time",
     *                   description="結束時間",
     *                   type="string",
     *                   example="2019-07-31",
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
     *               required={"page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getUserWithdrawLog()
    {
        try {
            $start_time = $this->input->post("start_time");
            $end_time = $this->input->post("end_time");
            $page = $this->input->post("page");
            $page = $page === null ? 1 : $page;
            $per_page = $this->input->post("per_page");
            $per_page = $per_page === null ? 10 : $per_page;
            $offset = ($page - 1) * $per_page;

            $where['uid'] = $this->uid;
            if ($start_time !== null) {
                $where['create_time1'] = $start_time;
            }
            if ($end_time !== null) {
                $where['create_time2'] = $end_time;
            }

            $total = $this->user_withdraw_db->where($where)->count();
            $result = $this->user_withdraw_db->where($where)
                ->order(['create_time', 'desc'])
                ->limit([$offset, $per_page])
                ->result();
            $list = [];
            foreach ($result as $row) {
                $list[] = [
                    'order_sn'    => $row['order_sn'],
                    'money'       => sprintf("%.2f", $row['money']),
                    'status'      => $row['status'],
                    'status_str'  => user_withdraw_model::$statusList[$row['status']],
                    'remark'      => $row['check_remarks'],
                    'create_time' => $row['create_time'],
                ];
            }

            ApiHelp::response(1, 200, "success", [
                'page'  => $page,
                'total' => $total,
                'list'  => $list,
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/getUserMoneyLog",
     *   summary="用戶帳變明細",
     *   tags={"User"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="start_time",
     *                   description="起始時間",
     *                   type="string",
     *                   example="2019-01-01",
     *               ),
     *               @OA\Property(
     *                   property="end_time",
     *                   description="結束時間",
     *                   type="string",
     *                   example="2019-12-31",
     *               ),
     *               @OA\Property(
     *                   property="type",
     *                   description="收支類型 0:全部 1:收入 2:支出",
     *                   type="string",
     *                   example="0",
     *               ),
     *               @OA\Property(
     *                   property="money_type",
     *                   description="貨幣類型 0:現金帳戶 1:特色棋牌帳戶",
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
     *               required={"page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getUserMoneyLog()
    {
        try {
            $start_time = $this->input->post("start_time");
            $end_time = $this->input->post("end_time");
            $type = $this->input->post("type");
            $money_type = $this->input->post("money_type");
            $money_type = $money_type === null ? 0 : $money_type;
            $page = $this->input->post("page");
            $page = $page === null ? 1 : $page;
            $per_page = $this->input->post("per_page");
            $per_page = $per_page === null ? 20 : $per_page;
            $offset = ($page - 1) * $per_page;

            $where['uid'] = $this->uid;
            if ($start_time !== null) {
                $where['create_time1'] = $start_time;
            }
            if ($end_time !== null) {
                $where['create_time2'] = $end_time;
            }
            if ($type !== null) {
                switch ($type) {
                    case 1:
                        $where['money_add >'] = 0;
                        break;
                    case 2:
                        $where['money_add <'] = 0;
                        break;
                }
            }
            $where['money_type'] = $money_type;

            $total = $this->user_money_log_db->where($where)->count();
            $result = $this->user_money_log_db->where($where)
                ->order(['create_time', 'desc'])
                ->limit([$offset, $per_page])
                ->result();
            $list = [];
            foreach ($result as $row) {
                $list[] = [
                    'change_money' => sprintf("%.2f", $row['money_add']),
                    'description'  => $row['description'],
                    'create_time'  => $row['create_time'],
                ];
            }

            ApiHelp::response(1, 200, "success", [
                'page'  => $page,
                'total' => $total,
                'list'  => $list,
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/getReportForm",
     *   summary="用戶結算報表",
     *   tags={"User"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="category",
     *                   description="玩法類別",
     *                   type="int",
     *                   example="0",
     *               ),
     *               @OA\Property(
     *                   property="start_time",
     *                   description="起始時間",
     *                   type="string",
     *                   example="2019-04-25",
     *               ),
     *               @OA\Property(
     *                   property="end_time",
     *                   description="結束時間",
     *                   type="string",
     *                   example="2019-04-30",
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
     *               required={"category","start_time","end_time","page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getReportForm()
    {
        try {
            $this->load->model('daily_user_report_model', 'daily_user_report_db');
            $this->form_validation->set_rules([
                ['field' => 'category', 'label' => '类别', 'rules' => 'trim|required'],
                ['field' => 'start_time', 'label' => '起始时间', 'rules' => 'trim|required'],
                ['field' => 'end_time', 'label' => '结束时间', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $category = $this->input->post("category");
            $start_time = $this->input->post("start_time");
            $end_time = $this->input->post("end_time");
            $page = $this->input->post("page");
            $page = $page === null ? 1 : $page;
            $per_page = $this->input->post("per_page");
            $per_page = $per_page === null ? 20 : $per_page;
            $offset = ($page - 1) * $per_page;

            $where['uid'] = $this->uid;
            $where['day_time1'] = $start_time;
            $where['day_time2'] = $end_time;
            if ($category > 0) {
                $where['category'] = $category;
            }
            //計算總筆數
            $total = 0;
            for ($i = strtotime($end_time); $i >= strtotime($start_time); $i -= 86400) {
                $total++;
            }
            //預設值
            $list = [];
            $p = 0;
            for ($i = strtotime($end_time); $i >= strtotime($start_time); $i -= 86400) {
                if ($p >= $offset && $p < $offset + $per_page) {
                    $list[date('Y-m-d', $i)] = [
                        'day_time'   => date('Y-m-d', $i),
                        'bet_number' => 0,
                        'bet_money'  => '0.00',
                        'bet_eff'    => '0.00',
                        'profit'     => '0.00',
                    ];
                }
                $p++;
            }

            $result = $this->daily_user_report_db->escape(false)->where($where)
                ->select('t.day_time,SUM(t.bet_number) bet_number,SUM(t.bet_money) bet_money,SUM(t.bet_eff) bet_eff,SUM(t.c_value-t.bet_money) profit')
                ->group('day_time')->order(['day_time', 'desc'])
                ->result();
            foreach ($result as $row) {
                if (isset($list[$row['day_time']])) {
                    $list[$row['day_time']] = [
                        'day_time'   => $row['day_time'],
                        'bet_number' => (int)$row['bet_number'],
                        'bet_money'  => sprintf("%.2f", $row['bet_money']),
                        'bet_eff'    => sprintf("%.2f", $row['bet_eff']),
                        'profit'     => $row['profit'] > 0 ? '+' . $row['profit'] : $row['profit'],
                    ];
                }
            }

            $footer = $this->daily_user_report_db->escape(false)->where($where)
                ->select('SUM(t.bet_number) bet_number,SUM(t.bet_money) bet_money,SUM(t.bet_eff) bet_eff,IFNULL(SUM(t.c_value-t.bet_money),0) profit')
                ->result_one();
            $footer['title']      = '合计';
            $footer['bet_number'] = (int)$footer['bet_number'];
            $footer['bet_money']  = sprintf("%.2f", $footer['bet_money']);
            $footer['bet_eff']    = sprintf("%.2f", $footer['bet_eff']);
            $footer['profit']     = $footer['profit'] > 0 ? '+' . $footer['profit'] : $footer['profit'];

            ApiHelp::response(1, 200, "success", [
                'page'   => (int)$page,
                'total'  => $total,
                'list'   => array_values($list),
                'footer' => $footer,
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/getVipInfoIosStatus",
     *   summary="取得目前VIP狀態(IOS)",
     *   tags={"User"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getVipInfoIosStatus()
    {
        try {
            $row = $this->recharge_order_db->escape(false)->select('SUM(money) money')->where([
                'uid'      => $this->uid,
                'status'     => 1,
            ])->group('t.uid')->result_one();
            $money = $row['money'] === null ? 0 : $row['money'];
            $user = $this->user_db->select('operator_id, vip_info_ios')->where([
                'id'      => $this->uid
            ])->result_one();
            $vip_info_ios = json_decode($user['vip_info_ios'], true);
            $operator_id = $user['operator_id'];

            $status = 0;
            if ($money > 0) {
                $status = 1;
            }
            if (isset($vip_info_ios[0]['udid']) && $vip_info_ios[0]['udid'] != '') {
                $status = 2;
            }
            if (isset($vip_info_ios[0]['binding']) && $vip_info_ios[0]['binding'] == 1) {
                $status = 3;
            }
            if (isset($vip_info_ios[0]['prompt']) && $vip_info_ios[0]['prompt'] == 1) {
                $status = 4;
            }
            $data = [
                'status' => $status,
                'apps_id' => "",
                'name' => "",
                'jump_url' => "",
                'download_url' => ""
            ];
            if ($status > 2) {
                $apps = $this->apps_db->where([
                    'operator_id' => $operator_id,
                    'type' => 2,
                    'is_vip' => 1,
                    'status' => 1
                ])->result_one();
                if (empty($apps)) {
                    throw new Exception('安装包不存在', 300);
                }
                $data = [
                    'status' => $status,
                    'apps_id' => $apps['id'],
                    'name' => $apps['name'],
                    'jump_url' => $apps['jump_url'],
                    'download_url' => $apps['download_url']
                ];
            }
            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/changeVipInfoIosPrompt",
     *   summary="更新提示窗狀態(IOS)",
     *   tags={"User"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function changeVipInfoIosPrompt()
    {
        try {
            $user = $this->user_db->select('vip_info_ios')->where([
                'id'      => $this->uid
            ])->result_one();
            $vip_info_ios = json_decode($user['vip_info_ios'], true);
            if (empty($vip_info_ios)) {
                throw new Exception('请先申请VIP', 300);
            }
            if ($vip_info_ios[0]['binding'] != 1) {
                throw new Exception('尚未审核完成', 300);
            }
            $vip_info_ios[0]['prompt'] = 1;
            $this->user_db->update([
                'id'           => $this->uid,
                'vip_info_ios' => json_encode($vip_info_ios, JSON_UNESCAPED_UNICODE),
            ]);
            ApiHelp::response(1, 200, "success");
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/user/getMobileConfigIos",
     *   summary="產出取得用戶UDID的XML(IOS)",
     *   tags={"User"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getMobileConfigIos()
    {
        try {
            $token = auth_code($this->uid, "ENCODE");

            $xml_contents = '<?xml version="1.0" encoding="UTF-8"?>
            <!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
            <plist version="1.0">
                <dict>
                    <key>PayloadContent</key>
                    <dict>
                        <key>URL</key>
                        <string>https://' . $this->input->server('SERVER_NAME') . '/common/writeUserVipInfoIosUdid?uid=' . $this->uid . '&token=' . $token . '</string>
                        <key>DeviceAttributes</key>
                        <array>
                            <string>UDID</string>
                            <string>IMEI</string>
                            <string>ICCID</string>
                            <string>VERSION</string>
                            <string>PRODUCT</string>
                        </array>
                    </dict>
                    <key>PayloadOrganization</key>
                    <string>' . $this->input->server('SERVER_NAME') . '</string>
                    <key>PayloadDisplayName</key>
                    <string>查询设备UDID</string>
                    <key>PayloadVersion</key>
                    <integer>1</integer>
                    <key>PayloadUUID</key>
                    <string>' . strrev($this->input->server('SERVER_NAME')) . '</string>
                    <key>PayloadIdentifier</key>
                    <string>' . $this->input->server('SERVER_NAME') . '.profile-service</string>
                    <key>PayloadDescription</key>
                    <string>本文件仅用来获取设备ID</string>
                    <key>PayloadType</key>
                    <string>Profile Service</string>
                </dict>
            </plist>';
            header('Content-type: text/xml');
            header('Content-Disposition: attachment; filename="udid.mobileconfig"');
            echo $xml_contents;
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }
}
