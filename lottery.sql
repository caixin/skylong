# Host: 192.168.99.100  (Version 5.7.27)
# Date: 2019-10-02 16:26:04
# Generator: MySQL-Front 6.1  (Build 1.26)


#
# Structure for table "bc_activity"
#

CREATE TABLE `bc_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '運營商編號',
  `type` int(11) NOT NULL COMMENT '類型 1:Wap 2:PC',
  `name` varchar(50) NOT NULL COMMENT '活動名稱',
  `content` text NOT NULL COMMENT '活動内容',
  `pic1` varchar(255) NOT NULL DEFAULT '' COMMENT '首頁輪播',
  `pic2` varchar(255) NOT NULL DEFAULT '' COMMENT '活動頁(模板1)',
  `pic3` varchar(255) NOT NULL DEFAULT '' COMMENT '活動頁(模板2)',
  `pic1_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否顯示首頁輪播',
  `pic2_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否顯示pic2圖',
  `pic3_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否顯示pic3圖',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  KEY `operator_id` (`operator_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='活動列表';

#
# Structure for table "bc_admin"
#

CREATE TABLE `bc_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用戶名',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密碼',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手機號碼',
  `roleid` int(11) NOT NULL DEFAULT '0' COMMENT '角色ID',
  `login_ip` varchar(16) NOT NULL COMMENT '登入IP',
  `login_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '登入時間',
  `login_count` int(11) NOT NULL DEFAULT '0' COMMENT '登入次數',
  `is_agent` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否為代理',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '代理用戶ID',
  `otp_check` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'OTP動態密碼：1:開啟 0:關閉',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '狀態 1:開啟 0:關閉',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COMMENT='系統帳號';

#
# Structure for table "bc_admin_action_log"
#

CREATE TABLE `bc_admin_action_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminid` int(11) NOT NULL DEFAULT '0' COMMENT 'adminid',
  `url` varchar(50) NOT NULL COMMENT 'URL',
  `message` text NOT NULL COMMENT '操作訊息',
  `sql_str` text NOT NULL COMMENT 'SQL指令',
  `ip` varchar(16) NOT NULL DEFAULT '' COMMENT '登入IP',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '狀態 0:失敗 1:成功',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2272 DEFAULT CHARSET=utf8 COMMENT='系統帳號操作LOG';

#
# Structure for table "bc_admin_login_log"
#

CREATE TABLE `bc_admin_login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminid` int(11) NOT NULL DEFAULT '0' COMMENT 'adminid',
  `ip` varchar(16) NOT NULL DEFAULT '' COMMENT '登入IP',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '狀態 0:失敗 1:成功',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=742 DEFAULT CHARSET=utf8 COMMENT='系統帳號登入LOG';

#
# Structure for table "bc_admin_nav"
#

CREATE TABLE `bc_admin_nav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父級ID',
  `icon` varchar(50) NOT NULL DEFAULT '' COMMENT 'ICON',
  `name` varchar(50) NOT NULL COMMENT '導航名稱',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT 'URL路徑',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '階層路徑',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '狀態 1:開啟 0:關閉',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB AUTO_INCREMENT=241 DEFAULT CHARSET=utf8 COMMENT='導航列表';

#
# Structure for table "bc_admin_otp"
#

CREATE TABLE `bc_admin_otp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手機號碼',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密碼',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8 COMMENT='OTP動態密碼';

#
# Structure for table "bc_admin_role"
#

CREATE TABLE `bc_admin_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名稱',
  `allow_operator` varchar(255) NOT NULL DEFAULT '' COMMENT '營運商權限',
  `allow_nav` text COMMENT '導航權限',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='系統帳號角色權限';

#
# Structure for table "bc_admin_session"
#

CREATE TABLE `bc_admin_session` (
  `adminid` int(11) NOT NULL DEFAULT '0' COMMENT 'adminid',
  `username` varchar(50) NOT NULL COMMENT '用戶名',
  `session_id` text NOT NULL COMMENT 'SessionID',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`adminid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系統帳號SESSION';

#
# Structure for table "bc_advertise"
#

CREATE TABLE `bc_advertise` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '編號',
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '運營商編號',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '廣告位置',
  `name` varchar(50) NOT NULL COMMENT '廣告名稱',
  `pic` varchar(800) DEFAULT '' COMMENT '廣告圖片',
  `pic_url` varchar(800) DEFAULT '' COMMENT '廣告圖片指向地址',
  `key_word` varchar(255) DEFAULT '' COMMENT 'key_word',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  KEY `operator_id` (`operator_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='廣告管理';

#
# Structure for table "bc_agent_code"
#

CREATE TABLE `bc_agent_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '編號',
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `type` tinyint(2) NOT NULL COMMENT '類型 1:代理 2:玩家',
  `level` tinyint(2) NOT NULL DEFAULT '1' COMMENT '層級',
  `code` varchar(10) NOT NULL COMMENT '代理邀請碼',
  `note` varchar(100) NOT NULL DEFAULT '' COMMENT '備註',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COMMENT='用戶代理邀請碼';

#
# Structure for table "bc_agent_code_detail"
#

CREATE TABLE `bc_agent_code_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '編號',
  `code` varchar(10) NOT NULL COMMENT '代理邀請碼',
  `lottery_id` int(11) NOT NULL COMMENT '樂透ID',
  `return_point` decimal(5,3) NOT NULL DEFAULT '0.000' COMMENT '返點',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`,`lottery_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3012 DEFAULT CHARSET=utf8 COMMENT='用戶代理邀請碼返點明細';

#
# Structure for table "bc_agent_return_point"
#

CREATE TABLE `bc_agent_return_point` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '編號',
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `from_uid` int(11) NOT NULL COMMENT '下線會員UID',
  `category` tinyint(1) NOT NULL DEFAULT '0' COMMENT '分類 1:經典 2:官方',
  `lottery_id` int(11) NOT NULL COMMENT '彩種ID',
  `qishu` varchar(20) NOT NULL COMMENT '期數',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '返點金額',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`,`from_uid`,`category`,`lottery_id`,`qishu`)
) ENGINE=InnoDB AUTO_INCREMENT=982 DEFAULT CHARSET=utf8 COMMENT='用戶代理返點';

#
# Structure for table "bc_api_log"
#

CREATE TABLE `bc_api_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT 'UID',
  `url` varchar(300) NOT NULL DEFAULT '' COMMENT 'API網址',
  `controllers` varchar(100) NOT NULL DEFAULT '' COMMENT '控制項',
  `functions` varchar(100) NOT NULL DEFAULT '' COMMENT '方法',
  `param` text NOT NULL COMMENT '參數',
  `return_str` text NOT NULL COMMENT '回傳參數',
  `exec_time` float(7,4) NOT NULL DEFAULT '0.0000' COMMENT '執行時間',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='API LOG';

#
# Structure for table "bc_apps"
#

CREATE TABLE `bc_apps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '運營商編號',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '應用類型',
  `name` varchar(50) NOT NULL COMMENT '應用名稱',
  `jump_url` varchar(200) DEFAULT NULL COMMENT '跳轉URL(H5網頁地址)',
  `download_url` varchar(500) DEFAULT NULL COMMENT '下載URL',
  `downloads` int(11) NOT NULL DEFAULT '0' COMMENT '下載次數',
  `is_vip` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否為VIP包：0:否 1:是',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`type`,`is_vip`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='应用管理';

#
# Structure for table "bc_bank"
#

CREATE TABLE `bc_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '銀行名稱',
  `image_url` varchar(255) NOT NULL DEFAULT '' COMMENT '圖片路徑',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(255) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '修改時間',
  `update_by` varchar(255) NOT NULL DEFAULT '' COMMENT '修改者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COMMENT='銀行資料';

#
# Structure for table "bc_cnzz"
#

CREATE TABLE `bc_cnzz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_url` varchar(200) NOT NULL COMMENT 'domain',
  `cnzz_url` varchar(255) NOT NULL COMMENT '網址',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='網域對應的CNZZ網址';

#
# Structure for table "bc_code_amount"
#

CREATE TABLE `bc_code_amount` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `money_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '貨幣類型 0:現金帳戶 1:特色棋牌帳戶',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '類型 1:充值，2:贈送彩金 3:人工入款 4:人工彩金',
  `related_id` int(11) NOT NULL DEFAULT '0' COMMENT '關聯ID',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金額',
  `description` varchar(300) NOT NULL DEFAULT '' COMMENT '描述',
  `multiple` smallint(4) NOT NULL DEFAULT '1' COMMENT '打碼量倍數',
  `code_amount_need` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '需求打碼量',
  `code_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '有效打碼量',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '狀態 0:未通過 1:通過',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8 COMMENT='打碼量清單';

#
# Structure for table "bc_code_amount_assign"
#

CREATE TABLE `bc_code_amount_assign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_amount_log_id` int(11) NOT NULL COMMENT '打碼量LogID',
  `code_amount_id` int(11) NOT NULL COMMENT '打碼量ID',
  `code_amount_use` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '打碼量',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_amount_log_id` (`code_amount_log_id`,`code_amount_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4694 DEFAULT CHARSET=utf8 COMMENT='打碼量分配';

#
# Structure for table "bc_code_amount_log"
#

CREATE TABLE `bc_code_amount_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `money_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '貨幣類型 0:現金帳戶 1:特色棋牌帳戶',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '類型 0:下注 1:人工加碼 2:人工減碼 3:退款',
  `category` int(11) NOT NULL DEFAULT '0' COMMENT '玩法類別 1:經典彩 2:官方彩',
  `bet_record_id` int(11) NOT NULL DEFAULT '0' COMMENT '下注ID',
  `code_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '有效打碼量',
  `description` varchar(300) NOT NULL DEFAULT '' COMMENT '描述',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=21777 DEFAULT CHARSET=utf8 COMMENT='打碼量LOG';

#
# Structure for table "bc_concurrent_user"
#

CREATE TABLE `bc_concurrent_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID',
  `per` tinyint(3) NOT NULL DEFAULT '1' COMMENT '每幾分鐘',
  `minute_time` datetime NOT NULL COMMENT '時間(每分鐘)',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '人數',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`per`,`minute_time`)
) ENGINE=InnoDB AUTO_INCREMENT=451061 DEFAULT CHARSET=utf8 COMMENT='同時在線人數(CCU)';

#
# Structure for table "bc_customer_service"
#

CREATE TABLE `bc_customer_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) DEFAULT '0' COMMENT '運營商編號',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '類別 1:在線客服 2:微信 3:QQ',
  `name` varchar(50) NOT NULL COMMENT '客服名稱',
  `image_url` varchar(300) NOT NULL COMMENT '圖片路徑',
  `account` varchar(100) NOT NULL DEFAULT '' COMMENT '帳號',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='客服列表';

#
# Structure for table "bc_daily_analysis"
#

CREATE TABLE `bc_daily_analysis` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID',
  `day_time` date NOT NULL DEFAULT '1970-01-01' COMMENT '日期',
  `type` tinyint(2) NOT NULL COMMENT '類型 1.每日新增遊戲帳號數(NUU) 2.每日不重覆登入帳號數(DAU) 3.每週不重複登入帳號數(WAU) 4.每月不重覆登入帳號數(MAU) 5.累積不重覆登入帳號數(UU) 6.最大同時在線帳號數(PCU) 7.日變動率，（昨日DAU - 今日DAU + 今日NUU）/ 當日為止MAU 8.週變動率，（今日DAU - 七日前DAU）/ 七日前DAU 9.月變動率，（本月最後一日MAU - 上月最後一日MAU）/ 上月最後一日MAU',
  `count` int(11) NOT NULL COMMENT '人數',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`day_time`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1364 DEFAULT CHARSET=utf8 COMMENT='每日統計-PM工具';

#
# Structure for table "bc_daily_digest"
#

CREATE TABLE `bc_daily_digest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID	',
  `day_time` date NOT NULL DEFAULT '1970-01-01' COMMENT '日期',
  `register_people` int(11) NOT NULL DEFAULT '0' COMMENT '註冊人數',
  `login_people` int(11) NOT NULL DEFAULT '0' COMMENT '登錄人數',
  `first_recharge_people` int(11) NOT NULL DEFAULT '0' COMMENT '首充人數',
  `first_recharge_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '首充金額',
  `recharge_people` int(11) NOT NULL DEFAULT '0' COMMENT '充值人數',
  `withdraw_people` int(11) NOT NULL DEFAULT '0' COMMENT '提現人數',
  `recharge_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '充值金額',
  `withdraw_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '提現金額',
  `real_income` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '資金匯總',
  `bet_people` int(11) NOT NULL DEFAULT '0' COMMENT '投注人數',
  `bet_number` int(11) NOT NULL DEFAULT '0' COMMENT '投注注數',
  `p_value` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '投注金額',
  `c_value` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '中獎金額',
  `return_point_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '返點金額',
  `income` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '盈虧',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_1` (`operator_id`,`day_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8;

#
# Structure for table "bc_daily_retention"
#

CREATE TABLE `bc_daily_retention` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID',
  `day_time` date NOT NULL DEFAULT '1970-01-01' COMMENT '日期',
  `type` tinyint(2) NOT NULL COMMENT '類型 1.1日內有登入,2.3日內有登入,3.7日內有登入,4.15日內有登入,5.30日內有登入,6.31日以上未登入',
  `all_count` int(11) NOT NULL DEFAULT '0' COMMENT '總數',
  `day_count` int(11) NOT NULL DEFAULT '0' COMMENT '人數',
  `avg_money` int(11) NOT NULL DEFAULT '0' COMMENT '平均餘額',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`day_time`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1243 DEFAULT CHARSET=utf8 COMMENT='每日統計-留存率';

#
# Structure for table "bc_daily_retention_user"
#

CREATE TABLE `bc_daily_retention_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID',
  `day_time` date NOT NULL DEFAULT '1970-01-01' COMMENT '日期',
  `type` tinyint(2) NOT NULL COMMENT '類型 1.1天前新帳號,2.3天前新帳號,3.7天前新帳號,4.15天前新帳號,5.30天前新帳號',
  `all_count` int(10) NOT NULL DEFAULT '0' COMMENT '總數',
  `day_count` int(10) NOT NULL DEFAULT '0' COMMENT '人數',
  `percent` int(10) NOT NULL DEFAULT '0' COMMENT '百分比',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`day_time`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1036 DEFAULT CHARSET=utf8 COMMENT='每日統計-新帳號留存率';

#
# Structure for table "bc_daily_user_report"
#

CREATE TABLE `bc_daily_user_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `day_time` date NOT NULL DEFAULT '1970-01-01' COMMENT '日期',
  `category` tinyint(2) NOT NULL DEFAULT '1' COMMENT '分類 1:經典 2:官方',
  `lottery_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種ID',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT 'UID',
  `bet_number` int(11) NOT NULL DEFAULT '0' COMMENT '下注筆數',
  `bet_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '下注金額',
  `c_value` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '賠付金額',
  `bet_eff` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '有效下注額',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY `day_time` (`day_time`,`category`,`lottery_id`,`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=707 DEFAULT CHARSET=utf8 COMMENT='每日結算報表';

#
# Structure for table "bc_ettm_classic_bet_record"
#

CREATE TABLE `bc_ettm_classic_bet_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_id` int(11) NOT NULL COMMENT '彩種ID',
  `qishu` bigint(20) NOT NULL COMMENT '期數',
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `wanfa_pid` int(11) NOT NULL COMMENT '玩法父級ID',
  `wanfa_id` int(11) NOT NULL COMMENT '玩法ID',
  `wanfa_detail_id` int(11) NOT NULL COMMENT '玩法詳細ID',
  `money_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '帳戶類型',
  `order_sn` varchar(50) NOT NULL COMMENT '訂單號',
  `p_value` int(11) NOT NULL DEFAULT '0' COMMENT '下注額',
  `c_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '賠付額',
  `bet_number` int(11) NOT NULL DEFAULT '1' COMMENT '注數',
  `total_p_value` int(11) NOT NULL DEFAULT '0' COMMENT '下注總額',
  `odds` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '賠率',
  `formula` varchar(200) NOT NULL DEFAULT '' COMMENT '中獎公式',
  `payload` text COMMENT 'Payload',
  `bet_values` varchar(100) NOT NULL COMMENT '下注值-數字',
  `bet_values_str` varchar(200) NOT NULL COMMENT '下注值-字串',
  `is_lose_win` tinyint(2) NOT NULL DEFAULT '0' COMMENT '輸贏 0:輸 1:贏 2:平',
  `is_code_amount` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否打碼 0:未打碼 1:已打碼',
  `source` varchar(10) NOT NULL DEFAULT '' COMMENT '來源 wap,pc,android,ios',
  `platform` tinyint(2) NOT NULL DEFAULT '0' COMMENT '平台 0:Windows 1:Android 2:IOS',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '狀態 -1:處理中 0:未結算 1:已結算 2:已退款',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  KEY `lottery_id` (`lottery_id`,`qishu`,`uid`),
  KEY `create_time` (`create_time`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=6982 DEFAULT CHARSET=utf8 COMMENT='經典下注表';

#
# Structure for table "bc_ettm_classic_odds_control"
#

CREATE TABLE `bc_ettm_classic_odds_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '流水號',
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID',
  `lottery_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種ID',
  `wanfa_detail_id` int(11) NOT NULL DEFAULT '0' COMMENT '玩法詳情ID',
  `qishu` bigint(20) NOT NULL DEFAULT '0' COMMENT '期數',
  `is_special` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否為特殊賠率',
  `interval` int(11) NOT NULL DEFAULT '0' COMMENT '降賠區間',
  `adjust` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '調整賠率',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `qishu` (`operator_id`,`lottery_id`,`wanfa_detail_id`,`qishu`,`is_special`,`interval`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='經典彩种控盘';

#
# Structure for table "bc_ettm_classic_wanfa"
#

CREATE TABLE `bc_ettm_classic_wanfa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_type_id` int(11) NOT NULL COMMENT '彩種類別ID',
  `pid` int(11) NOT NULL COMMENT '父級ID',
  `name` varchar(100) NOT NULL COMMENT '玩法名稱',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8 COMMENT='經典玩法';

#
# Structure for table "bc_ettm_classic_wanfa_detail"
#

CREATE TABLE `bc_ettm_classic_wanfa_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_type_id` int(11) NOT NULL COMMENT '彩種類別ID',
  `wanfa_id` int(11) NOT NULL COMMENT '玩法ID',
  `values` varchar(100) NOT NULL COMMENT '玩法值',
  `values_sup` varchar(250) NOT NULL COMMENT '輔助玩法值',
  `line_a_profit` decimal(5,3) NOT NULL DEFAULT '0.000' COMMENT 'A盤獲利(%)',
  `line_a_special` decimal(5,3) NOT NULL DEFAULT '0.000' COMMENT 'A盤獲利(特殊賠率)',
  `odds` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '賠率',
  `odds_special` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '特殊賠率',
  `qishu_max_money` int(11) NOT NULL DEFAULT '50000' COMMENT '單期累積最大下注額',
  `bet_max_money` int(11) NOT NULL DEFAULT '10000' COMMENT '單期單注最大下注額',
  `bet_min_money` int(11) NOT NULL DEFAULT '1' COMMENT '單期單注最小下注額',
  `max_number` int(11) NOT NULL DEFAULT '0' COMMENT '玩法值選號上限',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `formula` varchar(200) NOT NULL DEFAULT '' COMMENT '中獎公式',
  `payload` text COMMENT 'Payload',
  `trend_mode` tinyint(1) NOT NULL DEFAULT '0' COMMENT '露珠模式(二進位) 1:大小 2:單雙 4:龍虎 8:長龍 16:遺漏',
  `preview` tinyint(1) NOT NULL DEFAULT '0' COMMENT '代理返點預覽',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2493 DEFAULT CHARSET=utf8 COMMENT='經典玩法詳細';

#
# Structure for table "bc_ettm_lottery"
#

CREATE TABLE `bc_ettm_lottery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_type_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種類別id',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '彩種名稱',
  `pic_icon` varchar(255) NOT NULL DEFAULT '' COMMENT 'icon',
  `key_word` varchar(50) NOT NULL DEFAULT '' COMMENT '彩種keyword',
  `jump_url` varchar(255) NOT NULL DEFAULT '' COMMENT '轉跳網址',
  `day_start` time NOT NULL DEFAULT '00:00:00' COMMENT '每日開盤時間',
  `day_end` time NOT NULL DEFAULT '00:00:00' COMMENT '每日封盤時間',
  `open_start` time NOT NULL DEFAULT '00:00:00' COMMENT '開獎起始時間(分)',
  `open_end` time NOT NULL DEFAULT '00:00:00' COMMENT '開獎結束時間(分)',
  `interval` int(10) NOT NULL DEFAULT '600' COMMENT '開獎間隔時間(秒)',
  `halftime_start` time NOT NULL DEFAULT '00:00:00' COMMENT '中場休息起始時間',
  `halftime_end` time NOT NULL DEFAULT '00:00:00' COMMENT '中場休息結束時間',
  `adjust` int(11) NOT NULL DEFAULT '0' COMMENT '調整時間',
  `benchmark` int(10) NOT NULL DEFAULT '0' COMMENT '期數無日期者以此為基準值計算',
  `benchmark_date` date NOT NULL DEFAULT '2019-02-11' COMMENT '基準日期',
  `digit` tinyint(2) NOT NULL DEFAULT '2' COMMENT '期數幾位數',
  `mode` int(11) NOT NULL DEFAULT '0' COMMENT '玩法模式(二進位) 1:經典彩 2:官方彩',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `alarm` int(10) NOT NULL DEFAULT '90' COMMENT '報警秒數',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '狀態 0:維護中 1:開啟',
  `is_hot` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否為熱門彩種',
  `hot_logo` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否有HOT的LOGO',
  `is_custom` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否為自訂彩種',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_word` (`key_word`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='彩種列表';

#
# Structure for table "bc_ettm_lottery_cheat"
#

CREATE TABLE `bc_ettm_lottery_cheat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '運營商ID',
  `type` tinyint(2) NOT NULL COMMENT '作弊類型 0:控制獲利 1:控制不開豹子 2:控制開獎號碼 3:控制必贏機率',
  `lottery_id` int(11) NOT NULL COMMENT '彩種ID',
  `qishu` bigint(20) NOT NULL DEFAULT '0' COMMENT '期數',
  `numbers` varchar(50) NOT NULL COMMENT '開獎號碼',
  `starttime` time NOT NULL DEFAULT '00:00:00' COMMENT '起始時間',
  `endtime` time NOT NULL DEFAULT '00:00:00' COMMENT '結束時間',
  `percent` tinyint(3) NOT NULL DEFAULT '0' COMMENT '機率(%)',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '狀態 0:關閉 1以上開啟',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`operator_id`,`type`,`lottery_id`,`qishu`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='彩種作弊程式設定';

#
# Structure for table "bc_ettm_lottery_cheat_log"
#

CREATE TABLE `bc_ettm_lottery_cheat_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '運營商ID',
  `type` tinyint(2) NOT NULL COMMENT '作弊類型 0:控制獲利 1:控制不開豹子 2:控制開獎號碼 3:控制必贏機率',
  `lottery_id` int(11) NOT NULL COMMENT '彩種ID',
  `qishu` bigint(20) NOT NULL COMMENT '期數',
  `numbers` varchar(50) NOT NULL COMMENT '開獎號碼',
  `profit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '獲利',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`,`type`,`lottery_id`,`qishu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='彩種作弊程式號碼變更日誌';

#
# Structure for table "bc_ettm_lottery_dayoff"
#

CREATE TABLE `bc_ettm_lottery_dayoff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種ID',
  `dayoff` date NOT NULL DEFAULT '1970-01-01' COMMENT '未開獎日期',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='彩種未開獎日期';

#
# Structure for table "bc_ettm_lottery_record"
#

CREATE TABLE `bc_ettm_lottery_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種ID',
  `qishu` bigint(20) NOT NULL COMMENT '期數',
  `numbers` varchar(50) NOT NULL COMMENT '開獎號碼',
  `lottery_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '開獎時間',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '狀態 0:未開獎 1:已開獎',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lotteryid` (`lottery_id`,`qishu`),
  KEY `lottery_time` (`lottery_time`),
  KEY `lottery_id` (`lottery_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1550387 DEFAULT CHARSET=utf8 COMMENT='彩種開獎結果';

#
# Structure for table "bc_ettm_lottery_record_change"
#

CREATE TABLE `bc_ettm_lottery_record_change` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL COMMENT '營運商ID',
  `record_id` int(11) NOT NULL COMMENT '開獎結果ID',
  `numbers` varchar(50) NOT NULL COMMENT '替換的開獎號碼',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '狀態 0:未開獎 1:已開獎 2:已退款',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`record_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='自營彩-依營運商替換開獎結果';

#
# Structure for table "bc_ettm_lottery_sort"
#

CREATE TABLE `bc_ettm_lottery_sort` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID',
  `lottery_id` int(11) NOT NULL COMMENT '彩種ID',
  `sort` int(11) NOT NULL COMMENT '排序',
  `status` tinyint(2) NOT NULL COMMENT '狀態 0:維護中 1:開啟',
  `is_hot` tinyint(1) NOT NULL COMMENT '是否為熱門彩種',
  `hot_logo` tinyint(1) NOT NULL COMMENT '是否有HOT的LOGO',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`lottery_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='彩種排序';

#
# Structure for table "bc_ettm_lottery_type"
#

CREATE TABLE `bc_ettm_lottery_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '彩種類別',
  `pic_icon` varchar(255) NOT NULL DEFAULT '' COMMENT 'icon',
  `pic_icon2` varchar(255) NOT NULL DEFAULT '' COMMENT '代理用icon',
  `key_word` varchar(50) NOT NULL DEFAULT '' COMMENT 'keyword',
  `mode` int(11) NOT NULL DEFAULT '0' COMMENT '可用模式(二進位) 1:經典彩 2:官方彩',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='彩種分類';

#
# Structure for table "bc_ettm_lottery_type_sort"
#

CREATE TABLE `bc_ettm_lottery_type_sort` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID',
  `lottery_type_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種類型ID',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`lottery_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='彩種分類排序';

#
# Structure for table "bc_ettm_official_bet_record"
#

CREATE TABLE `bc_ettm_official_bet_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_id` int(11) NOT NULL COMMENT '彩種ID',
  `qishu` bigint(20) NOT NULL COMMENT '期數',
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `wanfa_pid` int(11) NOT NULL COMMENT '玩法父級ID',
  `wanfa_id` int(11) NOT NULL COMMENT '玩法ID',
  `money_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '帳戶類型',
  `order_sn` varchar(50) NOT NULL COMMENT '訂單號',
  `p_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '下注額',
  `c_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '賠付額',
  `bet_number` int(11) NOT NULL DEFAULT '1' COMMENT '注數',
  `bet_multiple` int(11) NOT NULL DEFAULT '1' COMMENT '倍數值',
  `total_p_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '下注總額',
  `odds` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '賠率',
  `payload` text COMMENT 'Payload',
  `bet_values` varchar(1000) NOT NULL DEFAULT '' COMMENT '下注值-數字',
  `bet_values_str` varchar(1000) NOT NULL DEFAULT '0' COMMENT '下注值-字串',
  `return_point` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '返點',
  `return_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '返水',
  `is_lose_win` tinyint(2) NOT NULL DEFAULT '0' COMMENT '輸贏 0:輸 1以上=贏的注數',
  `is_code_amount` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否打碼 0:未打碼 1:已打碼',
  `source` varchar(10) NOT NULL DEFAULT 'wap' COMMENT '來源 wap,pc,android,ios',
  `platform` tinyint(2) NOT NULL DEFAULT '0' COMMENT '平台 0:Windows 1:Android 2:IOS',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '狀態 -1:處理中 0:未結算 1:已結算 2:已退款',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`),
  KEY `lottery_id` (`lottery_id`,`qishu`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1121 DEFAULT CHARSET=utf8 COMMENT='官方下注表';

#
# Structure for table "bc_ettm_official_wanfa"
#

CREATE TABLE `bc_ettm_official_wanfa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_type_id` int(11) NOT NULL COMMENT '彩種類別ID',
  `pid` int(11) NOT NULL COMMENT '父級ID',
  `name` varchar(100) NOT NULL COMMENT '玩法名稱',
  `line_a_profit` decimal(5,3) NOT NULL DEFAULT '0.000' COMMENT 'A盤獲利(%)',
  `odds` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '滿盤賠率',
  `max_return` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '最大返點',
  `max_bet_number` int(11) NOT NULL DEFAULT '1' COMMENT '最大注數',
  `max_bet_money` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '最大投注額',
  `key_word` varchar(50) NOT NULL COMMENT '關鍵字',
  `payload` text COMMENT '紀錄有效資訊',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=199 DEFAULT CHARSET=utf8 COMMENT='官方玩法';

#
# Structure for table "bc_ettm_reduce"
#

CREATE TABLE `bc_ettm_reduce` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID',
  `lottery_type_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種類型ID',
  `lottery_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種ID',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '賠率 0:全部賠率 1:個人賠率',
  `items` text NOT NULL COMMENT '降賠項目',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`lottery_type_id`,`lottery_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='降賠設置';

#
# Structure for table "bc_ettm_special"
#

CREATE TABLE `bc_ettm_special` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種ID',
  `type` int(11) NOT NULL COMMENT '遊戲類型 1:牛牛 2:搶莊牛牛',
  `pic_icon` varchar(255) NOT NULL DEFAULT '' COMMENT 'Icon',
  `key_word` varchar(50) NOT NULL COMMENT 'Keyword',
  `jump_url` varchar(255) NOT NULL DEFAULT '' COMMENT '轉跳網址',
  `commission` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '主帳戶抽水比例(%)',
  `commission1` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '牛牛帳戶抽水比例(%)',
  `banker_limit` int(11) NOT NULL DEFAULT '0' COMMENT '莊家總額度上限',
  `player_limit` int(11) NOT NULL DEFAULT '0' COMMENT '閒家下注限額',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='特色棋牌列表';

#
# Structure for table "bc_ettm_special_bet_record"
#

CREATE TABLE `bc_ettm_special_bet_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_id` int(11) NOT NULL COMMENT '彩種ID',
  `special_id` int(11) NOT NULL COMMENT '特色棋牌ID',
  `qishu` bigint(20) NOT NULL COMMENT '期數',
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `money_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '貨幣類型 0:現金帳戶 1:特色棋牌帳戶',
  `order_sn` varchar(50) NOT NULL COMMENT '訂單號',
  `p_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '下注額',
  `c_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '賠付額',
  `bet_number` int(11) NOT NULL DEFAULT '1' COMMENT '注數',
  `bet_multiple` int(11) NOT NULL DEFAULT '1' COMMENT '下注倍數',
  `total_p_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '下注總額',
  `odds` decimal(11,3) NOT NULL DEFAULT '1.000' COMMENT '賠率',
  `bet_values` varchar(100) NOT NULL COMMENT '下注值',
  `is_lose_win` tinyint(2) NOT NULL COMMENT '輸贏 0:輸 1:贏 2:平',
  `is_code_amount` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否打碼 0:未打碼 1:已打碼',
  `source` varchar(10) NOT NULL DEFAULT 'wap' COMMENT '來源 wap,pc,android,ios',
  `platform` tinyint(2) NOT NULL DEFAULT '0' COMMENT '平台 0:Windows 1:Android 2:IOS',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '狀態 -1:處理中 0:未結算 1:已結算 2:已退款',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`),
  KEY `lottery_id` (`lottery_id`),
  KEY `special_id` (`special_id`,`qishu`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1157 DEFAULT CHARSET=utf8 COMMENT='特色棋牌注單';

#
# Structure for table "bc_header_action"
#

CREATE TABLE `bc_header_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mode` int(11) NOT NULL DEFAULT '0' COMMENT '模式(二進位) 1:瀏覽器 2:APP',
  `title` varchar(50) NOT NULL COMMENT '標題',
  `icon` varchar(300) NOT NULL COMMENT '圖片路徑',
  `jump_url` varchar(100) NOT NULL DEFAULT '' COMMENT '轉跳網址',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='首頁選項配置';

#
# Structure for table "bc_ipmanage"
#

CREATE TABLE `bc_ipmanage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(20) NOT NULL COMMENT 'IP',
  `note` varchar(255) NOT NULL DEFAULT '' COMMENT '備註',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL COMMENT '新增者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='封停IP管理';

#
# Structure for table "bc_module"
#

CREATE TABLE `bc_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '模組名稱',
  `keyword` varchar(50) NOT NULL COMMENT '關鍵字',
  `param` text NOT NULL COMMENT '模組參數',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='模組列表';

#
# Structure for table "bc_module_operator"
#

CREATE TABLE `bc_module_operator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID',
  `module_id` int(11) NOT NULL COMMENT '模組ID',
  `param` text NOT NULL COMMENT '模組參數',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`module_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='各營運商模組';

#
# Structure for table "bc_news"
#

CREATE TABLE `bc_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID 0:全部通用',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '文章類型 1:經典玩法 2:官方玩法 3:特色玩法 4:其它',
  `lottery_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種ID',
  `title` varchar(100) NOT NULL COMMENT '標題',
  `content_wap` text COMMENT 'WAP文章内容',
  `content_pc` text COMMENT 'PC文章內容',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  KEY `operator_id` (`operator_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8 COMMENT='文章列表';

#
# Structure for table "bc_notice"
#

CREATE TABLE `bc_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '運營商編號',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '公告類型',
  `name` varchar(50) NOT NULL COMMENT '公告名稱',
  `content` text NOT NULL COMMENT '公告内容',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  KEY `operator_id` (`operator_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='公告列表';

#
# Structure for table "bc_operator"
#

CREATE TABLE `bc_operator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '運營商名稱',
  `domain_url` varchar(500) NOT NULL DEFAULT '' COMMENT '綁定網域',
  `classic_adjustment` decimal(5,3) NOT NULL DEFAULT '1.000' COMMENT '經典彩A盤調整',
  `official_adjustment` decimal(5,3) NOT NULL DEFAULT '1.000' COMMENT '官方彩A盤調整',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='運營商列表';

#
# Structure for table "bc_prediction"
#

CREATE TABLE `bc_prediction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lottery_id` int(11) NOT NULL DEFAULT '0' COMMENT 'lottery_id',
  `wanfa_id` varchar(255) NOT NULL DEFAULT '' COMMENT 'wanfa_id(可多筆)',
  `ball` tinyint(2) NOT NULL DEFAULT '0' COMMENT '彩球位置',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名稱',
  `price` decimal(10,0) NOT NULL DEFAULT '0' COMMENT '購買金額',
  `is_home` tinyint(1) NOT NULL DEFAULT '0' COMMENT '首頁用 0=false 大於0=true',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_1` (`lottery_id`,`wanfa_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COMMENT='玩法預測設定檔';

#
# Structure for table "bc_prediction_assign"
#

CREATE TABLE `bc_prediction_assign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recharge_order_id` int(11) NOT NULL DEFAULT '0' COMMENT '充值紀錄id',
  `prediction_relief_id` int(11) NOT NULL DEFAULT '0' COMMENT '救濟金id',
  `reacharge_use` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '充值金額',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY `prediction_relief_id` (`recharge_order_id`,`prediction_relief_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='救濟金充值分配';

#
# Structure for table "bc_prediction_buy"
#

CREATE TABLE `bc_prediction_buy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `prediction_id` int(11) NOT NULL DEFAULT '0' COMMENT '預測ID',
  `qishu` bigint(20) NOT NULL COMMENT '期數',
  `digits` tinyint(2) unsigned NOT NULL DEFAULT '3' COMMENT '預測幾碼',
  `numbers` varchar(50) NOT NULL COMMENT '號碼',
  `price` int(10) NOT NULL DEFAULT '0' COMMENT '購買金額',
  `expire_time` datetime NOT NULL DEFAULT '2099-12-31 00:00:00' COMMENT '救濟金到期時間',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '-1退款;0待處理 1無救濟金 2有救濟金',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`digits`),
  KEY `uid1` (`uid`,`qishu`,`prediction_id`,`digits`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=330 DEFAULT CHARSET=utf8 COMMENT='玩法預測購買清單';

#
# Structure for table "bc_prediction_relief"
#

CREATE TABLE `bc_prediction_relief` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `prediction_id` int(11) NOT NULL COMMENT '預測ID',
  `qishu` bigint(20) NOT NULL COMMENT '期數',
  `payload` text NOT NULL COMMENT '資訊紀錄',
  `relief` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '救濟金',
  `bet_money` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '預測號下注總額',
  `recharge` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '已充值金額',
  `expire_time` datetime NOT NULL DEFAULT '2099-12-31 00:00:00' COMMENT '到期時間',
  `withdraw_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '提取時間',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '狀態 0:待激活 1:已激活 2:已提取 3:已過期',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`,`qishu`,`prediction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='預測救濟金';

#
# Structure for table "bc_prediction_robot_bet"
#

CREATE TABLE `bc_prediction_robot_bet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '1' COMMENT '運營商ID',
  `prediction_id` int(11) NOT NULL COMMENT '預測ID',
  `values` tinyint(3) NOT NULL DEFAULT '1' COMMENT '投注值',
  `qishu` bigint(20) NOT NULL COMMENT '期數',
  `bet_money` int(11) NOT NULL DEFAULT '0' COMMENT '下注金額',
  `bet_money_max` int(11) NOT NULL DEFAULT '0' COMMENT '投注總額',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_1` (`operator_id`,`prediction_id`,`qishu`,`values`)
) ENGINE=InnoDB AUTO_INCREMENT=273079 DEFAULT CHARSET=utf8 COMMENT='投注熱度虛擬下注';

#
# Structure for table "bc_prediction_robot_setting"
#

CREATE TABLE `bc_prediction_robot_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '1' COMMENT '運營商ID',
  `lottery_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種ID',
  `axis_y` int(11) NOT NULL DEFAULT '100000' COMMENT 'Y軸最大上限金額',
  `total_formula` text NOT NULL COMMENT '投注總額計算公式',
  `bet_formula` text NOT NULL COMMENT '下注公式',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lottery_id` (`operator_id`,`lottery_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='投注熱度虛擬下注公式';

#
# Structure for table "bc_recharge_offline"
#

CREATE TABLE `bc_recharge_offline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group_ids` varchar(100) NOT NULL DEFAULT '' COMMENT '會員分層ID',
  `channel` tinyint(2) NOT NULL COMMENT '渠道 1:銀行卡 2:微信 3:支付寶',
  `nickname` varchar(100) NOT NULL COMMENT '暱稱',
  `bank_id` int(11) NOT NULL DEFAULT '0' COMMENT '銀行ID',
  `account` varchar(50) NOT NULL COMMENT '帳號',
  `qrcode` varchar(300) NOT NULL COMMENT '圖片路徑',
  `handsel_percent` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '贈送彩金比例',
  `handsel_max` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '贈送彩金上限',
  `multiple` tinyint(2) NOT NULL DEFAULT '1' COMMENT '打碼量倍數',
  `min_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单笔最小限额',
  `max_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单笔最大限额',
  `day_max_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单日最大限额',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='線下充值帳戶設置';

#
# Structure for table "bc_recharge_online"
#

CREATE TABLE `bc_recharge_online` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group_ids` varchar(100) NOT NULL DEFAULT '' COMMENT '會員分層ID',
  `interface` tinyint(2) NOT NULL COMMENT '接口 1:橘子支付',
  `payment` tinyint(2) NOT NULL COMMENT '付款類型 1:支付寶 2:微信',
  `payment_logo` varchar(255) NOT NULL DEFAULT '' COMMENT '付款類型LOGO',
  `pay_url` varchar(255) NOT NULL COMMENT 'API網址',
  `notify_url` varchar(255) NOT NULL DEFAULT '' COMMENT '通知URL',
  `callback_url` varchar(255) NOT NULL DEFAULT '' COMMENT '回調URL',
  `m_num` varchar(50) NOT NULL COMMENT '商戶號',
  `secret_key` varchar(200) NOT NULL COMMENT '密鑰',
  `moneys` text NOT NULL COMMENT '面額',
  `handsel_percent` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '贈送彩金比例',
  `handsel_max` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '贈送彩金上限',
  `multiple` tinyint(2) NOT NULL DEFAULT '1' COMMENT '打碼量倍數',
  `min_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单笔最小限额',
  `max_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单笔最大限额',
  `day_max_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单日最大限额',
  `remark` varchar(500) NOT NULL COMMENT '備註',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否刪除',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='線上充值帳戶設置';

#
# Structure for table "bc_recharge_order"
#

CREATE TABLE `bc_recharge_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `type` tinyint(2) NOT NULL DEFAULT '2' COMMENT '類型 1:線上 2:線下',
  `order_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '訂單號',
  `line_id` int(11) NOT NULL DEFAULT '0' COMMENT '支付ID',
  `offline_channel` tinyint(2) NOT NULL DEFAULT '1' COMMENT '渠道 1:銀行卡 2:微信 3:支付寶',
  `offline_account` varchar(50) NOT NULL DEFAULT '' COMMENT '線下支付帳號',
  `offline_user_bank_name` varchar(50) NOT NULL COMMENT '用戶銀行名稱',
  `offline_user_realname` varchar(50) NOT NULL COMMENT '用戶匯款姓名',
  `offline_pay_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '匯款方式 1:網銀轉帳 2:ATM自動櫃員機 3:銀行櫃檯 4:手機銀行 5:其他',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '充值金額',
  `grand_total` int(11) NOT NULL DEFAULT '0' COMMENT '累計充值金額',
  `today_total` int(11) NOT NULL DEFAULT '0' COMMENT '當日充值金額',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '狀態 0:待審核 1:充值成功 2:充值失敗',
  `source` varchar(10) NOT NULL DEFAULT 'wap' COMMENT '來源 wap,pc,android,ios',
  `platform` tinyint(2) NOT NULL DEFAULT '0' COMMENT '平台 0:Windows 1:Android 2:IOS',
  `check_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '審核時間',
  `check_by` varchar(30) NOT NULL DEFAULT '' COMMENT '審核者',
  `check_remarks` varchar(100) NOT NULL DEFAULT '' COMMENT '審核備註(失敗)',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=643 DEFAULT CHARSET=utf8 COMMENT='充值訂單';

#
# Structure for table "bc_sysconfig"
#

CREATE TABLE `bc_sysconfig` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '流水號',
  `operator_id` int(11) NOT NULL DEFAULT '1' COMMENT '營運商ID',
  `varname` varchar(100) NOT NULL DEFAULT '' COMMENT '關鍵字',
  `value` text NOT NULL COMMENT '值',
  `info` varchar(100) NOT NULL DEFAULT '' COMMENT '說明',
  `groupid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '群組',
  `type` varchar(10) NOT NULL DEFAULT 'string' COMMENT '變數類型',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`),
  UNIQUE KEY `operator_id` (`operator_id`,`varname`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='系統參數';

#
# Structure for table "bc_user"
#

CREATE TABLE `bc_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session` char(32) NOT NULL DEFAULT '' COMMENT '登入唯一值',
  `user_name` varchar(50) NOT NULL COMMENT '用戶名',
  `security_pwd` char(32) NOT NULL DEFAULT '' COMMENT '用戶安全提現密碼',
  `user_pwd` char(32) NOT NULL DEFAULT '' COMMENT '用戶密碼',
  `real_name` varchar(50) NOT NULL COMMENT '姓名',
  `mobile` char(11) NOT NULL COMMENT '手機號碼',
  `money` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '貨幣',
  `money_frozen` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '凍結中貨幣',
  `money1` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '特色棋牌帳戶',
  `profit` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '輸贏',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '用戶類型 0:會員用戶 1:白名單用戶',
  `operator_id` int(11) NOT NULL DEFAULT '1' COMMENT '運營商ID',
  `agent_id` int(11) NOT NULL DEFAULT '0' COMMENT '代理商ID',
  `agent_pid` int(11) NOT NULL DEFAULT '0' COMMENT '上層玩家ID',
  `agent_code` char(7) NOT NULL DEFAULT '' COMMENT '代理邀請碼',
  `user_group_id` int(11) NOT NULL DEFAULT '1' COMMENT '會員群組',
  `referrer_code` varchar(50) NOT NULL DEFAULT '' COMMENT '用戶推薦碼',
  `referrer` varchar(50) NOT NULL DEFAULT '' COMMENT '推薦人代碼',
  `free_prediction` int(11) NOT NULL DEFAULT '0' COMMENT '免費看號次數',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '狀態 0:正常用戶 1:封號用戶 2:凍結用戶 3:標記用戶',
  `mode` int(11) NOT NULL DEFAULT '0' COMMENT '標記(二進位) 1:是否充值 2:是否二充 4:是否提現過',
  `remark` text COMMENT '備註',
  `vip_info_ios` text COMMENT 'ios的vip包資訊',
  `source` varchar(10) NOT NULL DEFAULT 'wap' COMMENT '來源 wap,pc,android,ios',
  `platform` tinyint(2) NOT NULL DEFAULT '0' COMMENT '平台 0:Windows 1:Android 2:IOS',
  `create_ua` varchar(300) NOT NULL DEFAULT '' COMMENT '註冊UA資訊',
  `create_domain` varchar(200) NOT NULL DEFAULT '' COMMENT '註冊網域',
  `create_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '註冊IP',
  `create_ip_info` text NOT NULL COMMENT '註冊IP資訊',
  `last_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '最後登入IP',
  `last_login_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '最後登入時間',
  `last_active_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '最後活動時間',
  `unlock_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '帳號解鎖時間',
  `first_recharge` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '首充時間',
  `first_money` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '首充金額',
  `second_recharge` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '二充時間',
  `special_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '特色棋牌類型 1:牛牛 2:搶莊牛牛',
  `special_id` int(11) NOT NULL DEFAULT '0' COMMENT '特色棋牌ID',
  `websocket_fd` int(11) NOT NULL DEFAULT '0' COMMENT 'WebSocket ID',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `mobile` (`mobile`),
  UNIQUE KEY `session` (`session`),
  KEY `agent_pid` (`agent_pid`),
  KEY `create_time` (`operator_id`,`create_time`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8 COMMENT='用戶列表';

#
# Structure for table "bc_user_bank"
#

CREATE TABLE `bc_user_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `bank_real_name` varchar(255) NOT NULL DEFAULT '' COMMENT '銀行卡姓名',
  `bank_name` varchar(100) NOT NULL COMMENT '銀行名稱',
  `bank_account` varchar(20) NOT NULL COMMENT '銀行帳號',
  `bank_address` varchar(200) NOT NULL COMMENT '銀行位址',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COMMENT='用戶銀行綁定';

#
# Structure for table "bc_user_group"
#

CREATE TABLE `bc_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '運營商ID',
  `name` varchar(50) NOT NULL COMMENT '群組名稱',
  `max_extract_money` int(11) NOT NULL DEFAULT '1' COMMENT '單次最大提現額度',
  `min_extract_money` int(11) NOT NULL DEFAULT '1' COMMENT '單次最小提現額度',
  `remark` varchar(50) NOT NULL COMMENT '備註',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否為預設群組',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '狀態 0:關閉 1:開啟',
  `sort` smallint(4) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  KEY `operator_id` (`operator_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='用戶群組';

#
# Structure for table "bc_user_login_log"
#

CREATE TABLE `bc_user_login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `ip` varchar(50) NOT NULL COMMENT '登入IP',
  `ip_info` text COMMENT 'IP位置資訊',
  `source` varchar(10) NOT NULL DEFAULT 'wap' COMMENT '來源 wap,pc,android,ios',
  `ua` varchar(500) NOT NULL DEFAULT '' COMMENT 'User Agent',
  `platform` tinyint(2) NOT NULL COMMENT '平台 0:Windows 1:Android 2:IOS',
  `source_url` varchar(200) NOT NULL COMMENT '登入網址',
  `domain_url` varchar(200) NOT NULL COMMENT '登入Domain',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `create_time` (`create_time`,`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=2250 DEFAULT CHARSET=utf8 COMMENT='用戶登入LOG';

#
# Structure for table "bc_user_money_log"
#

CREATE TABLE `bc_user_money_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT 'UID',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '類型',
  `money_type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '貨幣類型 0:現金帳戶 1:特色棋牌帳戶',
  `category` tinyint(2) NOT NULL DEFAULT '0' COMMENT '分類 0:無 1:經典彩 2:官方彩',
  `lottery_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種ID',
  `related_id` int(11) NOT NULL DEFAULT '0' COMMENT '關聯ID (特色棋牌ID)',
  `order_sn` varchar(50) NOT NULL COMMENT '訂單ID',
  `money_before` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '變動前餘額',
  `money_add` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '變動金額',
  `money_after` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '變動後餘額',
  `description` varchar(100) NOT NULL DEFAULT '' COMMENT '描述',
  `remark` varchar(100) NOT NULL DEFAULT '' COMMENT '備註',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `create_time` (`create_time`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=15878 DEFAULT CHARSET=utf8 COMMENT='玩家餘額變動LOG';

#
# Structure for table "bc_user_rakeback"
#

CREATE TABLE `bc_user_rakeback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '營運商ID',
  `user_group_id` int(11) NOT NULL COMMENT '會員分層ID',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '類型 0:投注 1:負盈利返水',
  `category` int(11) NOT NULL DEFAULT '0' COMMENT '玩法類別 0:全部 1:經典彩 2:官方彩',
  `lottery_type_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種類型',
  `lottery_id` int(11) NOT NULL DEFAULT '0' COMMENT '彩種ID',
  `rakeback_per` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '返水比率',
  `rakeback_max` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '返水上限',
  `start_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '起算金額',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_group_id` (`user_group_id`,`type`,`category`,`lottery_type_id`,`lottery_id`,`start_money`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='返水設置';

#
# Structure for table "bc_user_withdraw"
#

CREATE TABLE `bc_user_withdraw` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用戶ID',
  `order_sn` varchar(30) NOT NULL DEFAULT '' COMMENT '訂單號',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金額',
  `bank_realname` varchar(100) NOT NULL COMMENT '姓名',
  `bank_name` varchar(100) NOT NULL COMMENT '銀行名稱',
  `bank_account` varchar(20) NOT NULL COMMENT '銀行帳號',
  `grand_total` int(11) NOT NULL DEFAULT '0' COMMENT '累計提現金額',
  `today_total` int(11) NOT NULL DEFAULT '0' COMMENT '當日提現金額',
  `check_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '審核時間',
  `check_by` varchar(30) NOT NULL DEFAULT '' COMMENT '審核者',
  `check_remarks` varchar(100) NOT NULL DEFAULT '' COMMENT '審核備註(失敗)',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '狀態 0:待審核 1:提現成功 2:提現失敗',
  `source` varchar(10) NOT NULL DEFAULT 'wap' COMMENT '來源 wap,pc,android,ios',
  `platform` tinyint(2) NOT NULL DEFAULT '0' COMMENT '平台 0:Windows 1:Android 2:IOS',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  `create_by` varchar(30) NOT NULL DEFAULT '' COMMENT '新增者',
  `update_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '更新時間',
  `update_by` varchar(30) NOT NULL DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`,`uid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=348 DEFAULT CHARSET=utf8 COMMENT='用戶提現';

#
# Structure for table "bc_websocket_log"
#

CREATE TABLE `bc_websocket_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT 'UID',
  `special_id` int(11) NOT NULL DEFAULT '0' COMMENT '特色棋牌ID',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '類型',
  `fd` int(11) NOT NULL DEFAULT '0' COMMENT 'WebSocket fd',
  `data` text NOT NULL COMMENT '參數',
  `return_data` text NOT NULL COMMENT '回傳參數',
  `exec_time` float(7,4) NOT NULL DEFAULT '0.0000' COMMENT '執行時間',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' COMMENT '建檔時間',
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`,`uid`),
  KEY `special_id` (`create_time`,`special_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1384 DEFAULT CHARSET=utf8 COMMENT='WebSocket LOG';

#
# Structure for table "ip2location"
#

CREATE TABLE `ip2location` (
  `ip_from` int(10) unsigned DEFAULT NULL,
  `ip_to` int(10) unsigned DEFAULT NULL,
  `country_code` char(2) DEFAULT NULL,
  `country_name` varchar(64) DEFAULT NULL,
  `region_name` varchar(128) DEFAULT NULL,
  `city_name` varchar(128) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `zip_code` varchar(30) DEFAULT NULL,
  `time_zone` varchar(8) DEFAULT NULL,
  KEY `idx_ip_from` (`ip_from`) USING BTREE,
  KEY `idx_ip_from_to` (`ip_from`,`ip_to`) USING BTREE,
  KEY `idx_ip_to` (`ip_to`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Event "CCU1"
#

CREATE EVENT `CCU1` ON SCHEDULE EVERY 1 MINUTE STARTS '2019-06-06 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO INSERT INTO bc_concurrent_user (operator_id,per,minute_time,count,create_time)
SELECT t.id, 1, DATE_FORMAT(now(),'%Y-%m-%d %H:%i:00'), IFNULL(t1.count,0), now() count FROM bc_operator t
LEFT JOIN (
  SELECT operator_id,COUNT(id) `count` FROM bc_user
  WHERE last_active_time >= now() - INTERVAL 10 MINUTE
  AND unlock_time < now()
  GROUP BY operator_id
) t1 ON t.id = t1.operator_id;

#
# Event "CCU10"
#

CREATE EVENT `CCU10` ON SCHEDULE EVERY 10 MINUTE STARTS '2019-07-23 14:20:00' ON COMPLETION NOT PRESERVE ENABLE DO INSERT INTO bc_concurrent_user (operator_id,per,minute_time,count,create_time)
SELECT t.id, 10, DATE_FORMAT(now(),'%Y-%m-%d %H:%i:00'), IFNULL(t1.count,0), now() count FROM bc_operator t
LEFT JOIN (
  SELECT operator_id,COUNT(id) `count` FROM bc_user
  WHERE last_active_time >= now() - INTERVAL 20 MINUTE
  AND unlock_time < now()
  GROUP BY operator_id
) t1 ON t.id = t1.operator_id;

#
# Event "CCU30"
#

CREATE EVENT `CCU30` ON SCHEDULE EVERY 30 MINUTE STARTS '2019-07-23 14:30:00' ON COMPLETION NOT PRESERVE ENABLE DO INSERT INTO bc_concurrent_user (operator_id,per,minute_time,count,create_time)
SELECT t.id, 30, DATE_FORMAT(now(),'%Y-%m-%d %H:%i:00'), IFNULL(t1.count,0), now() count FROM bc_operator t
LEFT JOIN (
  SELECT operator_id,COUNT(id) `count` FROM bc_user
  WHERE last_active_time >= now() - INTERVAL 40 MINUTE
  AND unlock_time < now()
  GROUP BY operator_id
) t1 ON t.id = t1.operator_id;

#
# Event "CCU5"
#

CREATE EVENT `CCU5` ON SCHEDULE EVERY 5 MINUTE STARTS '2019-07-23 14:10:00' ON COMPLETION NOT PRESERVE ENABLE DO INSERT INTO bc_concurrent_user (operator_id,per,minute_time,count,create_time)
SELECT t.id, 5, DATE_FORMAT(now(),'%Y-%m-%d %H:%i:00'), IFNULL(t1.count,0), now() count FROM bc_operator t
LEFT JOIN (
  SELECT operator_id,COUNT(id) `count` FROM bc_user
  WHERE last_active_time >= now() - INTERVAL 15 MINUTE
  AND unlock_time < now()
  GROUP BY operator_id
) t1 ON t.id = t1.operator_id;

#
# Event "CCU60"
#

CREATE EVENT `CCU60` ON SCHEDULE EVERY 1 HOUR STARTS '2019-07-23 15:00:00' ON COMPLETION NOT PRESERVE ENABLE DO INSERT INTO bc_concurrent_user (operator_id,per,minute_time,count,create_time)
SELECT t.id, 60, DATE_FORMAT(now(),'%Y-%m-%d %H:%i:00'), IFNULL(t1.count,0), now() count FROM bc_operator t
LEFT JOIN (
  SELECT operator_id,COUNT(id) `count` FROM bc_user
  WHERE last_active_time >= now() - INTERVAL 70 MINUTE
  AND unlock_time < now()
  GROUP BY operator_id
) t1 ON t.id = t1.operator_id;

#
# Event "DeleteAPILog"
#

CREATE EVENT `DeleteAPILog` ON SCHEDULE EVERY 1 DAY STARTS '2019-08-31 04:00:00' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM bc_api_log WHERE create_time < now()- INTERVAL 30 DAY;

#
# Event "DeleteWebSocketLog"
#

CREATE EVENT `DeleteWebSocketLog` ON SCHEDULE EVERY 1 DAY STARTS '2019-08-31 04:00:00' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM bc_websocket_log WHERE create_time < now()- INTERVAL 30 DAY;

#
# Event "UpdateRechargeStatus"
#

CREATE EVENT `UpdateRechargeStatus` ON SCHEDULE EVERY 2 MINUTE STARTS '2019-05-17 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE bc_recharge_order SET status = 2 WHERE type = 1 AND status = 3 AND create_time <= now() - INTERVAL 10 MINUTE;
