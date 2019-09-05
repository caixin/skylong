<?php

class ApiHelp
{
    /**
     *
     * @param integer $status 1|0 请求状态
     * @param integer $code 状态码 请参照状态码表
     * @param string $message 提示信息
     * @param array $data 返回数据
     */
    public static function response($status, $code, $message = '', $data = [])
    {
        $CI = &get_instance();

        if ($status == 1) {
            //載入模組
            foreach ($CI->module as $row) {
                $keyword = $row['keyword'];
                if (is_dir($source = APPPATH."/module/$keyword/")) {
                    $controller = ucfirst($keyword.'_'.$CI->router->fetch_class());
                    if (is_file($source.$controller.'.php')) {
                        require_once($source.$controller.'.php');
                        $class =  new $controller();
                        $function = $CI->router->fetch_method();
                        if (method_exists($class, $function)) {
                            $data = $class->$function($data);
                        }
                    }
                }
            }
        }

        if (!is_numeric($code)) {
            exit(json_encode([
                'status'  => 0,
                'code'    => 100,
                'message' => '系统错误'
            ]));
        }
        $result = [
            'status'  => $status,
            'code'    => $code,
            'message' => $message,
        ];

        if ($status == 1) {
            $result['data'] = $data;
        }

        $return_str = json_encode($result, JSON_UNESCAPED_UNICODE);
        //更新API LOG
        $CI->apiAfter($return_str);

        echo $return_str;
        if ($status == 0) {
            exit();
        }
    }

    /**
     * 加密解密 ENCODE 加密 DECODE 解密...
     * @param string $string 加密字符
     * @param string $operation ENCODE:加密 DECODE:解密
     * @param string $key 加密密钥
     * @param integer $expiry 有效时间默认永久
     * @return string
     */
    public static function _encrypt($string, $operation = 'ENCODE', $key = '', $expiry = 0)
    {
        if ($operation == 'DECODE') {
            $string = str_replace('_', '/', $string);
        }
        $key_length = 4;
        $key = md5($key != '' ? $key : 'yilianfy.yxzt.com');
        $fixedkey = md5($key);
        $egiskeys = md5(substr($fixedkey, 16, 16));
        $runtokey = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), -$key_length) : substr(
            $string,
            0,
            $key_length
        )) : '';
        $keys = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));
        $string = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $egiskeys), 0, 16) .

            $string : base64_decode(substr($string, $key_length));

        $i = 0;
        $result = '';
        $string_length = strlen($string);
        for ($i = 0; $i < $string_length; $i++) {
            $result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));
        }
        if ($operation == 'ENCODE') {
            $retstrs = str_replace('=', '', base64_encode($result));
            $retstrs = str_replace('/', '_', $retstrs);
            return $runtokey . $retstrs;
        } else {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(
                substr($result, 26) . $egiskeys
            ), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        }
    }

    /**
     * 驗證手機號碼
     * @param  string $mobile 手機號碼
     * @return bool
     */
    public static function checkMobile($mobile = '')
    {
        $search = "/^1[0-9]{10}$/i";
        if (preg_match($search, $mobile)) {
            return true;
        }
        return false;
    }
}
