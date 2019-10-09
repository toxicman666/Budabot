#!/bin/sh
#Crontab for Taratime

botname="Taratime"
botdir="/usr/local/www/omnihq/bots/Taratime"
command="/usr/local/bin/screen -dmS Taratime1 /usr/local/bin/php -f mainloop.php taratime_config.php"

if ps -ax | grep "Taratime1" | grep -v grep
then 
else
	cd $botdir
	$command
fi

exit 0
