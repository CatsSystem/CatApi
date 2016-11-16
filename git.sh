#!/bin/sh

Usage()
{
    echo "eg:   ./git.sh -m test" -b branch;
    echo "      -m commit msg";
    echo "      -b branch, default master";
}

branch=master
msg=auto_upload
while getopts "m:b:" arg
do
        case $arg in
             m)
                msg=$OPTARG
                ;;
             b)
                branch=$OPTARG
                ;;
             ?)
                Usage
                exit 1
                ;;
        esac
done

git commit -m "$msg"
git push origin $branch