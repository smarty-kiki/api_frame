#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..

mysql -e "create database \`default\`;\
    GRANT ALL PRIVILEGES ON *.* TO 'default'@'%' IDENTIFIED BY 'password';\
    GRANT ALL PRIVILEGES ON *.* TO 'default'@'localhost' IDENTIFIED BY 'password';\
    FLUSH PRIVILEGES"

ENV=development php $ROOT_DIR/public/cli.php migrate:install
ENV=development php $ROOT_DIR/public/cli.php migrate
