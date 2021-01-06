<?php

/**
* 控制器基类 Base
* 通过_initialize()方法, 检查用户是否登陆
* @file      Base.php
* @date      2020/12/31
* @author    YSY
* @version   1.0
*/

namespace app\student\controller;

use think\Controller;
use think\Db;
use think\Session;
use think\Cookie;

class Base extends Controller {

	/**
     * 控制器任何方法之前调用，检查用户是否登陆
     * 根据检查结果跳转响应网页
     */

	public function _initialize() {

		if (!(self::checkAutoLogIn())) {
			$this->error("您还未登录", 'login/index');
		}

	}
	

	/**
     * 检查用户是否已登录，或者是否可以根据cookie自动登录
     * @return bool 
     */
	public static function checkAutoLogIn(){
		if (Session::has('user_name') && Session::has('user_id') && !(Session::has('role_ids'))){
			return true;
		}
		else if (Cookie::has('user_name') && Cookie::has('token'))
		{
			$user_name = Cookie::get('user_name');
			$token = Cookie::get('token');
			$log_time = time();
			$info = Db::name('student_user')->field('user_id,token, token_set_time')->where('user_name', $user_name)->find();
			if ($info && $info['token'] == $token && ($log_time - $info['token_set_time'] <= 7 * 24 * 60 * 60)) {
				Session::set('user_name', $user_name);
				Session::set('user_id', $info['user_id']);
				return true;
			}
		}
		return false;
		
	}


	/**
	 * 发送post请求
	 * @param string $url 请求地址
	 * @param array $post_data post键值对数据
	 * @return string
	 */
	public function sendPost($url, $post_data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "$url");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_TIMEOUT,3);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    echo curl_error($ch);
		}
		curl_close($ch);
	  	return $result;
	}
}








