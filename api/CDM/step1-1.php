<?php

// マニュアル 1. Phr Ticket の発行
$url1 = "http://cdm.srsphere.jp/karte/restservice/trusted/PhrRequestTickets";

$options = array(
    'http' => array(
        'method' => 'POST',
        ));
$response = @file_get_contents($url1, false, stream_context_create($options));

$contents = json_decode($response);

$ticketCode = $contents->t; // チケットコード取得完了

// マニュアル 2. Login IDP （認証）

$url2 = "http://cdm.srsphere.jp/welcome/login";

$data = array(
    'ticket' => $ticketCode
);
$content = http_build_query($data);

$options = array(
    'http' => array(
        'method' => 'POST',
        'content' => $content
        ));

$idp_response = @file_get_contents($url2, false, stream_context_create($options));

var_dump($_COOKIE);