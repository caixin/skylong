<?php

/**
 * Created by PhpStorm.
 * User: amao
 * Date: 18-6-25
 * Time: 上午10:18
 */
class One_two_three_select extends  Common_combination
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * pk 1,2,3名选一
     */
    public function one_Two_Three_Select($arr){
        $arr_one=explode(',',$arr);
        //$arr_one=array("01","02","03","04","05","06","07","08","09","10");//组合数
        $result=$this->combinations($arr_one, 1);
        $data=array();
        foreach ($result as $value){
            $data[]=$value[0];
        }
        return $data;
    }
}