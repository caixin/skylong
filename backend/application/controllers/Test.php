<?php defined('BASEPATH') || exit('No direct script access allowed');

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
}
