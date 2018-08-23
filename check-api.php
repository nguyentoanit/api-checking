#!/usr/bin/php
<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

// Check and create folder log
$today = date("Y-m-d");
$threeDayAgo = "log";

// Define variable for Chatwork
$chatwork = new GuzzleHttp\Client(['base_uri' => 'https://api.chatwork.com/v2/rooms/']);
define("roomID", '');
define("accessToken", '');

// Check folder log for today
if (!is_dir($today)) {
    mkdir($today);
    
    $threeDayAgo = date_create($today);
    date_sub($threeDayAgo,date_interval_create_from_date_string("3 days"));
    $threeDayAgo =  date_format($threeDayAgo,"Y-m-d");
}

// Check and remove old log folder
if (is_dir($threeDayAgo)) {
    array_map('unlink', glob("$threeDayAgo/*.*"));
    rmdir($threeDayAgo);
}

// Get list API from json file
$file_content = file_get_contents('api-list.json');
$apis = json_decode($file_content, true);

// Check result of decode json
if (!$apis){
    die('json_decode faile!');
}

// Initial Guzzle Client
$client = new GuzzleHttp\Client(['base_uri' => $apis['base_uri']]);

// Loop API list
foreach ($apis['api'] as $api) {
    try {
        $response = $client->request($api['method'], $api['endpoint'],[
            'json' => $api['parameters'],
            'headers' => $apis['headers']
        ]);
        
        $code = $response->getStatusCode();
        //sendMessage($chatwork, "$api[endpoint]: $code");
        
        $data['status'] = $code;
        $data['time'] = time();
        $data['notification'] = sendMessage($chatwork, "$api[endpoint]: $code");
        storeLogFile("$today/$api[endpoint].log", $data);

    } catch (RequestException $e) {
        if ($e->hasResponse()) {
            echo Psr7\str($e->getResponse());
        }
    }
}

// Sent message to chatwork
function sendMessage($chatwork, $message){
    $response = $chatwork->request('POST', roomID.'/messages', [
        'form_params' => ['body' => $message],
        'headers' => ['X-ChatWorkToken' => accessToken]
        ]);
    $code = $response->getStatusCode();
    if ($code >= 300) {
        return false;
    } else return true;
}

// Get last line of file
function getLastLine($file) {
    $data = file($file);
    $line = $data[count($data)-1];
    return $line;
}

// Store log
function storeLogFile($file, $data) {
    file_put_contents($file, json_encode($data)."\n", FILE_APPEND);
}

?>