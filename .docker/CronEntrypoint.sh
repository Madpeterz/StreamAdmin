#!/bin/bash
echo "Streamadmin crontab container"

# Setup a cron schedule > /dev/null 2>&1
echo "
* * * * * /usr/bin/php /srv/website/src/App/CronJob/CronTab.php -t DetailsServer
* * * * * /usr/bin/php /srv/website/src/App/CronJob/CronTab.php -t ClientAutoSuspend
* * * * * /usr/bin/php /srv/website/src/App/CronJob/CronTab.php -t ApiRequests
" > scheduler.txt

# Start the magic
crontab scheduler.txt
cron && tail -F /var/log/cron.log