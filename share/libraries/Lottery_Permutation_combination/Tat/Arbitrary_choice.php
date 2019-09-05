<?php

/**
 * 任意选择
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 下午5:39
 */
class Arbitrary_choice extends  Common_combination
{

    public $data=array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 任选2直选
     * @param $arr
     * @return array
     */
    public function arbitrary_Choice_Direct_Two($arr){
        $arr=explode('|', $arr);

        if (count($arr)<2){
            return array();
        }
        $data=array();
        for ($i=0;$i<count($arr);$i++){
            $data[]=explode(',', $arr[$i]);
        }
        $arr=$data;
//        $arr = array(
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//        );
        $result=array();
        $this->data=array();
        foreach ($arr as $value) {
            $arr_one=array_shift($arr);
            for ($i=0;$i<count($arr);$i++){
                $result[]=$this->compound_Combinations(array($arr_one,$arr[$i]));
            }
        }
        foreach ($result as $value) {
            foreach ($value as $val) {
                $this->data=array_merge($this->data,$val);
            }
        }
       return $this->data;
    }


    /**
     * 任选3直选
     * @param $arr
     * @return array
     */
    public function arbitrary_Choice_Direct_Three($arr){
        $arr=explode('|', $arr);
        if (count($arr)<3){
            return array();
        }
        $data=array();
        for ($i=0;$i<count($arr);$i++){
            $data[]=explode(',', $arr[$i]);
        }
        $arr=$data;
//        $arr = array(
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8),
//        );
        //得到数组key
        $key_arr=array();
        foreach ($arr as $key=>$value) {
            $key_arr[]=$key;
        }
        $result_c=$this->combinations($key_arr, 3);//根据数组个数下表得到排列组合

        $result=array();
        foreach ($result_c as $val) {
            $arr_c=array();
            foreach ($val as $v) {
                $arr_c[]=$arr[$v];
            }
            $result[]=$this->compound_Combinations($arr_c);
        }
        $this->data=array();
        foreach ($result as $value) {
            foreach ($value as $val) {
                $this->data=array_merge($this->data,$val);
            }
        }
        return $this->data;
    }


    /**
     * 任选4直选
     * @param $arr
     * @return array
     */
    public function arbitrary_Choice_Direct_Four($arr){
        $arr=explode('|', $arr);
        if (count($arr)<4){
            return array();
        }
        $data=array();
        for ($i=0;$i<count($arr);$i++){
            $data[]=explode(',', $arr[$i]);
        }
        $arr=$data;
//        $arr = array(
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8,9),
//            array(0,1,2,3,4,5,6,7,8),
//        );
        //得到数组key
        $key_arr=array();
        foreach ($arr as $key=>$value) {
            $key_arr[]=$key;
        }
        $result_c=$this->combinations($key_arr, 4);//根据数组个数下表得到排列组合

        $result=array();
        foreach ($result_c as $val) {
            $arr_c=array();
            foreach ($val as $v) {
                $arr_c[]=$arr[$v];
            }
            $result[]=$this->compound_Combinations($arr_c);
        }
        $this->data=array();
        foreach ($result as $value) {
            foreach ($value as $val) {
                $this->data=array_merge($this->data,$val);
            }

        }
       return $this->data;
    }

}