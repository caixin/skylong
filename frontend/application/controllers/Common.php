<?php defined('BASEPATH') || exit('No direct script access allowed');

class Common extends CommonBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @OA\Post(
     *   path="/Common/indexTabAction",
     *   summary="首頁選項配置",
     *   tags={"Common"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="mode",
     *                   description="模式 1:瀏覽器 2:APP",
     *                   type="int",
     *                   example="1",
     *               ),
     *               required={"mode"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function indexTabAction()
    {
        try {
            $this->load->model('header_action_model', 'header_action_db');
            $mode = $this->input->post("mode");
            $mode = $mode === null ? 1 : $mode;
            $data = $this->header_action_db->select('title,icon,jump_url,status')->where(['mode' => $mode])->result();
            ApiHelp::response(1, 200, 'Success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/Common/webParam",
     *   summary="網站參數",
     *   tags={"Common"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="key_name",
     *                   description="參數名稱(可不帶)",
     *                   type="string",
     *                   example="default_agent_code",
     *               )
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function webParam()
    {
        try {
            $key_name = $this->input->post("key_name");
            $data = [];
            if ($key_name !== null && $key_name != '') {
                if (isset($this->site_config[$key_name])) {
                    $data[$key_name] = $this->site_config[$key_name];
                }
            } else {
                foreach (['web_title', 'default_agent_code', 'activity_register'] as $val) {
                    $data[$val] = $this->site_config[$val];
                }
            }
            ApiHelp::response(1, 200, 'Success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/Common/enableModule",
     *   summary="啟用模組",
     *   tags={"Common"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function enableModule()
    {
        try {
            ApiHelp::response(1, 200, 'Success', $this->module);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/Common/cnzz",
     *   summary="取得CNZZ網址",
     *   tags={"Common"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function cnzz()
    {
        try {
            $this->load->model('cnzz_model', 'cnzz_db');
            ApiHelp::response(1, 200, 'Success', $this->cnzz_db->getUrl());
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/Common/getWinningList",
     *   summary="中獎列表",
     *   tags={"Common"},
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
     *                   example="10",
     *               ),
     *               required={"type","page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getWinningList()
    {
        try {
            $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
            $this->load->model('user_money_log_model', 'user_money_log_db');
            $page = $this->input->post("page");
            $page = $page === null ? 1 : $page;
            $per_page = $this->input->post("per_page");
            $per_page = $per_page === null ? 10 : $per_page;
            $offset = ($page - 1) * $per_page;

            $lottery = array_column($this->ettm_lottery_db->where(['status' => 1])->result(), 'name', 'id');

            $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
            $join[] = [$this->table_ . 'ettm_lottery t2', 't.lottery_id = t2.id', 'left'];
            $where['type_in'] = [6, 12];
            $where['money_type'] = 0;
            $where['create_time1'] = date('Y-m-d H:i:s', time() - 7200);
            $result = $this->user_money_log_db->select('t.*,t1.user_name,t2.name lottery_name')->where($where)
                ->join($join)->order(['create_time', 'desc'])->limit([$offset, $per_page])->result();
            $data = [];
            $count = count($result);
            foreach ($result as $row) {
                $data[] = [
                    'user_name' => substr_replace($row['user_name'], '*****', 2),
                    'game'      => $row['lottery_name'],
                    'win'       => number_format($row['money_add'], 2),
                ];
                //假資料
                if ($count < $per_page && rand(1, 10) % 3 == 0) {
                    $data[] = [
                        'user_name' => GetRandStr(2) . '*****',
                        'game'      => $lottery[array_rand($lottery)],
                        'win'       => number_format(rand(1000, 200000) / 100, 2),
                    ];
                    $count++;
                }
            }
            //補齊筆數
            for ($i = count($data); $i < $per_page; $i++) {
                $data[] = [
                    'user_name' => GetRandStr(2) . '*****',
                    'game'      => $lottery[array_rand($lottery)],
                    'win'       => number_format(rand(1000, 200000) / 100, 2),
                ];
            }

            ApiHelp::response(1, 200, 'Success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/common/customerService",
     *   summary="客服列表",
     *   tags={"Common"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="type",
     *                   description="類別 1:在線客服 2:微信 3:QQ",
     *                   type="int",
     *                   example="1",
     *               ),
     *               required={"type"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function customerService()
    {
        try {
            $this->load->model('customer_service_model', 'customer_service_db');
            $type = $this->input->post("type");
            $where['type'] = $type;
            $data = $this->customer_service_db->select('type,name,image_url,account')->where($where)->result();
            ApiHelp::response(1, 200, 'Success', $data);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/common/advertiseList",
     *   summary="廣告列表",
     *   tags={"Common"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="type",
     *                   description="類型 1:Wap首頁上方LOGO 2:wap彈窗廣告圖",
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
     *                   example="10",
     *               ),
     *               required={"type","page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function advertiseList()
    {
        try {
            $this->load->model('advertise_model', 'advertise_db');
            $type = $this->input->post("type");
            $page = $this->input->post("page");
            $page = $page === null ? 1 : $page;
            $per_page = $this->input->post("per_page");
            $per_page = $per_page === null ? 10 : $per_page;
            $offset = ($page - 1) * $per_page;

            $where['type'] = $type;
            $where['status'] = 1;

            $total = $this->advertise_db->where($where)->count();
            $result = $this->advertise_db->where($where)
                ->order(['sort', 'asc'])
                ->limit([$offset, $per_page])
                ->result();
            $list = [];
            foreach ($result as $row) {
                $list[] = [
                    'name'    => $row['name'],
                    'pic'     => $row['pic'],
                    'pic_url' => $row['pic_url'],
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
     *   path="/common/noticeList",
     *   summary="公告列表",
     *   tags={"Common"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="type",
     *                   description="類型 1:Wap系统公告 2:Wap跑马灯通知 11:PC跑马灯通知 12:PC首頁公告",
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
     *                   example="10",
     *               ),
     *               required={"type","page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function noticeList()
    {
        try {
            $this->load->model('notice_model', 'notice_db');
            $type = $this->input->post("type");
            $page = $this->input->post("page");
            $page = $page === null ? 1 : $page;
            $per_page = $this->input->post("per_page");
            $per_page = $per_page === null ? 10 : $per_page;
            $offset = ($page - 1) * $per_page;

            $where['type'] = $type;
            $where['status'] = 1;

            $total = $this->notice_db->where($where)->count();
            $result = $this->notice_db->where($where)
                ->order(['sort', 'asc'])
                ->limit([$offset, $per_page])
                ->result();
            $list = [];
            foreach ($result as $row) {
                $list[] = [
                    'name'    => $row['name'],
                    'content' => $row['content'],
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
     *   path="/common/activityList",
     *   summary="活動列表",
     *   tags={"Common"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="source",
     *                   description="來源 wap,pc,android,ios",
     *                   type="string",
     *                   example="wap",
     *                   enum={"wap","pc","android","ios"}
     *               ),
     *               @OA\Property(
     *                   property="pic_type",
     *                   description="類型 1:首頁輪播 2:活動頁(模板1) 3:活動頁(模板2)",
     *                   type="int",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="page",
     *                   description="頁數",
     *                   type="int",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="per_page",
     *                   description="一頁幾筆",
     *                   type="int",
     *                   example="10",
     *               ),
     *               required={"source","pic_type","page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function activityList()
    {
        try {
            $this->load->model('activity_model', 'activity_db');
            $source = strtolower($this->input->post("source"));
            $source = $source === null ? 'wap' : $source;
            $pic_type = $this->input->post("pic_type");
            $pic_type = $pic_type === null ? 1 : $pic_type;
            $page = $this->input->post("page");
            $page = $page === null ? 1 : $page;
            $per_page = $this->input->post("per_page");
            $per_page = $per_page === null ? 10 : $per_page;
            $offset = ($page - 1) * $per_page;

            $where["pic$pic_type <>"] = '';
            $where["pic{$pic_type}_show"] = 1;
            $where['type'] = activity_model::$sourceType[$source];
            $where['status'] = 1;

            $total = $this->activity_db->where($where)->count();
            $result = $this->activity_db->where($where)
                ->order(['sort', 'asc'])
                ->limit([$offset, $per_page])
                ->result();
            $list = [];
            foreach ($result as $row) {
                $list[] = [
                    'id'  => (int)$row['id'],
                    'pic' => $row["pic$pic_type"],
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
     *   path="/common/activityInfo",
     *   summary="活動詳情",
     *   tags={"Common"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="id",
     *                   description="活動ID",
     *                   type="string",
     *                   example="1",
     *               ),
     *               required={"id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function activityInfo()
    {
        try {
            $this->load->model('activity_model', 'activity_db');
            $id = $this->input->post('id');

            $row = $this->activity_db->row($id);
            if ($row === null) {
                throw new Exception("查无此活动", 400);
            }

            ApiHelp::response(1, 200, "success", [
                'id'          => $row['id'],
                'name'        => $row['name'],
                'content'     => $row['content'],
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/common/newsList",
     *   summary="文章列表",
     *   tags={"Common"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="type",
     *                   description="類型 1:經典玩法 2:官方玩法 3:特色玩法",
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
     *                   example="10",
     *               ),
     *               required={"type","page","per_page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function newsList()
    {
        try {
            $this->load->model('news_model', 'news_db');
            $type = $this->input->post("type");
            $page = $this->input->post("page");
            $page = $page === null ? 1 : $page;
            $per_page = $this->input->post("per_page");
            $per_page = $per_page === null ? 10 : $per_page;
            $offset = ($page - 1) * $per_page;

            $where['type'] = $type;
            $where['status'] = 1;

            $total = $this->news_db->where($where)->count();
            $result = $this->news_db->where($where)
                ->order(['sort', 'asc'])
                ->limit([$offset, $per_page])
                ->result();
            $list = [];
            foreach ($result as $row) {
                $list[] = [
                    'id'    => $row['id'],
                    'title' => $row['title'],
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
     *   path="/common/newsInfo",
     *   summary="文章詳情",
     *   tags={"Common"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="source",
     *                   description="來源 wap,pc,android,ios",
     *                   type="string",
     *                   example="wap",
     *                   enum={"wap","pc","android","ios"}
     *               ),
     *               @OA\Property(
     *                   property="id",
     *                   description="文章ID (id 和 category,lottery_id 兩者任選一種查詢)",
     *                   type="string",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="category",
     *                   description="類別 1:經典 2:官方",
     *                   type="int",
     *                   example="1",
     *               ),
     *               @OA\Property(
     *                   property="lottery_id",
     *                   description="彩種ID",
     *                   type="int",
     *                   example="1",
     *               ),
     *               required={"source"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function newsInfo()
    {
        try {
            $this->load->model('news_model', 'news_db');
            $this->load->model('ettm_lottery_model', 'ettm_lottery_db');
            $source = $this->input->post('source');
            $id = $this->input->post('id');
            $category = $this->input->post('category');
            $lottery_id = $this->input->post('lottery_id');

            if ($id == null || $id == '') {
                $row = $this->news_db->where([
                    'type'       => $category,
                    'lottery_id' => $lottery_id,
                ])->result_one();
            } else {
                $row = $this->news_db->row($id);
            }
            if ($row === null) {
                throw new Exception("查无此文章", 400);
            }

            $both = 0;
            if ($row['lottery_id'] > 0) {
                $lottery = $this->ettm_lottery_db->row($row['lottery_id']);
                if ($lottery['mode'] & 1 && $lottery['mode'] & 2) {
                    $both = 1;
                }
            }

            ApiHelp::response(1, 200, "success", [
                'title'   => $row['title'],
                'content' => htmlspecialchars_decode($source=='pc'?$row["content_pc"]:$row['content_wap']),
                'both'    => $both,
            ]);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/common/getCumulative",
     *   summary="累計派獎",
     *   tags={"Common"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getCumulative()
    {
        $money = 0;
        if (isset($this->site_config['cumulative'])) {
            $data = json_decode($this->site_config['cumulative'], true);
            $money = $data['money'];
            $timeUnit = floor((time() - $data['update_time']) / 60);
            if ($timeUnit > 0) {
                $money = (float)bcadd($money, bcmul($timeUnit, bcdiv(rand(30000, 50000), 100, 2), 2), 2);
                $this->sysconfig_db->where([
                    'operator_id' => $this->operator_id,
                    'varname'     => 'cumulative',
                ])->update_where([
                    'value' => json_encode(['money' => $money, 'update_time' => time()]),
                ]);
            }
        } else {
            $money = (float)bcadd(30000000, bcdiv(rand(30000000, 500000000), 100, 2), 2);
            $this->sysconfig_db->insert([
                'operator_id' => $this->operator_id,
                'varname'     => 'cumulative',
                'value'       => json_encode(['money' => $money, 'update_time' => time()]),
                'info'        => '累计派奖',
            ]);
        }
        ApiHelp::response(1, 200, 'success', $money);
    }

    /**
     * @OA\Post(
     *   path="/common/getApps",
     *   summary="取得APP下載",
     *   tags={"Common"},
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getApps()
    {
        try {
            $this->load->model('apps_model', 'apps_db');
            $apps = $this->apps_db->select('id, operator_id, type, name, jump_url, download_url')->where([
                'operator_id' => $this->operator_id,
                'is_vip'      => 0,
                'status'      => 1,
            ])->result();
            if (empty($apps)) {
                throw new Exception('安装包不存在', 300);
            }
            foreach ($apps as $key => $value) {
                $apps[$key]['type_name'] = apps_model::$typeList[$value['type']];
            }
            $apps = array_column($apps, null, 'type_name');
            ApiHelp::response(1, 200, "success", $apps);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/common/updateAppsDownloads",
     *   summary="累計APP安裝包下載次數",
     *   tags={"Common"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="apps_id",
     *                   description="应用ID",
     *                   type="int",
     *                   example="1",
     *               ),
     *               required={"page"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function updateAppsDownloads()
    {
        try {
            $this->load->model('apps_model', 'apps_db');
            // 取得应用ID
            $apps_id = intval($this->input->post("apps_id", true));
            if (empty($apps_id)) {
                throw new Exception('缺少必要参数', 300);
            }
            $apps = $this->apps_db->select('id, downloads')->where([
                'id'     => $apps_id,
                'status' => 1,
            ])->result_one();
            if (empty($apps)) {
                throw new Exception('安装包不存在', 300);
            }
            $this->apps_db->escape(false)->set(['downloads' => 'downloads + 1'])->update([
                'id' => $apps_id
            ]);
            ApiHelp::response(1, 200, "success");
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/common/writeUserVipInfoIosUdid?uid={uid}&token={token}",
     *   summary="寫入會員IOS的UDID(需用IOS手機測試)",
     *   tags={"Common"},
     *   @OA\Parameter(
     *       name="uid",
     *       in="path",
     *       description="會員ID",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *           example="34",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="token",
     *       in="path",
     *       description="會員ID金鑰",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *           example="f9ac1IkwFu3UjUSWPd8P3VxmqHDLVDi7SlH04-gYvg",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *       description="IOS回傳的XML",
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="application/xml",
     *           @OA\Schema(
     *               type="xml",
     *               example="<!DOCTYPE plist PUBLIC '-//Apple//DTD PLIST 1.0//EN' 'http://www.apple.com/DTDs/PropertyList-1.0.dtd'><plist version='1.0'><dict><key>IMEI</key><string>12 123456 123456 7</string><key>PRODUCT</key><string>iPhone8,1</string><key>UDID</key><string>b59769e6c28b73b1195009d4b21cXXXXXXXXXXXX</string><key>VERSION</key><string>15B206</string></dict></plist>"
     *           ),
     *       ),
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function writeUserVipInfoIosUdid()
    {
        try {
            $this->load->model('user_model', 'user_db');
            // 取得UID
            $uid = intval($this->input->get_post("uid", true));
            if (empty($uid)) {
                throw new Exception("请登录会员", 300);
            }
            // 取得token
            $token = $this->input->get_post("token", true);
            if (empty($token)) {
                throw new Exception('缺少金钥，请重新申请VIP', 300);
            }
            // 驗證token
            if ($uid != auth_code($token, "DECODE")) {
                throw new Exception('金钥出错，请重新申请VIP', 300);
            }

            // 取得UDID
            $data = file_get_contents('php://input');
            $plistBegin   = '<?xml version="1.0"';
            $plistEnd   = '</plist>';
            $pos1 = strpos($data, $plistBegin);
            $pos2 = strpos($data, $plistEnd);
            $data2 = substr($data, $pos1, $pos2 - $pos1);
            $xml = xml_parser_create();
            xml_parse_into_struct($xml, $data2, $vs);
            xml_parser_free($xml);
            $UDID = "";
            $iterator = 0;
            $arrayCleaned = [];
            foreach ($vs as $v) {
                if ($v['level'] == 3 && $v['type'] == 'complete') {
                    $arrayCleaned[] = $v;
                }
                $iterator++;
            }
            $data = "";
            $iterator = 0;
            foreach ($arrayCleaned as $elem) {
                $data .= "\n==" . $elem['tag'] . " -> " . $elem['value'] . "<br/>";
                switch ($elem['value']) {
                    case "UDID":
                        $UDID = $arrayCleaned[$iterator + 1]['value'];
                        break;
                }
                $iterator++;
            }
            if (empty($UDID)) {
                throw new Exception("UID：" . $uid . "，UDID不可为空", 300);
            }

            $user = $this->user_db->row($uid);
            $vip_info_ios = $user['vip_info_ios'];
            if (strpos($vip_info_ios, $UDID) !== false) {
                throw new Exception("此装置已申请过VIP", 300);
            }

            $vip_info_ios = json_decode($vip_info_ios, true);
            $vip_info_ios[] = ['udid' => $UDID, 'binding' => 0, 'prompt' => 0];
            $vip_info_ios = json_encode($vip_info_ios, JSON_UNESCAPED_UNICODE);
            $num = $this->user_db->update([
                'id'           => $uid,
                'vip_info_ios' => $vip_info_ios,
            ]);
            if ($num == 0) {
                throw new Exception('VIP申请失败', 300);
            }

            //發送Telegram訊息
            $bot = new \TelegramBot\Api\BotApi($this->config->item('telegram_bot_token'));
            $chatid = $this->config->item('telegram_chatid_'.ENVIRONMENT);
            $message = "{$this->operator['name']}-用戶：【$user[user_name]】已申請VIP，手機號碼：【$user[mobile]】，UDID：【{$UDID}】";
            $bot->sendMessage($chatid, $message);
        } catch (Exception $e) {
            Monolog::writeLogs('ios_vip', $e->getCode(), $e->getMessage());
        } finally {
            header('HTTP/1.1 301 Moved Permanently');
            header("Location: https://" . $this->input->server('SERVER_NAME') . "/wap/dist/#/MyInfo");
        }
    }

    /**
     * @OA\Post(
     *   path="/common/getOTP?token={token}",
     *   summary="取得登入後台所需的OTP",
     *   tags={"Common"},
     *   @OA\Parameter(
     *       name="token",
     *       in="path",
     *       description="金鑰",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *           example="ji3cl3gj94",
     *       ),
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function getOTP()
    {
        try {
            $this->load->model('backend/admin_otp_model', 'admin_otp_db');
            $token = $this->input->get_post('token');
            if (empty($token)) {
                throw new Exception('缺少金钥', 300);
            }
            // 驗證token
            if ($token != 'ji3cl3gj94') {
                throw new Exception('金钥出错', 300);
            }

            $this->admin_otp_db->where([
                'create_time <' => date('Y-m-d H:i:s', strtotime('-5 minute'))
            ])->delete_where();
            $result = $this->admin_otp_db->order(['create_time', 'desc'])->result();

            ApiHelp::response(1, 200, "success", $result);
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *   path="/common/redirectGame",
     *   summary="重新導向特色棋牌遊戲網址",
     *   tags={"Common"},
     *   @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *               type="object",
     *               @OA\Property(
     *                   property="special_id",
     *                   description="特色棋牌ID",
     *                   type="int",
     *                   example="1",
     *               ),
     *               required={"special_id"}
     *           )
     *       )
     *   ),
     *   @OA\Response(response="200", description="Success")
     * )
     */
    public function redirectGame()
    {
        try {
            $this->load->model('ettm_special_model', 'ettm_special_db');
            
            $special_id = $this->input->post("special_id", true);
            $row = $this->ettm_special_db->row($special_id);
            if ($row === null) {
                throw new Exception("查无此棋牌", 400);
            }
            
            $url = parse_url($row['jump_url']);
            $domain = $url['host'];
            //寫入cookie
            $this->input->set_cookie('cookie', $this->cookie, 86400 * 3, $domain);
            //轉址
            redirect("$row[jump_url]?gameType=$special_id");
        } catch (Exception $e) {
            ApiHelp::response(0, $e->getCode(), $e->getMessage());
        }
    }
}
