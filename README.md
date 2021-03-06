# ElectiveSystem

## 一、项目概述

本项目为2020年《互联网软件开发技术与实践》课程项目，主要基于本学期课程内容，实现一个学生选课的秒杀系统。

本项目基于现实高校选课系统简化而来，将学生选课方式设定为先到先得的秒杀方式，同时为系统管理员、教务和教师用户提供安全便捷的选课管理服务

### 1.用户角色

在本系统中，所有用户分为4种角色：

- 学生
  - 学生用户在规定时间范围内可以进行选课操作。系统根据学生选课请求的先后顺序以及课程容量确定学生是否可以选到相应课程。
- 教师
  - 教师用户可以进行查看本人开设课程、修改本人课程基本信息、查看选修本人课程的学生信息操作。
- 教务
  - 教务用户可以进行：
    - 课程管理，包括查看课程信息及选课学生、修改课程信息及授课教师、冻结课程、激活课程、删除课程操作
    - 教师管理，包括查看教师信息及开设课程、新增教师授课课程操作
    - 选课管理，包括查看学生信息及选课结果、分配学生未选课程、取消学生已选课程操作
- 管理员
  - 管理员用户可以进行：
    - 用户管理：对全部教务用户、教师用户、学生用户进行查看、修改、增加、删除、冻结、激活、重置密码、重置免登录token操作
    - 选课时间设置：设置学生选课的开始时间和结束时间
- 所有后台用户均具有查看修改个人信息、修改登录密码、7天免密登录、重置免登录token功能

### 2. 系统架构

本系统由前台秒杀模块和后台管理模块组成：

- 前台秒杀模块
  - 功能概述：前台秒杀模块为学生用户提供服务。学生用户可在任意时间登录前台，登录后可以查看全部可选课程的基本信息和课程容量；在规定时间到达后，学生可以选择若干课程并提交选课信息，系统根据请求的先后顺序决定学生是否可以选到该门课程；提交选课请求后，学生可以查看选课结果
  - 实现技术：前台主要通过Thinkphp框架和X-admin框架提供选课操作页面，通过Redis数据库提供缓存机制，通过rabbitMQ提供消息队列服务，采用golang语言实现选课请求处理、数据库访问和选课结果写入
- 后台管理模块
  - 功能描述：后台管理模块为教师、教务、管理员提供服务。系统根据用户的权限提供相应的操作页面和操作接口
  - 实现技术：主要通过Thinkphp框架实现处理逻辑，通过X-admin框架实现前端页面

## 二、文件结构

本系统全部文件存放在ElectiveSystem文件夹中，各文件的架构与功能如下所示

```markdown
ElectiveSystem 总目录
├─php                      ThinkPHP框架，实现后台管理模块的全部功能和前台秒杀模块的部分功能
│  ├─application        应用目录
│  │  ├─admin                后台管理模块
│  │  │  ├─controller         后台管理模块的各个控制器
│  │  │  ├─model                后台用户表和菜单权限表的模型
│  │  │  ├─view                   后台管理模块的视图，即每个方法对应的模板文件
│  │  │  ├─validate             验证器，验证数据输入的格式
│  │  │  ├─widget               左侧菜单，确定用户能访问的菜单以及菜单是否展开
│  │  │  └─config.php       后台管理模块的配置文件，主要配置模板布局和cookie路径
│  │  ├─student               前台秒杀模块
│  │  │  ├─controller          前台秒杀模块的各个控制器
│  │  │  ├─model                 学生用户表的模型
│  │  │  ├─view                    前台秒杀模块的模板文件
│  │  │  └─config.php        前台秒杀模块的配置文件
│  │  └─ ...                             应用目录的其他文件      
│  ├─public                 WEB 部署目录（对外访问目录）
│  │  ├─phpMyAdmin     phpMyAdmin，访问数据库
│  │  ├─static                    静态资源存放目录(css,js,image)
│  │  └─ ...                              对外访问目录的其他文件
│  └─...                     ThinkPHP框架的其他内容
├─go                     golang文件，接收、处理选课请求，将结果写回
│  ├─send.go           从PHP服务器接收选课请求，进行封装后转发到消息队列
│  └─recv.go            从消息队列中接收请求，处理请求并将结果写回数据库
├─sql                    SQL文件，创建数据库和用户，写入初始数据
│  └─create_database.sql    创建数据库和用户，写入初始数据
├─LICENSE        授权说明文件
├─README.md项目说明文件
```

## 三、部署说明

### 1. 环境依赖

- Apache：**`Apache / 2.4.6 (CentOS)`**
- MySQL：**`MySQL / 5.7.32`**
- PHP：**`PHP / 7.2.34`**
- RabbitMQ：**`RabbitMQ / 3.8.5`**
- Redis：**`Redis / 6.0.1`**
- PHPRedis：**`PHPRedis / 5.3.2`**

### 2. 部署说明

1. 安装配置以上环境依赖，并开启所有服务和相关端口
2. 添加MySQL用户`web_admin:WEB_ADMIN-2020`、添加RabbitMQ用户`web_admin:WEB_ADMIN-2020`
3. 下载项目文件到本地
4. 设置Web根目录为`ElectiveSystem/php/public`
5. 选课开始前，在`ElectiveSystem/php`下通过`php`命令运行`InitRedis.php`文件
6. 选课开始前，编译并运行`ElectiveSystem/go`下的`recvFromPHP.go`和`processElection.go`文件
7. 通过http://yourhostname/index.php/admin/login/index 即可登录管理后台
8. 通过http://yourhostname/index.php/student/login/index 即可登录选课前台

## 四、协议

本项目采用MIT协议

- 被授权人有权利使用、复制、修改、合并、出版发行、散布、再授权及贩售软体及软体的副本。

- 被授权人可根据程式的需要修改授权条款为适当的内容。

- 在软件和软件的所有副本中都必须包含版权声明和许可声明。