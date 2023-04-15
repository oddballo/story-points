<?php

require_once(__DIR__."/Lobby.class.php");
require_once(__DIR__."/RedisInstance.class.php");

class Room implements JsonSerializable {
    private $lobby;
    private $name;
    private $show;
    public final const MAX_VOTES = 10; 
    
    public function __construct($lobby, $name){
        $this->lobby = $lobby;
        $this->name = $name;
    }

    public function valid(){
        return !empty($this->name);
    }

    public function isShow(){
        $redis = RedisInstance::get();
        $value = $redis->get("room:".$this->getName().":show");
        return !empty($value);
    }

    public function create(&$errors=array()){
        if($this->exists()){
            return true;
        }

        if(!$this->valid()){
            $errors[] = "Invalid room name.";
            return false;
        }
                
        return $this->lobby->addRoom($this, $errors);
    }

    public function show(&$errors = array()){
        if(!$this->exists()){
            $errors[] = "Room does not exist.";
            return false;
        }
        $redis = RedisInstance::get();
        $redis->set("room:".$this->getName().":show", 1);
    }
    
    private function unshow(){
        $redis = RedisInstance::get();
        $redis->del("room:".$this->getName().":show");
    }

    public function destroy(){
        if($this->exists()){
            $this->lobby->removeRoom($this);
            $this->clear();
        }
        return true;
    }
    
    public function exists(){
        return $this->lobby->hasRoom($this); 
    }

    public function vote($user, $vote, &$errors = array()){
        if(!$this->exists()){
            $errors[] = "Room does not exist.";
            return false;
        }

        if($this->countVoters() >= Room::MAX_VOTES){
            $errors[] = "Too many voters. Refusing to vote.";
            return false;
        }   
       
        $this->setVote($user->getId(), $user->getName(), $vote);
        
        return true;
    }

    public function countVoters(){
        $redis = RedisInstance::get();
        return $redis->scard("room:".$this->getName().":voters");
    }
   
    private function deleteVoters(){
        $redis = RedisInstance::get();
        $redis->del("room:".$this->getName().":voters");
    }
 
    private function getVoters(){
        $redis = RedisInstance::get();
        return $redis->smembers("room:".$this->getName().":voters");
    }

    private function getVote($id){
        $redis = RedisInstance::get();
        $data = $redis->get("room:".$this->getName().":voter:".$id);
        return $data === false ? $data : json_decode($data, true);
    }

    private function setVote($id, $name, $vote){
        $redis = RedisInstance::get();
        $redis->sadd("room:".$this->getName().":voters", $id);
        return $redis->set("room:".$this->getName().":voter:".$id, 
            json_encode(array("name"=>$name, "vote"=>$vote))
        );
    }
    private function deleteVote($id){
        $redis = RedisInstance::get();
        return $redis->del("room:".$this->getName().":voter:".$id);
    }

    public function getName(){
        return $this->name;
    }

    public function clear(){
        $this->unshow();
        $voters = $this->getVoters();
        $this->deleteVoters();
        foreach($voters as $voter){
            $this->deleteVote($voter);
        }

        return true;
    }

    public function getVotes(){
        $data = array();
        $ids = $this->getVoters();
        foreach($ids as $id){
            $vote = $this->getVote($id);
            if($vote !== false){
                $data[] = $vote;
            }
        }
        return $data;
    }

    public function jsonSerialize():mixed{
        $votes = $this->getVotes();
        if($this->isShow() == false){
            $keys = array_keys($votes);
            foreach($keys as $key){
                $votes[$key]["vote"] = "Voted";
            }
        }
        return array(
            "name"=>$this->getName(), 
            "show"=>$this->isShow(), 
            "exists"=>$this->exists(), 
            "votes"=>$votes
        );
    }

}

