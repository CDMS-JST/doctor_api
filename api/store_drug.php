<?php

ini_set('display_errors',1);

require_once __DIR__ . '/../libs/crypt/Crypt.php';
require_once __DIR__ . '/../libs/communicateDB/CommunicateDB.php';
require_once __DIR__ . '/../libs/sagamed/fields.php';


$prescript_json =  $_POST['jsonobject'];

$jsons = json_decode($prescript_json,true);

// ログ設定　(Added by Takasaki)
$logfile = "./logs/logs.txt";
$logdata = date("Y-m-d H:i:s");
$logdata .= sprintf(",薬剤登録,%s",$id);

$ins = new UserDrug();

// スマホ側データ送信が大幅改訂のため　完全修正 By Takasaki
foreach($jsons as $json){
    foreach($fields_lv1 as $key=>$label){
        $objects = $json[$key];
        switch($key){
            case 'user_id':
                $user_id = $objects;
                $c = new Crypt($user_id);
                $id = $c->getID();
                $drug_info['user_id'] = $id;
                break;
            case 'data':
                foreach($fields_lv2 as $pkey=>$prescriptions){
                    if(is_array($prescriptions)){
                        $grp = $prescriptions['grp'];
                        $item = $prescriptions['item'];
                        $drug_info[$pkey] = $objects[$grp][$item];
                    } else {
                        if($pkey === "date_prepare"){
                            $date_prepare = date("Y-m-d", strtotime($objects[$prescriptions]));
                            $drug_info[$pkey] = $date_prepare;
                        } else {
                            $drug_info[$pkey] = $objects[$prescriptions];
                        }
                    }
                }
                $dosages = $json['data']["薬品_用法"];
                foreach($dosages as $dosage){
                    foreach ($fields_lv3 as $dkey => $items) {
                        $grp = $items['grp'];
                        $item = $items['item'];
                        $drug_info[$dkey] = $dosage[$item];
                    }
                    
                    $drug_id_yj9 = substr($drug_info['drug_id'], 0, 9);
                    // YJコード9桁情報で休薬危険薬剤等を判定
                    $em_rank_api = sprintf("https://suhtar.hospital.med.saga-u.ac.jp/svc1/dictionary/check_em_rank/%s", $drug_id_yj9);
                    $em_rank = file_get_contents($em_rank_api);
                    $drug_info['em_rank'] = $em_rank;
                    
                    $err = $ins->insertDrug($drug_info);
                    foreach ($drug_info as $key => $val) {
                        $logdata .= sprintf("(%s)=「%s」 ", $key, $val);
                    }
                    $logdata .= sprintf("SQL RESULT=【%s】%s\n", $err, date("Y-m-d H:i:s"));
                    file_put_contents($logfile, $logdata, FILE_APPEND | LOCK_EX);
                }
                
                break;
            default :
                // Do Nothing
                break;
        }
    }
    
    // 処方箋内に休薬危険薬剤等があるかその都度確認し、薬剤レコードにフラグ付与
    $drug_id_yj9 = substr($drug_info['drug_id'], 0, 9);
    // YJコード9桁情報で休薬危険薬剤等を判定
    $em_rank_api = sprintf("https://suhtar.hospital.med.saga-u.ac.jp/svc1/dictionary/check_em_rank/%s", $drug_id_yj9);
    $em_rank = file_get_contents($em_rank_api);
    $drug_info['em_rank'] = $em_rank; // em_rank 48H:休薬危険薬剤　　1W:準休薬危険薬剤
    
    
    // システム内の薬剤名マスタをUpsert(By Tkasaki)
    /*
     * 提供された元のスクリプトはQRコード内の薬剤名を用いず、QRコード内の薬剤コードを保険適用薬剤データのコードに引き当て
     * 得られた薬剤名を登録していたため、該当しないものは登録されても薬剤名が表示されない状態だった。
     * QRコードの薬剤名をそのまま登録しておけばすむが、上記の処理を行った理由が不明のため、薬剤名をすべて表示するためだけの
     * 無駄なテーブルを作成して対応した。
     * 対応内容は、QRコードに薬剤名が出てくるたびに、DB内のmedicationsテーブルを参照し、同じ名前の薬剤がなければ登録する。
     * 薬剤一覧で表示する際はここから薬剤名を得る。　登録がない薬剤はすべて登録するので、表示漏れはない
     */
    $medication_table = new Medication_DB();
    $save_medication = $medication_table->upsertMedication($drug_info['drug_id'], $drug_id_yj9, $drug_info['drug_name']);
    
    
}

// JSONのままとにかく保存 (By Takasaki)
/*
 * スマホアプリの機能がお粗末すぎたので追加
 * QRコードを読み取った後、薬剤情報をスマホで確認する機能がなかったので、突貫工事で追加作成
 * データベース登録時のカラム名などを協議して決めようと提案したが、なぜか「医薬品名」など
 * 日本語をキーとしたjsonの提供にこだわられたため、実際に使用するテーブルはカラム名等も
 * こちらで定義し、用途に応じて作成した。
 * スマホアプリ側からの履歴参照の時だけに使うテーブルとして、ユーザーIDと読み取ったJSONを
 * そのままラージオブジェクトとして保存するテーブルで対応
 */
$asis = new Prescription_asis_DB();
$asis->store_json($drug_info['user_id'], $prescript_json);
