#!/bin/bash

# 符合以下几点规范才会被创建进入自动加载文件
# 1. class、abstract、interface 为小写，并且写在行头，与类名中间为一个空格
# 2. 类声明的左括号需另起一行

if [ ! -n "$1" ] ;then
    echo "Usage: $0 <directory>"
    exit
fi

output=autoload.php

cd -P $1 || exit 1

echo "<?php" > $output
echo "" >> $output
echo "spl_autoload_register(function (\$class_name) {" >> $output
echo "" >> $output
echo '    $class_maps = [' >> $output
grep -rE "^(class|abstract class|interface) \S+\s*" * | awk -F ':class |:abstract class |:interface | ' '{if(length($2)>0) printf("        \047%s\047 => \047%s\047,\n",$2,$1)}' >> $output
echo '    ];' >> $output
echo "" >> $output
echo '    if (isset($class_maps[$class_name])) {' >> $output
echo "        include __DIR__.'/'.\$class_maps[\$class_name];" >> $output
echo '    }' >> $output
echo '});' >> $output

echo generate $1/$output success!
