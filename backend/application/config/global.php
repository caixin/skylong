<?php defined('BASEPATH') || exit('No direct script access allowed');

/*
| 后台cookie的周期
| 默认1个小时的时间
 */
$config['cookie_expire'] = 60 * 60 * 60;

/*
| 后台cookie的路径
 */
$config['cookie_path'] = "/";

/*
| 后台cookie的域
 */
$config['cookie_domain'] = "";

/*
| 后台加密的key
 */
$config['s_key'] = "cp42889859";

/*
| 角色缓存文件的路径
 */
$config['role_cache'] = APPPATH . "/cache/role_cache/"; //备注要确保role_cache文件夹存在

$config['nav_cache'] = APPPATH . "/cache/nav_cache/"; //备注要确保nav_cache文件夹存在
/*
| 没有权限的时候返回的一个code值 写小于0的值
 */
$config['no_permition'] = -8;

/*
| 不需要进行权限认证的控制器里面的方法（但是需要进行登录才能使用的）
| 注意每个控制器后面需要加上/
 */
$config['no_need_perm'] = array(
    'home/index',
);

/*
| 是否保存日志到数据库里面
| 默认是true
 */
$config['is_write_log_to_database'] = true;
/*
| 是否在后台登录的时候有验证码
| 默认是true
 */
$config['yzm_open'] = true;

//设置后台显示字段的有效期 ， 目前设置为10年
$config['cache_field_expire'] = 10 * 365 * 24 * 60 * 60;
