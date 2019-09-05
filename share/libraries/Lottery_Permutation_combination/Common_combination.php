<?php

/**
 * Created by PhpStorm.
 * User: amao
 * Date: 18-5-25
 * Time: 上午11:19
 */
class Common_combination
{
    public function __construct()
    {
        //parent::__construct();
    }

    /**
     * 阶乘
     */
    public function factorial($n)
    {
        //array_product 计算并返回数组的乘积
        //range 创建一个包含指定范围的元素的数组
        return array_product(range(1, $n));
    }

    /**
     * 排列数
     */
    public function A($n, $m)
    {
        return $this->factorial($n)/$this->factorial($n-$m);
    }

    /**
     * 组合数
     */
    public function C($n, $m)
    {
        return $this->A($n, $m)/$this->factorial($m);
    }

    /**
     * 排列结果
     */
    public function arrangement($a, $m)
    {
        $r = array();
        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }
        for ($i=0; $i<$n; $i++) {
            $b = $a;
            //从数组中移除选定的元素，并用新元素取代它。该函数也将返回包含被移除元素的数组
            $t = array_splice($b, $i, 1);
            if ($m == 1) {
                $r[] = $t;
            } else {
                $c = $this->arrangement($b, $m-1);
                foreach ($c as $v) {
                    $r[] = array_merge($t, $v);
                }
            }
        }

        return $r;
    }


    /**
     * 组合结果
     */
    public function combinations($a, $m)
    {
        $r = array();
        $n = count($a);
        if ($m <= 0 || $m > $n) {
            return $r;
        }

        for ($i=0; $i<$n; $i++) {
            $t = array($a[$i]);
            if ($m == 1) {
                $r[] = $t;
            } else {
                //array_slice() 函数在数组中根据条件取出一段值，并返回。
                //array_slice(array,start,length,preserve)
                $b = array_slice($a, $i+1);
                $c = $this->combinations($b, $m-1);
                foreach ($c as $v) {
                    //array_merge() 函数把一个或多个数组合并为一个数组
                    $r[] = array_merge($t, $v);
                }
            }
        }
        return $r;
    }


    /**
     *组合
     * @param array $arr
     */
    public function compound_Combinations($arr)
    {
        if (count($arr) >= 2) {
            $tmparr = array();
            $arr1 = array_shift($arr);
            $arr2 = array_shift($arr);
            foreach ($arr1 as $k1 => $v1) {
                foreach ($arr2 as $k2 => $v2) {
                    $tmparr[] = $v1.$v2;
                }
            }
            array_unshift($arr, $tmparr);
            $arr = $this->compound_Combinations($arr);
        } else {
            return $arr;
        }
        return $arr;
    }


    /**
     * 3个数值差值公式 知道a b c 两个数 知道d 求出d=a-b-c
     * @param array $open_arr 开奖号码
     * @param array $bet_arr 下注和值号码
     */
    public function three_Diff_Formula($open_arr, $bet_arr)
    {
        $dict = array();
        $data=array();
        foreach ($open_arr as $value) { //构造字典
            $key = $value[3]."-".$value[4]."-".$value[5]; //等式
            $diff_val = $value[0]-$value[1];//相减结果
            //$diff_val = $value[0].$value[1];//相减数字
            $dict[$key] = $diff_val;
        }
        //计算部分
        foreach ($bet_arr as $item) {
            $r = array_keys($dict, $item); //从字典中取出等式
            if ($r) {
                foreach ($r as $result) {
                    //$data[]=$result."=".$item;
                    $data[]=array(
                        $result."=".$item,
                        str_replace("-", "", $result),
                        $item
                        );
                }
            }
        }
        return $data;
    }

    /**
     * 3个数值和值公式 知道a b c三个数 知道d 求出0=(a+b+c)d%2
     * @param array $open_arr 开奖号码
     * @param array $bet_arr 下注和值号码
     */
    public function three_Sum_Formula($open_arr, $bet_arr, $ettm_type='tat')
    {
        $dict = array();
        $data=array();
        foreach ($open_arr as $value) { //构造字典
            foreach ($open_arr as $val) {
                foreach ($open_arr as $v) {
                    $key = $value."+".$val."+".$v; //等式
                    $sum_val = $value+$val+$v;
                    $dict[$key] = $sum_val;
                }
            }
        }
        if ($ettm_type!='low') {
            //计算部分
            foreach ($dict as $key => $value) {
                foreach ($bet_arr as $item) {
                    if (strlen($value) > 1 && substr($value, 1, 1) == $item) {
                        //$data[]=$key."=".$value;
                        $data[] = array(
                            $key . "=" . $value,
                            str_replace("+", "", $key),
                            $value
                        );
                    } elseif ($value == $item) {
                        //$data[]=$key."=".$value;
                        $data[] = array(
                            $key . "=" . $value,
                            str_replace("+", "", $key),
                            $value
                        );
                    }
                }
            }
        } else {
            //计算部分
            foreach ($bet_arr as $item) {
                $r = array_keys($dict, $item); //从字典中取出等式
                if ($r) {
                    foreach ($r as $result) {
                        $data[]=array(
                            $result."=".$item,
                            str_replace("+", "", $result),
                            $result
                        );
                    }
                }
            }
        }
        return $data;
    }


    /**
     * 2个数值和值公式 知道a b两个数 知道c 求出c=a+b
     * @param array $open_arr 开奖号码
     * @param array $bet_arr 下注和值号码
     */
    public function two_Sum_Formula($open_arr, $bet_arr)
    {
        $dict = array();
        $data=array();
        foreach ($open_arr as $value) { //构造字典
            foreach ($open_arr as $val) {
                $key = $value."+".$val; //等式
                $sum_val = $value+$val;
                $dict[$key] = $sum_val;
            }
        }
        //计算部分
        foreach ($bet_arr as $item) {
            $r = array_keys($dict, $item); //从字典中取出等式
            if ($r) {
                foreach ($r as $result) {
//                    $data[]=$result."=".$item;
                    $data[]=array(
                        $result."=".$item,
                        str_replace("+", "", $result),
                        $item
                    );
                }
            }
        }
        return $data;
    }
}
