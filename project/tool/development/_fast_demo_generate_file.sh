#!/bin/bash

ROOT_DIR="$(cd "$(dirname $0)" && pwd)"/../../..
ROOT_DIR=`readlink -f $ROOT_DIR`

env=development
event=$1
filenames=$(basename "$2")
all_filenames=`ls $ROOT_DIR/domain/description/`

controller_diff_dir=/tmp/description/controller

echog()
{
    php -r "echo \"\033[32m\"; echo '$1'; echo \"\033[0m\n\";" >&2
}

alias echo_filter='column -t | perl -pe "s/(^migrate|^include)|(^delete|^uninclude|^clean)|(^todo)|(^generate)/\\e[1;34m\$1\\e[0m\\e[1;31m\$2\\e[0m\e[1;30m\$3\\e[0m\e[1;32m\$4\\e[0m/gi"'

if [ "$event" = "INIT" ]
then
    (
    rm -rf $controller_diff_dir
    mkdir -p $controller_diff_dir

    for filename in $all_filenames
    do
        entity_name=${filename%.*}

        controller_file_old=$controller_diff_dir/$entity_name.php.old
        output_controller_file=`ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-from-description --entity_name=$entity_name --output_file=$controller_file_old`
        echo init $output_controller_file success!
    done
    ) | echo_filter
elif [ "${filenames##*.}" = "yml" ]
then

    if [ "$filenames" = ".relationship.yml" ]
    then
        filenames=`ls $ROOT_DIR/domain/description/`
        event="MODIFY"
    fi

    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate:reset | echo_filter

    for filename in $filenames
    do
        (
        entity_name=${filename%.*}

        if [ "$event" = "CREATE" ] || [ "$event" = "MODIFY" ];then
            echog "watch $filename generate"

            rm -rf $ROOT_DIR/command/migration/tmp/*[0-9]_$entity_name.sql
            echo delete $ROOT_DIR/command/migration/tmp/*_$entity_name.sql success!

            entity_files=`ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php entity:make-from-description --entity_name=$entity_name`
            for entity_file in $entity_files; do echo generate $entity_file success!; done

            rm -rf $ROOT_DIR/docs/entity/$entity_name.md
            rm -rf $ROOT_DIR/docs/entity/relationship.md
            grep -v "\(../entity/$entity_name.md\)" $ROOT_DIR/docs/sidebar.md > /tmp/sidebar.md
            mv /tmp/sidebar.md $ROOT_DIR/docs/sidebar.md
            echo delete $ROOT_DIR/docs/entity/$entity_name.md success!

            docs_entity_files=`ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php entity:make-docs-from-description --entity_name=$entity_name`
            for docs_entity_file in $docs_entity_files; do echo generate $docs_entity_file success!; done
            menu_name=`cat $ROOT_DIR/domain/description/$entity_name.yml | head -n 2 | tail -n 1 | cut -d ' ' -f 2`
            /bin/sed -i "/实体关联/a\\ \ \-\ \[$menu_name\]\(../entity\/$entity_name\.md\)" $ROOT_DIR/docs/sidebar.md
            echo include $ROOT_DIR/docs/entity/$entity_name.md success!

            controller_file=$ROOT_DIR/controller/$entity_name.php
            grep -v "'\/$entity_name\." $ROOT_DIR/public/index.php > /tmp/index.php
            mv /tmp/index.php $ROOT_DIR/public/index.php
            echo uninclude $controller_file success!

            controller_file_old=$controller_diff_dir/$entity_name.php.old
            controller_file_new=$controller_diff_dir/$entity_name.php.new
            controller_file_diff=$ROOT_DIR/controller/$entity_name.diff.php
            output_controller_file=$controller_file
            if [ -r $output_controller_file ]
            then
                output_controller_file=$controller_file_new
                output_controller_file=`ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-from-description --entity_name=$entity_name --output_file=$output_controller_file`
            else
                output_controller_file=`ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-from-description --entity_name=$entity_name --output_file=$output_controller_file`
                cp $output_controller_file $controller_file_old
            fi
            echo generate $output_controller_file success!
            if [ -r $controller_file_new ]
            then
                if [ ! -r $controller_file_old ] || test "`diff -u $controller_file $controller_file_old`"
                then
                    cp $controller_file_new $controller_file_old
                    controller_file_diff_str=`diff -u $controller_file_new $controller_file`
                    if test "$controller_file_diff_str"
                    then
                        echo "$controller_file_diff_str" > $controller_file_diff
                        echo generate $controller_file_diff success!
                    fi
                    rm $controller_file_new
                    echo delete $controller_file_new success!
                else
                    cp $controller_file_new $controller_file_old
                    cp $controller_file_new $controller_file
                    echo generate $controller_file success!

                    rm $controller_file_new
                    echo delete $controller_file_new success!
                fi
            fi
            /bin/sed -i "/init\ controller/a\include\ CONTROLLER_DIR\.\'\/$entity_name\.php\'\;" $ROOT_DIR/public/index.php
            echo include $ROOT_DIR/controller/$entity_name.php success!

            error_code_file=`ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-error-code-from-description --entity_name=$entity_name`
            echo generate $error_code_file success!

            error_code_doc_file=`ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-error-code-docs-from-description --entity_name=$entity_name`
            echo generate $error_code_doc_file success!

            rm -rf $ROOT_DIR/docs/api/$entity_name.md
            grep -v "\(../api/$entity_name.md\)" $ROOT_DIR/docs/sidebar.md > /tmp/sidebar.md
            mv /tmp/sidebar.md $ROOT_DIR/docs/sidebar.md
            grep -v "\($entity_name\)" $ROOT_DIR/docs/coverpage.md > /tmp/coverpage.md
            mv /tmp/coverpage.md $ROOT_DIR/docs/coverpage.md
            echo delete $ROOT_DIR/docs/api/$entity_name.md success!

            docs_api_files=`ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php crud:make-docs-from-description --entity_name=$entity_name`
            for docs_api_file in $docs_api_files; do echo generate $docs_api_file success!; done
            menu_name=`cat $ROOT_DIR/domain/description/$entity_name.yml | head -n 2 | tail -n 1 | cut -d ' ' -f 2`
            /bin/sed -i "/接口文档/a\\ \ \-\ \[$menu_name\]\(../api\/$entity_name\.md\)" $ROOT_DIR/docs/sidebar.md
            /bin/sed -i "/系统的能力/a\\-\ $menu_name管理\ \($entity_name\)" $ROOT_DIR/docs/coverpage.md
            echo include $ROOT_DIR/docs/api/$entity_name.md success!
        fi

        if [ "$event" = "DELETE" ];then

            echog "watch $filename delete"

            rm -rf $ROOT_DIR/command/migration/tmp/*[0-9]_$entity_name.sql
            echo delete $ROOT_DIR/command/migration/tmp/*_$entity_name.sql success!

            rm -rf $ROOT_DIR/domain/dao/$entity_name.php
            echo delete $ROOT_DIR/domain/dao/$entity_name.php success!
            rm -rf $ROOT_DIR/domain/entity/$entity_name.php
            echo delete $ROOT_DIR/domain/entity/$entity_name.php success!

            rm -rf $ROOT_DIR/docs/entity/$entity_name.md
            rm -rf $ROOT_DIR/docs/entity/relationship.md
            grep -v "\(../entity/$entity_name.md\)" $ROOT_DIR/docs/sidebar.md > /tmp/sidebar.md
            mv /tmp/sidebar.md $ROOT_DIR/docs/sidebar.md
            echo delete $ROOT_DIR/docs/entity/$entity_name.md success!

            rm -rf $ROOT_DIR/controller/$entity_name.php
            grep -v "'\/$entity_name\." $ROOT_DIR/public/index.php > /tmp/index.php
            mv /tmp/index.php $ROOT_DIR/public/index.php
            echo delete $ROOT_DIR/controller/$entity_name.php success!

            sed -i "/\/\*\ generated\ ${entity_name}\ start\ \*\//,/\/\*\ generated\ ${entity_name}\ end\ \*\//d" $ROOT_DIR/config/error_code.php
            echo clean $ROOT_DIR/config/error_code.php success!

            sed -i "/\[\^\_\^\]:\ ${entity_name}_start/,/\[\^\_\^\]:\ ${entity_name}_end/d" $ROOT_DIR/docs/error_code.md
            echo clean $ROOT_DIR/docs/error_code.md success!

            rm -rf $ROOT_DIR/docs/api/$entity_name.md
            grep -v "\(../api/$entity_name.md\)" $ROOT_DIR/docs/sidebar.md > /tmp/sidebar.md
            mv /tmp/sidebar.md $ROOT_DIR/docs/sidebar.md
            grep -v "\($entity_name\)" $ROOT_DIR/docs/coverpage.md > /tmp/coverpage.md
            mv /tmp/coverpage.md $ROOT_DIR/docs/coverpage.md
            echo delete $ROOT_DIR/docs/api/$entity_name.md success!
        fi

        ) | echo_filter
    done

    /bin/bash $ROOT_DIR/project/tool/classmap.sh $ROOT_DIR/domain | echo_filter
    ENV=$env /usr/bin/php $ROOT_DIR/public/cli.php migrate -tmp_files | echo_filter
fi
