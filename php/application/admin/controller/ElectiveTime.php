<?php

/**
* 设置选课开始时间和结束时间
* @file      ElectiveTime.php
* @date      2021/1/4
* @author    YSY
* @version   1.0
*/

namespace app\admin\controller;

use think\Request;
use think\Db;
use think\Session;

class ElectiveTime extends Base {

    /**
     * 返回主页面
     */
    public function index() {
        $start = "- - - - - -";
        $end = "- - - - - -";
        $dir = RUNTIME_PATH.DS.'time';
        $electiveTimeFile = $dir.DS.'electiveTime';
        $str = false;
        if (file_exists($electiveTimeFile))
            $str = file_get_contents($electiveTimeFile);
        if ($str) {
            $arr = explode(':', $str);
            $start_time = (int)$arr[0];
            $end_time = (int)$arr[1];
            $start = date("Y-m-d H:i:s",$start_time); 
            $end = date("Y-m-d H:i:s",$end_time);
        }
        $this->assign('start', $start);
        $this->assign('end', $end);
        return $this->fetch('index');
    }

    /**
     * 设置日期
     */
    public function setTime() {
        if (request()->isPost()) {
            $start = ((int)input("post.start_time"));
            $end = ((int)input("post.end_time"));
            if ($start <= time() || $end < $start) {
                Base::addLog(2, '日期设置错误');
                return json(['status' => -1, 'msg' => '日期设置错误']);
            }
            $dir = RUNTIME_PATH.DS.'time';
            if (!file_exists($dir)){
                mkdir($dir,0777,true);
            }
            $electiveTimeFile = $dir.DS.'electiveTime';
            $file = fopen($electiveTimeFile, 'w');
            if ($file) {
                if (fwrite($file, (string)$start.":".(string)$end))
                {
                    fclose($file);
                    return json(['status' => 1, 'msg' => '成功设置选课日期']);
                }
                fclose($file);
            }
            Base::addLog(2, '设置日期失败,可能由于文件无法创建或写入');
            return json(['status' => -1, 'msg' => '设置日期失败']);
        }
    }

}
