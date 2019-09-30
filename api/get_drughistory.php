<?php

ini_set('display_errors',1);

require_once __DIR__ . '/../libs/crypt/Crypt.php';
require_once __DIR__ . '/../libs/communicateDB/CommunicateDB.php';
require_once __DIR__ . '/../libs/sagamed/fields.php';

// ログ設定
$logfile = "./logs/logs.txt";
$logdata = date("Y-m-d H:i:s");


$refer = new Prescription_asis_DB();

$user_id = $_GET['user_id'];

//$user_id = "81f6f2ed";
$c = new Crypt($user_id);
$ID = $c->getID();

$logdata .= sprintf(",薬剤履歴参照, user_id=%s",$ID);
file_put_contents($logfile, $logdata, FILE_APPEND | LOCK_EX);

$jsonobject = $refer->get_drughistory($ID);

http_response_code(200);    //HTTPレスポンスコード(200正常終了)
header('Content-Type: application/json; charset=UTF-8');
header("X-Content-Type-Options: nosniff");

echo json_encode($jsonobject, JSON_UNESCAPED_UNICODE);    //エンコードして送信

exit();
