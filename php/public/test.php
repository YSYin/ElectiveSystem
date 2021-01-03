<?php
$redis = new Redis();  
$redis->connect('localhost', 6379);//serverip port
$redis->auth('');//my redis password 
$redis ->set( "test" , "Hello World");  
echo $redis ->get( "test");
