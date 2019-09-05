<?php defined('BASEPATH') || exit('No direct script access allowed');

function encode_search_params($params, $zero=[])
{
    foreach ($params as $key => $value) {
        if ($value == '' or $value == 'all' || (in_array($key, $zero) && $value == '0')) {
            unset($params[$key]);
        } else {
            if (is_array($value)) {
                $value = implode('|,|', $value).'array';
            }
            $params[$key] = urlencode($value);
        }
    }

    return $params;
}

function decode_search_params($params)
{
    foreach ($params as $key => $value) {
        $value = urldecode($value);
        if (strpos($value, 'array') !== false) {
            $value = explode('|,|', substr($value, 0, strpos($value, 'array')));
        }
        $params[$key] = $value;
    }

    return $params;
}

function get_search_uri($params, $uri, $zero=[])
{
    $ci =& get_instance();

    $params     = encode_search_params($params, $zero);
    $params_uri = $ci->uri->assoc_to_uri($params);

    return $uri.'/'.$params_uri;
}

function get_page($params)
{
    return isset($params['page']) ? (int) $params['page'] : 1;
}

function get_order($params, $default_order = [])
{
    $order = $default_order;

    isset($params['asc']) && $order = [$params['asc'], 'asc'];
    isset($params['desc']) && $order = [$params['desc'], 'desc'];

    return $order;
}

function param_process($params, $default_order = [])
{
    $ci =& get_instance();

    $result['page']  = get_page($params);
    $result['order'] = get_order($params, $default_order);

    unset($params['page']);

    $result['params_uri'] = $ci->uri->assoc_to_uri($params);
    
    unset($params['asc']);
    unset($params['desc']);

    $result['where'] = decode_search_params($params);

    return $result;
}

function sort_title($key, $name, $base_uri, $order, $where = [])
{
    $ci =& get_instance();
    
    $where     = encode_search_params($where);
    $where_uri = $ci->uri->assoc_to_uri($where);
    $order_uri = ($order[1] === 'asc') ? 'desc/'.$key : 'asc/'.$key;

    $where_uri = ($where_uri) ? '/'.$where_uri : '';
    $order_uri = ($order_uri) ? '/'.$order_uri : '';

    $class = ($order[0] === $key) ? 'sort '.$order[1] : 'sort';

    return '<a class="'.$class.'" href="'.site_url($base_uri.$where_uri.$order_uri).'">'.$name.'</a>';
}

function lists_message($type='success')
{
    $ci =& get_instance();
    if ($ci->session->flashdata('message')) {
        return '<div id="message" class="alert alert-'.$type.' alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa fa-check"></i> 讯息!</h4>
            '.$ci->session->flashdata('message').'
        </div>
        <script>
            setTimeout(function() { $(\'#message\').slideUp(); }, 3000);
            $(\'#message .close\').click(function() { $(\'#message\').slideUp(); });
        </script>';
    }
    return '';
}
