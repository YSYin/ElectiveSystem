<?php

/**
* 教师控制器 TeacherCourse
* 主要负责教务查看、修改教师开设的课程，同时提供教师查看管理个人开设课程的接口
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
     * 查询并返回教师用户列表
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
              ."c.course_capacity,c.course_student_num, c.course_time, c.course_room, c.course_info\n"
              ."FROM course c INNER JOIN teacher_course tc\n"
              ."ON c.course_id = tc.course_id\n"
              ."WHERE tc.teacher_id = {$user_id}\n";
        $list = Db::query($sql);
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        $this->assign("real_name", $real_name);
        $this->view->engine->layout("window");
        return $this->fetch();
    }

    /**
     * 为当前教师添加课程
     * @return 是否成功
     */
    public function addTeacherCourse() {
        $user_id = (int)input("get.user_id");
        $real_name = input('get.real_name');
        $exist = Base::checkUserAndRole('', 'Teacher', $user_id);
        if ($exist == -1) {
            Base::addLog(3, "教师ID不存在");
            Base::logout();
            return "<script>alert('非法操作！');window.history.go(-1);</script>";
        } else if ($exist == -2) {
            Base::addLog(3, "该用户账号不是教师账号");
            Base::logout();
            return "<script>alert('非法操作！');window.history.go(-1);</script>";
        }
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
            $res = Base::checkValidate('course_info', $data, 2);
            if ($res['status'] == -1) {
                $err = $res['msg'];
                return "<script>alert('{$err}');window.history.go(-1);</script>";
            }
            $exist = Db::name('course')->where('course_code', $data['course_code'])->find();
            if ($exist) {
                Base::addLog(2, '课程号已存在');
                return "<script>alert('课程号已存在');window.history.go(-1);</script>";
            }
            $course_id = Db::name("course")->insertGetId($data);
            $res = Db::name("teacher_course")->insert(['teacher_id' => $user_id, 'course_id' => $course_id]);
            if ($res){
                return $this->fetch("public/msg", ["code" => 1, "msg" => "您已成功添加课程".$data['course_code']]);
            } else {
                return "<script>alert('添加课程失败');window.history.go(-1);</script>";
            }
        }
        else {
            $this->view->engine->layout("window");
            $this->assign("real_name", $real_name);
            return $this->fetch();
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

            $res = Base::checkValidate('edit_course_info', $data, 2);
            if ($res['status'] == -1) {
                $err = $res['msg'];
                return "<script>alert('{$err}');window.history.go(-1);</script>";
            }
            $course_id = Db::name('course')->where('course_code', $course_code)->value('course_id');
            if (!$course_id) {
                Base::addLog(3, '课程号不存在');
                Base::logout();
                return "<script>alert('非法操作！');window.history.go(-1);</script>";
            }

            $user_id = (int)Session::get("user_id");
            $exist = Db::name('teacher_course')->where(['teacher_id' => $user_id, 'course_id' => $course_id])->find();
            if (!$exist) {
                Base::addLog(3, '用户修改非个人课程信息');
                Base::logout();
                return "<script>alert('非法操作！');window.history.go(-1);</script>";
            }

            $res = Db::name("course")->where('course_code', $course_code)->update($data);
            if ($res) {
                return $this->fetch("public/msg", ["code" => 1, "msg" => "您已成功修改课程".$course_code."的课程信息"]);
            }
            else {
                return "<script>alert('课程信息并未改变');window.history.go(-1);</script>";
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
     * 查询并返回选择用户个人开设课程的所有学生
     */
    public function showSelfAllStudent() {
        $user_id = (int)Session::get("user_id");
        $real_name = Session::get("real_name");
        $sql = "SELECT u.user_id, u.user_name, u.real_name, u.gender,u.grade, u.email, c.course_name\n"
                  ."FROM student_course sc INNER JOIN teacher_course tc\n"
                  ."ON sc.course_id = tc.course_id\n"
                  ."INNER JOIN student_user u\n"
                  ."ON sc.student_id = u.user_id\n"
                  ."INNER JOIN course c\n"
                  ."ON sc.course_id = c.course_id\n"
                  ."WHERE tc.teacher_id = {$user_id}\n";
        $list = Db::query($sql);
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        $this->assign("real_name", $real_name);
        return $this->fetch();
    }

    /**
     * 查询并返回选择用户个人开设特定课程的学生
     */
    public function showSelfCourseStudent() {
        $this->view->engine->layout("window");
        if (isset($_GET['course_code'])) {
            $user_id = (int)Session::get("user_id");
            $real_name = Session::get("real_name");
            $course_code = input('get.course_code');
            $res = Base::checkValidate('course_code', ['course_code' => $course_code], 3);
            if ($res['status'] == -1) {
                Base::logout();
                return "<script>alert('非法操作！');window.history.go(-1);</script>";
            }
            $info = Db::name('course')->field('course_name, course_id')->where('course_code', $course_code)->find();
            if (!$info) {
                Base::addLog(3, '课程号不存在');
                Base::logout();
                return "<script>alert('非法操作！');window.history.go(-1);</script>";
            }
            $course_id = $info['course_id'];
            $course_name = $info['course_name'];
            $exist = Db::name('teacher_course')->where(['teacher_id' => $user_id, 'course_id' => $course_id])->find();
            if (!$exist) {
                Base::addLog(3, '用户查看非个人课程信息');
                Base::logout();
                return "<script>alert('非法操作！');window.history.go(-1);</script>";
            }
            $sql = "SELECT u.user_id, u.user_name, u.real_name, u.grade, u.gender, u.email\n"
                  ."FROM student_course sc INNER JOIN teacher_course tc\n"
                  ."ON sc.course_id = tc.course_id\n"
                  ."INNER JOIN student_user u\n"
                  ."ON sc.student_id = u.user_id\n"
                  ."INNER JOIN course c\n"
                  ."ON sc.course_id = c.course_id\n"
                  ."WHERE tc.teacher_id = {$user_id} AND c.course_code = '{$course_code}'\n";
            $list = Db::query($sql);
            $this->assign("list", $list);
            $this->assign("data_num", count($list));
            $this->assign("real_name", $real_name);
            $this->assign("course_name", $course_name);
            return $this->fetch();
            
        }
        else {
            return $this->fetch();
        }
    }
}
