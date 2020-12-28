<?php

/**
* 用户控制器 TeacherUser
* 主要负责增删改查教师用户
* @file      TeacherUser.php
* @date      2020/12/23
* @author    YSY
* @version   1.0
*/
namespace app\admin\controller;

use think\Request;
use think\Db;
use think\Session;

class TeacherUser extends Base {

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

            $res = model('AdminUser')->addUser($data);
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
            
            $res = model("AdminUser")->updateUserInfo($user_name, $data);
            if ($res) {
                return $this->fetch("public/msg", ["code" => 1, "msg" => "您已成功修改用户".$user_name."的用户信息"]);
            }
            else {
                return $this->fetch("public/msg", ["code" => 0, "msg" => "修改用户".$user_name."信息失败"]);
            }
        }
        else {
            $this->view->engine->layout("window");
            $user = model("AdminUser")->getInfoByUserName($user_name, 'user_name, real_name, gender, email, mobile_number');
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
            
            $res = model("AdminUser")->updateUserInfo($user_name, $data);
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
        $res = model("AdminUser")->updateUserToken($user_name);
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
        $res = model("AdminUser")->updateUserInfo($user_name, $data);
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
        $res = model("AdminUser")->updateUserInfo($user_name, $data);
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
        $res1 = Db::name('admin_user')->where('user_id', $user_id)->delete();
        $res2 = Db::name('user_role')->where('user_id', $user_id)->delete();
        $res3 = Db::name('teacher_course')->where('teacher_id', $user_id)->delete();
        if ($res1 && $res2) {
            return true;
        }
        else {
            return false;
        }
    }

}
