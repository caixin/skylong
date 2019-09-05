<?php defined('BASEPATH') || exit('No direct script access allowed');

class Analysis extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        redirect($this->router->class."/current");
    }

    public function current()
    {
        $this->load->model('concurrent_user_model', 'concurrent_user_db');
        $this->load->library('pagination');
        
        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }
        
        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['minute_time', 'asc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];

        if (!isset($where['per'])) {
            $where['per'] = 1;
        }
        if (!isset($where['minute_time2'])) {
            $where['minute_time2'] = date('Y-m-d H:i:00', time()-60);
        }
        if (!isset($where['minute_time1'])) {
            $where['minute_time1'] = date('Y-m-d H:i:00', time()-1800);
        }
        //預設值
        $table = $chart_data = $chart = [];
        for ($i=strtotime($where['minute_time1']);$i<=strtotime($where['minute_time2']);$i+=60*$where['per']) {
            $minute = date('i', $i);
            $mod = $minute % $where['per'];
            $i += $mod == 0 ? 0:($where['per'] - $mod) * 60;
            $chart_data[$i] = 0;
        }

        $count = $this->concurrent_user_db->escape(false)
                ->select('minute_time,SUM(count) count')->where($where)
                ->group('minute_time')->order($order)
                ->count();
        if ($count <= 200) {
            $result = $this->concurrent_user_db->escape(false)
                ->select('minute_time,SUM(count) count')->where($where)
                ->group('minute_time')->order($order)
                ->result();
            //填入人數
            foreach ($result as $key => $row) {
                $chart_data[strtotime($row['minute_time'])] = $row['count'];
            }
            //轉換格式
            foreach ($chart_data as $key => $val) {
                $chart[] = [date('m-d H:i', $key),(int)$val];
                $table[] = [
                    'time' 	=> date('m-d H:i', $key),
                    'count'	=> $val
                ];
            }
            krsort($table);
        }
        
        $this->layout->view($this->cur_url, [
            'count'      => $count,
            'table'      => $table,
            'chart'      => $chart,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
        ]);
    }
    
    public function chart()
    {
        $this->load->model('user_model', 'user_db');
        $this->load->model('daily_analysis_model', 'daily_analysis_db');
        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['day_time', 'asc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        
        if (!isset($where['type'])) {
            $where['type'] = 1;
        }
        if (!isset($where['day_time1'])) {
            $where['day_time1'] = date('Y-m-d', time()-86400*30);
        }
        if (!isset($where['day_time2'])) {
            $where['day_time2'] = date('Y-m-d', time()-86400);
        }
        
        $table = $chart = [];
        for ($i=strtotime($where['day_time1']);$i<=strtotime($where['day_time2']);$i+=86400) {
            $table[$i] = 0;
        }
        
        // get main data.
        $result = $this->daily_analysis_db->escape(false)
                ->select('day_time,SUM(count) count')->where($where)
                ->group('day_time')->order($order)
                ->result();
        
        foreach ($result as $key => $row) {
            $table[strtotime($row['day_time'])] = $row['count'];
        }
        
        foreach ($table as $key => $val) {
            $chart[] = [date('Y-m-d', $key),(int)$val];
        }
        krsort($table);
        
        $this->layout->view($this->cur_url, [
            'table'      => $table,
            'chart'      => $chart,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
        ]);
    }
    
    public function retention()
    {
        $this->load->model('daily_retention_model', 'daily_retention_db');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['type', 'asc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        
        $where['day_time'] = date('Y-m-d', time()-86400);
        $result = $this->daily_retention_db->escape(false)
                ->select('type,SUM(day_count) day_count,SUM(all_count) all_count,ROUND(AVG(avg_money)) avg_money')
                ->where($where)->group('type')->order($order)->result();
        
        $total = 0;
        foreach ($result as $key => $row) {
            $total = $row['all_count'];
            $row['percent'] = $total == 0 ? 0:round($row['day_count']/$total*100, 2);
            $result[$key] = $row;
        }
        
        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
        ]);
    }
    
    public function retention_chart()
    {
        $this->load->model('daily_retention_model', 'daily_retention_db');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['type', 'asc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        
        if (!isset($where['day_time1'])) {
            $where['day_time1'] = date('Y-m-d', time()-86400*10);
        }
        if (!isset($where['day_time2'])) {
            $where['day_time2'] = date('Y-m-d', time()-86400);
        }
        
        $date = $table = $chart = [];
        for ($i=strtotime($where['day_time1']);$i<=strtotime($where['day_time2']);$i+=86400) {
            $date[] = date('Y-m-d', $i);
        }
        
        for ($i=1;$i<=6;$i++) {
            for ($j=strtotime($where['day_time1']);$j<=strtotime($where['day_time2']);$j+=86400) {
                $table[$i][$j] = 0;
            }
        }
        
        // get main data.
        $result = $this->daily_retention_db->escape(false)
                ->select('type,day_time,SUM(day_count) day_count,SUM(all_count) all_count,ROUND(AVG(avg_money)) avg_money')
                ->where($where)->group('type,day_time')->order($order)->result();
        
        foreach ($result as $row) {
            $table[$row['type']][strtotime($row['day_time'])] = $row['day_count'];
        }
        
        foreach ($table as $type => $row) {
            $arr = [];
            foreach ($row as $val) {
                $arr[] = (int)$val;
            }
            
            $chart[] = [
                'name'  => daily_retention_model::$typeList[$type],
                'data'  => $arr,
                'color' => daily_retention_model::$typeColor[$type],
            ];
        }
        
        $this->layout->view($this->cur_url, [
            'date'       => $date,
            'table'      => $table,
            'chart'      => $chart,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
        ]);
    }
    
    public function retention_analysis()
    {
        $this->load->model('user_model', 'user_db');
        $this->load->model('daily_retention_model', 'daily_retention_db');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['id', 'asc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        
        $where['type'] = 0;
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d', time()-86400*30);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d', time()-86400);
        }
        
        // get total.
        $total = $this->user_db->where($where)->count();
        
        $result = [];
        for ($i=1;$i<=9;$i++) {
            $row = $this->user_db->retention_analysis($where['create_time1'], $where['create_time2'], $i);
            $row['type'] = $i;
            $row['percent'] = $total == 0 ? 0:round($row['count']/$total*100, 2);
            $result[] = $row;
        }
        
        $this->layout->view($this->cur_url, [
            'result'     => $result,
            'total'      => $total,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
        ]);
    }
    
    public function retention_user()
    {
        $this->load->model('user_model', 'user_db');
        $this->load->model('daily_retention_user_model', 'daily_retention_user_db');
        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }

        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['type', 'asc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        
        if (!isset($where['day_time1'])) {
            $where['day_time1'] = date('Y-m-d', time()-86400*6);
        }
        if (!isset($where['day_time2'])) {
            $where['day_time2'] = date('Y-m-d', time()-86400);
        }
        
        $date = $table = $chart = [];
        for ($i=strtotime($where['day_time1']);$i<=strtotime($where['day_time2']);$i+=86400) {
            $date[] = date('Y-m-d', $i);
        }
        
        for ($i=1;$i<=5;$i++) {
            for ($j=strtotime($where['day_time1']);$j<=strtotime($where['day_time2']);$j+=86400) {
                $table[$i][$j] = '0%(0/0)';
            }
        }
        
        // get main data.
        $result = $this->daily_retention_user_db->escape(false)
                ->select('type,day_time,SUM(day_count) day_count,SUM(all_count) all_count')
                ->where($where)->group('type,day_time')->order($order)->result();
        
        $table2 = $table;
        foreach ($result as $row) {
            $percent = $row['all_count'] == 0 ? 0:round($row['day_count'] / $row['all_count'] * 100, 2);
            $table[$row['type']][strtotime($row['day_time'])] = $percent."%($row[day_count]/$row[all_count])";
            $table2[$row['type']][strtotime($row['day_time'])] = $percent;
        }
        
        foreach ($table2 as $type => $row) {
            $arr = [];
            foreach ($row as $val) {
                $arr[] = (int)$val;
            }
            
            $chart[] = [
                'name' => daily_retention_user_model::$typeList[$type],
                'data' => $arr,
            ];
        }
        
        $this->layout->view($this->cur_url, [
            'date'       => $date,
            'table'      => $table,
            'chart'      => $chart,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
        ]);
    }
    
    public function distribution()
    {
        $this->load->model('user_login_log_model', 'user_login_log_db');

        // redirect to search uri.
        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            redirect(get_search_uri($this->input->post(), $this->cur_url));
        }
        
        // get params.
        $params        = $this->uri->uri_to_assoc(3);
        $search_params = param_process($params, ['create_time', 'desc']);
        $order         = $search_params['order'];
        $where         = $search_params['where'];
        $params_uri    = $search_params['params_uri'];
        
        if (!isset($where['create_time1'])) {
            $where['create_time1'] = date('Y-m-d H:i:s', time()-86400);
        }
        if (!isset($where['create_time2'])) {
            $where['create_time2'] = date('Y-m-d H:i:s');
        }
        
        // get main data.
        $join[] = [$this->table_ . 'user t1', 't.uid = t1.id', 'left'];
        $result = $this->user_login_log_db->select('uid,ip_info')->join($join)
                ->where($where)->group('uid,ip_info')->result();
        $table = [];
        foreach ($result as $row) {
            $info = json_decode($row['ip_info'], true);
            if ($info == []) {
                continue;
            }
            $table[] = [
                'lat' => (float)$info['latitude'],
                'lng' => (float)$info['longitude']
            ];
        }
        
        $this->layout->view($this->cur_url, [
            'table'      => $table,
            'where'      => $where,
            'order'      => $order,
            'params_uri' => $params_uri,
        ]);
    }
}
