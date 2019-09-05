<?php defined('BASEPATH') || exit('No direct script access allowed');

class Login extends AdminCommon
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('backend/admin_model', 'admin_db');
        $this->load->model('backend/admin_otp_model', 'admin_otp_db');
    }

    public function index()
    {
        $this->load->library('form_validation');
        $this->load->helper('form');
        $this->load->helper('cookie');

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            //表單驗證
            $this->form_validation->set_rules('mobile', '手机号码', 'required');
            $this->form_validation->set_rules('password', '密码', 'required');
            if ($this->form_validation->run() == true) {
                $mobile = $this->input->post('mobile');
                $password = $this->input->post('password');
                $otp = $this->input->post('otp');

                $user = $this->admin_db->where(['mobile' => $mobile])->result_one();
                if (empty($user)) {
                    $this->session->set_flashdata('message', '此帐号不存在');
                    redirect('login/index');
                }
                if (isset($user['status']) && $user['status'] == 0) {
                    $this->session->set_flashdata('message', '此帐号已关闭');
                    redirect('login/index');
                }

                $status = $this->auth->login($mobile, $password, $otp);

                if ($status == 'success') {
                    if ($this->session->userdata('refer')) {
                        $refer = $this->session->userdata('refer');
                        $this->session->unset_userdata('refer');
                        redirect($refer);
                    } else {
                        redirect('home/index');
                    }
                } else {
                    $this->session->set_flashdata('message', $status);
                    redirect('login/index');
                }

                return;
            }
        }

        $this->load->view('login');
    }

    public function logout()
    {
        $this->auth->logout();

        $this->session->set_flashdata('message', '您已成功登出!');
        redirect('login/index');
    }

    public function produceOtp()
    {
        if ($this->input->is_ajax_request()) {
            $mobile = $this->input->post('mobile');
            $user = $this->admin_db->where(['mobile' => $mobile])->result_one();
            if (empty($user)) {
                echo '此帐号不存在';
                return;
            }
            if (isset($user['status']) && $user['status'] == 0) {
                echo '此帐号已关闭';
                return;
            }
            $this->admin_otp_db->where([
                'mobile'      => $user['mobile']
            ])->or_where([
                'create_time <' => date('Y-m-d H:i:s', strtotime('-5 minute'))
            ])->delete_where();
            $this->admin_otp_db->insert([
                'mobile'    => $user['mobile'],
                'password'  => GetRandStr(4)
            ]);
            setcookie('otpCountdown', time());
            echo 'done';
            return;
        }
        echo '操作失败!';
        return;
    }
}
