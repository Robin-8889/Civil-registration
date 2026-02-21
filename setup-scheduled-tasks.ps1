<#
.SYNOPSIS
    Setup Windows Task Scheduler for automated backups

.DESCRIPTION
    Creates scheduled tasks for:
    - Weekly full backups (Sunday 02:00)
    - Daily incremental backups (23:00)
    - Monthly verification (First Monday 08:00)

.USAGE
    # Run as Administrator
    .\setup-scheduled-tasks.ps1
    .\setup-scheduled-tasks.ps1 -Uninstall
    .\setup-scheduled-tasks.ps1 -ListTasks

.AUTHOR
    Civil Registration System

.VERSION
    1.0
#>

param(
    [switch]$Uninstall,
    [switch]$ListTasks,
    [switch]$Force
)

# ============================================================================
# VALIDATION
# ============================================================================

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")

if (-not $isAdmin) {
    Write-Host "ERROR: This script must run as Administrator" -ForegroundColor Red
    Write-Host "Please run: 'Run as administrator' and try again" -ForegroundColor Yellow
    exit 1
}

$configPath = Join-Path $PSScriptRoot "backup-config.json"
if (-not (Test-Path $configPath)) {
    Write-Host "ERROR: Configuration file not found: $configPath" -ForegroundColor Red
    exit 1
}

$config = Get-Content $configPath | ConvertFrom-Json
$backupScript = Join-Path $PSScriptRoot "backup-automation.ps1"
$verifyScript = Join-Path $PSScriptRoot "backup-verify.ps1"

# ============================================================================
# TASK DEFINITIONS
# ============================================================================

$tasks = @(
    @{
        Name = "CivilReg-FullBackup-Weekly"
        Description = "Weekly full database backup - Sunday 02:00"
        Script = $backupScript
        Arguments = "-BackupType full"
        Schedule = "At 02:00 on Sunday"
        Enabled = $config.scheduling.fullBackup.enabled
    },
    @{
        Name = "CivilReg-IncrementalBackup-Daily"
        Description = "Daily incremental backup - 23:00"
        Script = $backupScript
        Arguments = "-BackupType incremental"
        Schedule = "Daily at 23:00"
        Enabled = $config.scheduling.incrementalBackup.enabled
    },
    @{
        Name = "CivilReg-BackupVerify-Monthly"
        Description = "Monthly backup verification - First Monday 08:00"
        Script = $verifyScript
        Arguments = "-Detailed -SendReport"
        Schedule = "At 08:00 on the first Monday"
        Enabled = $config.scheduling.verification.enabled
    }
)

# ============================================================================
# HELPER FUNCTIONS
# ============================================================================

function Write-Status {
    param(
        [string]$Message,
        [ValidateSet("INFO", "SUCCESS", "WARNING", "ERROR")]
        [string]$Level = "INFO"
    )

    $color = switch ($Level) {
        "SUCCESS" { "Green" }
        "WARNING" { "Yellow" }
        "ERROR"   { "Red" }
        default   { "White" }
    }

    Write-Host "[$Level] $Message" -ForegroundColor $color
}

function Get-TaskTrigger {
    param([hashtable]$Task)

    $taskName = $Task.Name
    $trigger = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Triggers
    return $trigger
}

# ============================================================================
# LIST TASKS
# ============================================================================

if ($ListTasks) {
    Write-Status "========== Scheduled Backup Tasks ==========" -Level "INFO"
    Write-Status ""

    foreach ($task in $tasks) {
        $scheduled = Get-ScheduledTask -TaskName $task.Name -ErrorAction SilentlyContinue

        if ($scheduled) {
            Write-Status "Task: $($task.Name)" -Level "SUCCESS"
            Write-Status "Description: $($task.Description)" -Level "INFO"
            Write-Status "Status: $($scheduled.State)" -Level "INFO"
            Write-Status "Schedule: $($task.Schedule)" -Level "INFO"
            Write-Status "Script: $($task.Script)" -Level "INFO"
            Write-Status "Arguments: $($task.Arguments)" -Level "INFO"

            $trigger = $scheduled.Triggers
            if ($trigger) {
                Write-Status "Trigger Details:" -Level "INFO"
                $trigger | Format-List -Property @("StartBoundary", "Repetition", "Enabled") | Out-String | ForEach-Object { Write-Host "  $_" }
            }

            Write-Status ""
        }
    }

    exit 0
}

# ============================================================================
# UNINSTALL TASKS
# ============================================================================

if ($Uninstall) {
    Write-Status "========== Uninstalling Scheduled Tasks ==========" -Level "WARNING"
    Write-Status ""

    foreach ($task in $tasks) {
        $scheduled = Get-ScheduledTask -TaskName $task.Name -ErrorAction SilentlyContinue

        if ($scheduled) {
            Write-Status "Unregistering: $($task.Name)..." -Level "INFO"
            try {
                Unregister-ScheduledTask -TaskName $task.Name -Confirm:$false
                Write-Status "Successfully unregistered: $($task.Name)" -Level "SUCCESS"
            }
            catch {
                Write-Status "Failed to unregister $($task.Name): $_" -Level "ERROR"
            }
        } else {
            Write-Status "Task not found: $($task.Name)" -Level "WARNING"
        }
    }

    Write-Status ""
    Write-Status "Uninstall completed" -Level "INFO"
    exit 0
}

# ============================================================================
# CREATE/UPDATE TASKS
# ============================================================================

Write-Status "========== Installing Scheduled Backup Tasks ==========" -Level "INFO"
Write-Status ""
Write-Status "PowerShell Scripts:" -Level "INFO"
Write-Status "  Backup Script: $backupScript" -Level "INFO"
Write-Status "  Verify Script: $verifyScript" -Level "INFO"
Write-Status "  Config File: $configPath" -Level "INFO"
Write-Status ""

foreach ($task in $tasks) {
    if (-not $task.Enabled) {
        Write-Status "Skipping disabled task: $($task.Name)" -Level "WARNING"
        continue
    }

    Write-Status "Setting up: $($task.Name)" -Level "INFO"
    Write-Status "  Description: $($task.Description)" -Level "INFO"
    Write-Status "  Schedule: $($task.Schedule)" -Level "INFO"

    try {
        # Check if task exists
        $existing = Get-ScheduledTask -TaskName $task.Name -ErrorAction SilentlyContinue

        if ($existing -and -not $Force) {
            Write-Status "  Task already exists" -Level "WARNING"
            Write-Status "  Use -Force to overwrite" -Level "WARNING"
            continue
        }

        # Remove existing task if Force is used
        if ($existing -and $Force) {
            Unregister-ScheduledTask -TaskName $task.Name -Confirm:$false
            Write-Status "  Removed existing task" -Level "INFO"
        }

        # Create action
        $action = New-ScheduledTaskAction -Execute "powershell.exe" `
            -Argument "-NoProfile -WindowStyle Hidden -File `"$($task.Script)`" $($task.Arguments)"

        # Create trigger based on task
        $trigger = switch ($task.Name) {
            "CivilReg-FullBackup-Weekly" {
                New-ScheduledTaskTrigger -Weekly -DaysOfWeek Sunday -At 02:00
            }
            "CivilReg-IncrementalBackup-Daily" {
                New-ScheduledTaskTrigger -Daily -At 23:00
            }
            "CivilReg-BackupVerify-Monthly" {
                # First Monday of month at 08:00 (approximate using weekly trigger)
                New-ScheduledTaskTrigger -Weekly -DaysOfWeek Monday `
                    -WeeksInterval 4 -At 08:00
            }
        }

        # Create settings
        $settings = New-ScheduledTaskSettingsSet `
            -AllowStartIfOnBatteries `
            -DontStopIfGoingOnBatteries `
            -RunWithoutNetwork `
            -MultipleInstances IgnoreNew `
            -ExecutionTimeLimit (New-TimeSpan -Hours 4)

        # Create principal (run as SYSTEM)
        $principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest

        # Register task
        Register-ScheduledTask -TaskName $task.Name `
            -Description $task.Description `
            -Action $action `
            -Trigger $trigger `
            -Settings $settings `
            -Principal $principal `
            -Force | Out-Null

        Write-Status "  ✓ Task created successfully" -Level "SUCCESS"
    }
    catch {
        Write-Status "  ✗ Failed to create task: $_" -Level "ERROR"
    }

    Write-Status ""
}

# ============================================================================
# VERIFICATION
# ============================================================================

Write-Status "========== Task Verification ==========" -Level "INFO"
Write-Status ""

$allTasksFound = $true
foreach ($task in $tasks) {
    $scheduled = Get-ScheduledTask -TaskName $task.Name -ErrorAction SilentlyContinue

    if ($scheduled) {
        $lastRun = $scheduled.LastRunTime
        $state = $scheduled.State
        $statusColor = if ($state -eq "Ready") { "Green" } else { "Yellow" }

        Write-Status "✓ $($task.Name)" -Level "SUCCESS"
        Write-Status "  State: $state | Last Run: $lastRun" -Level "INFO"
    } else {
        Write-Status "✗ $($task.Name) - NOT FOUND" -Level "ERROR"
        $allTasksFound = $false
    }
}

Write-Status ""
if ($allTasksFound) {
    Write-Status "========== INSTALLATION SUCCESSFUL ==========" -Level "SUCCESS"
} else {
    Write-Status "========== INSTALLATION INCOMPLETE ==========" -Level "WARNING"
}

# ============================================================================
# NEXT STEPS
# ============================================================================

Write-Status ""
Write-Status "========== Next Steps ==========" -Level "INFO"
Write-Status ""
Write-Status "1. Verify tasks are running:" -Level "INFO"
Write-Status "   .\setup-scheduled-tasks.ps1 -ListTasks" -Level "INFO"
Write-Status ""
Write-Status "2. Check backup logs:" -Level "INFO"
Write-Status "   Get-ChildItem C:\xampp\backups\logs\*.log | Sort-Object LastWriteTime -Descending | Select-Object -First 10" -Level "INFO"
Write-Status ""
Write-Status "3. To uninstall tasks:" -Level "INFO"
Write-Status "   .\setup-scheduled-tasks.ps1 -Uninstall" -Level "INFO"
Write-Status ""
Write-Status "4. Monitor Task Scheduler:" -Level "INFO"
Write-Status "   - Open Task Scheduler (taskschd.msc)" -Level "INFO"
Write-Status "   - Look for tasks starting with 'CivilReg-'" -Level "INFO"
Write-Status ""
