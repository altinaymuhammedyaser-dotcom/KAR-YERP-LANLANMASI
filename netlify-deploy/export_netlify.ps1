# PowerShell script to export the site to Netlify

# Define the source directory
$sourceDir = "C:\\Users\\User\\Documents\\Varsayılan Proje\\akilli-kariyer"

# Define the destination directory
$destDir = "C:\\Users\\User\\Documents\\Varsayılan Proje\\akilli-kariyer\\netlify-deploy"

# Define the zip file name
$zipFile = "netlify-deploy-fixed.zip"

# Define the zip file path
$zipPath = "$destDir\\$zipFile"

# Create the destination directory if it doesn't exist
if (-not (Test-Path $destDir)) {
    New-Item -ItemType Directory -Path $destDir
}

# Copy the files to the destination directory
Copy-Item -Path "$sourceDir\\*" -Destination $destDir -Recurse -Force

# Remove the music.mp3 and slides directory
Remove-Item -Path "$destDir\\music.mp3" -Force
Remove-Item -Path "$destDir\\slides" -Recurse -Force

# Create the zip file
Compress-Archive -Path "$destDir\\*" -DestinationPath $zipPath

# Output the zip file path
Write-Host "Zip file created at $zipPath"