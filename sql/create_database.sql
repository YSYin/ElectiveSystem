#创建web_elective_db数据库
DROP DATABASE IF EXISTS `web_elective_db`;

CREATE DATABASE `web_elective_db`
DEFAULT CHARACTER SET utf8
DEFAULT COLLATE utf8_general_ci;

#创建后台用户表
CREATE TABLE `web_elective_db`.`admin_user` 
( `user_id` INT NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_name` VARCHAR(10) NOT NULL COMMENT '用户账号', 
  `password` VARCHAR(32) NOT NULL COMMENT '用户密码，MD5加密', 
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '用户状态(1：正常，2：冻结)', 
  `real_name` VARCHAR(16)  NOT NULL COMMENT '用户真实姓名', 
  `gender` TINYINT NOT NULL DEFAULT 0 COMMENT '用户性别(1:男, 2:女, 0:未设置)', 
  `email` VARCHAR(320)  NOT NULL DEFAULT '' COMMENT '用户邮箱',
  `mobile_number` VARCHAR(11)  NOT NULL DEFAULT '' COMMENT '用户手机号', 
  `token` VARCHAR(32)  NOT NULL DEFAULT '' COMMENT '用户token，用于保存用户登录信息', 
  `token_set_time` INT NOT NULL DEFAULT 0 COMMENT '设置用户token的时间，用于检查token是否过期', 
  `error_time` INT NOT NULL DEFAULT 0 COMMENT '用户第一次密码错误的登录时间',
  `error_count` TINYINT NOT NULL DEFAULT 0 COMMENT '用户密码错误的次数',
  `last_login_ip` INT NOT NULL DEFAULT 0 COMMENT '用户上次登录IP地址', 
  `last_login_time` INT NOT NULL DEFAULT 0 COMMENT '用户上次登录时间', 
  PRIMARY KEY (`user_id`)) ENGINE = InnoDB;

#创建后台用户角色表
CREATE TABLE `web_elective_db`.`admin_role` 
( `role_id` INT NOT NULL AUTO_INCREMENT COMMENT '角色ID', 
  `role_name` VARCHAR(32) NOT NULL COMMENT '角色名称', 
  `role_info` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '角色描述', 
  PRIMARY KEY (`role_id`), 
  UNIQUE (`role_name`)) ENGINE = InnoDB;

#创建后台菜单表，每个菜单对应一个功能，亦即一个权限
CREATE TABLE `web_elective_db`.`admin_menu` 
( `menu_id` INT NOT NULL AUTO_INCREMENT COMMENT '菜单ID', 
  `menu_name` VARCHAR(32) NOT NULL COMMENT '菜单名称', 
  `parent_id` INT NOT NULL DEFAULT 0 COMMENT '父菜单ID',
  `menu_icon` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '菜单icon',
  `menu_info` VARCHAR(256) NOT NULL DEFAULT '' COMMENT '菜单描述',
  `controller` VARCHAR(20) DEFAULT NULL COMMENT '菜单功能对应的控制器',
  `action` VARCHAR(20) DEFAULT NULL COMMENT '菜单功能对应的方法',
  `listorder` INT UNSIGNED NOT NULL DEFAULT 999 COMMENT '菜单列表排序键值',
  `display` TINYINT NOT NULL DEFAULT 1 COMMENT '是否显示该菜单(1:显示, 2:不显示)',
  PRIMARY KEY (`menu_id`)) ENGINE = InnoDB;

#用户角色对应表
CREATE TABLE `web_elective_db`.`user_role` 
( `user_id` INT NOT NULL COMMENT '用户ID', 
  `role_id` INT NOT NULL COMMENT '角色ID', 
  PRIMARY KEY (`user_id`, `role_id`)) ENGINE = InnoDB;

#角色菜单对应表
CREATE TABLE `web_elective_db`.`role_menu` 
( `role_id` INT NOT NULL , 
  `menu_id` INT NOT NULL , 
  PRIMARY KEY (`role_id`, `menu_id`) ) ENGINE = InnoDB;

#后台用户操作记录表
CREATE TABLE `web_elective_db`.`admin_log` 
( `log_id` INT NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `log_level` TINYINT  NOT NULL DEFAULT 1 COMMENT '日志等级(1：正常操作/INFO，2：错误操作/ERROR，3：警告操作/WARNING)', 
  `module` VARCHAR(32) NOT NULL COMMENT '访问模块名称', 
  `controller` VARCHAR(32)  NOT NULL COMMENT '访问控制器名称', 
  `action` VARCHAR(32)  NOT NULL COMMENT '访问方法名称', 
  `user_id` INT  NOT NULL COMMENT '用户ID', 
  `request_method` VARCHAR(10)  NOT NULL COMMENT 'HTTP请求(GET, POST)',
  `querystring` VARCHAR(255) NOT NULL COMMENT '用户查询参数',
  `ip` INT  NOT NULL COMMENT '用户IP地址',
  `time` INT NOT NULL COMMENT '访问时间', 
  `description` VARCHAR(512) NOT NULL DEFAULT '' COMMENT '日志描述',
  PRIMARY KEY (`log_id`)) ENGINE = InnoDB;

#前台用户操作记录表
CREATE TABLE `web_elective_db`.`student_log` 
( `log_id` INT NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `log_level` TINYINT  NOT NULL DEFAULT 1 COMMENT '日志等级(1：正常操作/INFO，2：错误操作/ERROR，3：警告操作/WARNING)', 
  `module` VARCHAR(32) NOT NULL COMMENT '访问模块名称', 
  `controller` VARCHAR(32)  NOT NULL COMMENT '访问控制器名称', 
  `action` VARCHAR(32)  NOT NULL COMMENT '访问方法名称', 
  `user_id` INT  NOT NULL COMMENT '用户ID', 
  `request_method` VARCHAR(10)  NOT NULL COMMENT 'HTTP请求(GET, POST)',
  `querystring` VARCHAR(255) NOT NULL COMMENT '用户查询参数',
  `ip` INT  NOT NULL COMMENT '用户IP地址',
  `time` INT NOT NULL COMMENT '访问时间', 
  `description` VARCHAR(512) NOT NULL DEFAULT '' COMMENT '日志描述',
  PRIMARY KEY (`log_id`)) ENGINE = InnoDB;

#学生用户表
CREATE TABLE `web_elective_db`.`student_user` 
( `user_id` INT NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `user_name` VARCHAR(10) NOT NULL COMMENT '学生账号', 
  `password` VARCHAR(32) NOT NULL COMMENT '用户密码，MD5加密', 
  `status` TINYINT NOT NULL DEFAULT 1 COMMENT '用户状态(1：正常，2：冻结)', 
  `real_name` VARCHAR(16)  NOT NULL COMMENT '用户真实姓名', 
  `gender` TINYINT NOT NULL DEFAULT 0 COMMENT '用户性别(1:男, 2:女, 0:未设置)', 
  `grade` VARCHAR(16) NOT NULL COMMENT '学生年级', 
  `email` VARCHAR(320)  NOT NULL DEFAULT '' COMMENT '用户邮箱',
  `mobile_number` VARCHAR(11)  NOT NULL DEFAULT '' COMMENT '用户手机号', 
  `token` VARCHAR(32)  NOT NULL DEFAULT '' COMMENT '用户token，用于保存用户登录信息', 
  `token_set_time` INT NOT NULL DEFAULT 0 COMMENT '设置用户token的时间，用于检查token是否过期', 
  `error_time` INT NOT NULL DEFAULT 0 COMMENT '用户第一次密码错误的登录时间',
  `error_count` TINYINT NOT NULL DEFAULT 0 COMMENT '用户密码错误的次数',
  `last_login_ip` INT NOT NULL DEFAULT 0 COMMENT '用户上次登录IP地址', 
  `last_login_time` INT NOT NULL DEFAULT 0 COMMENT '用户上次登录时间', 
  PRIMARY KEY (`user_id`)) ENGINE = InnoDB;

#课程表
CREATE TABLE `web_elective_db`.`course` 
( `course_id` INT NOT NULL AUTO_INCREMENT COMMENT '课程ID', 
  `course_code` VARCHAR(10) NOT NULL COMMENT '课程编号',  
  `course_name` VARCHAR(64) NOT NULL COMMENT '课程名称',  
  `course_status` TINYINT NOT NULL DEFAULT 1 COMMENT '课程状态(1：激活，2：冻结)', 
  `course_credit` TINYINT NOT NULL COMMENT '课程学分',
  `course_hour` TINYINT NOT NULL COMMENT '课程学时', 
  `course_capacity` INT NOT NULL COMMENT '课程容量',
  `course_student_num` INT NOT NULL DEFAULT 0 COMMENT '选课人数',
  `course_time` VARCHAR(64) NOT NULL COMMENT '授课时间', 
  `course_room` VARCHAR(64) NOT NULL COMMENT '授课地点', 
  `course_info` VARCHAR(512) NOT NULL DEFAULT '' COMMENT '课程描述', 
  PRIMARY KEY (`course_id`), 
  UNIQUE (`course_code`)) ENGINE = InnoDB;

#教师授课信息表
CREATE TABLE `web_elective_db`.`teacher_course` 
( `teacher_id` INT NOT NULL COMMENT '教师ID',
  `course_id` INT NOT NULL COMMENT '课程ID', 
  PRIMARY KEY (`teacher_id`, `course_id`)) ENGINE = InnoDB;


#学生选课信息表
CREATE TABLE `web_elective_db`.`student_course` 
( `student_id` INT NOT NULL COMMENT '学生ID',
  `course_id` INT NOT NULL COMMENT '课程ID', 
  PRIMARY KEY (`student_id`, `course_id`)) ENGINE = InnoDB;


INSERT INTO `web_elective_db`.`admin_user` (`user_id`, `user_name`, `password`, `status`, `real_name`, `gender`, `email`, `mobile_number`) 
VALUES (1, '0000000000', MD5(CONCAT(MD5('0'), 'web-2020')), 1, 'Y', 1, 'y@muggle.cn', '12345678910'),
       (2, '1111111111', MD5(CONCAT(MD5('PASS@11111'), 'web-2020')), 1, '邓布利多', 1, 'dumbledore@hogwarts.edu', '13141567890'),
       (3, '1011010001', MD5(CONCAT(MD5('PASS@10001'), 'web-2020')), 1, '麦格', 2, 'mcgonagall@hogwarts.edu', '19141667890'),
       (4, '1011010002', MD5(CONCAT(MD5('PASS@10002'), 'web-2020')), 1, '斯内普', 1, 'snape@hogwarts.edu', '12141667890'),
       (5, '1010010010', MD5(CONCAT(MD5('PASS@10010'), 'web-2020')), 1, '霍琦夫人', 2, 'hooch@hogwarts.edu', '16151667890'),
       (6, '1010010011', MD5(CONCAT(MD5('PASS@10011'), 'web-2020')), 1, '宾斯', 1, 'binns@hogwarts.edu', '15171667890'),
       (7, '1010010012', MD5(CONCAT(MD5('PASS@10012'), 'web-2020')), 1, '弗立维', 1, 'filtwick@hogwarts.edu', '14191667890'),
       (8, '1010010013', MD5(CONCAT(MD5('PASS@10013'), 'web-2020')), 1, '特里劳妮', 2, 'trelawney@hogwarts.edu', '18141667890'),
       (9, '1010010014', MD5(CONCAT(MD5('PASS@10014'), 'web-2020')), 1, '海格', 1, 'haiger@hogwarts.edu', '17141667890');

INSERT INTO `web_elective_db`.`admin_role` (`role_id`, `role_name`, `role_info`) 
VALUES (1, 'Admin', '系统管理员，拥有添加、删除、修改教务、教师、学生权限'),
       (2, 'Dean', '教务，可添加、修改课程信息，分配、取消学生选课，分配、取消教师授课'),
       (3, 'Teacher', '教师，可查看、修改本人开设课程、查看选课学生信息');

INSERT INTO `web_elective_db`.`admin_menu` (`menu_id`, `menu_name`, `parent_id`, `menu_icon`, `menu_info`, `controller`, `action`, `listorder`, `display`) 
VALUES (1, '系统首页', 0, 'xe696', '后台系统主页', 'Admin', 'index', 0, 1),
       (2, '系统设置', 0, 'xe69e', '修改个人信息、密码', '', '', 990, 1),
       (3, '修改个人信息', 2, '', '修改个人信息', 'Admin', 'editSelfInfo', 991, 1),
       (4, '修改登录密码', 2, '', '修改密码', 'Admin', 'changeSelfPassword', 992, 1),
       (5, '退出系统', 2, '', '退出系统', 'Login', 'logout', 994, 1),
       (6, '教务用户管理', 0, 'xe699', '增删改查教务用户', 'DeanUser', 'index', 1, 1),
       (7, '增加用户', 6, '', '增加教务用户', 'DeanUser', 'addUser', 1, 2), 
       (8, '查询用户', 6, '', '查询教务用户', 'DeanUser', 'searchUser', 1, 2), 
       (9, '修改用户', 6, '', '修改教务用户信息', 'DeanUser', 'editUserInfo', 1, 2), 
       (10, '冻结用户', 6, '', '冻结教务用户', 'DeanUser', 'blockUser', 1, 2),
       (11, '激活用户', 6, '', '激活教务用户', 'DeanUser', 'activateUser', 1, 2), 
       (12, '删除用户', 6, '', '删除教务用户', 'DeanUser', 'deleteUser', 1, 2), 
       (13, '重置用户密码', 6, '', '重置教务用户密码', 'DeanUser', 'resetUserPassword', 1, 2), 
       (14, '冻结用户token', 6, '', '重置教务用户token', 'DeanUser', 'resetUserToken', 1, 2), 
       (15, '教师用户管理', 0, 'xe699', '增删改查教师用户', 'TeacherUser', 'index', 2, 1),
       (16, '增加用户', 15, '', '增加教师用户', 'TeacherUser', 'addUser', 2, 2), 
       (17, '查询用户', 15, '', '查询教师用户', 'TeacherUser', 'searchUser', 2, 2), 
       (18, '修改用户', 15, '', '修改教师用户信息', 'TeacherUser', 'editUserInfo', 2, 2), 
       (19, '冻结用户', 15, '', '冻结教师用户', 'TeacherUser', 'blockUser', 2, 2),
       (20, '激活用户', 15, '', '激活教师用户', 'TeacherUser', 'activateUser', 2, 2), 
       (21, '删除用户', 15, '', '删除教师用户', 'TeacherUser', 'deleteUser', 2, 2), 
       (22, '重置用户密码', 15, '', '重置教师用户密码', 'TeacherUser', 'resetUserPassword', 2, 2), 
       (23, '冻结用户token', 15, '', '重置教师用户token', 'TeacherUser', 'resetUserToken', 2, 2),
       (24, '学生用户管理', 0, 'xe699', '增删改查学生用户', 'StudentUser', 'index', 3, 1),
       (25, '增加用户', 24, '', '增加学生用户', 'StudentUser', 'addUser', 3, 2), 
       (26, '查询用户', 24, '', '查询学生用户', 'StudentUser', 'searchUser', 3, 2), 
       (27, '修改用户', 24, '', '修改学生用户信息', 'StudentUser', 'editUserInfo', 3, 2), 
       (28, '冻结用户', 24, '', '冻结学生用户', 'StudentUser', 'blockUser', 3, 2),
       (29, '激活用户', 24, '', '激活学生用户', 'StudentUser', 'activateUser', 3, 2), 
       (30, '删除用户', 24, '', '删除学生用户', 'StudentUser', 'deleteUser', 3, 2), 
       (31, '重置用户密码', 24, '', '重置学生用户密码', 'StudentUser', 'resetUserPassword', 3, 2), 
       (32, '冻结用户token', 24, '', '重置学生用户token', 'StudentUser', 'resetUserToken', 3, 2),
       (33, '课程管理', 0, 'xe699', '增、删、改、查课程', 'Course', 'index', 4, 1),
       (34, '修改授课教师', 33, '', '修改授课教师', 'Course', 'changeCourseTeacher', 4, 2),
       (35, '删除课程', 33, '', '删除课程', 'Course', 'deleteCourse', 4, 2),
       (36, '冻结课程', 33, '', '冻结课程', 'Course', 'blockCourse', 4, 2),
       (37, '激活课程', 33, '', '激活课程', 'Course', 'activateCourse', 4, 2),
       (38, '修改课程', 33, '', '修改课程', 'Course', 'editCourseInfo', 4, 2),
       (39, '查看课程信息', 33, '', '查看课程信息', 'Course', 'showCourseInfo', 4, 2),
       (40, '查看课程选课学生', 33, '', '查看课程选课学生', 'Course', 'showCourseStudent', 4, 2),
       (41, '教师管理', 0, 'xe699', '管理教师授课', 'TeacherCourse', 'index', 5, 1),
       (42, '查看教师开设课程', 41, '', '查看教师开设课程', 'TeacherCourse', 'showTeacherCourse', 5, 2),
       (43, '添加教师开设课程', 41, '', '添加教师开设课程', 'TeacherCourse', 'addTeacherCourse', 5, 2),
       (44, '', 41, '', '', '', '', 5, 2),
       (45, '选课管理', 0, 'xe699', '管理学生选课操作', 'StudentCourse', 'index', 6, 1),
       (46, '查看学生选课结果', 45, '', '查看学生选课结果', 'StudentCourse', 'showStudentCourse', 6, 2),
       (47, '添加学生选课', 45, '', '添加学生选课', 'StudentCourse', 'addStudentCourse', 6, 2),
       (48, '取消学生选课', 45, '', '取消学生选课', 'StudentCourse', 'cancelStudentCourse', 6, 2),
       (49, '开课管理', 0, 'xe699', '查看本人开设课程', 'TeacherCourse', 'showSelfCourse', 7, 1),
       (50, '修改课程', 49, '', '修改本人开设课程信息', 'TeacherCourse', 'editSelfCourse', 7, 2),
       (51, '学生管理', 0, 'xe699', '查看所有选课学生', 'TeacherCourse', 'showSelfStudent', 8, 1),
       (52, '查看选课学生信息', 51, '', '查看选课学生信息', 'TeacherCourse', 'showSelfStudentInfo', 8, 2),
       (53, '重置免登录token', 2, '', '重置个人免登录token', 'Admin', 'resetSelfToken', 993, 1);


INSERT INTO `web_elective_db`.`user_role` (`user_id`, `role_id`)
VALUES (2, 1), (3, 2), (4, 2), (5, 3), (6, 3), (7, 3), (8, 3), (9, 3);

INSERT INTO `web_elective_db`.`role_menu` (`role_id`, `menu_id`)
VALUES (1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11), (1, 12), (1, 13),
       (1, 14), (1, 15), (1, 16), (1, 17), (1, 18), (1, 19), (1, 20), (1, 21), (1, 22), (1, 23), (1, 24), (1, 25), 
       (1, 26), (1, 27), (1, 28), (1, 29), (1, 30), (1, 31), (1, 32), (1, 53), 
       (2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 33), (2, 34), (2, 35), (2, 36), (2, 37), (2, 38), (2, 39), (2, 40),
       (2, 41), (2, 42), (2, 43), (2, 44), (2, 45), (2, 46), (2, 47), (2, 48), (2, 53),
       (3, 1), (3, 2), (3, 3), (3, 4), (3, 5), (3, 25), (3, 49), (3, 50), (3, 51), (3, 52), (3, 53); 

INSERT INTO `web_elective_db`.`student_user` (`user_id`, `user_name`, `password`, `status`, `real_name`, `gender`, `grade`, `email`, `mobile_number`)
VALUES (1, '2001210100', MD5(CONCAT(MD5('PASS@10100'), 'web-2020')), 1, '张一', 1, '本科一年级', '2001210100@ss.pku.edu.cn', '13131578966'),
(2, '2001210102', MD5(CONCAT(MD5('PASS@10102'), 'web-2020')), 1, '褚凤岐', 2, '本科一年级', '2001210102@ss.pku.edu.cn', '15894591510'),
(3, '2001210103', MD5(CONCAT(MD5('PASS@10103'), 'web-2020')), 1, '郑洪业', 1, '本科一年级', '2001210103@ss.pku.edu.cn', '13401526314'),
(4, '2001210104', MD5(CONCAT(MD5('PASS@10104'), 'web-2020')), 1, '韩偓', 1, '本科一年级', '2001210104@ss.pku.edu.cn', '15196701530');

INSERT INTO `web_elective_db`.`course` (`course_id`, `course_code`, `course_name`, `course_credit`, `course_hour`, `course_capacity`, `course_student_num`, `course_time`, `course_room`, `course_info`)
VALUES (1, '4853210254', '魔药学', 3, 48, 20, 1, '周三下午', '1号楼', '魔药学'),
       (2, '4853210268', '飞行', 3, 48, 20, 4, '周二下午', '1号楼', '飞行'),
       (3, '4853210275', '神奇动物', 3, 48, 20, 2, '周一上午', '1号楼', '神奇动物'),
       (4, '4853210292', '黑魔法', 5, 48, 20, 1, '周五下午', '5号楼', '黑魔法'),
       (5, '4853210375', '咒语学', 2, 48, 20, 1, '周四上午', '3号楼', '咒语学'); 

INSERT INTO `web_elective_db`.`teacher_course` (`teacher_id`, `course_id`)
VALUES (5, 1), (6, 2), (7, 3), (8, 4), (9, 5);

INSERT INTO `web_elective_db`.`student_course` (`student_id`, `course_id`)
VALUES (1, 1), (1, 2), (1, 3), (1, 4), (2, 2), (3, 2), (3, 3), (4, 2), (4, 5);