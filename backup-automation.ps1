<#
.SYNOPSIS
    Civil Registration System - Automated Backup Script

.DESCRIPTION
    Complete backup automation for Oracle database and Laravel application
    Handles: Database exports, application code backups, retention cleanup

.USAGE
    .\backup-automation.ps1
    .\backup-automation.ps1 -BackupType "full"
    .\backup-automation.ps1 -BackupType "incremental"

.AUTHOR
    Civil Registration System

.VERSION
    1.0
#>

param(
    [ValidateSet("full", "incremental", "both")]
    [string]$BackupType = "both",

    [switch]$SkipDatabaseBackup,
    [switch]$SkipApplicationBackup,
    [switch]$SkipCleanup
)

# ============================================================================
# CONFIGURATION
# ============================================================================

$configPath = Join-Path $PSScriptRoot "backup-config.json"
if (-not (Test-Path $configPath)) {
    Write-Error "Configuration file not found: $configPath"
    exit 1
}

$config = Get-Content $configPath | ConvertFrom-Json
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$logFile = Join-Path $config.logging.logDirectory "backup_$timestamp.log"

# Create log directory if it doesn't exist
$null = New-Item -ItemType Directory -Path $config.logging.logDirectory -Force -ErrorAction SilentlyContinue

# ============================================================================
# LOGGING FUNCTIONS
# ============================================================================

function Write-Log {
    param(
        [string]$Message,
        [ValidateSet("INFO", "WARNING", "ERROR", "SUCCESS")]
        [string]$Level = "INFO"
    )

    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] [$Level] $Message"

    Write-Host $logMessage -ForegroundColor $(
        switch ($Level) {
            "INFO"    { "White" }
            "SUCCESS" { "Green" }
            "WARNING" { "Yellow" }
            "ERROR"   { "Red" }
            default   { "White" }
        }
    )

    Add-Content -Path $logFile -Value $logMessage
}

function Write-Status {
    param([string]$Message)
    Write-Log -Message $Message -Level "INFO"
}

function Write-Success {
    param([string]$Message)
    Write-Log -Message $Message -Level "SUCCESS"
}

function Write-Warning {
    param([string]$Message)
    Write-Log -Message $Message -Level "WARNING"
}

function Write-Error {
    param([string]$Message)
    Write-Log -Message $Message -Level "ERROR"
    Write-Host $Message -ForegroundColor Red
}

# ============================================================================
# INITIALIZATION
# ============================================================================

Write-Status "=========================================="
Write-Status "Civil Registration Backup Automation"
Write-Status "=========================================="
Write-Status "Timestamp: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
Write-Status "Backup Type: $BackupType"
Write-Status "Configuration: $configPath"
Write-Status "Log File: $logFile"
Write-Status ""

# Create backup directories
$backupDirs = @(
    $config.backup.backupDirectories.full,
    $config.backup.backupDirectories.incremental,
    $config.backup.backupDirectories.logs,
    $config.backup.backupDirectories.archive
)

foreach ($dir in $backupDirs) {
    if (-not (Test-Path $dir)) {
        try {
            $null = New-Item -ItemType Directory -Path $dir -Force
            Write-Status "Created backup directory: $dir"
        }
        catch {
            Write-Error "Failed to create directory $dir : $_"
            exit 1
        }
    }
}

# ============================================================================
# DATABASE BACKUP FUNCTIONS
# ============================================================================

function Backup-OracleDatabase {
    param(
        [ValidateSet("full", "incremental")]
        [string]$Type = "full"
    )

    Write-Status ""
    Write-Status "========== Oracle Database Backup ($Type) =========="

    # Oracle XE: Use same directory for both types (XE doesn't support true incremental)
    $backupDir = $config.backup.backupDirectories.full

    $dumpFile = "civil_reg_$($Type)_$timestamp.dmp"
    $logFileOracle = "civil_reg_$($Type)_$timestamp.log"
    $dumpPath = Join-Path $backupDir $dumpFile
    $logPathOracle = Join-Path $backupDir $logFileOracle

    # Build expdp command
    $expdpCmd = @(
        "expdp",
        "system/41898803",
        "directory=backup_dir",
        "dumpfile=$dumpFile",
        "logfile=$logFileOracle"
    )

    if ($Type -eq "full") {
        # Full database export (Oracle XE compatible)
        $expdpCmd += @("full=y")
    } else {
        # Incremental: export full (XE doesn't support advanced incremental features)
        $expdpCmd += @("full=y")
    }

    Write-Status "Starting Data Pump export..."
    Write-Status "Command: $($expdpCmd -join ' ')"

    try {
        # Change to backup directory for export
        Push-Location $backupDir

        $process = Start-Process $expdpCmd[0] -ArgumentList $expdpCmd[1..($expdpCmd.Count-1)] `
            -NoNewWindow -Wait -PassThru -RedirectStandardOutput $logPathOracle

        Pop-Location

        if ($process.ExitCode -eq 0) {
            # Check if file exists and get size
            if (Test-Path $dumpPath) {
                $fileSize = (Get-Item $dumpPath).Length / 1MB
                Write-Success "Database backup completed successfully"
                Write-Success "Backup file: $dumpFile"
                Write-Success "Size: $([math]::Round($fileSize, 2)) MB"
            } else {
                # File might be in the Oracle directory location instead
                $altPath = Join-Path $config.backup.backupDirectories.full $dumpFile
                if (Test-Path $altPath) {
                    $fileSize = (Get-Item $altPath).Length / 1MB
                    Write-Success "Database backup completed successfully"
                    Write-Success "Backup file: $dumpFile (in full backup directory)"
                    Write-Success "Size: $([math]::Round($fileSize, 2)) MB"
                } else {
                    Write-Success "Database backup completed successfully"
                    Write-Warning "Could not determine backup file size"
                }
            }
            return $true
        } else {
            Write-Error "Data Pump export failed with exit code: $($process.ExitCode)"
            Write-Error "Check log: $logPathOracle"
            return $false
        }
    }
    catch {
        Write-Error "Exception during database backup: $_"
        return $false
    }
}

# ============================================================================
# APPLICATION BACKUP FUNCTIONS
# ============================================================================

function Backup-ApplicationCode {
    Write-Status ""
    Write-Status "========== Application Code Backup =========="

    $appPath = $config.application.path

    if (-not (Test-Path $appPath)) {
        Write-Error "Application path not found: $appPath"
        return $false
    }

    try {
        Push-Location $appPath

        # Check if git is initialized
        if (-not (Test-Path ".git")) {
            Write-Warning "Git repository not found in: $appPath"
            Pop-Location
            return $false
        }

        Write-Status "Committing application code..."
        $commitMessage = "Backup: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"

        # Add all changes
        & git add -A 2>&1 | ForEach-Object { Write-Status $_ }

        # Check if there are changes to commit
        $gitStatus = & git status --porcelain
        if ($gitStatus) {
            & git commit -m $commitMessage 2>&1 | ForEach-Object { Write-Status $_ }
            Write-Success "Code committed: $commitMessage"
        } else {
            Write-Status "No changes to commit"
        }

        # Push to remote
        if ($config.application.gitEnabled) {
            Write-Status "Pushing to remote repository..."
            & git push $config.application.gitRemote $config.application.gitBranch 2>&1 | `
                ForEach-Object { Write-Status $_ }
            Write-Success "Code pushed to remote"
        }

        Pop-Location
        return $true
    }
    catch {
        Write-Error "Exception during application backup: $_"
        Pop-Location
        return $false
    }
}

# ============================================================================
# RETENTION & CLEANUP
# ============================================================================

function Remove-OldBackups {
    Write-Status ""
    Write-Status "========== Cleanup Old Backups =========="

    $fullRetentionDays = $config.backup.retention.fullBackup
    $incrRetentionDays = $config.backup.retention.incrementalBackup
    $logRetentionDays = $config.backup.retention.transactionLogs

    $cutoffFull = (Get-Date).AddDays(-$fullRetentionDays)
    $cutoffIncr = (Get-Date).AddDays(-$incrRetentionDays)
    $cutoffLogs = (Get-Date).AddDays(-$logRetentionDays)

    $backupDirs = @{
        "Full Backups" = @{
            Path = $config.backup.backupDirectories.full
            Cutoff = $cutoffFull
            Days = $fullRetentionDays
        }
        "Incremental Backups" = @{
            Path = $config.backup.backupDirectories.incremental
            Cutoff = $cutoffIncr
            Days = $incrRetentionDays
        }
        "Transaction Logs" = @{
            Path = $config.backup.backupDirectories.logs
            Cutoff = $cutoffLogs
            Days = $logRetentionDays
        }
    }

    foreach ($dirName in $backupDirs.Keys) {
        $dirPath = $backupDirs[$dirName].Path
        $cutoff = $backupDirs[$dirName].Cutoff
        $days = $backupDirs[$dirName].Days

        if (Test-Path $dirPath) {
            Write-Status "Checking $dirName (retention: $days days)..."

            $oldFiles = Get-ChildItem -Path $dirPath -File | Where-Object { $_.LastWriteTime -lt $cutoff }

            if ($oldFiles) {
                foreach ($file in $oldFiles) {
                    try {
                        Remove-Item -Path $file.FullName -Force
                        Write-Status "Deleted: $($file.Name)"
                    }
                    catch {
                        Write-Warning "Failed to delete $($file.Name): $_"
                    }
                }
                Write-Success "Cleanup completed in $dirName"
            } else {
                Write-Status "No old files to delete in $dirName"
            }
        }
    }
}

# ============================================================================
# SUMMARY FUNCTIONS
# ============================================================================

function Show-BackupSummary {
    Write-Status ""
    Write-Status "========== Backup Summary =========="

    $summary = @{
        "Full Backup Directory" = $config.backup.backupDirectories.full
        "Incremental Directory" = $config.backup.backupDirectories.incremental
        "Total Backups (Full)" = (Get-ChildItem -Path $config.backup.backupDirectories.full -Filter "*.dmp" -ErrorAction SilentlyContinue | Measure-Object).Count
        "Total Backups (Incremental)" = (Get-ChildItem -Path $config.backup.backupDirectories.incremental -Filter "*.dmp" -ErrorAction SilentlyContinue | Measure-Object).Count
    }

    # Calculate total size
    $fullSize = (Get-ChildItem -Path $config.backup.backupDirectories.full -Filter "*.dmp" -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum
    $incrSize = (Get-ChildItem -Path $config.backup.backupDirectories.incremental -Filter "*.dmp" -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum

    $summary["Full Backups Size"] = "$([math]::Round(($fullSize / 1MB), 2)) MB"
    $summary["Incremental Size"] = "$([math]::Round(($incrSize / 1MB), 2)) MB"

    foreach ($key in $summary.Keys) {
        Write-Status "$key : $($summary[$key])"
    }
}

# ============================================================================
# MAIN EXECUTION
# ============================================================================

$backupSuccess = $true

try {
    # Execute backups based on type parameter
    if ($BackupType -eq "full" -or $BackupType -eq "both") {
        if (-not $SkipDatabaseBackup) {
            $success = Backup-OracleDatabase -Type "full"
            $backupSuccess = $backupSuccess -and $success
        }
    }

    if ($BackupType -eq "incremental" -or $BackupType -eq "both") {
        if (-not $SkipDatabaseBackup) {
            $success = Backup-OracleDatabase -Type "incremental"
            $backupSuccess = $backupSuccess -and $success
        }
    }

    if (-not $SkipApplicationBackup) {
        $success = Backup-ApplicationCode
        $backupSuccess = $backupSuccess -and $success
    }

    if (-not $SkipCleanup) {
        Remove-OldBackups
    }

    # Show summary
    Show-BackupSummary

    # Push to GitHub if enabled
    if ($config.github.enabled -and -not $SkipApplicationBackup) {
        Write-Status ""
        Write-Status "========== GitHub Integration =========="

        $githubScriptPath = Join-Path $PSScriptRoot "github-integration.ps1"
        if (Test-Path $githubScriptPath) {
            try {
                Write-Status "Pushing backup to GitHub..."
                & $githubScriptPath -PushToGithub
                Write-Success "GitHub push successful"
            }
            catch {
                Write-Warning "GitHub push failed: $_"
            }
        } else {
            Write-Warning "GitHub integration script not found"
        }
    }

    # Send success notification if enabled
    if ($config.notification.emailEnabled -and $config.notification.alertOnSuccess) {
        Write-Status ""
        Write-Status "========== Email Notification =========="

        $githubScriptPath = Join-Path $PSScriptRoot "github-integration.ps1"
        if (Test-Path $githubScriptPath) {
            try {
                # Format backup type for display
                $backupTypeDisplay = switch ($BackupType) {
                    "full" { "Full database backup" }
                    "incremental" { "Incremental backup" }
                    "both" { "Full and Incremental backups" }
                    default { "Database backup" }
                }

                Write-Status "Sending success notification..."
                & $githubScriptPath -SendEmailAlert -MessageType "Success" `
                    -MessageSubject "Backup Completed Successfully" `
                    -MessageBody "Successfully completed $backupTypeDisplay of Civil Registration database at $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
                Write-Success "Email notification sent"
            }
            catch {
                Write-Warning "Email notification failed: $_"
            }
        }
    }
}
catch {
    Write-Error "Unexpected error occurred: $_"
    $backupSuccess = $false

    # Send error notification
    if ($config.notification.emailEnabled -and $config.notification.alertOnError) {
        $githubScriptPath = Join-Path $PSScriptRoot "github-integration.ps1"
        if (Test-Path $githubScriptPath) {
            try {
                & $githubScriptPath -SendEmailAlert -MessageType "Error" `
                    -MessageSubject "Backup Failed" `
                    -MessageBody "Backup process encountered an error: $_"
            }
            catch {
                # Silent fail on error alert
            }
        }
    }
}

# ============================================================================
# FINAL STATUS
# ============================================================================

Write-Status ""
if ($backupSuccess) {
    Write-Success "========== BACKUP COMPLETED SUCCESSFULLY =========="
    exit 0
} else {
    Write-Error "========== BACKUP COMPLETED WITH ERRORS =========="
    exit 1
}
