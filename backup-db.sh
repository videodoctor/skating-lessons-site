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

# Delete S3 backups older than 30 days
aws s3 ls "${S3_BUCKET}/daily/" | while read -r line; do
  FILE_DATE=$(echo "$line" | awk '{print $1}')
  FILE_NAME=$(echo "$line" | awk '{print $4}')
  if [ -n "$FILE_DATE" ] && [ -n "$FILE_NAME" ]; then
    FILE_EPOCH=$(date -d "$FILE_DATE" +%s 2>/dev/null || echo 0)
    CUTOFF_EPOCH=$(date -d "30 days ago" +%s)
    if [ "$FILE_EPOCH" -gt 0 ] && [ "$FILE_EPOCH" -lt "$CUTOFF_EPOCH" ]; then
      aws s3 rm "${S3_BUCKET}/daily/${FILE_NAME}"
      echo "  Deleted old backup: ${FILE_NAME}"
    fi
  fi
done

echo "[$(date)] Backup complete: ${TIMESTAMP}.sql.gz"
