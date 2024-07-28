#!/bin/bash
echo "Streamadmin crontab container"

# Setup a cron schedule
echo "
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Detailsserver -d=1 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Detailsserver -d=11 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Detailsserver -d=21 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Detailsserver -d=31 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Detailsserver -d=41 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Detailsserver -d=51 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Dynamicnotecards -d=1 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Dynamicnotecards -d=11 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Dynamicnotecards -d=21 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Dynamicnotecards -d=31 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Dynamicnotecards -d=41 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Dynamicnotecards -d=51 > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Botcommandq -d=1  > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Botcommandq -d=7  > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Botcommandq -d=13  > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Botcommandq -d=19  > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Botcommandq -d=25  > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Botcommandq -d=31  > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Botcommandq -d=37  > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Botcommandq -d=43  > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Botcommandq -d=49  > /proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Botcommandq -d=55  > /proc/1/fd/1 2>/proc/1/fd/2
" > scheduler.txt

echo "Pushing ENV values to cron system"
env >> /etc/environment

echo "Starting up"
# Start the magic
crontab scheduler.txt
cron -f -l 2