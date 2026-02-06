#!/bin/bash
#
#  此脚本会粗暴杀掉队列 worker，只建议在开发环境使用
#

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..
ROOT_DIR=`readlink -f $ROOT_DIR`
OLD_MD5_FILE=/tmp/queue_job_watch_md5.old
NEW_MD5_FILE=/tmp/queue_job_watch_md5.new

find $ROOT_DIR/command/queue/queue_job/ -type f -print0 | xargs -0 md5sum > $OLD_MD5_FILE

while true
do
    sleep 1

    find $ROOT_DIR/command/queue/queue_job/ -type f -print0 | xargs -0 md5sum > $NEW_MD5_FILE
    diff_result=`diff -y --suppress-common-lines -W 300 $OLD_MD5_FILE $NEW_MD5_FILE`

    # MODIFY
    diff_line=`echo "$diff_result" | grep '|'`
    if [ "$diff_line" != "" ]
    then
        ps aux | grep queue:worker | grep -v grep | awk '{print $2}' | xargs kill -9
    fi

    # DELETE
    delete_line=`echo "$diff_result" | grep '<'`
    if [ "$delete_line" != "" ]
    then
        ps aux | grep queue:worker | grep -v grep | awk '{print $2}' | xargs kill -9
    fi

    # CREATE
    create_line=`echo "$diff_result" | grep '>'`
    if [ "$create_line" != "" ]
    then
        ps aux | grep queue:worker | grep -v grep | awk '{print $2}' | xargs kill -9
    fi

    cp $NEW_MD5_FILE $OLD_MD5_FILE
done
