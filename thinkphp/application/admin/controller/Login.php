<?php

/**
* 登录功能控制器
* 主要负责登录页面显示、身份信息验证、注销登录信息
* @file      Login.php
* @date      2020/12/20
* @author    YSY
* @version   1.0
*/

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Cookie;

class Login extends Controller{

	/**
     * 登录主页显示
     * 返回登录页面
     */
    public function index() {
        if (Base::checkAutoLogIn()) {
            $this->redirect('admin/index');
        }
        else {
            $this->view->engine->layout(false);
            return $this->fetch();
        }
    }
    
    /**
     * 登录处理
     * 接收表单中用户名密码等数据，进行身份验证
     * 验证成功时跳转到主页
     */
    public function dologin() {
        if (isset($_POST["submit"])) {
            $user_name = input('post.user_name');
            $password = input('post.password');
            $remember = isset($_POST["remember"]);

            if (!$user_name) {
                $this->error('用户名不能为空');
            }
            if (!$password) {
                $this->error('密码不能为空');
            }

            $admin = model('AdminUser');
            $info_name = 'user_id, user_name, password, real_name';
            $info = $admin->getInfoByUserName($user_name, $info_name);
            
            if (!$info) {
                $this->error('用户名或密码错误');
            }

            $md5_salt = config('md5_salt');

            if (md5(md5($password).$md5_salt) != $info['password']) {
                $this->error('用户名或密码错误');
            } 
            else {
                Session::set('user_name', $info['user_name']);
                Session::set('real_name', $info['real_name']);
                Session::set('user_id', $info['user_id']);
                Session::set('role_ids', $admin->getRoleByUserID($info['user_id']));
                $admin->updateUserLogin($info['user_name']);

                if ($remember) {
                    $token = $admin->updateUserToken($info['user_name']);
                    Cookie::set('token', $token, 7 * 24 *60 *60);
                    Cookie::set('user_name', $user_name, 7 * 24 *60 *60);
                }
                $this->redirect('admin/index');
            }
        } 
    }

    /**
     * 注销登录
     * 删除用户的登录信息：session、cookie和token
     */
    public function logout() {
        $user_name = Session::get('user_name');
        Session::set('user_name', null);
        Session::set('real_name', null);
        Session::set('user_id', null);
        Session::set('role_ids', null);
        Cookie::set('user_name', null);
        Cookie::set('token', null);
        model('AdminUser')->updateUserToken($user_name);
        $this->redirect('login/index');
    }

}
