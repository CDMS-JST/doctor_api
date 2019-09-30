<?php
// ini_set("display_errors",1);
class Drug_data{
    public $name;
    public $product;
    public $standard;
    public $drug_code;
    public $kind;
    public $risk;

    function __construct ($name,$product,$standard,$drug_code,$kind,$risk){
        $this->name = $name;
        $this->product = $product;
        $this->standard = $standard;
        $this->drug_code = $drug_code;
        $this->kind = $kind;
        $this->risk = $risk;
    }
}
?>