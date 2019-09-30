<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor. 
 */

$url = "http://cdm.srsphere.jp/karte/restservice/trusted/PhrRequestTickets";
$PatientNo = "81f6f2ed";

$url .= sprintf("/%s", $PatientNo);

$options = array(
    'http' => array(
        'method' => 'POST',
        ));
$contents = @file_get_contents($url, false, stream_context_create($options));

var_dump($contents);