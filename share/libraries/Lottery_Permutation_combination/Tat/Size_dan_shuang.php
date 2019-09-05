<?php

/**
 * 大小单双
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午5:53
 */
class Size_dan_shuang extends Common_combination
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 前2大小单双
     * @param $arr
     * @return array
     */
    public function tow_Front_Big_Smll_Single_Pair($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<2) {
            return array();
        }
        $arr=array(
            explode(',', $arr[0]),
            explode(',', $arr[1])
        );
//        $arr = array(
//            array('大','小','单','双'),
//            array('大','小','单','双'),
//        );
        $result=$this->compound_Combinations($arr)[0];
        return $result;
    }

    /**
     * 后2大小单双
     * @param $arr
     * @return array
     */
    public function tow_Back_Big_Smll_Single_Pair($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<2) {
            return array();
        }
        $arr=array(
            explode(',', $arr[0]),
            explode(',', $arr[1])
        );
//        $arr = array(
//            array('大','小','单','双'),
//            array('大','小','单','双'),
//        );
        $result=$this->compound_Combinations($arr)[0];
        return $result;
    }


    /**
     * 前3大小单双
     * @param $arr
     * @return array
     */
    public function three_Front_Big_Smll_Single_Pair($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<3) {
            return array();
        }
        $arr=array(
            explode(',', $arr[0]),
            explode(',', $arr[1]),
            explode(',', $arr[2])
        );
//        $arr = array(
//            array('大','小','单','双'),
//            array('大','小','单','双'),
//            array('大','小','单','双'),
//        );
        $result=$this->compound_Combinations($arr)[0];
        return $result;
    }


    /**
     * 后3大小单双
     * @param $arr
     * @return array
     */
    public function three_Back_Big_Smll_Single_Pair($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<3) {
            return array();
        }
        $arr=array(
            explode(',', $arr[0]),
            explode(',', $arr[1]),
            explode(',', $arr[2])
        );
//        $arr = array(
//            array('大','小','单','双'),
//            array('大','小','单','双'),
//            array('大','小','单','双'),
//        );
        $result=$this->compound_Combinations($arr)[0];
        return $result;
    }
}
