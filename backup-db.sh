#!/bin/bash
# Daily MySQL backup to S3 for kristineskates.com
# Run via cron: 0 4 * * * /var/www/kristineskates.com/backup-db.sh

set -uo pipefail

PATH=/usr/local/bin:/usr/bin:/bin
DB_NAME="kristineskates"
DB_USER="kristineskates_user"
DB_PASS='w8pKZL4KD0cv65flt0ces6srQ184oFj+JuJIq3JJPHY='
S3_BUCKET="s3://kristineskates-db-backups"
LOCAL_BACKUP_DIR="/var/www/kristineskates.com/storage/backups"
LOCAL_KEEP_DAYS=3
TIMESTAMP=$(date +%Y-%m-%d_%H%M%S)
BACKUP_FILE="${LOCAL_BACKUP_DIR}/kristineskates-${TIMESTAMP}.sql.gz"

mkdir -p "$LOCAL_BACKUP_DIR"

echo "[$(date)] Starting backup..."

# Dump and compress to local backup dir
/usr/bin/mysqldump --no-tablespaces -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_FILE"

echo "[$(date)] Local backup saved: ${BACKUP_FILE}"

# Upload to S3
if /usr/local/bin/aws s3 cp "$BACKUP_FILE" "${S3_BUCKET}/daily/${TIMESTAMP}.sql.gz"; then
    echo "[$(date)] S3 upload successful"
    # Prune local backups older than $LOCAL_KEEP_DAYS days
    find "$LOCAL_BACKUP_DIR" -name "kristineskates-*.sql.gz" -mtime +${LOCAL_KEEP_DAYS} -delete
    echo "[$(date)] Pruned local backups older than ${LOCAL_KEEP_DAYS} days"
else
    echo "[$(date)] WARNING: S3 upload failed — local backup retained at ${BACKUP_FILE}"
fi

# Old S3 backups are auto-deleted by lifecycle rule (30 days)

echo "[$(date)] Backup complete: ${TIMESTAMP}.sql.gz"
