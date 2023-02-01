#!/bin/bash
echo "Streamadmin crontab container"

# Setup a cron schedule
echo "* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Detailsserver >/proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Clientautosuspend >/proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Apirequests >/proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Dynamicnotecards >/proc/1/fd/1 2>/proc/1/fd/2
* * * * * /usr/local/bin/php /srv/website/src/App/CronTab.php -t=Botcommandq >/proc/1/fd/1 2>/proc/1/fd/2
${EXPORT_CRON_TIME} /srv/website/.docker/Exporter.sh >/proc/1/fd/1 2>/proc/1/fd/2
" > scheduler.txt

echo "Pushing ENV values to cron system"
env >> /etc/environment

echo "Doing startup Export"
/srv/website/.docker/Exporter.sh >/proc/1/fd/1 2>/proc/1/fd/2

echo "Starting up"
# Start the magic
crontab scheduler.txt
cron -f -l 2