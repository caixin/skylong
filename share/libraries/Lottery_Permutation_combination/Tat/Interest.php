<?php

/**
 * 趣味
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午6:00
 */
class Interest extends  Common_combination
{

    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 一帆风顺
     * @param $arr_one
     * @return array
     */
    public function everything_Is_Going_Smoothly($arr){
        $arr_one=explode(',', $arr);
        if (count($arr)<1){
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);
        $result=$this->combinations($arr_one, 1);
        $this->data=array();
        foreach ($result as $key=>$value){
            $this->data[]=implode("",$value);
        }
        return $this->data;
    }

}