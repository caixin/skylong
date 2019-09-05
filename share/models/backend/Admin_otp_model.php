<?php defined('BASEPATH') || exit('No direct script access allowed');

class Admin_otp_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'admin_otp';
        $this->_key = 'id';
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'       => '编号',
        'mobile'   => '手机号码',
        'password' => '密码',
    ];
}
