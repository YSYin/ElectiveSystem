<?php

/**
* 左侧菜单类 LeftMenu
* 查询当前用户所能操作的菜单，并将其按照既定顺序输出
* 输出时同时根据当前访问的controller/action决定哪一个菜单项展开
* @file      Menu.php
* @date      2020/12/20
* @author    YSY
* @version   1.0
*/

namespace app\admin\widget;

use think\Db;
use think\Request;
use think\Session;
use think\Cookie;

class LeftMenu {

    /**
     * 获取用户所能访问的菜单/权限，以左侧菜单的HTML格式返回
     * @return string 左侧菜单的HTML
     */
    public function index() {
        $menu = model('AdminMenu')->getUserMenu(1);
        $menuTree = list_to_tree($menu);
        trace($menuTree);

        $html = '<ul id="nav">';
        $html .=$this->menu_tree($menuTree);
        $html .= "
                </ul>";
        return $html;
    }

    /**
     * 检查当前菜单是否应该展开
     * @param array $tree 左侧菜单列表
     * @return bool 是否应该展开
     */
    private function check_menu_open($tree){
        $request = Request::instance();
        if (is_array($tree)) {
            foreach ($tree as $val) {
                if (strtolower($val['controller']) == strtolower($request->controller()) && strtolower($val['action']) == strtolower($request->action())) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 获取左侧菜单HTML
     * @param array $tree 左侧菜单列表
     * @return string 左侧菜单HTML
     */
    private function menu_tree($tree) {
        $request = Request::instance();
        $html = '';

        if (is_array($tree)) {

            foreach ($tree as $val) {

                if (isset($val["menu_name"])) {
                    $title = $val["menu_name"];

                    if (!empty($val["action"])) {
                        $url = url('@admin/' . $val['controller'] . '/' . $val['action']);
                    }

                    if (empty($val['menu_icon'])) {
                        $icon = "&#xe6a7;";
                    } else {
                        $icon = "&#".$val['menu_icon'].";";
                    }
                    if (strtolower($val['controller']) == strtolower($request->controller()) && strtolower($val['action']) == strtolower($request->action())) {
                        $current = 'current';
                    } else {
                        $current = '';
                    }

                    $opened = '';

                    if (isset($val['_child'])) {
                        if($this->check_menu_open($val['_child']))
                            $opened = 'opened';
                        $html .= ' 
                            <li class="list">
                                <a href="javascript:;">
                                    <i class="iconfont">' . $icon . '</i>
                                    ' . $title . '
                                    <i class="iconfont nav_right">&#xe697;</i>
                                </a>
                                <ul class="sub-menu '.$opened.' ">
                            ';

                        $html .= $this->menu_tree($val['_child']);

                        $html .= '              
                            </ul>
                        </li>
                        ';
                    } else {

                        $html .= '
                            <li class="'.$current.'">
                            <a href = "' . $url . '">
                            <i class="iconfont">' .  $icon. '</i>
                            ' .  $title . '
                              
                            </a>
                            </li>
                            ';
                    }
                }
            }
        }

        return $html;
    }

}