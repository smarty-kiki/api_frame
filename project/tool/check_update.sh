#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../

BH=`git log -1 --format="%H"`
git pull origin master
git checkout -f master
AH=`git log -1 --format="%H"`

if [ $BH != $AH ];then
    /bin/bash $ROOT_DIR/project/tool/after_push.sh
fi
