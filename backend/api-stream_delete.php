<?php

    header("Content-Type: application/json");

    // Log the request
    $logFile = './log/api.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] RTMP PREVIOUS STREMING VIDEO DELETE >>> {$_SERVER['REQUEST_METHOD']} ::: " . json_encode($_REQUEST) . "\n\n", FILE_APPEND);

    // Handle only POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        // http_response_code(405);
        echo json_encode(['status' => false, 'message' => '405 Method Not Allowed']);
        exit;
    }

    try {    
        // Check for required fields in the request
        if (!empty($_REQUEST['name'])) {
            $name = $_REQUEST['name'];

            // Attempt to delete the folder file
            $folderPath = "./rtmp_server/{$name}/data";
            if (is_dir($folderPath)) {
                chmod($folderPath, 0777);
                array_map('unlink', glob("{$folderPath}/*.*"));
                echo json_encode(['status' => true, 'message' => 'File deleted successfully.']);
                // http_response_code(200);
            } else {
                echo json_encode(['status' => false, 'message' => 'Something went wrong when file removed.']);
                // http_response_code(401);
            }
        } else {
            throw new Exception("Required fields missing (like name).");
            echo json_encode(['status' => false, 'message' => 'Required fields missing (like name).']);
            // http_response_code(404);
        }
    } catch (Exception $th) {
        // http_response_code(500);
        throw new Exception($th->getMessage());
        echo json_encode(['status' => false, 'message' => $th->getMessage()]);
    }

    exit;

?>