#!/bin/bash

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"/../..

ln -fs $ROOT_DIR/project/config/production/nginx/api_frame.conf /etc/nginx/sites-enabled/api_frame
/usr/sbin/service nginx reload

/bin/bash $ROOT_DIR/project/tool/dep_build.sh link
/usr/bin/php $ROOT_DIR/public/cli.php migrate:install
/usr/bin/php $ROOT_DIR/public/cli.php migrate

ln -fs $ROOT_DIR/project/config/production/supervisor/api_frame_queue_worker.conf /etc/supervisor/conf.d/api_frame_queue_worker.conf
/usr/bin/supervisorctl update
/usr/bin/supervisorctl restart api_frame_queue_worker:*

ln -fs $ROOT_DIR/project/config/production/crontab/api_frame /etc/cron.d/api_frame
