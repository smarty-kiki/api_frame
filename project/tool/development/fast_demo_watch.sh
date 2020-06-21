#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..
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

                ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate:reset

                rm -rf $ROOT_DIR/view/$entity_name
                rm -rf $ROOT_DIR/command/migration/*[0-9]_$entity_name.sql
                rm -rf $ROOT_DIR/controller/$entity_name.php

                grep -v "'\/$entity_name\." $ROOT_DIR/public/index.php > /tmp/index.php
                mv /tmp/index.php $ROOT_DIR/public/index.php

                grep -v "'$entity_name'" $ROOT_DIR/controller/index.php > /tmp/controller_index.php
                mv /tmp/controller_index.php $ROOT_DIR/controller/index.php

                if [ "$event" = "CREATE" ];then
                    echo $filename $event

                    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php entity:make-from-description --entity_name=$entity_name
                    /bin/bash $ROOT_DIR/project/tool/classmap.sh $ROOT_DIR/domain
                    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate

                    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-from-description --entity_name=$entity_name
                    /bin/sed -i "/init\ controller/a\include\ CONTROLLER_DIR\.\'\/$entity_name\.php\'\;" $ROOT_DIR/public/index.php

                    list_url=`cat $ROOT_DIR/controller/$entity_name.php | head -n 3 | tail -n 1 | cut -d "'" -f 2`
                    menu_name=`cat $ROOT_DIR/domain/description/$entity_name.yml | head -n 2 | tail -n 1 | cut -d ' ' -f 2`
                    /bin/sed -i "/children/a\\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ [\ 'name'\ =>\ \'$menu_name管理\',\ \'key\'\ =>\ \'$entity_name\',\ \'href\'\ =>\ \'$list_url\',\ ]," $ROOT_DIR/controller/index.php
                fi

                if [ "$event" = "MODIFY" ];then
                    echo $filename $event

                    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php entity:make-from-description --entity_name=$entity_name
                    /bin/bash $ROOT_DIR/project/tool/classmap.sh $ROOT_DIR/domain
                    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate

                    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-from-description --entity_name=$entity_name
                    /bin/sed -i "/init\ controller/a\include\ CONTROLLER_DIR\.\'\/$entity_name\.php\'\;" $ROOT_DIR/public/index.php

                    list_url=`cat $ROOT_DIR/controller/$entity_name.php | head -n 3 | tail -n 1 | cut -d "'" -f 2`
                    menu_name=`cat $ROOT_DIR/domain/description/$entity_name.yml | head -n 2 | tail -n 1 | cut -d ' ' -f 2`
                    /bin/sed -i "/children/a\\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ [\ 'name'\ =>\ \'$menu_name管理\',\ \'key\'\ =>\ \'$entity_name\',\ \'href\'\ =>\ \'$list_url\',\ ]," $ROOT_DIR/controller/index.php
                fi

                if [ "$event" = "DELETE" ];then
                    echo $filename $event

                    rm -rf $ROOT_DIR/domain/dao/$entity_name.php
                    rm -rf $ROOT_DIR/domain/entity/$entity_name.php

                    /bin/bash $ROOT_DIR/project/tool/classmap.sh $ROOT_DIR/domain
                    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate
                fi

            done

            rm -rf $LOCK_FILE
        ) &
    fi
fi
done
