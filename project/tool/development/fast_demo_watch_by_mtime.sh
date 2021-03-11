#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..
ROOT_DIR=`readlink -f $ROOT_DIR`
OLD_MTIME_FILE=/tmp/fast_demo_watch_mtime.old
NEW_MTIME_FILE=/tmp/fast_demo_watch_mtime.new

generate_file()
{
    /bin/sh $ROOT_DIR/project/tool/development/fast_demo_generate_file.sh $1 $2
}

generate_file INIT whatever

find $ROOT_DIR/domain/description/ -type f -print0 | xargs -0 stat -c '%y %n' > $OLD_MTIME_FILE

while true
do
    sleep 1
    find $ROOT_DIR/domain/description/ -type f -print0 | xargs -0 stat -c '%y %n' > $NEW_MTIME_FILE
    diff_result=`diff -y --suppress-common-lines -W 300 $OLD_MTIME_FILE $NEW_MTIME_FILE`

    # MODIFY
    diff_line=`echo "$diff_result" | grep '|'`
    if [ "$diff_line" != "" ]
    then
        echo "$diff_line" | while read line
    do
        old_file_name=`echo $line | awk '{print $4}'`
        new_file_name=`echo $line | awk '{print $9}'`
        if [ "$old_file_name" = "$new_file_name" ]
        then
            generate_file MODIFY $new_file_name
        else
            generate_file DELETE $old_file_name
            generate_file CREATE $new_file_name
        fi
    done
    fi

    # DELETE
    delete_line=`echo "$diff_result" | grep '<'`
    if [ "$delete_line" != "" ]
    then
        echo "$delete_line" | while read line
    do
        file_name=`echo $line | awk '{print $4}'`
        generate_file DELETE $file_name
    done
    fi

    # CREATE
    create_line=`echo "$diff_result" | grep '>'`
    if [ "$create_line" != "" ]
    then
        echo "$create_line" | while read line
    do
        file_name=`echo $line | awk '{print $5}'`
        generate_file CREATE $file_name
    done
    fi

    cp $NEW_MTIME_FILE $OLD_MTIME_FILE
done
