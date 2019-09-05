<?php

//MQTT連結資訊
$config['mqtt_server'] = '47.244.28.1';
$config['mqtt_port'] = 1883;
$config['mqtt_clientid'] = 'mqtt-Web' . rand();
$config['mqtt_username'] = 'tladmin';
$config['mqtt_password'] = 'zxc123';

//Telegram資訊
$config['telegram_bot_token'] = '630504500:AAHTNXB0z0uwAE4kTnuMTAue8ZZuNgMHWyY';
$config['telegram_chatid_development'] = '-1001442502977'; //警訊公告-測試
$config['telegram_chatid_production'] = '-1001252595092'; //警訊公告-正式
$config['telegram_chatid_lottery'] = '-1001265141945'; //開獎延遲公告
