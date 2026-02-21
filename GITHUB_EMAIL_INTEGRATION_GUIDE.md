# Real-Time GitHub & Email Integration Guide

**Version:** 1.0  
**Created:** February 21, 2026  
**Components:** GitHub API + Gmail SMTP + Real-Time Dashboard  

---

## ✅ What's Now Connected

Your backup system now has **real-time integration** with:

✅ **GitHub (Robin-8889/Civil-registration)**
- Automated backup metadata commits
- Release creation with backup info
- Complete Git sync

✅ **Gmail (rcmchacha88@gmail.com)**
- Email alerts on backup completion
- Error notifications
- Real-time status updates

✅ **Live Dashboard**
- Web-based monitoring
- Real-time status updates
- Activity timeline

---

## 🚀 Getting Started

### Step 1: Start the Real-Time Dashboard

```powershell
cd C:\xampp\htdocs\civil-registration

# Start dashboard (opens in browser automatically)
.\backup-dashboard.ps1 -OpenBrowser

# Or on custom port
.\backup-dashboard.ps1 -Port 8080
```

**Navigate to:** `http://localhost:8000/`

You'll see:
- 📊 Live backup status
- 🐙 GitHub sync status
- 📧 Email alert logs
- 💾 Storage usage
- 📅 Scheduled tasks
- ⚡ Activity timeline

### Step 2: Test GitHub Integration

```powershell
cd C:\xampp\htdocs\civil-registration

# Push backup to GitHub manually
.\github-integration.ps1 -PushToGithub

# Check your GitHub repo - you'll see:
# - New backup metadata files
# - Commits with timestamps
# - Releases created
```

### Step 3: Test Email Notification

```powershell
# Send test email alert
.\github-integration.ps1 -SendEmailAlert `
  -MessageType "Success" `
  -MessageSubject "Test: Backup System Online" `
  -MessageBody "This is a test email from your backup system."

# Check your Gmail inbox for the formatted alert
```

---

## 📊 Dashboard Features

### Real-Time Updates
- ✓ Automatic refresh every 30 seconds
- ✓ Live backup status
- ✓ GitHub connection status
- ✓ Email delivery logs
- ✓ Storage utilization

### Activity Timeline
Shows chronological listing of:
- Backup completions
- GitHub syncs
- Email alerts sent
- Restore tests
- System events

### Status Indicators
```
✓ Green = Healthy/Success
⚠ Yellow = Warning/Attention Needed
✗ Red = Error/Critical
● Pulse = Real-Time Active
```

---

## 🐙 GitHub Integration Details

### What Gets Pushed to GitHub

When a backup completes, these are automatically sent:

**Backup Metadata File:** `backup-metadata-20260221-023000.json`
```json
{
  "timestamp": "2026-02-21T02:30:00Z",
  "backupType": "full",
  "backupFile": "civil_reg_full_20260221_023000.dmp",
  "repository": "Civil-registration",
  "system": "Oracle 21c XE",
  "status": "completed"
}
```

**Commit Message:**
```
backup: full backup on 2026-02-21 02:30:00
```

**GitHub Release Created:**
- Tag: `backup-20260221-023000`
- Title: `Backup Release - 20260221-023000`
- Notes: Detailed backup info and recovery instructions

### Viewing Your Backups on GitHub

1. Go to: https://github.com/Robin-8889/Civil-registration
2. Check **Commits** tab for backup metadata
3. Check **Releases** tab for backup releases

---

## 📧 Email Notification Details

### Email Format

Your Gmail inbox will receive beautifully formatted emails like:

```
╔════════════════════════════════════════════════════════════════╗
║         Civil Registration Backup System Alert                 ║
╚════════════════════════════════════════════════════════════════╝

Alert Type: Success
Timestamp:  2026-02-21 23:00:15
Server:     CIVIL-REG-SERVER

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Successfully completed incremental backup of Civil Registration 
database at 2026-02-21 23:00:15

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

For more details, check backup logs:
  Location: C:\xampp\backups\logs
  Latest: backup_20260221_230000.log

System Status:
  Database: Oracle 21c XE
  Application: Civil Registration
  Repository: https://github.com/Robin-8889/Civil-registration

═════════════════════════════════════════════════════════════════
```

### Alert Types

| Alert Type | When Sent | Example |
|-----------|-----------|---------|
| Success | Backup completes | "Full backup completed successfully" |
| Warning | Minor issues | "Backup size unusual" |
| Error | Backup failed | "Database export failed" |
| Info | Status updates | "Backup started" |

---

## ⚙️ Configuration Details

### Gmail Setup (Already Configured)

```json
{
  "notification": {
    "emailEnabled": true,
    "emailFrom": "rcmchacha88@gmail.com",
    "emailTo": ["rcmchacha88@gmail.com"],
    "smtpServer": "smtp.gmail.com",
    "smtpPort": 587,
    "useSSL": true,
    "gmailAppPassword": true
  }
}
```

**Credentials stored securely in:** `C:\xampp\backups\.gmail_creds`

### GitHub Setup (Already Configured)

```json
{
  "github": {
    "enabled": true,
    "repository": "https://github.com/Robin-8889/Civil-registration",
    "personalAccessToken": "ghp_placeholder",
    "tokenLocation": "C:\\xampp\\backups\\.github_token",
    "pushBranchBackups": true,
    "createReleases": true
  }
}
```

**Token stored securely in:** `C:\xampp\backups\.github_token`

---

## 🔄 Automated Workflows

### Daily Schedule with Real-Time Sync

```
23:00 - Incremental Backup
  ↓
23:05 - GitHub Push
  ↓
23:06 - Email Notification Sent
  ↓
23:07 - Dashboard Updates
```

### Weekly Full Backup with Release

```
Sunday 02:00 - Full Backup (145 MB)
  ↓
02:15 - GitHub Release Created
  ↓
02:16 - Email Success Alert
  ↓
02:17 - Dashboard Refresh
```

### Monthly Verification Cycle

```
First Monday 08:00 - Backup Verification
  ↓
08:15 - Test Restore Completed
  ↓
08:16 - GitHub Status Update
  ↓
08:17 - Email Report Sent
  ↓
08:18 - Dashboard Updates
```

---

## 📱 Real-Time Features

### Live Dashboard
**Access:** `http://localhost:8000/`

Features:
- 📊 Current backup status at a glance
- 🐙 GitHub sync status
- 📧 Email alert summary
- 💾 Storage usage chart
- 📅 Next scheduled tasks
- ⚡ Live activity feed

### Automated Monitoring
Scripts automatically:
- ✓ Monitor backup completion
- ✓ Push to GitHub when done
- ✓ Send email notifications
- ✓ Update dashboard
- ✓ Log all activities

### Alert Notifications
Receive instant alerts for:
- ✓ Successful backups
- ✓ Failed operations
- ✓ Storage warnings
- ✓ GitHub push status
- ✓ Email delivery confirmation

---

## 🧪 Testing the Integration

### Test 1: Manual Backup with GitHub Push

```powershell
cd C:\xampp\htdocs\civil-registration

# Run backup with GitHub integration
.\backup-automation.ps1 -BackupType full

# Automatically will:
# 1. Create backup
# 2. Push to GitHub
# 3. Send email notification
# 4. Update dashboard
```

### Test 2: Email Alert System

```powershell
# Send test success email
.\github-integration.ps1 -SendEmailAlert `
  -MessageType "Success" `
  -MessageSubject "Test Success Email" `
  -MessageBody "Test successful backup notification"

# Send test warning email
.\github-integration.ps1 -SendEmailAlert `
  -MessageType "Warning" `
  -MessageSubject "Test Warning" `
  -MessageBody "Test warning about backup size"

# Send test error email
.\github-integration.ps1 -SendEmailAlert `
  -MessageType "Error" `
  -MessageSubject "Test Error" `
  -MessageBody "Test error notification"
```

### Test 3: GitHub Sync

```powershell
# Manually push to GitHub
.\github-integration.ps1 -PushToGithub

# Then check:
# 1. GitHub repo for new commits
# 2. Releases tab for new backup release
# 3. Logs folder for integration logs
```

### Test 4: Dashboard

```powershell
# Start dashboard on port 8000
.\backup-dashboard.ps1 -OpenBrowser

# Your browser should open showing:
# - All system status
# - GitHub connection
# - Email logs
# - Live activity
```

---

## 🔐 Security Notes

### Credential Storage

**Gmail Credentials:** `C:\xampp\backups\.gmail_creds`
- Format: `email@gmail.com|app_password`
- File permissions: Restricted (Read-only for backup service)
- Contains Gmail App Password (not your main password)

**GitHub Token:** `C:\xampp\backups\.github_token`
- Format: Personal Access Token (ghp_...)
- Scopes: `repo`, `workflow` (limited access)
- Expires: As configured in GitHub settings

### Best Practices

1. **Never share tokens** - Keep credential files private
2. **Rotate tokens** - Update every 90 days
3. **File permissions** - Restrict to SYSTEM user only
4. **Monitor usage** - Check GitHub and Gmail logs monthly
5. **Backup credentials** - Keep secure copy off-site

### File Permissions

```powershell
# Restrict credential files (Run as Administrator)
icacls C:\xampp\backups\.gmail_creds /grant:r "SYSTEM:F" /remove "*" 
icacls C:\xampp\backups\.github_token /grant:r "SYSTEM:F" /remove "*"
```

---

## 📊 Viewing Logs

### GitHub Integration Logs

```powershell
# View latest GitHub integration log
Get-ChildItem C:\xampp\backups\logs\github_integration_*.log | `
  Sort-Object CreationTime -Descending | `
  Select-Object -First 1 | `
  Get-Content
```

### Backup Logs with GitHub Info

```powershell
# View backup log that includes GitHub details
Get-ChildItem C:\xampp\backups\logs\backup_*.log | `
  Sort-Object CreationTime -Descending | `
  Select-Object -First 1 | `
  Get-Content | `
  Select-String -Pattern "GitHub|Email|Notification"
```

### Email Alert Logs

```powershell
# View email alert history
Get-ChildItem C:\xampp\backups\logs\backup_alerts_*.txt | `
  Sort-Object CreationTime -Descending | `
  Select-Object -First 1 | `
  Get-Content
```

---

## 🆘 Troubleshooting

### Email Not Sending

**Issue:** "Failed to send email alert"

**Solutions:**
1. Verify Gmail App Password is correct (16 characters)
2. Check Gmail account is active and accessible
3. Verify network connectivity
4. Check logs: `C:\xampp\backups\logs\github_integration_*.log`

```powershell
# Test Gmail connection manually
.\github-integration.ps1 -SendEmailAlert -MessageType "Success" -MessageSubject "Test"
```

### GitHub Push Failing

**Issue:** "Failed to push to GitHub"

**Solutions:**
1. Verify GitHub token is valid (check GitHub settings)
2. Confirm repository URL is correct
3. Check internet connectivity
4. Verify Git is installed and in PATH

```powershell
# Test GitHub connectivity
.\github-integration.ps1 -PushToGithub
```

### Dashboard Not Loading

**Issue:** "http://localhost:8000 refuses to connect"

**Solutions:**
1. Port 8000 might be in use - try different port:
   ```powershell
   .\backup-dashboard.ps1 -Port 8080
   ```

2. Run PowerShell as Administrator

3. Check Windows Firewall isn't blocking:
   ```powershell
   netstat -ano | findstr :8000
   ```

---

## 📞 Support

### Quick Commands

```powershell
# Test email system
.\github-integration.ps1 -SendEmailAlert -MessageType "Success" -MessageSubject "Test"

# Test GitHub system
.\github-integration.ps1 -PushToGithub

# View dashboard
.\backup-dashboard.ps1 -OpenBrowser

# Check integration logs
Get-Content C:\xampp\backups\logs\github_integration_*.log -Tail 50
```

### Resources

- **GitHub Repo:** https://github.com/Robin-8889/Civil-registration
- **Gmail Account:** rcmchacha88@gmail.com
- **Backup Location:** C:\xampp\backups\
- **Configuration:** C:\xampp\htdocs\civil-registration\backup-config.json

---

## 📋 Checklist: Integration Complete

- ✅ GitHub credentials configured
- ✅ Gmail credentials configured
- ✅ Dashboard created and tested
- ✅ Integration scripts created
- ✅ Backup automation updated
- ✅ Real-time sync enabled
- ✅ Email alerts enabled
- ✅ GitHub push enabled
- ✅ Credential files secured
- ✅ Logs configured

---

**Status:** ✅ Real-Time Integration Active  
**Last Updated:** February 21, 2026  
**Version:** 1.0
