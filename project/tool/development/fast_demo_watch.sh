#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..
ROOT_DIR=`readlink -f $ROOT_DIR`
LOCK_FILE=/tmp/description_watch.lock

inotifywait -qm -e CREATE -e MODIFY -e DELETE $ROOT_DIR/domain/description/ | while read -r directory event filenames;do
if [ "${filenames##*.}" = "yml" ]
then
    if [ ! -f $LOCK_FILE ]
    then
        echo $$ > $LOCK_FILE
        (
        /bin/sh $ROOT_DIR/project/tool/development/fast_demo_generate_file.sh $event $filenames
        rm -rf $LOCK_FILE
        ) &
    fi
fi
done
