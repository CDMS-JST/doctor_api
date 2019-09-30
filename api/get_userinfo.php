<?php
// ini_set("display_errors",1);
require_once __DIR__ . '/../libs/crypt/Crypt.php';
require_once __DIR__ . '/../libs/communicateDB/CommunicateDB.php';

$id = $_POST['user_id'];


$c = new Crypt($id);
$id = $c->getID();


$ins = new Patient_DB();

$data = $ins->getUserData($id);

$json = '';
if(array_key_exists('callback', $_GET)){
    $json = $_GET['callback'] . "(" . json_encode($data) . ");";
}else{
    $json = json_encode($data);
}
// ログ

//file_put_contents("get_userinfo_log.txt",var_export($json,true));

header('Content-Type: text/html; charset=utf-8');
echo  $json;

// $str = print_r($data,true);

?>