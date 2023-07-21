#!/bin/bash
echo "Streamadmin crontab container"

# Setup a cron schedule
echo "* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Detailsserver
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Dynamicnotecards
" > scheduler.txt

echo "Pushing ENV values to cron system"
env >> /etc/environment

echo "Starting up"
# Start the magic
crontab scheduler.txt
cron -f -l 2