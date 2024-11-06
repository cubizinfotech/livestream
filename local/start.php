<?php

// header('HTTP/1.1 200 OK');
// header('HTTP/1.1 401 Unauthorized');

$streamKey = $_REQUEST['name'];
$streamCall = $_REQUEST['call'];

$logFile = 'stream.log';
$logMessage = '[' . date('Y-m-d H:i:s') . '] streamStart ::: ' . json_encode($_REQUEST) . " \n\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

if ($streamKey == "stream") {
    echo json_encode([
        'status' => true,
        'message' => 'Stream started successfully.',
        'result' => $streamKey
    ]);
    header('HTTP/1.1 200 OK');
    return;
} 
else {
    echo json_encode([
        'status' => false,
        'message' => 'Something went wrong.',
    ]);
    header('HTTP/1.1 401 Unauthorized');
    return;
}

?>
