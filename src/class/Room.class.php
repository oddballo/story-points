<?php

require_once(__DIR__."/Lobby.class.php");

class Room implements JsonSerializable {
    private $lobby;
    private $name;
    private $show;
    public final const FILE_MARKER = "show";
    public final const MAX_VOTES = 10;
    
    
    public function __construct($lobby, $name){
        $this->lobby = $lobby;
        $this->name = $name; 
    }

    public function valid(){
        return !empty($this->name);
    }

    public function create(&$errors=array()){
        if($this->exists()){
            return true;
        }
        if(count($this->lobby->getRooms()) >= Lobby::MAX_ROOMS+2){
            $errors[] = "Too many rooms. Refusing to create.";
            return false;
        }
        if(!$this->valid()){
            $errors[] = "Invalid room name.";
            return false;
        }
        mkdir($this->getFolder());
        
        return true;
    }

    public function isShow(){
        if(!$this->exists()){
            return false;
        }
        return is_file($this->getShowFile());
    }

    private function getShowFile(){
        return $this->getFolder()."/".Room::FILE_MARKER;
    }

    public function show(&$errors = array()){
        if(!$this->exists()){
            $errors[] = "Room does not exist.";
            return false;
        }
        touch($this->getShowFile());
    }

    public function destroy(){
        if($this->exists()){
            $this->clear();
            rmdir($this->getFolder());
        }
        return true;
    }
    
    public function exists(){
        return $this->valid() && is_dir($this->getFolder());
    }

    public function vote($user, $vote, &$errors = array()){
        if(!$this->exists()){
            $errors[] = "Room does not exist.";
            return false;
        }

        $folder = $this->getFolder();
        
        # Check to see if we have too many voters
        $files = scandir($folder);
        if(count($files) >= Room::MAX_VOTES+2){
            $errors[] = "Too many voters. Refusing to vote.";
            return false;
        }   
        
        # Create vote file
        file_put_contents(
            "$folder/".$user->getId().".json", 
            json_encode(
                array(
                    "name"=>$user->getName(),
                    "vote"=>$vote
                )
            )
        );
        
        # Add flag for clients to pull data again
        #file_put_contents("$folder/".Room::FILE_MARKER, time()); 

        return true;
    }

    public function getName(){
        return $this->name;
    }

    public function clear(){
        if(!$this->exists()){
            return true;
        }
    
        $folder = $this->getFolder();
        $filenames = scandir($folder);
        foreach($filenames as $filename){
            $file = "$folder/$filename";
            if ( is_file ($file)){
                unlink($file);
            }
        }
        return true;
    }

    public function getVotes(){
        $data = array();
        if($this->exists()){
            $folder = $this->getFolder();
            $filenames = scandir($folder);
            foreach ($filenames as $filename){
                $file = "$folder/$filename";
                if(is_file($file) && strcmp($filename, Room::FILE_MARKER) != 0){
                    $data[] = json_decode(file_get_contents($file));
                }
            }
        }
        return $data;
    }

    public function getFolder(){
        return $this->lobby->getFolder()."/".$this->name;
    }
    
    public function jsonSerialize():mixed{
        $votes = $this->getVotes();
        if($this->isShow() == false){
            foreach($votes as $vote){
                $vote->vote = "Voted";
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

