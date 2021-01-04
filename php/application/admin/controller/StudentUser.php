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
        if (request()->isPost()) {
            $data = array();
            $data['user_name'] = input('post.user_name');
            $data['real_name'] = input('post.real_name');
            $data['gender'] = (int)input('post.gender');
            $data['email'] = input('post.email');
            $data['password'] = input('post.new_password');
            $data['repassword'] = input('post.renew_password');
            $data['mobile_number'] = input('post.mobile_number');
            $data['grade'] = input('post.grade');
            $res = Base::checkValidate('add_student', $data, 2);
            if ($res['status'] == -1) {
                return json($res);
            }
            $exist = Db::name('student_user')->where('user_name', $data['user_name'])->find();
            if ($exist){
                Base::addLog(2, '用户账号已存在');
                return json(['status' => 0, 'msg' => '用户账号已存在']);
            }
            unset($data['repassword']);
            $res = Db::name("student_user")->insert($data);
            if ($res){
                return json(['status' => 1, 'msg' => "您已成功添加学生".$data['user_name']]);
            } else {
                Base::addLog(2, $err);
                return json(['status' => 0, 'msg' => '添加学生用户失败']);
            }
        }
        else {
            $this->view->engine->layout("window");
            return $this->fetch();
        }
    }

    /**
     * 检查GET请求中的username是否合法
     * @return 是否合法
     */
    public function checkGetUserName($user_name) {
        $res = Base::checkValidate('user_name', ['user_name' => $user_name], 3);
        if ($res['status'] == -1) {
            return false;
        }

        $exist = Db::name('student_user')->where('user_name', $user_name)->find();
        if (!$exist) {
            Base::addLog(3, '用户账号不存在');
            return false;
        }

        return true;
    }


    /**
     * 修改用户个人信息：姓名、性别、邮箱、手机号
     * @return 是否成功
     */
    public function editUserInfo() {
        $user_name = input('get.user_name');
        $check = $this->checkGetUserName($user_name);
        if (!$check) {
            Base::logout();
            return "<script>alert('非法操作！');window.history.go(-1);</script>";
        }
        if (request()->isPost()) {
            $data = array();
            $data['real_name'] = input('post.real_name');
            $gender = input('post.gender');
            $data['gender'] = (int)$gender;
            $data['email'] = input('post.email');
            $data['grade'] = input('post.grade');
            $data['mobile_number'] = input('post.mobile_number');
            $res = Base::checkValidate('edit_student_info', $data, 2);
            if ($res['status'] == -1) {
                $err = $res['msg'];
                return "<script>alert('{$err}');window.history.go(-1);</script>";
            }

            $res = Db::name("student_user")->where("user_name", $user_name)->update($data);
            if ($res) {
                return $this->fetch("public/msg", ["code" => 1, "msg" => "您已成功修改用户".$user_name."的用户信息"]);
            }
            else {
                return "<script>alert('学生信息并未变化');window.history.go(-1);</script>";
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
        $check = $this->checkGetUserName($user_name);
        if (!$check) {
            Base::logout();
            return json(['status'=>-1,'msg'=>'非法操作！']);
        }
        if (isset($_POST["submit"])) {
        
            $new_password = input("post.new_password");
            $renew_password = input("post.renew_password");
            if ($new_password != $renew_password) {
                Base::addLog(3, '您两次输入的密码不相同，请检查后重新输入');
                return json(['status'=>-1,'msg'=>'您两次输入的密码不相同，请检查后重新输入']);
            }
            $data = array();
            $md5_salt = config('md5_salt');
            $data["password"] = md5(md5($new_password).$md5_salt);
            
            $res = model("AdminUser")->updateUserInfo($user_name, $data);
            if ($res) {
                return json(['status'=>1,'msg'=>"您已成功重置用户".$user_name."的登录密码"]);
            }
            else {
                Base::addLog(2, '新密码和原密码相同');
                return json(['status'=>-1,'msg'=>'新密码和原密码相同']);
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
        $check = $this->checkGetUserName($user_name);
        if (!$check) {
            Base::logout();
            return json(['status'=>-1,'msg'=>'非法操作！']);
        }

        $data = array();
        $data['token'] = '';
        $res = Db::name("student_user")->where("user_name", $user_name)->update($data);
        if ($res) {
            return json(['status' => 1,'msg'=>'']);
        }
        else {
            return json(['status' => -1,'msg'=>'']);
        }
    }

    /**
     * 冻结用户
     * @return 是否成功
     */
    public function blockUser() {
        $user_name = input('get.user_name');
        $check = $this->checkGetUserName($user_name);
        if (!$check) {
            Base::logout();
            return json(['status'=>-1,'msg'=>'非法操作！']);
        }
        $data = array();
        $data['status'] = 2;
        $data['error_time'] = -1;
        $res = Db::name("student_user")->where("user_name", $user_name)->update($data);
        if ($res) {
            return json(['status' => 1,'msg'=>'']);
        }
        else {
            return json(['status' => -1,'msg'=>'']);
        }
    }

    /**
     * 激活用户
     * @return 是否成功
     */
    public function activateUser() {
        $user_name = input('get.user_name');
        $check = $this->checkGetUserName($user_name);
        if (!$check) {
            Base::logout();
            return json(['status'=>-1,'msg'=>'非法操作！']);
        }
        $data = array();
        $data['status'] = 1;
        $data['error_time'] = 0;
        $data['error_count'] = 0;
        $res = Db::name("student_user")->where("user_name", $user_name)->update($data);
        if ($res) {
            return json(['status' => 1,'msg'=>'']);
        }
        else {
            return json(['status' => -1,'msg'=>'']);
        }
    }

    /**
     * 删除用户
     * @return 是否成功
     */
    public function deleteUser() {
        $user_id = (int)input('get.user_id');

        $exist = Db::name('student_user')->where('user_id', $user_id)->find();
        if (!$exist) {
            Base::addLog(3, '用户账号不存在');
            Base::logout();
            return json(['status'=>-1,'msg'=>'非法操作！']);
        }
        $res1 = Db::name('student_user')->where('user_id', $user_id)->delete();
        $res2 = Db::name('student_course')->where('student_id', $user_id)->delete();
        if ($res1) {
            return json(['status' => 1,'msg'=>'']);
        }
        else {
            return json(['status' => -1,'msg'=>'']);
        }
    }

}
