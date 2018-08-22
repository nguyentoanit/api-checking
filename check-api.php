#!/usr/bin/php
<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

//Check and create folder log
$today = date("Y-m-d");
$threeDayAgo = "log";

if (!is_dir($today)) {
    mkdir($today);
    
    $threeDayAgo = date_create($today);
    date_sub($threeDayAgo,date_interval_create_from_date_string("3 days"));
    $threeDayAgo =  date_format($threeDayAgo,"Y-m-d");
}

//Check and remove old log folder
if (is_dir($threeDayAgo)) {
    array_map('unlink', glob("$threeDayAgo/*.*"));
    rmdir($threeDayAgo);
}

$chatwork = new GuzzleHttp\Client(['base_uri' => 'https://api.chatwork.com/v2/rooms/']);
define("roomID", '');
define("accessToken", '');

function sendMessage($chatwork){
    $response = $chatwork->request('POST', roomID.'/messages', [
        'form_params' => ['body' => urlencode('Test message from client!')],
        'headers' => ['X-ChatWorkToken' => accessToken]
        ]);
}

//sendMessage($chatwork);

?>