#!/bin/bash
DATE=$(date +%Y%m%d%H%M)
FILENAME=/export/$DATE.streamadmin.flow1.xlsx
LATEST=/export/latest.streamadmin.flow1.xlsx

echo "==> Creating export: "$FILENAME""
/usr/local/bin/php /srv/website/src/App/CronJob/CronTab.php -t=Export1 > "$FILENAME"

echo "==> Creating symlink to latest export: "$FILENAME""
rm "$LATEST" 2> /dev/null
cd /export || exit && ln -s "$FILENAME" "$LATEST"

MAX_FILES=31
while [ "$(find /export -maxdepth 1 -name "*.xlsx" -type f | wc -l)" -gt "$MAX_FILES" ]
do
TARGET=$(find /export -maxdepth 1 -name "*.xlsx" -type f | sort | head -n 1)
echo "==> Max number of backups (31) reached. Deleting ${TARGET} ..."
rm -rf "${TARGET}"
echo "==> Backup ${TARGET} deleted"
done