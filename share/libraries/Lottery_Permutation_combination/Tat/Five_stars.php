<?php

/**
 * 5星
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 上午11:33
 */
class Five_stars extends Common_combination
{
    public $data = array();

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 五星直选复
     * @param $arr
     * @return array
     */
    public function five_Direct_Compound($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<5) {
            return array();
        }
        $arr=array(
            explode(',', $arr[0]),
            explode(',', $arr[1]),
            explode(',', $arr[2]),
            explode(',', $arr[3]),
            explode(',', $arr[4])
        );
        //        $arr = array(
        //            array(0,1,2,3,4,5,6,7,8,9),
        //            array(0,1,2,3,4,5,6,7,8,9),
        //            array(0,1,2,3,4,5,6,7,8,9),
        //            array(0,1,2,3,4,5,6,7,8,9),
        //            array(0,1,2,3,4,5,6,7,8,9),
        //        );
        return $this->compound_Combinations($arr)[0];
    }


    /**
     * 五星组选120
     * @param $arr
     * @return array
     */
    public function five_Group120($arr)
    {
        $arr= explode(',', $arr);
        if (count($arr)<5) {
            return array();
        }
//        $arr=array(0,1,2,3,4,5,6,7,8,9);//单号
        $result_c=$this->combinations($arr, 5);//得到排列组合
        //遍历得到的组合
        $this->data=array();
        foreach ($result_c as $key=>$val) {
            rsort($val);//desc array
            $this->data[]=implode("", $val);
        }
        return $this->data;
    }


    /**
     * 五星组选60
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function five_Group60($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<2) {
            return array();
        }

        $arr_one=explode(',', $arr[0]);
        if (count($arr_one)<1) {
            return array();
        }
        $arr_two=explode(',', $arr[1]);
        if (count($arr_two)<3) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//二从号
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//单号
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two, array($value));//去掉重复数据
                array_unshift($result, $value);//把重复数据插入到数组开头
                $result_c=$this->combinations($result, 4);//得到排列组合
                //遍历得到的组合
                foreach ($result_c as $key=>$val) {
                    //拿从号的号码 与排列的号码对比 只取从号在排列数据的第一位的队列
                    if ($val[0]==$value) {
                        $val[]=$value;
                        rsort($val);//desc array
                        $this->data[]=implode("", $val);
                    }
                }
        }
        return $this->data;
    }

    /**
     * 五星组选30
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function five_Group30($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<2) {
            return array();
        }

        $arr_one=explode(',', $arr[0]);
        if (count($arr_one)<2) {
            return array();
        }
        $arr_two=explode(',', $arr[1]);
        if (count($arr_two)<1) {
            return array();
        }

//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//二从号
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//单号
        $this->data=array();
        foreach ($arr_two as $value) {
            $result=array_diff($arr_one, array($value));//去掉重复数据
            $result_arr_c=$this->combinations(array_values($result), 2);//把二从号用2个号码进行排列
            array_unshift($result, $value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 3);//从号和单号进$result行3个号码的排列
            foreach ($result_c as $key=>$val) {
                //拿从号的号码 与排列的号码对比 只取从号在排列数据的第一位的队列
                if ($val[0]==$value) {
                    foreach ($result_arr_c as $k=>$v) {
                        //二从号2个号码排列的数 必须要在 3个号码排列的数组里面存在
                        if (in_array($v[0], $val)&&in_array($v[1], $val)) {
                            $val[]=$v[0];
                            $val[]=$v[1];
                            rsort($val);//desc array
                        }
                    }
                    $this->data[]=implode("", $val);
                }
            }
        }
        return $this->data;
    }


    /**
     * 五星组选20
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function five_Group20($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<2) {
            return array();
        }

        $arr_one=explode(',', $arr[0]);
        if (count($arr_one)<1) {
            return array();
        }
        $arr_two=explode(',', $arr[1]);
        if (count($arr_two)<2) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//三从号
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//单号
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two, array($value));//去掉重复数据
            array_unshift($result, $value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 3);
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    $val[]=$value;
                    $val[]=$value;
                    rsort($val);//desc array
                    $this->data[]=implode("", $val);
                }
            }
        }
        return $this->data;
    }

    /**
     * 五星组选10
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function five_Group10($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<2) {
            return array();
        }

        $arr_one=explode(',', $arr[0]);
        if (count($arr_one)<1) {
            return array();
        }
        $arr_two=explode(',', $arr[1]);
        if (count($arr_two)<1) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//三从号
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//二从号
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two, array($value));//去掉重复数据
            array_unshift($result, $value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    $val[]=$value;
                    $val[]=$value;
                    $val[]=$val[1];
                    rsort($val);//desc array
                    $this->data[]=implode("", $val);
                }
            }
        }
        return $this->data;
    }


    /**
     * 五星组选5
     * @param $arr_one
     * @param $arr_two
     * @return array
     */
    public function five_Group5($arr)
    {
        $arr=explode('|', $arr);
        if (count($arr)<2) {
            return array();
        }

        $arr_one=explode(',', $arr[0]);
        if (count($arr_one)<1) {
            return array();
        }
        $arr_two=explode(',', $arr[1]);
        if (count($arr_two)<1) {
            return array();
        }
//        $arr_one=array(0,1,2,3,4,5,6,7,8,9);//三从号
//        $arr_two=array(0,1,2,3,4,5,6,7,8,9);//单号
        $this->data=array();
        foreach ($arr_one as $value) {
            $result=array_diff($arr_two, array($value));//去掉重复数据
            array_unshift($result, $value);//把重复数据插入到数组开头
            $result_c=$this->combinations($result, 2);
            foreach ($result_c as $key=>$val) {
                if ($val[0]==$value) {
                    $val[]=$value;
                    $val[]=$value;
                    $val[]=$value;
                    rsort($val);//desc array
                    $this->data[]=implode("", $val);
                }
            }
        }
        return $this->data;
    }
}
