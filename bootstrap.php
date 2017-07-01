<?php

define('ROOT_DIR', __DIR__);
define('FRAME_DIR', __DIR__.'/.frame');

include FRAME_DIR.'/function.php';
include FRAME_DIR.'/entity.php';
include FRAME_DIR.'/database/mysql.php';
include FRAME_DIR.'/cache/redis.php';
include FRAME_DIR.'/otherwise.php';

config_dir(ROOT_DIR.'/config');

include ROOT_DIR.'/util/load.php';
include ROOT_DIR.'/domain/load.php';
