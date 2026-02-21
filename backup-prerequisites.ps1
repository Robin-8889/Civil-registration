<#
.SYNOPSIS
    Pre-Installation Verification Script

.DESCRIPTION
    Checks all prerequisites and dependencies before installing backup automation

.USAGE
    .\backup-prerequisites.ps1
    .\backup-prerequisites.ps1 -Detailed
    .\backup-prerequisites.ps1 -Fix

.VERSION
    1.0
#>

param(
    [switch]$Detailed,
    [switch]$Fix
)

$script:checksPass = 0
$script:checksFail = 0
$script:checksWarn = 0

# ============================================================================
# HELPER FUNCTIONS
# ============================================================================

function Write-Check {
    param(
        [ValidateSet("PASS", "FAIL", "WARN")]
        [string]$Status,
        [string]$Title,
        [string]$Details = ""
    )

    $color = switch ($Status) {
        "PASS" { "Green"; $script:checksPass++ }
        "FAIL" { "Red"; $script:checksFail++ }
        "WARN" { "Yellow"; $script:checksWarn++ }
    }

    $symbol = switch ($Status) {
        "PASS" { "✓" }
        "FAIL" { "✗" }
        "WARN" { "⚠" }
    }

    Write-Host "$symbol $Title" -ForegroundColor $color
    if ($Details) {
        Write-Host "  $Details" -ForegroundColor Gray
    }
}

# ============================================================================
# CHECKS
# ============================================================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Backup System Prerequisites Check" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check 1: Windows Version
Write-Host "📋 System Checks" -ForegroundColor White
Write-Host ""

$osInfo = Get-CimInstance Win32_OperatingSystem
$winVersion = [System.Environment]::OSVersion.Version.Major
$buildNumber = $osInfo.BuildNumber

if ($winVersion -ge 10 -or ($winVersion -eq 6 -and $buildNumber -ge 14393)) {
    Write-Check -Status PASS -Title "Windows Version" -Details "$(($osInfo).Caption) - Build $buildNumber"
} else {
    Write-Check -Status FAIL -Title "Windows Version" -Details "Requires Windows 10/11 or Server 2016+, found Build $buildNumber"
}

# Check 2: PowerShell Version
$PSVersion = $PSVersionTable.PSVersion
if ($PSVersion.Major -ge 5) {
    Write-Check -Status PASS -Title "PowerShell Version" -Details "v$($PSVersion.Major).$($PSVersion.Minor)"
} else {
    Write-Check -Status FAIL -Title "PowerShell Version" -Details "Requires v5.0+, found v$($PSVersion.Major).$($PSVersion.Minor)"
}

# Check 3: Administrator Privileges
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")
if ($isAdmin) {
    Write-Check -Status PASS -Title "Administrator Privileges" -Details "Running as Administrator"
} else {
    Write-Check -Status FAIL -Title "Administrator Privileges" -Details "Required for Task Scheduler operations"
}

# Check 4: Execution Policy
Write-Host ""
Write-Host "🔐 Script Execution" -ForegroundColor White
Write-Host ""

$executionPolicy = Get-ExecutionPolicy -Scope CurrentUser
$allowedPolicies = @("RemoteSigned", "Unrestricted", "Bypass")
if ($executionPolicy -in $allowedPolicies) {
    Write-Check -Status PASS -Title "Execution Policy" -Details "CurrentUser: $executionPolicy"
} else {
    Write-Check -Status FAIL -Title "Execution Policy" -Details "CurrentUser: $executionPolicy (requires RemoteSigned or higher)"
    if ($Fix) {
        Write-Host "  Setting ExecutionPolicy to RemoteSigned..." -ForegroundColor Yellow
        Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser -Force | Out-Null
        Write-Host "  ✓ Policy updated" -ForegroundColor Green
    }
}

# Check 5: Configuration File
Write-Host ""
Write-Host "📁 Configuration Files" -ForegroundColor White
Write-Host ""

$configPath = Join-Path (Get-Location) "backup-config.json"
if (Test-Path $configPath) {
    Write-Check -Status PASS -Title "Configuration File" -Details "Found at $configPath"

    try {
        $config = Get-Content $configPath | ConvertFrom-Json | Out-Null
        Write-Check -Status PASS -Title "Configuration Valid" -Details "JSON is valid and readable"
    }
    catch {
        Write-Check -Status FAIL -Title "Configuration Valid" -Details "JSON parsing error: $_"
    }
} else {
    Write-Check -Status FAIL -Title "Configuration File" -Details "Not found at $configPath"
}

# Check 6: Script Files
Write-Host ""
Write-Host "📜 Required Scripts" -ForegroundColor White
Write-Host ""

$requiredScripts = @(
    "backup-automation.ps1",
    "backup-verify.ps1",
    "backup-alert.ps1",
    "setup-scheduled-tasks.ps1"
)

foreach ($script in $requiredScripts) {
    $scriptPath = Join-Path (Get-Location) $script
    if (Test-Path $scriptPath) {
        $size = (Get-Item $scriptPath).Length / 1KB
        Write-Check -Status PASS -Title $script -Details "$('{0:N0}' -f $size) KB"
    } else {
        Write-Check -Status FAIL -Title $script -Details "Not found"
    }
}

# Check 7: Oracle Database
Write-Host ""
Write-Host "🗄️  Oracle Database" -ForegroundColor White
Write-Host ""

$oracleServices = Get-Service | Where-Object { $_.Name -match "Oracle" }
if ($oracleServices) {
    foreach ($service in $oracleServices) {
        $status = if ($service.Status -eq "Running") { "Running" } else { "Stopped" }
        $statusType = if ($service.Status -eq "Running") { "PASS" } else { "WARN" }
        Write-Check -Status $statusType -Title "Service: $($service.DisplayName)" -Details $status
    }
} else {
    Write-Check -Status FAIL -Title "Oracle Services" -Details "No Oracle services found. Is Oracle installed?"
}

# Check Oracle executable
$oracleExe = Get-Command sqlplus -ErrorAction SilentlyContinue
if ($oracleExe) {
    Write-Check -Status PASS -Title "SQLPlus Command" -Details "Found at $($oracleExe.Source)"
} else {
    Write-Check -Status WARN -Title "SQLPlus Command" -Details "Not in PATH. Add C:\xampp\mysql\bin to environment variables"
}

# Check 8: Git
Write-Host ""
Write-Host "🔧 Version Control" -ForegroundColor White
Write-Host ""

$git = Get-Command git -ErrorAction SilentlyContinue
if ($git) {
    $gitVersion = & git --version
    Write-Check -Status PASS -Title "Git Installation" -Details $gitVersion

    # Check if in git repo
    $gitRepo = git rev-parse --git-dir 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Check -Status PASS -Title "Git Repository" -Details "Initialized in current directory"
    } else {
        Write-Check -Status WARN -Title "Git Repository" -Details "Not initialized. Application code backup may not work"
    }
} else {
    Write-Check -Status FAIL -Title "Git Installation" -Details "Git not found in PATH. Required for application backup"
}

# Check 9: Disk Space
Write-Host ""
Write-Host "💾 Storage" -ForegroundColor White
Write-Host ""

$backupDrive = "C"
$driveInfo = Get-PSDrive -Name $backupDrive
$freeGB = [math]::Round($driveInfo.Free / 1GB, 2)
$usedPercent = [math]::Round(100 * $driveInfo.Used / ($driveInfo.Used + $driveInfo.Free), 1)

Write-Check -Status PASS -Title "Backup Drive" -Details "Drive $backupDrive`: Free: ${freeGB}GB (${usedPercent}% used)"

if ($freeGB -lt 100) {
    Write-Check -Status WARN -Title "Free Space" -Details "Less than 100 GB available"
} elseif ($freeGB -lt 500) {
    Write-Check -Status WARN -Title "Free Space" -Details "Less than 500 GB available (recommended for full backups)"
} else {
    Write-Check -Status PASS -Title "Free Space" -Details "Sufficient space for backups"
}

# Check 10: Backup Directories
Write-Host ""
Write-Host "📂 Backup Directories" -ForegroundColor White
Write-Host ""

if ($config) {
    $backupDirs = @(
        $config.backup.backupDirectories.full,
        $config.backup.backupDirectories.incremental,
        $config.backup.backupDirectories.logs,
        $config.backup.backupDirectories.archive
    )

    foreach ($dir in $backupDirs) {
        if (Test-Path $dir) {
            $fileCount = (Get-ChildItem -Path $dir -ErrorAction SilentlyContinue | Measure-Object).Count
            Write-Check -Status PASS -Title "Directory: $($dir.Split('\')[-1])" -Details "Exists, $fileCount files"
        } else {
            Write-Check -Status WARN -Title "Directory: $($dir.Split('\')[-1])" -Details "Does not exist. Will be created during first backup"
        }
    }
}

# ============================================================================
# SUMMARY
# ============================================================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Summary" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "✓ Passed: $script:checksPass" -ForegroundColor Green
Write-Host "⚠ Warnings: $script:checksWarn" -ForegroundColor Yellow
Write-Host "✗ Failed: $script:checksFail" -ForegroundColor Red
Write-Host ""

if ($script:checksFail -eq 0) {
    if ($script:checksWarn -eq 0) {
        Write-Host "🎉 All checks passed! Ready to install." -ForegroundColor Green
        Write-Host ""
        Write-Host "Next step: .\setup-scheduled-tasks.ps1" -ForegroundColor Cyan
    } else {
        Write-Host "⚠️  Some warnings found. Review above and fix if needed." -ForegroundColor Yellow
        Write-Host ""
        Write-Host "Next step: .\setup-scheduled-tasks.ps1" -ForegroundColor Cyan
    }
} else {
    Write-Host "❌ Prerequisites not met. Fix issues above before proceeding." -ForegroundColor Red
    Write-Host ""

    if ($script:checksWarn -gt 0 -or $script:checksFail -gt 0) {
        Write-Host "💡 Run with -Fix flag to attempt automatic fixes:" -ForegroundColor Yellow
        Write-Host "   .\backup-prerequisites.ps1 -Fix" -ForegroundColor Yellow
    }
}

Write-Host ""

# Detailed report
if ($Detailed) {
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "Detailed Information" -ForegroundColor Cyan
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host ""

    Write-Host "System Information:" -ForegroundColor White
    $osInfo | Format-List Caption, Version, BuildNumber, OSArchitecture | Out-String | Write-Host

    Write-Host "PowerShell Details:" -ForegroundColor White
    $PSVersionTable | Format-List PSVersion, Edition, Platform | Out-String | Write-Host
}

exit 0
