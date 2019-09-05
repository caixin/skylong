<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_reduce_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'ettm_reduce';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'operator_id', 'label' => '运营商ID', 'rules' => "trim|required"],
            ['field' => 'lottery_type_id', 'label' => '彩種大類', 'rules' => "trim|required"],
            ['field' => 'lottery_id', 'label' => '彩種', 'rules' => "trim|required|callback_unique_check"],
            ['field' => 'type', 'label' => '类型', 'rules' => "trim|required"],
        ];
    }

    public function insert($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-getReduce");

        return parent::insert($row, $return_string);
    }

    public function update($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-getReduce");

        return parent::update($row, $return_string);
    }

    public function delete($id)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-getReduce");

        return parent::delete($id);
    }

    public function _do_where()
    {
        if (isset($this->_where['operator_id'])) {
            $this->db->where('t.operator_id', $this->_where['operator_id']);
            unset($this->_where['operator_id']);
        } elseif ($this->is_login && $this->session->userdata('show_operator')) {
            $this->db->where_in("t.operator_id", $this->session->userdata('show_operator'));
        }

        if (isset($this->_where['lottery_type_id'])) {
            $this->db->where('t.lottery_type_id', $this->_where['lottery_type_id']);
            unset($this->_where['lottery_type_id']);
        }

        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }

        if (isset($this->_where['type'])) {
            $this->db->where('t.type', $this->_where['type']);
            unset($this->_where['type']);
        }
        return $this;
    }

    /**
     * 取得降賠
     *
     * @param integer $operator_id 營運商ID
     * @param integer $operator_id 彩種大類
     * @param integer $operator_id 彩種ID
     * @param integer $operator_id 類型 0:全部 1:個人
     * @return array 降賠資料
     */
    public function getReduce($operator_id, $lottery_type_id, $lottery_id, $type)
    {
        $key = "$operator_id-$lottery_type_id-$lottery_id-$type";
        
        if ($result = $this->cache->redis->get("$this->_table_name-getReduce")) {
            if (isset($result[$key])) {
                return $result[$key];
            }
        }
        
        $row = $this->where([
            'operator_id'     => $operator_id,
            'lottery_type_id' => $lottery_type_id,
            'lottery_id'      => $lottery_id,
            'type'            => $type,
        ])->result_one();
        if ($row === null) {
            $row = $this->where([
                'operator_id'     => $operator_id,
                'lottery_type_id' => $lottery_type_id,
                'lottery_id'      => 0,
                'type'            => $type,
            ])->result_one();
            if ($row === null) {
                $row = $this->where([
                    'operator_id'     => $operator_id,
                    'lottery_type_id' => 0,
                    'lottery_id'      => 0,
                    'type'            => $type,
                ])->result_one();
            }
        }
        
        if ($row === null) {
            $items = [];
        } else {
            $items = json_decode($row['items'], true);
        }

        $result[$key] = $items;
        $this->cache->redis->save("$this->_table_name-getReduce", $result, 86400);

        return $items;
    }

    public static $typeList = [
        0 => '全部赔率',
        1 => '个人赔率',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'              => '编号',
        'operator_id'     => '运营商ID',
        'lottery_type_id' => '彩种大类',
        'lottery_id'      => '彩种',
        'type'            => '类型',
        'items'           => '降赔项目',
    ];
}
