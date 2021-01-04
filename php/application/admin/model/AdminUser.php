<?php

/**
* 管理员用户数据表 AdminUser
* 负责读取、查询管理员用户数据表
* 提供根据用户账号查询用户信息、根据用户ID查询用户角色、修改用户信息功能
* @file      AdminUser.php
* @date      2020/12/20
* @author    YSY
* @version   1.0
*/

namespace app\admin\model;

use think\Model;
use think\Db;

class AdminUser extends Model {

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
     * 获取用户角色ID
     * @param int $user_id 用户ID
     * @return array 用户角色列表
     */
    public function getRoleByUserID($user_id) {
        $res = Db::name('user_role')->field('role_id')->where('user_id', $user_id)->select();
        $role_ids = array();
        foreach ($res as $k => $value) {
            $role_ids[] = $value['role_id'];
        }
        return $role_ids;
    }

    /**
     * 检查用户密码是否正确
     * @param string 用户账号 用户密码
     * @return array status, msg
     */
    public function checkUserPassword($user_name, $password) {
        $res = Db::name('admin_user')->field('password,error_time,error_count, status')->where('user_name', $user_name)->find();

        if (!$res) return ['status'=>-1,'msg'=>'用户名或密码错误'];

        $data = array();
        $now = time();

        if ($res['status'] == 2) {
            if ($res['error_time'] < 0) return ['status'=>-1,'msg'=>'抱歉，由于某些原因，系统管理员已将您的账户冻结'];
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
        
        if ($res['error_count'] == 1) {
            $this->save(["error_time" => $now], ['user_name' => $user_name]);
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

    /**
     * 更新用户信息
     * @param string $user_name 用户名
     * @param string $data 请求修改的用户属性及用户属性值
     * @return string 返回token
     */
    public function updateUserInfo($user_name, $data) {
        // allowField,过滤数组中的非数据表字段数据
        $res = $this->allowField(true)->save($data, ['user_name' => $user_name]);
        return $res;
    }

    /**
     * 添加新用户
     * @param string $data 用户属性及用户属性值
     * @return string 返回token
     */
    public function addUser($data, $role_name) {
        // allowField,过滤数组中的非数据表字段数据
        $this->data($data);
        $res = $this->allowField(true)->save();
        $user_id = $this->user_id;
        $role_id = Db::name('admin_role')->where('role_name', $role_name)->value('role_id');
        $res2 = Db::name('user_role')->insert(['user_id' => $user_id, 'role_id' => $role_id]);
        return $res&&$res2;
    }

}
