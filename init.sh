#!/bin/bash

# Install composer
composer install

composer dump-autoload

# Run supervisor
# Keep one process is running to display log on terminal, "-n" is no-daemon (keep run foreground, daemon is run background) an options of supervisord
/usr/bin/supervisord -n

#exec $@