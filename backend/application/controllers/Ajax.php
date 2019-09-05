<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ajax extends AdminCommon
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 取得頁首資訊
     */
    public function getTopMessage()
    {
        $this->load->model('user_model', 'user_db');
        $this->load->model('recharge_order_model', 'recharge_order_db');
        $this->load->model('user_withdraw_model', 'user_withdraw_db');
        $this->is_login = 1;
        //在線會員
        $top_count['online'] = $this->user_db->where([
            'type'              => 0,
            'unlock_time <'     => date('Y-m-d H:i:s'),
            'last_active_time1' => date('Y-m-d H:i:s', time() - $this->site_config['online_status'])
        ])->count();
        //今日註冊
        $top_count['register'] = $this->user_db->where([
            'create_time1' => date('Y-m-d')
        ])->count();
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        //充值
        $top_count['recharge'] = $this->recharge_order_db->where([
            'status' => 0
        ])->join($join)->count();
        //提現
        $top_count['withdraw'] = $this->user_withdraw_db->where([
            'status' => 0
        ])->join($join)->count();

        $top_count['player_audio'] = 0;
        if ($top_count['recharge'] > 0 || $top_count['withdraw'] > 0) {
            if ($this->session->userdata('audio_time')) {
                if ($this->session->userdata('audio_time') + 60 < time()) {
                    $top_count['player_audio'] = 1;
                    $this->session->set_userdata('audio_time', time());
                }
            } else {
                $top_count['player_audio'] = 1;
                $this->session->set_userdata('audio_time', time());
            }
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($top_count));
    }

    /**
     * 設定全局單頁顯示筆數
     */
    public function setPerPage()
    {
        if ($this->input->is_ajax_request()) {
            $per_page = $this->input->post('per_page');
            $per_page = $per_page == '' ? 20 : $per_page;
            $this->session->set_userdata('per_page', $per_page);

            echo 'done';
        }
    }

    /**
     * 設定全局運營商
     */
    public function globalOperator()
    {
        if ($this->input->is_ajax_request()) {
            $operator = $this->input->post('operator');
            $operator[] = 0;
            $this->session->set_userdata('show_operator', $operator);
            $this->cache->redis->delete("{$this->operator_db->_table_name}-getList-0");
            echo 'done';
        }
    }

    /**
     * 取得經典玩法
     */
    public function getClassicWanfa()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('ettm_classic_wanfa_model', 'ettm_classic_wanfa_db');
            $lottery_type_id = $this->input->post('lottery_type_id');
            $pid = $this->input->post('pid');
            if ($pid === null) {
                $pid = 0;
            }

            $result = $this->ettm_classic_wanfa_db->where([
                'lottery_type_id' => $lottery_type_id,
                'pid'             => $pid,
            ])->result();

            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    /**
     * 取得官方玩法
     */
    public function getOfficialWanfa()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('ettm_official_wanfa_model', 'ettm_official_wanfa_db');
            $lottery_type_id = $this->input->post('lottery_type_id');
            $pid = $this->input->post('pid');
            if ($pid === null) {
                $pid = 0;
            }

            $result = $this->ettm_official_wanfa_db->where([
                'lottery_type_id' => $lottery_type_id,
                'pid'             => $pid,
            ])->result();

            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    /**
     * 取得彩种大类
     */
    public function getLotteryType()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('ettm_lottery_type_model', 'ettm_lottery_type_db');
            $category = $this->input->post('category');

            $result = $this->ettm_lottery_type_db->where([
                'mode' => $category,
            ])->result();

            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    /**
     * 取得彩种清單
     */
    public function getLottery()
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
            $category = $this->input->post('category');
            $lottery_type_id = $this->input->post('typeid');

            $where = [];
            if ($category !== null) {
                $where['mode'] = $category;
            }
            if ($lottery_type_id !== null) {
                $where['lottery_type_id'] = $lottery_type_id;
            }
            $result = $this->ettm_lottery_db->where($where)->order(['lottery_type_id' => 'asc', 'id' => 'asc'])->result();

            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    /**
     * 圖片上傳
     */
    public function image_upload($dir = 'images')
    {
        $this->load->library('upload');
        $path = "../data/upload/$dir";
        @mkdir($path, 0777);

        $config['upload_path'] = $path;
        $config['allowed_types'] = 'gif|png|jpg|jpeg|jpe';
        $config['max_size']    = '2000';
        $config['overwrite'] = 'true';

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file')) {
            echo json_encode([
                'error' => $this->upload->display_errors('', ''),
            ]);
        } else {
            $data = $this->upload->data(); //Image Resizing
            $file_path = $dir . '/' . $data['file_name'];

            echo stripslashes(json_encode([
                'status'   => 1,
                'filelink' => $file_path,
            ]));
        }
    }

    /**
     * 檔案上傳
     */
    public function file_upload($dir = 'files')
    {
        $this->load->library('upload');
        $path = "../data/upload/$dir";
        @mkdir($path, 0777);

        $config['upload_path'] = $path;
        $config['allowed_types'] = '*';
        $config['overwrite'] = 'true';

        $this->upload->initialize($config);
        //$this->upload->display_errors('<p>','</p>');

        if (!$this->upload->do_upload('file')) {
            echo json_encode([
                'error' => $this->upload->display_errors('', ''),
            ]);
        } else {
            $data = $this->upload->data();
            $file_path = $path . '/' . $data['file_name'];

            echo stripslashes(json_encode([
                'status'   => 1,
                'filelink' => substr($file_path, 1),
            ]));
        }
    }
}
