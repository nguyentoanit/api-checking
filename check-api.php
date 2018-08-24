#!/usr/bin/php
<?php
require 'vendor/autoload.php';
require_once 'configs/chatwork.php';

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

// Check and create folder log
$today = date("Y-m-d");
$threeDayAgo = "log";

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
    $data['error'] = '';
    $data['time'] = time();
    $lastLog = json_decode(getLastLine("$today/$api[endpoint].log"), true);

    try {
        $response = $client->request($api['method'], $api['endpoint'],[
            'json' => $api['parameters'],
            'headers' => $apis['headers']
        ]);
                
    } catch (RequestException $e) {
        $data['error'] = Psr7\str($e->getRequest());
        if ($e->hasResponse()) {
            $data['error'] = Psr7\str($e->getResponse());
        }
    }

    if ( ($data['error'] != '' && $data['time'] - $lastLog['time'] > 600 ) ) {
        // Send error message to all
        $message = $data['error'];
        sendMessage($chatwork, "$api[endpoint]: $message");
    } elseif ( !$data['error'] xor !$lastLog['error'] ) {
        $message = 'Alive';
        sendMessage($chatwork, "$api[endpoint]: $message");
    }
    storeLogFile("$today/$api[endpoint].log", $data);
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
    if(!$line) {
        return '{"time": 0,"error":""}';
    } else {
        return $line;
    }
}

// Store log
function storeLogFile($file, $data) {
    file_put_contents($file, json_encode($data)."\n", FILE_APPEND);
}

?>