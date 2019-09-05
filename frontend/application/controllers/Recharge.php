<?php defined('BASEPATH') || exit('No direct script access allowed');

class Recharge extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model', 'user_db');
        $this->load->model('recharge_online_model', 'recharge_online_db');
        $this->load->model('recharge_offline_model', 'recharge_offline_db');
        $this->load->model('recharge_order_model', 'recharge_order_db');
        $this->load->model('bank_model', 'bank_db');
    }

    /**
     * @OA\Post(
     *   path="/recharge/list",
     *   summary="充值列表",
     *   tags={"Recharge"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function list()
    {
        try {
            $user = $this->user_db->row($this->uid);

            $where['status'] = 1;
            $where['user_group_ids'] = $user['user_group_id'];
            $result = $this->recharge_offline_db->where($where)->order(['sort', 'asc'])->result();
            $data = ['online' => $this->site_config['recharge_online']];
            foreach ($result as $key => $row) {
                $min_money = (float)$row['min_money'];
                $max_money = (float)$row['max_money'];
                $data['offline'][] = [
                    'line_id'   => (int)$row['id'],
                    'channel'   => (int)$row['channel'],
                    'icon'      => recharge_offline_model::$channelIcon[$row['channel']],
                    'name'      => recharge_offline_model::$channelList[$row['channel']] . ($key + 1),
                    'tip'       => "限额{$min_money}-{$max_money}元",
                ];
            }

            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/recharge/offlineInfo",
     *   summary="線下充值資訊",
     *   tags={"Recharge"},
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
     *                   example="5",
     *               ),
     *               required={"source","line_id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function offlineInfo()
    {
        try {
            $line_id = $this->input->post('line_id');
            $row = $this->recharge_offline_db->row($line_id);
            if ($row === null) {
                throw new Exception("查无此充值方式", 400);
            }
            $min_money = (float)$row['min_money'];
            $max_money = (float)$row['max_money'];

            $bank_name = $bank_icon = '';
            if ($row['channel'] == 1 && $row['bank_id'] > 0) {
                $bank = $this->bank_db->row($row['bank_id']);
                $bank_icon = $this->site_config['image_path'] . $bank['image_url'];
                $bank_name = $bank['name'];
            }

            ApiHelp::response(1, 200, "success", [
                'account'      => $row['account'],
                'bank_icon'    => $bank_icon,
                'bank_name'    => $bank_name,
                'channel'      => (int)$row['channel'],
                'channel_name' => recharge_offline_model::$channelList[$row['channel']],
                'qrcode'       => $row['qrcode'] == '' ? '' : $this->site_config['image_path'] . $row['qrcode'],
                'tip'          => $this->site_config[recharge_offline_model::$channelTip[$row['channel']]],
                'user_name'    => $row['nickname'],
                'min_money'    => $min_money,
                'max_money'    => $max_money,
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/recharge/onlineList",
     *   summary="線上充值列表",
     *   tags={"Recharge"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function onlineList()
    {
        try {
            $user = $this->user_db->row($this->uid);

            $where['status'] = 1;
            $where['user_group_ids'] = $user['user_group_id'];
            $result = $this->recharge_online_db->where($where)->order(['sort','asc'])->result();
            $data = [];
            foreach ($result as $key => $row) {
                $data[] = [
                    'line_id' => (int)$row['id'],
                    'payment' => (int)$row['payment'],
                    'name'    => recharge_online_model::$paymentList[$row['payment']] . ($key + 1),
                    'logo'    => recharge_online_model::$paymentIcon[$row['payment']],
                    'money'   => array_map('intval', explode(',', $row['moneys'])),
                ];
            }

            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/recharge/onlineOrder",
     *   summary="線上充值訂單",
     *   tags={"Recharge"},
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
     *                   type="int",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="money",
     *                   description="面额",
     *                   type="int",
     *                   example="100",
     *               ),
     *               required={"source","line_id","money"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function onlineOrder()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'line_id', 'label' => '充值源ID', 'rules' => 'trim|required'],
                ['field' => 'money', 'label' => '面额', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }

            $line_id = $this->input->post('line_id');
            $money = $this->input->post('money');
            $line = $this->recharge_online_db->row($line_id);
            if ($line === null) {
                throw new Exception("支付渠道不存在", 300);
            }
            if (!in_array($money, explode(',', $line['moneys']))) {
                throw new Exception("不存在的充值面额", 300);
            }

            $order_sn = create_order_sn('R');
            $order_id = $this->recharge_order_db->insert([
                'uid'      => $this->uid,
                'type'     => 1,
                'order_sn' => $order_sn,
                'money'    => $money,
                'line_id'  => $line_id,
                'status'   => 3,
            ]);
            
            Monolog::writeLogs("OnlineOrder", Monolog::NOTICE, "Order ID: $order_id");
            $data = [
                "pay_memberid"    => $line['m_num'],
                "pay_orderid"     => $order_sn,
                "pay_amount"      => $money,
                "pay_applydate"   => date("Y-m-d H:i:s"),
                "pay_bankcode"    => 902, //支付方式
                "pay_notifyurl"   => site_url('recharge/notifyUrl'),
                "pay_callbackurl" => site_url('recharge/callbackUrl'),
            ];
            //生成md5sign
            ksort($data);
            $md5str = "";
            foreach ($data as $key => $val) {
                $md5str .= "$key=$val&";
            }
            $data["pay_md5sign"] = strtoupper(md5($md5str."key=$line[secret_key]"));
            $data['pay_attach'] = $order_id; //訂單備註說明
            $data['pay_productname'] = '902充值'; //商品名称

            Monolog::writeLogs("OnlineOrder", Monolog::NOTICE, $data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $line['pay_url']);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            $return = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($return, true);
            
            if (isset($result['status']) && $result['status'] == 'success' && $result['pay_url'] != '' && strpos($result['pay_url'], 'form') <= 0) {
                ApiHelp::response(1, 200, 'success', ["jump_url" => $result['pay_url']]);
            } else {
                Monolog::writeLogs("OnlineOrder", Monolog::NOTICE, ["pay_fail", $result]);
                ApiHelp::response(0, 500, 'fail');
            }
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/recharge/notifyUrl",
     *   summary="線上充值異步通知",
     *   tags={"Recharge"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="memberid",
     *                   description="商戶ID",
     *                   type="string",
     *                   example="10023"
     *               ),
     *               @OA\Property(
     *                   property="orderid",
     *                   description="充值源ID",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="amount",
     *                   description="交易金額",
     *                   type="string",
     *                   example="100",
     *               ),
     *               @OA\Property(
     *                   property="datetime",
     *                   description="交易時間",
     *                   type="string",
     *                   example="2019-05-16 11:00:00",
     *               ),
     *               @OA\Property(
     *                   property="transaction_id",
     *                   description="支付流水號",
     *                   type="string",
     *                   example="123456789",
     *               ),
     *               @OA\Property(
     *                   property="returncode",
     *                   description="回傳代碼",
     *                   type="string",
     *                   example="00",
     *               ),
     *               @OA\Property(
     *                   property="sign",
     *                   description="MD5驗證碼",
     *                   type="string",
     *                   example="EFDGEDFDSERWEGDGDS67DDFD4",
     *               ),
     *               required={"memberid","orderid","amount","datetime","transaction_id","returncode","sign"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function notifyUrl()
    {
        try {
            Monolog::writeLogs("NotifyUrl", Monolog::NOTICE, $this->input->post());
            $order_sn = $this->input->post('orderid');
            if (empty($order_sn)) {
                throw new Exception("订单编号错误", 500);
            }
            //查询secret_key
            $order = $this->recharge_order_db->where(['order_sn'=>$order_sn])->result_one();
            $line = $this->recharge_online_db->row($order['line_id']);
            if ($line === null) {
                throw new Exception("签名商户secret_key不存在", 500);
            }
            //返回字段
            $data = [
                "memberid"       => $this->input->post('memberid'),       //商户ID
                "orderid"        => $this->input->post('orderid'),        //订单号
                "amount"         => $this->input->post('amount'),         //交易金额
                "datetime"       => $this->input->post('datetime'),       //交易时间
                "transaction_id" => $this->input->post('transaction_id'), //支付流水号
                "returncode"     => $this->input->post('returncode'),
            ];
            //生成md5sign
            ksort($data);
            $md5str = "";
            foreach ($data as $key => $val) {
                $md5str .= "$key=$val&";
            }
            $sign = strtoupper(md5($md5str."key=$line[secret_key]"));
            if ($this->input->post('sign') != $sign) {
                throw new Exception("验证码:$sign 不相符", 500);
            }
            //支付成功
            if ($this->input->post('returncode') == '00') {
                $this->base_model->trans_start();
                $this->recharge_order_db->update([
                    'id'         => $order['id'],
                    'check_time' => date('Y-m-d H:i:s'),
                    'check_by'   => 'notifyUrl',
                    'status'     => 1,
                ]);
                $this->recharge_order_db->orderSuccess($order['id'], 1);
                $this->base_model->trans_complete();
                if ($this->base_model->trans_status() !== false) {
                    Monolog::writeLogs("NotifyUrl", Monolog::NOTICE, "支付成功");
                    echo 'ok';
                    return;
                }
            }
            //支付失敗
            $this->recharge_order_db->update([
                'id'     => $order['id'],
                'status' => 2,
            ]);
            throw new Exception("支付失败", 500);
        } catch (Exception $e) {
            Monolog::writeLogs("NotifyUrl", Monolog::NOTICE, [$e->getCode(),$e->getMessage()]);
            echo 'fail';
        }
    }

    public function callbackUrl()
    {
        echo 'ok';
    }
}
