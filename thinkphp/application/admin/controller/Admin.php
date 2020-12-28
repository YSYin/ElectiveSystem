<?php

/**
* 后台主页
* 主要负责显示后台主页、并对个人账户进行操作
* @file      Admin.php
* @date      2020/12/20
* @author    YSY
* @version   1.0
*/

namespace app\admin\controller;

use think\Request;
use think\Db;
use think\Session;

class Admin extends Base {

    /**
     * 返回主页面
     */
    public function index() {
        return $this->fetch('index');
    }

    /**
     * 修改个人信息，包括真实姓名、性别、邮箱、手机号等
     */
    public function editSelfInfo() {
        $user_name = Session::get("user_name");
        if (isset($_POST["submit"])) {
            $data = array();
            $data['real_name'] = input('post.real_name');
            $gender = input('post.gender');
            $data['gender'] = (int)$gender;
            $data['email'] = input('post.email');
            $data['mobile_number'] = input('post.mobile_number');
            $res = model('AdminUser')->updateUserInfo($user_name, $data);
            if ($res) {
                return "<script>alert('成功修改个人信息');window.history.go(-1);</script>";
            }
            else {
                return "<script>alert('修改个人信息失败');window.history.go(-1);</script>";
            }
        }
        else {
            $user = model('AdminUser')->getInfoByUserName($user_name, 'user_name, real_name, gender, email, mobile_number');
            $this->assign("user", $user);
            return $this->fetch();
        }
    }

    /**
     * 修改个人密码
     */
    public function changeSelfPassword() {
        $user_name = Session::get("user_name");
        if (isset($_POST["submit"])) {

            $new_password = input("post.new_password");
            $renew_password = input("post.renew_password");
            if ($new_password != $renew_password) {
                return $this->error("您两次输入的密码不相同，请检查后重新输入");
            }

            $md5_salt = config('md5_salt');
            $old_password = md5(md5(input("post.old_password")).$md5_salt);
            $admin = model('AdminUser');
            $info_name = 'password';
            $info = $admin->getInfoByUserName($user_name, $info_name);
            if ($info['password'] != $old_password) {
                return $this->error("您输入的旧密码不正确，请检查后重新输入");
            }
            $new_password = md5(md5(input("post.new_password")).$md5_salt);
            $data = array();
            $data['password'] = $new_password;
            $res = $admin->updateUserInfo($user_name, $data);
            if ($res) {
                return $this->success("您已成功修改密码");
            }
            else {
                return $this->error("修改密码失败");
            }
        }
        else {
            return $this->fetch();
        }
    }

}
