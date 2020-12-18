<?php
/*
* fill users and emails  tables with random dates data 
*/

$max_users = 1000000;
$batch_size = 10000;

require_once("db.php");

function random_expire_date(){
    $start = new DateTimeImmutable();
    $sign = "-";
    if (rand(1,2)%2){
        $sign = "+";
    }
    $days = rand(0,4);
    $datetime = $start->modify($sign.$days .' day');
    return $datetime->format('Y-m-d H:i:s');
}


$db =  get_db_connection();


// we don't use foreign keys here, pay attention:
$values_users = [];
$values_emails = [];

for ($i = 1; $i <=$max_users; $i++) {
    $username = "test_user".$i;
    $email = $username."@example.com";
    $values_users[] = [$i, $username, $email, random_expire_date(), rand(0,1)];
    $values_emails[] = [$i, $email, rand(0,1), rand(0,1)];
    
    if ($i % $batch_size == 0) {
        batch_insert($db, "`k8`.`users`", "oid, username, email, validts, confirmed", $values_users);
        batch_insert($db, "`k8`.`emails`", "oid, email, checked, valid", $values_emails);
        $values_users = $values_emails = [];
    }
}
// things got ugly, push the rest of our data
if ($i == $max_users && $i % $batch_size){
    batch_insert($db, "`k8`.`users`", "oid, username, email, validts, confirmed", $values_users);
    batch_insert($db, "`k8`.`emails`", "oid, email, checked, valid", $values_emails);
}