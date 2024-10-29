<?php

$logFile = 'api.log';
$logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $_SERVER['REQUEST_METHOD'] . ' ::: ' . json_encode($_REQUEST) . " \n \n ";
file_put_contents($logFile, $logMessage, FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_REQUEST['path'])) {

        $path = $_REQUEST['path'];
        if (unlink("./".$path)) {

            echo json_encode([
                'status' => true,
                'message' => 'File deleted successfully.',
            ]);
            header('HTTP/1.1 200 OK');
            exit;
        } 
        else {
            echo json_encode([
                'status' => false,
                'message' => 'Something went wrong when file remove.',
            ]);
            header('HTTP/1.1 401 Unauthorized');
            exit;
        }
    } 
    else {
        echo json_encode([
            'status' => false,
            'message' => 'Required fiels missing (like path).',
        ]);
        header('HTTP/1.1 401 Unauthorized');
        exit;
    }
}
else {
    echo json_encode([
        'status' => false,
        'message' => '405 Method Not Allowed',
    ]);
    http_response_code(405);
    exit;
}

?>