<?php
class DB_Common{
    protected $pdo;
    
    

    function __construct(){
        // eagle4 DB
	// <user> と <password> を データベースにアクセスするユーザのIDとパスワードに書き換えてください。
        $user = "suhtaradm";
        $pw = "GLEgm45T24CqgISo";
        $dns = "mysql:dbname=Doctor_app;host=localhost;";
        try{
            $this->pdo = new PDO($dns,$user,$pw,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET 'utf8', NAMES utf8"));
            // $this->pdo = new PDO("mysql:host=localhost;dbname=Doctor_app;charset=utf8;","root","1ken-nakayama");
            return $this->pdo;
        }catch(PDOException $ex){
            die("Error :" . $ex->getMessage());
        }
    }
}

// 薬のデータベースにアクセスするスクリプト
class Drug_DB extends DB_Common{
    // 上位クラスのコンストラクタ呼び出し
    function __construct(){
        parent::__construct();
    }
    // 薬の情報を取得するメソッド
    public function getData(){
        $sql = "SELECT * FROM drug_table";
        $stmh = $this->pdo->prepare($sql);
        try{
            $stmh->execute();
        }catch(PDOException $ex){
            die("Error :" . $ex->getMessage);
        }
        echo $stmh->fetch(PDO::FETCH_ASSOC);
    }
    /*
    public function insertDrug($drug_id,$drug_kind,$drug_name,$drug_product,$standard,$risk){
        $sql = "INSERT INTO drug_table (drug_id, drug_kind, drug_name, drug_product, standard, risk) VALUES (:drug_id, :drug_kind, :drug_name, :drug_product, :standard, :risk)";
        try{
            $stmh = $this->pdo->prepare($sql);
            $stmh->bindValue(":drug_id",$drug_id,PDO::PARAM_STR);
            $stmh->bindValue(":drug_kind",$drug_kind,PDO::PARAM_STR);
            $stmh->bindValue(":drug_name",$drug_name,PDO::PARAM_STR);
            $stmh->bindValue(":drug_product",$drug_product,PDO::PARAM_STR);
            $stmh->bindValue(":standard",$standard,PDO::PARAM_STR);
            $stmh->bindValue(":risk",$risk,PDO::PARAM_INT);
            $stmh->execute();
        }catch(PDOException $ex){
            return "Error : ". $ex->getMessage();
        }
    }
    */
    
    public function getDrugCode($drug_basecode){
        try{
            $sql = "SELECT * from drugs WHERE drug_basecode = :drug_code";
            $stmh = $this->pdo->prepare($sql);
            $stmh->bindValue(":drug_code",$drug_basecode,PDO::PARAM_STR);
            $stmh->execute();
            $data = $stmh->fetch(PDO::FETCH_ASSOC);
            return $data["drug_code"];
        }catch(PDOException $e){
            return "Error : " . $e->getMessage();
        }
    }

}

// 処方箋QRコードから得た情報をmedicationsテーブルにupsertする
class Medication_DB extends DB_Common{
    // 上位クラスのコンストラクタ呼び出し
    function __construct() {
        parent::__construct();
    }

    // 薬の情報を取得するメソッド
    public function getData() {
        $sql = "SELECT * FROM medications";
        $stmh = $this->pdo->prepare($sql);
        try {
            $stmh->execute();
        } catch (PDOException $ex) {
            die("Error :" . $ex->getMessage);
        }
        echo $stmh->fetch(PDO::FETCH_ASSOC);
    }
    
    public function upsertMedication($drug_id_qr,$drug_id_yj9,$drug_name_qr){
        $sql = "INSERT INTO medications (drug_id_qr, drug_id_yj9, drug_name_qr) VALUES (:drug_id_qr, :drug_id_yj9, :drug_name_qr) ON DUPLICATE KEY UPDATE drug_id_qr= :drug_id_qr";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":drug_id_qr",$drug_id_qr,PDO::PARAM_STR);
        $stmh->bindValue(":drug_id_yj9",$drug_id_yj9,PDO::PARAM_STR);
        $stmh->bindValue(":drug_name_qr",$drug_name_qr,PDO::PARAM_STR);
        try{
            $stmh->execute();
            return "success";
        }catch(PDOException $ex){
            return "ERROR : " . $ex->getMessage();
        }
    }

}

// QRコードJSONをそのまま登録する
class Prescription_asis_DB extends DB_Common{
    protected $pdo;
    // 上位クラスのコンストラクタ呼び出し
    function __construct(){
        $this->pdo = parent::__construct();
    }
    
    public function store_json($user_id, $prescript_json){
        $n_dup = $this->check_duplicate($user_id, $prescript_json);
        if($n_dup<1){
            $sql = "INSERT INTO user_drugs_asis (user_id, prescript_json) VALUES (:user_id, :prescript_json)";
            $stmh = $this->pdo->prepare($sql);
            $stmh->bindValue(":user_id", $user_id, PDO::PARAM_STR);
            $stmh->bindValue(":prescript_json", $prescript_json, PDO::PARAM_LOB);
            try {
                $stmh->execute();
                return "success";
            } catch (PDOException $ex) {
                return "ERROR : " . $ex->getMessage();
            }
        } else {
            return "duplicate operation suspected.";
        }
        
    }
    
    public function check_duplicate($user_id, $prescript_json){
        $sql = "SELECT COUNT(*) AS COUNT FROM user_drugs_asis where user_id = :user_id and prescript_json = :prescript_json";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":user_id", $user_id, PDO::PARAM_STR);
        $stmh->bindValue(":prescript_json", $prescript_json, PDO::PARAM_LOB);
        try {
            $stmh->execute();
        } catch (PDOException $ex) {
            die("ERROR : " . $ex);
        }
        $chk = $stmh->fetch(PDO::FETCH_ASSOC);
        $n_dup = (int)$chk['COUNT'] * 1;
        return $n_dup;
    }
    
    public function get_drughistory($ID){
        $sql = "select user_id, prescript_json, created_at from user_drugs_asis where user_id = :ID";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":ID", $ID, PDO::PARAM_STR);
        try {
            $stmh->execute();
        } catch (PDOException $ex) {
            die("ERROR : " . $ex);
        }
        return $stmh->fetchAll(PDO::FETCH_ASSOC);
    }
}


// 患者のデータベースにアクセスするスクリプト
class Patient_DB extends DB_Common{
    protected $pdo;
    // 上位クラスのコンストラクタ呼び出し
    function __construct(){
        $this->pdo = parent::__construct();
    }
    // 患者の情報を取得するメソッド
    public function getData(){
        $sql = "SELECT * FROM user_info";
        $stmh = $this->pdo->prepare($sql);
        try{
            $stmh->execute();
        }catch(PDOException $ex){
            die("Error :" . $ex->getMessage());
        }
        echo $stmh->fetch(PDO::FETCH_ASSOC);
    }

    // セレクトした患者IDからデータを取得
    public function getUserData($ID){
        $sql = "SELECT * FROM user_info WHERE user_id = :ID";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":ID",$ID,PDO::PARAM_STR);
        try{
            $stmh->execute();
        }catch(PDOException $ex){
            die("ERROR : " . $ex);
        }
        return $stmh->fetch(PDO::FETCH_ASSOC);
    }
    
    // 重複　IDチェックの姑息な手段
    public function checkDuplicate($ID){
        $sql = "SELECT count(*) AS count FROM user_info WHERE user_id = :ID";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":ID", $ID, PDO::PARAM_STR);
        try {
            $stmh->execute();
        } catch (PDOException $ex) {
            die("ERROR : " . $ex);
        }
        return $stmh->fetch(PDO::FETCH_ASSOC);
    }

    // 新しく患者を登録するメソッド
    public function insertUser($ID,$NAME,$ADDRESS,$TEL,$BIRTH,$SEX,$MYNUMBER,$POSTAL,$LAT,$LNG){
        $LATEST_TIME = date("Y-m-d H:i:s");
        try{
            $sql = "INSERT INTO user_info (user_id, user_name, user_address, user_tel, user_birth, user_sex, user_mynumber, user_postal, user_lat, user_lng, latest_time) VALUES (:user_id, :user_name, :user_address, :user_tel, :user_birth, :user_sex, :user_mynumber, :user_postal, :user_lat, :user_lng, :latest_time)";
            $stmh = $this->pdo->prepare($sql);
            $stmh->bindValue(":user_id",$ID,PDO::PARAM_STR);
            $stmh->bindValue(":user_name",$NAME,PDO::PARAM_STR);
            $stmh->bindValue(":user_address",$ADDRESS,PDO::PARAM_STR);
            $stmh->bindValue(":user_tel",$TEL,PDO::PARAM_STR);
            $stmh->bindValue(":user_birth",$BIRTH,PDO::PARAM_STR);
            $stmh->bindValue(":user_sex",$SEX,PDO::PARAM_INT);
            $stmh->bindValue(":user_mynumber",$MYNUMBER,PDO::PARAM_INT);
            $stmh->bindValue(":user_postal",$POSTAL,PDO::PARAM_STR);
            $stmh->bindValue(":user_lat",$LAT,PDO::PARAM_STR);
            $stmh->bindValue(":user_lng",$LNG,PDO::PARAM_STR);
            $stmh->bindValue(":latest_time",$LATEST_TIME,PDO::PARAM_STR);
            $stmh->execute();
            return "success";
        }catch(PDOException $ex){
            // die("ERROR : " . $ex->getMessage());
            return "ERROR : " . $ex->getMessage();
        }
    }
    // ユーザのアップデート
    public function updateUser($ID,$ADDRESS,$TEL,$POSTAL,$LAT,$LNG){
        $sql = "UPDATE user_info SET user_address = :user_address, user_tel = :user_tel, user_postal = :user_postal, user_lat = :user_lat, user_lng = :user_lng WHERE user_id = :user_id";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":user_address",$ADDRESS,PDO::PARAM_STR);
        $stmh->bindValue(":user_tel",$TEL,PDO::PARAM_STR);
        $stmh->bindValue(":user_postal",$POSTAL,PDO::PARAM_STR);
        $stmh->bindValue(":user_id",$ID,PDO::PARAM_STR);
//        $stmh->bindValue(":user_lat",$POSTAL,PDO::PARAM_STR);
//        $stmh->bindValue(":user_lng",$ID,PDO::PARAM_STR);
        $stmh->bindValue(":user_lat",$LAT,PDO::PARAM_STR);
        $stmh->bindValue(":user_lng",$LNG,PDO::PARAM_STR);
        try{
            $stmh->execute();
            return "success";
        }catch(PDOException $ex){
            return "Error : " . $ex->getMessage();
        }
    }
}

/*
// ユーザの緯度経度のデータベースにアクセスするスクリプト
class UserLatLng_DB extends DB_Common{
    protected $pdo;
    // 上位クラスのコンストラクタ呼び出し
    function __construct(){
        $this->pdo = parent::__construct();
    }

    // 患者の緯度経度の情報を取得するメソッド
    public function getData(){
        $sql = "SELECT * FROM USER_LATLNG";
        $stmh = $this->pdo->prepare($sql);
        try{
            $stmh->execute();
        }catch(PDOException $ex){
            die("Error :" . $ex->getMessage);
        }
        echo $stmh->fetch(PDO::FETCH_ASSOC);
        return $stmh->fetch(PDO::FETCH_ASSOC);
    }

    // セレクトしたユーザのデータの取得
    public function getUserData($ID){
        $sql = "SELECT * FROM USER_LATLNG WHERE user_id = :ID";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":ID",$ID,PDO::PARAM_STR);
        try{
            $stmh->execute();
            return $stmh->fetch(PDO::FETCH_ASSOC);
        }catch(PDOException $ex){
            return "Error :" . $ex->getMessage();
            die("Error :" . $ex->getMessage());
        }
    }
    // 新しく患者を登録するメソッド
    public function insertUser($ID,$LAT,$LNG,$DRUG_POSESSION){
        $sql = "INSERT INTO USER_LATLNG (user_id, user_lat, user_lng, drug_possesion) VALUES (:user_id, :user_lat, :user_lng, :drug_possesion)";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":user_id",$ID,PDO::PARAM_STR);
        $stmh->bindValue(":user_lat",$LAT,PDO::PARAM_STR);
        $stmh->bindValue(":user_lng",$LNG,PDO::PARAM_STR);
        $stmh->bindValue(":drug_possesion",$DRUG_POSESSION,PDO::PARAM_INT);
        try{
            $stmh->execute();
        }catch(PDOException $ex){
            die("ERROR : " . $ex);
        }
    }

    // 患者の緯度経度データを更新
    public function updateUser($ID,$LAT,$LNG,$DRUG_POSESSION){
        $sql = "UPDATE USER_LATLNG SET user_lat = :user_lat, user_lng = :user_lng, drug_possesion = :drug_possesion WHERE user_id = :user_id";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":user_id",$ID,PDO::PARAM_STR);
        $stmh->bindValue(":user_lat",$LAT,PDO::PARAM_STR);
        $stmh->bindValue(":user_lng",$LNG,PDO::PARAM_STR);
        $stmh->bindValue(":drug_possesion",$DRUG_POSESSION,PDO::PARAM_INT);
        try{
            $stmh->execute();
        }catch(PDOException $ex){
            die("ERROR : " . $ex);
        }
    }
}
*/
class UserDefault extends DB_Common {
    protected $pdo;

    // 上位クラスのコンストラクタ呼び出し
    function __construct(){
        $this->pdo = parent::__construct();
    }
    public function insertDefault($id){
        
        try{
            $sql = "INSERT INTO user_default (user_id) VALUES (:user_id)";
            $stmh = $this->pdo->prepare($sql);
            $stmh->bindValue(':user_id',$id,PDO::PARAM_STR);
            $stmh->execute();
            return "success";
        }catch(PDOException $ex){
            return "ERROR : " . $ex->getMessage();
        }
    }
}

class UserDrug extends DB_Common {
    protected $pdo;
    // 上位クラスのコンストラクタ呼び出し
    function __construct() {
        $this->pdo = parent::__construct();
    }
    
    public function insertDrug($drug_info){
        $n_dup = $this->check_duplicate($drug_info['user_id'], $drug_info['drug_id'], $drug_info['date_prepare'], $drug_info['pharmacy_instcode']);
        
        if($n_dup<1){
            $fieldnames = "user_id, drug_id, drug_id_type, date_prepare, ";
            $fieldnames .= "pharmacy_name, pharmacy_prefecture, pharmacy_table, pharmacy_instcode, ";
            $fieldnames .= "inst_prescript_name, inst_prescript_prefecture, inst_prescript_table, inst_prescript_code, ";
            $fieldnames .= "prescript_dr_name, prescript_dr_clinic, ";
            $fieldnames .= "drug_name, drug_dose, drug_dose_unit, drug_dosage, drug_dispense_amount, drug_dispense_unit, drug_form_code, drug_usage_type, drug_usage_code, em_rank";

            $setvalues = ":user_id, :drug_id, :drug_id_type, :date_prepare, ";
            $setvalues .= ":pharmacy_name, :pharmacy_prefecture, :pharmacy_table, :pharmacy_instcode, ";
            $setvalues .= ":inst_prescript_name, :inst_prescript_prefecture, :inst_prescript_table, :inst_prescript_code, ";
            $setvalues .= ":prescript_dr_name, :prescript_dr_clinic, ";
            $setvalues .= ":drug_name, :drug_dose, :drug_dose_unit, :drug_dosage, :drug_dispense_amount, :drug_dispense_unit, :drug_form_code, :drug_usage_type, :drug_usage_code, :em_rank";

            $sql = "INSERT INTO user_drug_info ($fieldnames) VALUES ($setvalues) ON DUPLICATE KEY UPDATE date_prepare= :date_prepare"; // ON DUPLICATE KEY UPDATE drug_id_qr= :drug_id_qr

            $stmh = $this->pdo->prepare($sql);
            $stmh->bindValue(":user_id", $drug_info['user_id'], PDO::PARAM_STR);
            $stmh->bindValue(":drug_id", $drug_info['drug_id'], PDO::PARAM_STR);
            $stmh->bindValue(":drug_id_type", $drug_info['drug_id_type'], PDO::PARAM_STR);
            $stmh->bindValue(":date_prepare", $drug_info['date_prepare'], PDO::PARAM_STR);
            $stmh->bindValue(":pharmacy_name", $drug_info['pharmacy_name'], PDO::PARAM_STR);
            $stmh->bindValue(":pharmacy_prefecture", $drug_info['pharmacy_prefecture'], PDO::PARAM_STR);
            $stmh->bindValue(":pharmacy_table", $drug_info['pharmacy_table'], PDO::PARAM_STR);
            $stmh->bindValue(":pharmacy_instcode", $drug_info['pharmacy_instcode'], PDO::PARAM_STR);
            $stmh->bindValue(":inst_prescript_name", $drug_info['inst_prescript_name'], PDO::PARAM_STR);
            $stmh->bindValue(":inst_prescript_prefecture", $drug_info['inst_prescript_prefecture'], PDO::PARAM_STR);
            $stmh->bindValue(":inst_prescript_table", $drug_info['inst_prescript_table'], PDO::PARAM_STR);
            $stmh->bindValue(":inst_prescript_code", $drug_info['inst_prescript_code'], PDO::PARAM_STR);
            $stmh->bindValue(":prescript_dr_name", $drug_info['prescript_dr_name'], PDO::PARAM_STR);
            $stmh->bindValue(":prescript_dr_clinic", $drug_info['prescript_dr_clinic'], PDO::PARAM_STR);
            $stmh->bindValue(":drug_name", $drug_info['drug_name'], PDO::PARAM_STR);
            $stmh->bindValue(":drug_dose", $drug_info['drug_dose'], PDO::PARAM_STR);
            $stmh->bindValue(":drug_dose_unit", $drug_info['drug_dose_unit'], PDO::PARAM_STR);
            $stmh->bindValue(":drug_dosage", $drug_info['drug_dosage'], PDO::PARAM_STR);
            $stmh->bindValue(":drug_dispense_amount", $drug_info['drug_dispense_amount'], PDO::PARAM_STR);
            $stmh->bindValue(":drug_dispense_unit", $drug_info['drug_dispense_unit'], PDO::PARAM_STR);
            $stmh->bindValue(":drug_form_code", $drug_info['drug_form_code'], PDO::PARAM_STR);
            $stmh->bindValue(":drug_usage_type", $drug_info['drug_usage_type'], PDO::PARAM_STR);
            $stmh->bindValue(":drug_usage_code", $drug_info['drug_usage_code'], PDO::PARAM_STR);
            $stmh->bindValue(":em_rank", $drug_info['em_rank'], PDO::PARAM_STR);
            try {
                $stmh->execute();
                return "success";
            } catch (PDOException $ex) {
                return "ERROR : " . $ex->getMessage();
            }
        } else {
            return "Duplicate operation suspected.";
        }
    }
    
    public function check_duplicate($user_id, $drug_id, $date_prepare, $pharmacy_instcode){
        $sql = "SELECT COUNT(*) AS COUNT from user_drug_info where user_id = :user_id AND drug_id = :drug_id AND date_prepare = :date_prepare AND pharmacy_instcode = :pharmacy_instcode";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":user_id", $user_id, PDO::PARAM_STR);
        $stmh->bindValue(":drug_id", $drug_id, PDO::PARAM_STR);
        $stmh->bindValue(":date_prepare", $date_prepare, PDO::PARAM_STR);
        $stmh->bindValue(":pharmacy_instcode", $pharmacy_instcode, PDO::PARAM_STR);
        try {
            $stmh->execute();
        } catch (PDOException $ex) {
            die("ERROR : " . $ex);
        }
        $chk = $stmh->fetch(PDO::FETCH_ASSOC);
        $n_dup = (int) $chk['COUNT'] * 1;
        return $n_dup;
    }
}

class UserDrug_v0 extends DB_Common {
    // 2019/9までで廃止した（元の名前は UserDrug）
    protected $pdo;

    // 上位クラスのコンストラクタ呼び出し
    function __construct(){
        $this->pdo = parent::__construct();
    }

    // 患者の薬のデータをstore
    
    public function insertDrug($ID,$drug_id,$day_usage,$drug_num,$day_left,$last_day,$em_rank){
        $sql = "INSERT INTO user_drug (user_id, drug_id, day_usage, drug_num, day_left, last_day, em_rank) VALUES (:user_id, :drug_id, :day_usage, :drug_num, :day_left, :last_day, :em_rank)";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":user_id",$ID,PDO::PARAM_STR);
        $stmh->bindValue(":drug_id",$drug_id,PDO::PARAM_STR);
        $stmh->bindValue(":day_usage",$day_usage,PDO::PARAM_INT);
        $stmh->bindValue(":drug_num",$drug_num,PDO::PARAM_INT);
        $stmh->bindValue(":day_left",$day_left,PDO::PARAM_INT);
        $stmh->bindValue(":last_day",$last_day,PDO::PARAM_STR);
        $stmh->bindValue(":em_rank",$em_rank,PDO::PARAM_STR);
        try{
            $stmh->execute();
            return "success";
        }catch(PDOException $ex){
            return "ERROR : " . $ex->getMessage();
        }
    }


    /*
    // 患者の薬のデータをstore
    public function updateDrug($ID,$drug1,$drug2,$drug3,$drug4,$drug5,$drug6,$drug7,$drug8,$drug9,$drug10,$drug11,$drug12,$drug13,$drug14,$drug15,$drug16,$drug17,$drug18,$drug19,$drug20){
        $sql = "UPDATE UserDrug SET drug_1=:drug1, drug_2=:drug2, drug_3=:drug3, drug_4=:drug4, drug_5=:drug5, drug_6=:drug6, drug_7=:drug7, drug_8=:drug8, drug_9=:drug9, drug_10=:drug10, drug_11=:drug11, drug_12=:drug12, drug_13=:drug13, drug_14=:drug14, drug_15=:drug15, drug_16=:drug16, drug_17=:drug17, drug_18=:drug18, drug_19=:drug19, drug_20=:drug20 WHERE user_id=:user_id";
        $stmh = $this->pdo->prepare($sql);
        $stmh->bindValue(":user_id",$ID,PDO::PARAM_STR);
        $stmh->bindValue(":drug1",$drug1,PDO::PARAM_STR);
        $stmh->bindValue(":drug2",$drug2,PDO::PARAM_STR);
        $stmh->bindValue(":drug3",$drug3,PDO::PARAM_STR);
        $stmh->bindValue(":drug4",$drug4,PDO::PARAM_STR);
        $stmh->bindValue(":drug5",$drug5,PDO::PARAM_STR);
        $stmh->bindValue(":drug6",$drug6,PDO::PARAM_STR);
        $stmh->bindValue(":drug7",$drug7,PDO::PARAM_STR);
        $stmh->bindValue(":drug8",$drug8,PDO::PARAM_STR);
        $stmh->bindValue(":drug9",$drug9,PDO::PARAM_STR);
        $stmh->bindValue(":drug10",$drug10,PDO::PARAM_STR);
        $stmh->bindValue(":drug11",$drug11,PDO::PARAM_STR);
        $stmh->bindValue(":drug12",$drug12,PDO::PARAM_STR);
        $stmh->bindValue(":drug13",$drug13,PDO::PARAM_STR);
        $stmh->bindValue(":drug14",$drug14,PDO::PARAM_STR);
        $stmh->bindValue(":drug15",$drug15,PDO::PARAM_STR);
        $stmh->bindValue(":drug16",$drug16,PDO::PARAM_STR);
        $stmh->bindValue(":drug17",$drug17,PDO::PARAM_STR);
        $stmh->bindValue(":drug18",$drug18,PDO::PARAM_STR);
        $stmh->bindValue(":drug19",$drug19,PDO::PARAM_STR);
        $stmh->bindValue(":drug20",$drug20,PDO::PARAM_STR);
        try{
            $stmh->execute();
            return "success";
        }catch(PDOException $ex){
            return "ERROR : " . $ex->getMessage();
        }
    }
    */
}

 ?>
