#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..
env=development

filenames=`ls $ROOT_DIR/domain/description/`

for filename in $filenames
do
  entity_name=${filename%.*}

  ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate:reset

  rm -rf $ROOT_DIR/command/migration/tmp/*[0-9]_$entity_name.sql
  echo delete $ROOT_DIR/command/migration/tmp/*_$entity_name.sql success!

  rm -rf $ROOT_DIR/controller/$entity_name.php
  grep -v "'\/$entity_name\." $ROOT_DIR/public/index.php > /tmp/index.php
  mv /tmp/index.php $ROOT_DIR/public/index.php

  grep -v "'$entity_name'" $ROOT_DIR/controller/index.php > /tmp/controller_index.php
  mv /tmp/controller_index.php $ROOT_DIR/controller/index.php
  echo delete $ROOT_DIR/controller/$entity_name.php success!

  ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php entity:make-from-description --entity_name=$entity_name
  /bin/bash $ROOT_DIR/project/tool/classmap.sh $ROOT_DIR/domain
  ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate -tmp_files

  ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-from-description --entity_name=$entity_name
  /bin/sed -i "/init\ controller/a\include\ CONTROLLER_DIR\.\'\/$entity_name\.php\'\;" $ROOT_DIR/public/index.php
  echo include $ROOT_DIR/controller/$entity_name.php success!

  rm -rf $ROOT_DIR/docs/api/$entity_name.md
  grep -v "\(api/$entity_name.md\)" $ROOT_DIR/docs/sidebar.md > /tmp/sidebar.md
  mv /tmp/sidebar.md $ROOT_DIR/docs/sidebar.md
  echo delete $ROOT_DIR/docs/api/$entity_name.md success!
  ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-docs-from-description --entity_name=$entity_name
  menu_name=`cat $ROOT_DIR/domain/description/$entity_name.yml | head -n 2 | tail -n 1 | cut -d ' ' -f 2`
  /bin/sed -i "/接口文档/a\\ \ \-\ \[$menu_name\]\(api\/$entity_name\.md\)" $ROOT_DIR/docs/sidebar.md
  echo include $ROOT_DIR/docs/api/$entity_name.md success!
done
