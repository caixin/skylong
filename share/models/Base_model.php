<?php defined('BASEPATH') || exit('No direct script access allowed');

class Base_Model extends CI_Model
{
    protected $table_ = 'bc_'; // 表的前缀
    protected $_db_default = '';
    protected $_table_default = '';
    protected $_related_key = '';
    public $_table_name = '';
    protected $_key = 'id';
    protected $_select = '*';
    protected $_where = [];
    protected $_or_where = [];
    protected $_join = [];
    protected $_order = [];
    protected $_group = '';
    protected $_having = [];
    protected $_limit = [];
    protected $_set = [];
    protected $_escape = true;
    public $is_action_log = false;

    public function __construct()
    {
        parent::__construct();
        $this->table_ = $this->db->table_pre;
    }

    public function setTable($table)
    {
        $this->_table_name = $table;
        return $this;
    }

    public function setKey($key)
    {
        $this->_key = $key;
        return $this;
    }

    public function get_compiled_select()
    {
        $this->_do_action();
        $sql = $this->db->get_compiled_select($this->_table_name . ' t');
        $this->reset();
        return $sql;
    }

    public function last_query()
    {
        return $this->db->last_query();
    }

    public function field_data()
    {
        return $this->db->field_data($this->_table_name);
    }

    public function list_fields()
    {
        return $this->db->list_fields($this->_table_name);
    }

    public function insert($row, $return_string = false)
    {
        $fields = $this->list_fields();
        $date = date('Y-m-d H:i:s');
        $data = $row;
        if (in_array('create_time', $fields)) {
            $data['create_time'] = $date;
        }
        if (in_array('create_by', $fields)) {
            $data['create_by'] = $this->session->userdata('username') === null ? '' : $this->session->userdata('username');
        }
        if (in_array('update_time', $fields)) {
            $data['update_time'] = $date;
        }
        if (in_array('update_by', $fields)) {
            $data['update_by'] = $this->session->userdata('username') === null ? '' : $this->session->userdata('username');
        }
        if (in_array('source', $fields)) {
            $data['source'] = $this->source == '' ? 'wap':$this->source;
        }
        if (in_array('platform', $fields)) {
            $data['platform'] = get_platform();
        }

        if ($return_string) {
            return $this->db->insert_string($this->_table_name, $data);
        } else {
            $this->db->insert($this->_table_name, $data);
            $id = $this->db->insert_id();
            //寫入操作日誌
            if ($this->is_action_log) {
                $sql_str = $this->db->insert_string($this->_table_name, $data);
                $message = $this->title . '(' . $this->getActionString($data) . ')';
                $this->admin_action_log_db->insert([
                    'sql_str' => $sql_str,
                    'message' => $message,
                    'status'  => $id > 0 ? 1 : 0,
                ]);
            }
            return $id;
        }
    }

    public function insert_batch($data)
    {
        $affected_rows = $this->db->insert_batch($this->_table_name, $data);
        //寫入操作日誌
        if ($this->is_action_log) {
            $sql_str = $this->db->last_query();
            $message = $this->title . '(' . $this->getActionString_batch($data) . ')';
            $this->admin_action_log_db->insert([
                'sql_str' => $sql_str,
                'message' => $message,
                'status'  => $this->trans_status() ? 1 : 0,
            ]);
        }
        return $affected_rows;
    }

    public function update($row, $return_string = false)
    {
        $fields = $this->list_fields();
        $date = date('Y-m-d H:i:s');
        $data = $row;
        if (in_array('update_time', $fields)) {
            $data['update_time'] = $date;
        }
        if (in_array('update_by', $fields)) {
            $data['update_by'] = $this->session->userdata('username');
        }
        unset($data[$this->_key]);

        if ($return_string) {
            if (!empty($this->_set)) {
                foreach ($this->_set as $key => $val) {
                    $this->db->set($key, $val, $this->_escape);
                }
            }
            return $this->db->update_string($this->_table_name, $data, [$this->_key => $row[$this->_key]]);
        } else {
            $origin = $this->row($row[$this->_key]);
            if (!empty($this->_set)) {
                foreach ($this->_set as $key => $val) {
                    $this->db->set($key, $val, $this->_escape);
                }
            }
            $this->reset();
            $this->db->update($this->_table_name, $data, [$this->_key => $row[$this->_key]]);
            $num = $this->db->affected_rows(); //影響列數
            //清除快取
            $this->cache->redis->delete("$this->_table_name-".$row[$this->_key]);
            
            //寫入操作日誌
            if ($this->is_action_log) {
                $sql_str = $this->db->update_string($this->_table_name, $row, [$this->_key => $row[$this->_key]]);
                $message = $this->title . '(' . $this->getActionString($row, $this->_array_diff($row, $origin)) . ')';
                $this->admin_action_log_db->insert([
                    'sql_str' => $sql_str,
                    'message' => $message,
                    'status' => $num > 0 ? 1 : 0,
                ]);
            }
            return $num;
        }
    }

    public function update_where($row=null)
    {
        $fields = $this->list_fields();
        $date = date('Y-m-d H:i:s');
        $data = $row;
        if (in_array('update_time', $fields)) {
            $data['update_time'] = $date;
        }
        if (in_array('update_by', $fields)) {
            $data['update_by'] = $this->session->userdata('username');
        }
        unset($data[$this->_key]);

        $this->_do_action();
        $this->db->update($this->_table_name . ' t', $data);
        $num = $this->db->affected_rows(); //影響列數
        //清除快取
        $this->_set = [];
        $result = $this->result();
        foreach ($result as $row) {
            $this->cache->redis->delete("$this->_table_name-".$row[$this->_key]);
        }
        
        //寫入操作日誌
        if ($this->is_action_log) {
            $sql_str = $this->last_query();
            $message = $this->title . '(' . $sql_str . ')';
            $this->admin_action_log_db->insert([
                'sql_str' => $sql_str,
                'message' => $message,
                'status' => $num > 0 ? 1 : 0,
            ]);
        }
        $this->reset();
        return $num;
    }

    public function update_batch($data, $key)
    {
        $affected_rows = $this->db->update_batch($this->_table_name, $data, $key);
        //清除快取
        foreach ($data as $row) {
            $this->cache->redis->delete("$this->_table_name-".$row[$key]);
        }
        
        //寫入操作日誌
        if ($this->is_action_log) {
            $sql_str = $this->db->last_query();
            $message = $this->title . '(' . $this->getActionString_batch($data) . ')';
            $this->admin_action_log_db->insert([
                'sql_str' => $sql_str,
                'message' => $message,
                'status'  => $this->trans_status() ? 1 : 0,
            ]);
        }
        return $affected_rows;
    }

    public function delete($id)
    {
        $data = $this->row($id);
        $this->db->where($this->_key, $id)->delete($this->_table_name);
        $num = $this->db->affected_rows(); //影響列數
        //清除快取
        $this->cache->redis->delete("$this->_table_name-$id");

        //寫入操作日誌
        if ($this->is_action_log) {
            $sql_str = $this->last_query();
            $message = $this->title . '(' . $this->getActionString($data) . ')';
            $this->admin_action_log_db->insert([
                'sql_str' => $sql_str,
                'message' => $message,
                'status' => $num > 0 ? 1 : 0,
            ]);
        }
        return $num;
    }

    public function delete_where()
    {
        $this->_do_action(false);
        $this->db->delete($this->_table_name);
        $num = $this->db->affected_rows(); //影響列數

        //寫入操作日誌
        if ($this->is_action_log) {
            $sql_str = $this->last_query();
            $message = $this->title . '(' . $sql_str . ')';
            $this->admin_action_log_db->insert([
                'sql_str' => $sql_str,
                'message' => $message,
                'status' => $num > 0 ? 1 : 0,
            ]);
        }
        $this->reset();
        return $num;
    }

    public function truncate()
    {
        $this->db->truncate($this->_table_name);
    }

    public function query($sql)
    {
        return $this->db->query($sql);
    }

    public function trans_start()
    {
        return $this->db->trans_start();
    }

    public function trans_complete()
    {
        return $this->db->trans_complete();
    }

    public function trans_status()
    {
        return $this->db->trans_status();
    }

    public function trans_begin()
    {
        return $this->db->trans_begin();
    }

    public function trans_commit()
    {
        return $this->db->trans_commit();
    }

    public function trans_rollback()
    {
        return $this->db->trans_rollback();
    }

    public function table_exists($table)
    {
        return $this->db->table_exists($table);
    }

    public function escape_str($str)
    {
        return $this->db->escape_str($str);
    }

    public function escape($bool)
    {
        $this->_escape = $bool;
        return $this;
    }

    public function select($data)
    {
        $this->_select = $data;
        return $this;
    }

    public function where($data)
    {
        $this->_where = $data;
        return $this;
    }

    public function or_where($data)
    {
        $this->_or_where = $data;
        return $this;
    }

    public function join($data)
    {
        $this->_join = $data;
        return $this;
    }

    public function group($data)
    {
        $this->_group = $data;
        return $this;
    }

    public function having($data)
    {
        $this->_having = $data;
        return $this;
    }

    public function order($data)
    {
        $this->_order = $data;
        return $this;
    }

    public function limit($data)
    {
        $this->_limit = $data;
        return $this;
    }

    public function set($data)
    {
        $this->_set = $data;
        return $this;
    }

    public function reset()
    {
        $this->_select = '*';
        $this->_where = [];
        $this->_or_where = [];
        $this->_join = [];
        $this->_group = '';
        $this->_having = [];
        $this->_order = [];
        $this->_limit = [];
        $this->_set = [];
        $this->_escape = true;
        return $this;
    }

    public function _do_where()
    {
        return $this;
    }

    public function _do_action($alias=true)
    {
        $this->db->reset_query();
        $this->db->select($this->_select, $this->_escape);

        if (!empty($this->_group)) {
            $this->db->group_by($this->_group, $this->_escape);
        }

        if (!empty($this->_order)) {
            if (isset($this->_order[0])) {
                $this->db->order_by($this->_order[0], $this->_order[1]);
            } else {
                foreach ($this->_order as $key => $val) {
                    $this->db->order_by($key, $val);
                }
            }
        }

        if (!empty($this->_limit)) {
            $this->db->limit($this->_limit[1], $this->_limit[0]);
        }

        if (!empty($this->_join)) {
            foreach ($this->_join as $join) {
                $this->db->join($join[0], $join[1], $join[2]);
            }
        }

        if ($alias) {
            $this->_do_where();
        }
        if (!empty($this->_where)) {
            if (isset($this->_where['like'])) {
                foreach ($this->_where['like'] as $arr) {
                    $this->db->like($arr[0], $arr[1], $arr[2]);
                }
                unset($this->_where['like']);
            }
            foreach ($this->_where as $key => $val) {
                if (is_array($val)) {
                    $this->db->where_in($key, $val);
                } else {
                    if ($val === null) {
                        $this->db->where($key, $val, $this->_escape);
                    } else {
                        $this->db->where($key, $val);
                    }
                }
            }
        }

        if (!empty($this->_or_where)) {
            if (isset($this->_or_where['like'])) {
                foreach ($this->_or_where['like'] as $arr) {
                    $this->db->or_like($arr[0], $arr[1], $arr[2]);
                }
                unset($this->_or_where['like']);
            }
            foreach ($this->_or_where as $key => $val) {
                if (is_array($val)) {
                    $this->db->where_in($key, $val);
                } else {
                    if ($val === null) {
                        $this->db->where($key, $val, $this->_escape);
                    } else {
                        $this->db->where($key, $val);
                    }
                }
            }
        }

        if (!empty($this->_set)) {
            foreach ($this->_set as $key => $val) {
                $this->db->set($key, $val, $this->_escape);
            }
        }

        if (!empty($this->_having)) {
            $this->db->having($this->_having);
        }

        return $this;
    }

    public function row($id)
    {
        if ($row = $this->cache->redis->get("$this->_table_name-$id")) {
            return $row;
        }

        $this->db->reset_query();
        $row = $this->db->where('t.'.$this->_key, $id)
                ->get($this->_table_name.' t')->row_array();
        
        $this->cache->redis->save("$this->_table_name-$id", $row, 1800);
        return $row;
    }

    public function result_one()
    {
        $this->_do_action();
        $row = $this->db->limit(1)->get($this->_table_name . ' t')->row_array();
        $this->reset();
        return $row;
    }

    public function result()
    {
        $this->_do_action();
        $result = $this->db->get($this->_table_name . ' t')->result_array();
        $this->reset();
        return $result;
    }

    public function count()
    {
        $this->_do_action();
        $count = $this->db->count_all_results($this->_table_name . ' t');
        $this->reset();
        return $count;
    }
    
    public function _do_action_change()
    {
        $this->db->reset_query();
        $self_fields = $this->list_fields();
        if ($this->_select != '*') {
            $self_fields = explode(',', $this->_select);
            $self_fields = array_map('trim', $self_fields);
        }
        $fields = $this->{$this->_db_default}->list_fields();

        $select[] = "default.id default_id";
        foreach ($fields as $field) {
            if (in_array($field, $self_fields)) {
                $select[] = "IF(t.$field is null,default.$field,t.$field) $field";
            } else {
                $select[] = $field;
            }
        }

        $this->db->select(implode(',', $select));

        if (!empty($this->_group)) {
            $this->db->group_by($this->_group, $this->_escape);
        }

        if (!empty($this->_order)) {
            if (isset($this->_order[0])) {
                if (in_array($this->_order[0], $self_fields)) {
                    $sort = $this->_order[0];
                    if ($sort == 'id') {
                        $order = "default.id ".$this->_order[1];
                    } else {
                        $order = "IF(t.$sort is null,default.$sort,t.$sort) ".$this->_order[1];
                    }
                } else {
                    $order = $this->_order[0].' '.$this->_order[1];
                }
            } else {
                $order_arr = [];
                foreach ($this->_order as $key => $val) {
                    if (in_array($key, $self_fields)) {
                        if ($key == 'id') {
                            $order_arr[] = "default.id $val";
                        } else {
                            $order_arr[] = "IF(t.$key is null,default.$key,t.$key) $val";
                        }
                    } else {
                        $order_arr[] = "$key $val";
                    }
                }
                $order = implode(',', $order_arr);
            }
            $this->db->order_by($order);
        }

        if (!empty($this->_limit)) {
            $this->db->limit($this->_limit[1], $this->_limit[0]);
        }

        if (!empty($this->_join)) {
            foreach ($this->_join as $join) {
                $this->db->join($join[0], $join[1], $join[2]);
            }
        }

        $this->_do_where();
        if (!empty($this->_where)) {
            if (isset($this->_where['like'])) {
                foreach ($this->_where['like'] as $arr) {
                    $this->db->like($arr[0], $arr[1], $arr[2]);
                }
                unset($this->_where['like']);
            }
            foreach ($this->_where as $key => $val) {
                if (is_array($val)) {
                    $this->db->where_in($key, $val);
                } else {
                    if ($val === null) {
                        $this->db->where($key, $val, $this->_escape);
                    } else {
                        $this->db->where($key, $val);
                    }
                }
            }
        }

        if (!empty($this->_or_where)) {
            if (isset($this->_or_where['like'])) {
                foreach ($this->_or_where['like'] as $arr) {
                    $this->db->or_like($arr[0], $arr[1], $arr[2]);
                }
                unset($this->_or_where['like']);
            }
            foreach ($this->_or_where as $key => $val) {
                if (is_array($val)) {
                    $this->db->where_in($key, $val);
                } else {
                    if ($val === null) {
                        $this->db->where($key, $val, $this->_escape);
                    } else {
                        $this->db->where($key, $val);
                    }
                }
            }
        }

        if (!empty($this->_set)) {
            foreach ($this->_set as $key => $val) {
                $this->db->set($key, $val, $this->_escape);
            }
        }

        if (!empty($this->_having)) {
            $this->db->having($this->_having);
        }

        return $this;
    }

    public function row_change($id)
    {
        $this->db->reset_query();
        $self_fields = $this->list_fields();
        $fields = $this->{$this->_db_default}->list_fields();
        $select = [];
        foreach ($fields as $field) {
            if (in_array($field, $self_fields)) {
                $select[] = "IF(t.$field is null,default.$field,t.$field) $field";
            } else {
                $select[] = $field;
            }
        }
        return $this->db->select(implode(',', $select))
                ->join($this->_table_name . ' t', "default.id = t.$this->_related_key AND t.operator_id = $this->operator_id", 'left')
                ->where('default.' . $this->_key, $id)
                ->get($this->_table_default.' default')->row_array();
    }

    public function result_one_change()
    {
        $this->_do_action_change();
        $row = $this->db->join($this->_table_name . ' t', "default.id = t.$this->_related_key AND t.operator_id = $this->operator_id", 'left')
                ->limit(1)->get($this->_table_default.' default')->row_array();
        
        $this->reset();
        return $row;
    }

    public function result_change()
    {
        $this->_do_action_change();
        $result = $this->db->join($this->_table_name . ' t', "default.id = t.$this->_related_key AND t.operator_id = $this->operator_id", 'left')
                    ->get($this->_table_default.' default')->result_array();
        $this->reset();
        return $result;
    }

    public function count_change()
    {
        $this->_do_action_change();
        $count = $this->db->join($this->_table_name . ' t', "default.id = t.$this->_related_key AND t.operator_id = $this->operator_id", 'left')
                    ->count_all_results($this->_table_default.' default');
        $this->reset();
        return $count;
    }

    /**
     * 比對陣列差異
     */
    private function _array_diff($a1, $a2)
    {
        $ret = [];
        foreach ($a1 as $key => $val) {
            //主key強制寫入
            if ($key == $this->_key) {
                $ret[$key] = $val;
                continue;
            }

            if (isset($a2[$key])) {
                if ($val !== $a2[$key]) {
                    $ret[$key] = $val;
                }
            } else {
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    /**
     * 組成操作日誌字串
     */
    public function getActionString($data, $highlight = [])
    {
        $str = [];
        foreach ($data as $key => $val) {
            //判斷有無欄位
            if (isset(static::$columnList[$key])) {
                //判斷欄位有無靜態陣列
                if (isset(static::${"{$key}List"}[$val])) {
                    $val = static::${"{$key}List"}[$val];
                }

                if (isset($highlight[$key])) {
                    $str[] = '<font color="blue">' . static::$columnList[$key] . "=$val</font>";
                } else {
                    $str[] = static::$columnList[$key] . "=$val";
                }
            }
        }

        return implode(';', $str);
    }

    /**
     * 組成操作日誌字串(多筆)
     */
    public function getActionString_batch($result)
    {
        $return = [];
        foreach ($result as $data) {
            $str = [];
            foreach ($data as $key => $val) {
                //判斷有無欄位
                if (isset(static::$columnList[$key])) {
                    //判斷欄位有無靜態陣列
                    if (isset(static::${"{$key}List"}[$val])) {
                        $val = static::${"{$key}List"}[$val];
                    }

                    $str[] = static::$columnList[$key] . "=$val";
                }
            }
            $return[] = implode(';', $str);
        }

        return implode('<br>', $return);
    }

    public function methodExists($func)
    {
        return method_exists($this, $func);
    }

    /**
     * 取得獲利顯示顏色
     */
    public static function getProfitColor($money)
    {
        return self::$profitColor[$money > 0 ? 1:($money < 0 ? -1:0)];
    }

    public static $columnList = [];

    public static $sourceList = [
        'ios'     => 'iOS',
        'android' => 'Android',
        'wap'     => 'Wap',
        'pc'      => 'PC',
    ];

    public static $platformList = [
        0 => 'Windows',
        1 => 'Android',
        2 => 'IOS',
    ];

    public static $profitColor = [
        -1 => 'red',
        0  => '#000',
        1  => 'green',
    ];
}
