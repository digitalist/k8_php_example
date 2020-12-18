<?php

/** 
* Email checker script dummy
*/

require_once ("db.php");
require_once ("lock.php");



/**
* Email checker dummy
* @param string  $email
* @param bool $isDummy if true: returns random validness
*/
function check_email($email, $isDummy=true) {
    if ($isDummy){
        $dryRunSeconds = rand(10, 60);
        //sleep($dryRunSeconds);
        return $dryRunSeconds % 2;
    }

    return 0;
}


function get_oid_status($data){
    $oids = array(
        'good'=>array(),
        'bad'=>array()
    );
    foreach ($data as &$v) {
         if (check_email($v['email'])){
             $oids['good'][] = $v['oid'];
         }else{
            $oids['bad'][] = $v['oid'];
         }
    }
    return $oids;
}


/**
* Gets expiring subscriptions
* @param object  $dbc PDO connection
* @param int $bucket bucket for multiprocessing by oid
*/
function get_uncheked_emails($dbc, $bucket, $offset, $limit){
    $query = "select e.oid, e.email 
    from emails e 
    where 
    e.checked = 0 and e.oid % 16 = ".$bucket .
    " order by e.oid ".
    " limit ". $limit .
    " offset ". $offset;
    $q = $dbc->prepare($query);
    $q->execute();
    return  $q->fetchAll();
}



function update_checked($dbc, $oids, $valid=''){
    if ($valid) {$valid = ", valid=1";}
    
    if (count($oids)){
        $oids = implode(",", $oids);
        $q = $dbc->prepare("update k8.emails set checked=1 $valid where oid in (".$oids.")");
        $q->execute();
    }

}

function main(){
    $args = $_SERVER['argv'];
    if (count($args) < 2 ){
        echo "Run with a bucket number: email_check.php 1\n";
        exit();
    }
    $bucket = $args[1];
    $limit = 5000;
    $offset = 0;
    $i = 1;
    $lock_name = get_lock_name(__FILE__, $bucket);
    try {
        script_lock($lock_name);
        $dbc = get_db_connection();
        
        do {
            $data = get_uncheked_emails($dbc, $bucket,  $offset, $limit);
            // offsets and limits math
            $offset = $limit*$i;
            $i++;
            $result_size = count($data);
            $oids = get_oid_status($data);
            //Log valid/invalid emails 
            var_dump($data);
            
            update_checked($dbc, $oids['good'], true);
            update_checked($dbc, $oids['bad'], false);
            
        } while( $result_size && $result_size >= $limit ); // omit extra query
        //script_unlock($lock_name);
    } catch (Exception $e){
        //error log goes here
        script_unlock($lock_name);
        exit();
    }
}

main();