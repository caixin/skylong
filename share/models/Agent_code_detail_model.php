<?php defined('BASEPATH') || exit('No direct script access allowed');

class Agent_code_detail_model extends Base_model
{
    public function __construct()
    {
        parent::__construct();

        $this->_table_name = $this->table_ . 'agent_code_detail';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'lottery_id', 'label' => '彩种ID', 'rules' => "trim|required"],
        ];
    }

    public function delete($code)
    {
        return $this->db->where('code', $code)->delete($this->_table_name);
    }

    public function _do_where()
    {
        if (isset($this->_where['lottery_id'])) {
            $this->db->where('t.lottery_id', $this->_where['lottery_id']);
            unset($this->_where['lottery_id']);
        }

        if (isset($this->_where['code'])) {
            $this->db->where('t.code', $this->_where['code']);
            unset($this->_where['code']);
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
     * 查詢返點設定
     * @param string $code 邀請碼
     */
    public function getCodeSetting($code)
    {
        $join[] = [$this->table_ . 'ettm_lottery t1', 't.lottery_id = t1.id', 'left'];
        $result = $this->select('t.id,t.lottery_id,t1.name lottery_name,t.return_point')
            ->where(['t.code' => $code])->join($join)->result();

        return $result;
    }

    /**
     * 寫入新彩種代理反點-預設複製湖南快10
     *
     * @param int $lottery_id 彩種ID
     * @param int $copy_id 複製的彩種ID
     */
    public function setNewLottery($lottery_id, $copy_id=1)
    {
        $result = $this->where(['lottery_id'=>1])->result();
        $insert = [];
        foreach ($result as $row) {
            unset($row['id']);
            $row['lottery_id'] = $lottery_id;
            $insert[] = $row;
        }
        $this->insert_batch($insert);
    }

    public static $typeList = [
        1 => '代理',
        2 => '玩家',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'                 => '编号',
        'code'               => '代理邀请码',
        'lottery_id'         => '彩种ID',
        'return_point'       => '返点',
    ];
}
