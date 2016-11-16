#!/bin/sh

project_name="playurl_dev"

echo "stop server..."
ps aux | grep $project_name | awk  '{print $2}' | xargs kill -9