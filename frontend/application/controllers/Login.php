<?php defined('BASEPATH') || exit('No direct script access allowed');

class Login extends CommonBase
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model', 'user_db');
        $this->load->model('agent_code_model', 'agent_code_db');
    }

    /**
     * @OA\Post(
     *   path="/login/code/{mini}",
     *   summary="生成驗證碼",
     *   tags={"Login"},
     *   @OA\Parameter(
     *     name="mini",
     *     in="path",
     *     required=true,
     *     example="0",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function code($mini = 0)
    {
        $config = [
            'width'    => 120,
            'height'   => 26,
            'fontSize' => 18,
            'font'     => APPPATH . "/fonts/font.ttf"
        ];
        if ($mini == 1) {
            $config['width'] = 60;
            $config['height'] = 20;
            $config['fontSize'] = 12;
        }
        $this->load->library("code", $config);
        $this->code->show();
    }

    /**
     * @OA\Post(
     *   path="/login/login",
     *   summary="用戶登入",
     *   tags={"Login"},
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
     *                   property="user_name",
     *                   description="用戶名",
     *                   type="string",
     *                   example="test123",
     *               ),
     *               @OA\Property(
     *                   property="user_pwd",
     *                   description="登入密碼",
     *                   type="string",
     *                   example="a123456",
     *               ),
     *               @OA\Property(
     *                   property="udid",
     *                   description="IOS使用的UDID",
     *                   type="string",
     *                   example="",
     *               ),
     *               @OA\Property(
     *                   property="img_code",
     *                   description="驗證碼",
     *                   type="string",
     *                   example="",
     *               ),
     *               required={"source","user_name","user_pwd"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function login()
    {
        try {
            $this->form_validation->set_message('min_length', '%s需要为6-12个英文字母和数字');
            $this->form_validation->set_message('max_length', '%s需要为6-12个英文字母和数字');
            $this->form_validation->set_rules($this->user_db->login_rules());
            if ($this->form_validation->run() === false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }
            $user_name = trim($this->input->post('user_name'));
            $user_pwd = trim($this->input->post('user_pwd'));
            $img_code = trim($this->input->post("img_code"));
            //IOS使用的UDID
            //$udid = trim($this->input->post("udid"));

            if ($this->source == 'pc') {
                if (!$this->check_code($img_code)) {
                    throw new Exception('验证码错误', 400);
                }
            }
            if (!in_array($this->source, ['wap', 'pc'])) {
                throw new Exception('未知源', 400);
            }

            $result = $this->user_db->userLogin([
                'user_name' => $user_name,
                'user_pwd'  => $user_pwd,
                //'udid' => $udid, //目前無用
            ]);

            if ($result['status'] == 0) {
                throw new Exception($result['message'], $result['code']);
            }
            $data = $result['data'];

            ApiHelp::response(1, $result['code'], $result['message'], [
                'user_name'   => $data['user_name'],
                'real_name'   => $data['real_name'],
                'money'       => $data['money'],
                'allow_agent' => $data['allow_agent'],
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/login/register",
     *   summary="用戶註冊",
     *   tags={"Login"},
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
     *                   property="user_name",
     *                   description="用戶名",
     *                   type="string",
     *                   example="test123",
     *               ),
     *               @OA\Property(
     *                   property="user_pwd",
     *                   description="登入密碼",
     *                   type="string",
     *                   example="a123456",
     *               ),
     *               @OA\Property(
     *                   property="security_pwd",
     *                   description="提現密碼",
     *                   type="string",
     *                   example="123456",
     *               ),
     *               @OA\Property(
     *                   property="real_name",
     *                   description="真實姓名",
     *                   type="string",
     *                   example="test",
     *               ),
     *               @OA\Property(
     *                   property="mobile",
     *                   description="電話",
     *                   type="string",
     *                   example="13123456789",
     *               ),
     *               @OA\Property(
     *                   property="code",
     *                   description="邀請碼",
     *                   type="string",
     *                   example="abcde",
     *               ),
     *               @OA\Property(
     *                   property="udid",
     *                   description="IOS使用的UDID",
     *                   type="string",
     *                   example="",
     *               ),
     *               @OA\Property(
     *                   property="img_code",
     *                   description="驗證碼",
     *                   type="string",
     *                   example="",
     *               ),
     *               required={"source","user_name","user_pwd","security_pwd","real_name","mobile"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function register()
    {
        try {
            $this->form_validation->set_rules($this->user_db->register_rules());
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }

            $user_name    = trim($this->input->post('user_name'));
            $user_pwd     = trim($this->input->post('user_pwd'));
            $security_pwd = trim($this->input->post('security_pwd'));
            $real_name    = trim($this->input->post('real_name'));
            $mobile       = trim($this->input->post('mobile'));
            $code         = strtolower(trim($this->input->post('code')));
            $img_code     = trim($this->input->get_post("img_code"));
            //$udid         = trim($this->input->get_post("udid"));

            if ($this->source == 'pc') {
                if (!$this->check_code($img_code)) {
                    throw new Exception('验证码错误', 400);
                }
            }
            if (!in_array($this->source, ['wap', 'pc'])) {
                throw new Exception('未知源', 400);
            }
            if (!((preg_match('/[0-9]/', $user_name) && preg_match('/[a-zA-Z]/', $user_name) && preg_match('/[a-zA-Z]/', $user_name[0])))) {
                throw new Exception('用户名只能以字母开头，数字与字母组合', 400);
            }

            if ($code == '') {
                $code = $this->site_config['default_agent_code'];
            }
            if ($code == '') {
                throw new Exception('邀请码不可为空', 405);
            }
    
            $agent_id = $agent_pid = 0;
            $agent_code = '';
            $is_player_code = false;
            //判断用戶邀請碼存是否存在
            $referrer_code = $this->user_db->where([
                't.referrer_code' => $code
            ])->result_one();
    
            if ($referrer_code !== null) {
                //用戶邀請碼存在則繼承玩家的代理ID
                $agent_id = $referrer_code['agent_id'];
                $agent_pid = $referrer_code['agent_pid'];
                $agent_code = $referrer_code['agent_code'];
                $is_player_code = true; //是否為玩家邀請碼
            } else {
                //判断代理邀請碼是否存在
                $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
                $agent_code_info = $this->agent_code_db->select('t.*,t1.agent_id')->where([
                    't.code' => $code
                ])->join($join)->result_one();
                if ($agent_code_info !== null) {
                    $agent_id = $agent_code_info['agent_id'];
                    $agent_pid = $agent_code_info['uid'];
                    $agent_code = $agent_code_info['code'];
                } else {
                    throw new Exception('若无邀请码请联系客服', 405);
                }
            }

            //註冊寫入
            $this->user_db->insert([
                'user_name'    => $user_name,
                'user_pwd'     => $user_pwd,
                'security_pwd' => $security_pwd,
                'real_name'    => $real_name,
                'mobile'       => $mobile,
                'agent_id'     => $agent_id,
                'agent_pid'    => $agent_pid,
                'agent_code'   => $agent_code,
                'referrer'     => $is_player_code ? $code:'',
            ]);

            //註冊完登入
            $result = $this->user_db->userLogin([
                'user_name' => $user_name,
                'user_pwd'  => $user_pwd,
                //'udid'      => $udid,
            ]);

            if ($result['status'] == 0) {
                throw new Exception($result['message'], $result['code']);
            }

            ApiHelp::response(1, $result['code'], $result['message'], $result['data']);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/login/logout",
     *   summary="用戶登出",
     *   tags={"Login"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function logout()
    {
        try {
            $this->input->set_cookie('cookie', '', 0);
            ApiHelp::response(1, 200, 'Success');
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 校驗驗證碼
     */
    private function check_code($code)
    {
        if (strtolower($this->session->userdata('verifyCode')) != strtolower($code)) {
            return false;
        } else {
            $this->session->set_userdata('verifyCode', '');
            return true;
        }
    }
}
