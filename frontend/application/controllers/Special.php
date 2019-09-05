<?php defined('BASEPATH') || exit('No direct script access allowed');

class Special extends CommonBase
{
    public function __construct()
    {
        parent::__construct();
        $this->db->pconnect = true;
        $this->db->db_pconnect();
        $this->load->model('user_model', 'user_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('ettm_special_model', 'ettm_special_db');
        $this->load->model('ettm_special_bet_record_model', 'ettm_special_bet_record_db');
        $this->load->model('websocket_log_model', 'websocket_log_db');
        $this->load->model('qishu_model');
    }

    public function index($type)
    {
        $this->input->set_cookie('cookie', $this->input->cookie('cookie'), 86400);
        $this->load->view('special/index', [
            'ip'   => $this->site_config['swoole_ip'],
            'port' => $this->site_config['swoole_port'] + $type,
        ]);
    }

    /**
     * 牛牛 WebSocket
     * @param int $type 棋牌類型 1:牛牛 2:搶莊牛牛
     */
    public function niuniu($type)
    {
        header("Access-Control-Allow-Origin:");           //必选 允许所有来源访问
        header("Access-Control-Allow-Credentials:true");  //可选 是否允许发送cookie
        header("Access-Control-Allow-Method:POST,GET");   //可选 允许访问的方式
        $server = new swoole_websocket_server('0.0.0.0', $this->site_config['swoole_port']+$type);
        // $server->set([
        //     'worker_num' => 1,   //worker进程数量
        // ]);
        //連接動作
        $server->on('open', function ($server, $request) {
            $operator = $this->operator_db->getOperator(str_replace(['http://','https://'], '', $request->header['origin']));
            $server->fddata[$request->fd] = [
                'operator_id' => $operator === null ? 0:$operator['id'],
                'ip'          => is_null($request->server['remote_addr']) ? '':$request->server['remote_addr'],
                'user_agent'  => is_null($request->header['user-agent']) ? '':$request->header['user-agent'],
            ];
        });
        //監聽事件
        $server->on('message', function ($server, $frame) use ($type) {
            try {
                $operator_id = $server->fddata[$frame->fd]['operator_id'];
                //啟用模組
                $money_type = 0;
                $money_field = 'money';
                if ($type == 1) {
                    $module = $this->module_operator_db->getEnable($operator_id);
                    $money_type = array_key_exists(1, $module) ? 1:0;
                    $money_field = $money_type == 0 ? 'money':'money'.$money_type;
                }
                //判斷來源用
                $_SERVER['HTTP_USER_AGENT'] = $server->fddata[$frame->fd]['user_agent'];
                //於前端接收到的使用者訊息
                $data_list = json_decode(stripslashes($frame->data), true);
                if (!isset($data_list['gameType'])) {
                    throw new Exception('无gameType参数', 400);
                }
                //寫入LOG
                $this->benchmark->mark('Start');
                $logid = $this->websocket_log_db->insert([
                    'type'        => $data_list['type'],
                    'special_id'  => $data_list['gameType'],
                    'fd'          => $frame->fd,
                    'data'        => $frame->data,
                    'return_data' => '',
                    'ip'          => isset($server->fddata[$frame->fd]['ip']) ? $server->fddata[$frame->fd]['ip']:'',
                ]);
                $conn_list = $this->getAllClient($server, $data_list['gameType']/*, $operator_id */);
                $special = $this->ettm_special_db->row($data_list['gameType']);
                if ($special === null) {
                    throw new Exception('无此游戏，请确认', 400);
                }
                if ($special['status'] == 0) {
                    throw new Exception('棋牌关闭中', 400);
                }
                $lottery = $this->ettm_lottery_db->row($special['lottery_id']);
                if ($lottery['status'] == 0) {
                    throw new Exception('彩种关闭中', 400);
                }
                $return_data = '';
                $uid = 0;
                $_userinfo = [];

                //判斷登入狀態
                if (in_array($data_list['type'], ['getUserInfoByUid','bet','nextGame'])) {
                    //判斷是否允許該網域
                    if ($operator_id == 0) {
                        if ($server->exist($frame->fd)) {
                            $server->disconnect($frame->fd, 1000, '您的网域尚未开启游戏服务!');
                        }
                        throw new Exception('您的网域尚未开启游戏服务', 400);
                    }
                    //查詢該使用者資訊
                    $_userinfo = $this->user_db->where([
                        //'operator_id' => $operator_id,
                        'session'     => $data_list['uid'],
                    ])->result_one();
                    //判斷是否登入
                    if ($_userinfo === null) {
                        //LOG紀錄
                        $this->benchmark->mark('End');
                        $this->websocket_log_db->update([
                            'id'          => $logid,
                            'return_data' => '您的登录异常，请您重新登录',
                            'exec_time'   => $this->benchmark->elapsed_time('Start', 'End'),
                        ]);
                        if ($server->exist($frame->fd)) {
                            $server->disconnect($frame->fd, 1000, '您的登录异常，请您重新登录');
                        }
                        throw new Exception('您的登录异常，请您重新登录', 400);
                    }
                    $uid = $_userinfo['id'];
                }
    
                //遊戲初始化
                if ($data_list['type'] == 'getUserInfoByUid') {
                    //如果當前fd不一樣 則踢掉上一個連接者
                    if ($special['type'] == $_userinfo['special_type'] && $frame->fd != $_userinfo['websocket_fd']) {
                        if ($server->exist($_userinfo['websocket_fd'])) {
                            $server->disconnect($_userinfo['websocket_fd'], 1000, '您的账号在另一台设备登录~');
                        }
                    }
                    //更新user fd
                    $this->user_db->update([
                        'id'           => $_userinfo['id'],
                        'special_type' => $special['type'],
                        'special_id'   => $special['id'],
                        'websocket_fd' => $frame->fd,
                    ]);

                    //取得期數資料
                    $open_data = $this->getOpenAction($lottery, $special);
                    if ($server->exist($frame->fd)) {
                        $return_data .= json_encode($open_data);
                        $server->push($frame->fd, json_encode($open_data));
                    }
                    //桌面金幣總額
                    $qishu = $open_data['data']['n_qishu'];
                    $table = $this->getTableCoin($special['id'], $qishu, $_userinfo['id']/*, $operator_id*/);
                    if ($server->exist($frame->fd)) {
                        $return_data .= json_encode($table['table_coin']);
                        $server->push($frame->fd, json_encode($table['table_coin']));
                    }
                    //桌面金幣
                    if ($server->exist($frame->fd)) {
                        $return_data .= json_encode($table['info']);
                        $server->push($frame->fd, json_encode($table['info']));
                    }
                    //使用者資訊
                    if ($server->exist($frame->fd)) {
                        $data = [
                            'type'   => 'sendUserInfoByUid',
                            'status' => 1,
                            'code'   => 200,
                            'qishu'  => $qishu,
                            'data'   => [
                                'money'     => $_userinfo[$money_field],
                                'user_name' => $_userinfo['user_name']
                            ]
                        ];
                        $return_data .= json_encode($data);
                        $server->push($frame->fd, json_encode($data));
                    }
                }
                
                //下注
                if ($data_list['type'] == 'bet') {
                    //下注動作
                    $result = $this->betAction($_userinfo, $data_list['qishu'], $lottery, $special, $data_list['data'], $operator_id);
                    if ($result['status'] == 1) {
                        //下注完成推送給在線玩家
                        $sendBetOne = [
                            'type'   => 'betOne',
                            'status' => 1,
                            'code'   => 200,
                            'qishu'  => $data_list['qishu'],
                            'data'   => $data_list['data'],
                        ];
                        $return_data .= json_encode($sendBetOne);
                        $allin = isset($data_list['data']['allin']) ? $data_list['data']['allin']:0;
                        if ($allin == 1) {
                            for ($i=1000;$i<=$data_list['data']['bet'];$i+=1000) {
                                $sendBetOne['data']['bet'] = 1000;
                                $sendBetOne['data']['price'] = 1000;
                                foreach ($conn_list as $fd) {
                                    if ($server->exist($fd)) {
                                        if ($frame->fd != $fd) {
                                            $server->push($fd, json_encode($sendBetOne));
                                        }
                                    }
                                }
                            }
                        } else {
                            foreach ($conn_list as $fd) {
                                if ($server->exist($fd)) {
                                    if ($frame->fd != $fd) {
                                        $server->push($fd, json_encode($sendBetOne));
                                    }
                                }
                            }
                        }
                        //推送桌面各注區總額
                        $table = $this->getTableCoin($special['id'], $data_list['qishu'], $_userinfo['id']/*, $operator_id*/);
                        $countCoin = [];
                        foreach ($table['table_coin']['data'] as $key => $arr) {
                            $countCoin[$key] = $arr['countCoin'];
                        }
                        $data = [
                            'type'  => 'countCoinArr',
                            'qishu' => $data_list['qishu'],
                            'data'  => ['countCoinArr'=>$countCoin],
                        ];
                        $return_data .= json_encode($data);
                        foreach ($conn_list as $fd) {
                            if ($server->exist($fd)) {
                                $server->push($fd, json_encode($data));
                            }
                        }
                    }
                    //推送下注結果
                    $user = $this->user_db->row($_userinfo['id']);
                    if ($server->exist($frame->fd)) {
                        $data = [
                            'type'    => 'betResult',
                            'status'  => $result['status'],
                            'message' => $result['message'],
                            'code'    => $result['code'],
                            'data'    => $result['data'] + ['money'=>$user[$money_field]],
                        ];
                        $return_data .= json_encode($data);
                        $server->push($frame->fd, json_encode($data));
                    }
                }
                
                //開獎任務
                if ($data_list['type'] == 'pai') {
                    //取得庄家的撲克牌牛型
                    $getCardArr = $this->ettm_special_db->getNiuCard($data_list['data']['numbers']);
                    //取得玩家於各注區的加總賠付額
                    $profit = $this->getProfitByUID($special, $data_list['data']['qishu']/*, $operator_id*/);
                    //查詢目前所有再線玩家餘額，並單獨推送給各玩家自己的餘額
                    $where['special_id'] = $special['id'];
                    if ($data_list['data']['operator_id'] > 0) {
                        $where['operator_id'] = $data_list['data']['operator_id'];
                    }
                    $user = $this->user_db->where($where)->result();
                    $user = array_column($user, $money_field, 'websocket_fd');
                    $push = [
                        'type'  => 'openNumbers',
                        'qishu' => (int)$data_list['data']['qishu'],
                        'times' => time(),
                        'data'  => [
                            'numbers'  => $data_list['data']['numbers'],
                            'pokerArr' => $getCardArr,
                        ]
                    ];
                    $return_data .= json_encode($push);
                    foreach ($conn_list as $fd) {
                        //Client fd是否存在
                        if ($server->exist($fd) && isset($user[$fd])) {
                            //1.推送金币
                            $server->push($fd, json_encode([
                                'type' => "moneyUpdate",
                                'data' => ['money' => $user[$fd]],
                            ]));
                            
                            //寫入輸贏
                            for ($i=0;$i<=5;$i++) {
                                $push['data']['pokerArr'][$i]['win'] = isset($profit[$fd][$i]) ? $profit[$fd][$i] : 0;
                            }
                            //2.推送开奖号码
                            $server->push($fd, json_encode($push));
                        }
                    }
                }
    
                //下一期數之遊戲初始化
                if ($data_list['type'] == 'nextGame') {
                    //取得倒數計時及期數資料
                    $open_data = $this->getOpenAction($lottery, $special);
                    if ($server->exist($frame->fd)) {
                        $return_data .= json_encode($open_data);
                        $server->push($frame->fd, json_encode($open_data));
                    }
                    //桌面金幣總額
                    $table = $this->getTableCoin($special['id'], $open_data['data']['n_qishu'], $_userinfo['id']/*, $operator_id*/);
                    if ($server->exist($frame->fd)) {
                        $server->push($frame->fd, json_encode($table['table_coin']));
                    }
                    //桌面金幣
                    if ($server->exist($frame->fd)) {
                        $server->push($frame->fd, json_encode($table['info']));
                    }
                    //更新使用者餘額
                    if ($server->exist($frame->fd)) {
                        $server->push($frame->fd, json_encode([
                            'type' => 'moneyUpdate',
                            'data' => ['money'=>$_userinfo[$money_field]],
                        ]));
                    }
                }
    
                //取得在線玩家
                if ($data_list['type'] == 'getUserList') {
                    if ($server->exist($frame->fd)) {
                        $result = $this->user_db->select('user_name')->where([
                            //'operator_id' => $operator_id,
                            'special_id'  => $special['id'],
                        ])->result();
                        foreach ($result as $key => $row) {
                            $row['user_name'] = substr($row['user_name'], 0, 2).'***'.substr($row['user_name'], -2);
                            $result[$key] = $row;
                        }

                        $data = [
                            'type' => 'userlist',
                            'data' => $result,
                        ];
                        $return_data .= json_encode($data);
                        $server->push($frame->fd, json_encode($data));
                    }
                }

                //取得當期最新15筆下注
                if ($data_list['type'] == 'getBetList') {
                    if ($server->exist($frame->fd)) {
                        $join[] = [$this->table_.'user t1','t.uid = t1.id','left'];
                        $result = $this->ettm_special_bet_record_db->select('t1.user_name,t.c_value')->where([
                            //'operator_id' => $operator_id,
                            'special_id'  => $special['id'],
                            'status'      => 1,
                            'is_lose_win' => 1,
                        ])->join($join)->order(['id','desc'])->limit([0,15])->result();
                        foreach ($result as $key => $row) {
                            $row['user_name'] = substr($row['user_name'], 0, 2).'***';
                            $row['c_value'] = (int)$row['c_value'];
                            $result[$key] = $row;
                        }
                        $server->push($frame->fd, json_encode([
                            'type' => 'betList',
                            'data' => $result,
                        ]));
                    }
                }

                //LOG紀錄
                if ($logid != 0) {
                    $this->benchmark->mark('End');
                    $this->websocket_log_db->update([
                        'id'          => $logid,
                        'uid'         => $uid,
                        'return_data' => $return_data,
                        'exec_time'   => $this->benchmark->elapsed_time('Start', 'End'),
                    ]);
                }
            } catch (Exception $e) {
                if ($server->exist($frame->fd)) {
                    $server->push($frame->fd, json_encode([
                        'type'    => 'error',
                        'status'  => 0,
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode()
                    ]));
                }
            }
        });
        //close關閉事件
        $server->on('close', function ($server, $fd) use ($type) {
            try {
                //删除在线用户
                $this->user_db->where([
                    'special_type' => $type,
                    'websocket_fd' => $fd,
                ])->update_where([
                    'special_type' => 0,
                    'special_id'   => 0,
                    'websocket_fd' => 0,
                ]);
                unset($server->fddata[$fd]);
            } catch (Exception $e) {
                if ($server->exist($fd)) {
                    $server->push($fd, json_encode([
                        'type'    => 'error',
                        'status'  => 0,
                        'message' => $e->getMessage(),
                        'code'    => $e->getCode()
                    ]));
                }
            }
        });
        //定時任務
        $server->on('WorkerStart', function ($server, $worker_id) use ($type) {
            if ($server->worker_id == 0) {
                //$operator_list = $this->operator_db->result();
                $special_list = $this->ettm_special_db->where(['type'=>$type])->result();
                $robot_user = $this->user_db->where([/*'operator_id'=>0,*/'type'=>-$type])->result();
                $online_uids = [];
                foreach ($robot_user as $user) {
                    if ($user['special_id'] > 0) {
                        $online_uids[$user['special_id']][] = $user['id'];
                    }
                }
                //定時任務-ROBOT上線
                swoole_timer_tick(180000, function ($id) use ($server, $special_list, $robot_user, &$online_uids, $type) {
                    $ids = [];
                    //篩選出正在開盤的棋牌
                    foreach ($special_list as $special) {
                        $qishu_ing = $this->qishu_model->getQishu(3, $special['lottery_id']);
                        //關盤不下注
                        if (time() >= $qishu_ing['count_down'] - $qishu_ing['interval']) {
                            $ids[] = $special['id'];
                        }
                    }
                    $online_uids = [];
                    foreach ($robot_user as $user) {
                        //有彩種開盤 機器人才會上線
                        if ($ids != [] && rand(1, 4 / count($ids)) == 1) {
                            $special_id = $ids[array_rand($ids)];
                            $online_uids[$special_id][] = $user['id'];
                            //標記上線
                            $this->user_db->update([
                                'id'           => $user['id'],
                                'special_type' => $type,
                                'special_id'   => $special_id,
                                'websocket_fd' => 0,
                            ]);
                        } else {
                            //標記離線
                            $this->user_db->update([
                                'id'           => $user['id'],
                                'special_type' => 0,
                                'special_id'   => 0,
                                'websocket_fd' => 0,
                            ]);
                        }
                    }
                });
                //定時任務-隨機下注
                swoole_timer_tick(3000, function ($id) use ($server, $special_list, &$online_uids) {
                    //隨機範圍
                    $price = [10,25,50,100,250];
                    $groupIndex = [1,2,3,4,5];
                    foreach ($special_list as $special) {
                        $lottery = $this->ettm_lottery_db->row($special['lottery_id']);
                        $qishu_ing = $this->qishu_model->getQishu(3, $special['lottery_id']);
                        //關盤不下注
                        if (time() < $qishu_ing['count_down'] - $qishu_ing['interval']) {
                            break;
                        }

                        //foreach ($operator_list as $operator) {
                        $conn_list = $this->getAllClient($server, $special['id']/*, $operator['id']*/);
                        //在線玩家
                        $online = isset($online_uids[$special['id']]) ? $online_uids[$special['id']]:[];
                        foreach ($online as $uid) {
                            if (rand(1, 50) == 1) {
                                //訂單號
                                $order_sn = create_order_sn('SB');
                                $description = $lottery['name'].ettm_special_model::$typeList[$special['type']].'下注(ROBOT)';
                                $p_value = $price[array_rand($price)];
                                $bet_values = $groupIndex[array_rand($groupIndex)];
                                //寫入注單
                                $this->user_db->addMoney($uid, $order_sn, 18, -$p_value, $description, 3, $lottery['id'], $special['id']);
                                $this->ettm_special_bet_record_db->insert([
                                    "lottery_id"    => $lottery['id'],
                                    "special_id"    => $special['id'],
                                    "qishu"         => $qishu_ing['next_qishu'],
                                    "uid"           => $uid,
                                    "order_sn"      => $order_sn,
                                    "p_value"       => $p_value,
                                    "bet_multiple"  => 1,
                                    "bet_values"    => $bet_values,
                                    "total_p_value" => $p_value,
                                ]);
                                //下注完成推送給在線玩家
                                foreach ($conn_list as $fd) {
                                    if ($server->exist($fd)) {
                                        $server->push($fd, json_encode([
                                            'type'   => 'betOne',
                                            'status' => 1,
                                            'code'   => 200,
                                            'qishu'  => $qishu_ing['next_qishu'],
                                            'data'   => [
                                                'allin'      => 0,
                                                'price'      => $p_value,
                                                'groupIndex' => $bet_values,
                                                'bet'        => $p_value,
                                                'dobule'     => 0,
                                            ],
                                        ]));
                                    }
                                }
                                //推送桌面各注區總額
                                $table = $this->getTableCoin($special['id'], $qishu_ing['next_qishu'], $uid/*, $operator['id']*/);
                                $countCoin = [];
                                foreach ($table['table_coin']['data'] as $key => $arr) {
                                    $countCoin[$key] = $arr['countCoin'];
                                }
                                foreach ($conn_list as $fd) {
                                    if ($server->exist($fd)) {
                                        $server->push($fd, json_encode([
                                            'type'  => 'countCoinArr',
                                            'qishu' => $qishu_ing['next_qishu'],
                                            'data'  => ['countCoinArr'=>$countCoin],
                                        ]));
                                    }
                                }
                            }
                        }
                        //}
                    }
                });

                //保持MySQL連線存活 半小時執行一次
                // swoole_timer_tick(1800000, function ($id) {
                //     $this->db->reconnect();
                // });
            }
        });
        
        $server->start();
    }
    
    /**
     * 下注
     * @param array $user  用戶資訊
     * @param int $qishu  期數
     * @param array $lottery 彩種資訊
     * @param array $special 特色棋牌資訊
     * @param array $betdata 注單
     * @param int $operator_id 營運商ID
     */
    private function betAction($user, $qishu, $lottery, $special, $betdata, $operator_id)
    {
        try {
            if ($user !== null && $user['status']==1) {
                throw new Exception("账户已经封号,请联系客服(csa002)", 452);
            }
            if ($user !== null && $user['status']==2) {
                throw new Exception("账户已经冻结,请联系客服(csa003)", 452);
            }
            if (empty($qishu)) {
                throw new Exception("缺少必要参数(csa004)", 300);
            }
            //娱乐类型
            if ($special['status'] == 0) {
                throw new Exception("娱乐关闭，请稍后再试(csa006)！", 300);
            }
            //啟用模組
            $money_type = 0;
            $money_field = 'money';
            if ($special['type'] == 1) {
                $module = $this->module_operator_db->getEnable($operator_id);
                $money_type = array_key_exists(1, $module) ? 1:0;
                $money_field = $money_type == 0 ? 'money':'money'.$money_type;
            }
            //牛牛期數
            $qishu_ing = $this->qishu_model->getQishu(3, $special['lottery_id']);
            if (($qishu_ing['count_down'] - time()) <= $qishu_ing['adjust']) {
                throw new Exception('该期数已截至投注(niu001)', 300);
            }
            if ($qishu != $qishu_ing['next_qishu']) {
                throw new Exception('该期投注已截止(niu002)', 300);
            }
            $groupIndex = [0,1,2,3,4,5];
            switch ($special['type']) {
                case 1: $groupIndex = [1,2,3,4,5]; break;
                case 2: $groupIndex = [0,1,2,3,4,5]; break;
            }
            //驗證玩家下注位置是否正確
            if (!in_array($betdata['groupIndex'], $groupIndex)) {
                throw new Exception('投注资讯有误(niu003)', 300);
            }
            //驗證玩家下注籌碼是否正確 (搶莊牛牛)
            if ($special['type'] == 2 && $betdata['groupIndex'] == 0 && $betdata['price'] > 0 && $betdata['price'] % 1000 != 0) {
                throw new Exception('庄家限制投注1000的倍数(niu004)', 300);
            }
            if (in_array($betdata['groupIndex'], [1,2,3,4,5]) && !in_array($betdata['price'], [10,25,50,100,250])) {
                throw new Exception('闲家限制投注10,25,50,100,250(niu005)', 300);
            }
            //驗證玩家下注是否翻倍 0 = 不翻倍, 1 = 翻倍 (搶莊牛牛)
            if ($special['type'] == 2 && $betdata['groupIndex'] == 0 && $betdata['dobule'] != 0) {
                throw new Exception('庄家无法翻倍投注(niu006)', 300);
            }
            if (in_array($betdata['groupIndex'], [1,2,3,4,5]) && !in_array($betdata['dobule'], [0,1])) {
                throw new Exception('投注资讯有误(niu007)', 300);
            }
            $bet_multiple_money = 0;
            $double_value = 4;
            //驗證玩家是否翻倍，餘額是否大於翻倍金額
            if ($betdata['dobule'] == 0) {
                $bet_multiple_money = $betdata['price'];
                $double_value = 1;
                if ($user[$money_field] < $bet_multiple_money) {
                    throw new Exception('余额不足(niu008)', 300);
                }
            }
            if ($betdata['dobule'] == 1) {
                $bet_multiple_money = $betdata['price'] * 4;
                $double_value = 4;
                if ($user[$money_field] < $bet_multiple_money) {
                    throw new Exception('翻倍余额不足(niu009)', 300);
                }
            }
            $join[] = [$this->table_.'user t1',"t.uid = t1.id",'left'];
            if ($betdata['groupIndex'] == 0) {
                //莊家下注上限
                $row = $this->ettm_special_bet_record_db->escape(false)->select('IFNULL(SUM(t.total_p_value),0) total_p_value')->where([
                    //'operator_id'  => $operator_id,
                    't.special_id' => $special['id'],
                    't.qishu'      => $qishu,
                    't.bet_values' => 0,
                ])->join($join)->result_one();
                
                if ($row['total_p_value'] + $betdata['price'] > $special['banker_limit']) {
                    if ($betdata['allin'] == 0) {
                        throw new Exception("庄家最大限额 $special[banker_limit] 元", 300);
                    } else {
                        //莊家All in時的實際下注金額
                        $betdata['price'] -= $row['total_p_value'] + $betdata['price'] - $special['banker_limit'];
                        $betdata['bet'] = $betdata['price'];
                    }
                }
            } else {
                //閒家總下注上限 (搶莊牛牛)
                if ($special['type'] == 2) {
                    $row = $this->ettm_special_bet_record_db->escape(false)->select('IFNULL(SUM(t.total_p_value),0) total_p_value')->where([
                        //'operator_id'  => $operator_id,
                        't.special_id'    => $special['id'],
                        't.qishu'         => $qishu,
                        't.bet_values <>' => 0,
                    ])->join($join)->result_one();
                    if ($row['total_p_value'] + $betdata['price'] > $special['banker_limit']) {
                        throw new Exception("已超出闲家总下注上限 $special[banker_limit] 元", 300);
                    }
                }
                //閒家單注區下注限額
                $row = $this->ettm_special_bet_record_db->escape(false)->select('IFNULL(SUM(t.total_p_value),0) total_p_value')->where([
                    //'operator_id'  => $operator_id,
                    't.special_id' => $special['id'],
                    't.qishu'      => $qishu,
                    't.uid'        => $user['id'],
                    't.bet_values' => $betdata['groupIndex'],
                ])->join($join)->result_one();
                $betMoney = $betdata['dobule'] == 1 ? $betdata['price'] * 4 : $betdata['price'];
                if ($row['total_p_value'] + $betMoney > $special['player_limit']) {
                    throw new Exception("闲家单区最大限额 $special[banker_limit] 元", 300);
                }
            }
            //訂單號
            $order_sn = create_order_sn('SB');
            $description = $lottery['name'].ettm_special_model::$typeList[$special['type']].'下注';
            $insert = [
                "lottery_id"    => $lottery['id'],
                "special_id"    => $special['id'],
                "qishu"         => $qishu,
                "uid"           => $user['id'],
                "order_sn"      => $order_sn,
                "p_value"       => $betdata['price'],
                "bet_multiple"  => $double_value,
                "bet_values"    => $betdata['groupIndex'],
                "total_p_value" => $bet_multiple_money,
            ];
            //寫入注單
            $allin = isset($betdata['allin']) ? $betdata['allin']:0;
            if ($allin == 1) {
                for ($i=1000;$i<=$betdata['price'];$i+=1000) {
                    $insert['p_value'] = 1000;
                    $this->user_db->addMoney($user['id'], $order_sn, 18, -1000, $description, 3, $lottery['id'], $special['id'], $money_type);
                    $this->ettm_special_bet_record_db->insert($insert);
                }
            } else {
                $this->user_db->addMoney($user['id'], $order_sn, 18, -$bet_multiple_money, $description, 3, $lottery['id'], $special['id'], $money_type);
                $this->ettm_special_bet_record_db->insert($insert);
            }

            return [
                'type'    => 'betStatus',
                'status'  => 1,
                'code'    => 200,
                'message' => 'success',
                'data'    => $betdata,
            ];
        } catch (Exception $e) {
            return [
                'type'    => 'betStatus',
                'status'  => 0,
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'data'    => [],
            ];
        }
    }

    /**
     * 依彩種獲取開獎結果
     * @param array $lottery 彩種資料
     * @param array $special 特色棋牌資料
     * @return array 開獎結果資訊
     */
    private function getOpenAction($lottery, $special)
    {
        $qishu_arr = $this->qishu_model->getQishu(3, $lottery['id']);

        $record = $this->ettm_lottery_record_db->where([
            'lottery_id' => $lottery['id'],
            'qishu'      => $qishu_arr['qishu'],
        ])->result_one();

        $getPokerArr = $this->ettm_special_db->getNiuCard(explode(',', $record['numbers']));
        $resultData = [
            'type' => 'gameData',
            'data' => [
                'name'        => $lottery['name'],
                'type'        => ettm_special_model::$typeList[$special['type']],
                'logo'        => $lottery['pic_icon'],
                'timestamp'   => $qishu_arr['count_down'],
                'timestamps'  => time(),
                'qishu'       => $qishu_arr['qishu'],
                'numbers'     => $record['numbers'] == '' ? []:explode(',', $record['numbers']),
                'n_qishu'     => $qishu_arr['next_qishu'],
                'lastOpen'    => [
                    'qishu'   => $qishu_arr['qishu'],
                    'numbers' => $record['numbers']
                ],
                'pokerArr'    => $getPokerArr,
                'qishu_close' => $qishu_arr['adjust'],
                'httpApi'     => '',
                'game_ename'  => $lottery['key_word'],
            ]
        ];
        return $resultData;
    }

    /**
     * 取得該玩家桌面金幣信息
     * @param int $special_id 特色棋牌ID
     * @param int $qishu 期數
     * @param int $uid 用戶ID
     * @param int $operator_id 營運商ID
     * @return array
     */
    public function getTableCoin($special_id, $qishu, $uid/*, $operator_id*/)
    {
        $table_coin = [
            'type' => 'tableCoin',
            'qishu' => $qishu,
            'data' => [
                0 => ['myCoin' => 0, 'countCoin' => 0,],
                1 => ['myCoin' => 0, 'countCoin' => 0,],
                2 => ['myCoin' => 0, 'countCoin' => 0,],
                3 => ['myCoin' => 0, 'countCoin' => 0,],
                4 => ['myCoin' => 0, 'countCoin' => 0,],
                5 => ['myCoin' => 0, 'countCoin' => 0,],
            ],
        ];

        $info = [
            'type' => 'tableCoinInfo',
            'qishu' => $qishu,
            'data' => [
                0=> [],
                1=> [],
                2=> [],
                3=> [],
                4=> [],
                5=> [],
            ],
        ];
        
        $join[] = [$this->table_.'user t1',"t.uid = t1.id",'left'];
        $result = $this->ettm_special_bet_record_db->select('t.*')->where([
            //'operator_id' => $operator_id,
            'special_id'  => $special_id,
            'qishu'       => $qishu,
        ])->join($join)->result();
        
        //如果無資料則返回空值
        foreach ($result as $row) {
            $table_coin['data'][$row['bet_values']]['countCoin'] += (int)$row['total_p_value'];
            if ($row['uid'] == $uid) {
                $table_coin['data'][$row['bet_values']]['myCoin'] += (int)$row['total_p_value'];
            }

            $info['data'][$row['bet_values']][] = [
                'price'      => (int)$row['p_value'],
                'bet'        => (int)$row['total_p_value'],
                'groupIndex' => $row['bet_values'],
            ];
        }

        return [
            'table_coin' => $table_coin,
            'info'       => $info,
        ];
    }

    /**
     * 取得各注區的賠付額
     * @param array $special 特色棋牌資訊
     * @param int $qishu 期數
     * @param int $operator_id 營運商ID
     */
    public function getProfitByUID($special, $qishu/*, $operator_id*/)
    {
        $join[] = [$this->table_.'user t1',"t.uid = t1.id",'left'];
        $result = $this->ettm_special_bet_record_db->escape(false)->select('t1.websocket_fd,t.bet_values,SUM(t.c_value - t.total_p_value) profit')->where([
            //'operator_id'     => $operator_id,
            't.special_id'    => $special['id'],
            't.qishu'         => $qishu,
            't.status'        => 1,
            't1.special_type' => $special['type'],
        ])->join($join)->group('t.bet_values,t.uid')->result();
        
        $data = [];
        foreach ($result as $row) {
            $data[(int)$row['websocket_fd']][$row['bet_values']] = (float)$row['profit'];
        }
        
        return $data;
    }

    /**
     * 依遊戲取得所有Client fd
     * @param object $server WebSocket Server
     * @param int $special_id 特色棋牌ID
     * @param int $operator_id 營運商ID
     */
    private function getAllClient($server, $special_id/*, $operator_id*/)
    {
        $user = $this->user_db->where([
            //'operator_id' => $operator_id,
            'special_id'  => $special_id,
        ])->result();
        $userfd = array_column($user, 'websocket_fd');

        $result = [];
        $start_fd = 0;
        while (true) {
            $conn_list = $server->getClientList($start_fd, 100);
            if ($conn_list === false || count($conn_list) === 0) {
                break;
            }
            
            foreach ($conn_list as $fd) {
                if (in_array($fd, $userfd)) {
                    $result[] = $fd;
                }
                $start_fd = $fd;
            }
        }
        return $result;
    }
}
