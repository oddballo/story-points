<?php

require_once(__DIR__."/Room.class.php");
require_once(__DIR__."/RedisInstance.class.php");

class Lobby implements JsonSerializable {
    
    private $name;   
    public final const MAX_ROOMS = 10; 

    public function __construct($name){
        $this->name = $name;

    }

    public function getRooms(){
        $redis = RedisInstance::get();
        $data = $redis->smembers("lobby:".$this->getName());
        sort($data);
        return $data;
    }

    public function hasRoom($room){
        $redis = RedisInstance::get();
        return $redis->sismember("lobby:".$this->getName(), $room->getName()); 
    }

    public function addRoom($room, &$errors=array()){
        if($this->countRooms() > self::MAX_ROOMS){
            $errors[] = "Too many rooms. Refusing to create.";
            return false;
        }
        $redis = RedisInstance::get();
        $redis->sadd("lobby:".$this->getName(), $room->getName()); 
    }

    public function removeRoom($room){
        $redis = RedisInstance::get();
        $redis->srem("lobby:".$this->getName(), $room->getName());
    }

    private function countRooms(){
        $redis = RedisInstance::get();
        return $redis->scard("lobby:".$this->getName());
    }

    public function getName(){
        return $this->name;
    }

    public function jsonSerialize():mixed{
        return array("rooms"=>$this->getRooms());
    }

}
