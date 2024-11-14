<?php

// header('HTTP/1.1 200 OK');
// header('HTTP/1.1 401 Unauthorized');
header("Content-Type: application/json");

$streamKey = $_REQUEST['name'];
$streamCall = $_REQUEST['call'];

$logFile = 'stream.log';
$logMessage = '[' . date('Y-m-d H:i:s') . '] streamBlocked ::: ' . json_encode($_REQUEST) . " \n\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

if ($streamKey == "stream" || 1 == 1) {
    echo json_encode([
        'status' => true,
        'is_blocked' => true,
        'message' => 'Stream blocked successfully.',
        'result' => $streamKey
    ]);
    header('HTTP/1.1 200 OK');
    exit;
} 
else {
    echo json_encode([
        'status' => false,
        'message' => 'Something went wrong.',
    ]);
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

?>
