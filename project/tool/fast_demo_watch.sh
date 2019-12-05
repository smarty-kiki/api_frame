#!/bin/bash

PROJECT_PATH="$(cd "$(dirname $0)" && pwd)"/../..
LOCK_FILE=/tmp/description_watch.lock
env=development

inotifywait -qm -e CREATE -e MODIFY -e DELETE $PROJECT_PATH/domain/description/ | while read -r directory event filenames;do
if [ "${filenames##*.}" = "yml" ]
then
    if [ ! -f $LOCK_FILE ]
    then
        echo $$ > $LOCK_FILE
        (
            if [ "$filenames" = ".relationship.yml" ]
            then
                filenames=`ls $PROJECT_PATH/domain/description/`
                event="MODIFY"
            fi

            for filename in $filenames
            do
                entity_name=${filename%.*}

                ENV=$env /usr/bin/php $PROJECT_PATH/public/cli.php migrate:reset

                rm -rf $PROJECT_PATH/view/$entity_name
                #rm -rf $PROJECT_PATH/domain/dao/$entity_name.php
                #rm -rf $PROJECT_PATH/domain/entity/$entity_name.php
                rm -rf $PROJECT_PATH/command/migration/*_$entity_name.sql
                rm -rf $PROJECT_PATH/controller/$entity_name.php

                grep -v "'\/$entity_name\." $PROJECT_PATH/public/index.php > /tmp/index.php
                mv /tmp/index.php $PROJECT_PATH/public/index.php

                grep -v "'$entity_name'" $PROJECT_PATH/controller/index.php > /tmp/controller_index.php
                mv /tmp/controller_index.php $PROJECT_PATH/controller/index.php

                if [ "$event" = "CREATE" ];then
                    echo $filename $event

                    ENV=$env /usr/bin/php $PROJECT_PATH/public/cli.php entity:make-from-description --entity_name=$entity_name
                    /bin/bash $PROJECT_PATH/project/tool/classmap.sh $PROJECT_PATH/domain
                    ENV=$env /usr/bin/php $PROJECT_PATH/public/cli.php migrate

                    ENV=$env /usr/bin/php $PROJECT_PATH/public/cli.php crud:make-from-description --entity_name=$entity_name
                    /bin/sed -i "/init\ controller/a\include\ CONTROLLER_DIR\.\'\/$entity_name\.php\'\;" $PROJECT_PATH/public/index.php

                    list_url=`cat $PROJECT_PATH/controller/$entity_name.php | head -n 3 | tail -n 1 | cut -d "'" -f 2`
                    menu_name=`cat $PROJECT_PATH/domain/description/$entity_name.yml | head -n 2 | tail -n 1 | cut -d ' ' -f 2`
                    /bin/sed -i "/children/a\\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ [\ 'name'\ =>\ \'$menu_name管理\',\ \'key\'\ =>\ \'$entity_name\',\ \'href\'\ =>\ \'$list_url\',\ ]," $PROJECT_PATH/controller/index.php
                fi

                if [ "$event" = "MODIFY" ];then
                    echo $filename $event

                    ENV=$env /usr/bin/php $PROJECT_PATH/public/cli.php entity:make-from-description --entity_name=$entity_name
                    /bin/bash $PROJECT_PATH/project/tool/classmap.sh $PROJECT_PATH/domain
                    ENV=$env /usr/bin/php $PROJECT_PATH/public/cli.php migrate

                    ENV=$env /usr/bin/php $PROJECT_PATH/public/cli.php crud:make-from-description --entity_name=$entity_name
                    /bin/sed -i "/init\ controller/a\include\ CONTROLLER_DIR\.\'\/$entity_name\.php\'\;" $PROJECT_PATH/public/index.php

                    list_url=`cat $PROJECT_PATH/controller/$entity_name.php | head -n 3 | tail -n 1 | cut -d "'" -f 2`
                    menu_name=`cat $PROJECT_PATH/domain/description/$entity_name.yml | head -n 2 | tail -n 1 | cut -d ' ' -f 2`
                    /bin/sed -i "/children/a\\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ [\ 'name'\ =>\ \'$menu_name管理\',\ \'key\'\ =>\ \'$entity_name\',\ \'href\'\ =>\ \'$list_url\',\ ]," $PROJECT_PATH/controller/index.php
                fi

                if [ "$event" = "DELETE" ];then
                    echo $filename $event

                    /bin/bash $PROJECT_PATH/project/tool/classmap.sh $PROJECT_PATH/domain
                    ENV=$env /usr/bin/php $PROJECT_PATH/public/cli.php migrate
                fi

            done

            rm -rf $LOCK_FILE
        ) &
    fi
fi
done
