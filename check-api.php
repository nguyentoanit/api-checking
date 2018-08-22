#!/usr/bin/php
<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

//Check folder
$dir = date("Y_m_d");

if (!is_dir($dir)) {
    mkdir($dir);
}


?>