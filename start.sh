#!/bin/sh

mode=0
path=$(cd `dirname $0`; pwd)

Usage()
{
    echo "eg:   ./start.sh -r";
    echo "      -r release mode";
}
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
echo "start server..."

if [ $mode = 0 ] ; then
    php $path/main.php
else
    php $path/main.php &
fi

