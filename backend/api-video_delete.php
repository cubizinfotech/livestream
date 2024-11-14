<?php

    header("Content-Type: application/json");

    // Log the request
    $logFile = './log/api.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] RTMP RECORDING DELETE >>> {$_SERVER['REQUEST_METHOD']} ::: " . json_encode($_REQUEST) . "\n\n", FILE_APPEND);

    // Handle only POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        // http_response_code(405);
        echo json_encode(['status' => false, 'message' => '405 Method Not Allowed']);
        exit;
    }

    try {    
        // Check for required fields in the request
        if (!empty($_REQUEST['path']) && !empty($_REQUEST['name'])) {
            $path = $_REQUEST['path'];
            $name = $_REQUEST['name'];

            // Attempt to delete the file
            $filePath = "./rtmp_server/{$name}/{$path}";
            if (unlink($filePath)) {
                echo json_encode(['status' => true, 'message' => 'File deleted successfully.']);
                // http_response_code(200);
            } else {
                echo json_encode(['status' => false, 'message' => 'Something went wrong when file removed.']);
                // http_response_code(401);
            }
        } else {
            echo json_encode(['status' => false, 'message' => 'Required fields missing (like path, name).']);
            // http_response_code(404);
        }
    } catch (Exception $th) {
        // http_response_code(500);
        echo json_encode(['status' => false, 'message' => $th->Message()]);
    }

    exit;

?>