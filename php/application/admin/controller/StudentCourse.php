<?php

/**
* 选课控制器 StudentCourse
* 主要负责教务查看、分配、取消学生选课
* @file      StudentCourse.php
* @date      2020/12/28
* @author    YSY
* @version   1.0
*/
namespace app\admin\controller;

use think\Request;
use think\Db;
use think\Session;

class StudentCourse extends Base {


    /**
     * 查询并返回学生列表，并提供相应的操作接口
     */
    public function index() {
        $list = Db::name('student_user')->field('user_id, user_name, status, real_name, gender, grade, email')->select();
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        return $this->fetch();
    }

    /**
     * 查询并返回学生已选的课程列表，并提供相应的操作接口
     */
    public function showStudentCourse() {
        $user_id = (int)input("get.user_id");
        $real_name = input('get.real_name');
        $sql = "SELECT c.course_id, c.course_code, c.course_name, c.course_status, c.course_credit, c.course_hour,"
              ."c.course_capacity, c.course_student_num, c.course_time, c.course_room\n"
              ."FROM course c INNER JOIN student_course sc\n"
              ."ON c.course_id = sc.course_id\n"
              ."WHERE sc.student_id = {$user_id}\n";
        $list = Db::query($sql);
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        $this->assign("real_name", $real_name);
        $this->view->engine->layout("window");
        return $this->fetch();
    }

    /**
     * 手动分配学生课程
     * @return 是否成功
     */
    public function addStudentCourse() {
        $user_id = (int)input("get.user_id");

        if (isset($_GET["course_id"])) {
            $course_id = (int)input("get.course_id");
            $data = Db::name("course")->field('course_capacity, course_student_num')->where('course_id', $course_id)->find();
            if (!$data) {
                Base::addLog(3, '课程账号ID不存在');
                Base::logout();
                return ['status' => -1, 'msg' => '非法操作'];
            }
            if ($data['course_capacity'] == $data['course_student_num']) return ['status' => -1, 'msg' => '课程人数已满'];
            $exist = Db::name("student_user")->where('user_id', $user_id)->find();
            if (!$exist) {
                Base::addLog(3, '学生账号ID不存在');
                Base::logout();
                return ['status' => -1, 'msg' => '非法操作'];
            }
            $exist = Db::name("student_course")->where(['student_id' => $user_id, 'course_id' => $course_id])->find();
            if ($exist) {
                Base::addLog(2, '学生重复选课');
                return ['status' => -1, 'msg' => '该学生已选此课程！'];
            }
            $res1 = Db::name("student_course")->insert(['student_id' => $user_id, 'course_id' => $course_id]);
            $res2 = Db::name("course")->where('course_id', $course_id)->update(['course_student_num' => $data['course_student_num'] + 1]);
            if ($res1 && $res2){
                return ['status' => 1, 'msg' => '选课成功'];
            } else {
                return ['status' => -1, 'msg' => '选课失败'];
            }
        }
        else {
            $this->view->engine->layout("window");
            $sql1 = "SELECT c.course_id\n"
              ."FROM course c INNER JOIN student_course sc\n"
              ."ON c.course_id = sc.course_id\n"
              ."WHERE sc.student_id = {$user_id}\n";
            $res = Db::query($sql1);
            $course_ids = array();
            foreach ($res as $key => $value) {
                $course_ids[] = $value['course_id'];
            }

            $sql2 = "SELECT c.course_id, c.course_code, c.course_name, c.course_credit, c.course_hour,"
                  ."c.course_capacity, c.course_student_num, c.course_time, c.course_room, au.real_name\n"
                  ."FROM course c LEFT JOIN teacher_course tc\n"
                  ."ON c.course_id = tc.course_id\n"
                  ."LEFT JOIN admin_user au\n"
                  ."ON tc.teacher_id = au.user_id\n"
                  ."WHERE c.course_status = 1";
            $list = Db::query($sql2);
            foreach ($list as $key => $value) {
                if (in_array($value['course_id'], $course_ids)){
                    unset($list[$key]);
                }
            }
            $real_name = input('get.real_name');
            $this->assign("list", $list);
            $this->assign("data_num", count($list));
            $this->assign("real_name", $real_name);
            return $this->fetch();
        }
    }

    /**
     * 取消已选课程
     * @return 是否成功
     */
    public function cancelStudentCourse() {
        $user_id = (int)input("get.user_id");
        $course_id = (int)input('get.course_id');
        $exist = Db::name("student_course")->where(['student_id' => $user_id, 'course_id' => $course_id])->find();
        if (!$exist) {
            Base::addLog(3, '学生未选此课程');
            Base::logout();
            return ['status' => -1, 'msg' => '该学生未选此课程！'];
        }
        $res1 = Db::name("student_course")->where(['student_id' => $user_id, 'course_id' => $course_id])->delete();
        $course_student_num = Db::name("course")->where('course_id', $course_id)->value('course_student_num');
        $res2 = Db::name("course")->where('course_id', $course_id)->update(['course_student_num' => $course_student_num - 1]);
        if ($res1 && $res2){
            return ['status' => 1, 'msg' => '退课成功'];
        } else {
            return ['status' => -1, 'msg' => '退课失败'];
        }  
    }
}
