<?php

    header("Content-Type: application/json");

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        echo json_encode([
            'status' => false,
            'message' => '405 Method Not Allowed',
        ]);
        http_response_code(405);
        exit;
    }

    require_once("./php/function.php");
    loadEnv(__DIR__ . '/.env');

    require_once("./php/config.php");
    require_once("./php/create-yml.php");
    require_once("./php/create-conf.php");
    require_once("./php/create-sh.php");

    // echo "complete...";
    sleep(1);

    $dockerComposeFile = './docker-compose.yml';
    $nginxConfigFile = './nginx.conf';

    if (file_exists($dockerComposeFile) && file_exists($nginxConfigFile)) {

        $output = shell_exec('docker-compose -f ' . escapeshellarg($dockerComposeFile) . ' up -d --build 2>&1');
        if ($output) {
            $containerId = trim(shell_exec("docker ps -q -f \"name=$containerName\""));
            if ($containerId) {

                $output = shell_exec("docker update --restart=always $containerId 2>&1");

                $logFile = './log/api.log';
                $logMessage = '[' . date('Y-m-d H:i:s') . '] CONTAINER ID ::: ' . $output . "\nRequest Data: " . json_encode($_REQUEST) . " \n\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);

                echo json_encode([
                    'status' => true,
                    'message' => "Container updated with restart policy 'always': $output",
                ]);
                header('HTTP/1.1 200 OK');
                exit;
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => "Container with name '$containerName' not found.",
                ]);
                http_response_code(404);
                exit;
            }
        } 
        else {
            echo json_encode([
                'status' => false,
                'message' => 'Something went wrong!',
            ]);
            http_response_code(404);
            exit;
        }
    } 
    else {
        echo json_encode([
            'status' => false,
            'message' => 'Docker compose or NGINX configuration files not found!',
        ]);
        http_response_code(404);
        exit;
    }

?>