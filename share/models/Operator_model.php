<?php defined('BASEPATH') || exit('No direct script access allowed');

class Operator_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'operator';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'name', 'label' => '运营商名称', 'rules' => "trim|required"],
            ['field' => 'classic_adjustment', 'label' => '经典A盘调整', 'rules' => "trim|required"],
            ['field' => 'official_adjustment', 'label' => '官方A盘调整', 'rules' => "trim|required"],
        ];
    }

    /**
     * 新增
     *
     * @param array $row 新增資料
     * @param bool $return_string 回傳SQL字串
     * @return string
     */
    public function insert($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-getList-0");
        $this->cache->redis->delete("$this->_table_name-getList-1");

        return parent::insert($row, $return_string);
    }

    public function update($row, $return_string = false)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-getList-0");
        $this->cache->redis->delete("$this->_table_name-getList-1");

        return parent::update($row, $return_string);
    }

    public function delete($id)
    {
        //清除快取
        $this->cache->redis->delete("$this->_table_name-getList-0");
        $this->cache->redis->delete("$this->_table_name-getList-1");

        return parent::delete($id);
    }

    public function _do_where()
    {
        if (isset($this->_where['ids'])) {
            $this->db->where_in('t.id', $this->_where['ids']);
            unset($this->_where['ids']);
        }
        if (isset($this->_where['name'])) {
            $this->db->like('t.name', $this->_where['name'], 'both');
            unset($this->_where['name']);
        }
        if (isset($this->_where['domain_url'])) {
            $this->db->where("find_in_set('" . $this->_where['domain_url'] . "',t.domain_url) >", 0, false);
            unset($this->_where['domain_url']);
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
     * 取得營運商清單
     * @param int $all 是否回傳全部 0:只回傳有權限的 1:全部
     */
    public function getList($all = 1)
    {
        if ($result = $this->cache->redis->get("$this->_table_name-getList-$all")) {
            return $result;
        }

        $where['status'] = 1;
        if ($all == 0) {
            $where['ids'] = $this->session->userdata('show_operator');
        }
        $result = $this->where($where)->result();
        $result = array_column($result, 'name', 'id');
        
        $this->cache->redis->save("$this->_table_name-getList-$all", $result, 86400);
        return $result;
    }

    /**
     * 取得營運商資訊
     * @param string $domain
     */
    public function getOperator($domain='')
    {
        if ($domain == '') {
            $domain = $this->input->server('SERVER_NAME');
        }
        return $this->where([
            'status'     => 1,
            'domain_url' => $domain,
        ])->result_one();
    }

    /**
     * 取得預設營運商ID
     *
     * @return integer 營運商ID
     */
    public function getDefaultID()
    {
        $row = $this->order(['id','asc'])->result_one();
        return isset($row['id']) ? $row['id']:0;
    }

    //操作日誌欄位轉換
    public static $columnList = [
        'id'                  => '编号',
        'name'                => '运营商名称',
        'domain_url'          => '绑定网域',
        'classic_adjustment'  => '经典A盘调整',
        'official_adjustment' => '官方A盘调整',
    ];
}
