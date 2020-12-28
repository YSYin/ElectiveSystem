<?php

/**
* 教师控制器 TeacherCourse
* 主要负责教务查看、修改教师开设的课程
* @file      TeacherCourse.php
* @date      2020/12/27
* @author    YSY
* @version   1.0
*/
namespace app\admin\controller;

use think\Request;
use think\Db;
use think\Session;

class TeacherCourse extends Base {

    /**
     * 查询并返回教师用户列表，并提供相应的操作接口
     */
    public function index() {
        $sql = "SELECT u.user_id, u.user_name, u.real_name, u.gender, "
                . "u.email, u.mobile_number, u.status\n"
                . "FROM admin_user u INNER JOIN user_role ur\n"
                . "ON u.user_id = ur.user_id\n"
                . "INNER JOIN admin_role r \n"
                . "ON ur.role_id = r.role_id\n"
                . "WHERE r.role_name = 'Teacher'\n";
        $list = Db::query($sql);
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        return $this->fetch();
    }

    /**
     * 查询并返回教师开设的课程列表，并提供相应的操作接口
     */
    public function showTeacherCourse() {
        $user_id = (int)input("get.user_id");
        $real_name = input('get.real_name');
        $sql = "SELECT c.course_id, c.course_code, c.course_name, c.course_status, c.course_credit, c.course_hour,"
              ."c.course_capacity,c.course_student_num, c.course_time, c.course_room\n"
              ."FROM course c INNER JOIN teacher_course tc\n"
              ."ON c.course_id = tc.course_id\n"
              ."WHERE tc.teacher_id = '{$user_id}'\n";
        $list = Db::query($sql);
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        $this->assign("real_name", $real_name);
        $this->view->engine->layout("window");
        return $this->fetch();
    }

    /**
     * 添加课程
     * @return 是否成功
     */
    public function addTeacherCourse() {
        $user_id = (int)input("get.user_id");
        $real_name = input('get.real_name');
        if (isset($_POST["submit"])) {
            $data = array();
            $data['course_code'] = input('post.course_code');
            $data['course_name'] = input('post.course_name');
            $data['course_credit'] = (int)input('post.course_credit');
            $data['course_hour'] = (int)input('post.course_hour');
            $data['course_capacity'] = (int)input('post.course_capacity');
            $data['course_time'] = input('post.course_time');
            $data['course_room'] = input('post.course_room');
            $data['course_info'] = input('post.course_info');

            $course_id = Db::name("course")->insertGetId($data);
            $res = Db::name("teacher_course")->insert(['teacher_id' => $user_id, 'course_id' => $course_id]);
            if ($res){
                return $this->fetch("public/msg", ["code" => 1, "msg" => "您已成功添加课程".$data['course_code']]);
            } else {
                return $this->fetch("public/msg", ["code" => 0, "msg" => "添加课程失败"]);
            }
        }
        else {
            $this->view->engine->layout("window");
            $this->assign("real_name", $real_name);
            return $this->fetch();
        }   
    }

    /**
     * 取消授课课程
     * @return 是否成功
     */
    public function cancelTeacherCourse() {
        $user_id = (int)input("get.user_id");
        $course_id = (int)input('get.course_id');
        $res = Db::name("teacher_course")->where(['teacher_id' => $user_id, 'course_id' => $course_id])->delete();
        if ($res){
            return true;
        } else {
            return false;
        }  
    }

    /**
     * 查询并返回用户本人开设的课程列表，并提供相应的操作接口
     */
    public function showSelfCourse() {
        $user_id = Session::get("user_id");
        $real_name = Session::get("real_name");
        $sql = "SELECT c.course_id, c.course_code, c.course_name, c.course_status, c.course_credit, c.course_hour,"
              ."c.course_capacity, c.course_student_num, c.course_time, c.course_room\n"
              ."FROM course c INNER JOIN teacher_course tc\n"
              ."ON c.course_id = tc.course_id\n"
              ."WHERE tc.teacher_id = '{$user_id}'\n";
        $list = Db::query($sql);
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        $this->assign("real_name", $real_name);
        return $this->fetch();
    }

    /**
     * 修改本人开设课程信息：课程名称、学分、学时、容量、时间、地点、描述 
     * @return 是否成功
     */
    public function editSelfCourse() {
        $course_code = input('get.course_code');
        if (isset($_POST["submit"])) {
            $data = array();
            $data['course_name'] = input('post.course_name');
            $data['course_credit'] = (int)input('post.course_credit');
            $data['course_hour'] = (int)input('post.course_hour');
            $data['course_capacity'] = (int)input('post.course_capacity');
            $data['course_time'] = input('post.course_time');
            $data['course_room'] = input('post.course_room');
            $data['course_info'] = input('post.course_info');

            $res = Db::name("course")->where('course_code', $course_code)->update($data);
            if ($res) {
                return $this->fetch("public/msg", ["code" => 1, "msg" => "您已成功修改课程".$course_code."的课程信息"]);
            }
            else {
                return $this->fetch("public/msg", ["code" => 0, "msg" => "修改课程".$course_code."信息失败"]);
            }
        }
        else {
            $this->view->engine->layout("window");
            $course = Db::name('course')->field('course_code, course_name, course_status, course_credit, course_hour, course_capacity, course_time, course_room, course_info')->where('course_code', $course_code)->find();
            $this->assign("course", $course);
            return $this->fetch();
        }
    }

    /**
     * 查询并返回选择用户个人开设课程的所有学生，并提供其个人信息接口
     */
    public function showSelfStudent() {
        $user_id = (int)Session::get("user_id");
        $real_name = Session::get("real_name");
        if (isset($_GET['course_code'])) {
            $course_code = input('get.course_code');
            $sql = "SELECT u.user_id, u.user_name, u.real_name, c.course_name\n"
                  ."FROM student_course sc INNER JOIN teacher_course tc\n"
                  ."ON sc.course_id = tc.course_id\n"
                  ."INNER JOIN student_user u\n"
                  ."ON sc.student_id = u.user_id\n"
                  ."INNER JOIN course c\n"
                  ."ON sc.course_id = c.course_id\n"
                  ."WHERE tc.teacher_id = {$user_id} AND c.course_code = '{$course_code}'\n";
            $list = Db::query($sql);
        }
        else {
            $sql = "SELECT u.user_id, u.user_name, u.real_name, c.course_name\n"
                  ."FROM student_course sc INNER JOIN teacher_course tc\n"
                  ."ON sc.course_id = tc.course_id\n"
                  ."INNER JOIN student_user u\n"
                  ."ON sc.student_id = u.user_id\n"
                  ."INNER JOIN course c\n"
                  ."ON sc.course_id = c.course_id\n"
                  ."WHERE tc.teacher_id = {$user_id}\n";
            $list = Db::query($sql);
        }
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        $this->assign("real_name", $real_name);
        return $this->fetch();
    }

    /**
     * 查询并返回选择用户个人开设课程的学生信息
     */
    public function showSelfStudentInfo() {
        $user = Db::name('student_user')->field('user_name, real_name, gender, grade, email, mobile_number')->where('user_name', input('get.user_name'))->find();
        $this->assign("user", $user);
        $this->view->engine->layout("window");
        return $this->fetch();
    }

}
