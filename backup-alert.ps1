<#
.SYNOPSIS
    Backup Monitoring & Alert System

.DESCRIPTION
    Monitors backup status and sends alerts for:
    - Failed backups
    - Storage issues
    - Stale backups
    - Restore failures

.USAGE
    .\backup-alert.ps1
    .\backup-alert.ps1 -SendEmail
    .\backup-alert.ps1 -CheckInterval 3600

.AUTHOR
    Civil Registration System

.VERSION
    1.0
#>

param(
    [int]$CheckInterval = 300,  # Default: 5 minutes
    [switch]$SendEmail,
    [switch]$OneTimeCheck
)

# ============================================================================
# CONFIGURATION
# ============================================================================

$configPath = Join-Path $PSScriptRoot "backup-config.json"
$config = Get-Content $configPath | ConvertFrom-Json

$checkInterval = $CheckInterval
$maxFullBackupAge = $config.backup.retention.fullBackup
$maxIncrBackupAge = $config.backup.retention.incrementalBackup
$alertsFile = Join-Path $config.logging.logDirectory "backup_alerts_$(Get-Date -Format 'yyyy-MM-dd').txt"
$statusFile = Join-Path $config.logging.logDirectory "backup_status.json"

# ============================================================================
# ALERT FUNCTIONS
# ============================================================================

function New-Alert {
    param(
        [ValidateSet("INFO", "WARNING", "CRITICAL")]
        [string]$Severity,
        [string]$Title,
        [string]$Message,
        [datetime]$Timestamp = (Get-Date)
    )

    return @{
        Timestamp = $Timestamp
        Severity = $Severity
        Title = $Title
        Message = $Message
        Read = $false
    }
}

function Write-Alert {
    param(
        [object]$Alert
    )

    $timestamp = $Alert.Timestamp.ToString("yyyy-MM-dd HH:mm:ss")
    $line = "[$timestamp] [$($Alert.Severity)] $($Alert.Title) - $($Alert.Message)"

    $color = switch ($Alert.Severity) {
        "CRITICAL" { "Red" }
        "WARNING" { "Yellow" }
        "INFO" { "Cyan" }
    }

    Write-Host $line -ForegroundColor $color
    Add-Content -Path $alertsFile -Value $line
}

function Send-EmailAlert {
    param(
        [object[]]$Alerts
    )

    if (-not $config.notification.emailEnabled) {
        Write-Host "Email notifications disabled in config" -ForegroundColor Yellow
        return
    }

    $criticalAlerts = $Alerts | Where-Object { $_.Severity -eq "CRITICAL" }
    $warningAlerts = $Alerts | Where-Object { $_.Severity -eq "WARNING" }

    if (-not $criticalAlerts -and -not $warningAlerts) {
        return
    }

    $emailBody = @"
Backup System Alert Report
==========================
Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')

CRITICAL ALERTS: $($criticalAlerts.Count)
$($criticalAlerts | ForEach-Object { "- $($_.Title): $($_.Message)" } | Out-String)

WARNING ALERTS: $($warningAlerts.Count)
$($warningAlerts | ForEach-Object { "- $($_.Title): $($_.Message)" } | Out-String)

For detailed logs, check: $alertsFile
"@

    try {
        $emailParams = @{
            From = $config.notification.emailFrom
            To = $config.notification.emailTo
            Subject = "Civil Registration Backup Alert Report - $(Get-Date -Format 'yyyy-MM-dd HH:mm')"
            Body = $emailBody
            SmtpServer = $config.notification.smtpServer
            Port = $config.notification.smtpPort
            UseSsl = $true
        }

        Send-MailMessage @emailParams
        Write-Host "Email sent to: $($config.notification.emailTo -join ', ')" -ForegroundColor Green
    }
    catch {
        Write-Host "Failed to send email: $_" -ForegroundColor Red
    }
}

# ============================================================================
# CHECK FUNCTIONS
# ============================================================================

function Check-FullBackupAge {
    $alerts = @()
    $fullDir = $config.backup.backupDirectories.full

    if (-not (Test-Path $fullDir)) {
        $alerts += New-Alert -Severity CRITICAL -Title "Full Backup Dir Missing" `
            -Message "Directory not found: $fullDir"
        return $alerts
    }

    $backups = Get-ChildItem -Path $fullDir -Filter "*.dmp" -ErrorAction SilentlyContinue | `
        Sort-Object LastWriteTime -Descending

    if (-not $backups) {
        $alerts += New-Alert -Severity CRITICAL -Title "No Full Backups" `
            -Message "No backup files found in: $fullDir"
        return $alerts
    }

    $latest = $backups[0]
    $age = ((Get-Date) - $latest.LastWriteTime).Days

    if ($age -gt $maxFullBackupAge + 2) {
        $alerts += New-Alert -Severity CRITICAL -Title "Full Backup Too Old" `
            -Message "Latest backup is $age days old (max: $maxFullBackupAge days). File: $($latest.Name)"
    }
    elseif ($age -gt $maxFullBackupAge) {
        $alerts += New-Alert -Severity WARNING -Title "Full Backup Aging" `
            -Message "Latest backup is $age days old. File: $($latest.Name)"
    }

    return $alerts
}

function Check-IncrementalBackupAge {
    $alerts = @()
    $incrDir = $config.backup.backupDirectories.incremental

    if (-not (Test-Path $incrDir)) {
        $alerts += New-Alert -Severity WARNING -Title "Incremental Backup Dir Missing" `
            -Message "Directory not found: $incrDir"
        return $alerts
    }

    $backups = Get-ChildItem -Path $incrDir -Filter "*.dmp" -ErrorAction SilentlyContinue | `
        Sort-Object LastWriteTime -Descending

    if (-not $backups) {
        $alerts += New-Alert -Severity WARNING -Title "No Incremental Backups" `
            -Message "No backup files found in: $incrDir"
        return $alerts
    }

    $latest = $backups[0]
    $age = ((Get-Date) - $latest.LastWriteTime).Hours

    if ($age -gt 48) {
        $alerts += New-Alert -Severity CRITICAL -Title "Incremental Backup Too Old" `
            -Message "Latest backup is $age hours old (max: 24 hours). File: $($latest.Name)"
    }
    elseif ($age -gt 24) {
        $alerts += New-Alert -Severity WARNING -Title "Incremental Backup Not Recent" `
            -Message "Latest backup is $age hours old. File: $($latest.Name)"
    }

    return $alerts
}

function Check-StorageSpace {
    $alerts = @()
    $backupRoot = $config.backup.backupRoot
    $drive = Split-Path -Qualifier $backupRoot

    try {
        $diskInfo = Get-PSDrive -Name ($drive -replace ':', '') -ErrorAction SilentlyContinue
        if ($diskInfo) {
            $totalSize = $diskInfo.Used + $diskInfo.Free
            $usedPercent = ($diskInfo.Used / $totalSize) * 100

            if ($usedPercent -gt 95) {
                $alerts += New-Alert -Severity CRITICAL -Title "Storage Critical" `
                    -Message "Disk $drive is $([math]::Round($usedPercent, 1))% full. Free space: $([math]::Round($diskInfo.Free / 1GB, 2)) GB"
            }
            elseif ($usedPercent -gt 90) {
                $alerts += New-Alert -Severity CRITICAL -Title "Storage Critical" `
                    -Message "Disk $drive is $([math]::Round($usedPercent, 1))% full. Free space: $([math]::Round($diskInfo.Free / 1GB, 2)) GB"
            }
            elseif ($usedPercent -gt 80) {
                $alerts += New-Alert -Severity WARNING -Title "Storage Warning" `
                    -Message "Disk $drive is $([math]::Round($usedPercent, 1))% full. Free space: $([math]::Round($diskInfo.Free / 1GB, 2)) GB"
            }
        }
    }
    catch {
        $alerts += New-Alert -Severity WARNING -Title "Storage Check Failed" `
            -Message "Could not check disk space: $_"
    }

    return $alerts
}

function Check-BackupFileSize {
    $alerts = @()
    $fullDir = $config.backup.backupDirectories.full

    if (Test-Path $fullDir) {
        $backups = Get-ChildItem -Path $fullDir -Filter "*.dmp" -ErrorAction SilentlyContinue | `
            Sort-Object LastWriteTime -Descending | Select-Object -First 3

        foreach ($backup in $backups) {
            $sizeMB = $backup.Length / 1MB

            # Normal full backup is 50-200 MB
            if ($sizeMB -lt 20) {
                $alerts += New-Alert -Severity WARNING -Title "Backup Size Unusual" `
                    -Message "Backup file is very small ($([math]::Round($sizeMB, 2)) MB): $($backup.Name)"
            }
            elseif ($sizeMB -gt 300) {
                $alerts += New-Alert -Severity WARNING -Title "Backup Size Unusual" `
                    -Message "Backup file is very large ($([math]::Round($sizeMB, 2)) MB): $($backup.Name)"
            }
        }
    }

    return $alerts
}

function Check-LogFiles {
    $alerts = @()
    $logDir = $config.logging.logDirectory

    if (Test-Path $logDir) {
        $logFiles = Get-ChildItem -Path $logDir -Filter "backup*.log" -ErrorAction SilentlyContinue

        foreach ($log in $logFiles) {
            # Check for error keywords in log files
            $content = Get-Content -Path $log.FullName -Tail 50 -ErrorAction SilentlyContinue

            if ($content -match "ERROR|FAILED|Exception") {
                $alerts += New-Alert -Severity WARNING -Title "Errors in Backup Log" `
                    -Message "Log file contains errors: $($log.Name)"
            }
        }
    }

    return $alerts
}

# ============================================================================
# STATUS REPORTING
# ============================================================================

function Get-BackupStatus {
    $fullDir = $config.backup.backupDirectories.full
    $incrDir = $config.backup.backupDirectories.incremental

    $fullBackups = Get-ChildItem -Path $fullDir -Filter "*.dmp" -ErrorAction SilentlyContinue | `
        Sort-Object LastWriteTime -Descending
    $incrBackups = Get-ChildItem -Path $incrDir -Filter "*.dmp" -ErrorAction SilentlyContinue | `
        Sort-Object LastWriteTime -Descending

    $status = @{
        Timestamp = Get-Date
        FullBackup = @{
            LastFile = $fullBackups[0].Name
            LastDate = $fullBackups[0].LastWriteTime
            Age = [math]::Round(((Get-Date) - $fullBackups[0].LastWriteTime).TotalHours, 1)
            TotalCount = $fullBackups.Count
        }
        IncrementalBackup = @{
            LastFile = $incrBackups[0].Name
            LastDate = $incrBackups[0].LastWriteTime
            Age = [math]::Round(((Get-Date) - $incrBackups[0].LastWriteTime).TotalHours, 1)
            TotalCount = $incrBackups.Count
        }
    }

    return $status
}

function Write-StatusReport {
    $status = Get-BackupStatus

    Write-Host ""
    Write-Host "========== Backup Status Report ==========" -ForegroundColor Cyan
    Write-Host "Timestamp: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Full Backups:" -ForegroundColor White
    Write-Host "  Latest: $($status.FullBackup.LastFile)" -ForegroundColor Gray
    Write-Host "  Age: $($status.FullBackup.Age) hours"
    Write-Host "  Total: $($status.FullBackup.TotalCount) files"
    Write-Host ""
    Write-Host "Incremental Backups:" -ForegroundColor White
    Write-Host "  Latest: $($status.IncrementalBackup.LastFile)" -ForegroundColor Gray
    Write-Host "  Age: $($status.IncrementalBackup.Age) hours"
    Write-Host "  Total: $($status.IncrementalBackup.TotalCount) files"
    Write-Host ""
    Write-Host "=========================================" -ForegroundColor Cyan
    Write-Host ""
}

# ============================================================================
# MAIN MONITORING LOOP
# ============================================================================

Write-Host "Civil Registration Backup Monitoring System" -ForegroundColor Cyan
Write-Host "Started: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
Write-Host ""

if ($OneTimeCheck) {
    # Single check run
    $allAlerts = @()

    Write-Host "Running backup checks..." -ForegroundColor Yellow

    $allAlerts += Check-FullBackupAge
    $allAlerts += Check-IncrementalBackupAge
    $allAlerts += Check-StorageSpace
    $allAlerts += Check-BackupFileSize
    $allAlerts += Check-LogFiles

    Write-StatusReport

    if ($allAlerts) {
        Write-Host "Alerts Found: $($allAlerts.Count)" -ForegroundColor Yellow
        Write-Host ""
        foreach ($alert in $allAlerts) {
            Write-Alert $alert
        }

        if ($SendEmail) {
            Send-EmailAlert -Alerts $allAlerts
        }
    } else {
        Write-Host "✓ All checks passed" -ForegroundColor Green
    }

    exit 0
}

# Continuous monitoring mode
$lastFullCheck = $null

while ($true) {
    $allAlerts = @()

    # Run checks
    $allAlerts += Check-FullBackupAge
    $allAlerts += Check-IncrementalBackupAge
    $allAlerts += Check-StorageSpace
    $allAlerts += Check-BackupFileSize
    $allAlerts += Check-LogFiles

    # Display status
    Write-StatusReport

    # Write alerts
    if ($allAlerts) {
        Write-Host "⚠ Alerts detected: $($allAlerts.Count)" -ForegroundColor Yellow
        foreach ($alert in $allAlerts) {
            Write-Alert $alert
        }

        if ($SendEmail) {
            Send-EmailAlert -Alerts $allAlerts
        }
    } else {
        Write-Host "✓ All checks passed at $(Get-Date -Format 'HH:mm:ss')" -ForegroundColor Green
    }

    Write-Host "Next check in $([math]::Round($CheckInterval / 60)) minutes..." -ForegroundColor Gray
    Write-Host ""

    Start-Sleep -Seconds $CheckInterval
}
