#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..
ROOT_DIR=`readlink -f $ROOT_DIR`
LOCK_FILE=/tmp/description_watch.lock
env=development

inotifywait -qm -e CREATE -e MODIFY -e DELETE $ROOT_DIR/domain/description/ | while read -r directory event filenames;do
if [ "${filenames##*.}" = "yml" ]
then
    if [ ! -f $LOCK_FILE ]
    then
        echo $$ > $LOCK_FILE
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

                    rm -rf $ROOT_DIR/controller/$entity_name.php
                    grep -v "'\/$entity_name\." $ROOT_DIR/public/index.php > /tmp/index.php
                    mv /tmp/index.php $ROOT_DIR/public/index.php
                    echo delete $ROOT_DIR/controller/$entity_name.php success!

                    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-from-description --entity_name=$entity_name
                    /bin/sed -i "/init\ controller/a\include\ CONTROLLER_DIR\.\'\/$entity_name\.php\'\;" $ROOT_DIR/public/index.php
                    echo include $ROOT_DIR/controller/$entity_name.php success!
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

                    rm -rf $ROOT_DIR/controller/$entity_name.php
                    grep -v "'\/$entity_name\." $ROOT_DIR/public/index.php > /tmp/index.php
                    mv /tmp/index.php $ROOT_DIR/public/index.php
                    echo delete $ROOT_DIR/controller/$entity_name.php success!

                    /bin/bash $ROOT_DIR/project/tool/classmap.sh $ROOT_DIR/domain
                    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate -tmp_files
                fi

            done

            rm -rf $LOCK_FILE
        ) | column -t &
    fi
fi
done
