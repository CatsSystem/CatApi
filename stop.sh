#!/bin/sh

project_name="async_server"

echo "stop server..."
ps aux | grep $project_name | awk  '{print $2}' | xargs kill -9