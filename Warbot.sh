#!/bin/sh
#Crontab for Warbot


botname="Warbot"
botdir="/usr/local/www/omnihq/bots/Warbot"
command="/usr/local/bin/screen -dmS Warbot1 /usr/local/bin/php -f mainloop.php warbot_config.php"
command1="/usr/local/bin/screen -dmS Twinkbot1 /usr/local/bin/php -f mainloop.php twinkbot_config.php"
command2="/usr/local/bin/screen -dmS Warleaders1 /usr/local/bin/php -f mainloop.php warleaders_config.php"
command3="/usr/local/bin/screen -dmS Playerbase1 /usr/local/bin/php -f mainloop.php playerbase_config.php"

if ps -ax | grep "Warbot1" | grep -v grep
then 
else
	cd $botdir
	$command
fi


if ps -ax | grep "Warleaders1" | grep -v grep
then
else
        cd $botdir
        $command2
fi


if ps -ax | grep "Twinkbot1" | grep -v grep
then
else
	cd $botdir
	$command1
fi

#if ps -ax | grep "Playerbase1" | grep -v grep
#then
#else
#	cd $botdir
#	$command3
#fi

exit 0
