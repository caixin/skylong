<?php

/**
 * 不定胆
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午3:46
 */
class Location_no_gall extends  Common_combination
{

    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 不定位三星前3 2码不定位
     * @param $arr_one
     * @return array
     */
    public function bdw_Three_Front_Two_Gall($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<2){
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);
        $result=$this->combinations($arr_one, 2);
        $this->data=array();
        foreach ($result as $key=>$value){
            $this->data[]=implode("",$value);
        }
        return $this->data;
    }

    /**
     * 不定位三星中3 2码不定位
     * @param $arr_one
     * @return array
     */
    public function bdw_Three_In_Two_Gall($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<2){
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);
        $result=$this->combinations($arr_one, 2);
        $this->data=array();
        foreach ($result as $key=>$value){
            $this->data[]=implode("",$value);
        }
        return $this->data;
    }

    /**
     * 不定位三星后3 2码不定位
     * @param $arr_one
     * @return array
     */
    public function bdw_Three_Back_Two_Gall($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<2){
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);
        $result=$this->combinations($arr_one, 2);
        $this->data=array();
        foreach ($result as $key=>$value){
            $this->data[]=implode("",$value);
        }
        return $this->data;
    }


    /**
     * 不定位 五星2码不定位
     * @param $arr_one
     * @return array
     */
    public function bdw_Five_Two_Gall($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<2){
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);
        $result=$this->combinations($arr_one, 2);
        $this->data=array();
        foreach ($result as $key=>$value){
            $this->data[]=implode("",$value);
        }
        return $this->data;
    }


    /**
     * 不定位四星后4 2码不定位
     * @param $arr_one
     * @return array
     */
    public function bdw_Four_Back_Two_Gall($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<2){
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);
        $result=$this->combinations($arr_one, 2);
        $this->data=array();
        foreach ($result as $key=>$value){
            $this->data[]=implode("",$value);
        }
        return $this->data;
    }


    /**
     * 不定位五星 1码不定位
     * @param $arr_one
     * @return array
     */
    public function bdw_F_One_Gall($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<1){
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

    /**
     * 不定位四星后4 1码不定位
     * @param $arr_one
     * @return array
     */
    public function bdw_Four_Back_One_Gall($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<1){
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


    /**
     * 不定位三星前3 1码不定位
     * @param $arr_one
     * @return array
     */
    public function bdw_Three_Front_One_Gall($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<1){
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

    /**
     * 不定位三星中3 1码不定位
     * @param $arr_one
     * @return array
     */
    public function bdw_Three_In_One_Gall($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<1){
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

    /**
     * 不定位三星后3 1码不定位
     * @param $arr_one
     * @return array
     */
    public function bdw_Three_Back_One_Gall($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<1){
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


    /**
     * 不定位 五星3码 3码不定位
     * @param $arr_one
     * @return array
     */
    public function bdw_Five_Stars_Three_Gall($arr){
        $arr_one=explode(',', $arr);
        if (count($arr_one)<3){
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);
        $result=$this->combinations($arr_one, 3);
        $this->data=array();
        foreach ($result as $key=>$value){
            $this->data[]=implode("",$value);
        }
        return $this->data;
    }




}