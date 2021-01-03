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

		self::addLog();
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
     * @param $description 操作描述
	 * @param $log_level 出错时要记录的安全日志等级（1：INFO，2：ERROR，3：WARNING）
	 */
	public static function addLog($log_level=1, $description='') {
		$data = array();
		$data['log_level'] = $log_level;
		$data['description'] = $description;
        $data['querystring'] = request()->query()?'?'.request()->query():'';
        $data['module'] = request()->module();
        $data['controller'] = request()->controller();
        $data['action'] = request()->action();
        $data['request_method'] = request()->method();
        $data['user_id'] = Session::get('user_id');
        $data['ip'] = ip2long(request()->ip());
		$data['time'] = time();
        Db::name('admin_log')->insert($data);
	}

	/**
	* 校验指定场景下的数据是否符合要求
	* @param $scene_name 场景名称
	* @param $data 校验数据
	* @param $log_level 出错时要记录的安全日志等级（1：INFO，2：ERROR，3：WARNING）
	* @return 是否符合要求
	*/
	public static function checkValidate($scene_name, $data, $log_level) {
		$validate = validate('DataInput');
        $check = $validate->scene($scene_name)->check($data);
        if (!$check) {
            $err = $validate->getError();
            Base::addLog($log_level, $err);
            return ['status' => -1, 'msg' => $err];
        }
        return ['status' => 1, 'msg' => ''];
	}

	/**
	* 校验用户是否存在以及用户与角色名称是否匹配
	* @param $user_name 用户名称（我在此之前进行校验，确保传递来的用户名为10位数字，避免sql注入攻击）
	* @param $role_name 角色名称
	* @param $user_id 用户ID，传递此参数时不再使用user_name
	* @return -1:用户名不存在，-2：用户名角色不匹配，1：正确
	*/
	public static function checkUserAndRole($user_name, $role_name, $user_id=-1) {
		if ($user_id == -1) 
			$user_id = Db::name('admin_user')->where('user_name', $user_name)->value('user_id');
		if (!$user_id) return -1;
		$role_id = Db::name("admin_role")->where('role_name', $role_name)->value('role_id');
		$res = Db::name('user_role')->where(['user_id' => $user_id, 'role_id' => $role_id])->find();
		if (!$res) return -2;
		return 1;
	}

	/**
     * 注销登录，在用户非法操作后调用
     * 删除用户的登录信息：session、cookie和token
     */
	public static function logout() {
        $user_name = Session::get('user_name');
        Session::set('user_name', null);
        Session::set('real_name', null);
        Session::set('user_id', null);
        Session::set('role_ids', null);
        Cookie::set('user_name', null);
        Cookie::set('token', null);
        model('AdminUser')->updateUserToken($user_name);
    }
}








