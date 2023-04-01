<?php

function tellTheWorld($topic){
    $url = 'http://longpolling:8081/publish';
    // use key 'http' even if you send the request to https://...
    $data = array("topic"=>$topic);
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) { 
        error_log("Couldn't communicate with the long poller", 0);
    }
}

function valuesAvailable($array, $array_keys, &$response){
    foreach($array_keys as $key){
        if(!isset($array[$key])
            || strlen($array[$key]) == 0){
            $response["errors"][] = "Missing key value \"$key\"";
            $response["success"] = false;
            return false;
        }
    }
    return true;
}

function sanitizeAction($action){
    return sanitizeSimpleString($action);
}

function sanitizeName($name){
    return sanitizeSimpleString($name);
}

function sanitizeVote($vote){
    return max(0, min(100, intval($vote)));
}

function sanitizeRoom($room){
    return sanitizeSimpleString($room);
}

function sanitizeSimpleString($string){
    if(empty($string)){
        return "";
    }
    $string = substr($string,0,40);
    $string = preg_replace("/[^A-Za-z0-9 ]/", '', $string);
    return $string;
}

