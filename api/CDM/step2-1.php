<?php

$url = "http://cdm.srsphere.jp/welcome/login";
$ticket = "5a5ccd68-5b2a-4dd1-a711-69976966eeb9";

$data = array(
    'ticket' => $ticket,
);
$content = http_build_query($data);

$options = array(
    'http' => array(
        'method' => 'POST',
        'content' => $content
        ));
$contents = @file_get_contents($url, false, stream_context_create($options));

var_dump($contents);