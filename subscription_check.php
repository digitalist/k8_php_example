<?php

/** 
*
* пользователю с текстом "{username}, your subscription is expiring soon".
*/

require_once ("db.php");
require_once ("lock.php");




/**
* Email sender dummy
*/
function send_email($email, $from, $to, $subj, $body) {
    sleep (rand(1, 10));
    //error log goes here
}

function process_data($data){
    foreach ($data as $v) {
        send_email(
            $v['email'], 
            "dummy@example.com",  
            $v['username'], 
            "Subscription is expiring", 
            $v['username'].", your subscription is expiring soon"
        );
    }
}


/**
* Gets expiring subscriptions
* @param object  $dbc PDO connection
* @param int $bucket bucket for multiprocessing by oid
*/
function get_expiring_subscriptions($dbc, $bucket, $offset, $limit){
    $query = "select e.email 
    from  emails e 
    where 
    e.checked = 0 and e.valid=1 and e.oid % 16 = ".$bucket .
    " and  validts <= NOW() + INTERVAL 3 DAY
      and validts >= NOW() /* ignore invalid subscriptions */
    order by u.oid ".
    " limit ". $limit .
    " offset ". $offset;
    $q = $dbc->prepare($query);
    $q->execute();
    return  $q->fetchAll();
}

function main(){
    $args = $_SERVER['argv'];
    if (count($args) < 2 ){
        echo "Run with a bucket number: subscription_check.php 1\n";
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
            $data = get_expiring_subscriptions($dbc, $bucket,  $offset, $limit);
            // offsets and limits math
            $offset = $limit*$i;
            $i++;
            $result_size = count($data);

            process_data($data);
        } while( $result_size && $result_size >= $limit ); // omit extra query
        script_unlock($lock_name);
    } catch (Exception $e){
        //log goes here
        var_dump($e);
        script_unlock($lock_name);
        exit();
    }
}

main();