<?php

/**
* 菜单(权限)数据表 AdminMenu
* 负责读取、查询菜单数据表
* 负责返回当前用户所能看到的菜单/权限
* @file      AdminMenu\.php
* @date      2020/12/20
* @author    YSY
* @version   1.0
*/

namespace app\admin\model;

use think\Model;
use think\Db;
use think\Session;

class AdminMenu extends Model {

    /**
     * 获取用户所能访问的菜单/权限
     * @param type $display 是否只返回可显示在左侧列表中的菜单项
     * @return array 用户菜单/权限列表
     */
    public function getUserMenu($display=null) {
        $where = array();
        $user_id = Session::get('user_id');
        $role_ids = Session::get('role_ids');
            $res = Db::name('role_menu')->field('menu_id')
                                        ->where('role_id','in', $role_ids)
                                        ->select();
            if (!$res) {
                return false;
            }
            $menu_ids = array();
            foreach ($res as $k => $value) {
                $menu_ids[] = $value['menu_id'];
            }
            $where['menu_id'] = ['in', $menu_ids];

        if ($display) {
            $where['display'] = $display;
        }

        $res = Db::name('admin_menu')->where($where)->order('listorder asc')->select();
        return $res;
    }

}