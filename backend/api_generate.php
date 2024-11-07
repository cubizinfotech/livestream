<?php

    header("Content-Type: application/json");

    $logFile = './log/api.log';
    $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $_SERVER['REQUEST_METHOD'] . ' ::: ' . json_encode($_REQUEST) . " \n\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

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
    $timeoutFile = './timeout.sh';

    if($appEnviroment != 'local') {
        chmod($dockerComposeFile, 0777);
        chmod($nginxConfigFile, 0777);
        chmod($timeoutFile, 0777);
    }

    if (file_exists($dockerComposeFile) && file_exists($nginxConfigFile)) {
        if($appEnviroment == 'local') {
            $output = shell_exec('docker-compose -f ' . escapeshellarg($dockerComposeFile) . ' up -d --build 2>&1');
            if ($output) {

                $logFile = './log/api.log';
                $logMessage = '[' . date('Y-m-d H:i:s') . '] OUTPUT if Data ::: ' . $output . "\n==============================\n\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);

                sleep(1);
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
                    $logFile = './log/api.log';
                    $logMessage = '[' . date('Y-m-d H:i:s') . '] CONTAINER ID else ::: ' . $containerId . "\nRequest Data: " . json_encode($_REQUEST) . " \n\n";
                    file_put_contents($logFile, $logMessage, FILE_APPEND);

                    echo json_encode([
                        'status' => false,
                        'message' => "Container with name '$containerName' not found.",
                    ]);
                    http_response_code(404);
                    exit;
                }
            } 
            else {
                $logFile = './log/api.log';
                $logMessage = '[' . date('Y-m-d H:i:s') . '] OUTPUT else Data ::: ' . $output . "\n==============================\n\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);

                echo json_encode([
                    'status' => false,
                    'message' => 'Something went wrong!',
                ]);
                http_response_code(404);
                exit;
            }
        } else {
            // shell_exec('sudo usermod -aG docker ubuntu');
            $output = shell_exec('sudo docker-compose -f ' . escapeshellarg($dockerComposeFile) . ' up -d --build 2>&1');
            if ($output) {

                $logFile = './log/api.log';
                $logMessage = '[' . date('Y-m-d H:i:s') . '] OUTPUT if Data ::: ' . $output . "\n==============================\n\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);

                sleep(1);
                $containerId = trim(shell_exec("sudo docker ps -q -f \"name=$containerName\""));
                if ($containerId) {

                    $output = shell_exec("sudo docker update --restart=always $containerId 2>&1");

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
                    $logFile = './log/api.log';
                    $logMessage = '[' . date('Y-m-d H:i:s') . '] CONTAINER ID else ::: ' . $containerId . "\nRequest Data: " . json_encode($_REQUEST) . " \n\n";
                    file_put_contents($logFile, $logMessage, FILE_APPEND);

                    echo json_encode([
                        'status' => false,
                        'message' => "Container with name '$containerName' not found.",
                    ]);
                    http_response_code(404);
                    exit;
                }
            } 
            else {
                $logFile = './log/api.log';
                $logMessage = '[' . date('Y-m-d H:i:s') . '] OUTPUT else Data ::: ' . $output . "\n==============================\n\n";
                file_put_contents($logFile, $logMessage, FILE_APPEND);

                echo json_encode([
                    'status' => false,
                    'message' => 'Something went wrong!',
                ]);
                http_response_code(404);
                exit;
            }
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