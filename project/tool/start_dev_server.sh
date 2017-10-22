#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"/../../

sudo docker run --rm -ti -p 80:80 -p 3306:3306 --name api_frame \
    -v $SCRIPT_DIR:/var/www/api_frame \
    -v $SCRIPT_DIR/project/config/development/nginx/api_frame.conf:/etc/nginx/sites-enabled/default \
    -v $SCRIPT_DIR/project/config/development/supervisor/queue_worker.conf:/etc/supervisor/conf.d/queue_worker.conf \
kikiyao/debian_php_dev_env start
