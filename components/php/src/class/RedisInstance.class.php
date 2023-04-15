<?php

class RedisInstance {
    private static $instance = null;
    
    private function __construct(){
        $instance = new Redis();
    }

    public static function get(){
        if(empty(self::$instance)){
            self::$instance = new Redis();
            self::$instance->connect("redis", 6379);
        }
        return self::$instance;
    }
}
