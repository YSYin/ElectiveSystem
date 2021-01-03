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

    /**
     * 欢迎页面
     */
    public function index() {
        return $this->fetch();
    }

    /**
     * 查询并返回学生可选的全部课程列表，并提供相应的操作接口
     */
    public function showAllCourse() {
    	$sql = "SELECT c.course_id, c.course_code, c.course_name, c.course_credit, c.course_capacity, c.course_hour, c.course_room, c.course_time, c.course_info, au.real_name\n"
              ."FROM course c LEFT JOIN teacher_course tc\n"
              ."ON c.course_id = tc.course_id\n"
              ."LEFT JOIN admin_user au\n"
              ."ON tc.teacher_id = au.user_id\n"
              ."WHERE c.course_status = 1";
        $list = Db::query($sql);
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        return $this->fetch();
    }


    /**
     * 接收并处理学生选课请求:原始版本
     * @return 是否成功
     */
    public function doElective2() {
        
        if (request()->isPost()) {
        	$user_id = Session::get("user_id");
        	$courses = $_POST["courses"];
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
     * 初始化redis
     */
    public function initRedis() {

        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $courses = Db::name('course')->field('course_id,course_capacity')->select();
        foreach ($courses as $key => $course) {
            $listKey = (string)$course['course_id'];
            for($i = 1; $i <= $course['course_capacity']; $i++) {
              $redis->rPush($listKey,$i);
            }
        }
    }


    /**
     * 接收并处理学生选课请求:性能优化
     * @return 是否成功
     */
    public function doElective() {

        $url = "localhost:3456/process";
        $post_data = array();
        $post_data["key"] = implode("#", array('1', '2', '3'));
        $res = Base::sendPost($url, $post_data);
        echo var_dump($res);
        return;
 
        if (request()->isPost()) {
        	$user_id = Session::get("user_id");
        	$courses = $_POST["courses"];
        	foreach ($courses as $key => $value) {
        		$course_id = (int)$value;
        		$select_before = Db::name('student_course')->where(['student_id' => $user_id, 'course_id' => $course_id])->find();
        		if ($select_before) continue;
        		$data = Db::name("course")->field('course_capacity, course_student_num')->where('course_id', $course_id)->find();
        		if ($data['course_capacity'] == $data['course_student_num']) continue;
        		$res1 = Db::name("student_course")->insert(['student_id' => $user_id, 'course_id' => $course_id]);
            	$res2 = Db::name("course")->where('course_id', $course_id)->update(['course_student_num' => $data['course_student_num'] + 1]);
        	};
        	return json(['status' => 1, 'msg' => '您的选课请求已处理，请点击左侧菜单查看选课结果']);
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
