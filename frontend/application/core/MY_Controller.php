<?php defined('BASEPATH') || exit('No direct script access allowed');

abstract class CommonBase extends CI_Controller
{
    /**
     * 營運ID
     * @var integer
     */
    public $operator_id = 1;
    /**
     * 營運商資料
     * @var array
     */
    public $operator = [];
    /**
     * 啟用模組
     * @var array
     */
    public $module = [];
    /**
     * 網站參數
     * @var array
     */
    public $site_config = [];
    /**
     * 資料表前綴
     * @var string
     */
    public $table_ = 'bc_';
    /**
     * 用戶id
     * @var integer
     */
    public $uid = 0;
    /**
     * API id
     * @var integer
     */
    public $api_id = 0;
    /**
     * 來源 wap OR pc
     * @var string
     */
    public $source = 'wap';
    /**
     * 是否登入
     * @var integer
     */
    public $is_login = 1;

    /**
     * 建構子
     */
    public function __construct()
    {
        parent::__construct();
        $this->config->load();
        $this->load->driver('cache');
        $this->load->model('sysconfig_model', 'sysconfig_db');
        $this->load->model('api_log_model', 'api_log_db');
        $this->load->model('user_model', 'user_db');
        $this->load->model('operator_model', 'operator_db');
        $this->load->model('module_operator_model', 'module_operator_db');
        $this->load->model('ipmanage_model', 'ipmanage_db');
        $this->load->library('form_validation');

        Monolog::$folder = 'frontend'; //指定LOG資料夾
        $this->table_ = $this->db->table_pre; //資料表前綴

        //WebSocket不需要驗證
        if ($this->router->class == 'special' || $this->input->post('testing')) {
            //網站參數
            $this->site_config = $this->sysconfig_db->make_sysconfig();
        } else {
            //取得營運ID
            $operator = $this->operator_db->getOperator();
            if ($this->input->post('testing')) {
                $operator = $this->operator_db->row(1);
            }
            if ($operator === null) {
                ApiHelp::response(0, 400, '此网域尚未开通，请联络管理员!');
            }
            $this->operator = $operator;
            $this->operator_id = $operator['id'];
            $this->session->set_userdata('show_operator', [0,$operator['id']]);
            //網站參數
            $this->site_config = $this->sysconfig_db->make_sysconfig($operator['id']);
            //啟用模組
            $this->module = $this->module_operator_db->getEnable($operator['id']);
            $this->apiBefore(); //API LOG

            header("Access-Control-Allow-Origin: *");
            header("Content-type:application/json; charset=utf-8");
            $this->verify();
        }
        
        //取得來源
        if ($this->input->post('source') !== null) {
            $this->source = strtolower($this->input->post('source'));
        }
    }

    /**
     * 驗證
     */
    public function verify()
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            ApiHelp::response(0, 400, '必须用POST传递');
        }
        //網站關閉
        if ($this->site_config['website_close'] == 'Y') {
            ApiHelp::response(0, 999, '网站维护中');
        }
        //封鎖IP
        $count = $this->ipmanage_db->where([
            'ip' => $this->input->ip_address(),
        ])->count();
        if ($count > 0) {
            ApiHelp::response(0, 800, '来源IP异常，请联系客服');
        }
    }

    /**
     * API LOG 紀錄POST傳送參數
     */
    public function apiBefore()
    {
        $controllers = $this->router->class;
        $functions = $this->router->method;

        if (in_array($controllers, ['common','lottery','special'])) {
            return;
        }
        if (in_array($functions, ['loginStatus','code','test'])) {
            return;
        }
        $this->benchmark->mark('Start');
        $this->api_id = $this->api_log_db->insert([
            'url'         => site_url($this->uri->uri_string()),
            'controllers' => $controllers,
            'functions'   => $functions,
            'param'       => json_encode($this->input->post()),
            'return_str'  => '',
            'ip'          => $this->input->ip_address()
        ]);
    }

    /**
     * API LOG 紀錄回傳參數 (ApiHelp裡使用)
     * @param string $return_str
     */
    public function apiAfter($return_str)
    {
        if ($this->api_id != 0) {
            $this->benchmark->mark('End');
            $this->api_log_db->update([
                'id'         => $this->api_id,
                'uid'        => $this->uid,
                'return_str' => $return_str,
                'exec_time'  => $this->benchmark->elapsed_time('Start', 'End'),
            ]);
        }
    }

    /**
     * 判斷是否登入
     */
    public function is_userlogin()
    {
        $cookie = $this->input->cookie('cookie');
        $now = date('Y-m-d H:i:s');

        if (empty($cookie)) {
            return false;
        }
        $user = $this->user_db->where(['t.session'=>$cookie])->result_one();
        if ($user === null) {
            return false;
        }
        //在線飛踢 清空session
        if ($user['unlock_time'] > $now) {
            return false;
        }
        //登入迂時 清空session
        if ((time() - strtotime($user['last_active_time'])) > $this->site_config['login_expires']) {
            return false;
        }
        if ($user['status'] == 1) {
            return false;
        }
        $this->uid = $user['id'];
        return true;
    }
}

class MY_Controller extends CommonBase
{
    /**
     * 用户登录唯一标识符号
     * @var string
     */
    public $cookie = '';
    
    /**
     * 用户登录的更新时间
     * @var string
     */
    public $login_time = '';

    /**
     * 建構子
     */
    public function __construct()
    {
        parent::__construct();

        $this->check_login();
        $this->is_login = 1;
    }

    /**
     * 判斷是否登入
     */
    private function check_login()
    {
        $cookie = $this->input->cookie('cookie');
        $now = date('Y-m-d H:i:s');
        
        try {
            if (empty($cookie)) {
                throw new Exception('没有登录，请您先登录~~', 401);
            }
            $user = $this->user_db->where(['t.session'=>$cookie])->result_one();
            if ($user === null) {
                throw new Exception('您的账号在另一台设备登录~', 401);
            }
            //在線飛踢 清空session
            if ($user['unlock_time'] > $now) {
                throw new Exception('您的登录异常，请您重新登录', 401);
            }
            //登入迂時 清空session
            if ((time() - strtotime($user['last_active_time'])) > $this->site_config['login_expires']) {
                throw new Exception('您的帐号已登出，请重新登录', 401);
            }
            
            if ($user['status'] == 1) {
                ApiHelp::response(0, 452, "账户已经封锁,请联系客服");
            }
    
            $this->uid = $user['id'];
            $this->cookie = $user['session'];
            $this->login_time = $user['last_login_time'];
        } catch (Exception $e) {
            $this->input->set_cookie('cookie', '', 0);
            Monolog::writeLogs('login_status_user', 200, $e->getMessage());
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }
}
