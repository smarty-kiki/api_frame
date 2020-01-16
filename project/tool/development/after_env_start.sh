#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..

ENV=development php $ROOT_DIR/public/cli.php migrate:install
ENV=development php $ROOT_DIR/public/cli.php migrate
