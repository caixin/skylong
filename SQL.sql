CREATE TABLE bc_admin_level(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(50) NOT NULL DEFAULT '' COMMENT '名稱',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者',
)ENGINE=InnoDB COMMENT='系統帳號層級';

CREATE TABLE bc_admin(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username varchar(50) NOT NULL COMMENT '用戶名',
  password varchar(50) NOT NULL COMMENT '密碼',
  mobile varchar(12) NOT NULL COMMENT '手機號碼',
  roleid int(11) NOT NULL COMMENT '角色ID',
  login_ip varchar(16) NOT NULL COMMENT '登入IP',
  login_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '登入時間',
  login_count int(11) NOT NULL DEFAULT 0 COMMENT '登入次數',
  status tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態 1:開啟 0:關閉',
  otp_check tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否需要動態密碼',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者',
)ENGINE=InnoDB COMMENT='系統帳號';

CREATE TABLE bc_admin_nav(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  pid int(11) NOT NULL DEFAULT 0 COMMENT '父級ID',
  name varchar(50) NOT NULL COMMENT '導航名稱',
  url varchar(100) NOT NULL DEFAULT '' COMMENT 'URL路徑',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '階層路徑',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  status tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態 1:開啟 0:關閉',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者',
)ENGINE=InnoDB COMMENT='導航列表';

CREATE TABLE bc_admin_role(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  level INT(11) NOT NULL DEFAULT 6 COMMENT '層級',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名稱',
  allow_nav text NOT NULL COMMENT '權限',
  is_delete tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否刪除',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='系統帳號角色權限';

CREATE TABLE bc_admin_action_log(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  adminid int(11) NOT NULL DEFAULT 0 COMMENT 'adminid',
  url varchar(50) NOT NULL COMMENT 'URL',
  message text NOT NULL COMMENT '操作訊息',
  sql_str text NOT NULL COMMENT 'SQL指令',
  ip VARCHAR(16) NOT NULL DEFAULT '' COMMENT '登入IP',
  status tinyint(1) NOT NULL DEFAULT 0 COMMENT '狀態 0:失敗 1:成功',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者'
)ENGINE=InnoDB COMMENT='系統帳號操作LOG';

CREATE TABLE bc_admin_login_log(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  adminid int(11) NOT NULL DEFAULT 0 COMMENT 'adminid',
  ip VARCHAR(16) NOT NULL DEFAULT '' COMMENT '登入IP',
  status tinyint(1) NOT NULL DEFAULT 0 COMMENT '狀態 0:失敗 1:成功',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者'
)ENGINE=InnoDB COMMENT='系統帳號登入LOG';

CREATE TABLE bc_admin_session(
  adminid int(11) NOT NULL DEFAULT 0 PRIMARY KEY COMMENT 'adminid',
  username varchar(50) NOT NULL COMMENT '用戶名',
  session_id text NOT NULL COMMENT 'SessionID',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間'
)ENGINE=InnoDB COMMENT='系統帳號SESSION';

CREATE TABLE bc_ettm_lottery_type(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(50) NOT NULL COMMENT '彩種類別',
  pic_icon varchar(255) NOT NULL DEFAULT '' COMMENT 'icon',
  key_word varchar(50) NOT NULL DEFAULT '' COMMENT 'keyword',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='彩種分類';

CREATE TABLE bc_ettm_lottery_dayoff(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lotteryid int(11) NOT NULl COMMENT '彩種ID',
  dayoff datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '未開獎日期',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者',
)ENGINE=InnoDB COMMENT='彩種未開獎日期';

CREATE TABLE bc_ettm_lottery_record(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lotteryid int(11) NOT NULl COMMENT '彩種ID',
  qishu bigint(20) NOT NULL COMMENT '期數',
  numbers varchar(50) NOT NULL COMMENT '開獎號碼',
  status tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態 0:未開獎 1:已開獎',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者',
)ENGINE=InnoDB COMMENT='彩種開獎結果';

CREATE TABLE bc_ettm_lottery_record_change(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  operator_id int(11) NOT NULL COMMENT '營運商ID',
  record_id int(11) NOT NULL COMMENT '開獎結果ID',
  numbers varchar(50) NOT NULL COMMENT '替換的開獎號碼',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者',
)ENGINE=InnoDB COMMENT='自營彩-依營運商替換開獎結果';

CREATE TABLE bc_ettm_classic_wanfa(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lottery_type_id int(11) NOT NULl COMMENT '彩種類別ID',
  pid int(11) NOT NULL COMMENT '父級ID',
  name varchar(100) NOT NULL COMMENT '玩法名稱',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='經典玩法';

CREATE TABLE bc_ettm_classic_wanfa_detail(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lottery_type_id int(11) NOT NULl COMMENT '彩種類別ID',
  wanfa_id int(11) NOT NULL COMMENT '玩法ID',
  `values` varchar(100) NOT NULL COMMENT '玩法值',
  `values_sup` varchar(250) NOT NULL COMMENT '輔助玩法值',
  odds decimal(10,3) NOT NULL DEFAULT 0 COMMENT '賠率',
  odds_special decimal(10,3) NOT NULL DEFAULT 0 COMMENT '特殊賠率',
  qishu_max_money int(11) NOT NULL DEFAULT 50000 COMMENT '單期累積最大下注額',
  bet_max_money int(11) NOT NULL DEFAULT 10000 COMMENT '單期單注最大下注額',
  bet_min_money int(11) NOT NULL DEFAULT 1 COMMENT '單期單注最小下注額',
  max_number int(11) NOT NULL DEFAULT 0 COMMENT '玩法值選號上限',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  formula varchar(200) NOT NULL DEFAULT '' COMMENT '中獎公式',
  payload text COMMENT 'Payload',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='經典玩法詳細';

CREATE TABLE bc_ettm_classic_bet_record(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lottery_id int(11) NOT NULl COMMENT '彩種ID',
  qishu bigint(20) NOT NULL COMMENT '期數',
  uid int(11) NOT NULL COMMENT '用戶ID',
  wanfa_pid int(11) NOT NULL COMMENT '玩法父級ID',
  wanfa_id int(11) NOT NULL COMMENT '玩法ID',
  wanfa_detail_id int(11) NOT NULL COMMENT '玩法詳細ID',
  order_sn varchar(50) NOT NULL COMMENT '訂單號',
  p_value int(11) NOT NULL DEFAULT 0 COMMENT '下注額',
  c_value decimal(10,3) NOT NULL DEFAULT 0 COMMENT '賠付額',
  bet_number int(11) NOT NULL DEFAULT 1 COMMENT '注數',
  total_p_value int(11) NOT NULL DEFAULT 0 COMMENT '下注總額',
  odds decimal(10,3) NOT NULL DEFAULT 0 COMMENT '賠率',
  formula varchar(200) NOT NULL DEFAULT '' COMMENT '中獎公式',
  payload text COMMENT 'Payload',
  bet_values varchar(100) NOT NULL COMMENT '下注值-數字',
  bet_values_str varchar(200) NOT NULL COMMENT '下注值-字串',
  is_lose_win tinyint(2) NOT NULL DEFAULT 0 COMMENT '輸贏 0:輸 1:贏 2:平',
  is_code_amount tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否打碼 0:未打碼 1:已打碼',
  status tinyint(2) NOT NULL DEFAULT 0 COMMENT '狀態 -1:處理中 0:未結算 1:已結算 2:已退款',
  is_delete tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否刪除',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='經典下注表';

CREATE TABLE bc_ettm_official_wanfa(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lottery_type_id int(11) NOT NULl COMMENT '彩種類別ID',
  pid int(11) NOT NULL COMMENT '父級ID',
  name varchar(100) NOT NULL COMMENT '玩法名稱',
  max_odds decimal(10,3) NOT NULL DEFAULT 0 COMMENT '最大賠率',
  min_odds decimal(10,3) NOT NULL DEFAULT 0 COMMENT '最小賠率',
  max_return decimal(10,3) NOT NULL DEFAULT 0 COMMENT '最大返點',
  max_bet_number int(11) NOT NULL DEFAULT 1 COMMENT '最大注數',
  max_bet_multiple int(11) NOT NULL DEFAULT 1 COMMENT '最大倍數',
  max_bet_money decimal(10,3) NOT NULL DEFAULT 0 COMMENT '最投注額',
  key_word varchar(50) NOT NULL COMMENT '關鍵字',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='官方玩法';

CREATE TABLE bc_ettm_official_bet_record(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lottery_id int(11) NOT NULl COMMENT '彩種ID',
  qishu bigint(20) NOT NULL COMMENT '期數',
  uid int(11) NOT NULL COMMENT '用戶ID',
  wanfa_pid int(11) NOT NULL COMMENT '玩法父級ID',
  wanfa_id int(11) NOT NULL COMMENT '玩法ID',
  order_sn varchar(50) NOT NULL COMMENT '訂單號',
  p_value decimal(10,3) NOT NULL DEFAULT 0 COMMENT '下注額',
  c_value decimal(10,3) NOT NULL DEFAULT 0 COMMENT '賠付額',
  bet_number int(11) NOT NULL DEFAULT 1 COMMENT '注數',
  total_p_value decimal(10,3) NOT NULL DEFAULT 0 COMMENT '下注總額',
  odds decimal(10,3) NOT NULL DEFAULT 0 COMMENT '賠率',
  formula varchar(200) NOT NULL DEFAULT '' COMMENT '中獎公式',
  payload text COMMENT 'Payload',
  bet_values varchar(100) NOT NULL DEFAULT '' COMMENT '下注值-數字',
  bet_values_str varchar(200) NOT NULL DEFAULT 0 COMMENT '下注值-字串',
  return_point decimal(10,3) NOT NULL DEFAULT 0 COMMENT '返點',
  return_money decimal(10,3) NOT NULL DEFAULT 0 COMMENT '返水',
  bet_multiple int(11) NOT NULL DEFAULT 1 COMMENT '倍數值',
  is_lose_win tinyint(2) NOT NULL DEFAULT 0 COMMENT '輸贏 0:輸 1:贏 2:平',
  is_code_amount tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否打碼 0:未打碼 1:已打碼',
  status tinyint(2) NOT NULL DEFAULT 0 COMMENT '狀態 -1:處理中 0:未結算 1:已結算 2:已退款',
  is_delete tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否刪除',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='官方下注表';

CREATE TABLE bc_notice_type(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(50) NOT NULL COMMENT '公告類型名稱',
  sign varchar(5) NOT NULL COMMENT '公告標示',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='公告類型';

CREATE TABLE bc_notice(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  type_id int(11) NOT NULl COMMENT '公告類型',
  name varchar(50) NOT NULL COMMENT '公告名稱',
  content text NOT NULL COMMENT '公告内容',
  sort varchar(50) NOT NULL COMMENT '排序',
  status tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態 0:關閉 1:開啟',
  is_delete tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否刪除',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='公告列表';

CREATE TABLE bc_news_type(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  category tinyint(2) NOT NULl DEFAULT 1 COMMENT '類別 1:經典玩法 2:官方玩法 3:特色玩法 11:APP端文章 12:PC端文章',
  name varchar(50) NOT NULL COMMENT '文章類型名稱',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='文章類型';

CREATE TABLE bc_news(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  type_id int(11) NOT NULl COMMENT '文章類型',
  lottery_type_id int(11) NOT NULL DEFAULT 0 COMMENT '彩票玩法ID',
  title varchar(100) NOT NULL COMMENT '標題',
  introduce varchar(200) NOT NULL COMMENT '介绍',
  content text NOT NULL COMMENT '文章内容',
  jumpurl varchar(255) NOT NULL COMMENT '轉跳網址',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  status tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態 0:關閉 1:開啟',
  is_delete tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否刪除',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='文章列表';

CREATE TABLE bc_activity(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  type int(11) NOT NULL COMMENT '類型 1:Wap 2:PC',
  name varchar(50) NOT NULL COMMENT '活動名稱',
  content text NOT NULL COMMENT '活動内容',
  brief text NOT NULL COMMENT '活動簡介',
  pic varchar(255) NOT NULL COMMENT '圖片路徑',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  status tinyint(1) NOT NULL DEFAULT 1 COMMENT '狀態 0:關閉 1:開啟',
  is_delete tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否刪除',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='活動列表';

CREATE TABLE bc_user(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  operator_id int(11) NOT NULL DEFAULT 0 COMMENT '運營商ID',
  user_name varchar(50) NOT NULL COMMENT '用戶名',
  security_pwd varchar(50) NOT NULL COMMENT '用戶安全提現密碼',
  user_pwd varchar(50) NOT NULL COMMENT '用戶密碼',
  real_name varchar(50) NOT NULL COMMENT '姓名',
  mail varchar(100) NOT NULL COMMENT '信箱',
  mobile char(11) NOT NULL COMMENT '手機號碼',
  money decimal(14,3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '貨幣',
  money_frozen decimal(12,3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '凍結中貨幣',
  type tinyint(2) NOT NULL DEFAULT 0 COMMENT '用戶類型 0:會員用戶 1:試玩用戶 2:白名單用戶',
  pid int(11) NOT NULL DEFAULT 0 COMMENT '上層玩家ID(無限代理)',
  user_group_id int(11) NOT NULL DEFAULT 1 COMMENT '會員群組',
  code varchar(50) NOT NULL DEFAULT '' COMMENT '用戶邀請碼',
  code_use varchar(50) NOT NULL DEFAULT '' COMMENT '填寫邀請碼',
  status tinyint(2) NOT NULL DEFAULT 0 COMMENT '0:正常用戶 1:封號用戶 2:凍結用戶 3:標記用戶',
  remark text COMMENT '備註',
  ios_vip_info text COMMENT 'ios的vip包資訊',
  create_ip varchar(50) NOT NULL DEFAULT '' COMMENT '註冊IP',
  last_login_ip varchar(50) NOT NULL DEFAULT '' COMMENT '最後登入IP',
  last_login_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '最後登入時間',
  last_active_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '最後活動時間',
  unlock_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '帳號解鎖時間',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='用戶列表';

CREATE TABLE bc_user_bank(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uid int(11) NOT NULL COMMENT '用戶ID',
  bank_name varchar(100) NOT NULL COMMENT '銀行名稱',
  bank_account varchar(20) NOT NULL COMMENT '銀行帳號',
  bank_address varchar(200) NOT NULL COMMENT '銀行位址',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='用戶銀行綁定';

CREATE TABLE bc_user_group(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(50) NOT NULL COMMENT '群組名稱',
  max_extract_money int(11) NOT NULL DEFAULT 1 COMMENT '單次最大提現額度',
  min_extract_money int(11) NOT NULL DEFAULT 1 COMMENT '單次最小提現額度',
  remark varchar(50) NOT NULL COMMENT '備註',
  status tinyint(2) NOT NULL DEFAULT 1 COMMENT '狀態 0:關閉 1:開啟',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='用戶群組';

CREATE TABLE bc_user_login_log(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uid int(11) NOT NULL COMMENT '用戶ID',
  ip varchar(50) NOT NULL COMMENT '登入IP',
  platform tinyint(2) NOT NULL COMMENT '平台 0:Windows 1:Android 2:IOS',
  source_url varchar(200) NOT NULL COMMENT '登入網址',
  domain_url varchar(200) NOT NULL COMMENT '登入Domain',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間'
)ENGINE=InnoDB COMMENT='用戶登入LOG';

CREATE TABLE `bc_customer_service` (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '類別 0:在線客服 1:QQ 2:微信',
  `name` varchar(50) NOT NULL COMMENT '客服名稱',
  `image_url` varchar(300) NOT NULL COMMENT '圖片路徑',
  `account` varchar(100) NOT NULL DEFAULT '' COMMENT '帳號',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
) ENGINE=InnoDB COMMENT='客服列表';

CREATE TABLE api_log(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT 'UID',
  `url` varchar(300) NOT NULL DEFAULT '' COMMENT 'API網址',flow
  `controllers` varchar(100) NOT NULL DEFAULT '' COMMENT '控制項',
  `functions` varchar(100) NOT NULL DEFAULT '' COMMENT '方法',
  `param` text NOT NULL COMMENT '參數',
  `return_str` text NOT NULL COMMENT '回傳參數',
  `exec_time` float(7,4) NOT NULL DEFAULT '0.0000' COMMENT '執行時間',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  KEY `create_time` (`create_time`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB COMMENT='API LOG';

CREATE TABLE bc_user_money_log(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT 'UID',
  flow_type tinyint(2) NOT NULL COMMENT '類型',
  order_sn varchar(50) NOT NULL COMMENT '訂單ID',
  money_before decimal(10,2) NOT NULL DEFAULT 0 COMMENT '變動前餘額',
  money_change decimal(10,2) NOT NULL DEFAULT 0 COMMENT '變動金額',
  money_after decimal(10,2) NOT NULL DEFAULT 0 COMMENT '變動後餘額',
  description varchar(100) NOT NULL DEFAULT '' COMMENT '描述',
  remark varchar(100) NOT NULL DEFAULT '' COMMENT '備註',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  KEY `create_time` (`create_time`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB COMMENT='玩家餘額變動LOG';

CREATE TABLE bc_ipmanage(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ip varchar(20) NOT NULL COMMENT 'IP',
  note varchar(255) NOT NULL DEFAULT '' COMMENT '備註',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間'
) ENGINE=InnoDB COMMENT='封停IP管理';

CREATE TABLE `bc_header_action` (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title` varchar(50) NOT NULL COMMENT '標題',
  `icon` varchar(300) NOT NULL COMMENT '圖片路徑',
  `jump_url` varchar(100) NOT NULL DEFAULT '' COMMENT '轉跳網址',
  status tinyint(2) NOT NULL DEFAULT 1 COMMENT '狀態 0:關閉 1:開啟',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
) ENGINE=InnoDB COMMENT='首頁選項配置';

CREATE TABLE `bc_recharge_online` (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_group_ids` varchar(100) NOT NULL DEFAULT '' COMMENT '會員分層ID',
  `interface tinyint`(2) NOT NULL COMMENT '接口 1:橘子支付',
  payment tinyint(2) NOT NULL COMMENT '付款類型 1:支付寶 2:微信',
  payment_logo varchar(255) NOT NULL DEFAULT '' COMMENT '付款類型LOGO',
  pay_url varchar(255) NOT NULL COMMENT 'API網址',
  m_num varchar(50) NOT NULL COMMENT '商戶號',
  secret_key varchar(200) NOT NULL COMMENT '密鑰',
  moneys text NOT NULL COMMENT '面額',
  `handsel_percent` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '贈送彩金比例',
  `handsel_max` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '贈送彩金上限',
  `multiple` tinyint(2) NOT NULL DEFAULT 1 COMMENT '打碼量倍數',
  `min_money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '单笔最小限额',
  `max_money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '单笔最大限额',
  `day_max_money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '单日最大限额',
  remark varchar(500) NOT NULL COMMENT '備註',
  status tinyint(2) NOT NULL DEFAULT 1 COMMENT '狀態 0:關閉 1:開啟',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
) ENGINE=InnoDB COMMENT='線上充值帳戶設置';

CREATE TABLE `bc_recharge_offline` (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_group_ids` varchar(100) NOT NULL DEFAULT '' COMMENT '會員分層ID',
  channel tinyint(2) NOT NULL COMMENT '渠道 1:銀行卡 2:微信 3:支付寶',
  nickname varchar(100) NOT NULL COMMENT '暱稱',
  `bank_name` varchar(50) NOT NULL COMMENT '銀行名稱',
  `account` varchar(50) NOT NULL COMMENT '帳號',
  `qrcode` varchar(300) NOT NULL COMMENT '圖片路徑',
  `handsel_percent` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '贈送彩金比例',
  `handsel_max` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '贈送彩金上限',
  `code_amount_beishu` tinyint(2) NOT NULL DEFAULT 1 COMMENT '打碼量倍數',
  `one_min_money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '单笔最小限额',
  `one_max_money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '单笔最大限额',
  `one_day_max_money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '单日最大限额',
  status tinyint(2) NOT NULL DEFAULT 1 COMMENT '狀態 0:關閉 1:開啟',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
) ENGINE=InnoDB COMMENT='線下充值帳戶設置';

CREATE TABLE `bc_recharge_order` (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uid int(11) NOT NULL COMMENT '用戶ID',
  type tinyint(2) NOT NULL DEFAULT 2 COMMENT '類型 1:線上 2:線下',
  order_sn varchar(30) NOT NULL DEFAULT '' COMMENT '訂單號',
  offline_id int(11) NOT NULL DEFAULT 0 COMMENT '線下支付ID',
  offline_channel tinyint(2) NOT NULL DEFAULT 1 COMMENT '渠道 1:銀行卡 2:微信 3:支付寶',
  offline_account varchar(50) NOT NULL DEFAULT '' COMMENT '線下支付帳號',
  offline_user_bank_name varchar(50) NOT NULL COMMENT '用戶銀行名稱',
  offline_user_realname varchar(50) NOT NULL COMMENT '用戶匯款姓名',
  offline_pay_type tinyint(2) NOT NULL DEFAULT 1 COMMENT '匯款方式 1:網銀轉帳 2:ATM自動櫃員機 3:銀行櫃檯 4:手機銀行 5:其他',
  money decimal(10,2) NOT NULL DEFAULT 0 COMMENT '充值金額',
  grand_total int(11) NOT NULL DEFAULT 0 COMMENT '累計充值金額',
  today_total int(11) NOT NULL DEFAULT 0 COMMENT '當日充值金額',
  status tinyint(2) NOT NULL DEFAULT 1 COMMENT '狀態 0:待審核 1:充值成功 2:充值失敗',
  check_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '審核時間',
  check_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '審核者',
  check_remarks VARCHAR(100) NOT NULL DEFAULT '' COMMENT '審核備註(失敗)',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
) ENGINE=InnoDB COMMENT='充值訂單';

CREATE TABLE bc_code_amount (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uid int(11) NOT NULL COMMENT '用戶ID',
  type tinyint(2) NOT NULL DEFAULT 0 COMMENT '類型 1:充值，2:贈送彩金 3:人工入款 4:人工彩金',
  money decimal(10,2) NOT NULL DEFAULT 0 COMMENT '金額',
  description varchar(300) NOT NULL DEFAULT '' COMMENT '描述',
  multiple smallint(4) NOT NULL DEFAULT 1 COMMENT '打碼量倍數',
  code_amount_need decimal(10,2) NOT NULL DEFAULT 0 COMMENT '需求打碼量',
  code_amount decimal(10,2) NOT NULL DEFAULT 0 COMMENT '有效打碼量',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
) ENGINE=InnoDB COMMENT='打碼量清單';

CREATE TABLE bc_code_amount_log (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uid int(11) NOT NULL COMMENT '用戶ID',
  type tinyint(2) NOT NULL DEFAULT 0 COMMENT '類型 0:下注 1:人工加碼 2:人工減碼 3:退款',
  category int(11) NOT NULL DEFAULT 0 COMMENT '玩法類別 1:經典彩 2:官方彩',
  bet_record_id int(11) NOT NULL DEFAULT 0 COMMENT '下注ID',
  code_amount decimal(10,2) NOT NULL DEFAULT 0 COMMENT '有效打碼量',
  description varchar(300) NOT NULL DEFAULT '' COMMENT '描述',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者'
) ENGINE=InnoDB COMMENT='打碼量LOG';

CREATE TABLE bc_code_amount_assign (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  code_amount_log_id int(11) NOT NULL COMMENT '打碼量LogID',
  code_amount_id int(11) NOT NULL COMMENT '打碼量ID',
  type tinyint(2) NOT NULL DEFAULT 0 COMMENT '類型 0:下注 1:人工加碼 2:人工減碼 3:退款',
  code_amount_use int(11) NOT NULL DEFAULT 0 COMMENT '打碼量 1:經典彩 2:官方彩',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者'
) ENGINE=InnoDB COMMENT='打碼量分配';

CREATE TABLE bc_user_withdraw (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uid int(11) NOT NULL COMMENT '用戶ID',
  order_sn varchar(30) NOT NULL DEFAULT '' COMMENT '訂單號',
  money decimal(10,2) NOT NULL DEFAULT 0 COMMENT '金額',
  bank_realname varchar(100) NOT NULL COMMENT '姓名',
  bank_name varchar(100) NOT NULL COMMENT '銀行名稱',
  bank_account varchar(20) NOT NULL COMMENT '銀行帳號',
  grand_total int(11) NOT NULL DEFAULT 0 COMMENT '累計提現金額',
  today_total int(11) NOT NULL DEFAULT 0 COMMENT '當日提現金額',
  check_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '審核時間',
  check_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '審核者',
  check_remarks VARCHAR(100) NOT NULL DEFAULT '' COMMENT '審核備註(失敗)',
  status tinyint(2) NOT NULL DEFAULT 1 COMMENT '狀態 0:待審核 1:提現成功 2:提現失敗',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
) ENGINE=InnoDB COMMENT='用戶提現';

CREATE TABLE bc_rakeback (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_group_id int(11) NOT NULL COMMENT '會員分層ID',
  type tinyint(2) NOT NULL DEFAULT 0 COMMENT '類型 0:投注 1:負盈利返水',
  category int(11) NOT NULL DEFAULT 0 COMMENT '玩法類別 0:全部 1:經典彩 2:官方彩',
  lottery_type_id int(11) NOT NULL DEFAULT 0 COMMENT '彩種類型',
  lottery_id int(11) NOT NULL DEFAULT 0 COMMENT '彩種ID',
  rakeback_per decimal(10,2) NOT NULL DEFAULT 0 COMMENT '返水比率',
  rakeback_max decimal(10,2) NOT NULL DEFAULT 0 COMMENT '返水上限',
  start_money decimal(10,2) NOT NULL DEFAULT 0 COMMENT '起算金額',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
) ENGINE=InnoDB COMMENT='返水設置';

CREATE TABLE bc_ettm_reduce (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  type tinyint(2) NOT NULL DEFAULT 0 COMMENT '類型 0:百分比 1:固定值',
  `value` int(11) NOT NULL DEFAULT 0 COMMENT '依type降賠值',
  interval int(11) NOT NULL DEFAULT 1 COMMENT '降賠區間',
  frequency int(11) NOT NULL DEFAULT 1 COMMENT '次數',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
) ENGINE=InnoDB COMMENT='降賠設置';

CREATE TABLE bc_operator(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(50) NOT NULL COMMENT '運營商名稱',
  domain_url varchar(500) NOT NULL COMMENT '綁定網域',
  commission decimal(4,2) NOT NULL COMMENT '抽成',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='運營商列表';

CREATE TABLE bc_daily_user_report (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  day_time DATE NOT NULL DEFAULT '2000-01-01' COMMENT '日期',
  category tinyint(2) NOT NULL DEFAULT 1 COMMENT '分類 1:經典 2:官方',
  lottery_id int(11) NOT NULL DEFAULT 0 COMMENT '彩種ID',
  uid int(11) NOT NULL DEFAULT 0 COMMENT 'UID',
  bet_number int(11) NOT NULL DEFAULT 0 COMMENT '下注筆數',
  bet_money decimal(12,2) NOT NULL DEFAULT 0 COMMENT '下注金額',
  c_value decimal(12,2) NOT NULL DEFAULT 0 COMMENT '賠付金額',
  bet_eff decimal(12,2) NOT NULL DEFAULT 0 COMMENT '有效下注額',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  UNIQUE (day_time,category,lottery_id,uid)
) ENGINE=InnoDB COMMENT='每日結算報表';

CREATE TABLE bc_ettm_special(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  type int(11) NOT NULL COMMENT '遊戲類型 1:牛牛 2:搶莊牛牛',
  lottery_id int(11) NOT NULL COMMENT '彩種ID',
  name varchar(50) NOT NULL COMMENT '遊戲名稱',
  pic_icon varchar(255) NOT NULL DEFAULT '' COMMENT 'Icon',
  key_word varchar(50) NOT NULL COMMENT 'Keyword',
  jump_url varchar(255) NOT NULL DEFAULT '' COMMENT '轉跳網址',
  commission decimal(5,2) NOT NULL DEFAULT 0 COMMENT '抽水比例(%)',
  banker_limit int(11) NOT NULL DEFAULT 0 COMMENT '莊家總額度上限',
  player_limit int(11) NOT NULL DEFAULT 0 COMMENT '閒家下注限額',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  status tinyint(2) NOT NULL DEFAULT 1 COMMENT '狀態 0:關閉 1:開啟',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='特色棋牌列表';

CREATE TABLE bc_ettm_special_bet_record(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lottery_id int(11) NOT NULL COMMENT '彩種ID',
  special_id int(11) NOT NULL COMMENT '特色棋牌ID',
  qishu bigint(20) NOT NULL COMMENT '期數',
  uid int(11) NOT NULL COMMENT '用戶ID',
  order_sn varchar(50) NOT NULL COMMENT '訂單號',
  p_value decimal(10,2) NOT NULL DEFAULT 0 COMMENT '下注額',
  c_value decimal(10,2) NOT NULL DEFAULT 0 COMMENT '賠付額',
  bet_number int(11) NOT NULL DEFAULT 1 COMMENT '注數',
  bet_multiple int(11) NOT NULL DEFAULT 1 COMMENT '下注倍數',
  total_p_value decimal(10,2) NOT NULL DEFAULT 0 COMMENT '下注總額',
  odds decimal(11,3) NOT NULL DEFAULT 1 COMMENT '賠率',
  bet_values varchar(100) NOT NULL COMMENT '下注值',
  is_lose_win tinyint(2) NOT NULL COMMENT '輸贏 0:輸 1:贏 2:平',
  is_code_amount tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否打碼 0:未打碼 1:已打碼',
  source varchar(5) NOT NULL DEFAULT 'wap' COMMENT '來源 wap or pc',
  platform tinyint(2) NOT NULL DEFAULT 0 COMMENT '平台 0:Windows 1:Android 2:IOS',
  status tinyint(2) NOT NULL DEFAULT 0 COMMENT '狀態 -1:處理中 0:未結算 1:已結算 2:已退款',
  is_delete tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否刪除',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='特色棋牌注單';

CREATE TABLE bc_ettm_lottery_cheat(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  type tinyint(2) NOT NULL COMMENT '作弊類型 0:控制獲利 1:控制不開豹子 2:控制開獎號碼 3:控制必贏機率',
  lottery_id int(11) NOT NULL COMMENT '彩種ID',
  qishu bigint(20) NOT NULL COMMENT '期數',
  numbers varchar(50) NOT NULL COMMENT '開獎號碼',
  starttime time NOT NULL DEFAULT '00:00:00' COMMENT '起始時間',
  endtime time NOT NULL DEFAULT '00:00:00' COMMENT '結束時間',
  percent tinyint(3) NOT NULL DEFAULT 0 COMMENT '機率(%)',
  status tinyint(2) NOT NULL DEFAULT 1 COMMENT '狀態 0:關閉',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='彩種作弊程式設定';

CREATE TABLE bc_ettm_lottery_cheat_log(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  type tinyint(2) NOT NULL COMMENT '作弊類型 0:控制獲利 1:控制不開豹子 2:控制開獎號碼 3:控制必贏機率',
  lottery_id int(11) NOT NULL COMMENT '彩種ID',
  qishu bigint(20) NOT NULL COMMENT '期數',
  numbers varchar(50) NOT NULL COMMENT '開獎號碼',
  profit decimal(10,2) NOT NULL DEFAULT 0 COMMENT '獲利',
  status tinyint(2) NOT NULL DEFAULT 0 COMMENT '狀態 0:不符合 1:符合',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
)ENGINE=InnoDB COMMENT='彩種作弊程式日誌';

CREATE TABLE bc_websocket_log(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT 'UID',
  `fd` int(11) NOT NULL DEFAULT 0 COMMENT 'WebSocket fd',
  `data` varchar(100) NOT NULL DEFAULT '' COMMENT '參數',
  `return_data` text NOT NULL DEFAULT '' COMMENT '回傳參數',
  `exec_time` float(7,4) NOT NULL DEFAULT '0.0000' COMMENT '執行時間',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  KEY `create_time` (`create_time`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB COMMENT='WebSocket LOG';

CREATE TABLE bc_concurrent_user(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  operator_id int(11) NOT NULL DEFAULT 0 COMMENT '營運商ID',
  minute_time datetime NOT NULL COMMENT '時間(每分鐘)',
  count int(11) NOT NULL DEFAULT 0 COMMENT '人數',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  UNIQUE key (operator_id,`minute_time`)
) ENGINE=InnoDB COMMENT='同時在線人數(CCU)';

CREATE TABLE `bc_daily_analysis` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  operator_id int(11) NOT NULL DEFAULT 0 COMMENT '營運商ID',
  `day_time` date NOT NULL COMMENT '日期',
  `type` tinyint(2) NOT NULL COMMENT '類型 1.每日新增遊戲帳號數(NUU) 2.每日不重覆登入帳號數(DAU) 3.每週不重複登入帳號數(WAU) 4.每月不重覆登入帳號數(MAU) 6.最大同時在線帳號數(PCU) 7.日變動率，（昨日DAU - 今日DAU + 今日NUU）/ 當日為止MAU 8.週變動率，（今日DAU - 七日前DAU）/ 七日前DAU 9.月變動率，（本月最後一日MAU - 上月最後一日MAU）/ 上月最後一日MAU',
  `count` int(11) NOT NULL COMMENT '人數',
  `create_time` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY (operator_id,`date`,`type`)
) ENGINE=InnoDB COMMENT='每日統計-PM工具';

CREATE TABLE `bc_daily_retention` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  operator_id int(11) NOT NULL DEFAULT 0 COMMENT '營運商ID',
  `day_time` date NOT NULL COMMENT '日期',
  `type` tinyint(2) NOT NULL COMMENT '類型 1.1日內有登入,2.3日內有登入,3.7日內有登入,4.15日內有登入,5.30日內有登入,6.31日以上未登入',
  `all_count` int(11) NOT NULL DEFAULT 0 COMMENT '總數',
  `day_count` int(11) NOT NULL DEFAULT 0 COMMENT '人數',
  `avg_money` int(11) NOT NULL DEFAULT 0 COMMENT '平均餘額',
  `create_time` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY (operator_id,`day_time`,`type`)
) ENGINE=InnoDB COMMENT='每日統計-留存率';

CREATE TABLE `bc_daily_retention_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  operator_id int(11) NOT NULL DEFAULT 0 COMMENT '營運商ID',
  `day_time` date NOT NULL COMMENT '日期',
  `type` tinyint(2) NOT NULL COMMENT '類型 1.1天前新帳號,2.3天前新帳號,3.7天前新帳號,4.15天前新帳號,5.30天前新帳號',
  `all_count` int(10) NOT NULL DEFAULT '0' COMMENT '總數',
  `day_count` int(10) NOT NULL DEFAULT '0' COMMENT '人數',
  `percent` int(10) NOT NULL DEFAULT '0' COMMENT '百分比',
  `create_time` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`operator_id`,`day_time`,`type`)
) ENGINE=InnoDB COMMENT='每日統計-新帳號留存率';

CREATE TABLE bc_ettm_lottery_type_sort(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  operator_id int(11) NOT NULL DEFAULT 0 COMMENT '營運商ID',
  lottery_id int(11) NOT NULL COMMENT '彩種ID',
  sort smallint(4) NOT NULL DEFAULT 0 COMMENT '排序',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='彩種分類排序';

CREATE TABLE bc_ettm_lottery_sort(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  operator_id int(11) NOT NULL DEFAULT 0 COMMENT '營運商ID',
  lottery_id int(11) NOT NULL COMMENT '彩種ID',
  sort int(11) NOT NULL COMMENT '排序',
  status tinyint(2) NOT NULL COMMENT '狀態 0:維護中 1:開啟',
  is_hot tinyint(1) NOT NULL COMMENT '是否為熱門彩種',
  hot_logo tinyint(1) NOT NULL COMMENT '是否有HOT的LOGO',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='彩種排序';

CREATE TABLE bc_module(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(50) NOT NULL COMMENT '模組名稱',
  keyword varchar(50) NOT NULL COMMENT '關鍵字',
  status tinyint(2) NOT NULL DEFAULT 1 COMMENT '狀態 0:關閉 1:開啟',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='模組列表';

CREATE TABLE bc_module_operator(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  operator_id int(11) NOT NULL DEFAULT 0 COMMENT '營運商ID',
  module_id int(11) NOT NULL COMMENT '模組ID',
  status tinyint(2) NOT NULL DEFAULT 1 COMMENT '狀態 0:關閉 1:開啟',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者',
  UNIQUE (operator_id,module_id)
)ENGINE=InnoDB COMMENT='各營運商模組';

CREATE TABLE bc_cnzz(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  domain_url varchar(200) NOT NULL COMMENT 'domain',
  cnzz_url varchar(255) NOT NULL COMMENT '網址',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='網域對應的CNZZ網址';

CREATE TABLE bc_prediction_relief(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uid int(11) NOT NULL COMMENT '用戶ID',
  prediction_id int(11) NOT NULL COMMENT '預測ID',
  qishu bigint(20) NOT NULL COMMENT '期數',
  payload text NOT NULL COMMENT '資訊紀錄',
  relief decimal(11,2) NOT NULL DEFAULT 0 COMMENT '救濟金',
  bet_money decimal(11,2) NOT NULL DEFAULT 0 COMMENT '預測號下注總額',
  recharge decimal(11,2) NOT NULL DEFAULT 0 COMMENT '已充值金額',
  expire_time datetime NOT NULL DEFAULT '2099-12-31 00:00:00' COMMENT '到期時間',
  withdraw_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '提取時間',
  status tinyint(2) NOT NULL DEFAULT 1 COMMENT '狀態 0:待激活 1:已激活 2:已提取 3:已過期',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='預測救濟金';

CREATE TABLE bc_prediction_robot_bet(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  prediction_id int(11) NOT NULL COMMENT '預測ID',
  qishu bigint(20) NOT NULL COMMENT '期數',
  bet_money decimal(12,2) NOT NULL DEFAULT 0 COMMENT '下注金額',
  bet_money_max decimal(12,2) NOT NULL DEFAULT 0 COMMENT '投注總額',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間'
)ENGINE=InnoDB COMMENT='投注熱度虛擬下注';

CREATE TABLE bc_prediction_robot_setting(
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lottery_id int(11) NOT NULL DEFAULT 0 COMMENT '彩種ID 0=預設',
  total_formula text NOT NULL COMMENT '投注總額計算公式',
  bet_formula text NOT NULL COMMENT '下注公式',
  create_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '建檔時間',
  create_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '新增者',
  update_time DATETIME NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '更新時間',
  update_by VARCHAR(30) NOT NULL DEFAULT '' COMMENT '更新者'
)ENGINE=InnoDB COMMENT='投注熱度虛擬下注公式';


