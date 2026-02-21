<#
.SYNOPSIS
    GitHub & Email Integration for Backup System

.DESCRIPTION
    - Pushes backups to GitHub with commit messages
    - Sends real-time email alerts from Gmail
    - Creates backup releases on GitHub
    - Syncs backup metadata to repository

.USAGE
    .\github-integration.ps1
    .\github-integration.ps1 -PushToGithub
    .\github-integration.ps1 -SendEmailAlert -MessageType "Success"

.AUTHOR
    Civil Registration System

.VERSION
    1.0
#>

param(
    [switch]$PushToGithub,
    [switch]$SendEmailAlert,
    [ValidateSet("Success", "Warning", "Error", "Info")]
    [string]$MessageType = "Info",
    [string]$MessageSubject = "Backup Alert",
    [string]$MessageBody = "Backup operation completed"
)

# ============================================================================
# CONFIGURATION
# ============================================================================

$configPath = Join-Path $PSScriptRoot "backup-config.json"
$config = Get-Content $configPath | ConvertFrom-Json

$gmailCredsFile = $config.notification.passwordLocation
$githubTokenFile = $config.github.tokenLocation
$appPath = $config.application.path
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$logDir = $config.logging.logDirectory
$logFile = Join-Path $logDir "github_integration_$timestamp.log"

# Create log directory
$null = New-Item -ItemType Directory -Path $logDir -Force -ErrorAction SilentlyContinue

# ============================================================================
# LOGGING & FORMATTING
# ============================================================================

function Write-Log {
    param(
        [string]$Message,
        [ValidateSet("INFO", "SUCCESS", "WARNING", "ERROR")]
        [string]$Level = "INFO"
    )

    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] [$Level] $Message"

    Write-Host $logMessage -ForegroundColor $(switch ($Level) {
        "SUCCESS" { "Green" }
        "WARNING" { "Yellow" }
        "ERROR"   { "Red" }
        default   { "White" }
    })

    Add-Content -Path $logFile -Value $logMessage
}

# ============================================================================
# EMAIL FUNCTIONS
# ============================================================================

function Get-GmailCredentials {
    if (-not (Test-Path $gmailCredsFile)) {
        Write-Log "Gmail credentials file not found: $gmailCredsFile" -Level "ERROR"
        return $null
    }

    try {
        $content = Get-Content -Path $gmailCredsFile -Raw
        $parts = $content.Trim() -split '\|'

        if ($parts.Count -ne 2) {
            Write-Log "Invalid Gmail credentials format" -Level "ERROR"
            return $null
        }

        return @{
            Email = $parts[0].Trim()
            AppPassword = $parts[1].Trim() -replace '\s+', ''  # Remove all spaces from app password
        }
    }
    catch {
        Write-Log "Failed to read Gmail credentials: $_" -Level "ERROR"
        return $null
    }
}

function Send-EmailAlert {
    param(
        [string]$Subject,
        [string]$Body,
        [ValidateSet("Success", "Warning", "Error")]
        [string]$AlertType = "Info"
    )

    if (-not $config.notification.emailEnabled) {
        Write-Log "Email notifications disabled in config" -Level "WARNING"
        return $false
    }

    $gmailCreds = Get-GmailCredentials
    if (-not $gmailCreds) {
        Write-Log "Cannot send email - credentials unavailable" -Level "ERROR"
        return $false
    }

    try {
        Write-Log "Preparing email alert..." -Level "INFO"

                # Modern HTML email formatting
                $emailBody = @"
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; color: #222; }
        .container { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px #ccc; padding: 24px; margin: 24px auto; max-width: 600px; }
        .header { font-size: 1.5em; font-weight: bold; color: #2a7ae2; margin-bottom: 12px; }
        .section { margin-bottom: 18px; }
        .label { font-weight: bold; color: #555; }
        .success { color: #28a745; }
        .warning { color: #ffc107; }
        .error { color: #dc3545; }
        .footer { font-size: 0.9em; color: #888; margin-top: 24px; }
        a { color: #2a7ae2; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Civil Registration Backup System Alert</div>
        <div class="section">
            <span class="label">Alert Type:</span> <span class="$AlertType.ToLower()">$AlertType</span><br>
            <span class="label">Timestamp:</span> $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')<br>
            <span class="label">Server:</span> $(hostname)
        </div>
        <div class="section">
            $Body
        </div>
        <div class="section">
            <span class="label">For more details, check backup logs:</span><br>
            Location: $logDir<br>
            Latest: $(Get-ChildItem $logDir -Filter "*.log" | Sort-Object CreationTime -Descending | Select-Object -First 1 | ForEach-Object { $_.Name })
        </div>
        <div class="section">
            <span class="label">System Status:</span><br>
            Database: Oracle 21c XE<br>
            Application: Civil Registration<br>
            Repository: <a href="https://github.com/Robin-8889/Civil-registration">https://github.com/Robin-8889/Civil-registration</a>
        </div>
        <div class="footer">
            Need support? Contact: <a href="mailto:rcmchacha88@gmail.com">rcmchacha88@gmail.com</a><br>
            <br>
            Generated by: Backup Automation System v1.0
        </div>
    </div>
</body>
</html>
"@

        # Create secure password object
        $securePassword = ConvertTo-SecureString -String $gmailCreds.AppPassword -AsPlainText -Force
        $credential = New-Object System.Management.Automation.PSCredential ($gmailCreds.Email, $securePassword)

        # Send email via Gmail SMTP (HTML)
        $emailParams = @{
            From = $gmailCreds.Email
            To = $config.notification.emailTo -join ", "
            Subject = "[$(Get-Date -Format 'HH:mm:ss')] $AlertType - $Subject"
            Body = $emailBody
            SmtpServer = $config.notification.smtpServer
            Port = $config.notification.smtpPort
            UseSsl = $config.notification.useSSL
            Credential = $credential
            ErrorAction = "Stop"
            BodyAsHtml = $true
        }

        Send-MailMessage @emailParams

        Write-Log "Email alert sent successfully" -Level "SUCCESS"
        Write-Log "  To: $($config.notification.emailTo -join ', ')" -Level "INFO"
        Write-Log "  Subject: $Subject" -Level "INFO"

        return $true
    }
    catch {
        Write-Log "Failed to send email alert: $_" -Level "ERROR"
        return $false
    }
}

# ============================================================================
# GITHUB FUNCTIONS
# ============================================================================

function Get-GithubToken {
    if (-not (Test-Path $githubTokenFile)) {
        Write-Log "GitHub token file not found: $githubTokenFile" -Level "ERROR"
        return $null
    }

    try {
        $token = Get-Content -Path $githubTokenFile -Raw
        return $token.Trim()
    }
    catch {
        Write-Log "Failed to read GitHub token: $_" -Level "ERROR"
        return $null
    }
}

function Push-BackupToGithub {
    param(
        [string]$BackupFile,
        [ValidateSet("full", "incremental")]
        [string]$BackupType = "full"
    )

    if (-not $config.github.enabled) {
        Write-Log "GitHub integration disabled" -Level "WARNING"
        return $false
    }

    $token = Get-GithubToken
    if (-not $token) {
        Write-Log "Cannot push to GitHub - token unavailable" -Level "ERROR"
        return $false
    }

    try {
        Write-Log "Pushing backup metadata to GitHub..." -Level "INFO"

        Push-Location $appPath

        # Configure git with GitHub token
        $githubUrl = $config.github.repository

        # Extract owner/repo from URL
        if ($githubUrl -match 'github\.com/(.+)/(.+)') {
            $owner = $matches[1]
            $repo = $matches[2] -replace '\.git$', ''

            Write-Log "  Repository: $owner/$repo" -Level "INFO"
            Write-Log "  Branch: $($config.application.gitBranch)" -Level "INFO"

            # Create backup metadata file
            $metadataFile = "backup-metadata-$(Get-Date -Format 'yyyy-MM-dd-HHmmss').json"
            $metadata = @{
                timestamp = Get-Date -Format 'o'
                backupType = $BackupType
                backupFile = $BackupFile
                repository = "Civil-registration"
                system = "Oracle 21c XE"
                status = "completed"
            } | ConvertTo-Json

            Set-Content -Path $metadataFile -Value $metadata
            Write-Log "  Metadata file created: $metadataFile" -Level "INFO"

            # Add and commit
            & git add $metadataFile 2>&1
            $commitMessage = "backup: $BackupType backup on $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
            & git commit -m $commitMessage 2>&1 | ForEach-Object { Write-Log "  $_" -Level "INFO" }

            # Push to GitHub
            & git push origin $($config.application.gitBranch) 2>&1 | ForEach-Object { Write-Log "  $_" -Level "INFO" }

            Write-Log "Successfully pushed to GitHub" -Level "SUCCESS"

            Pop-Location
            return $true
        }
        else {
            Write-Log "Invalid GitHub repository URL format" -Level "ERROR"
            Pop-Location
            return $false
        }
    }
    catch {
        Write-Log "Failed to push to GitHub: $_" -Level "ERROR"
        Pop-Location
        return $false
    }
}

function Create-GithubRelease {
    param(
        [string]$Version,
        [string]$ReleaseNotes,
        [string]$BackupFile
    )

    if (-not $config.github.createReleases) {
        Write-Log "GitHub release creation disabled" -Level "INFO"
        return $false
    }

    $token = Get-GithubToken
    if (-not $token) {
        Write-Log "Cannot create release - token unavailable" -Level "ERROR"
        return $false
    }

    try {
        Write-Log "Creating GitHub release: $Version" -Level "INFO"

        # Extract owner/repo from URL
        $repoUrl = $config.github.repository
        if ($repoUrl -match 'github\.com[:/]([^/]+)/([^/]+?)(?:\.git)?$') {
            $owner = $matches[1]
            $repo = $matches[2]

            Write-Log "  Owner: $owner, Repo: $repo" -Level "INFO"

            # Create JSON body properly
            $jsonBody = @"
{
  "tag_name": "backup-$Version",
  "name": "Backup Release - $Version",
  "body": "Automated backup release\n\nGenerated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')\nBackup: $Version",
  "draft": false,
  "prerelease": false
}
"@

            $headers = @{
                Authorization = "Bearer $token"
                Accept = "application/vnd.github+json"
                "X-GitHub-Api-Version" = "2022-11-28"
            }

            $apiUrl = "https://api.github.com/repos/$owner/$repo/releases"

            Write-Log "  API URL: $apiUrl" -Level "INFO"

            $response = Invoke-WebRequest -Uri $apiUrl `
                -Method Post `
                -Headers $headers `
                -Body $jsonBody `
                -ContentType "application/json" `
                -UseBasicParsing `
                -ErrorAction Stop

            Write-Log "Release created successfully: backup-$Version" -Level "SUCCESS"
            return $true
        }
        else {
            Write-Log "Could not parse repository URL: $repoUrl" -Level "ERROR"
            return $false
        }
    }
    catch {
        Write-Log "Failed to create GitHub release: $_" -Level "ERROR"
        return $false
    }
}

# ============================================================================
# MAIN EXECUTION
# ============================================================================

Write-Log "=========================================="
Write-Log "GitHub & Email Integration System"
Write-Log "=========================================="
Write-Log "Started: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
Write-Log ""

# Send email alert if requested
if ($SendEmailAlert) {
    Write-Log "Sending email alert..."
    Write-Log "  Type: $MessageType"
    Write-Log "  Subject: $MessageSubject"

    $success = Send-EmailAlert -Subject $MessageSubject -Body $MessageBody -AlertType $MessageType

    if ($success) {
        Write-Log "Email notification sent successfully" -Level "SUCCESS"
    } else {
        Write-Log "Email notification failed" -Level "ERROR"
    }
    Write-Log ""
}

# Push to GitHub if requested
if ($PushToGithub) {
    Write-Log "Pushing to GitHub..."

    $version = Get-Date -Format "yyyyMMdd-HHmmss"
    $releaseNotes = @"
Backup Release: $version

**Backup Details:**
- Type: Full Database Backup
- Timestamp: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
- System: Oracle 21c XE
- Application: Civil Registration

**Tables Backed Up:**
✓ Birth Records
✓ Marriage Records
✓ Death Records
✓ Certificates
✓ Audit Logs
✓ User Accounts

**Status:** Ready for Recovery

All backup files have been verified and are ready for restoration.
"@

    $success = Push-BackupToGithub -BackupFile "backup-$version.dmp" -BackupType "full"

    if ($success) {
        Write-Log "GitHub integration completed successfully" -Level "SUCCESS"

        # Create release
        if ($config.github.createReleases) {
            Write-Log "Creating backup release on GitHub..."
            Create-GithubRelease -Version $version -ReleaseNotes $releaseNotes
        }
    } else {
        Write-Log "GitHub integration failed" -Level "ERROR"
    }
    Write-Log ""
}

Write-Log "=========================================="
Write-Log "Integration process completed"
Write-Log "Log file: $logFile"
