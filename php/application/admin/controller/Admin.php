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
        if (request()->isPost()) {
            $data = array();
            $data['real_name'] = input('post.real_name');
            $data['gender'] = (int)input('post.gender');
            $data['email'] = input('post.email');
            $data['mobile_number'] = input('post.mobile_number');
            $res = Base::checkValidate('edit_user_info', $data, 2);
            if ($res['status'] == -1) {
                $err = $res['msg'];
                return "<script>alert('{$err}');window.history.go(-1);</script>";
            }
            $res = model('AdminUser')->updateUserInfo($user_name, $data);
            if ($res) {
                return "<script>alert('成功修改个人信息');window.history.go(-1);</script>";
            }
            else {
                return "<script>alert('个人信息并未变化');window.history.go(-1);</script>";
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
        if (request()->isPost()) {
            $new_password = input("post.new_password");
            $renew_password = input("post.renew_password");
            $old_password = input("post.old_password");
            if ($new_password != $renew_password) {
                Base::addLog(3, '两次输入的密码不相同');
                return json(['status'=>0,'msg'=>'您两次输入的密码不相同，请检查后重新输入']);
            }
            $md5_salt = config('md5_salt');
            $old_password = md5($old_password.$md5_salt);
            $admin = model('AdminUser');
            $info_name = 'password';
            $info = $admin->getInfoByUserName($user_name, $info_name);
            if ($info['password'] != $old_password) {
                Base::addLog(2, $err);
                return json(['status'=>0,'msg'=>'您输入的旧密码不正确，请检查后重新输入']);
            }
            $new_password = md5($new_password.$md5_salt);
            $data = array();
            $data['password'] = $new_password;
            $res = $admin->updateUserInfo($user_name, $data);
            if ($res) {
                return json(['status'=>1,'msg'=>'您已成功修改密码']);
            }
            else {
                return json(['status'=>0,'msg'=>'您输入的新密码和旧密码相同']);
            }
        }
        else {
            return $this->fetch();
        }
    }

    /**
     * 重置个人免登录token
     * @return 是否成功
     */
    public function resetSelfToken() {
        $user_name = Session::get("user_name");
        $res = model("AdminUser")->updateUserInfo($user_name, ['token' => '']);
        if ($res) {
            return "<script>alert('您已成功重置免登录token');window.history.go(-1);</script>";
        }
        else {
            return "<script>alert('您无免登录token，无需重置');window.history.go(-1);</script>";
        }
    }

}
