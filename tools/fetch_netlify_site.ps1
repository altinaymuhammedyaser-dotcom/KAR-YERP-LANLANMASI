param(
  [string]$Base = 'https://akillikariyer.netlify.app',
  [string]$Out = 'C:\Users\User\Documents\Varsayılan Proje\akilli-kariyer\netlify-src',
  [int]$Max = 320
)

$ErrorActionPreference = 'Stop'
if (-not (Test-Path $Out)) { New-Item -ItemType Directory -Path $Out -Force | Out-Null }

$queue = [System.Collections.Generic.Queue[string]]::new()
$seen = [System.Collections.Generic.HashSet[string]]::new()
$queue.Enqueue('/')
[void]$seen.Add('/')

function LocalPath([string]$p) {
  $p = $p.Split('?')[0].Split('#')[0]
  if ($p -eq '/' -or $p -eq '') { return 'index.html' }
  $p = $p.TrimStart('/')
  if ([System.IO.Path]::GetExtension($p) -eq '') { $p = $p + '/index.html' }
  return $p.Replace('/', [System.IO.Path]::DirectorySeparatorChar)
}

$count = 0
while ($queue.Count -gt 0 -and $count -lt $Max) {
  $path = $queue.Dequeue()
  $count++
  $url = $Base + $path
  $lp = Join-Path $Out (LocalPath $path)
  $dir = Split-Path $lp -Parent
  if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
  try {
    $r = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 25
    $ext = [System.IO.Path]::GetExtension($lp).ToLowerInvariant()
    if ($ext -in @('.png','.jpg','.jpeg','.gif','.webp','.ico','.mp4','.woff','.woff2','.ttf','.pdf','.svg')) {
      [System.IO.File]::WriteAllBytes($lp, $r.Content)
    } else {
      [System.IO.File]::WriteAllText($lp, $r.Content, [System.Text.Encoding]::UTF8)
    }
    Write-Output ("OK $path $($r.StatusCode)")
    $ct = $r.Headers['Content-Type'] -join ''
    if ($ct -like '*html*') {
      $matches2 = [regex]::Matches($r.Content, '(?:href|src)\s*=\s*["'']([^"'']+)["'']')
      foreach ($m in $matches2) {
        $v = $m.Groups[1].Value
        if ($v -match '^(https?:)?//') {
          if ($v -like '*akillikariyer.netlify.app*') { $v = $v -replace '^https?://akillikariyer\.netlify\.app','' }
          else { continue }
        }
        if ($v.StartsWith('mailto:') -or $v.StartsWith('tel:') -or $v.StartsWith('data:') -or $v.StartsWith('javascript:')) { continue }
        $v = $v.Split('?')[0].Split('#')[0]
        if (-not $v.StartsWith('/')) { continue }
        if ($v -eq '/') { $v = '/' }
        if (-not $seen.Contains($v)) { [void]$seen.Add($v); $queue.Enqueue($v) }
      }
    }
  } catch {
    Write-Output ("ERR $path $($_.Exception.Message)")
  }
}
Write-Output ("TOPLAM $count")
