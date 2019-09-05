<?php defined('BASEPATH') || exit('No direct script access allowed');

class Module_operator_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_db_default = 'module_db';
        $this->load->model('module_model', $this->_db_default);

        $this->_table_default = $this->table_ . 'module';
        $this->_related_key = 'module_id';
        $this->_table_name = $this->table_ . 'module_operator';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'status', 'label' => '状态', 'rules' => 'trim|required'],
        ];
    }

    public function update($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("module-getEnable");

        $data = $this->where([
            'operator_id' => $row['operator_id'],
            'module_id'   => $row['module_id'],
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

        if (isset($this->_where['status'])) {
            $this->db->where('IF(t.status is null,default.status,t.status) =', $this->_where['status'], false);
            unset($this->_where['status']);
        }
        if (isset($this->_where['module_id'])) {
            $this->db->where('t.module_id', $this->_where['module_id']);
            unset($this->_where['module_id']);
        }
        if (isset($this->_where['module_ids'])) {
            $this->db->where_in('t.module_id', $this->_where['module_ids']);
            unset($this->_where['module_ids']);
        }
    }

    /**
     * 取得該運營商開啟的模組
     * @param int $operator_id 營運商ID
     */
    public function getEnable($operator_id)
    {
        if ($redis = $this->cache->redis->get("module-getEnable")) {
            if (isset($redis[$operator_id])) {
                return $redis[$operator_id];
            }
        }

        //預設啟用模組
        $result = $this->where([
            'status' => 1,
        ])->result_change();
        $enable = [];
        foreach ($result as $row) {
            $enable[$row['default_id']] = [
                'id'      => $row['default_id'],
                'name'    => $row['name'],
                'keyword' => $row['keyword'],
                'param'   => (array)json_decode($row['param'], true),
            ];
        }

        $redis[$operator_id] = $enable;
        $this->cache->redis->save("module-getEnable", $redis, 86400);

        return $enable;
    }

    public static $statusList = [
        1 => '开启',
        0 => '关闭',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'          => '编号',
        'operator_id' => '运营商编号',
        'module_id'   => '模组ID',
        'status'      => '状态',
    ];
}
