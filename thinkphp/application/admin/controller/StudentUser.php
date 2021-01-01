<?php

/**
* 用户控制器 StudentUser
* 主要负责增删改查学生用户
* @file      StudentUser.php
* @date      2020/12/23
* @author    YSY
* @version   1.0
*/
namespace app\admin\controller;

use think\Request;
use think\Db;
use think\Session;

class StudentUser extends Base {

    /**
     * 查询并返回学生列表，并提供相应的操作接口
     */
    public function index() {
        $list = Db::name('student_user')->field('user_id, user_name, status, real_name, gender, grade, email, mobile_number')->select();
        $this->assign("list", $list);
        $this->assign("data_num", count($list));
        return $this->fetch();
    }

    /**
     * 添加用户
     * @return 是否成功
     */
    public function addUser() {
        if (isset($_POST["submit"])) {
            $data = array();
            $data['user_name'] = input('post.user_name');
            $data['real_name'] = input('post.real_name');
            $gender = input('post.gender');
            $data['gender'] = (int)$gender;
            $data['email'] = input('post.email');
            $data['mobile_number'] = input('post.mobile_number');
            $data['grade'] = input('post.grade');

            $res = Db::name("student_user")->insert($data);
            if ($res){
                return $this->fetch("public/msg", ["code" => 1, "msg" => "您已成功添加用户".$data['user_name']]);
            } else {
                return $this->fetch("public/msg", ["code" => 0, "msg" => "添加用户失败"]);
            }
        }
        else {
            $this->view->engine->layout("window");
            return $this->fetch();
        }
        
    }

    /**
     * 查找用户，实际上在前端通过javascript实现
     */
    public function searchUser(){}

    /**
     * 修改用户个人信息：姓名、性别、邮箱、手机号
     * @return 是否成功
     */
    public function editUserInfo() {
        $user_name = input('get.user_name');
        if (isset($_POST["submit"])) {
            $data = array();
            $data['real_name'] = input('post.real_name');
            $gender = input('post.gender');
            $data['gender'] = (int)$gender;
            $data['email'] = input('post.email');
            $data['mobile_number'] = input('post.mobile_number');
            
            $res = Db::name("student_user")->where("user_name", $user_name)->update($data);
            if ($res) {
                return $this->fetch("public/msg", ["code" => 1, "msg" => "您已成功修改用户".$user_name."的用户信息"]);
            }
            else {
                return $this->fetch("public/msg", ["code" => 0, "msg" => "修改用户".$user_name."信息失败"]);
            }
        }
        else {
            $this->view->engine->layout("window");
            $user = Db::name('student_user')->field('real_name, user_name, gender, grade, email, mobile_number')->where('user_name', $user_name)->find();
            $this->assign("user", $user);
            return $this->fetch();
        }
    }

    /**
     * 重置用户密码 
     * @return 是否成功
     */
    public function resetUserPassword() {
        $user_name = input('get.user_name');
        if (isset($_POST["submit"])) {
            $new_password = input("post.new_password");
            $renew_password = input("post.renew_password");
            if ($new_password != $renew_password) {
                return $this->error("您两次输入的密码不相同，请检查后重新输入");
            }
            $data = array();
            $md5_salt = config('md5_salt');
            $data["password"] = md5(md5($new_password).$md5_salt);
            
            $res = Db::name("student_user")->where("user_name", $user_name)->update($data);
            if ($res) {
                return $this->fetch("public/msg", ["code" => 1, "msg" => "您已成功重置用户".$user_name."的登录密码"]);
            }
            else {
                return $this->fetch("public/msg", ["code" => 0, "msg" => "修改用户".$user_name."登录密码失败"]);
            }
        }
        else {
            $this->view->engine->layout("window");
            $real_name = input('get.real_name');
            $this->assign("user_name", $user_name);
            $this->assign("real_name", $real_name);
            return $this->fetch();
        }
    }

    /**
     * 重置用户token，即删除用户免登录时的token
     * @return 是否成功
     */
    public function resetUserToken() {
        $user_name = input('get.user_name');
        $data = array();
        $data['token'] = '';
        $res = Db::name("student_user")->where("user_name", $user_name)->update($data);
        if ($res) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 冻结用户 
     * @return 是否成功
     */
    public function blockUser() {
        $user_name = input('get.user_name');
        $data = array();
        $data['status'] = 2;
        $res = Db::name("student_user")->where("user_name", $user_name)->update($data);
        if ($res) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 激活用户 
     * @return 是否成功
     */
    public function activateUser() {
        $user_name = input('get.user_name');
        $data = array();
        $data['status'] = 1;
        $res = Db::name("student_user")->where("user_name", $user_name)->update($data);
        if ($res) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * 删除用户
     * @return 是否成功
     */
    public function deleteUser() {
        $user_id = (int)input('get.user_id');
        $res1 = Db::name('student_user')->where('user_id', $user_id)->delete();
        $res2 = Db::name('student_course')->where('student_id', $user_id)->delete();
        if ($res1) {
            return true;
        }
        else {
            return false;
        }
    }

}
