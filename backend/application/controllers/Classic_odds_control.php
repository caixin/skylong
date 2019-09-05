<?php defined('BASEPATH') || exit('No direct script access allowed');

class Classic_odds_control extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('ettm_classic_bet_record_model', 'ettm_classic_bet_record_db');
        $this->load->model('ettm_classic_wanfa_model', 'ettm_classic_wanfa_db');
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
        $this->load->model('ettm_classic_odds_control_model', 'ettm_classic_odds_control_db');
        $this->load->model('qishu_model');
    }

    public function index($lottery_id=35)
    {
        // get params.
        $params        = $this->uri->uri_to_assoc(4);
        $search_params = param_process($params, ['id', 'asc']);
        $where         = $search_params['where'];
        $operator = $this->operator_db->getList(0);
        
        if (!isset($where['operator_id'])) {
            foreach ($operator as $key => $val) {
                $where['operator_id'] = $key;
                break;
            }
        }

        if ($this->input->is_ajax_request()) {
            $position = $this->input->post('position', true);
            switch ($position) {
                case 'left':      $list = $this->_left($lottery_id, $where['operator_id']);      break;
                case 'open':      $list = $this->_open($lottery_id, $where['operator_id']);      break;
                case 'list':      $list = $this->_list($lottery_id, $where['operator_id']);      break;
                case 'edit_odds': $list = $this->_edit_odds($lottery_id, $where['operator_id']); break;
                case 'numbers':   $list = $this->_numbers($lottery_id, $where['operator_id']);   break;
            }

            $this->output->set_content_type('application/json')->set_output(json_encode($list));
            return;
        }
        
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), "$this->cur_url/$lottery_id"));
        }

        $lottery = $this->ettm_lottery_db->where([
            'id' => [14,15,22,35],
        ])->order(['id','desc'])->result();
        $lottery_type = array_column($lottery, 'lottery_type_id', 'id');
        $lottery_type_id = $lottery_type[$lottery_id];

        $wanfa = $this->ettm_classic_wanfa_db->getListByLottery($lottery_type_id);
        $wanfa_id = 0;
        foreach ($wanfa as $row) {
            //六合彩顯示第二階
            if ($lottery_type_id == 8) {
                if ($row['pid'] == 0) {
                    continue;
                }
            } else {
                if ($row['pid'] != 0) {
                    continue;
                }
            }
            $wanfa_id = $row['id'];
            break;
        }

        $this->layout->view($this->cur_url, [
            'lottery'         => array_column($lottery, 'name', 'id'),
            'lottery_id'      => $lottery_id,
            'lottery_type_id' => $lottery_type_id,
            'wanfa_id'        => $wanfa_id,
            'operator'        => $operator,
            'where'           => $where,
        ]);
    }

    private function _left($lottery_id, $operator_id)
    {
        $wanfa_id = $this->input->post('wanfa_id', true);
        $statistics = $this->input->post('statistics', true);

        $lottery = $this->ettm_lottery_db->row($lottery_id);
        //獲取期數資訊
        $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);
        $qishu = $statistics == 1 ? $qishu_arr['qishu']:$qishu_arr['next_qishu'];

        $wanfa = $this->ettm_classic_wanfa_db->getListByLottery($lottery['lottery_type_id']);
        $where = [
            'operator_id'  => $operator_id,
            't.lottery_id' => $lottery_id,
            't.qishu'      => $qishu,
        ];
        if ($lottery['lottery_type_id'] == 8) {
            //六合彩統計第二階
            $wanfa_bet = $this->ettm_classic_bet_record_db->getBetCountGroup($where, 'wanfa_id');
            $wanfa_bet = array_column($wanfa_bet, ($statistics == 1 ? 'profit':'total_p_value'), 'wanfa_id');
        } else {
            //其他統計第一階
            $wanfa_bet = $this->ettm_classic_bet_record_db->getBetCountGroup($where, 'wanfa_pid');
            $wanfa_bet = array_column($wanfa_bet, ($statistics == 1 ? 'profit':'total_p_value'), 'wanfa_pid');
        }
        //總額
        $total = 0;
        foreach ($wanfa_bet as $val) {
            $total = bcadd($total, $val, 2);
        }
        $data[] = [
            'id'    => '',
            'name'  => $statistics == 0 ? '虚货':'实货',
            'total' => $total,
        ];
        //側欄
        $pidname = [];
        foreach ($wanfa as $row) {
            if ($lottery['lottery_type_id'] == 8) {
                //六合彩顯示第二階
                if ($row['pid'] == 0) {
                    $pidname[$row['id']] = $row['name'];
                    continue;
                }
            } else {
                //其餘顯示第一階
                if ($row['pid'] != 0) {
                    continue;
                }
            }

            $data[] = [
                'id'    => $row['id'],
                'name'  => in_array($row['pid'], [177,178,179]) ? $pidname[$row['pid']].'-'.$row['name']:$row['name'],
                'color' => $row['id'] == $wanfa_id ? '#FDFCA4':'',
                'total' => isset($wanfa_bet[$row['id']]) ? $wanfa_bet[$row['id']]:0,
            ];
        }
        return $data;
    }

    private function _open($lottery_id, $operator_id)
    {
        $wanfa_id = $this->input->post('wanfa_id', true);
        
        $lottery = $this->ettm_lottery_db->row($lottery_id);
        //獲取期數資訊
        $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);
        //取得當前開獎號碼
        $record = $this->ettm_lottery_record_db->where([
            'lottery_id' => $lottery_id,
            'qishu'      => $qishu_arr['qishu'],
        ])->result_one();
        //上期輸贏
        $where = [
            'operator_id'  => $operator_id,
            't.lottery_id' => $lottery_id,
            't.qishu'      => $qishu_arr['qishu'],
        ];
        if ($lottery['lottery_type_id'] == 8) {
            //六合彩統計第二階
            $wanfa_bet = $this->ettm_classic_bet_record_db->getBetCountGroup($where, 'wanfa_id');
            $wanfa_profit = array_column($wanfa_bet, 'profit', 'wanfa_id');
        } else {
            //其他統計第一階
            $wanfa_bet = $this->ettm_classic_bet_record_db->getBetCountGroup($where, 'wanfa_pid');
            $wanfa_profit = array_column($wanfa_bet, 'profit', 'wanfa_pid');
        }
        $profit = isset($wanfa_profit[$wanfa_id]) ? $wanfa_profit[$wanfa_id]:0;
        //球號顏色
        if (in_array($wanfa_id, [184,185,186,187,188,189,190,192])) {
            $numbers = [];
            foreach (explode(',', $record['numbers']) as $val) {
                $val = str_pad($val, 2, '0', STR_PAD_LEFT);
                if (in_array((int)$val, ettm_classic_wanfa_detail_model::$colorBall['r'])) {
                    $numbers[] = "<span class=\"ball_red\">$val</span>";
                }
                if (in_array((int)$val, ettm_classic_wanfa_detail_model::$colorBall['b'])) {
                    $numbers[] = "<span class=\"ball_blue\">$val</span>";
                }
                if (in_array((int)$val, ettm_classic_wanfa_detail_model::$colorBall['g'])) {
                    $numbers[] = "<span class=\"ball_green\">$val</span>";
                }
            }
            $record['numbers'] = implode(',', $numbers);
        }
        $wanfa = $this->ettm_classic_wanfa_db->row($wanfa_id);

        return [
            'qishu'      => $qishu_arr['qishu'],
            'next_qishu' => $qishu_arr['next_qishu'],
            'count_down' => date('Y-m-d H:i:s', $qishu_arr['count_down']),
            'numbers'    => $record['numbers'],
            'wanfa_name' => $wanfa['name'],
            'profit'     => $profit,
        ];
    }

    private function _list($lottery_id, $operator_id)
    {
        $wanfa_id = $this->input->post('wanfa_id', true);
        $sort = $this->input->post('sort', true);
        $statistics = $this->input->post('statistics', true);

        $lottery = $this->ettm_lottery_db->row($lottery_id);
        //獲取期數資訊
        $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);
        $qishu = $statistics == 1 ? $qishu_arr['qishu']:$qishu_arr['next_qishu'];
        //取得
        $where = [
            'operator_id'  => $operator_id,
            't.lottery_id' => $lottery_id,
            't.qishu'      => $qishu
        ];
        if ($lottery['lottery_type_id'] == 8) {
            $where['t.wanfa_id'] = $wanfa_id;
        } else {
            $where['t.wanfa_pid'] = $wanfa_id;
        }
        $wanfa_detail_bet = $this->ettm_classic_bet_record_db->getBetCountGroup($where, 'wanfa_detail_id');
        $wanfa_detail_bet = array_column($wanfa_detail_bet, null, 'wanfa_detail_id');
        //玩法詳情
        $list = [];
        if ($lottery['lottery_type_id'] == 8) { //六合彩系列
            $result = $this->ettm_classic_wanfa_detail_db->oddsCalculation($lottery_id, $qishu, 0, [], ['operator_id' => $operator_id,'wanfa_id'=>$wanfa_id], 1);
            //統計相對注區總額
            $relative = [];
            foreach ($result as $key => $row) {
                $formula = json_decode($row['formula'], true);
                $relative[$formula['type']] = isset($relative[$formula['type']]) ? $relative[$formula['type']]:0;
                $total = isset($wanfa_detail_bet[$row['id']]['total_p_value']) ? $wanfa_detail_bet[$row['id']]['total_p_value']:0;
                $relative[$formula['type']] += $total;
            }

            $data = [];
            foreach ($result as $key => $row) {
                $formula = json_decode($row['formula'], true);
                //計算總額
                $total = isset($wanfa_detail_bet[$row['id']]['total_p_value']) ? $wanfa_detail_bet[$row['id']]['total_p_value']:0;
                //計算盈虧
                $c_value = isset($wanfa_detail_bet[$row['id']]['tmp_c_value']) ? $wanfa_detail_bet[$row['id']]['tmp_c_value']:0;
                if ($statistics == 0) {
                    if (in_array($wanfa_id, [197,198,199,201,202,204,205,207])) {
                        $profit = '-';
                    } else {
                        $profit = bcsub($relative[$formula['type']], $c_value);
                    }
                } else {
                    $profit = isset($wanfa_detail_bet[$row['id']]['profit']) ? $wanfa_detail_bet[$row['id']]['profit']:0;
                }
                //球號顏色
                $values = $row['values'];
                if (in_array($row['wanfa_id'], [184,185,186,187,188,189,190,192])) {
                    if (in_array((int)$row['values'], ettm_classic_wanfa_detail_model::$colorBall['r'])) {
                        $values = "<span class=\"ball_red\">$row[values]</span>";
                    }
                    if (in_array((int)$row['values'], ettm_classic_wanfa_detail_model::$colorBall['b'])) {
                        $values = "<span class=\"ball_blue\">$row[values]</span>";
                    }
                    if (in_array((int)$row['values'], ettm_classic_wanfa_detail_model::$colorBall['g'])) {
                        $values = "<span class=\"ball_green\">$row[values]</span>";
                    }
                }

                $zodiac = $this->ettm_classic_wanfa_detail_db->getZodiacNumber($qishu_arr['count_down']);
                $show_odds = $show_odds_special = 1;
                if (in_array($wanfa_id, [194,195])) {
                    if ($row['values'] == $zodiac[-1]) {
                        $show_odds = 0;
                    } else {
                        $show_odds_special = 0;
                    }
                } else {
                    if ((float)$row['odds_special'] == 0) {
                        $show_odds_special = 0;
                    }
                }

                $profit_color = '#000';
                if ($profit > 0) {
                    $profit_color = 'red';
                }
                if ($profit < 0) {
                    $profit_color = 'green';
                }

                $data[] = [
                    'id'                => (int)$row['id'],
                    'values'            => $row['values'],
                    'values_str'        => $values,
                    'interval'          => $row['interval'],
                    'odds'              => (float)$row['odds'],
                    'odds_special'      => (float)$row['odds_special'],
                    'total'             => $total,
                    'profit'            => (float)$profit,
                    'profit_color'      => $profit_color,
                    'sort'              => (int)$row['sort'],
                    'show_odds'         => $show_odds,
                    'show_odds_special' => $show_odds_special,
                ];
            }
            $data = $sort == 'sort' ? multi_array_sort($data, $sort, SORT_REGULAR, SORT_ASC):multi_array_sort($data, $sort, SORT_NUMERIC, SORT_DESC);
            //筆數少的單列顯示
            $count = count($data);
            foreach ($data as $key => $row) {
                if (in_array($wanfa_id, [197,198,199,201,202,204,205,207])) {
                    $list[$key][0] = $row;
                } else {
                    $list[$key % ceil($count / 3)][] = $row;
                }
            }
        } else {
            $result = $this->ettm_classic_wanfa_detail_db->oddsCalculation($lottery_id, $qishu, 0, [], ['operator_id' => $operator_id,'wanfa_pid'=>$wanfa_id], 1);
            //統計相對注區總額
            $relative = [];
            foreach ($result as $key => $row) {
                $formula = json_decode($row['formula'], true);
                $relative["$row[wanfa_id]-$formula[type]"] = isset($relative["$row[wanfa_id]-$formula[type]"]) ? $relative["$row[wanfa_id]-$formula[type]"]:0;
                $total = isset($wanfa_detail_bet[$row['id']]['total_p_value']) ? $wanfa_detail_bet[$row['id']]['total_p_value']:0;
                $relative["$row[wanfa_id]-$formula[type]"] += $total;
            }
            $data = [];
            foreach ($result as $key => $row) {
                $formula = json_decode($row['formula'], true);
                //計算總額
                $total = isset($wanfa_detail_bet[$row['id']]['total_p_value']) ? $wanfa_detail_bet[$row['id']]['total_p_value']:0;
                //計算盈虧
                $c_value = isset($wanfa_detail_bet[$row['id']]['tmp_c_value']) ? $wanfa_detail_bet[$row['id']]['tmp_c_value']:0;
                if ($statistics == 0) {
                    $profit = bcsub($relative["$row[wanfa_id]-$formula[type]"], $c_value);
                } else {
                    $profit = isset($wanfa_detail_bet[$row['id']]['profit']) ? $wanfa_detail_bet[$row['id']]['profit']:0;
                }

                $show_odds = $show_odds_special = 1;
                if ((float)$row['odds_special'] == 0) {
                    $show_odds_special = 0;
                }

                $profit_color = '#000';
                if ($profit > 0) {
                    $profit_color = 'red';
                }
                if ($profit < 0) {
                    $profit_color = 'green';
                }
                
                $data[] = [
                    'id'                => (int)$row['id'],
                    'values'            => $row['values'],
                    'values_str'        => $row['values'],
                    'interval'          => $row['interval'],
                    'odds'              => (float)$row['odds'],
                    'odds_special'      => (float)$row['odds_special'],
                    'total'             => $total,
                    'profit'            => (float)$profit,
                    'profit_color'      => $profit_color,
                    'sort'              => (int)$row['sort'],
                    'wanfa_id'          => (int)$row['wanfa_id'],
                    'name'              => $row['name'],
                    'show_odds'         => $show_odds,
                    'show_odds_special' => $show_odds_special,
                ];
            }
            $data = $sort == 'sort' ? multi_array_sort($data, $sort, SORT_REGULAR, SORT_ASC):multi_array_sort($data, $sort, SORT_NUMERIC, SORT_DESC);
            //排序完依玩法分類
            $list = [];
            foreach ($data as $row) {
                $list[$row['wanfa_id']]['name'] = $row['name'];
                $list[$row['wanfa_id']]['list'][] = $row;
            }
        }

        return $list;
    }

    private function _edit_odds($lottery_id, $operator_id)
    {
        $wanfa_detail_id = $this->input->post('wanfa_detail_id', true);
        $qishu = $this->input->post('qishu', true);
        $special = $this->input->post('special', true);
        $interval = $this->input->post('interval', true);
        $adject_odds = $this->input->post('adject_odds', true);
        $odds_field = $special == 1 ? 'odds_special':'odds';
        
        //調整賠率
        $this->ettm_classic_odds_control_db->setAdject($operator_id, $lottery_id, $qishu, $wanfa_detail_id, $special, $interval, $adject_odds);
        //賠率運算
        $result = $this->ettm_classic_wanfa_detail_db->oddsCalculation($lottery_id, $qishu, 0, [], ['operator_id' => $operator_id,'wanfa_detail_id'=>$wanfa_detail_id], 1);

        return [
            'odds' => (float)$result[0][$odds_field]
        ];
    }

    /**
     * 寫入控制開獎號碼
     *
     * @param integer $lottery_id 彩種ID
     * @param integer $operator_id 營運商ID
     * @return array
     */
    private function _numbers($lottery_id, $operator_id)
    {
        $this->load->model('ettm_lottery_cheat_model', 'ettm_lottery_cheat_db');
        $qishu = $this->input->post('qishu', true);
        $numbers = $this->input->post('numbers', true);

        //獲取期數資訊
        $qishu_arr = $this->qishu_model->getQishu(1, $lottery_id);
        if ($qishu_arr['next_qishu'] != $qishu) {
            return [
                'code'    => 0,
                'message' => '该期数已过，请重新输入',
            ];
        }

        //寫入表格
        $this->ettm_lottery_cheat_db->insert([
            'operator_id' => $operator_id,
            'type'        => 2,
            'lottery_id'  => $lottery_id,
            'qishu'       => $qishu,
            'numbers'     => $numbers,
        ]);

        return [
            'code'    => 1,
            'message' => '写入完成!',
        ];
    }
}
