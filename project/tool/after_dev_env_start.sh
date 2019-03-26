#!/bin/bash

php /var/www/api_frame/public/cli.php migrate:install
php /var/www/api_frame/public/cli.php migrate
