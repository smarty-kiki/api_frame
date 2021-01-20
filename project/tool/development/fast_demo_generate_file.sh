#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..
ROOT_DIR=`readlink -f $ROOT_DIR`

env=development
event=$1
filenames=$(basename "$2")

if [ "${filenames##*.}" = "yml" ]
then
    (
    if [ "$filenames" = ".relationship.yml" ]
    then
        filenames=`ls $ROOT_DIR/domain/description/`
        event="MODIFY"
    fi

    for filename in $filenames
    do
        entity_name=${filename%.*}


        if [ "$event" = "CREATE" ] || [ "$event" = "MODIFY" ];then
            echo "\033[32mwatch $filename generate \033[0m"

            ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate:reset
            rm -rf $ROOT_DIR/command/migration/tmp/*[0-9]_$entity_name.sql
            echo delete $ROOT_DIR/command/migration/tmp/*_$entity_name.sql success!

            ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php entity:make-from-description --entity_name=$entity_name
            /bin/bash $ROOT_DIR/project/tool/classmap.sh $ROOT_DIR/domain
            ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate -tmp_files

            rm -rf $ROOT_DIR/docs/entity/$entity_name.md
            rm -rf $ROOT_DIR/docs/entity/relationship.md
            grep -v "\(entity/$entity_name.md\)" $ROOT_DIR/docs/sidebar.md > /tmp/sidebar.md
            mv /tmp/sidebar.md $ROOT_DIR/docs/sidebar.md
            echo delete $ROOT_DIR/docs/entity/$entity_name.md success!

            ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php entity:make-docs-from-description --entity_name=$entity_name
            menu_name=`cat $ROOT_DIR/domain/description/$entity_name.yml | head -n 2 | tail -n 1 | cut -d ' ' -f 2`
            /bin/sed -i "/实体关联/a\\ \ \-\ \[$menu_name\]\(entity\/$entity_name\.md\)" $ROOT_DIR/docs/sidebar.md
            echo include $ROOT_DIR/docs/entity/$entity_name.md success!

            rm -rf $ROOT_DIR/controller/$entity_name.php
            grep -v "'\/$entity_name\." $ROOT_DIR/public/index.php > /tmp/index.php
            mv /tmp/index.php $ROOT_DIR/public/index.php
            echo delete $ROOT_DIR/controller/$entity_name.php success!

            ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-from-description --entity_name=$entity_name
            /bin/sed -i "/init\ controller/a\include\ CONTROLLER_DIR\.\'\/$entity_name\.php\'\;" $ROOT_DIR/public/index.php
            echo include $ROOT_DIR/controller/$entity_name.php success!

            rm -rf $ROOT_DIR/docs/api/$entity_name.md
            grep -v "\(api/$entity_name.md\)" $ROOT_DIR/docs/sidebar.md > /tmp/sidebar.md
            mv /tmp/sidebar.md $ROOT_DIR/docs/sidebar.md
            grep -v "\($entity_name\)" $ROOT_DIR/docs/coverpage.md > /tmp/coverpage.md
            mv /tmp/coverpage.md $ROOT_DIR/docs/coverpage.md
            echo delete $ROOT_DIR/docs/api/$entity_name.md success!

            ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-docs-from-description --entity_name=$entity_name
            menu_name=`cat $ROOT_DIR/domain/description/$entity_name.yml | head -n 2 | tail -n 1 | cut -d ' ' -f 2`
            /bin/sed -i "/接口文档/a\\ \ \-\ \[$menu_name\]\(api\/$entity_name\.md\)" $ROOT_DIR/docs/sidebar.md
            /bin/sed -i "/系统的能力/a\\-\ $menu_name管理\ \($entity_name\)" $ROOT_DIR/docs/coverpage.md
            echo include $ROOT_DIR/docs/api/$entity_name.md success!
        fi

        if [ "$event" = "DELETE" ];then
            echo "\033[32mwatch $filename delete \033[0m"

            ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate:reset
            rm -rf $ROOT_DIR/command/migration/tmp/*[0-9]_$entity_name.sql
            echo delete $ROOT_DIR/command/migration/tmp/*_$entity_name.sql success!

            rm -rf $ROOT_DIR/domain/dao/$entity_name.php
            echo delete $ROOT_DIR/domain/dao/$entity_name.php success!
            rm -rf $ROOT_DIR/domain/entity/$entity_name.php
            echo delete $ROOT_DIR/domain/entity/$entity_name.php success!

            rm -rf $ROOT_DIR/docs/entity/$entity_name.md
            rm -rf $ROOT_DIR/docs/entity/relationship.md
            grep -v "\(entity/$entity_name.md\)" $ROOT_DIR/docs/sidebar.md > /tmp/sidebar.md
            mv /tmp/sidebar.md $ROOT_DIR/docs/sidebar.md
            echo delete $ROOT_DIR/docs/entity/$entity_name.md success!

            rm -rf $ROOT_DIR/controller/$entity_name.php
            grep -v "'\/$entity_name\." $ROOT_DIR/public/index.php > /tmp/index.php
            mv /tmp/index.php $ROOT_DIR/public/index.php
            echo delete $ROOT_DIR/controller/$entity_name.php success!

            rm -rf $ROOT_DIR/docs/api/$entity_name.md
            grep -v "\(api/$entity_name.md\)" $ROOT_DIR/docs/sidebar.md > /tmp/sidebar.md
            mv /tmp/sidebar.md $ROOT_DIR/docs/sidebar.md
            grep -v "\($entity_name\)" $ROOT_DIR/docs/coverpage.md > /tmp/coverpage.md
            mv /tmp/coverpage.md $ROOT_DIR/docs/coverpage.md
            echo delete $ROOT_DIR/docs/api/$entity_name.md success!

            /bin/bash $ROOT_DIR/project/tool/classmap.sh $ROOT_DIR/domain
            ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate -tmp_files
        fi

    done

    ) | column -t
fi
