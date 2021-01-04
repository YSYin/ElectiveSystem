<?php

/**
* 学生用户数据表 StudentUser
* 负责读取、查询学生用户数据表
* @file      StudentUser.php
* @date      2020/12/31
* @author    YSY
* @version   1.0
*/

namespace app\student\model;

use think\Model;
use think\Db;

class StudentUser extends Model {

    public $status = array(1 => '无效', 2 => '有效');

    /**
     * 登录时调用，获取用户信息
     * @param string $user_name 用户账号名
     * @param string $info_name 请求查看的用户属性，以,分隔
     * @return array 用户信息
     */
    public function getInfoByUserName($user_name, $info_name) {
        $info = $this->field($info_name)
                     ->where(array('user_name' => $user_name, 'status' => 1))
                     ->find();
        if ($info) {
            $info = $info->data;
        }
        return $info;
    }

    /**
     * 获取用户token和token设置时间
     * @param string $user_name 用户账号名
     * @return array 用户信息
     */
    public function getTokenByUserName($user_name) {
        $info = $this->field('user_id, real_name, token, token_set_time')
                     ->where('user_name', $user_name)
                     ->find();
        if ($info) {
            $info = $info->data;
        }
        return $info;
    }

    /**
     * 检查用户密码是否正确
     * @param string 用户账号 用户密码
     * @return array status, msg
     */
    public function checkUserPassword($user_name, $password) {
        $res = Db::name('student_user')->field('password,error_time,error_count, status')->where('user_name', $user_name)->find();

        if (!$res) return ['status'=>-1,'msg'=>'用户名或密码错误'];

        $data = array();
        $now = time();

        if ($res['status'] == 2) {
            if ($now - $res['error_time'] > 30 * 60)
            {
                $data['error_time'] = 0;
                $data['error_count'] = 0;
                $data['status'] = 1;
                $this->save($data, ['user_name' => $user_name]);
            }
            else return ['status'=>-1,'msg'=>'您在10分钟内连续5次输入密码错误，系统已将您的账户冻结30分钟，请等待冻结期结束后重试'];
        }
        
        if ($password == $res['password']){
            $data['error_time'] = 0;
            $data['error_count'] = 0;
            $data['status'] = 1;
            $this->save($data, ['user_name' => $user_name]);
            return ['status'=>1,'msg'=>'登陆成功'];
        }
        if ($now - $res['error_time'] > 10 * 60)
        {
            $data['error_time'] = $now;
            $data['error_count'] = 1;
            $data['status'] = 1;
            $this->save($data, ['user_name' => $user_name]);
            return ['status'=>-1,'msg'=>'用户名或密码错误'];
        }

        if ($res['error_count'] == 4) {
            $data['error_time'] = $now;
            $data['error_count'] = 5;
            $data['status'] = 2;
            $this->save($data, ['user_name' => $user_name]);
            return ['status'=>-1,'msg'=>'您在10分钟内连续5次输入密码错误，系统已将您冻结30分钟，请等待冻结结束后重试'];
        }

        $this->save(["error_count" => $res['error_count'] + 1], ['user_name' => $user_name]);
        return ['status'=>-1,'msg'=>'用户名或密码错误'];
    }

    /**
     * 登陆后更新用户登录时间和登录IP
     * @param string $user_name 用户名
     * @return 是否更新成功
     */
    public function updateUserLogin($user_name) {
        $data = array();
        $data['last_login_time'] = time();
        $data['last_login_ip'] = ip2long(request()->ip());
     
        // allowField,过滤数组中的非数据表字段数据
        $res = $this->allowField(true)->save($data, ['user_name' => $user_name]);
        return $res;
    }

    /**
     * 更新用户token和token设置时间
     * @param string $user_name 用户名
     * @return string 返回token
     */
    public function updateUserToken($user_name) {
        $data = array();
        $rand_int = rand(); 
        $set_time = time();
        $raw_token = $user_name.(string)$set_time.(string)$rand_int;
        $token = md5($raw_token);

        $data['token'] = $token;
        $data['token_set_time'] = $set_time;
     
        // allowField,过滤数组中的非数据表字段数据
        $this->allowField(true)->save($data, ['user_name' => $user_name]);
        return $token;
    }

}
