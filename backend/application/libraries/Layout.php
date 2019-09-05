<?php defined('BASEPATH') || exit('No direct script access allowed');

class Layout
{
    public $ci;
    public $layout;
    public $sidebar = true;

    public function __construct($layout='layout_main')
    {
        $this->ci =& get_instance();
        $this->layout = $layout;
    }

    public function set_layout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * 視圖
     *
     * @param string $view
     * @param array $data
     * @param boolean $return
     * @return void|string
     */
    public function view($view, $data = null, $return = false)
    {
        $data['content_for_layout'] = $this->ci->load->view($view, $data, true);
        $data['sidebar'] = $this->sidebar;

        if ($return) {
            return $this->ci->load->view($this->layout, $data, true);
        } else {
            $this->ci->load->view($this->layout, $data, false);
        }
    }
}
