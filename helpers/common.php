<?php
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
