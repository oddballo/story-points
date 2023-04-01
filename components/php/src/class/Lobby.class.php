<?php

require_once(__DIR__."/Room.class.php");

class Lobby implements JsonSerializable {
    
    private $folder;   
    public final const MAX_ROOMS = 10; 

    public function __construct($folder){
        $this->folder = $folder;
    }

    public function getRooms(){
        $rooms = array();
        if(is_dir($this->getFolder())){
            $files = scandir($this->getFolder());
            foreach($files as $file){
                if(strcmp($file, ".") != 0 &&
                    strcmp($file, "..") != 0 &&
                    is_dir($this->getFolder()."/".$file)){
                    $rooms[] = new Room($this, $file);
                }
            }
        }
        return $rooms;
    }

    public function getFolder(){
        return $this->folder;
    }

    public function jsonSerialize():mixed{

        $rooms = $this->getRooms();
        $labels = array();
        foreach($rooms as $room){
            $labels[] = $room->getName();
        }
        return array("rooms"=>$labels);
    }

}
