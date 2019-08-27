#!/bin/bash

ENV=development php /var/www/api_frame/public/cli.php migrate:install
ENV=development php /var/www/api_frame/public/cli.php migrate
