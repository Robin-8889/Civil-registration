# Civil Registration System - Backup & Recovery Plan

**Document Version:** 1.0  
**Last Updated:** February 21, 2026  
**System:** Civil Registration Database (Oracle 21c XE)  
**Location:** Tanzania

---

## Table of Contents

1. [Overview](#overview)
2. [Backup Strategy](#backup-strategy)
3. [Backup Methods](#backup-methods)
4. [Recovery Procedures](#recovery-procedures)
5. [Testing & Verification](#testing--verification)
6. [Storage & Retention](#storage--retention)
7. [Disaster Recovery Plan](#disaster-recovery-plan)
8. [Monitoring & Alerts](#monitoring--alerts)

---

## Overview

### Purpose

Civil registration records are irreplaceable legal documents. This backup and recovery plan ensures:
- **Data Protection:** Critical vital statistics are protected from loss
- **Legal Compliance:** Records can be recovered for government audits
- **Business Continuity:** System can be restored within acceptable timeframes
- **Data Integrity:** Backup integrity is regularly verified

### Critical Data Assets

| Asset | Importance | RTO | RPO |
|-------|-----------|-----|-----|
| Birth Records | CRITICAL | 4 hours | 1 hour |
| Marriage Records | CRITICAL | 4 hours | 1 hour |
| Death Records | CRITICAL | 4 hours | 1 hour |
| Audit Logs | HIGH | 24 hours | 4 hours |
| Users & Permissions | HIGH | 8 hours | 1 hour |
| Certificates | MEDIUM | 24 hours | 4 hours |

**RTO** = Recovery Time Objective (max downtime)  
**RPO** = Recovery Point Objective (max data loss acceptable)

---

## Backup Strategy

### Backup Schedule

```
Full Backups:     Weekly (Sunday midnight)
Incremental:      Daily (Monday-Saturday midnight)
Transaction Logs: Hourly (automated)
Application Code: Daily backup to Git
```

### Backup Levels

#### Level 1: Daily Transaction Log Backup
- **Frequency:** Every hour
- **Method:** Oracle automatic archive log backup
- **Retention:** 14 days
- **Location:** `/backup/oracle/logs/`

#### Level 2: Daily Incremental Database Backup
- **Frequency:** Daily at 23:00
- **Method:** Oracle Data Pump (expdp) incremental
- **Retention:** 7 days
- **Size:** ~50-100 MB
- **Location:** `/backup/oracle/incremental/`

#### Level 3: Weekly Full Database Backup
- **Frequency:** Every Sunday at 02:00
- **Method:** Oracle Data Pump (expdp) full export
- **Retention:** 4 weeks (keep current month + 3 previous weeks)
- **Size:** ~100-150 MB
- **Location:** `/backup/oracle/full/`

#### Level 4: Monthly Archive Backup
- **Frequency:** First Sunday of each month
- **Method:** Compressed full backup + archived logs
- **Retention:** 12 months (full calendar year)
- **Size:** ~50 MB (compressed)
- **Location:** `/backup/archive/months/`

#### Level 5: Application Code Backup
- **Frequency:** Daily (automated via Git)
- **Method:** Git repository push to remote
- **Retention:** Unlimited (version control)
- **Location:** GitHub repository

---

## Backup Methods

### Method 1: Data Pump Export (Logical Backup)

**Advantages:**
- Platform-independent
- Selective table export possible
- Can be restored to different schema
- Easy for data migration

**Full Database Backup:**

```bash
# Create backup directory
mkdir -p /backup/oracle/full

# Export full database
expdp system/oracle directory=backup_dir \
    dumpfile=civil_reg_full_$(date +%Y%m%d).dmp \
    logfile=civil_reg_full_$(date +%Y%m%d).log \
    full=y \
    parallel=4 \
    compression=all

# Verify backup
ls -lh /backup/oracle/full/
```

**Incremental Backup (Changes only):**

```bash
mkdir -p /backup/oracle/incremental

expdp system/oracle directory=backup_dir \
    dumpfile=civil_reg_incr_$(date +%Y%m%d).dmp \
    logfile=civil_reg_incr_$(date +%Y%m%d).log \
    flashback_time="TO_TIMESTAMP('$(date '+%Y-%m-%d %H:%M:%S') - 1', 'YYYY-MM-DD HH24:MI:SS')" \
    parallel=4
```

**Table-Specific Backup:**

```bash
# Backup only critical tables
expdp system/oracle directory=backup_dir \
    dumpfile=civil_vital_records_$(date +%Y%m%d).dmp \
    tables=birth_records,marriage_records,death_records,audit_logs \
    parallel=4
```

### Method 2: RMAN Backup (Oracle Recovery Manager)

**Advantages:**
- Physical backup (block-level)
- Incremental backups very fast
- Can perform point-in-time recovery
- Built into Oracle

**Configuration:**

```sql
-- Configure RMAN backup destination
CONFIGURE DEFAULT DEVICE TYPE TO DISK;
CONFIGURE DEVICE TYPE DISK PARALLELIZATION 4;
CONFIGURE CONTROLFILE AUTOBACKUP ON;
CONFIGURE CONTROLFILE AUTOBACKUP FORMAT FOR DEVICE TYPE DISK 
    TO '/backup/oracle/controlfile/c-%F';
```

**Full Backup:**

```bash
rman target sys/oracle << EOF
BACKUP DATABASE 
    PLUS ARCHIVELOG 
    FORMAT '/backup/oracle/rman/db_%d_%U.bkp';
BACKUP CURRENT CONTROLFILE 
    FORMAT '/backup/oracle/rman/control_%U.bkp';
EOF
```

**Incremental Backup:**

```bash
rman target sys/oracle << EOF
BACKUP INCREMENTAL LEVEL 1 DATABASE 
    FORMAT '/backup/oracle/rman/incr_%d_%U.bkp';
EOF
```

### Method 3: File System Snapshots (if using storage arrays)

**Advantages:**
- Instant snapshots
- Space-efficient
- Can mount snapshots for recovery testing

**Process:**
1. Quiesce database (make read-only)
2. Create storage snapshot
3. Resume database operations
4. Store snapshot reference

---

## Recovery Procedures

### Scenario 1: Complete Database Loss (Catastrophic Failure)

**Recovery Time: 2-4 hours**

#### Step 1: Restore Database Container

```bash
# Stop Oracle
sqlplus / as sysdba
SHUTDOWN ABORT;
EXIT;

# Restore data files from Level 3 backup (latest full backup)
cd /backup/oracle/full
impdp system/oracle directory=backup_dir \
    dumpfile=civil_reg_full_20260221.dmp \
    logfile=recovery_$(date +%Y%m%d_%H%M%S).log \
    full=y \
    table_exists_action=truncate

# Verify restoration
sqlplus / as sysdba
SELECT COUNT(*) FROM birth_records;
SELECT COUNT(*) FROM marriage_records;
SELECT COUNT(*) FROM death_records;
EXIT;
```

#### Step 2: Restore Recent Transactions (if available)

```bash
# Apply incremental backups from Level 2 (if newer than full backup)
# This recovers data since the last full backup
impdp system/oracle directory=backup_dir \
    dumpfile=civil_reg_incr_20260221.dmp \
    logfile=recovery_incr_$(date +%Y%m%d_%H%M%S).log \
    table_exists_action=replace
```

#### Step 3: Verify Data Integrity

```bash
# Check audit logs for data integrity
sqlplus / as sysdba << EOF
SELECT COUNT(*) as total_births FROM birth_records 
WHERE status IN ('pending', 'registered');

SELECT COUNT(*) as total_deaths FROM death_records 
WHERE status IN ('pending', 'registered');

SELECT COUNT(*) as total_marriages FROM marriage_records 
WHERE status IN ('pending', 'registered');

SELECT MAX(created_at) as last_transaction FROM audit_logs;
EXIT;
EOF
```

#### Step 4: Restart Application Services

```bash
# Restart Laravel application
cd /path/to/civil-registration
php artisan migrate
php artisan cache:clear
php artisan config:cache
systemctl restart apache2 (or php-fpm)
```

### Scenario 2: Recent Data Corruption (Last 24 Hours)

**Recovery Time: 1-2 hours**

#### Step 1: Identify Corruption Point

```sql
-- Check audit logs for abnormal activities
SELECT * FROM audit_logs 
WHERE created_at > TRUNC(SYSDATE) - 1 
ORDER BY created_at DESC;

-- Check last valid transaction
SELECT * FROM audit_logs 
WHERE action = 'created' 
ORDER BY created_at DESC 
FETCH FIRST 10 ROWS ONLY;
```

#### Step 2: Recover from Point-in-Time

```bash
# If using RMAN with archivelog mode
rman target sys/oracle << EOF
STARTUP MOUNT;
RECOVER DATABASE UNTIL TIME '2026-02-20 12:00:00';
ALTER DATABASE OPEN RESETLOGS;
EXIT;
EOF
```

#### Step 3: Verify Recovery

```sql
-- Validate record counts match yesterday's backup
-- Compare with level 2 incremental backup from previous day
sqlplus / as sysdba << EOF
SELECT COUNT(*) FROM birth_records WHERE created_at > TRUNC(SYSDATE) - 2;
SELECT COUNT(*) FROM audit_logs WHERE created_at > TRUNC(SYSDATE) - 2;
EXIT;
EOF
```

### Scenario 3: Single Table Loss (Accidental Deletion)

**Recovery Time: 30 minutes - 1 hour**

#### Step 1: Restore Single Table

```bash
# Use Data Pump to restore only the affected table
impdp system/oracle directory=backup_dir \
    dumpfile=civil_reg_full_20260216.dmp \
    logfile=restore_table_$(date +%Y%m%d_%H%M%S).log \
    tables=marriage_records \
    table_exists_action=replace
```

#### Step 2: Verify Restoration

```sql
SELECT COUNT(*) FROM marriage_records;
SELECT marriage_certificate_no FROM marriage_records 
ORDER BY created_at DESC FETCH FIRST 10 ROWS ONLY;
```

#### Step 3: Check for Data Consistency

```sql
-- Verify foreign key relationships
SELECT COUNT(*) FROM marriage_records mr 
WHERE NOT EXISTS (
    SELECT 1 FROM birth_records br WHERE br.id = mr.groom_id
);

-- If count > 0, there's a data integrity issue
```

### Scenario 4: Partial Data Loss (Corruption in Specific Records)

**Recovery Time: 2-4 hours**

#### Manual Recovery Process:

```sql
-- 1. Identify corrupted records
SELECT * FROM birth_records 
WHERE date_of_birth > SYSDATE 
   OR registration_date < date_of_birth;

-- 2. Backup current data
CREATE TABLE birth_records_backup_20260221 AS 
SELECT * FROM birth_records;

-- 3. Restore from backup
DELETE FROM birth_records 
WHERE id IN (SELECT id FROM birth_records WHERE date_of_birth > SYSDATE);

-- 4. Reload from most recent clean backup
impdp system/oracle directory=backup_dir \
    dumpfile=civil_reg_full_20260215.dmp \
    query="WHERE id IN (SELECT id FROM corrupted_records)"
    table_exists_action=replace;

-- 5. Verify restored data
SELECT COUNT(*) FROM birth_records;
```

---

## Testing & Verification

### Monthly Backup Verification (CRITICAL)

**First Monday of each month - 2 hour maintenance window**

```bash
#!/bin/bash
# backup-verify.sh

BACKUP_DIR="/backup/oracle/full"
LATEST_DUMP=$(ls -t ${BACKUP_DIR}/*.dmp | head -1)
TEST_SCHEMA="civil_reg_test"

echo "Testing restore from: $LATEST_DUMP"

# Test import to temporary schema
impdp system/oracle directory=backup_dir \
    dumpfile=$(basename $LATEST_DUMP) \
    remap_schema=civil_app:${TEST_SCHEMA} \
    logfile=test_restore_$(date +%Y%m%d_%H%M%S).log

# Validate record counts
sqlplus / as sysdba << EOF
SET HEADING OFF FEEDBACK OFF VERIFY OFF TRIMSPOOL ON
SELECT COUNT(*) FROM ${TEST_SCHEMA}.birth_records;
SELECT COUNT(*) FROM ${TEST_SCHEMA}.marriage_records;
SELECT COUNT(*) FROM ${TEST_SCHEMA}.death_records;
EOF

# Cleanup test schema
sqlplus / as sysdba << EOF
DROP USER ${TEST_SCHEMA} CASCADE;
EOF

echo "Backup verification completed successfully"
```

### Quarterly Disaster Recovery Drill

**First week of each quarter - 4 hour drill**

1. **Simulate** complete database loss
2. **Recover** from Level 3 backup + Level 2 increments
3. **Validate** data integrity
4. **Measure** actual recovery time
5. **Document** any issues found
6. **Update** recovery procedures if needed

---

## Storage & Retention

### Backup Storage Locations

```
Production Backups:
├── /backup/oracle/logs/          (Transaction logs - 14 days)
├── /backup/oracle/incremental/   (Daily increments - 7 days)
├── /backup/oracle/full/          (Weekly full - 4 weeks)
└── /backup/archive/months/       (Monthly archives - 12 months)

Off-Site Storage:
├── Cloud Backup (AWS S3)          (Automated daily push)
├── External Hard Drive            (Quarterly manual copy)
└── Data Center Vault              (Annual legal archive)
```

### Backup Naming Convention

```
civil_reg_[TYPE]_[SCOPE]_[DATE].dmp
  └─ TYPE: full, incr, weekly, monthly
  └─ SCOPE: all, births, marriages, deaths, audit
  └─ DATE: YYYYMMDD_HHMMSS
  
Example: civil_reg_full_all_20260221_020000.dmp
```

### Retention Schedule

| Backup Type | Retention Period | Storage Location | Purpose |
|------------|-----------------|-----------------|---------|
| Transaction Logs | 14 days | `/backup/oracle/logs/` | Point-in-time recovery |
| Incremental | 7 days | `/backup/oracle/incremental/` | Fast recovery within week |
| Weekly Full | 4 weeks | `/backup/oracle/full/` | General recovery |
| Monthly Archive | 12 months | `/backup/archive/months/` | Legal compliance, audits |
| Annual Archive | 7 years | External vault | Government archival |

---

## Disaster Recovery Plan

### RTO & RPO Summary

| Failure Type | RTO | RPO | Method |
|-------------|-----|-----|--------|
| Transaction Log Full | 1 hour | 1 hour | Archive old logs |
| Single Table Loss | 1 hour | 4 hours | Data Pump table restore |
| Database Corruption | 2 hours | 1 hour | Full restore + increments |
| Complete Database Loss | 4 hours | 1 hour | Full restore + increments |
| Data Center Failure | 24 hours | 24 hours | AWS S3 restore to new location |

### Priority Order for Recovery

```
1. Restore audit logs (ensures legal compliance)
2. Restore critical records (births, marriages, deaths)
3. Restore certificates
4. Restore user accounts
5. Verify data integrity
6. Bring application online
7. Notify stakeholders
```

### Communication Plan

| When | Who | What |
|------|-----|------|
| T+15 min | IT Manager | Assess failure severity |
| T+30 min | System Admin | Begin recovery procedure |
| T+1 hour | Director | Status update to leadership |
| T+2 hours | All Staff | Email update on progress |
| T+4 hours | All Staff | System back online notification |
| T+24 hours | Director | Post-incident analysis report |

---

## Monitoring & Alerts

### Automated Backup Monitoring

```sql
-- Daily backup verification job (runs at 08:00)
CREATE OR REPLACE PROCEDURE check_backup_status AS
BEGIN
    -- Check last backup
    IF TRUNC(SYSDATE) - (SELECT MAX(backup_start_time) 
        FROM v$rman_backup_job_details) > 1 THEN
        -- Send alert email
        UTL_MAIL.SEND(
            sender => 'system@civil-registration.tz',
            recipients => 'admin@civil-registration.tz',
            subject => 'ALERT: Backup Failed',
            message => 'No backup completed in last 24 hours'
        );
    END IF;
END;
/

-- Schedule job
BEGIN
    DBMS_SCHEDULER.CREATE_JOB (
        job_name => 'CHECK_BACKUP_STATUS',
        job_type => 'STORED_PROCEDURE',
        job_action => 'check_backup_status',
        repeat_interval => 'FREQ=DAILY;BYHOUR=8'
    );
    DBMS_SCHEDULER.ENABLE('CHECK_BACKUP_STATUS');
END;
/
```

### Key Metrics to Monitor

1. **Backup Success Rate** - Target: 100% (zero missed backups)
2. **Backup Duration** - Full backup < 30 minutes
3. **Backup Size** - Monitor growth trends
4. **Restore Test Success** - Monthly tests must pass 100%
5. **Storage Utilization** - Alert at 80% capacity

### Health Checks

```bash
#!/bin/bash
# Run weekly
echo "=== Backup Health Check ==="
echo "Last Full Backup:"
ls -lh /backup/oracle/full/ | tail -1

echo -e "\nLast Incremental Backup:"
ls -lh /backup/oracle/incremental/ | tail -1

echo -e "\nStorage Usage:"
df -h /backup/

echo -e "\nArchive Log Status:"
sqlplus /nolog <<EOF
CONNECT / AS SYSDBA
ARCHIVE LOG LIST;
EXIT;
EOF

echo "=== Check Complete ==="
```

---

## Appendix: Technical Details

### Oracle Database Configuration for Backup

```sql
-- Enable archivelog mode (required for recovery)
SHUTDOWN IMMEDIATE;
STARTUP MOUNT;
ALTER DATABASE ARCHIVELOG;
ALTER DATABASE OPEN;

-- Verify mode
ARCHIVE LOG LIST;
-- Should show: "Database log mode: Archive Mode"

-- Set backup destination
ALTER SYSTEM SET DB_RECOVERY_FILE_DEST='/backup/oracle/fra' 
SCOPE=BOTH;

-- Enable automatic backups
ALTER SYSTEM SET DB_RECOVERY_FILE_DEST_SIZE=500G SCOPE=BOTH;
```

### Laravel Application Backup

```bash
# Backup Laravel application code
cd /path/to/civil-registration

# Create git backup
git add -A
git commit -m "Backup: $(date '+%Y-%m-%d %H:%M:%S')"
git push origin main

# Backup .env and other configs
tar -czf /backup/app/config_$(date +%Y%m%d).tar.gz .env config/
```

### Restore Checklist

- [ ] Database restored and verified
- [ ] Application code restored/rolled back
- [ ] Configuration files correct
- [ ] Audit logs functional
- [ ] Application services restarted
- [ ] All endpoints responding
- [ ] Authentication working
- [ ] Data integrity verified
- [ ] Staff notified of restoration

---

## Contact & Escalation

| Role | Contact | Phone |
|------|---------|-------|
| System Administrator | admin@civil-registration.tz | +255-XXX-XXXXXX |
| Database Manager | dba@civil-registration.tz | +255-XXX-XXXXXX |
| IT Director | director@civil-registration.tz | +255-XXX-XXXXXX |
| Backup Vendor Support | support@backup-provider.com | Vendor hotline |

---

**Document Classification:** Internal Use Only  
**Approval:** IT Director, Database Administrator  
**Review Frequency:** Quarterly  
**Last Review Date:** February 21, 2026  
**Next Review Date:** May 21, 2026
