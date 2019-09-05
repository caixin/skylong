<?php defined('BASEPATH') || exit('No direct script access allowed');

class Ettm_special_model extends Base_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->is_action_log = true;
        $this->_table_name = $this->table_ . 'ettm_special';
        $this->_key = 'id';
    }

    public function rules()
    {
        return [
            ['field' => 'type', 'label' => '類型', 'rules' => 'trim|required'],
            ['field' => 'lottery_id', 'label' => '彩种ID', 'rules' => 'trim|required'],
            ['field' => 'key_word', 'label' => 'keyword', 'rules' => 'trim|required'],
        ];
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
        if (isset($this->_where['status'])) {
            $this->db->where('t.status', $this->_where['status']);
            unset($this->_where['status']);
        }
    }

    public function getList($lottery = [])
    {
        if ($lottery === []) {
            $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
            $lottery = $this->ettm_lottery_db->getLotteryList();
        }

        $result = $this->result();
        $data = [];
        foreach ($result as $row) {
            $data[$row['id']] = $lottery[$row['lottery_id']] . self::$typeList[$row['type']];
        }

        return $data;
    }

    /**
     * 牛的牌型輸贏
     * @param array $number_arr 開獎號碼
     * @return array 所有牌型輸贏
     */
    public function getNiuCard($number_arr)
    {
        if (count($number_arr) == 1) {
            return [];
        }
        $data = [];
        for ($i = 0; $i < count($number_arr); $i++) {
            for ($k = $i; $k < $i + 5; $k++) {
                $play[$i][] = $number_arr[$k];
            }
            $youniu = $this->getNN($play[$i]);
            $win = 0;
            if ($i > 0) {
                if ($youniu['point'] > $data[0]['point']) {
                    $win = 1;
                }
                if ($youniu['point'] == $data[0]['point']) {
                    $win = $this->niuPointJudge($data[0]['numbers'], $youniu['numbers']);
                }
            }

            $data[] = [
                'numbers'  => $youniu['numbers'],
                'point'    => $youniu['point'],
                'is_niu'   => $youniu['is_niu'],
                'is_win'   => $win,
                'multiple' => self::$niuMultiple[$youniu['point']],
            ];

            if ($i == 5) {
                break;
            }
        }
        return $data;
    }

    /**
     * 計算牛數
     * @param array $numbers 五張牌
     * @return array 牛數
     */
    public function getNN($numbers)
    {
        $niu = [];
        for ($i = 0; $i < count($numbers); $i++) {
            for ($j = 1; $j < count($numbers); $j++) {
                for ($k = 2; $k < count($numbers); $k++) {
                    if ($j <= $i || $k <= $j) {
                        continue;
                    }
                    if (($numbers[$i] + $numbers[$j] + $numbers[$k]) % 10 == 0) {
                        $niu = [
                            $numbers[$i],
                            $numbers[$j],
                            $numbers[$k],
                        ];
                    }
                }
            }
        }
        if (!empty($niu)) {
            $other = array_diff($numbers, $niu);
            $numbers = array_merge($niu, $other);
        }
        return [
            'numbers' => $numbers,
            'point'   => (!empty($niu) && (array_sum($numbers) % 10) == 0) ? 10 : (empty($niu) ? 0 : (array_sum($numbers) % 10)),
            'is_niu'  => empty($niu) ? 0 : 1,
        ];
    }

    /**
     * 牛數相同時比點數
     * @param array $gmPoker 庄家牌
     * @param array $gpPoker 閒家牌
     * @return int 0 = 閒家輸, 1 = 閒家贏
     */
    public function niuPointJudge($gmPoker, $gpPoker, $position = 0)
    {
        if ($position > 4) {
            return 0;
        }
        //庄閒排列，由大到小
        rsort($gmPoker);
        rsort($gpPoker);

        if ($gpPoker[$position] > $gmPoker[$position]) {
            return 1;
        } elseif ($gpPoker[$position] < $gmPoker[$position]) {
            return 0;
        } else {
            return $this->niuPointJudge($gmPoker, $gpPoker, $position + 1);
        }
    }

    public static $niuList = [
        0  => '没牛',
        1  => "牛一",
        2  => "牛二",
        3  => "牛三",
        4  => "牛四",
        5  => "牛五",
        6  => "牛六",
        7  => "牛七",
        8  => "牛八",
        9  => "牛九",
        10 => "牛牛",
    ];

    public static $niuBetValuesList = [
        0  => '庄家',
        1  => "闲一",
        2  => "闲二",
        3  => "闲三",
        4  => "闲四",
        5  => "闲五",
    ];

    public static $niuMultiple = [
        0 => 1,
        1 => 1,
        2 => 1,
        3 => 1,
        4 => 1,
        5 => 1,
        6 => 1,
        7 => 2,
        8 => 2,
        9 => 3,
        10 => 4,
    ];

    public static $typeList = [
        1 => '牛牛',
        2 => '抢庄牛牛',
    ];

    public static $statusList = [
        0  => '关闭',
        1  => '开启',
    ];

    public static $is_deleteList = [
        1 => '正常',
        0 => '已删除',
    ];

    //操作日誌欄位轉換
    public static $columnList = [
        'id'           => '编号',
        'type'         => '类型',
        'lottery_id'   => '彩种名称',
        'name'         => '玩法名称',
        'pic_icon'     => '图片logo',
        'key_word'     => 'Keyword',
        'jump_url'     => '链接',
        'commission'   => '抽水比例(%)',
        'banker_limit' => '庄家额度上限',
        'player_limit' => '闲家下注限额',
        'sort'         => '排序',
        'status'       => '状态',
        'is_delete'    => '是否删除',
    ];
}
