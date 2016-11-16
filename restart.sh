#!/bin/sh

project_name="playurl_dev"
path=$(cd `dirname $0`; pwd)

Usage()
{
    echo "eg:   ./start.sh -r";
    echo "      -r release mode";
}

mode=0
while getopts "r" arg
do
        case $arg in
             r)
                mode=1
                ;;
             ?)
                Usage
                exit 1
                ;;
        esac
done
ps
echo "stop server..."

ps aux | grep $project_name | awk  '{print $2}' | xargs kill -SIGTERM

echo "wait until the server shutdown..."
sleep 3

echo "start server..."
if [ $mode = 0 ] ; then
    php $path/main.php
else
    php $path/main.php &
fi