# =====================================================================
# AKILLI KARİYER - Uctan uca dogrulama betigi
# Kullanim: powershell -ExecutionPolicy Bypass -File tools/verify.ps1
# =====================================================================
param(
    [string]$Base = "http://127.0.0.1:8080"
)

$script:pass = 0; $script:fail = 0

function Check([string]$name, [bool]$ok, [string]$detail = "") {
    if ($ok) { $script:pass++; Write-Host ("  [OK]   {0} {1}" -f $name, $detail) -ForegroundColor Green }
    else     { $script:fail++; Write-Host ("  [FAIL] {0} {1}" -f $name, $detail) -ForegroundColor Red }
}
function Section([string]$t) { Write-Host "`n=== $t ===" -ForegroundColor Cyan }

function Raw([string]$method, [string]$path, $body = $null, $session = $null, $json = $false) {
    $req = [System.Net.HttpWebRequest]::Create("$Base$path")
    $req.Method = $method
    $req.AllowAutoRedirect = $false
    $req.UserAgent = "VerifyScript/1.0"
    if ($session) { $req.CookieContainer = $session }
    if ($json) { $req.ContentType = "application/json" }
    elseif ($body) { $req.ContentType = "application/x-www-form-urlencoded" }
    if ($body -ne $null) {
        $bytes = [System.Text.Encoding]::UTF8.GetBytes($body)
        $req.ContentLength = $bytes.Length
        $st = $req.GetRequestStream(); $st.Write($bytes, 0, $bytes.Length); $st.Close()
    }
    try {
        $resp = $req.GetResponse()
        $sr = New-Object System.IO.StreamReader($resp.GetResponseStream())
        $content = $sr.ReadToEnd()
        $code = [int]$resp.StatusCode
        $loc = $resp.Headers["Location"]
        $resp.Close()
        return @{ Code = $code; Location = $loc; Content = $content; Session = $session }
    } catch [System.Net.WebException] {
        $resp = $_.Exception.Response
        if ($resp -eq $null) { throw }
        $code = [int]$resp.StatusCode
        $loc = $resp.Headers["Location"]
        $stream = $resp.GetResponseStream()
        $content = ""
        if ($stream) { $sr = New-Object System.IO.StreamReader($stream); $content = $sr.ReadToEnd() }
        return @{ Code = $code; Location = $loc; Content = $content; Session = $session }
    }
}

function GetToken([string]$path, $session) {
    $r = Raw "GET" $path $null $session
    return ([regex]::Match($r.Content, 'name="_token" value="([^"]+)"')).Groups[1].Value
}

# ---------------------------------------------------------------------
Section "1. Kayit ve giris"
$email = "kullanici" + (Get-Random -Maximum 99999) + "@ornek.com"
$s = New-Object System.Net.CookieContainer
$t = GetToken "/register.php" $s
Check "CSRF token uretildi" ($t.Length -eq 64)

$body = "_token=$t&first_name=Deniz&last_name=Aydin&email=$email&password=Sifre%212345&password_confirmation=Sifre%212345&education_level=universite&kvkk=1"
$r = Raw "POST" "/register.php" $body $s
Check "Kayit yonlendirmesi (302)" ($r.Code -eq 302) "-> $($r.Location)"

$s2 = New-Object System.Net.CookieContainer
$t2 = GetToken "/login.php" $s2
$r = Raw "POST" "/login.php" "_token=$t2&email=$email&password=yanlis" $s2
Check "Hatali sifre reddedildi" ($r.Code -eq 200 -and $r.Content -match "alert-danger") "-> HTTP $($r.Code)"

$s2 = New-Object System.Net.CookieContainer
$t2 = GetToken "/login.php" $s2
$r = Raw "POST" "/login.php" "_token=$t2&email=$email&password=Sifre%212345" $s2
Check "Dogru sifre ile giris" ($r.Code -eq 302 -and $r.Location -like "*dashboard*") "-> $($r.Location)"

# ---------------------------------------------------------------------
Section "2. Yetki kontrolu"
$guest = New-Object System.Net.CookieContainer
foreach ($p in @("/dashboard/index.php", "/admin/index.php", "/career-test.php", "/dashboard/results.php")) {
    $r = Raw "GET" $p $null $guest
    Check "Yetkisiz erisim engellendi: $p" ($r.Code -eq 302 -and $r.Location -like "*login.php*") "-> HTTP $($r.Code) $($r.Location)"
}

$r = Raw "GET" "/admin/index.php" $null $s2
Check "Kullanici admin paneline giremedi" ($r.Code -eq 403) "-> HTTP $($r.Code)"

# ---------------------------------------------------------------------
Section "3. Kariyer testi akisi"
$r = Raw "GET" "/career-test.php" $null $s2
Check "Test ekrani yuklendi" ($r.Code -eq 200 -and $r.Content -match "test-root")
$csrf = ([regex]::Match($r.Content, 'name="csrf-token" content="([^"]+)"')).Groups[1].Value
$sessionId = ([regex]::Match($r.Content, 'data-session="(\d+)"')).Groups[1].Value
$total = ([regex]::Match($r.Content, 'data-total="(\d+)"')).Groups[1].Value
Check "Oturum olusturuldu" ([int]$sessionId -gt 0) "session=$sessionId, toplam=$total soru"

$api = Raw "POST" "/api/test.php" ('{"_token":"' + $csrf + '","action":"status","session_id":' + $sessionId + '}') $s2 $true
$st = $api.Content | ConvertFrom-Json
Check "Test durumu API'si calisiyor" ($st.success) "answered=$($st.answered)/$($st.total)"

$questionsJson = ([regex]::Match($r.Content, "data-questions='([^']*)'")).Groups[1].Value
$questions = ($questionsJson -replace "&quot;", '"' -replace "&#039;", "'" -replace "&amp;", "&" | ConvertFrom-Json)
Check "Sorular istemciye aktarildi" ($questions.Count -eq [int]$total) "$($questions.Count) soru"

$answered = 0
foreach ($q in $questions) {
    $opt = $q.options | Select-Object -First 1
    $ids = @([int]$opt.id)
    if ($q.question_type -eq 'multiple') { $ids += @([int]$q.options[1].id) }
    $payload = '{"_token":"' + $csrf + '","action":"answer","session_id":' + $sessionId + ',"question_id":' + [int]$q.id + ',"option_ids":[' + ($ids -join ',') + ']}'
    $resp = Raw "POST" "/api/test.php" $payload $s2 $true
    if ($resp.Code -eq 200 -and ($resp.Content | ConvertFrom-Json).success) { $answered++ }
}
Check "Tum sorular cevaplandi ve kaydedildi" ($answered -eq $questions.Count) "$answered/$($questions.Count)"

$resp = Raw "POST" "/api/test.php" ('{"_token":"' + $csrf + '","action":"complete","session_id":' + $sessionId + '}') $s2 $true
$comp = $resp.Content | ConvertFrom-Json
Check "Test tamamlandi ve sonuc uretildi" ($comp.success -and $comp.result_id -gt 0) "result_id=$($comp.result_id)"

# ---------------------------------------------------------------------
Section "4. Sonuc ekrani"
$r = Raw "GET" "/test-result.php?session=$sessionId" $null $s2
Check "Sonuc ekrani yuklendi" ($r.Code -eq 200)
$cards = ([regex]::Matches($r.Content, 'class="mc-rank"')).Count
Check "En uyumlu 5 meslek gosterildi" ($cards -eq 5) "$cards kart"
$charts = ([regex]::Matches($r.Content, 'data-chart=')).Count
Check "Chart.js grafikleri eklendi" ($charts -ge 4) "$charts grafik"
$scores = [regex]::Matches($r.Content, 'ms-val">%([\d\.]+)') | ForEach-Object { [double]$_.Groups[1].Value }
$sorted = ($scores | Sort-Object -Descending)
Check "Meslekler uyum puanina gore sirali" (($scores -join ",") -eq ($sorted -join ",")) ($scores -join ", ")

# ---------------------------------------------------------------------
Section "5. Favori sistemi"
$resp = Raw "POST" "/api/favorites.php" ('{"_token":"' + $csrf + '","action":"add","career_id":3}') $s2 $true
$fav = $resp.Content | ConvertFrom-Json
Check "Favoriye eklendi" ($fav.success -and $fav.favorite) "count=$($fav.count)"

$resp = Raw "POST" "/api/favorites.php" ('{"_token":"' + $csrf + '","action":"add","career_id":3}') $s2 $true
$fav2 = $resp.Content | ConvertFrom-Json
Check "Cift ekleme engellendi" ($fav2.favorite -eq $true -and $fav2.count -eq 1) "count=$($fav2.count)"

$resp = Raw "POST" "/api/favorites.php" ('{"_token":"' + $csrf + '","action":"remove","career_id":3}') $s2 $true
$fav3 = $resp.Content | ConvertFrom-Json
Check "Favoriden cikarildi" ($fav3.success -and -not $fav3.favorite) "count=$($fav3.count)"

$resp = Raw "POST" "/api/favorites.php" '{"action":"add","career_id":3}' $s2 $true
Check "CSRF'siz istek reddedildi" ($resp.Code -eq 419) "-> HTTP $($resp.Code)"

# ---------------------------------------------------------------------
Section "6. Panel sayfaları"
foreach ($p in @("/dashboard/index.php","/dashboard/tests.php","/dashboard/results.php","/dashboard/recommendations.php","/dashboard/favorites.php","/dashboard/explore.php","/dashboard/activities.php","/dashboard/profile.php","/dashboard/settings.php")) {
    $r = Raw "GET" $p $null $s2
    $ok = ($r.Code -eq 200 -and $r.Content -notmatch "Fatal error|Uncaught")
    Check "Panel: $p" $ok "HTTP $($r.Code)"
}

# ---------------------------------------------------------------------
Section "7. Admin paneli"
$sa = New-Object System.Net.CookieContainer
$ta = GetToken "/login.php" $sa
$r = Raw "POST" "/login.php" "_token=$ta&email=admin%40akillikariyer.com&password=Admin%212345" $sa
Check "Yonetici girisi" ($r.Code -eq 302) "-> $($r.Location)"

$r = Raw "GET" "/admin/index.php" $null $sa
$acsrf = ([regex]::Match($r.Content, 'name="csrf-token" content="([^"]+)"')).Groups[1].Value

foreach ($p in @("/admin/index.php","/admin/users.php","/admin/careers.php","/admin/career-edit.php","/admin/categories.php","/admin/questions.php","/admin/question-edit.php","/admin/blog.php","/admin/blog-edit.php","/admin/messages.php","/admin/settings.php")) {
    $r = Raw "GET" $p $null $sa
    $ok = ($r.Code -eq 200 -and $r.Content -notmatch "Fatal error|Uncaught")
    Check "Admin: $p" $ok "HTTP $($r.Code)"
}

# ---------------------------------------------------------------------
Section "8. API ve AJAX"
$r = Raw "GET" "/api/careers.php?category=teknoloji&sayfa=1" $null $s2
$json = $r.Content | ConvertFrom-Json
Check "Meslek filtresi API'si" ($json.success -and $json.html -match "career-card") "toplam=$($json.total)"

$r = Raw "GET" "/api/recommendations.php" $null $s2
$json = $r.Content | ConvertFrom-Json
Check "Oneri API'si" ($json.success -and $json.recommendations.Count -gt 0) "$($json.recommendations.Count) oneri"

$r = Raw "GET" "/api/careers.php?q=yazilim" $null $s2
$json = $r.Content | ConvertFrom-Json
Check "Meslek arama calisiyor" ($json.success -and $json.total -gt 0) "$($json.total) sonuc"

# ---------------------------------------------------------------------
Section "9. PDF raporu"
try {
    $pdfPath = Join-Path $env:TEMP "akilli-kariyer-rapor.pdf"
    $req = [System.Net.HttpWebRequest]::Create("$Base/api/result-pdf.php?session=$sessionId")
    $req.Method = "GET"; $req.CookieContainer = $s2
    try {
        $resp = $req.GetResponse()
        $in = $resp.GetResponseStream()
        $out = [System.IO.File]::Create($pdfPath)
        $in.CopyTo($out); $out.Close(); $in.Close(); $resp.Close()
        $bytes = [System.IO.File]::ReadAllBytes($pdfPath)
        $sig = [System.Text.Encoding]::ASCII.GetString($bytes[0..3])
        Check "PDF raporu olusturuldu" ($sig -eq "%PDF" -and $bytes.Length -gt 20000) "imza=$sig boyut=$([math]::Round($bytes.Length/1KB,1)) KB"
    } catch {
        Check "PDF raporu olusturuldu" $false "-> $($_.Exception.Message)"
    }
} catch {
    Check "PDF raporu olusturuldu" $false "-> $($_.Exception.Message)"
}

# ---------------------------------------------------------------------
Section "10. Sifreleme ve veri butunlugu"
$hash = (& "C:\xampp\mysql\bin\mysql.exe" -u root -N -B -e "USE akilli_kariyer; SELECT password FROM users WHERE email='$email';" 2>$null)
Check "Sifre hash olarak saklandi" ($hash -like '$2y$*' -or $hash -like '$argon2*') ($hash.Substring(0, [Math]::Min(10, $hash.Length)) + "...")
$plain = (& "C:\xampp\mysql\bin\mysql.exe" -u root -N -B -e "USE akilli_kariyer; SELECT COUNT(*) FROM users WHERE password='Sifre!2345';" 2>$null)
Check "Duz metin sifre saklanmadi" ($plain.Trim() -eq "0")

Write-Host "`n============================================" -ForegroundColor Cyan
if ($fail -eq 0) { Write-Host ("  BASARILI: {0}   BASARISIZ: {1}" -f $pass, $fail) -ForegroundColor Green }
else { Write-Host ("  BASARILI: {0}   BASARISIZ: {1}" -f $pass, $fail) -ForegroundColor Red }
Write-Host "============================================" -ForegroundColor Cyan
