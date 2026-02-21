# Civil Registration System - Backup Automation Setup Guide

**Version:** 1.0  
**Created:** February 21, 2026  
**System:** Oracle 21c XE with Laravel Application  

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Files Included](#files-included)
3. [Prerequisites](#prerequisites)
4. [Installation](#installation)
5. [Configuration](#configuration)
6. [Usage](#usage)
7. [Monitoring](#monitoring)
8. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The Backup Automation Setup provides three key components:

- **Backup Automation** - Automated database and code backups
- **Verification** - Testing backup integrity and recoverability
- **Monitoring & Alerts** - Real-time alerts for backup failures

### What Gets Backed Up

✓ **Oracle Database** (Data Pump export format)
- Birth records, Marriage records, Death records
- Audit logs, Certificates, Users
- Complete database schema

✓ **Application Code** (Git repository)
- Laravel application files
- Configuration files
- Version history

---

## 📦 Files Included

| File | Purpose |
|------|---------|
| `backup-config.json` | Master configuration file |
| `backup-automation.ps1` | Main backup script |
| `backup-verify.ps1` | Verification & testing script |
| `setup-scheduled-tasks.ps1` | Windows Task Scheduler setup |
| `backup-alert.ps1` | Monitoring & alerts |
| `BACKUP_SETUP_GUIDE.md` | This file |

---

## ✅ Prerequisites

- **Windows System** (Server 2016+ or Windows 10+)
- **PowerShell 5.1+** (built-in on Windows 10/11/Server 2016+)
- **Oracle 21c XE** installed and running
- **Git** installed (for application code backups)
- **Administrator access** (required for task scheduling)
- **Disk space** (~500+ GB recommended for backup storage)

### Verify Prerequisites

```powershell
# Check PowerShell version
$PSVersionTable.PSVersion

# Check Oracle is running
Get-Service | Where-Object {$_.Name -match "Oracle"}

# Check Git is installed
git --version

# Check current user is Administrator
(whoami /groups | find /i "administrator") -ne $null
```

---

## 🚀 Installation

### Step 1: Create Backup Directories

```powershell
# Run in PowerShell as Administrator
$backupRoot = "C:\xampp\backups"
mkdir $backupRoot\oracle\full
mkdir $backupRoot\oracle\incremental
mkdir $backupRoot\oracle\logs
mkdir $backupRoot\archive
mkdir $backupRoot\logs
```

### Step 2: Verify Configuration

Edit `backup-config.json` and confirm:

```json
{
  "backup": {
    "backupRoot": "C:\\xampp\\backups",
    "backupDirectories": {
      "full": "C:\\xampp\\backups\\oracle\\full",
      "incremental": "C:\\xampp\\backups\\oracle\\incremental",
      ...
    }
  },
  "database": {
    "username": "system",
    "passwordFile": "C:\\xampp\\backups\\.oracle_creds"
  }
}
```

### Step 3: Setup Task Scheduler

**Run as Administrator:**

```powershell
cd C:\xampp\htdocs\civil-registration

# Install scheduled backup tasks
.\setup-scheduled-tasks.ps1

# Verify installation
.\setup-scheduled-tasks.ps1 -ListTasks

# To uninstall (if needed)
.\setup-scheduled-tasks.ps1 -Uninstall
```

---

## ⚙️ Configuration

### backup-config.json Settings

```json
{
  "backup": {
    "enabled": true,                    // Enable/disable backups
    "backupRoot": "C:\\xampp\\backups", // Backup directory root
    "retention": {
      "fullBackup": 28,        // Keep 4 weeks (28 days)
      "incrementalBackup": 7,  // Keep 7 days
      "transactionLogs": 14,   // Keep 2 weeks
      "archiveMonths": 365     // Keep 12 months
    }
  },
  
  "database": {
    "type": "oracle",
    "hostname": "localhost",
    "port": 1521,
    "serviceName": "XE",
    "username": "system",
    "passwordFile": "C:\\xampp\\backups\\.oracle_creds"
  },
  
  "application": {
    "path": "C:\\xampp\\htdocs\\civil-registration",
    "gitEnabled": true,
    "gitBranch": "main",
    "gitRemote": "origin"
  },
  
  "scheduling": {
    "fullBackup": {
      "enabled": true,
      "day": "Sunday",
      "time": "02:00"
    },
    "incrementalBackup": {
      "enabled": true,
      "time": "23:00"
    },
    "verification": {
      "enabled": true,
      "dayOfWeek": "Monday",
      "time": "08:00"
    }
  }
}
```

### Backup Schedule

| Task | Schedule | Frequency | Files Kept |
|------|----------|-----------|-----------|
| Full Backup | Sunday 02:00 | Weekly | 4 weeks |
| Incremental | Daily 23:00 | Every day | 7 days |
| Verification | Monday 08:00 | Monthly | Reports only |

---

## 🔧 Usage

### Manual Full Backup

```powershell
cd C:\xampp\htdocs\civil-registration

# Run full backup immediately
.\backup-automation.ps1 -BackupType full

# Run incremental backup only
.\backup-automation.ps1 -BackupType incremental

# Run both (default)
.\backup-automation.ps1
```

### Run Backup Verification

```powershell
# Quick verification check
.\backup-verify.ps1

# Detailed verification with restore test
.\backup-verify.ps1 -Detailed -TestRestore

# Send verification report via email
.\backup-verify.ps1 -Detailed -SendReport
```

### Monitor Backup Status

```powershell
# One-time status check
.\backup-alert.ps1 -OneTimeCheck

# Continuous monitoring (checks every 5 minutes)
.\backup-alert.ps1

# Continuous monitoring with email alerts
.\backup-alert.ps1 -SendEmail

# Custom check interval (every 30 minutes)
.\backup-alert.ps1 -CheckInterval 1800
```

### View Scheduled Tasks

```powershell
# List all backup tasks
.\setup-scheduled-tasks.ps1 -ListTasks

# View task in GUI
taskschd.msc
# Look for tasks starting with "CivilReg-"

# View task details via PowerShell
Get-ScheduledTask -TaskName "CivilReg-*" | Format-List
```

### Check Backup Files

```powershell
# View recent backups
Get-ChildItem C:\xampp\backups\oracle\full\*.dmp -ErrorAction SilentlyContinue | `
  Sort-Object CreationTime -Descending | Select-Object Name, Length, CreationTime | `
  Format-Table -AutoSize

# Calculate backup size
(Get-ChildItem C:\xampp\backups\oracle\full\*.dmp -ErrorAction SilentlyContinue | `
  Measure-Object -Property Length -Sum).Sum / 1MB
```

---

## 📊 Monitoring

### Log Files

All backup activities are logged to:
- **Backup logs:** `C:\xampp\backups\logs\backup_*.log`
- **Verification logs:** `C:\xampp\backups\logs\backup_verify_*.txt`
- **Alert logs:** `C:\xampp\backups\logs\backup_alerts_*.txt`

### Check Recent Backups

```powershell
# Last full backup
Get-ChildItem C:\xampp\backups\oracle\full\ -Filter "*.dmp" | Sort-Object CreationTime -Descending | Select-Object -First 1

# Last incremental backup
Get-ChildItem C:\xampp\backups\oracle\incremental\ -Filter "*.dmp" | Sort-Object CreationTime -Descending | Select-Object -First 1
```

### Storage Usage

```powershell
# Check total backup size
Get-ChildItem C:\xampp\backups -Recurse -Filter "*.dmp" | `
  Measure-Object -Property Length -Sum | `
  ForEach-Object { "$([math]::Round($_.Sum / 1GB, 2)) GB" }

# Check disk free space
Get-PSDrive -Name C | Select-Object @{N="Free GB";E={[math]::Round($_.Free / 1GB, 2)}}
```

---

## 🔍 Troubleshooting

### Issue: Scripts Don't Run

**Error:** "cannot be loaded because running scripts is disabled"

**Solution:**
```powershell
# Run PowerShell as Administrator
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser -Force

# Verify
Get-ExecutionPolicy
```

### Issue: Oracle Commands Not Found

**Error:** "expdp is not recognized"

**Solution:**
1. Add Oracle to PATH environment variable:
   - `C:\xampp\mysql\bin` should contain Oracle utilities
   - Edit environment variables: Start → "Edit environment variables"
   - Add Oracle binary path to PATH

2. Or use full path in config:
   ```json
   "oracleExepath": "C:\\xampp\\mysql\\bin\\expdp"
   ```

### Issue: Backup File Too Small

**Symptoms:** Backup .dmp file is < 10 MB

**Causes & Solutions:**
1. Database is empty - verify data exists with:
   ```sql
   SELECT COUNT(*) FROM birth_records;
   SELECT COUNT(*) FROM marriage_records;
   ```

2. Insufficient privileges - verify system user has backup rights:
   ```sql
   -- Connect as DBA
   GRANT DATAPUMP_EXP_FULL_DATABASE TO system;
   GRANT DATAPUMP_IMP_FULL_DATABASE TO system;
   ```

### Issue: Task Scheduler Tasks Not Running

**Diagnostic:**
```powershell
# Check task history
Get-ScheduledTaskInfo -TaskName "CivilReg-FullBackup-Weekly" | `
  Select-Object LastRunTime, LastTaskResult

# View last run result (0 = success, non-zero = failure)
# Get task logs from Event Viewer
eventvwr.msc
# Look in: Windows Logs > Application
```

### Issue: Disk Space Warning

**Check usage:**
```powershell
# Show disk usage by backup type
Get-ChildItem C:\xampp\backups -Recurse -File | `
  Group-Object Directory | `
  Select-Object @{N="Directory";E={$_.Name}}, `
           @{N="Files";E={$_.Count}}, `
           @{N="Size(GB)";E={[math]::Round(($_.Group | Measure-Object Length -Sum).Sum / 1GB, 2)}}
```

**Solution:**
Run cleanup to remove old backups:
```powershell
# Delete backups older than retention period
.\backup-automation.ps1 -SkipDatabaseBackup -SkipApplicationBackup
```

---

## 📋 Maintenance Tasks

### Weekly
- [ ] Check backup completed successfully
- [ ] Monitor backup logs
- [ ] Verify storage space available

### Monthly
- [ ] Run full backup verification test
- [ ] Review alert logs
- [ ] Confirm restore capability

### Quarterly
- [ ] Test full recovery procedure
- [ ] Verify application backup completeness
- [ ] Update backup documentation

### Annually
- [ ] Archive year's monthly backups to external storage
- [ ] Review and update backup strategy
- [ ] Test disaster recovery plan

---

## 🆘 Emergency Recovery

If you need to restore from backup, see [BACKUP_RECOVERY_PLAN.md](BACKUP_RECOVERY_PLAN.md) for:
- Complete database recovery steps
- Table-specific recovery
- Point-in-time recovery procedures
- Data integrity verification

---

## 📞 Support

For issues or questions:

| Contact | Role | Email |
|---------|------|-------|
| DBA | Database Admin | dba@civil-registration.tz |
| IT Manager | System Admin | admin@civil-registration.tz |
| Director | IT Director | director@civil-registration.tz |

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Feb 21, 2026 | Initial setup guide |

---

**Last Updated:** February 21, 2026  
**Status:** Active
