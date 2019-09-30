<?php
require_once "../libs/crypt/Crypt.php";
require_once 'kajihara/dbinfo.php';

// ini_set("display_errors",1);
$str = $_POST['jsonobject'];
// file_put_contents("log.txt",$str);
// return;
$json = json_decode($str,true);

// $user_id = filter_input(INPUT_POST, "user_id");
// $user_lng = filter_input(INPUT_POST, "user_lng");
// $user_lat = filter_input(INPUT_POST, "user_lat");
// $drug_possesion = filter_input(INPUT_POST, "drug_possesion");

$user_id = $json["user_id"];
$user_lng = $json["user_lng"];
$user_lat = $json["user_lat"];
$drug_possesion = $json["drug_possesion"];

// file_put_contents("log.txt",$user_id);

date_default_timezone_set('Asia/Tokyo');
$time = date_create()->format('Y-m-d H:i:s');
$c = new Crypt($user_id);
$id = $c->getID();

// ログ設定 (Add by Takasaki)
$logfile = "./logs/logs.txt";
$logdata = date("Y-m-d H:i:s");
$logdata .= sprintf(",災害時安否確認,%s",$id);

if(isset($json["user_lat"])){
    $logdata .= sprintf(",(user_lat)%s",$json["user_lat"]);
} else {
    $logdata .= ",user_lat未受信";
}

if(isset($json["user_lng"])){
    $logdata .= sprintf(",(user_lng)%s",$json["user_lng"]);
} else {
    $logdata .= ",user_lng未受信";
}

if(isset($json["drug_possesion"])){
    $logdata .= sprintf(",(drug_possesion)%s",$json["drug_possesion"]);
} else {
    $logdata .= ",drug_possesion未受信";
}

$logdata .= "\n";

file_put_contents($logfile, $logdata, FILE_APPEND | LOCK_EX);

try {
    $sst = 'UPDATE user_info SET user_lat=:user_lat,user_lng=:user_lng,latest_time=:latest_time WHERE user_id=:user_id';
    $sst1 = $db->prepare($sst);
//    $sst1->bindValue(':user_id', $c->getID());
    $sst1->bindValue(':user_id', $id);
    $sst1->bindValue(':user_lat', $user_lat);
    $sst1->bindValue(':user_lng', $user_lng);
    $sst1->bindValue(':latest_time', $time);
    $result = $sst1->execute();
    if ($result != 1) {
        // $res_arrays["errormessage"] = getErrorMessage(-2);
        // echo json_encode($res_arrays);
        // die();
        echo false+":";
    }
    // file_put_contents("log.txt",$time);
    if($drug_possesion == 0){
        $sst = 'UPDATE user_drug SET drug_num=:num,day_left=:num WHERE user_id=:user_id';
        $sst1 = $db->prepare($sst);
        $sst1->bindValue(':user_id', $id);
        $sst1->bindValue(':num', 0);
        $result = $sst1->execute();
        if ($result != 1) {
            // $res_arrays["errormessage"] = getErrorMessage(-2);
            // echo json_encode($res_arrays);
            // die();
            echo false;
        }
    }

    // $res_arrays["data"] = "true";
    // $res_arrays["error"] = 0;
    // echo json_encode($res_arrays);
    echo true;
} catch (PDOException $e) {
    // echo json_encode($res_arrays);
    echo false;
}


