<?php
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

