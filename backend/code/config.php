<?php

    $servername             = getenv('DB_HOST');
    $username               = getenv('DB_USERNAME');
    $password               = getenv('DB_PASSWORD');
    $database               = getenv('DB_DATABASE');

    $appEnviroment          = getenv('APP_ENV');
    $maxViewers             = getenv('MAX_VIEWERS');
    $hlsFragment            = getenv('HLS_FRAGMENT');
    $hlsPlaylistLength      = getenv('HLS_PLAYLIST_LENGTH');
    $recInterval            = getenv('MAX_STREAMING_TIME');

    $streamStart            = getenv('ON_PUBLISH');
    $streamStop             = getenv('ON_PUBLISH_DONE');
    $streamRecord           = getenv('ON_RECORD_DONE');
    $streamBlocked          = getenv('STREAM_BLOCKED');
    $logURL                 = getenv('STREAM_LOG');
    $mainURL                = getenv('MAIN_SERVER_URL');
    $mainPageURL            = getenv('MAIN_PAGE_URL');

    // Create the connection
    $conn = mysqli_connect($servername, $username, $password, $database);

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>