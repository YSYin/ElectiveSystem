<?php

/**
* 验证器类DataInput
* 主要负责对输入数据进行格式检查
* @file      DataInput.php
* @date      2021/1/1
* @author    YSY
* @version   1.0
*/

namespace app\admin\validate;

use think\Validate;

class DataInput extends Validate
{
    protected $rule = [
    	['user_id', 'require|number', '非法输入|非法输入'],
    	['user_name', 'require|number|length:10', '账号不得为空|账号不是数字|账号不是10位数字'],
    	['real_name', 'require|max:16', '姓名不得为空|姓名长度超过16位'],
    	['gender', 'require|number|in:1,2', '性别不得为空|非法输入|非法输入'],
        ['grade', 'require|chs|max:16', '年级不得为空|年级必须全为汉字|年级长度不超过16'],
    	['email', 'email', '邮箱格式错误'],
    	['mobile_number', 'require|number|length:11', '手机号不得为空|手机号不是数字|手机号不是11位数字'],
    	['password', 'require|alphaNum|length:32', '密码不得为空|非法输入|非法输入'],
        ['repassword', 'require|confirm:password', '密码不得为空|两次输入密码不同'],
    	['token', 'require|alphaNum|length:32', '非法输入|非法输入|非法输入'],
    	['captcha', 'require', '验证码不得为空'],
    	['remember', 'require|number|in:0,1', '非法输入|非法输入|非法输入'],
    	['course_code', 'require|number|length:10', '课程号不得为空|课程号不是数字|课程号不是10位数字'],
    	['course_id', 'require|number', '非法输入|非法输入'],
    	['course_name', 'require|max:64', '课程名称不得为空|课程名称不得超过64位'],
    	['course_credit', 'require|number', '课程学分不得为空|课程学分不是数字'],
    	['course_hour', 'require|number', '课程学时不得为空|课程学时不是数字'],
    	['course_capacity', 'require|number', '课程限选人数不得为空|课程限选人数不是数字'],
    	['course_time', 'require|max:64', '课程上课时间不得为空|上课时间超过最大长度'],
    	['course_room', 'require|max:64', '课程上课地点不得为空|上课地点超过最大长度'],
    	['course_info', 'require|max:512', '课程描述不得为空|课程描述超过最大长度']
    ];
    
    protected $scene = [
        'edit_user_info'  =>  ['real_name','gender','email','mobile_number'],
        'add_user'  =>  ['user_name', 'real_name','gender','email','mobile_number','password','repassword'],
        'user_id'  =>  ['user_id'],
        'user_real'  =>  ['user_name', 'real_name'],
        'user_name'  =>  ['user_name'],

        'edit_student_info'  =>  ['real_name','gender','email','mobile_number', 'grade'],
        'add_student'  =>  ['user_name', 'real_name','gender','email','mobile_number','password','repassword', 'grade'],

        'course_code'  =>  ['course_code'],
        'course_id'  =>  ['course_id'],
        'edit_course_info'  =>  ['course_name', 'course_credit', 'course_hour', 'course_capacity', 'course_time','course_room','course_info'],

        'change_self_password'  =>  ['password'],
        'show_course_info'  =>  ['course_code','real_name'],
        'course_info'  =>  ['course_code', 'course_name', 'course_credit', 'course_hour', 'course_capacity', 'course_time','course_room','course_info'],
        
        'change_course_teacher'  =>  ['course_id','user_name','real_name'],
    ];

}