<?php

/**
* 选课前台控制器 Elective
* 主要负责教务查看、分配、取消学生选课
* @file      Elective.php
* @date      2020/12/31
* @author    YSY
* @version   1.0
*/
namespace app\student\controller;

use think\Request;
use think\Db;
use think\Session;

class Elective extends Base {


    protected static $start_time = 0;
    protected static $end_time = 0;

    /**
     * 欢迎页面
     */
    public function index() {
        return $this->fetch();
    }

    /**
    * 设置并返回选课开始和结束时间
    */
    public function getElectiveTime() {
        $electiveTimeFile = APP_PATH.DS.'student'.DS.'electiveTime.txt';
        $str = file_get_contents($electiveTimeFile);
        $arr = explode(':', $str);
        Elective::$start_time = (int)$arr[0];
        Elective::$end_time = (int)$arr[1];
        return json(['start' => $arr[0], 'end' => $arr[1]]);
    }

    /**
     * 查询并返回学生可选的全部课程列表，并提供相应的操作接口
     */
    public function showAllCourse() {
        $staticHtmlFile = APP_PATH.DS.'student'.DS.'static_html'.DS.'showAllCourse.html';
        if(file_exists($staticHtmlFile) && filectime($staticHtmlFile) >= time()- 60*60*2)
            return file_get_contents($staticHtmlFile);
      	$sql = "SELECT c.course_id, c.course_code, c.course_name, c.course_credit, c.course_capacity, c.course_hour, c.course_room, c.course_time, c.course_info, au.real_name\n"
                ."FROM course c LEFT JOIN teacher_course tc\n"
                ."ON c.course_id = tc.course_id\n"
                ."LEFT JOIN admin_user au\n"
                ."ON tc.teacher_id = au.user_id\n"
                ."WHERE c.course_status = 1";
        $list = Db::query($sql);
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        $html = $this->fetch();
        if(file_exists($staticHtmlFile)) {
            unlink($staticHtmlFile);
        }
        file_put_contents($staticHtmlFile, $html);
        return $html;
    }

    /**
     * 接收并处理学生选课请求:原始版本
     * @return 是否成功
     */
    public function doSimpleElective() {
        $this->getElectiveTime();
        $now = time();
        if ($now < Elective::$start_time) 
          return json(['status' => -1, 'msg' => '选课还未开始！']);
        if ($now >= Elective::$end_time) 
          return json(['status' => -1, 'msg' => '选课已经结束！']);
        if (request()->isPost()) {
        	$user_id = Session::get("user_id");
          $user_id = 1;
        	$course_str = input("post.courses");
          $courses = explode('.', $course_str);
          error_log($course_str."\r\n",3,'E:\phpstudy_pro\errors.log');
        	foreach ($courses as $key => $value) {
            $course_id = (int)$value;
            $select_before = Db::name('student_course')->where(['student_id' => $user_id, 'course_id' => $course_id])->find();
            if ($select_before) continue;
            $data = Db::name("course")->field('course_capacity, course_student_num')->where('course_id', $course_id)->find();
            if ($data['course_capacity'] == $data['course_student_num']) continue;
            $res1 = Db::name("student_course")->insert(['student_id' => $user_id, 'course_id' => $course_id]);
            $res2 = Db::name("course")->where('course_id', $course_id)->setInc('course_student_num');
        	};
        	return json(['status' => 1, 'msg' => '您的选课请求已处理，请点击左侧菜单查看选课结果']);
        }
    }


    /**
     * 接收并处理学生选课请求:性能优化
     * @return 是否成功
     */
    public function doElective() {
        $this->getElectiveTime();
        $now = time();
        if ($now < Elective::$start_time) 
          return json(['status' => -1, 'msg' => '选课还未开始！']);
        if ($now >= Elective::$end_time) 
          return json(['status' => -1, 'msg' => '选课已经结束！']);
        
        if (request()->isPost()) {
        	$user_id = Session::get("user_id");
          $user_id = 1;
        	$course_str = input("post.courses");
          $courses = explode('.', $course_str);
          $redis = new \Redis();
          $redis->connect('localhost', 6379);
          $course_ids = '';
          
        	foreach ($courses as $key => $value) {
        		  $course_id = $value;
              if ($redis->hSet('course_'.$course_id.'_elections', $user_id, '1')) {
                  if ($redis->rPop('course_'.$course_id)) {
                      $course_ids .= $course_id.'.';
                  }
              }
        	};
          if ($course_ids != '') {
              $url = "localhost:9090/process";
              $post_data = array();
              $post_data['user_id'] = $user_id;
              $post_data["course_ids"] = rtrim($course_ids, '.');
              error_log("发送课程ID".$course_str."到消息队列\r\n",3,'E:\phpstudy_pro\errors.log');
              $res = Base::sendPost($url, $post_data);
              error_log("收到回复".$res."到消息队列\r\n",3,'E:\phpstudy_pro\errors.log');
              return json(['status' => 1, 'msg' => $res]);
          } else return json(['status' => -1, 'msg' => '您选择的课程人数已满或您重复选课，本次请求无效']);
        }
    }

    /**
     * 查看学生选课结果
     */
    public function showElectiveResult() {
        $user_id = Session::get("user_id");
        $sql = "SELECT c.course_code, c.course_name, c.course_credit, c.course_hour,"
              ."c.course_capacity, c.course_info, c.course_time, c.course_room\n"
              ."FROM course c INNER JOIN student_course sc\n"
              ."ON c.course_id = sc.course_id\n"
              ."WHERE sc.student_id = '{$user_id}'\n";
        $list = Db::query($sql);
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        return $this->fetch();
    }

}
