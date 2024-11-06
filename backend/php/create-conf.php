<?php
        
    $query = "SELECT * FROM rtmps WHERE `status` = 1";
    $result = mysqli_query($conn, $query);

    $txt = "";
    $txt .= "worker_processes auto;";
    $txt .= "\n";
    $txt .= "events {";
    $txt .= "\n";
    $txt .= "\tworker_connections 1024;";
    $txt .= "\n";
    $txt .= "}";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "rtmp {";
    $txt .= "\n";
    $txt .= "\tbuflen 3s;";
    $txt .= "\n";
    $txt .= "\tserver {";
    $txt .= "\n";
    $txt .= "\t\tlisten $rtmpPort;";
    $txt .= "\n";
    $txt .= "\t\tlisten [::]:$rtmpPort ipv6only=on;";
    $txt .= "\n";
    $txt .= "\t\tchunk_size 4096;";
    $txt .= "\n";
    $txt .= "\n";

    $txt .= "\t\tapplication live {";
    $txt .= "\n";
    $txt .= "\t\t\tlive on;";
    $txt .= "\n";
    $txt .= "\t\t\tmeta on;";
    $txt .= "\n";
    $txt .= "\t\t\tinterleave on;";
    $txt .= "\n";
    $txt .= "\t\t\t# max_connections $maxViewers;";
    $txt .= "\n";
    $txt .= "\t\t\t# session_relay on;";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "\t\t\t# HLS settings";
    $txt .= "\n";
    $txt .= "\t\t\thls on;";
    $txt .= "\n";
    $txt .= "\t\t\thls_path /tmp/hls;";
    $txt .= "\n";
    $txt .= "\t\t\thls_fragment 10s;";
    $txt .= "\n";
    $txt .= "\t\t\thls_playlist_length $hlsPlaylistLength;";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "\t\t\t# Video recording";
    $txt .= "\n";
    $txt .= "\t\t\trecord all;";
    $txt .= "\n";
    $txt .= "\t\t\trecord_path record;";
    $txt .= "\n";
    $txt .= "\t\t\trecord_unique on;";
    $txt .= "\n";
    $txt .= "\t\t\trecord_max_size 0;";
    $txt .= "\n";
    $txt .= "\t\t\trecord_suffix .flv;";
    $txt .= "\n";
    $txt .= "\t\t\trecord_interval $recInterval;";
    $txt .= "\n";
    $txt .= "\t\t\trecord_notify on;";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "\t\t\t# Stream callbacks";
    $txt .= "\n";
    $txt .= "\t\t\ton_publish $streamStart;";
    $txt .= "\n";
    $txt .= "\t\t\ton_publish_done $streamStop;";
    $txt .= "\n";
    $txt .= "\t\t\ton_record_done $streamRecord;";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "\t\t\t# on_record_done event";
    $txt .= "\n";
    $txt .= "\t\t\texec_record_done /timeout.sh \$name;";
    $txt .= "\n";
    $txt .= "\t\t}";
    $txt .= "\n";

    while($row = mysqli_fetch_assoc($result)) {
        $key = $row['stream_key'];
        
        $txt .= "\n";
        $txt .= "\t\tapplication live_$key {";
        $txt .= "\n";
        $txt .= "\t\t\tlive on;";
        $txt .= "\n";
        $txt .= "\t\t\tmeta on;";
        $txt .= "\n";
        $txt .= "\t\t\tinterleave on;";
        $txt .= "\n";
        $txt .= "\t\t\t# max_connections $maxViewers;";
        $txt .= "\n";
        $txt .= "\t\t\t# session_relay on;";
        $txt .= "\n";
        $txt .= "\n";
        $txt .= "\t\t\t# HLS settings";
        $txt .= "\n";
        $txt .= "\t\t\thls on;";
        $txt .= "\n";
        $txt .= "\t\t\thls_path /tmp/hls_$key;";
        $txt .= "\n";
        $txt .= "\t\t\thls_fragment 10s;";
        $txt .= "\n";
        $txt .= "\t\t\thls_playlist_length $hlsPlaylistLength;";
        $txt .= "\n";
        $txt .= "\n";
        $txt .= "\t\t\t# Video recording";
        $txt .= "\n";
        $txt .= "\t\t\trecord all;";
        $txt .= "\n";
        $txt .= "\t\t\trecord_path record;";
        $txt .= "\n";
        $txt .= "\t\t\trecord_unique on;";
        $txt .= "\n";
        $txt .= "\t\t\trecord_max_size 0;";
        $txt .= "\n";
        $txt .= "\t\t\trecord_suffix .flv;";
        $txt .= "\n";
        $txt .= "\t\t\trecord_interval $recInterval;";
        $txt .= "\n";
        $txt .= "\t\t\trecord_notify on;";
        $txt .= "\n";
        $txt .= "\n";
        $txt .= "\t\t\t# Stream callbacks";
        $txt .= "\n";
        $txt .= "\t\t\ton_publish $streamStart;";
        $txt .= "\n";
        $txt .= "\t\t\ton_publish_done $streamStop;";
        $txt .= "\n";
        $txt .= "\t\t\ton_record_done $streamRecord;";
        $txt .= "\n";
        $txt .= "\n";
        $txt .= "\t\t\t# on_record_done event";
        $txt .= "\n";
        $txt .= "\t\t\texec_record_done /timeout.sh \$name;";
        $txt .= "\n";
        $txt .= "\t\t}";
        $txt .= "\n";
    }

    $txt .= "\t}";
    $txt .= "\n";
    $txt .= "}";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "http {";
    $txt .= "\n";
    $txt .= "\t# ";
    $txt .= "\n";
    $txt .= "\tsendfile on;";
    $txt .= "\n";
    $txt .= "\ttcp_nopush on;";
    $txt .= "\n";
    $txt .= "\tkeepalive_timeout 65;";
    $txt .= "\n";
    $txt .= "\ttypes_hash_max_size 2048;";
    $txt .= "\n";
    $txt .= "\tinclude /etc/nginx/mime.types;";
    $txt .= "\n";
    $txt .= "\tdefault_type application/octet-stream;";
    $txt .= "\n";
    $txt .= "\t# ";
    $txt .= "\n";
    $txt .= "\ttcp_nodelay on;";
    $txt .= "\n";
    $txt .= "\tserver {";
    $txt .= "\n";
    $txt .= "\t\tlisten $httpPort;";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "\t\tlocation / {";
    $txt .= "\n";
    $txt .= "\t\t\troot /www;";
    $txt .= "\n";
    $txt .= "\t\t}";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "\t\tlocation /hls {";
    $txt .= "\n";
    $txt .= "\t\t\ttypes {";
    $txt .= "\n";
    $txt .= "\t\t\t\tapplication/vnd.apple.mpegurl m3u8;";
    $txt .= "\n";
    $txt .= "\t\t\t\tapplication/octet-stream ts;";
    $txt .= "\n";
    $txt .= "\t\t\t}";
    $txt .= "\n";
    $txt .= "\t\t\troot /tmp;";
    $txt .= "\n";
    $txt .= "\t\t\tadd_header Cache-Control no-cache;";
    $txt .= "\n";
    $txt .= "\t\t\tadd_header Access-Control-Allow-Origin *;";
    $txt .= "\n";
    $txt .= "\t\t}";
    $txt .= "\n";
    $txt .= "\n";
    $txt .= "\t\tlocation /log {";
    $txt .= "\n";
    $txt .= "\t\t\tproxy_pass $logURL;";
    $txt .= "\n";
    $txt .= "\t\t}";
    $txt .= "\n";
    $txt .= "\t}";
    $txt .= "\n";
    $txt .= "}";

    $filename = "./nginx.conf";
    if (file_put_contents($filename, $txt)) {
        // echo "Success...";
        // exit;
    } else {
        echo "Failed...";
        exit;
    }

?>