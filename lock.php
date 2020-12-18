<?php
// Typed exceptions are good
class ScriptLockException extends Exception{};
function script_lock($lock_name){
    if (file_exists($lock_name)){
        throw new ScriptLockException("Ohno! Locked! ".$lock_name);
    }
    touch($lock_name);
}

function script_unlock($lock_name){
    unlink($lock_name);
}

function get_lock_name($filename, $bucket){
    return '/tmp/'.basename($filename)."_".$bucket.'.lock';
}
