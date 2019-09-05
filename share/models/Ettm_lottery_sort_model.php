<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_lottery_sort_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_db_default = 'ettm_lottery_db';
        $this->load->model('ettm_lottery_model', $this->_db_default);

        $this->_table_default = $this->table_ . 'ettm_lottery';
        $this->_related_key = 'lottery_id';
        $this->_table_name = $this->table_ . 'ettm_lottery_sort';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'sort', 'label' => '排序', 'rules' => 'trim|required']
        ];
    }

    public function update($row, $return_string = false)
    {
        $data = $this->where([
            'operator_id' => $row['operator_id'],
            'lottery_id'  => $row['lottery_id'],
        ])->result_one();

        if ($data === null) {
            return parent::insert($row, $return_string);
        } else {
            $row['id'] = $data['id'];
            return parent::update($row);
        }
    }

    public function _do_where()
    {
        unset($this->_where['operator_id']);

        //預設排除刪除資料
        if (isset($this->_where['is_delete'])) {
            $this->db->where('default.is_delete', $this->_where['is_delete']);
            unset($this->_where['is_delete']);
        }

        if (isset($this->_where['key_word'])) {
            $this->db->where('default.key_word', $this->_where['key_word']);
            unset($this->_where['key_word']);
        }
        if (isset($this->_where['lottery_type_id'])) {
            $this->db->where('default.lottery_type_id', $this->_where['lottery_type_id']);
            unset($this->_where['lottery_type_id']);
        }
        if (isset($this->_where['name'])) {
            $this->db->like('default.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
        if (isset($this->_where['mode'])) {
            $this->db->where('default.mode &', $this->_where['mode']);
            unset($this->_where['mode']);
        }
        if (isset($this->_where['status'])) {
            $this->db->where('IF(t.status is null,default.status,t.status) =', $this->_where['status'], false);
            unset($this->_where['status']);
        }
        if (isset($this->_where['is_hot'])) {
            $this->db->where('IF(t.is_hot is null,default.is_hot,t.is_hot) =', $this->_where['is_hot'], false);
            unset($this->_where['is_hot']);
        }
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }
        if (isset($this->_where['lottery_ids'])) {
            $this->db->where_in('t.lottery_id', $this->_where['lottery_ids']);
            unset($this->_where['lottery_ids']);
        }
    }

    public function result_change()
    {
        $this->_do_action_change();
        $result = $this->db->select('default.status status_default')
                    ->join($this->_table_name . ' t', "default.id = t.lottery_id AND t.operator_id = $this->operator_id", 'left')
                    ->get($this->_table_default.' default')->result_array();
        $this->reset();
        return $result;
    }

    public static $statusList = [
        0 => '维护中',
        1 => '开启',
        //2 => '代开盘', //不顯示
    ];

    public static $is_hotList = [
        1 => '是',
        0 => '否',
    ];

    public static $hot_logoList = [
        1 => '是',
        0 => '否',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'          => '编号',
        'operator_id' => '运营商ID',
        'lottery_id'  => '彩种ID',
        'sort'        => '排序',
        'status'      => '状态',
        'is_hot'      => '是否为热门彩种',
        'hot_logo'    => '是否有Hot LOGO',
    ];
}
