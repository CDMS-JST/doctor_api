<?php
ini_set('display_errors',1);

require_once __DIR__ . '/../libs/crypt/Crypt.php';
require_once __DIR__ . '/../libs/communicateDB/CommunicateDB.php';

$str =  $_POST['jsonobject'];

$json = json_decode($str,true);

$c = new Crypt($json['user_id']);
$id = $c->getID();
//file_put_contents("log.txt",var_export($drug,true));

// ログ設定
$logfile = "./logs/logs.txt";
$logdata = date("Y-m-d H:i:s");
$logdata .= sprintf(",薬剤登録,%s",$id);

foreach ($json['drugStatuses'] as $key => $val){
    // ログ設定
    $logdata .= sprintf(",(drugStatuses)%s",$val["identify"]);
    
    if(isset($val['drug_id'])){
        $logdata .= sprintf(",(drug_id)%s",$val['drug_id']);
    } else {
        $logdata .= ",drug_id未受信";
    }
    
    if(isset($val['day_usage'])){
        $logdata .= sprintf(",(day_usage)%s",$val['day_usage']);
    } else {
        $logdata .= ",day_usage未受信";
    }
    
    if(isset($val['drug_num'])){
        $logdata .= sprintf(",(drug_num)%s",$val['drug_num']);
    } else {
        $logdata .= ",drug_num未受信";
    }
    
    if(isset($val['day_left'])){
        $logdata .= sprintf(",(day_left)%s",$val['day_left']);
    } else {
        $logdata .= ",day_left未受信";
    }
    
    if(isset($val['last_day'])){
        $logdata .= sprintf(",(last_day)%s",$val['last_day']);
    } else {
        $logdata .= ",last_day未受信";
    }
    

    
    if ($val["identify"] == 2) {
        $ins = new UserDrug();
        $err = $ins->insertDrug($id,$val['drug_id'],$val['day_usage'],$val['drug_num'],$val['day_left'],$val['last_day']);
    }else if($val["identify"] == 4){
        $dins = new Drug_DB();
        $uins = new UserDrug();
        $drug = $dins->getDrugCode($val['drug_id']);
        $err = $uins->insertDrug($id,$drug,$val['day_usage'],$val['drug_num'],$val['day_left'],$val['last_day']);
//        file_put_contents("log.txt",var_export($err,true));
    }
    
    $logdata .= sprintf("SQL_RESULT=%s\n", $err);
    file_put_contents($logfile, $logdata, FILE_APPEND | LOCK_EX);
    
}
?>