<?php
class Crypt {
    private $hash_id;

    private $salt;

    function __construct($ID){
        $this->hash_id = $ID;
        $this->crypt();
    }
    private function crypt(){
        $this->create_salt(rand(4,16));
        $this->stretching();
    }

    private function stretching(){
        $ID = $this->hash_id;
        for($i = 0;$i < 100; $i++){
            $ID = hash('sha256',$ID);
        }
        $this->hash_id = $ID;
    }

    private function create_salt($len){
        $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
        $r_str = null;
        for ($i = 0; $i < $len; $i++) {
            $r_str .= $str[rand(0, count($str) - 1)];
        }
        $this->salt = $r_str;
    }

    public function getSalt(){
        return $this->salt;
    }

    public function getID(){
        return $this->hash_id;
    }
}
?>
