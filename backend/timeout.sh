#!/bin/bash
# Stream monitoring script

STREAM_NAME=$1

sleep "${MAX_STREAMING_TIME}"

# Stop the stream by killing the process or using an API
curl -X POST "${STREAM_BLOCKED}" -d "name=$STREAM_NAME"

# Optionally, kill the NGINX worker handling the stream
nginx_pid=$(ps aux | grep 'nginx: worker process' | grep -v grep | awk '{print $2}')
kill -9 $nginx_pid
