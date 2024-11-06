<?php

    $query = "SELECT * FROM rtmps WHERE `status` = 1";
    $result = mysqli_query($conn, $query);

    $txt = "";
    $txt .= "version: \"3.9\"\n";
    $txt .= "services:\n";
    $txt .= "  rtmp:\n";
    $txt .= "    container_name: $containerName\n";
    $txt .= "    build: ./\n";
    $txt .= "    ports:\n";
    $txt .= "      - \"\${RTMP_PORT}:$rtmpPort\"\n";
    $txt .= "      - \"\${HTTP_PORT}:$httpPort\"\n";
    $txt .= "    environment:\n";
    $txt .= "      - RTMP_PORT=\${RTMP_PORT}\n";
    $txt .= "      - HTTP_PORT=\${HTTP_PORT}\n";
    $txt .= "      - MAX_VIEWERS=\${MAX_VIEWERS}\n";
    $txt .= "      - MAX_STREAMING_TIME=\${MAX_STREAMING_TIME}\n";
    $txt .= "      - ON_PUBLISH=\${ON_PUBLISH}\n";
    $txt .= "      - ON_PUBLISH_DONE=\${ON_PUBLISH_DONE}\n";
    $txt .= "      - ON_RECORD_DONE=\${ON_RECORD_DONE}\n";
    $txt .= "      - STREAM_BLOCKED=\${STREAM_BLOCKED}\n";
    $txt .= "      - STREAM_LOG=\${STREAM_LOG}\n";
    $txt .= "    volumes:\n";
    $txt .= "      - ./data:/tmp/hls\n";

    // Loop through each row from the database and add a unique HLS directory
    while ($row = mysqli_fetch_assoc($result)) {
        $key = $row['stream_key'];
        
        // $txt .= "      - ./data/hls_$key:/tmp/hls_$key\n";
        $txt .= "      - ./data:/tmp/hls_$key\n";

        // $dirPath = "./data/hls_$key";
        // if (!is_dir($dirPath)) {
        //     mkdir($dirPath, 0777, true);
        // }
    }

    $txt .= "      - ./record:/record\n";
    $txt .= "      - ./timeout.sh:/timeout.sh\n";
    $txt .= "    # entrypoint: /bin/bash -c \"/timeout.sh & nginx -g 'daemon off;'\"\n";
    $txt .= "    restart: always\n";
    $txt .= "    command: /bin/sh -c \"nginx -g 'daemon off;'\"\n";
    $txt .= "    networks:\n";
    $txt .= "      - livestream\n";
    $txt .= "\n";
    $txt .= "networks:\n";
    $txt .= "  livestream:\n";
    $txt .= "    driver: bridge\n";

    // Save to the docker-compose.yml file
    $filename = "./docker-compose.yml";
    if (file_put_contents($filename, $txt)) {
        // echo "Success...";
        // exit;
    } else {
        echo "Failed...";
        exit;
    }

?>
