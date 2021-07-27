#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..

date > /tmp/php_exception.log
date > /tmp/php_notice.log
date > /tmp/php_module.log
chown www-data:www-data /tmp/php_exception.log
chown www-data:www-data /tmp/php_notice.log
chown www-data:www-data /tmp/php_module.log

mysql -e "create database \`default\`;\
    GRANT ALL PRIVILEGES ON *.* TO 'default'@'%' IDENTIFIED BY 'password';\
    GRANT ALL PRIVILEGES ON *.* TO 'default'@'localhost' IDENTIFIED BY 'password';\
    FLUSH PRIVILEGES"

ENV=development php $ROOT_DIR/public/cli.php migrate:install
ENV=development php $ROOT_DIR/public/cli.php migrate
ENV=development php $ROOT_DIR/public/cli.php migrate -tmp_files

echo ". $ROOT_DIR/project/config/development/bash/cli_complete.bash" >> ~/.bashrc
