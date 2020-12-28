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
    public function addUser($data) {
        // allowField,过滤数组中的非数据表字段数据
        $this->data($data);
        $res = $this->allowField(true)->save();
        return $res;
    }

}
