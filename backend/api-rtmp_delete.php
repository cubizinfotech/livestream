<?php

    header("Content-Type: application/json");

    // Log request method and request data
    $logFile = './log/api.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] RTMP DELETE >>> {$_SERVER['REQUEST_METHOD']} ::: " . json_encode($_REQUEST) . "\n\n", FILE_APPEND);

    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        // http_response_code(405);
        echo json_encode(['status' => false, 'message' => '405 Method Not Allowed']);
        exit;
    }

    // Check if 'id' parameter is present
    if (!isset($_REQUEST['id'])) {
        // http_response_code(401);
        echo json_encode(['status' => false, 'message' => '401 Unauthorized']);
        exit;
    }

    $id = $_REQUEST['id'];

    // Load environment variables and database configuration
    require_once "./code/function.php";
    loadEnv(__DIR__ . '/.env');
    require_once "./code/config.php";

    try {    
        // Query for the container by ID
        $query = "SELECT * FROM rtmps WHERE `status` = 0 AND `id` = $id";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) === 0) {
            // http_response_code(404);
            echo json_encode(['status' => false, 'message' => '404 No record found']);
            exit;
        }

        $row = mysqli_fetch_assoc($result);
        $container_name = $row["container_name"];
        $stream_key = $row["stream_key"];

        // Execute Docker command to remove the container
        $command = $appEnviroment == 'local' ? "docker rm -f $container_name" : "sudo docker rm -f $container_name";
        $container = trim(shell_exec($command));

        // Log container deletion status
        $logMessage = $container ? "[$timestamp] CONTAINER DELETED ::: $container\n\n" : "[$timestamp] CONTAINER NOT FOUND ::: Container name '$container_name'\n\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        if ($container) {

            $folderPath = "./rtmp_server/$stream_key";
            if (is_dir($folderPath)) {
                // Delete all files in 'data' and 'record' subdirectories, then remove those directories
                foreach (['data', 'record'] as $subDir) {
                    $subDirPath = "$folderPath/$subDir";
                    array_map('unlink', glob("$subDirPath/*.*"));
                    rmdir($subDirPath);
                }
            
                // Delete all remaining files in the main folder, then remove the main folder
                array_map('unlink', glob("$folderPath/*.*"));
                rmdir($folderPath);
            }

            $query = "DELETE FROM rtmps WHERE `id` = $id";
            mysqli_query($conn, $query);

            echo json_encode(['status' => true, 'message' => "Container DELETED: $container"]);
            // http_response_code(200);
        } else {
            // http_response_code(404);
            echo json_encode(['status' => false, 'message' => "Container with name '$container_name' not found."]);
        }
    } catch (Exception $th) {
        // http_response_code(500);
        echo json_encode(['status' => false, 'message' => $th->Message()]);
    }

    mysqli_close($conn);
    exit;

?>