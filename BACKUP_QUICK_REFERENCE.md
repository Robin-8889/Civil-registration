# Backup System - Quick Reference Card

**For System Operators & Administrators**

---

## ⚡ Quick Start

### First Time Setup (Administrator Only)

```powershell
# Open PowerShell as Administrator
cd C:\xampp\htdocs\civil-registration

# Create backup directories
mkdir C:\xampp\backups\oracle\full, C:\xampp\backups\oracle\incremental, `
      C:\xampp\backups\logs

# Install scheduled backup tasks
.\setup-scheduled-tasks.ps1

# Verify installation
.\setup-scheduled-tasks.ps1 -ListTasks
```

---

## 🔄 Daily Operations

### Manual Backup (Any Time)

```powershell
cd C:\xampp\htdocs\civil-registration

# Full backup
.\backup-automation.ps1 -BackupType full

# Incremental backup
.\backup-automation.ps1 -BackupType incremental

# Both types
.\backup-automation.ps1
```

### Quick Status Check

```powershell
# One-time health check
.\backup-alert.ps1 -OneTimeCheck

# View recent backups
Get-ChildItem C:\xampp\backups\oracle\full -Filter "*.dmp" | `
  Sort-Object CreationTime -Descending | Select-Object -First 3
```

### View Logs

```powershell
# Show latest backup log
Get-ChildItem C:\xampp\backups\logs\backup_*.log | `
  Sort-Object CreationTime -Descending | Select-Object -First 1 | `
  ForEach-Object { type $_ }

# Show last 100 lines
Get-ChildItem C:\xampp\backups\logs\backup_*.log -ErrorAction SilentlyContinue | `
  Sort-Object CreationTime -Descending | Select-Object -First 1 | `
  ForEach-Object { Get-Content $_ -Tail 100 }
```

---

## ✅ Weekly Tasks

```powershell
# Tuesday: Verify last backup was successful
.\backup-verify.ps1 -Detailed

# Check backup file sizes
Get-ChildItem C:\xampp\backups\oracle\full -Filter "*.dmp" | `
  ForEach-Object { "$($_.Name): $([math]::Round($_.Length / 1MB, 2)) MB" }
```

---

## 🎛️ Monitoring & Alerts

### Run Continuous Monitoring

```powershell
# Monitor with 5-minute check interval
.\backup-alert.ps1

# Monitor with custom interval (every 30 minutes)
.\backup-alert.ps1 -CheckInterval 1800

# With email alerts enabled
.\backup-alert.ps1 -SendEmail
```

---

## 🛠️ Troubleshooting

### Enable Scripts to Run

```powershell
# If you get "cannot be loaded" error:
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser -Force
```

### Fix Script Path Issues

```powershell
# Make sure you're in the right directory
cd C:\xampp\htdocs\civil-registration

# Verify scripts exist
dir *.ps1
# Should show: backup-automation.ps1, backup-verify.ps1, etc.
```

### Check Oracle Connection

```powershell
# Test Oracle is accessible
sqlplus system/oracle @?
# Should show SQL> prompt and exit

# If fails, check:
# 1. Oracle service is running: Get-Service | grep Oracle
# 2. Add C:\xampp\mysql\bin to PATH environment variable
# 3. Verify system/oracle credentials
```

### Check Disk Space

```powershell
# Show free space
Get-PSDrive -Name C | `
  Select-Object @{N="Drive";E={$_.Name}}, `
           @{N="Free(GB)";E={[math]::Round($_.Free/1GB,2)}}, `
           @{N="Used(%)";E={[math]::Round(100*$_.Used/($_.Used+$_.Free),1)}}
```

---

## 📊 Task Scheduler Commands

### View Scheduled Tasks

```powershell
# Show all backup tasks
Get-ScheduledTask -TaskName "CivilReg-*" | `
  Format-Table TaskName, State, LastRunTime

# Show detailed info
Get-ScheduledTask -TaskName "CivilReg-FullBackup-Weekly" | `
  Format-List TaskName, Description, State, Principal, Triggers
```

### Run Task Immediately

```powershell
# Trigger full backup task now
Start-ScheduledTask -TaskName "CivilReg-FullBackup-Weekly"

# Check if it's running
Get-ScheduledTask -TaskName "CivilReg-FullBackup-Weekly" | `
  Select-Object TaskName, State
```

### Check Last Run Status

```powershell
# Get task history
Get-ScheduledTaskInfo -TaskName "CivilReg-*" | `
  Select-Object TaskName, LastRunTime, LastTaskResult | `
  Format-Table

# LastTaskResult: 0 = Success, non-zero = Failed
```

---

## 📁 Important Locations

```
C:\xampp\htdocs\civil-registration\
├── backup-config.json          ← Configuration
├── backup-automation.ps1       ← Main backup script
├── backup-verify.ps1           ← Verification script
├── backup-alert.ps1            ← Monitoring script
├── setup-scheduled-tasks.ps1   ← Task scheduler setup
└── BACKUP_SETUP_GUIDE.md       ← Full documentation

C:\xampp\backups\               ← Backup storage
├── oracle\
│   ├── full\                   ← Weekly full backups
│   ├── incremental\            ← Daily incremental backups
│   └── logs\                   ← Archive logs
├── archive\                    ← Monthly archives
└── logs\                       ← Backup operation logs
```

---

## 🚨 Alert Symbols

| Symbol | Meaning | Action |
|--------|---------|--------|
| ✓ | Success | All good, no action needed |
| ⚠ | Warning | Monitor, may need attention soon |
| ✗ | Critical | Take action immediately |

---

## 🔐 Common Issues & Solutions

### "No backup found"
→ Check if backup directory exists: `mkdir C:\xampp\backups\oracle\full`

### "Oracle command not found"
→ Add Oracle to PATH: Settings → Edit environment variables → Add `C:\xampp\mysql\bin`

### "Task not running"
→ Check if scheduled: `.\setup-scheduled-tasks.ps1 -ListTasks`

### "Disk full"
→ Old backups not deleted: `.\backup-automation.ps1 -SkipDatabaseBackup`

### "Permission denied"
→ Run PowerShell as Administrator (right-click → Run as administrator)

---

## 📞 Quick Contact

| Issue | Contact |
|-------|---------|
| Database questions | dba@civil-registration.tz |
| System/Server issues | admin@civil-registration.tz |
| Backup strategy | director@civil-registration.tz |

---

## ⏱️ Expected Times

| Operation | Duration | When |
|-----------|----------|------|
| Full Backup | 20-30 minutes | Weekly, Sunday 02:00 |
| Incremental | 5-10 minutes | Daily, 23:00 |
| Verification Test | 15-20 minutes | Monthly, Monday 08:00 |
| Cleanup | < 1 minute | After each backup |

---

## ✨ Pro Tips

1. **Always test restore** - Backups are useless if you can't restore them
2. **Keep separate copy** - Store monthly archives off-site
3. **Monitor logs** - Check logs daily, especially after major changes
4. **Verify sizes** - File sizes should be consistent week-to-week
5. **Test recovery** - Practice full recovery quarterly

---

**Created:** February 21, 2026  
**Last Updated:** February 21, 2026  
**Version:** 1.0
