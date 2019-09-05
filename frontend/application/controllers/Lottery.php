<?php defined('BASEPATH') || exit('No direct script access allowed');

class Lottery extends CommonBase
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ettm_lottery_type_model', 'ettm_lottery_type_db');
        $this->load->model('ettm_lottery_type_sort_model', 'ettm_lottery_type_sort_db');
        $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
        $this->load->model('ettm_lottery_sort_model', 'ettm_lottery_sort_db');
        $this->load->model('ettm_lottery_record_model', 'ettm_lottery_record_db');
        $this->load->model('ettm_classic_wanfa_detail_model', 'ettm_classic_wanfa_detail_db');
        $this->load->model('qishu_model');
    }

    /**
     * @OA\Post(
     *   path="/lottery/getLotteryList",
     *   summary="取得彩種列表",
     *   tags={"Lottery"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getLotteryList()
    {
        try {
            $result = $this->ettm_lottery_sort_db->order([
                'is_hot' => 'desc',
                'sort'   => 'asc'
            ])->result_change();
            $data = [];
            foreach ($result as $row) {
                $data[] = [
                    'lottery_type_id' => (int)$row['lottery_type_id'],
                    'lottery_id'      => (int)$row['default_id'],
                    'name'            => $row['name'],
                    'pic_icon'        => $row['pic_icon'],
                    'is_classic'      => $row['mode'] & 1 ? true : false,
                    'is_official'     => $row['mode'] & 2 ? true : false,
                    'is_hot'          => $row['is_hot'] == 1 ? true : false,
                    'hot_logo'        => $row['hot_logo'] == 1 ? true : false,
                ];
            }

            ApiHelp::response(1, 200, 'success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery/getWebLotteryList",
     *   summary="取得購彩大廳彩種列表",
     *   tags={"Lottery"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getWebLotteryList()
    {
        try {
            $type = $this->ettm_lottery_type_sort_db->order(['sort', 'asc'])->result_change();
            $lottery = $this->ettm_lottery_sort_db->order(['sort', 'asc'])->result_change();

            $hot = [];
            foreach ($lottery as $row) {
                if ($row['is_hot'] == 1) {
                    $hot[] = [
                        'type_id'      => (int)$row['lottery_type_id'],
                        'lottery_id'   => (int)$row['default_id'],
                        'lottery_name' => $row['name'],
                        'pic_icon'     => $row['pic_icon'],
                        'is_classic'   => $row['mode'] & 1 ? true : false,
                        'is_official'  => $row['mode'] & 2 ? true : false,
                        'hot_logo'     => $row['hot_logo'] == 1 ? true : false,
                    ];
                }
            }
            $data[0] = [
                'lottery_type_id' => 0,
                'name'            => '热门彩种',
                'pic_icon'        => 'https://cpdd.oss-cn-beijing.aliyuncs.com/syjingdiancai/lottery_hot.png',
                'data'            => $hot,
            ];
            foreach ($type as $typerow) {
                $data[$typerow['default_id']]['lottery_type_id'] = (int)$typerow['default_id'];
                $data[$typerow['default_id']]['name'] = $typerow['name'];
                $data[$typerow['default_id']]['pic_icon'] = $typerow['pic_icon'];
            }
            foreach ($lottery as $row) {
                $data[$row['lottery_type_id']]['data'][] = [
                    'type_id'      => (int)$row['lottery_type_id'],
                    'lottery_id'   => (int)$row['default_id'],
                    'lottery_name' => $row['name'],
                    'pic_icon'     => $row['pic_icon'],
                    'is_classic'   => $row['mode'] & 1 ? true : false,
                    'is_official'  => $row['mode'] & 2 ? true : false,
                    'hot_logo'     => $row['hot_logo'] == 1 ? true : false,
                ];
            }

            ApiHelp::response(1, 200, 'success', array_values($data));
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery/getIndexLotteryList",
     *   summary="取得首頁彩種倒數資訊",
     *   tags={"Lottery"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getIndexLotteryList()
    {
        try {
            $time = time();
            $data = [];
            $lottery = $this->ettm_lottery_sort_db->where([
                'mode' => 1,
            ])->order([
                'is_hot' => 'desc',
                'sort'   => 'asc'
            ])->limit([0, 7])->result_change();

            foreach ($lottery as $row) {
                $qishu_arr = $this->qishu_model->getQishu(1, $row['default_id']);
                $status = $row['status'] == 1 && $row['status_default'] == 1 ? 1:0;
                $count_down = $qishu_arr['count_down'] - $time;
                if ($row['lottery_type_id'] != 8 && ($time < $qishu_arr['day_start_time'] || $time > $qishu_arr['day_close_time'])) {
                    $status = 2;
                    $count_down = $qishu_arr['day_start_time'] - $time;
                    $count_down = $count_down < 0 ? $count_down + 86400 : $count_down;
                }
                $data['classic'][] = [
                    'category'   => 1,
                    'lottery_id' => (int)$row['default_id'],
                    'name'       => $row['name'],
                    'pic_icon'   => $row['pic_icon'],
                    "count_down" => $count_down,
                    'status'     => $status,
                    "close_time" => (int)$qishu_arr['adjust'],
                    'key_word'   => $row['key_word'],
                ];
            }

            $lottery = $this->ettm_lottery_sort_db->where([
                'mode' => 2,
            ])->order([
                'is_hot' => 'desc',
                'sort'   => 'asc'
            ])->limit([0, 7])->result_change();
            foreach ($lottery as $row) {
                $qishu_arr = $this->qishu_model->getQishu(2, $row['default_id']);
                $data['official'][] = [
                    'category'   => 2,
                    'lottery_id' => (int)$row['default_id'],
                    'name'       => $row['name'],
                    'pic_icon'   => $row['pic_icon'],
                    "count_down" => $qishu_arr['count_down'] - $time,
                    'status'     => (int)$row['status'],
                    "close_time" => 0,
                    'key_word'   => $row['key_word'],
                ];
            }

            ApiHelp::response(1, 200, 'success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery/getAllLotteryTypeList",
     *   summary="取得所有彩種倒數資訊",
     *   tags={"Lottery"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="category",
     *                   description="分類 0:全部 1:經典 2:官方",
     *                   type="string",
     *                   example="0",
     *               ),
     *               required={"category"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getAllLotteryTypeList()
    {
        try {
            $category = $this->input->post('category');
            if ($category === null) {
                $category = 0;
            }
            $time = time();
            $data['server_time'] = $time;
            if (in_array($category, [0, 1])) {
                $type = $this->ettm_lottery_type_sort_db->where([
                    'mode' => 1,
                ])->order(['sort', 'asc'])->result_change();
                $lottery = $this->ettm_lottery_sort_db->where([
                    'mode' => 1,
                ])->order(['sort', 'asc'])->result_change();

                $data['classic']['type'] = 'classic';
                $data['classic']['name'] = '经典彩票';
                foreach ($type as $typerow) {
                    $arr = [];
                    $arr['type'] = $typerow['name'];
                    $arr['sort'] = $typerow['sort'];
                    $arr['list'] = [];
                    foreach ($lottery as $row) {
                        if ($row['lottery_type_id'] != $typerow['default_id']) {
                            continue;
                        }
                        $qishu_arr = $this->qishu_model->getQishu(1, $row['default_id']);
                        $status = $row['status'] == 1 && $row['status_default'] == 1 ? 1:0;
                        $count_down = $qishu_arr['count_down'] - $time;
                        if ($row['lottery_type_id'] != 8 && ($time < $qishu_arr['day_start_time'] || $time > $qishu_arr['day_close_time'])) {
                            $status = 2;
                            $count_down = $qishu_arr['day_start_time'] - $time;
                            $count_down = $count_down < 0 ? $count_down + 86400 : $count_down;
                        }
                        $arr['list'][] = [
                            'category'   => 1,
                            'lottery_id' => (int)$row['default_id'],
                            'name'       => $row['name'],
                            'pic_icon'   => $row['pic_icon'],
                            "count_down" => $count_down,
                            'status'     => $status,
                            "close_time" => (int)$qishu_arr['adjust'],
                            'key_word'   => $row['key_word'],
                        ];
                    }
                    if ($arr['list'] != []) {
                        $data['classic']['data'][] = $arr;
                    }
                }
            }

            if (in_array($category, [0, 2])) {
                $type = $this->ettm_lottery_type_sort_db->where([
                    'mode' => 2,
                ])->order(['sort', 'asc'])->result_change();
                $lottery = $this->ettm_lottery_sort_db->where([
                    'mode' => 2,
                ])->order(['sort', 'asc'])->result_change();
                $data['official']['type'] = 'official';
                $data['official']['name'] = '官方彩票';
                foreach ($type as $typerow) {
                    $arr = [];
                    $arr['type'] = $typerow['name'];
                    $arr['sort'] = $typerow['sort'];
                    $arr['key_word'] = $typerow['key_word'];
                    $arr['list'] = [];
                    foreach ($lottery as $row) {
                        if ($row['lottery_type_id'] != $typerow['default_id']) {
                            continue;
                        }
                        $qishu_arr = $this->qishu_model->getQishu(2, $row['default_id']);
                        $arr['list'][] = [
                            'category'   => 2,
                            'lottery_id' => (int)$row['default_id'],
                            'name'       => $row['name'],
                            'pic_icon'   => $row['pic_icon'],
                            'count_down' => $qishu_arr['count_down'] - $time,
                            'status'     => (int)$row['status'],
                            'close_time' => 0,
                            'key_word'   => $row['key_word'],
                        ];
                    }
                    if ($arr['list'] != []) {
                        $data['official']['data'][] = $arr;
                    }
                }
            }

            ApiHelp::response(1, 200, 'success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery/getLotteryData",
     *   summary="取得彩種倒數資訊",
     *   tags={"Lottery"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="category",
     *                   description="分類 1:經典 2:官方",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="string",
     *                   example="14",
     *               ),
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getLotteryData()
    {
        try {
            $category = $this->input->post('category', true);
            $lottery_id = $this->input->post('lottery_id', true);
            $time = time();

            $lottery = $this->ettm_lottery_db->row($lottery_id);
            $lottery_sort = $this->ettm_lottery_sort_db->row_change($lottery_id);
            $qishu_arr = $this->qishu_model->getQishu($category, $lottery_id);

            $status = $lottery['status'] == 1 && $lottery_sort['status'] == 1 ? 1:0;
            $count_down = $qishu_arr['count_down'] - $time;
            if ($category == 1 && $lottery['lottery_type_id'] != 8 && ($time < $qishu_arr['day_start_time'] || $time > $qishu_arr['day_close_time'])) {
                $status = 2;
                $count_down = $qishu_arr['day_start_time'] - $time;
                $count_down = $count_down < 0 ? $count_down + 86400 : $count_down;
            }

            ApiHelp::response(1, 200, 'success', [
                'category'   => (int)$category,
                'lottery_id' => (int)$lottery['id'],
                'name'       => $lottery['name'],
                'pic_icon'   => $lottery['pic_icon'],
                "count_down" => $count_down,
                'status'     => $status,
                "close_time" => $category == 1 ? (int)$qishu_arr['adjust'] : 0,
                'key_word'   => $lottery['key_word'],
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery/getSpecialList",
     *   summary="特色棋牌列表",
     *   tags={"Lottery"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getSpecialList()
    {
        try {
            $this->load->model('ettm_special_model', 'ettm_special_db');

            $result = $this->ettm_special_db->result();
            $data = [];
            foreach ($result as $row) {
                $data[] = [
                    'id'       => (int)$row['id'],
                    'name'     => ettm_special_model::$typeList[$row['type']],
                    'pic_icon' => $row['pic_icon'],
                    'jump_url' => $row['jump_url'],
                    'status'   => (int)$row['status'],
                ];
            }

            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery/getCurrentPeriod",
     *   summary="彩種當前期數資訊",
     *   tags={"Lottery"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="category",
     *                   description="類別 1:經典 2:官方 3:特色",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="string",
     *                   example="1",
     *               ),
     *               required={"lottery_id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getCurrentPeriod()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'category', 'label' => '類別', 'rules' => 'trim|required'],
                ['field' => 'lottery_id', 'label' => '彩種ID', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }

            $category = $this->input->post("category");
            $lottery_id = $this->input->post("lottery_id");
            $lottery = $this->ettm_lottery_db->row($lottery_id);
            $lottery_sort = $this->ettm_lottery_sort_db->row_change($lottery_id);
            if ($lottery === null) {
                throw new Exception("请选择正确的彩种", 300);
            }

            $time = time();
            $qishu_arr = $this->qishu_model->getQishu($category, $lottery_id);

            $where['lottery_id'] = $lottery_id;
            $where['qishu'] = $qishu_arr['qishu'];
            $record = $this->ettm_lottery_record_db->where($where)->result_one();
            $status = $lottery['status'] == 1 && $lottery_sort['status'] == 1 ? 1:0;
            $count_down = $qishu_arr['count_down'] - $time;
            //經典關盤
            if ($category == 1 && $lottery['lottery_type_id'] != 8 && ($time < $qishu_arr['day_start_time'] || $time > $qishu_arr['day_close_time'])) {
                $status = 2;
                $count_down = $qishu_arr['day_start_time'] - $time;
                $count_down = $count_down < 0 ? $count_down + 86400 : $count_down;
            }

            $value_str = [];
            if ($record['status'] == 1) {
                $value_str = $this->ettm_lottery_record_db->getValue($lottery['lottery_type_id'], $record['numbers'], $record['lottery_time']);
            }

            $data = [
                'lottery_name'  => $lottery['name'],
                'lottery_logo'  => $lottery['pic_icon'],
                'qishu'         => (int)$record['qishu'], //當前期數
                'numbers'       => $this->ettm_lottery_record_db->padLeft($lottery['lottery_type_id'], $record['numbers']), //當前期數號碼
                'value_str'     => $value_str,
                'number_length' => ettm_lottery_record_model::$numberLength[$lottery['lottery_type_id']],
                'n_qishu'       => (int)$qishu_arr['next_qishu'], //下期期數
                'count_down'    => $count_down,
                'close_time'    => $category == 1 ? (int)$qishu_arr['adjust'] : 0,
                'status'        => $status,
            ];
            //彩種關閉時 刪除無用參數
            if ($status == 0) {
                unset($data['qishu'],$data['numbers'],$data['n_qishu'],$data['count_down'],$data['close_time']);
            }

            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery/lotteryNewOpen",
     *   summary="彩種最新開獎列表",
     *   tags={"Lottery"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="page",
     *                   description="頁數",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="per_page",
     *                   description="一頁幾筆",
     *                   type="string",
     *                   example="50",
     *               ),
     *               required={"page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function lotteryNewOpen()
    {
        try {
            $page = $this->input->post("page");
            $page = $page === null ? 1 : $page;
            $per_page = $this->input->post("per_page");
            $per_page = $per_page === null ? 999 : $per_page;
            $offset = ($page - 1) * $per_page;

            $where['status'] = 1;
            $max_sql = $this->ettm_lottery_record_db->escape(false)->select('MAX(id) id')->where($where)
                ->group('lottery_id')->get_compiled_select();

            $join[] = ["($max_sql) t1", 't.id = t1.id', 'inner'];
            $join[] = [$this->table_ . "ettm_lottery t2", 't.lottery_id = t2.id', 'left'];
            $join[] = [$this->table_ . "ettm_lottery_type t3", 't2.lottery_type_id = t3.id', 'left'];
            $result = $this->ettm_lottery_record_db->escape(false)
                ->select('t.*,t2.lottery_type_id,t2.name lottery_name')
                ->join($join)->order(['t3.sort'=>'asc','lottery_time'=>'desc'])->limit([$offset, $per_page])->result();

            $list = [];
            foreach ($result as $row) {
                $numbers = $this->ettm_lottery_record_db->padLeft($row['lottery_type_id'], $row['numbers']);
                $list[] = [
                    'lottery_type_id' => (int)$row['lottery_type_id'],
                    'lottery_id'      => (int)$row['lottery_id'],
                    'lottery_name'    => $row['lottery_name'],
                    'qishu'           => (int)$row['qishu'],
                    'numbers'         => explode(',', $numbers),
                    'open_time'       => $row['lottery_time'],
                    'value_str'       => $this->ettm_lottery_record_db->getValue($row['lottery_type_id'], $row['numbers'], $row['lottery_time']),
                ];
            }

            ApiHelp::response(1, 200, "success", $list);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery/lotteryOpenList",
     *   summary="彩種歷史開獎列表",
     *   tags={"Lottery"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="page",
     *                   description="頁數",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="per_page",
     *                   description="一頁幾筆",
     *                   type="string",
     *                   example="20",
     *               ),
     *               required={"page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function lotteryOpenList()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'lottery_id', 'label' => '彩種ID', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }

            $lottery_id = $this->input->post('lottery_id');
            $lottery = $this->ettm_lottery_db->row($lottery_id);
            if ($lottery === null) {
                throw new Exception("请选择正确的彩种", 300);
            }
            if ($lottery['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }
            $lottery_sort = $this->ettm_lottery_sort_db->where([
                't.operator_id' => $this->operator_id,
                't.lottery_id'  => $lottery_id,
            ])->result_one();
            if ($lottery_sort !== null && $lottery_sort['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }

            $page = $this->input->post("page");
            $page = $page === null ? 1 : $page;
            $per_page = $this->input->post("per_page");
            $per_page = $per_page === null ? 10 : $per_page;
            $offset = ($page - 1) * $per_page;

            $where['t.lottery_id'] = $lottery_id;
            $where['t.status'] = 1;

            $total = $this->ettm_lottery_record_db->where($where)->count();
            $join[] = [$this->table_ . "ettm_lottery t1", 't.lottery_id = t1.id', 'left'];
            $result = $this->ettm_lottery_record_db->select('t.*,t1.lottery_type_id')
                ->join($join)->where($where)->order(['qishu', 'desc'])
                ->limit([$offset, $per_page])
                ->result();
            $list = [];
            foreach ($result as $row) {
                $list[] = [
                    'id'        => (int)$row['id'],
                    'qishu'     => (int)$row['qishu'],
                    'numbers'   => $this->ettm_lottery_record_db->padLeft($row['lottery_type_id'], $row['numbers']),
                    'open_time' => $row['lottery_time'],
                    'value_str' => $this->ettm_lottery_record_db->getValue($row['lottery_type_id'], $row['numbers'], $row['lottery_time']),
                ];
            }

            ApiHelp::response(1, 200, "success", [
                'page'  => $page,
                'total' => $total,
                'list'  => $list,
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery/trendChart",
     *   summary="露珠走勢",
     *   tags={"Lottery"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="int",
     *                   example="1",
     *               ),
     *               required={"source","lottery_id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function trendChart()
    {
        try {
            $this->form_validation->set_rules([
                ['field' => 'lottery_id', 'label' => '彩種ID', 'rules' => 'trim|required'],
            ]);
            if ($this->form_validation->run() == false) {
                $error = strip_tags(validation_errors());
                if (empty($error)) {
                    $error = '该数据只能用post提交方式提交.';
                }
                throw new Exception($error, 400);
            }

            $lottery_id = $this->input->post('lottery_id');
            $lottery = $this->ettm_lottery_db->row($lottery_id);
            if ($lottery === null) {
                throw new Exception("请选择正确的彩种", 300);
            }
            if ($lottery['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }
            $lottery_sort = $this->ettm_lottery_sort_db->where([
                't.operator_id' => $this->operator_id,
                't.lottery_id'  => $lottery_id,
            ])->result_one();
            if ($lottery_sort !== null && $lottery_sort['status'] == 0) {
                throw new Exception("娱乐维护，请稍后再试！", 300);
            }
            $data = $this->ettm_lottery_record_db->trendChart($lottery_id);

            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/lottery/longBetList",
     *   summary="長龍投注",
     *   tags={"Lottery"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="random_count",
     *                   description="隨機幾筆",
     *                   type="int",
     *                   example="2",
     *               ),
     *               required={"random_count"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function longBetList()
    {
        try {
            $random_count = $this->input->post('random_count');
            $random_count = empty($random_count) ? 2 : $random_count;

            $time = time();
            $data = [];
            $lottery = $this->ettm_lottery_sort_db->where(['mode' => 1])->order(['rand()', ''])->result_change();
            foreach ($lottery as $row) {
                //彩種倒數資訊
                $qishu_arr = $this->qishu_model->getQishu(1, $row['default_id']);
                $status = $row['status'] == 1 && $row['status_default'] == 1 ? 1:0;
                $count_down = $qishu_arr['count_down'] - $time;
                if ($row['lottery_type_id'] != 8 && ($time < $qishu_arr['day_start_time'] || $time > $qishu_arr['day_close_time'])) {
                    $status = 2;
                    $count_down = $qishu_arr['day_start_time'] - $time;
                    $count_down = $count_down < 0 ? $count_down + 86400 : $count_down;
                }
                //彩種狀態已維護或待開盤時略過
                if ($status != 1) {
                    continue;
                }
                //撈出長龍
                if ($row['lottery_type_id'] == 8) {
                    $long = $this->ettm_lottery_record_db->getLongMk6($row['default_id'], [], 5);
                    foreach ($long as $key => $arr) {
                        $keyword = explode('-', $arr['keyword']);
                        if (!in_array($keyword[0], ['bigsmall', 'oddeven', 'longhu'])) {
                            unset($long[$key]);
                        }
                    }
                } else {
                    $long = $this->ettm_lottery_record_db->getLong($row['default_id'], [], 5);
                }
                //無資料略過
                if ($long == []) {
                    continue;
                }
                //隨機一注
                $long = $long[array_rand($long)];
                //找出長龍的玩法ID
                $trend = $this->ettm_classic_wanfa_detail_db->getTrendWanfaID($row['lottery_type_id'], 8);

                $wanfa = [];
                if (isset($trend[$long['keyword']])) {
                    $wanfa = $trend[$long['keyword']];
                }
                //無資料略過
                if ($wanfa == []) {
                    continue;
                }
                $wanfa_dealil_list = [];
                if ($this->is_userlogin()) {
                    $wanfa_detail = $this->ettm_classic_wanfa_detail_db->oddsCalculation($row['default_id'], $qishu_arr['next_qishu'], $this->uid);
                    $wanfa_detail = array_column($wanfa_detail, null, 'id');
                    foreach ($wanfa['related'] as $id) {
                        $wd = $wanfa_detail[$id];
                        $wanfa_dealil_list[] = [
                            'id'           => (int)$id,
                            'values'       => $wd['values'],
                            'odds'         => (float)$wd['odds'],
                            'odds_special' => (float)$wd['odds_special'],
                        ];
                    }
                }

                $data[] = [
                    'lottery_id'        => (int)$row['default_id'],
                    'lottery_name'      => $row['name'],
                    'lottery_logo'      => $row['pic_icon'],
                    'n_qishu'           => (int)$qishu_arr['next_qishu'],
                    'count_down'        => $count_down,
                    'close_time'        => (int)$qishu_arr['adjust'],
                    'wanfa_pid'         => (int)$wanfa['pid'],
                    'wanfa_id'          => (int)$wanfa['wanfa_id'],
                    'data_name'         => $long['name'],
                    'data_values'       => $long['type'],
                    'data_count'        => (int)$long['count'],
                    'wanfa_dealil_list' => $wanfa_dealil_list,
                ];

                //達到數量後跳離
                if (count($data) >= $random_count) {
                    break;
                }
            }

            ApiHelp::response(1, 200, "success", $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }
}
