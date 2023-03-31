<?php

require_once(__DIR__."/../functions.php");
require_once(__DIR__."/../class/Lobby.class.php");
require_once(__DIR__."/../class/Room.class.php");
require_once(__DIR__."/../class/User.class.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

$folder_data = __DIR__."/../data";
if ( ! is_dir ($folder_data) ){
    mkdir($folder_data);    
}

#
#   Lobby
#
$lobby = new Lobby($folder_data);

#
#   Room 
#
$room = null;
if(isset($_REQUEST['room'])){
    $room = new Room($lobby, sanitizeRoom($_REQUEST['room']));
}

#
#   User   
#
if(!isset($_SESSION['user'])){
    $_SESSION['user'] = new User();
}
$user = $_SESSION['user'];

function isRoom($room, &$errors = array()){
    if(empty($room)){
        $errors[] = "Missing room information";
        return false;
    }
    return true;
}

#
#   Router
#
$response = array("errors"=>array(), "success"=>true, "data"=>null);
if(valuesAvailable($_POST, array("action"), $response)){
    $action = sanitizeAction($_POST['action']);
    switch($action){
        case "createRoom":
            if(!isRoom($room, $response["errors"])){
                break;
            }
            $response["success"] = $room->create($response["errors"]);
            break;
        case "destroyRoom":
            if(!isRoom($room, $response["errors"])){
                break;
            }
            $room->destroy();
            break;
        case "voteRoom":
            if(!isRoom($room, $response["errors"])){
                break;
            }
            if(!valuesAvailable($_POST, array("vote"), $response)){
                break;
            }
            $vote = sanitizeVote($_POST['vote']);
            $response["success"] = $room->vote($user, $vote, $response["errors"]);
            break;
        case "showRoom":
            if(!isRoom($room, $response["errors"])){
                break;
            }
            $room->show();
            break;
        case "clearRoom":
            if(!isRoom($room, $response["errors"])){
                break;
            }
            $room->clear();
            break;
        case "dataRoom":
            if(!isRoom($room, $response["errors"])){
                break;
            }
            $response["data"] = $room;
            break;
        case "nameUser":
            if(!valuesAvailable($_POST, array("name"), $response)){
                break;
            }
            $name = sanitizeName($_POST['name']);
            $user->setName($name);
            break;
        case "dataUser":
            $response["data"] = $user;
            break;
        case "dataLobby":
            $response["data"] = $lobby;
            break;
        default:
            $response["success"] = false;
            $response["errors"][] = "Unknown action \"$action\"";
    }
}
echo json_encode($response);

