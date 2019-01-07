#!/bin/bash

alias phpunit='phpunit --colors=auto'

TEST_FILE=$1
if [ -z "$TEST_FILE" ]
then
    echo "sh $0 unit_test_file"
    exit
fi

clear
phpunit $TEST_FILE;

inotifywait -qm -e CREATE -e MODIFY $TEST_FILE | while read -r directory event filename;
do
    clear;
    phpunit $directory;
done
