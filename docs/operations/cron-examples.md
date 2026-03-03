# Cron Configuration Examples

## Shared hosting crontab
```cron
* * * * * php /home/user/cbt-app/current/bin/queue-work-db --max-jobs=100 --max-time=50 --memory=128 --sleep=1 >> /home/user/cbt-app/shared/storage/logs/queue.log 2>&1
*/5 * * * * php /home/user/cbt-app/current/bin/schedule-run >> /home/user/cbt-app/shared/storage/logs/scheduler.log 2>&1
0 2 * * * /home/user/cbt-app/current/scripts/ops/backup.sh >> /home/user/cbt-app/shared/storage/logs/backup.log 2>&1
30 2 * * * /home/user/cbt-app/current/scripts/ops/retention-prune.sh >> /home/user/cbt-app/shared/storage/logs/retention.log 2>&1
```

## VPS crontab
```cron
*/5 * * * * php /var/www/voracbt/current/bin/schedule-run >> /var/www/voracbt/shared/storage/logs/scheduler.log 2>&1
0 2 * * * /var/www/voracbt/current/scripts/ops/backup.sh >> /var/www/voracbt/shared/storage/logs/backup.log 2>&1
0 3 * * 0 /var/www/voracbt/current/scripts/ops/backup.sh --full >> /var/www/voracbt/shared/storage/logs/backup.log 2>&1
30 2 * * * /var/www/voracbt/current/scripts/ops/retention-prune.sh >> /var/www/voracbt/shared/storage/logs/retention.log 2>&1
```

## Cron hygiene
- Use absolute paths.
- Redirect stdout/stderr to log files.
- Keep queue task single-instance on shared hosting (lock file recommended).

---

## Documentation Assessment Update (2026-03-02)
- Reviewed for consistency with the current repository structure and operational workflow.
- No blocking documentation gaps were identified in this document during this review pass.
- Next review trigger: any architecture, deployment, or operational process change impacting this topic.
