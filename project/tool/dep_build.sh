#!/bin/bash

get_relative_path()
{
    python -c "import os.path; print os.path.relpath('$1', '$2')"
}

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

dep_frame_file()
{
    git_clone $FRAME_REPOSITORY $FRAME_DIR frame
    checkout_branch $FRAME_DIR $BRANCH

    add_gitignore '/frame'
}

dep_frame_link()
{
    FRAME_TMP_DIR=$ROOT_DIR/../frame

    if [ ! -d $FRAME_TMP_DIR ]
    then
        git_clone $FRAME_REPOSITORY $FRAME_TMP_DIR frame
    fi
    checkout_branch $FRAME_TMP_DIR $BRANCH

    ln -fs ../frame $FRAME_DIR
    add_gitignore '/frame'
}

dep_build_file()
{
    SERVICE_NAME=$1
    SERVICE_REPOSITORY=$2
    SERVICE_TMP_DIR=$ROOT_DIR/.dep_tmp_dir

    git_clone $SERVICE_REPOSITORY $SERVICE_TMP_DIR $SERVICE_NAME
    checkout_branch $SERVICE_TMP_DIR $BRANCH

    cp -r $SERVICE_TMP_DIR/client $DEP_CLIENT_DIR/$SERVICE_NAME
    echo "include __DIR__.'/$SERVICE_NAME/load.php';" >> $DEP_CLIENT_DIR/load.php

    cp -r $SERVICE_TMP_DIR/domain $DEP_DOMAIN_DIR/$SERVICE_NAME
    echo "include __DIR__.'/$SERVICE_NAME/load.php';" >> $DEP_DOMAIN_DIR/load.php

    rm -rf $SERVICE_TMP_DIR
}

dep_build_link()
{
    SERVICE_NAME=$1
    SERVICE_REPOSITORY=$2
    SERVICE_TMP_DIR=$ROOT_DIR/../$SERVICE_NAME

    if [ ! -d $SERVICE_TMP_DIR ]
    then
        git_clone $SERVICE_REPOSITORY $SERVICE_TMP_DIR $SERVICE_NAME
    fi
    checkout_branch $SERVICE_TMP_DIR $BRANCH

    ln -fs `get_relative_path $SERVICE_TMP_DIR/client $DEP_CLIENT_DIR` $DEP_CLIENT_DIR/$SERVICE_NAME
    echo "include __DIR__.'/$SERVICE_NAME/load.php';" >> $DEP_CLIENT_DIR/load.php

    ln -fs `get_relative_path $SERVICE_TMP_DIR/domain $DEP_DOMAIN_DIR` $DEP_DOMAIN_DIR/$SERVICE_NAME
    echo "include __DIR__.'/$SERVICE_NAME/load.php';" >> $DEP_DOMAIN_DIR/load.php
}

# ------------------ start --------------------

TYPE=$1
if [ -z "$TYPE" ]
then
    echo "用法 $0 模式 [依赖项目分支]"
    echo "模式:"
    echo ""
    echo "  link        依赖关系和框架以软链的方式引入项目，依赖项目会下载在本项目所在目录中"
    echo "  file        依赖关系和框架以文件方式引入"
    echo ""
    echo "依赖项目分支   可以不传，默认为不切换依赖项目的分支"
    echo ""
    exit
fi

BRANCH=$2

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../..

# ------------------ add frame ------------------
FRAME_DIR=$ROOT_DIR/frame
FRAME_REPOSITORY=https://github.com/smarty-kiki/frame.git

rm -rf $FRAME_DIR
dep_frame_$TYPE

# ------------------ add depend ------------------
DEP_FILE=$ROOT_DIR/dep_service_list
if [ ! -f $DEP_FILE ]
then
    echo "DEP_FILE 不存在，检查 $DEP_FILE"
    exit
fi

DEP_CLIENT_DIR=$ROOT_DIR/dep_client
DEP_DOMAIN_DIR=$ROOT_DIR/dep_domain

for d in /dep_client /dep_domain
do
    dir=$ROOT_DIR/$d
    rm -rf $dir
    mkdir  $dir
    echo "<?php" > $dir/load.php
    echo "" >> $dir/load.php
    add_gitignore $d
done

cat $DEP_FILE | grep -v ^# | while read arg
do
    dep_build_$TYPE $arg
done
