<?php defined('BASEPATH') || exit('No direct script access allowed');

class Agent_code_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'agent_code';
        $this->_key = 'code';
    }

    public function rules()
    {
        return [
            ['field' => 'type', 'label' => '类型', 'rules' => "trim|required"],
            ['field' => 'return_point', 'label' => '返点资料', 'rules' => "trim|required"],
        ];
    }

    public function insert($row, $return_string = false)
    {
        while (true) {
            $row['code'] = (string)rand(1000000, 9999999);
            $arr = $this->where(['code' => $row['code']])->result_one();
            if ($arr === null) {
                break;
            }
        }
        $return_point = $row['return_point'];
        unset($row['return_point']);
        parent::insert($row, $return_string);

        $date = date('Y-m-d H:i:s');
        //寫入返點明細
        $inserts = [];
        foreach ($return_point as $key => $val) {
            $inserts[] = [
                'code'         => $row['code'],
                'lottery_id'   => $key,
                'return_point' => $val,
                'create_time'  => $date,
            ];
        }
        $this->load->model('agent_code_detail_model', 'agent_code_detail_db');
        $this->agent_code_detail_db->insert_batch($inserts);

        return $row['code'];
    }

    public function delete($id)
    {
        $num = parent::delete($id);

        $this->load->model('agent_code_detail_model', 'agent_code_detail_db');
        $this->agent_code_detail_db->delete($id);

        return $num;
    }

    public function _do_where()
    {
        unset($this->_where['starttime'], $this->_where['endtime']);
        
        if (isset($this->_where['operator_id'])) {
            $this->db->where_in('t1.operator_id', [0,$this->_where['operator_id']]);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            //篩選運營商
            foreach ($this->_join as $arr) {
                if (strpos($arr[0], $this->table_ . 'user ') !== false) {
                    $table = trim(str_replace($this->table_ . 'user ', '', $arr[0]));
                    $this->db->where_in("$table.operator_id", $this->session->userdata('show_operator'));
                    break;
                }
            }
        }

        if (isset($this->_where['agent_id'])) {
            $this->db->where('t1.agent_id', $this->_where['agent_id']);
            unset($this->_where['agent_id']);
        }

        if (isset($this->_where['uid'])) {
            $this->db->where('t.uid', $this->_where['uid']);
            unset($this->_where['uid']);
        }

        if (isset($this->_where['user_name'])) {
            $this->db->where('t1.user_name', $this->_where['user_name']);
            unset($this->_where['user_name']);
        }

        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
        }

        if (isset($this->_where['code'])) {
            $this->db->where('t.code', $this->_where['code']);
            unset($this->_where['code']);
        }

        if (isset($this->_where['level'])) {
            $this->db->where('t.level', $this->_where['level']);
            unset($this->_where['level']);
        }

        if (isset($this->_where['create_time1'])) {
            $this->db->where('t.create_time >=', $this->_where['create_time1'] . ' 00:00:00');
            unset($this->_where['create_time1']);
        }

        if (isset($this->_where['create_time2'])) {
            $this->db->where('t.create_time <=', $this->_where['create_time2'] . ' 23:59:59');
            unset($this->_where['create_time2']);
        }
        return $this;
    }

    /**
     * 取得該玩家可設定的返點值
     * @param int $uid 用戶ID
     */
    public function getReturnPoint($uid)
    {
        $this->load->model('user_model', 'user_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('agent_code_detail_model', 'agent_code_detail_db');
        $user = $this->user_db->row($uid);
        //預設最大返點
        $lottery = $this->ettm_lottery_db->getLotteryList();
        $return_point = [];
        foreach ($lottery as $key => $val) {
            $return_point[$key] = $this->site_config['agent_return_point_max'];
        }
        //扣除上層返點
        while (true) {
            //上層無邀請碼則跳離
            if ($user['agent_code'] == '') {
                break;
            }
            $result = $this->agent_code_detail_db->where(['code' => $user['agent_code']])->result();
            foreach ($result as $row) {
                $return_point[$row['lottery_id']] = bcsub($return_point[$row['lottery_id']], $row['return_point'], 3);
            }
            $join = [];
            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $user = $this->select('t1.*')->join($join)->where(['t.code' => $user['agent_code']])->result_one();
        }

        return $return_point;
    }

    /**
     * 依UID,LotteryID取得所有上級返點
     * @param int $uid 用戶ID
     * @param int $lottery_id 彩種ID
     * @param array $data 返點資料
     */
    public function getUpReturnPoint($uid, $lottery_id, $data = [])
    {
        $join[] = [$this->table_ . 'agent_code t1', 't.agent_code = t1.code', 'left'];
        $join[] = [$this->table_ . 'agent_code_detail t2', "t.agent_code = t2.code AND t2.lottery_id = $lottery_id", 'left'];
        $row = $this->user_db->where(['t.id' => $uid])->select('t.agent_code,t1.uid,t1.type,t2.return_point')->join($join)->result_one();
        if ($row['agent_code'] == '') {
            return $data;
        }
        $data[$row['uid']] = $row['return_point'];
        return $this->getUpReturnPoint($row['uid'], $lottery_id, $data);
    }

    /**
     * 取得下級會員資料
     * @param int $uid 用戶ID
     */
    public function getSubDataByUID($uid)
    {
        $join[] = [$this->table_ . 'user t1', 't.code = t1.agent_code', 'right'];
        $result = $this->select('t.uid,t1.user_name,t1.money,t.type,t.code,t1.last_login_time,t1.last_active_time')
                    ->where(['t.uid' => $uid])->join($join)->result();
        return $result;
    }

    /**
     * 取得下級所有邀請碼
     * @param string $code 邀請碼
     * @param array $codes 下級邀請碼
     */
    public function getSubCode($code, $codes = [])
    {
        $codes[] = $code;
        $where['t1.agent_code'] = $code;
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->select('t.code')->where($where)->join($join)->group('t.code')->result();
        foreach ($result as $row) {
            $codes = $this->getSubCode($row['code'], $codes);
        }
        return $codes;
    }

    public static $typeList = [
        1 => '代理',
        2 => '玩家',
    ];

    public static $typeColor = [
        1 => 'red',
        2 => 'blue',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'   => '编号',
        'uid'  => '用户ID',
        'type' => '类型',
        'code' => '代理邀请码',
        'note' => '备注',
    ];
}
