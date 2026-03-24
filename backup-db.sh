#!/bin/bash
# Daily MySQL backup to S3 for kristineskates.com
# Run via cron: 0 4 * * * /var/www/kristineskates.com/backup-db.sh

set -euo pipefail

DB_NAME="kristineskates"
DB_USER="kristineskates_user"
DB_PASS='w8pKZL4KD0cv65flt0ces6srQ184oFj+JuJIq3JJPHY='
S3_BUCKET="s3://kristineskates-db-backups"
TIMESTAMP=$(date +%Y-%m-%d_%H%M%S)
BACKUP_FILE="/tmp/kristineskates-${TIMESTAMP}.sql.gz"

echo "[$(date)] Starting backup..."

# Dump and compress
mysqldump --no-tablespaces -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_FILE"

# Upload to S3
aws s3 cp "$BACKUP_FILE" "${S3_BUCKET}/daily/${TIMESTAMP}.sql.gz"

# Clean up local file
rm -f "$BACKUP_FILE"

# Old backups are auto-deleted by S3 lifecycle rule (30 days)

echo "[$(date)] Backup complete: ${TIMESTAMP}.sql.gz"
