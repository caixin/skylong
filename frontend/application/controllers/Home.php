<?php defined('BASEPATH') || exit('No direct script access allowed');

class Home extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $web = isset($_SERVER['WEB_ENV']) ? $_SERVER['WEB_ENV']:'wap';
        $this->load->view($web);
    }
}
