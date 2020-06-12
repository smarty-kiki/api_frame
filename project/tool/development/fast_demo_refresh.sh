#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..
env=development

filenames=`ls $ROOT_DIR/domain/description/`

for filename in $filenames
do
  entity_name=${filename%.*}

  ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate:reset

  rm -rf $ROOT_DIR/command/migration/*_$entity_name.sql
  rm -rf $ROOT_DIR/controller/$entity_name.php

  grep -v "'\/$entity_name\." $ROOT_DIR/public/index.php > /tmp/index.php
  mv /tmp/index.php $ROOT_DIR/public/index.php

  grep -v "'$entity_name'" $ROOT_DIR/controller/index.php > /tmp/controller_index.php
  mv /tmp/controller_index.php $ROOT_DIR/controller/index.php

  ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php entity:make-from-description --entity_name=$entity_name
  /bin/bash $ROOT_DIR/project/tool/classmap.sh $ROOT_DIR/domain
  ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate

  ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-from-description --entity_name=$entity_name
  /bin/sed -i "/init\ controller/a\include\ CONTROLLER_DIR\.\'\/$entity_name\.php\'\;" $ROOT_DIR/public/index.php
done
