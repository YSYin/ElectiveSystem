<?php

/**
* 初始化redis
* @file      InitRedis.php
* @date      2020/1/3
* @author    YSY
* @version   1.0
*/
    /**
     * 初始化redis
     */
    function initRedis() {

        echo "Redis 初始化数据库开始\n";
        $redis = new \Redis();
        echo "连接Redis服务器，清空数据库\n";
        $redis->connect('127.0.0.1', 6379);
        $redis->flushDB();
        echo "连接MYSQL服务器，读取课程信息\n";
        $conn = mysqli_connect('localhost', 'web_admin', 'WEB_ADMIN2020', 'web_elective_db');
        if (!$conn) 
        {
            die('数据库连接失败'.mysqli_connect_error()); 
        }
        $sql = "SELECT `course_id`, `course_name`, `course_capacity`, `course_student_num` FROM course WHERE course_status = 1";
        $result = mysqli_query($conn, $sql);
        $courses = array();
        while($row = mysqli_fetch_assoc($result))
        {
            $courses[] = $row;
        }
        mysqli_free_result($result);
        mysqli_close($conn);
        echo "开始写入课程信息\n";
        foreach ($courses as $key => $course) {
            $listKey = 'course_'.(string)$course['course_id'];
            $num = $course['course_capacity'] - $course['course_student_num'];
            for($i = 1; $i <= $num; $i++) {
              $redis->rPush($listKey,$i);
            }
            echo '课程《'.$course['course_name']."》, 在Redis中的KEY为: ".$listKey.', 限选人数为: '.(string)$redis->lLen($listKey)."\n";
        }
        echo "Redis 初始化数据库结束\n";
    }
    initRedis();