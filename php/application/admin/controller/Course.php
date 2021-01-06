<?php

/**
* 课程控制器 Course
* 主要负责删改查课程
* @file      Course.php
* @date      2020/12/27
* @author    YSY
* @version   1.0
*/
namespace app\admin\controller;

use think\Request;
use think\Db;
use think\Session;

class Course extends Base {

    /**
     * 查询并返回课程列表，并提供相应的操作接口
     */
    public function index() {
        $sql = "SELECT c.course_id, c.course_code, c.course_name, c.course_status, c.course_credit, c.course_capacity, c.course_student_num, au.real_name\n"
              ."FROM course c LEFT JOIN teacher_course tc\n"
              ."ON c.course_id = tc.course_id\n"
              ."LEFT JOIN admin_user au\n"
              ."ON tc.teacher_id = au.user_id";
        $list = Db::query($sql);
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        return $this->fetch();
    }

    /**
    * 检查课程号格式是否正确以及是否存在
    */
    public static function checkCourseCode($course_code) {
        $res = Base::checkValidate('course_code', ['course_code' => $course_code], 3);
        if ($res['status'] == -1) {
            return false;
        }
        $exist = Db::name('course')->where('course_code', $course_code)->find();
        if (!$exist) {
            Base::addLog(3, '课程编号错误');
            return false;
        }
        return true;
    }

    /**
    * 检查课程ID格式是否正确以及是否存在
    */
    public static function checkCourseID($course_id) {
        $res = Base::checkValidate('course_id', ['course_id' => $course_id], 3);
        if ($res['status'] == -1) {
            return false;
        }
        $exist = Db::name('course')->where('course_id', $course_id)->find();
        if (!$exist) {
            Base::addLog(3, '课程ID错误');
            return false;
        }
        return true;
    }


    /**
     * 查询并返回课程信息
     */
    public function showCourseInfo() {
        $course_code = input('get.course_code');
        $real_name = input('get.real_name');
        $res = Course::checkCourseCode($course_code);
        if (!$res) {
            Base::logout();
            return "<script>alert('非法操作！');window.history.go(-1);</script>";
        }

        $course = Db::name('course')->field('course_code, course_name, course_status, course_credit, course_hour, course_capacity, course_student_num, course_time, course_room, course_info')->where('course_code', $course_code)->find();
        $this->assign("real_name", $real_name);
        $this->assign("course", $course);
        $this->view->engine->layout("window");
        return $this->fetch();
    }

    /**
     * 查询并返回选课学生
     */
    public function showCourseStudent() {
        $course_id = input('get.course_id');
        $res = Course::checkCourseID($course_id);
        if (!$res) {
            Base::logout();
            return "<script>alert('非法操作！');window.history.go(-1);</script>";
        }
        
        $sql = "SELECT u.user_name, u.real_name, u.grade, u.gender, u.email\n"
                ."FROM student_user u INNER JOIN student_course sc\n"
                ."ON u.user_id = sc.student_id\n"
                ."WHERE sc.course_id = {$course_id}\n";
        $list = Db::query($sql);
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        $this->view->engine->layout("window");
        return $this->fetch();
    }

    /**
     * 修改课程信息：课程名称、学分、学时、容量、时间、地点、描述 
     * @return 是否成功
     */
    public function editCourseInfo() {
        $course_code = input('get.course_code');
        $res = Course::checkCourseCode($course_code);
        if (!$res) {
            Base::logout();
            return "<script>alert('非法操作！');window.history.go(-1);</script>";
        }
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
            $res = Db::name("course")->where('course_code', $course_code)->update($data);
            if ($res) {
                return $this->fetch("public/msg", ["code" => 1, "msg" => "您已成功修改课程".$course_code."的课程信息"]);
            }
            else {
                return "<script>alert('课程".$course_code."的信息与之前相同');window.history.go(-1);</script>";
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
     * 修改课程授课教师 
     * @return 是否成功
     */
    public function changeCourseTeacher() {
        $course_id = input('get.course_id');
        $old_user_name = input('get.real_name');
        $res = Course::checkCourseID($course_id);
        if (!$res) {
            Base::logout();
            return "<script>alert('非法操作！');window.history.go(-1);</script>";
        }
        if (isset($_POST["submit"])) {
            $data = array();
            $data['user_name'] = input('post.new_user_name');
            $data['real_name'] = input('post.new_user_real_name');
            $res = Base::checkValidate('user_real', $data, 2);
            if ($res['status'] == -1) {
                $err = $res['msg'];
                return "<script>alert('{$err}');window.history.go(-1);</script>";
            }
            $exist = Base::checkUserAndRole($data['user_name'], 'Teacher');
            if ($exist == -1) {
                Base::addLog(2, "新授课教师账号或名字错误");
                return "<script>alert('新授课教师账号或名字错误');window.history.go(-1);</script>";
            } else if ($exist == -2) {
                Base::addLog(2, "该用户账号不是教师账号");
                return "<script>alert('该用户账号不是教师账号');window.history.go(-1);</script>";
            }
            
            $data = array();
            $data['course_id'] = $course_id;
            $res = Db::name('teacher_course')->where($data)->update(['teacher_id' => $user_id]);
            if ($res) {
                return $this->fetch("public/msg", ["code" => 1, "msg" => "您已成功修改课程授课教师"]);
            }
            else {
                return "<script>alert('新授课教师与原授课教师相同');window.history.go(-1);</script>";
            }
        }
        else {
            $this->view->engine->layout("window");
            $course = Db::name('course')->field('course_code, course_name')->where('course_id', $course_id)->find();
            $this->assign("course", $course);
            $this->assign("old_user_name", $old_user_name);
            return $this->fetch();
        }
    }

    
    /**
     * 冻结课程
     * @return 是否成功
     */
    public function blockCourse() {
        $course_code = input('get.course_code');
        $res = Course::checkCourseCode($course_code);
        if (!$res) {
            Base::logout();
            return "<script>alert('非法操作！');window.history.go(-1);</script>";
        }
        $data = array();
        $data['course_status'] = 2;
        $res = Db::name("course")->where("course_code", $course_code)->update($data);
        if ($res) {
            return json(['status' => 1]);
        }
        else {
            return json(['status' => 0]);
        }
    }

    /**
     * 激活课程
     * @return 是否成功
     */
    public function activateCourse() {
        $course_code = input('get.course_code');
        $res = Course::checkCourseCode($course_code);
        if (!$res) {
            Base::logout();
            return "<script>alert('非法操作！');window.history.go(-1);</script>";
        }
        $data = array();
        $data['course_status'] = 1;
        $res = Db::name("course")->where("course_code", $course_code)->update($data);
        if ($res) {
            return json(['status' => 1]);
        }
        else {
            return json(['status' => 0]);
        }
    }

    /**
     * 删除课程
     * @return 是否成功
     */
    public function deleteCourse() {
        $course_id = input('get.course_id');
        $res = Course::checkCourseID($course_id);
        if (!$res) {
            Base::logout();
            return "<script>alert('非法操作！');window.history.go(-1);</script>";
        }
        $course_id = (int)$course_id;
        $res1 = Db::name('course')->where('course_id', $course_id)->delete();
        $res2 = Db::name('student_course')->where('course_id', $course_id)->delete();
        $res3 = Db::name('teacher_course')->where('course_id', $course_id)->delete();
        if ($res1) {
            return json(['status' => 1]);
        }
        else {
            Base::addLog(3, '课程ID不存在');
            Base::logout();
            return json(['status' => 0]);
        }
    }

}
