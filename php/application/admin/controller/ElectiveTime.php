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
        return $this->fetch('index');
    }

    /**
     * 设置日期
     */
    public function setTime() {
        if (request()->isPost()) {
            $start = ((int)input("post.start_time"));
            $end = ((int)input("post.end_time"));
            if ($start <= time() || $end < $start)
                return json(['status' => -1, 'msg' => '日期设置错误']);
            $electiveTimeFile = APP_PATH.DS.'student'.DS.'electiveTime.txt';
            $file = fopen($electiveTimeFile, 'w');
            if ($file) {
                if (fwrite($file, (string)$start.":".(string)$end))
                {
                    fclose($file);
                    return json(['status' => 1, 'msg' => '成功设置选课日期']);
                }
                fclose($file);
            }
            return json(['status' => -1, 'msg' => '设置日期失败']);
        }
    }

}
