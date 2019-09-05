<?php defined('BASEPATH') || exit('No direct script access allowed');

//十進制轉二進制成陣列
if (! function_exists('bindec_array')) {
    function bindec_array($decimal, $reverse=false, $inverse=false)
    {
        $bin = decbin($decimal);
        if ($inverse) {
            $bin = str_replace("0", "x", $bin);
            $bin = str_replace("1", "0", $bin);
            $bin = str_replace("x", "1", $bin);
        }
        $total = strlen($bin);
        
        $stock = [];
        
        for ($i = 0; $i < $total; $i++) {
            if ($bin{$i} != 0) {
                $bin_2 = str_pad($bin{$i}, $total - $i, 0);
                array_push($stock, bindec($bin_2));
            }
        }
        
        $reverse ? rsort($stock):sort($stock);
        return $stock;
    }
}

if (!function_exists("xml_to_array")) {
    function xml_to_array($xml)
    {
        $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            $arr = [];
            for ($i = 0; $i < $count; $i++) {
                $key = $matches[1][$i];
                $val = xml_to_array($matches[2][$i]);  // 递归
                if (array_key_exists($key, $arr)) {
                    if (is_array($arr[$key])) {
                        if (!array_key_exists(0, $arr[$key])) {
                            $arr[$key] = [$arr[$key]];
                        }
                    } else {
                        $arr[$key] = [$arr[$key]];
                    }
                    $arr[$key][] = $val;
                } else {
                    $arr[$key] = $val;
                }
            }
            return $arr;
        } else {
            return $xml;
        }
    }
}

/**
 * PHP判断字符串纯汉字 OR 纯英文 OR 汉英混合
 * return 1: 英文
 * return 2：纯汉字
 * return 3：汉字和英文
 */
function utf8_str($str)
{
    $mb = mb_strlen($str, 'utf-8');
    $st = strlen($str);
    if ($st == $mb) {
        return 1;
    }
    if ($st % $mb == 0 && $st % 3 == 0) {
        return 2;
    }
    return 3;
}

/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其他編碼
 +----------------------------------------------------------
 * @param string $str 需要轉換的字符串
 * @param integer $start 開始位置
 * @param integer $length 截取長度
 * @param string $charset 編碼格式
 * @param string $suffix 截斷顯示字符
 * @param string $strength 字符串的長度
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start = 0, $length, $strength, $charset="utf-8", $suffix=true)
{
    if (function_exists("mb_substr")) {
        if ($suffix) {
            if ($length < $strength) {
                return mb_substr($str, $start, $length, $charset) . "....";
            } else {
                return mb_substr($str, $start, $length, $charset);
            }
        } else {
            return mb_substr($str, $start, $length, $charset);
        }
    } elseif (function_exists('iconv_substr')) {
        if ($suffix) {//是否加上......符号
            if ($length < $strength) {
                return iconv_substr($str, $start, $length, $charset) . "....";
            } else {
                return iconv_substr($str, $start, $length, $charset);
            }
        } else {
            return iconv_substr($str, $start, $length, $charset);
        }
    }
    $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    if ($suffix) {
        return $slice . "…";
    } else {
        return $slice;
    }
}

/**  摘自 discuz
 * @param string $string 明文或密文
 * @param string $operation 加密ENCODE或解密DECODE
 * @param string $key 密鑰
 * @param integer $expiry 密鑰有效期，默認是一直有效
 */
if (!function_exists("auth_code")) {
    function auth_code($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        /*
         動態密匙長度，相同的明文會生成不同密文就是依靠動態密匙
        加入隨機密鑰，可以令密文無任何規律，即便是原文和密鑰完全相同，加密結果也會每次不同，增大破解難度。
        取值越大，密文變動規律越大，密文變化 = 16 的 $ckey_length 次方
        當此值爲 0 時，則不產生隨機密鑰
         */
        $ckey_length = 4;
        $key = md5($key != '' ? $key : "JliNlk1i1103141220171231"); //此處的key可以自己進行定義，寫到配置文件也可以
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        //明文，前10位用來保存時間戳，解密時驗證數據有效性，10到26位用來保存$keyb(密匙b)，解密時會通過這個密匙驗證數據完整性
        //如果是解碼的話，會從第$ckey_length位開始，因爲密文前$ckey_length位保存 動態密匙，以保證解密正確
        $string = $operation == 'DECODE' ? base64_decode(substr(str_replace(['-', '_'], ['+', '/'], $string), $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = [];
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            //把動態密匙保存在密文裏，這也是爲什麼同樣的明文，生產不同密文後能解密的原因
            //因爲加密後的密文可能是一些特殊字符，複製過程可能會丟失，所以用base64編碼
            return $keyc . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($result));
        }
    }
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * @return string
 */
if (!function_exists("think_encrypt")) {
    function think_encrypt($data, $key = '', $expire = 0)
    {
        $key = md5(empty($key) ? "speakphp.com" : $key);
        $data = base64_encode($data);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        $str = sprintf('%010d', $expire ? $expire + time() : 0);
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
        }
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
    }
}

/**
 * 系统解密方法
 * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key  加密密钥
 * @return string
 */
if (!function_exists("think_decrypt")) {
    function think_decrypt($data, $key = '')
    {
        $key = md5(empty($key) ? "speakphp.com" : $key);
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        $data = base64_decode($data);
        $expire = substr($data, 0, 10);
        $data = substr($data, 10);
        if ($expire > 0 && $expire < time()) {
            return '';
        }
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = $str = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return base64_decode($str);
    }
}

if (!function_exists("downloadImage")) {
    function downloadImage($url, $filepath)
    {
        //服务器返回的头信息
        $responseHeaders = [];
        //原始图片名
        $originalfilename = '';
        //图片的后缀名
        $ext = '';
        $ch = curl_init($url);
        //设置curl_exec返回的值包含Http头
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //设置curl_exec返回的值包含Http内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //设置抓取跳转（http 301，302）后的页面
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        //设置最多的HTTP重定向的数量
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
        //服务器返回的数据（包括http头信息和内容）
        $html = curl_exec($ch);
        //获取此次抓取的相关信息
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);
        if ($html !== false) {
            //分离response的header和body，由于服务器可能使用了302跳转，所以此处需要将字符串分离为 2+跳转次数 个子串
            $httpArr = explode("\r\n\r\n", $html, 2 + $httpinfo['redirect_count']);
        }
        //倒数第二段是服务器最后一次response的http头
        $header = $httpArr[count($httpArr) - 2];
        //倒数第一段是服务器最后一次response的内容
        $body = $httpArr[count($httpArr) - 1];
        $header .= "\r\n";
        //获取最后一次response的header信息
        preg_match_all('/([a-z0-9-_]+):\s*([^\r\n]+)\r\n/i', $header, $matches);
        if (!empty($matches) && count($matches) == 3 && !empty($matches[1]) && !empty($matches[1])) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                if (array_key_exists($i, $matches[2])) {
                    $responseHeaders[$matches[1][$i]] = $matches[2][$i];
                }
            }
        }
        //获取图片后缀名
        if (0 < preg_match('{(?:[^\/\\\\]+)\.(jpg|jpeg|gif|png|bmp)$}i', $url, $matches)) {
            $originalfilename = $matches[0];
            $ext = $matches[1];
        } else {
            if (array_key_exists('Content-Type', $responseHeaders)) {
                if (0 < preg_match('{image/(\w+)}i', $responseHeaders['Content-Type'], $extmatches)) {
                    $ext = $extmatches[1];
                }
            }
        }
        //保存文件
        if (!empty($ext)) {
            $filepath .= ".$ext";
            $local_file = fopen($filepath, 'w');
            if (false !== $local_file) {
                if (false !== fwrite($local_file, $body)) {
                    fclose($local_file);
                    $sizeinfo = getimagesize($filepath);
                    return [
                        'filepath'        => realpath($filepath),
                        'width'           => $sizeinfo[0],
                        'height'          => $sizeinfo[1],
                        'orginalfilename' => $originalfilename,
                        'filename'        => pathinfo($filepath, PATHINFO_BASENAME)
                    ];
                }
            }
        }
        return false;
    }
}

if (!function_exists("https_request")) {
    function https_request($url, $data = null, $headers = "Content-Type:application/json;charset=utf-8")
    {
        //$header[]="Content-Type:application/json;charset=utf-8";
        //$header[] = 'application/x-www-form-urlencoded;charset=utf-8';
        $header[] = $headers;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            if (!is_array($data)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}

/**
 * 替换当前url中的参数值
 *
 * @param string $url
 * @param array $replace 需要替换的值 格式如下: array('name'=>'wangjian' , 'age'=>'1111' , 'sex'=> 1   ) 第一个是参数 第二个是替换的值
 */
if (!function_exists("url_set_val")) {
    function url_set_val($url, $replace = [])
    {
        if (empty($replace)) {
            return $url;
        }
        list($url_f, $query) = explode('?', $url);
        parse_str($query, $arr);
        if ($arr) {
            foreach ($arr as $kk => $vv) {
                if (array_key_exists($kk, $replace)) {
                    $arr[$kk] = $replace[$kk];
                }
            }
        }
        return $url_f . '?' . http_build_query($arr);
    }
}

if (!function_exists("deldir")) {
    function deldir($dir, $not_del = [])
    {
        //先删除目录下的文件：
        $dh = opendir($dir);

        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;

                if (!is_dir($fullpath)) {
                    if (!in_array($file, $not_del)) {
                        unlink($fullpath);
                    }
                } else {
                    deldir($fullpath);
                }
            }
        }
        closedir($dh);

        return true;
    }
}

/**
 * csv_get_lines 读取CSV文件中的某几行数据
 * @param string $csvfile csv文件路径
 * @param integer $lines 读取行数
 * @param integer $offset 起始行数
 * @return array
 * */
if (!function_exists("csv_get_lines")) {
    function csv_get_lines($csvfile, $lines, $offset = 0)
    {
        if (!$fp = fopen($csvfile, 'r')) {
            return false;
        }
        $i = $j = 0;
        while (false !== ($line = fgets($fp))) {
            if ($i++ < $offset) {
                continue;
            }
            break;
        }
        $data = [];
        while (($j++ < $lines) && !feof($fp)) {
            $data[] = fgetcsv($fp);
        }
        fclose($fp);
        return $data;
    }
}

/**************************************************************
 *
 *    使用特定function對數組中所有元素做處理
 *    @param    string    &$array        要處理的字符串
 *    @param    string    $function    要執行的函數
 *    @return boolean    $apply_to_keys_also        是否也應用到key上
 *
 *************************************************************/
if (!function_exists("arrayRecursive")) {
    function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
}

/**************************************************************
 *
 *    將數組轉換爲JSON字符串（兼容中文）
 *    @param    array    $array        要轉換的數組
 *    @return string        轉換得到的json字符串
 *
 *************************************************************/
if (!function_exists("json_str")) {
    function json_str($array)
    {
        arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }
}

if (!function_exists('check_phone')) {
    function check_phone($phone)
    {
        $result = preg_match('/^1\d{10}$/', $phone);
        return $result;
    }
}

/**
 *  時間轉換字串
 */
if (!function_exists('change_time_to_str_mis')) {
    function change_time_to_str_mis($time)
    {
        $now = time();
        $t = $now - $time;
        //如果在一分钟内
        if ($t < 60) {
            return $t . '秒前';
        //一个小时内
        } elseif ($t < 3600) {
            return round($t / 60) . '分钟前';
        //一天内
        } elseif ($t < 24 * 3600) {
            return round($t / 3600) . '小时前';
        //一个月之内
        } elseif ($t < 24 * 3600 * 30) {
            return round($t / (3600 * 24)) . '天前';
        } else {
            return date("Y-m-d H:i:s");
        }
    }
}

/**
 * 数字排列组合
 */
if (!function_exists('combination')) {
    function combination($a, $m)
    {
        $r = [];
        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }

        for ($i = 0; $i < $n; $i++) {
            $t = [$a[$i]];

            if ($m == 1) {
                $r[] = $t;
            } else {
                $b = array_slice($a, $i + 1);
                $c = combination($b, $m - 1);
                foreach ($c as $v) {
                    $r[] = array_merge($t, $v);
                }
            }
        }

        return $r;
    }
}

/**
 * 替换手机号码
 */
if (!function_exists('replace_phone')) {
    function replace_phone($phone)
    {
        return preg_match('/^\d{11}$/', $phone) ? (substr($phone, 0, 3) . '****' . substr($phone, -3)) : $phone;
    }
}

function loginEncode($arr)
{
    $json = base64_encode(json_encode($arr));
    $json = auth_code($json, "ENCODE");
    return $json;
}

function loginDecode($str)
{
    $arr = auth_code($str, "DECODE");
    $arr = json_decode(base64_decode($arr), true);
    return $arr;
}

function GetRandStr($len)
{
    $chars = [
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    ];
    $charsLen = count($chars) - 1;
    shuffle($chars);
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}

function userPwdEncode($pwd)
{
    return md5(md5($pwd) . 'sb');
}

function create_order_sn($str = '')
{
    $order_sn = $str . date("YmdHis") . rand(100, 999);
    return $order_sn;
}
/**
 *
 *3号连选判断处理
 * @param array $arr
 * @param integer $n
 * @return bool
 */
function getconsecutive($arr, $n)
{
    sort($arr, $n);
    $m = 1;
    for ($i = 0, $t = count($arr) - 1; $i < $t; $i++) {
        $m = $arr[$i] + 1 == @$arr[$i + 1] ? $m + 1 : 1;
        if ($m >= $n) {
            return true;
        };
    }
    return false;
}

/**
 * 隐藏中间N位数
 */
function replace_middle($str, $n)
{
    $len = strlen($str);
    $s = ceil($len / 2) - ceil($n / 2);
    return substr_replace($str, str_repeat('*', $n), $s, $n);
}

function aes_encrypt($data)
{
    $key = 'lssb';
    $str = openssl_encrypt($data, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    $str = base64_encode($str);
    return $str;
}

/**
 * 數字向左補0
 *
 * @param string $str 開獎號碼 EX:1,2,3,4,5
 * @param integer $length 長度
 * @return string 處理後字串 EX:01,02,03,04,05
 */
function string_Pad_Zero_Left($str, $length = 2)
{
    if (trim($str) == '') {
        return '';
    }
    $numbers = explode(",", $str);
    foreach ($numbers as $k => $v) {
        $numbers[$k] = sprintf("%0" . $length . "d", $v);
    }
    return implode(",", $numbers);
}


//计算1个数值  和一组数值最相近的
function next_NumberArray($number, $numberRangeArray)
{
    $w = 0;
    $c = -1;
    $abstand = 0;
    foreach ($numberRangeArray as $key => $value) {
        if ($w == 0) {
            $w = $key;
        }
        $n = $numberRangeArray[$key];
        $abstand = ($n < $number) ? $number - $n : $n - $number;
        if ($c == -1) {
            $c = $abstand;
            continue;
        } elseif ($abstand < $c) {
            $c = $abstand;
            $w = $key;
        }
    }
    return [
        "key" => $w,
        "m_p" => $numberRangeArray[$w]
    ];
}

/**
 * 從號碼$a中取出$m 碼，所有的排列組合
 * 範例：01,02,03,04,05 取4個號碼的所有排列組合
 *
 * @param  array   $a [號碼]
 * @param  integer $m [取幾碼]
 * @return array      [所有的組合]
 */
if (!function_exists('combinations_str')) {
    function combinations_str($a, $m)
    {
        $numbers_arr = explode(",", $a);
        return combinations_arr($numbers_arr, $m);
    }
}
/**
 * 從號碼$a中取出$m 碼，所有的排列組合
 * 範例：['01','02','03','04','05']取4個號碼的所有排列組合
 *
 * @param  array   $a [號碼]
 * @param  integer $m [取幾碼]
 * @return array      [所有的組合]
 */
if (!function_exists('combinations_arr')) {
    function combinations_arr($a, $m)
    {
        $r = [];
        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }

        for ($i = 0; $i < $n; $i++) {
            $t = [$a[$i]];
            if ($m == 1) {
                $r[] = $t;
            } else {
                $b = array_slice($a, $i + 1);
                $c = combinations_arr($b, $m - 1);
                foreach ($c as $v) {
                    $r[] = array_merge($t, $v);
                }
            }
        }
        return $r;
    }
}

//二维数组排序
if (!function_exists('multi_array_sort')) {
    function multi_array_sort($arr, $key, $type = SORT_REGULAR, $short = SORT_DESC)
    {
        foreach ($arr as $k => $v) {
            $name[$k] = $v[$key];
        }
        array_multisort($name, $type, $short, $arr);
        return $arr;
    }
}

if (! function_exists('rand_pwd')) {
    /**
     * 隨機產生密碼
     * @param integer $pwd_len 密碼長度
     * @param integer $type
     * @return string
     */
    function rand_pwd($pwd_len, $type=0)
    {
        $password = '';
        if (!in_array($type, [0,1,2,3])) {
            return '';
        }
        
        // remove o,0,1,l
        if ($type == 0) {
            $word = 'abcdefghijkmnpqrstuvwxyz-ABCDEFGHIJKLMNPQRSTUVWXYZ_23456789';
        }
        if ($type == 1) {
            $word = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($type == 2) {
            $word = '123456789';
        }
        if ($type == 3) {
            $word = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
        }
        
        $len = strlen($word);
    
        for ($i = 0; $i < $pwd_len; $i++) {
            $password .= $word[rand(1, 99999) % $len];
        }
    
        return $password;
    }
}

if (! function_exists('get_platform')) {
    /**
     * 取得平台類型
     * @return int 0:Windown 1:Android 2:IOS
     */
    function get_platform()
    {
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']):'';
        $platform = 0; //預設Windows
        //IOS
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            $platform = 2;
        }
        //Android
        if (strpos($agent, 'android')) {
            $platform = 1;
        }

        return $platform;
    }
}
