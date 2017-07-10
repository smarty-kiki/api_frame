#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"/../../

sudo docker run --rm -ti -p 80:80 -p 3306:3306 --name micro_service \
    -v $SCRIPT_DIR:/var/www/ms_service \
    -v $SCRIPT_DIR/project/config/development/nginx/micro_api.conf:/etc/nginx/sites-enabled/micro_api.conf \
debian-php-server start
