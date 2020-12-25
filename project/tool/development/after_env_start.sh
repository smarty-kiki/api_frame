#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..

mysql -e "create database \`default\`;\
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'password';\
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY 'password'"

ENV=development php $ROOT_DIR/public/cli.php migrate:install
ENV=development php $ROOT_DIR/public/cli.php migrate
