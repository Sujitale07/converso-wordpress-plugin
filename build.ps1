# Simple build script for Connectapre plugin
$ErrorActionPreference = "Stop"

$pluginDir = Get-Location
$buildDir = Join-Path $pluginDir "build"
$parentDir = Split-Path $pluginDir -Parent
$zipName = "connectapre.zip"
$zipPath = Join-Path $parentDir $zipName

Write-Host "=== Connectapre Plugin Build ===" -ForegroundColor Cyan
Write-Host ""

# Clean previous build
if (Test-Path $buildDir) {
    Write-Host "Removing previous build..." -ForegroundColor Yellow
    Remove-Item $buildDir -Recurse -Force
}

if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

# Create build directory
Write-Host "Creating build directory..." -ForegroundColor Yellow
New-Item -ItemType Directory -Path $buildDir | Out-Null

# Copy all files
Write-Host "Copying plugin files..." -ForegroundColor Yellow
Copy-Item -Path "$pluginDir\*" -Destination $buildDir -Recurse -Force -Exclude @('.git', 'node_modules', 'vendor', 'build')

# Read and process .distignore
Write-Host "Processing .distignore..." -ForegroundColor Yellow
$distignorePath = Join-Path $pluginDir ".distignore"
$excludePatterns = Get-Content $distignorePath | Where-Object { 
    $_.Trim() -ne "" -and -not $_.StartsWith("#") 
}

$removedCount = 0
foreach ($pattern in $excludePatterns) {
    $pattern = $pattern.Trim().Replace('/', '\')
    
    if ($pattern.StartsWith('\')) {
        $pattern = $pattern.Substring(1)
        $targetPath = Join-Path $buildDir $pattern
        if (Test-Path $targetPath) {
            Remove-Item $targetPath -Recurse -Force -ErrorAction SilentlyContinue
            $removedCount++
        }
    }
    elseif ($pattern.Contains('*')) {
        $items = Get-ChildItem -Path $buildDir -Recurse -Filter $pattern -Force -ErrorAction SilentlyContinue
        foreach ($item in $items) {
            Remove-Item $item.FullName -Recurse -Force -ErrorAction SilentlyContinue
            $removedCount++
        }
    }
    else {
        $items = Get-ChildItem -Path $buildDir -Recurse -Force -ErrorAction SilentlyContinue | Where-Object {
            $_.Name -eq $pattern
        }
        foreach ($item in $items) {
            Remove-Item $item.FullName -Recurse -Force -ErrorAction SilentlyContinue
            $removedCount++
        }
    }
}

Write-Host "Removed $removedCount excluded items" -ForegroundColor Green

# Count files
$fileCount = (Get-ChildItem -Path $buildDir -Recurse -File).Count
Write-Host "Packaging $fileCount files..." -ForegroundColor Yellow

# Create ZIP
Compress-Archive -Path "$buildDir\*" -DestinationPath $zipPath -Force

# Get file size
$zipSize = (Get-Item $zipPath).Length
$zipSizeMB = [math]::Round($zipSize / 1MB, 2)

# Cleanup
Write-Host "Cleaning up..." -ForegroundColor Yellow
Remove-Item $buildDir -Recurse -Force

# Success
Write-Host ""
Write-Host "=== Build Complete ===" -ForegroundColor Green
Write-Host "Package: $zipPath" -ForegroundColor White
Write-Host "Size: $zipSizeMB MB" -ForegroundColor White
Write-Host "Files: $fileCount" -ForegroundColor White
