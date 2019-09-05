<?php defined('BASEPATH') || exit('No direct script access allowed');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Monolog
{
    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;
    const ALERT = 550;
    const EMERGENCY = 600;

    public static $folder = 'backend';

    /**
     * Monolog 日记记录
     * @param string $channel  日志频道  注：次频道是为了更加细粒度的分解日志信息
     * @param integer $level   日志类型  DEBUG = 100;INFO = 200;NOTICE = 250;WARNING = 300;ERROR = 400;CRITICAL = 500;ALERT = 550;EMERGENCY = 600;
     * @param string|array $message  日志内容
     */
    public static function writeLogs($channel, $level, $message)
    {
        // Create some handlers
        // 创建一个handler，monolog决定把日志发送到哪里，就是由相应的handler操作的。
        $date = date('Ymd');
        $folder = self::$folder;
        $handler = new StreamHandler("../logs/$folder/$channel/{$channel}_{$level}_{$date}.log", $level, true, 0777);
        //创建频道
        $logger = new Logger($channel);
        $logger->pushHandler($handler);
        $logger->addRecord($level, json_encode($message, JSON_UNESCAPED_UNICODE));
    }
}
