#!/bin/bash

# Capture the stream name from the argument
STREAM_NAME=$1

# API call to notify that the stream ended with retry
curl --retry 3 --retry-delay 1 -X POST "http://44.216.74.109/livestream/local/blocked.php" \
     -d "name=$STREAM_NAME" \
     -d "end_time=$(date)"

# Give time for the API call to complete
sleep 2

# Now terminate the NGINX worker process
nginx_pid=$(ps aux | grep 'nginx: worker process' | grep -v grep | awk '{print $2}')
kill -9 $nginx_pid
