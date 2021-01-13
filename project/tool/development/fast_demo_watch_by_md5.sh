#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..
ROOT_DIR=`readlink -f $ROOT_DIR`
OLD_MD5_FILE=/tmp/fast_demo_watch_md5.old
NEW_MD5_FILE=/tmp/fast_demo_watch_md5.new
env=development

find $ROOT_DIR/domain/description/ -type f -print0 | xargs -0 md5sum > $OLD_MD5_FILE

generate_file()
{
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
                    echo delete $ROOT_DIR/docs/api/$entity_name.md success!
                    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-docs-from-description --entity_name=$entity_name
                    menu_name=`cat $ROOT_DIR/domain/description/$entity_name.yml | head -n 2 | tail -n 1 | cut -d ' ' -f 2`
                    /bin/sed -i "/接口文档/a\\ \ \-\ \[$menu_name\]\(api\/$entity_name\.md\)" $ROOT_DIR/docs/sidebar.md
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

                    rm -rf $ROOT_DIR/controller/$entity_name.php
                    grep -v "'\/$entity_name\." $ROOT_DIR/public/index.php > /tmp/index.php
                    mv /tmp/index.php $ROOT_DIR/public/index.php
                    echo delete $ROOT_DIR/controller/$entity_name.php success!

                    rm -rf $ROOT_DIR/docs/api/$entity_name.md
                    grep -v "\(api/$entity_name.md\)" $ROOT_DIR/docs/sidebar.md > /tmp/sidebar.md
                    mv /tmp/sidebar.md $ROOT_DIR/docs/sidebar.md
                    echo delete $ROOT_DIR/docs/api/$entity_name.md success!

                    /bin/bash $ROOT_DIR/project/tool/classmap.sh $ROOT_DIR/domain
                    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate -tmp_files
                fi

            done

        ) | column -t
    fi
}



while true
do
    sleep 1
    find $ROOT_DIR/domain/description/ -type f -print0 | xargs -0 md5sum > $NEW_MD5_FILE
    diff_result=`diff -y --suppress-common-lines -W 300 $OLD_MD5_FILE $NEW_MD5_FILE`

    # MODIFY
    diff_line=`echo "$diff_result" | grep '|'`
    if [ "$diff_line" != "" ]
    then
        old_file_name=`echo $diff_line | awk '{print $2}'`
        new_file_name=`echo $diff_line | awk '{print $5}'`
        if [ "$old_file_name" = "$new_file_name" ]
        then
            generate_file MODIFY $new_file_name
        else
            generate_file DELETE $old_file_name
            generate_file CREATE $new_file_name
        fi
    fi

    # DELETE
    delete_line=`echo "$diff_result" | grep '<'`
    if [ "$delete_line" != "" ]
    then
        file_name=`echo $delete_line | awk '{print $2}'`
        generate_file DELETE $file_name
    fi

    # CREATE
    create_line=`echo "$diff_result" | grep '>'`
    if [ "$create_line" != "" ]
    then
        file_name=`echo $create_line | awk '{print $3}'`
        generate_file CREATE $file_name
    fi

    cp $NEW_MD5_FILE $OLD_MD5_FILE
done
