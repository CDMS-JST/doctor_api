<?php
ini_set('display_errors',1);

require_once __DIR__ . '/../libs/crypt/Crypt.php';
require_once __DIR__ . '/../libs/communicateDB/CommunicateDB.php';

$str = $_POST['jsonobject'];

$json = json_decode($str,true);
$c = new Crypt($json['user_id']);

$id = $c->getID();

$ins = new Patient_DB();

$dup = $ins->checkDuplicate($id);
if($dup['count']>0){
    header( "HTTP/1.1 400 Bad Request" ) ;
    exit;
}

$err = $ins->insertUser($id,$json['user_name'],$json['user_address'],$json['user_tel'],$json['user_birth'],$json['user_sex'],$json['user_mynumber'],$json['user_postal'],$json['user_lat'],$json['user_lng']);


// ログ設定　（Added by Takasaki）
$logfile = "./logs/logs.txt";
$logfile_json = "./logs/jsons.txt";
$logdata = date("Y-m-d H:i:s");
$logdata .= sprintf(",個人情報登録,%s",$id);
$logdata_json = sprintf("%s ----- START -----\n%s\n----- END -----\n", $logdata, var_export($json,true));

if(isset($json['user_name'])){
    $logdata .= sprintf(",(user_name)%s",$json['user_name']);
} else {
    $logdata .= ",user_name未受信";
}

if(isset($json['user_address'])){
    $logdata .= sprintf(",(user_address)%s",$json['user_address']);
} else {
    $logdata .= ",user_address未受信";
}

if(isset($json['user_tel'])){
    $logdata .= sprintf(",(user_tel)%s",$json['user_tel']);
} else {
    $logdata .= ",user_tel未受信";
}

if(isset($json['user_birth'])){
    $logdata .= sprintf(",(user_birth)%s",$json['user_birth']);
} else {
    $logdata .= ",user_birth未受信";
}

if(isset($json['user_sex'])){
    $logdata .= sprintf(",(user_sex)%s",$json['user_sex']);
} else {
    $logdata .= ",user_sex未受信";
}

if(isset($json['user_mynumber'])){
    $logdata .= sprintf(",(user_mynumber)%s",$json['user_mynumber']);
} else {
    $logdata .= ",user_mynumber未受信";
}

if(isset($json['user_postal'])){
    $logdata .= sprintf(",(user_postal)%s",$json['user_postal']);
} else {
    $logdata .= ",user_postal未受信";
}

if(isset($json['user_lat'])){
    $logdata .= sprintf(",(user_lat)%s",$json['user_lat']);
} else {
    $logdata .= ",user_lat未受信";
}

if(isset($json['user_lng'])){
    $logdata .= sprintf(",(user_lng)%s",$json['user_lng']);
} else {
    $logdata .= ",user_lng未受信";
}

$logdata .= sprintf("SQL_RESULT=%s\n", $err);


file_put_contents($logfile, $logdata, FILE_APPEND | LOCK_EX);
file_put_contents($logfile_json, $logdata_json, FILE_APPEND | LOCK_EX);

?>