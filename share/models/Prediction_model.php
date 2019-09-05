<?php defined('BASEPATH') || exit('No direct script access allowed');

class Prediction_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'prediction';
        $this->_key = 'id';
    }

    public function insert($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-getPredictionLottery");

        return parent::insert($row, $return_string);
    }

    public function update($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-getPredictionLottery");

        return parent::update($row, $return_string);
    }

    public function delete($id)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-getPredictionLottery");

        return parent::delete($id);
    }

    public function _do_where()
    {
        //預設排除刪除資料
        if (isset($this->_where['is_delete'])) {
            $this->db->where('t.is_delete', $this->_where['is_delete']);
            unset($this->_where['is_delete']);
        } else {
            $this->db->where('t.is_delete', 0);
        }
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }

        if (isset($this->_where['not_in'])) {
            foreach ($this->_where['not_in'] as $key => $arr) {
                $this->db->where_not_in($key, $arr);
            }
            unset($this->_where['not_in']);
        }
        return $this;
    }

    /**
     * 取得所有預測彩種
     *
     * @return array
     */
    public function getPredictionLottery()
    {
        if ($data = $this->cache->redis->get("$this->_table_name-getPredictionLottery")) {
            return $data;
        }
        
        $result = $this->select('t.lottery_id')->group('lottery_id')->result();
        $data = [];
        foreach ($result as $row) {
            $data[] = $row['lottery_id'];
        }

        $this->cache->redis->save("$this->_table_name-getPredictionLottery", $data, 86400);
        return $data;
    }

    /**
     * 取得預測號
     *
     * @param integer $prediction_id 預測ID
     * @return array
     */
    public function getValues($prediction_id)
    {
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
        $prediction = $this->row($prediction_id);

        if ($prediction['ball'] < 0) {
            return ettm_classic_wanfa_detail_model::$zodiacType;
        } else {
            $lottery = $this->ettm_lottery_db->row($prediction['lottery_id']);
            $ball = ettm_lottery_record_model::getLotteryTypeBall();
            $typeid = $lottery['lottery_type_id'];
            return $this->ettm_lottery_record_db->padLeft($typeid, $ball[$typeid]);
        }
    }

    /**
     * 取得該位置各預測號的玩法詳情ID
     *
     * @param integer $prediction_id 預測ID
     * @return array
     */
    public function getValuesWanfaDetailID($prediction_id)
    {
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
        $prediction = $this->row($prediction_id);
        //彩種資訊
        $lottery = $this->ettm_lottery_db->row($prediction['lottery_id']);
        //玩法整理
        $wanfa = $this->ettm_classic_wanfa_detail_db->where([
            't.lottery_type_id' => $lottery['lottery_type_id'],
        ])->result();
        $wanfa_arr = [];
        foreach ($wanfa as $row) {
            $wanfa_arr[$row['wanfa_id']][$row['id']] = $row['values'];
        }
        $wanfa_ids = explode(',', $prediction['wanfa_id']);
        $data = [];
        foreach ($wanfa_ids as $wanfa_id) {
            if (!isset($wanfa_arr[$wanfa_id])) {
                continue;
            }
            foreach ($wanfa_arr[$wanfa_id] as $wanfa_detail_id => $values) {
                $values = is_numeric($values) ? (int)$values: $values;
                $data[(string)$values][] = (int)$wanfa_detail_id;
            }
        }
        return $data;
    }

    public static $ballList = [
        -2 => '一肖',
        -1 => '特肖',
        1  => '第一球',
        2  => '第二球',
        3  => '第三球',
        4  => '第四球',
        5  => '第五球',
        6  => '第六球',
        7  => '第七球',
        8  => '第八球',
        9  => '第九球',
        10 => '第十球',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'         => '编号',
        'lottery_id' => '彩种',
        'wanfa_id'   => '玩法',
        'ball'       => '球号位置',
        'name'       => '名称',
        'price'      => '价格',
        'is_home'    => '显示首页順序',
        'sort'       => '排序',
    ];
}
