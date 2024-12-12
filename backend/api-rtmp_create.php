<?php

    header("Content-Type: application/json");

    // Log the request
    $logFile = './log/api.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] RTMP CREATE >>> {$_SERVER['REQUEST_METHOD']} ::: " . json_encode($_REQUEST) . "\n\n", FILE_APPEND);

    // Allow only POST requests and check if 'id' is set
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        // http_response_code(405);
        echo json_encode(['status' => false, 'message' => '405 Method Not Allowed']);
        exit;
    }
    if (!isset($_REQUEST['id'])) {
        // http_response_code(401);
        echo json_encode(['status' => false, 'message' => 'Required field is missing (like id).']);
        exit;
    }

    $id = $_REQUEST['id'];

    // Load environment and configuration files
    require_once "./code/function.php";
    loadEnv(__DIR__ . '/.env');
    require_once "./code/config.php";

    try {    
        // Query for container details
        $query = "SELECT * FROM rtmps WHERE `status` = 2 AND id = $id";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) == 0) {
            // http_response_code(401);
            echo json_encode(['status' => false, 'message' => 'No record found']);
            exit;
        }
        $row = mysqli_fetch_assoc($result);
        $server_name = $row["server_name"];
        $container_name = $row["container_name"];
        $rtmp_port = $row["rtmp_port"];
        $http_port = $row["http_port"];
        $stream_key = $row["stream_key"];

        // Replace placeholders in templates
        $templateFiles = [
            'yml'   => './rtmp/docker-compose.yml',
            'conf'  => './rtmp/nginx.conf',
            'sh'    => './rtmp/timeout.sh',
            'html'  => './rtmp/index.html',
            'php'   => './rtmp/index.php'
        ];
        $serverPath = "./rtmp_server/$stream_key";
        $dataPath = "./rtmp_server/$stream_key/data";
        $recordPath = "./rtmp_server/$stream_key/record";
        if (!is_dir($serverPath)) {
            umask(0);
            mkdir($serverPath, 0777, true);
            mkdir($dataPath, 0777, true);
            mkdir($recordPath, 0777, true);
        }

        $replacements = [
            'yml' => ['[SERVER_NAME]', '[CONTAINER_NAME]', '[RTMP_PORT]', '[HTTP_PORT]'],
            'conf' => ['[RTMP_PORT]', '[MAX_VIEWERS]', '[HLS_FRAGMENT]', '[HLS_PLAYLIST_LENGTH]', '[MAX_STREAMING_TIME]', '[ON_PUBLISH]', '[ON_PUBLISH_DONE]', '[ON_RECORD_DONE]', '[HTTP_PORT]', '[STREAM_LOG]'],
            'sh' => ['[STREAM_BLOCKED]'],
            'html' => ['[MAIN_SERVER_URL]', '[HTTP_PORT]', '[STREAM_KEY]'],
            'php' => ['[MAIN_PAGE_URL]']
        ];

        $values = [
            'yml' => [$server_name, $container_name, $rtmp_port, $http_port],
            'conf' => [$rtmp_port, $maxViewers, $hlsFragment, $hlsPlaylistLength, $recInterval, $streamStart, $streamStop, $streamRecord, $http_port, $logURL],
            'sh' => [$streamBlocked],
            'html' => [$mainURL, $http_port, $stream_key],
            'php' => [$mainPageURL]
        ];

        foreach ($templateFiles as $key => $filePath) {
            $content = file_get_contents($filePath);
            $contentReplaced = str_replace($replacements[$key], $values[$key], $content);
            file_put_contents("$serverPath/" . basename($filePath), $contentReplaced);
        }

        // Set file permissions if in non-local environment
        if ($appEnviroment !== 'local') {
            chmod("$serverPath/docker-compose.yml", 0777);
            chmod("$serverPath/nginx.conf", 0777);
            chmod("$serverPath/timeout.sh", 0777);
        }

        // Run Docker commands and update restart policy
        $dockerCommand = ($appEnviroment === 'local' ? '' : 'sudo ') . 'docker-compose -f ' . escapeshellarg("$serverPath/docker-compose.yml") . ' up -d --build 2>&1';
        $output = shell_exec($dockerCommand);

        file_put_contents($logFile, "[$timestamp] Docker Compose Output: \n$output\n==============================\n\n", FILE_APPEND);

        if ($output) {
            sleep(1);
            $containerIdCommand = ($appEnviroment === 'local' ? '' : 'sudo ') . "docker ps -q -f \"name=$container_name\"";
            $containerId = trim(shell_exec($containerIdCommand));

            if ($containerId) {
                $restartCommand = ($appEnviroment === 'local' ? '' : 'sudo ') . "docker update --restart=always $containerId 2>&1";
                $restartOutput = shell_exec($restartCommand);
                file_put_contents($logFile, "[$timestamp] Container Update: $restartOutput\n\n", FILE_APPEND);

                $query = "UPDATE rtmps SET `status` = 1 WHERE `id` = $id";
                mysqli_query($conn, $query);

                // http_response_code(200);
                echo json_encode(['status' => true, 'message' => "Container updated with restart policy 'always': $restartOutput"]);
            } else {
                file_put_contents($logFile, "[$timestamp] Container Not Found: '$container_name'\n\n", FILE_APPEND);
                // http_response_code(404);
                echo json_encode(['status' => false, 'message' => "Container with name '$container_name' not found."]);
            }
        } else {
            // http_response_code(500);
            echo json_encode(['status' => false, 'message' => 'Docker compose or NGINX configuration files not found!', 'data' => $output]);
        }
    } catch (Exception $th) {
        // http_response_code(500);
        echo json_encode(['status' => false, 'message' => $th->getMessage()]);
    }

    if ($appEnviroment !== 'local') {
        // Set permissions
        chmod($serverPath, 0777);
        chmod($dataPath, 0777);
        chmod($recordPath, 0777);
    }

    mysqli_close($conn);
    exit;

?>