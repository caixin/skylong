<?php defined('BASEPATH') || exit('No direct script access allowed');

use Plunar\Plunar;
use Plunar\PlunarException;

class Test extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function clear_cache()
    {
        $this->load->driver('cache');
        $this->cache->redis->clean();
    }
    
    public function calendar()
    {
        echo mktime(0, 0, 0, 2, 9, 2100);
        exit();
        try {
            //支持字符串输入形式 Plunar::solarToLunar('1984-09-22');
            $lunar_array = Plunar::solarToLunar(2020, 1, 25);
        } catch (PlunarException $e) {
            echo $e->getMessage();
            exit;
        }
        
        var_dump($lunar_array);
    }
}
