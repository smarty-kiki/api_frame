#!/bin/bash

function sed_name
{
    cat $1 | sed -e "s/mvc_frame/$2/g" > $1.new && mv $1.new $1
}

if [ ! -n "$1" ] ;then
    echo "Usage: $0 <name>"
    exit
fi

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"/../../

mv $ROOT_DIR/project/config/development/nginx/mvc_frame.conf $ROOT_DIR/project/config/development/nginx/$1.conf
mv $ROOT_DIR/project/config/production/nginx/mvc_frame.conf $ROOT_DIR/project/config/production/nginx/$1.conf

sed_name $ROOT_DIR/project/config/development/nginx/hrm.conf $1
sed_name $ROOT_DIR/project/config/development/supervisor/queue_worker.conf $1
sed_name $ROOT_DIR/project/config/production/caddy/caddy_file $1
sed_name $ROOT_DIR/project/config/production/nginx/hrm.conf $1
sed_name $ROOT_DIR/project/config/production/supervisor/queue_worker.conf $1
sed_name $ROOT_DIR/project/tool/start_dev_server.sh $1
