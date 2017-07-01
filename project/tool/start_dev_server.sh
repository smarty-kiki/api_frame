#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"/../../

sudo docker run --rm -ti -p 80:80 --name micro_service \
    -v $SCRIPT_DIR:/var/www/ms_service \
    -v $SCRIPT_DIR/project/config/development/nginx/micro_service.conf:/etc/nginx/sites-enabled/micro_service.conf \
debian-php-server start
