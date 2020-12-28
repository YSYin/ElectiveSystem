<?php

/**
* 控制器基类 Base
* 后台管理模块admin中除Login外所有控制器的基类，主要通过_initialize()方法
* 在任何控制器的任何方法执行之前执行，检查用户是否登陆、以及用户权限
* @file      Base.php
* @date      2020/12/11
* @author    YSY
* @version   1.0
*/

namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Cookie;

class Base extends Controller {

	/**
     * 控制器任何方法之前调用，检查用户是否登陆、是否具有权限
     * 根据检查结果跳转响应网页
     */
	public function _initialize() {

		if (!(self::checkAutoLogIn())) {
			$this->error("您还未登录", 'login/index');
		}

		if (!self::checkUserAccess()) {
			$this->error("您没有权限进行此操作");
		}

		$this->addLog();
	}

	/**
     * 检查用户是否已登录，或者是否可以根据cookie自动登录
     * @return bool 
     */
	public static function checkAutoLogIn(){
		if (Session::has('user_name') && Session::has('user_id') && Session::has('role_ids') && Session::has('real_name')){
			return true;
		}
		else if (Cookie::has('user_name') && Cookie::has('token'))
		{
			$user_name = Cookie::get('user_name');
			$token = Cookie::get('token');
			$log_time = time();
			$info = model('AdminUser')->getTokenByUserName($user_name);
			if ($info && $info['token'] == $token && ($log_time - $info['token_set_time'] <= 7 * 24 * 60 * 60)) {
				Session::set('user_name', $user_name);
				Session::set('user_id', $info['user_id']);
				Session::set('real_name', $info['real_name']);
				Session::set('role_ids', model('AdminUser')->getRoleByUserID($info['user_id']));
				return true;
			}
		}
		return false;
		
	}

	/**
     * 检查用户权限
     * @return bool 
     */
	private function checkUserAccess() {

		$user_id = Session::get('user_id');
        if ($user_id==1) {
            return true;
        }

        $c = strtolower(request()->controller());
        $a = strtolower(request()->action());

        if (preg_match('/^public_/', $a)) {
            return true;
        }
        if ($c == 'index' && $a == 'index') {
            return true;
        }

        $menu = model('AdminMenu')->getUserMenu();
        foreach ($menu as $k => $v) {
            if (strtolower($v['controller']) == $c && strtolower($v['action']) == $a) {
                return true;
            }
        }

        return false;
    }
	
	/**
     * 添加操作日志 
     */
	private function addLog() {
		$data = array();
        $data['querystring'] = request()->query()?'?'.request()->query():'';
        $data['module'] = request()->module();
        $data['controller'] = request()->controller();
        $data['action'] = request()->action();
        $data['user_id'] = Session::get('user_id');
        $data['ip'] = ip2long(request()->ip());
		$data['time'] = time();
        Db::name('admin_log')->insert($data);
	}
}








