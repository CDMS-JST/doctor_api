<?php
require_once __DIR__ . '/../libs/communicateDB/CommunicateDB.php';
require_once __DIR__ . '/../libs/crypt/Crypt.php';

$json = $_POST["jsonobject"];


$json = json_decode($json,true);
 file_put_contents("update_userinfo_log.txt",var_export($json,true));

$c  = new Crypt($json['user_id']);
$id = $c->getID();
//file_put_contents("log.txt",$id);

// ログ設定
$logfile = "./logs/logs.txt";
$logdata = date("Y-m-d H:i:s");
$logdata .= sprintf(",個人情報変更,%s",$id);

if (isset($json['user_address'])) {
    $logdata .= sprintf(",(user_address)%s", $json['user_address']);
} else {
    $logdata .= ",user_address未受信";
}

if (isset($json['user_tel'])) {
    $logdata .= sprintf(",(user_tel)%s", $json['user_tel']);
} else {
    $logdata .= ",user_tel未受信";
}

if (isset($json['user_postal'])) {
    $logdata .= sprintf(",(user_postal)%s", $json['user_postal']);
} else {
    $logdata .= ",user_postal未受信";
}

if (isset($json['user_lat'])) {
    $logdata .= sprintf(",(user_lat)%s", $json['user_lat']);
} else {
    $logdata .= ",user_lat未受信";
}

if(isset($json['user_lng'])){
    $logdata .= sprintf(",(user_lng)%s",$json['user_lng']);
} else {
    $logdata .= ",user_lng未受信";
}


$ins = new Patient_DB();

$err = $ins->updateUser($id,$json['user_address'],$json['user_tel'],$json['user_postal'],$json['user_lat'],$json['user_lng']);
//file_put_contents("log.txt",$err);

$logdata .= sprintf("SQL_RESULT=%s\n", $err);
file_put_contents($logfile, $logdata, FILE_APPEND | LOCK_EX);

?>