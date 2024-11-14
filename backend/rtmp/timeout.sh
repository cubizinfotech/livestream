#!/bin/bash

# Capture the stream name from the argument
STREAM_NAME=$1

# API call to notify that the stream ended and capture the response
response=$(curl --retry 3 --retry-delay 1 -s -X POST "[STREAM_BLOCKED]" \
     -d "name=$STREAM_NAME" \
     -d "end_time=$(date)")

# Parse the response to check if "is_blocked" is true
is_blocked=$(echo "$response" | grep -o '"is_blocked":true')

# If is_blocked is true, terminate the NGINX worker process
if [ "$is_blocked" == '"is_blocked":true' ]; then
    echo "Stream is blocked. Terminating NGINX worker process."
    
    # Get the NGINX worker process ID and kill it
    nginx_pid=$(ps aux | grep 'nginx: worker process' | grep -v grep | awk '{print $2}')
    kill -9 $nginx_pid
else
    echo "Stream is not blocked. No action taken."
fi

# Give time for the API call to complete
sleep 2
