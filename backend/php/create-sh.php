<?php

    $txt = "";
    $txt .= "#!/bin/bash";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "# Capture the stream name from the argument";
    $txt .= "\n";
    $txt .= "STREAM_NAME=\$1";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "# API call to notify that the stream ended with retry";
    $txt .= "\n";
    $txt .= "curl --retry 3 --retry-delay 1 -X POST \"$streamBlocked\" \\";
    $txt .= "\n";
    $txt .= "\t-d \"name=\$STREAM_NAME\" \\";
    $txt .= "\n";
    $txt .= "\t-d \"end_time=$(date)\"";
    $txt .= "\n";
    $txt .= "";
    $txt .= "\n";
    $txt .= "# Give time for the API call to complete";
    $txt .= "\n";
    $txt .= "sleep 2";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "# Now terminate the NGINX worker process";
    $txt .= "\n";
    $txt .= "nginx_pid=\$(ps aux | grep 'nginx: worker process' | grep -v grep | awk '{print \$2}')";
    $txt .= "\n";
    $txt .= "kill -9 \$nginx_pid";

    $filename = "./timeout.sh";
    if (file_put_contents($filename, $txt)) {
        // echo "Success...";
        // exit;
    } else {
        echo "Failed...";
        exit;
    }

?>