<?php defined('BASEPATH') || exit('No direct script access allowed');

abstract class AdminCommon extends CI_Controller
{
    public $table_ = 'bc_'; // 表的前缀
    public $site_config = []; // 站点基本信息
    public $allow_url = [];
    public $allow_operator = [];
    public $cur_url = ''; // 当前访问的url 格式 news/index
    public $title = '首页';
    public $breadcrumb = [];
    public $version = '1.0';
    public $per_page = 20;
    public $is_login = 0;
    public $operator = [];
    public $operator_id = 0; //指定運營商
    public $source = 'pc'; //來源 後台新增帳號時需要

    /**
     * 初始化
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();
        $this->config->load();
        $this->load->driver('cache');
        $this->load->model('user_model', 'user_db');
        $this->load->model('operator_model', 'operator_db');
        $this->load->model('backend/admin_nav_model', 'admin_nav_db');
        $this->load->model('backend/admin_role_model', 'admin_role_db');
        $this->load->model('backend/admin_action_log_model', 'admin_action_log_db');
        $this->load->model('sysconfig_model', 'sysconfig_db');
        $this->init();
    }

    /**
     * 基本設置
     *
     * @return null
     */
    public function init()
    {
        $this->table_ = $this->db->table_pre;
        $this->cur_url = "{$this->router->class}/{$this->router->method}";
        $this->site_config = $this->sysconfig_db->make_sysconfig(); //获取站点基本信息
        //讀取Version
        $json_string = file_get_contents("../config/version.json"); //從檔案中讀取資料到PHP變數
        $version = json_decode($json_string, true); //把JSON字串轉成PHP陣列
        $this->version = $version['version'];
    }
}

abstract class LoginCommon extends AdminCommon
{
    public $navList = [];

    /**
     * 初始化
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();
        $this->is_login = 1;
        $this->auth->is_login();
        $this->initLogin();
        $this->per_page = $this->session->userdata('per_page') !== null ? $this->session->userdata('per_page') : 20;
    }

    /**
     * 初始化登入
     *
     * @return null
     */
    private function initLogin()
    {
        $this->setPermition();
        $result = $this->admin_nav_db->allNav();
        //導航路徑
        $urls = array_column($result, 'id', 'url');
        $navid = isset($urls[$this->cur_url]) ? $urls[$this->cur_url] : 0;
        $this->breadcrumb = $this->admin_nav_db->getBreadcrumb($result, $navid);
        //內頁Title
        $urls = array_column($result, 'name', 'url');
        $this->title = isset($urls[$this->cur_url]) ? $urls[$this->cur_url] : $this->title;
        //導航樹狀
        $this->navList = $this->admin_nav_db->treeNav($result);
    }

    /**
     * 设置当期登录的用户 有哪些操作权限
     *
     * @return null
     */
    public function setPermition()
    {
        //導航權限
        $role = $this->admin_role_db->row($this->session->userdata('roleid'));
        $permition = $role !== null && $role['allow_nav'] != '' ? json_decode($role['allow_nav'], true) : [];
        $permition = array_merge_recursive($permition, $this->config->item('no_need_perm'));
        $this->allow_url = array_unique($permition);
        //運營商權限
        $operator = $this->operator_db->getList();
        if ($this->session->userdata('roleid') == 1) {
            $this->allow_operator = $operator;
        } else {
            $allow_operator = $role !== null && $role['allow_operator'] != '' ? explode(',', $role['allow_operator']) : [];
            foreach ($allow_operator as $val) {
                $this->allow_operator[$val] = $operator[$val];
            }
        }
        if ($this->session->userdata('show_operator') === null) {
            $this->session->set_userdata('show_operator', array_merge([0], array_keys($this->allow_operator)));
        }
    }
}

/**
 * 让CI继承自己的类库
 * ######################################
 * 这个类里面写权限代码 和登录判断代码 ,
 * ###################################
 */
class MY_Controller extends LoginCommon
{
    /**
     * 初始化
     *
     * @return null
     */
    public function __construct()
    {
        parent::__construct();
        $this->checkUserOnlyoneLogin();
        $this->checkUrlExists();

        if ($this->session->userdata('roleid') != 1) {
            //不是超级管理员
            $this->_permition();
        }
    }

    /**
     * 驗證錯誤 跳轉登入頁
     * $message 跳轉訊息
     *
     * @return null
     */
    public function errorRedirect($message)
    {
        $this->session->set_flashdata('message', $message);
        $this->session->set_userdata('refer', $this->uri->ruri_string());
        redirect('login/index');
    }

    /**
     * 检测用户，必须一个人登录 且 檢測COOKIE
     *
     * @return null
     */
    public function checkUserOnlyoneLogin()
    {
        $id = $this->session->userdata('id');
        $session_id = $this->session->userdata('auth_session');
        if ($session_id == '') {
            $this->errorRedirect('session无效，请重新登录');
        }

        $exists = $this->auth->getDataBySessionIdUserid($session_id, $id);
        if (!$exists) {
            $this->errorRedirect("session无效，请重新登录");
        }

        //驗證Cookie劫持
        if ($exists['agent'] != $_SERVER['HTTP_USER_AGENT'] || $exists['ip'] != $this->input->ip_address()) {
            $this->errorRedirect("登入訊息无效，请重新登录");
        }
    }

    /**
     * 检测url是不是存在的
     *
     * @return null
     */
    public function checkUrlExists()
    {
        $url = $this->admin_nav_db->getAllUrl();
        $url = array_merge_recursive($url, $this->config->item('no_need_perm'));

        if (!in_array($this->cur_url, $url)) {
            show_error("当前的url没有设置,或者已经禁用,请联系管理员设置！", '500', '信息提示');
        }
    }

    /**
     * 验证是否有访问的权限
     *
     * @return null
     */
    private function _permition()
    {
        if (!in_array($this->cur_url, $this->allow_url)) {
            $this->errorRedirect("对不起没权限执行此操作，请联系管理员：{$this->config->item('web_admin_email')}");
        }
    }
}
