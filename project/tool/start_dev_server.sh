#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"/../../

sudo docker run --rm -ti -p 80:80 -p 3306:3306 --name micro_api \
    -v $SCRIPT_DIR:/var/www/micro_api \
    -v $SCRIPT_DIR/project/config/development/nginx/micro_api.conf:/etc/nginx/sites-enabled/default \
    -v $SCRIPT_DIR/project/config/development/supervisor/queue_worker.conf:/etc/supervisor/conf.d/queue_worker.conf \
kikiyao/debian_php_dev_env start
