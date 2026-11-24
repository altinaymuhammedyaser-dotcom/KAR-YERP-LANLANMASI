<?php
/**
 * Akilli Kariyer - Panel: Sertifika Indir
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$certId = (int) ($_GET['id'] ?? 0);
if (!$certId) {
    header('Location: ' . url('dashboard/certificates.php'));
    exit;
}

$cert = \App\Core\Database::fetch("
    SELECT c.*, u.first_name, u.last_name, u.email, e.title AS exam_title, e.category
    FROM certificates c
    JOIN users u ON u.id = c.user_id
    JOIN exams e ON e.id = c.exam_id
    WHERE c.id = ? AND c.user_id = ?
", [$certId, $userId]);
if (!$cert) {
    header('Location: ' . url('dashboard/certificates.php'));
    exit;
}

// Basit HTML tabanli sertifika; yazdirma/PDF icin tarayici tarafindan kaydedilebilir.
$html = '<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="utf-8">
<title>Sertifika - ' . htmlspecialchars($cert['exam_title'], ENT_QUOTES) . '</title>
<style>
    body { font-family: "Plus Jakarta Sans", sans-serif; color:#142B4A; margin:0; padding:20px; background:#fff }
    .certificate { border:2px solid #142B4A; border-radius:16px; padding:30px; max-width:800px; margin:0 auto; box-shadow:0 10px 40px -12px rgba(20,43,74,.35) }
    .cert-header { border-bottom:4px solid #16B8B0; padding-bottom:16px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:flex-start }
    .cert-logo { font-size:2rem; font-weight:900; letter-spacing:.04em; color:#142B4A }
    .cert-logo small { font-size:.7rem; color:#1C466D; display:block; margin-top:2px }
    .cert-code { background:#E4F7F6; color:#0E918B; border-radius:10px; padding:8px 14px; font-size:.85rem; font-weight:800; white-space:nowrap }
    .cert-title { font-size:1.7rem; font-weight:800; color:#142B4A; margin:10px 0 6px }
    .cert-sub { color:#748399; font-size:.9rem; margin-bottom:16px }
    .cert-meta { display:flex; gap:16px; margin-bottom:20px; flex-wrap:wrap }
    .cert-item { flex:1; border:1px solid #E6ECF5; border-radius:12px; padding:14px; background:#F6F8FC }
    .cert-label { font-size:.7rem; font-weight:800; color:#748399; text-transform:uppercase; letter-spacing:.06em; display:block; margin-bottom:4px }
    .cert-value { font-size:1rem; color:#172B42; font-weight:700 }
    .cert-sign { margin-top:40px; display:flex; justify-content:space-between; align-items:flex-end; gap:20px }
    .sig-line { width:180px; height:1px; background:#142B4A; margin-top:10px }
    .sig-name { font-weight:800; color:#142B4A; font-size:.9rem }
    .sig-role { font-size:.75rem; color:#748399; margin-top:2px }
    @page { size: A4; margin: 15mm }
    @media print { body { padding:0 } .no-print { display:none } }
</style>
</head>
<body>
<div class="certificate">
    <div class="cert-header">
        <div class="cert-logo">AKILLI KARİYER<br><small>Geleceğini Keşfet</small></div>
        <div class="cert-code">' . htmlspecialchars($cert['certificate_code'], ENT_QUOTES) . '</div>
    </div>
    <div class="cert-title">Başarı Sertifikası</div>
    <p class="cert-sub">Bu sertifika, ' . htmlspecialchars($cert['first_name'] . ' ' . $cert['last_name'], ENT_QUOTES) . ' adlı kullanıcıya verilmiştir.</p>
    <div class="cert-meta">
        <div class="cert-item"><span class="cert-label">Sınav</span><div class="cert-value">' . htmlspecialchars($cert['exam_title'], ENT_QUOTES) . '</div></div>
        <div class="cert-item"><span class="cert-label">Kategori</span><div class="cert-value">' . htmlspecialchars($cert['category'] ?: 'Genel', ENT_QUOTES) . '</div></div>
        <div class="cert-item"><span class="cert-label">Tarih</span><div class="cert-value">' . date('d F Y', strtotime($cert['issued_at'])) . '</div></div>
        <div class="cert-item"><span class="cert-label">E-posta</span><div class="cert-value">' . htmlspecialchars($cert['email'], ENT_QUOTES) . '</div></div>
    </div>
    <div class="cert-sign">
        <div>
            <div class="sig-line"></div>
            <div class="sig-name">Akıllı Kariyer Rehberi</div>
            <div class="sig-role">Kariyer Rehberi Platformu</div>
        </div>
        <div style="text-align:right">
            <div class="sig-line"></div>
            <div class="sig-name">' . htmlspecialchars($cert['first_name'] . ' ' . $cert['last_name'], ENT_QUOTES) . '</div>
            <div class="sig-role">Sahip</div>
        </div>
    </div>
</div>
<script>window.print()</script>
</body>
</html>';

header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="sertifika-' . $cert['certificate_code'] . '.html"');
echo $html;
exit;
