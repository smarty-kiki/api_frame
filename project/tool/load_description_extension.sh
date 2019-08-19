#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../..

DESCRIPTION_EXTENSION_REPOSITORY=https://github.com/smarty-kiki/frame_description_extension.git
DESCRIPTION_EXTENSION_DIR=$ROOT_DIR/command/description_extension

add_gitignore()
{
    echo "$1" >> $ROOT_DIR/.gitignore
    cat $ROOT_DIR/.gitignore | sort | uniq > $ROOT_DIR/.gitignore.tmp
    mv $ROOT_DIR/.gitignore.tmp $ROOT_DIR/.gitignore
}

checkout_branch()
{
    REPOSITORY_DIR=$1
    TARGET_BRANCH=$2
    if [ -n "$TARGET_BRANCH" ]
    then
        cd $REPOSITORY_DIR
        git fetch origin $TARGET_BRANCH
        git checkout $TARGET_BRANCH
        cd - > /dev/null
    fi
}

git_clone()
{
    git clone $1 $2 || (echo "获取项目 $3 失败" && exit)
}

if [ "$1" != "" ]
then
    DESCRIPTION_EXTENSION_REPOSITORY=$1
fi

if [ ! -d $DESCRIPTION_EXTENSION_DIR ]
then
    git_clone $DESCRIPTION_EXTENSION_REPOSITORY $DESCRIPTION_EXTENSION_DIR frame_description_extension
fi

checkout_branch $DESCRIPTION_EXTENSION_DIR master

add_gitignore '/command/description_extension'
