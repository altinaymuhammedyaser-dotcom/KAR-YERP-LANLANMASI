<?php
/**
 * Akilli Kariyer - Panel: Sertifika Goruntule
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

$panelType     = 'user';
$panelActive   = 'certificates';
$panelTitle    = 'Sertifika: ' . e($cert['exam_title']);
$panelSubtitle = 'Sertifika Kodu: ' . e($cert['certificate_code']);

require_once __DIR__ . '/../includes/panel-header.php';
?>

<style>
    .cert-page { padding:20px; }
    .certificate { border:2px solid #142B4A; border-radius:16px; padding:30px; max-width:800px; margin:0 auto; background:#fff; color:#142B4A; box-shadow:0 10px 40px -12px rgba(20,43,74,.35); }
    .cert-header { border-bottom:4px solid #16B8B0; padding-bottom:16px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:flex-start; }
    .cert-logo { font-size:2rem; font-weight:900; letter-spacing:.04em; color:#142B4A }
    .cert-logo small { font-size:.7rem; color:#1C466D; display:block; margin-top:2px }
    .cert-code { background:var(--c-accent-soft); color:var(--c-accent-dark); border-radius:10px; padding:8px 14px; font-size:.85rem; font-weight:800; white-space:nowrap }
    .cert-title { font-size:1.7rem; font-weight:800; color:#142B4A; margin:10px 0 6px }
    .cert-sub { color:#748399; font-size:.9rem; margin-bottom:16px }
    .cert-meta { display:flex; gap:16px; margin-bottom:20px; flex-wrap:wrap }
    .cert-meta .item { flex:1; border:1px solid var(--c-border); border-radius:12px; padding:14px; background:var(--c-bg) }
    .cert-meta .label { font-size:.7rem; font-weight:800; color:#748399; text-transform:uppercase; letter-spacing:.06em; display:block; margin-bottom:4px }
    .cert-meta .value { font-size:1rem; color:#172B42; font-weight:700 }
    .cert-sign { margin-top:40px; display:flex; justify-content:space-between; align-items:flex-end; gap:20px }
    .cert-sign .sig-line { width:180px; height:1px; background:#142B4A; margin-top:10px }
    .cert-sign .name { font-weight:800; color:#142B4A; font-size:.9rem }
    .cert-sign .role { font-size:.75rem; color:#748399; margin-top:2px }
    .print-only { display:none }
    @media print {
        body { background:#fff !important; -webkit-print-color-adjust:exact }
        .panel-card, .panel-main { box-shadow:none !important; border:none !important; margin:0 !important; padding:0 !important }
        .cert-page { padding:0 }
        .certificate { border:2px solid #142B4A !important; box-shadow:none !important }
        .no-print { display:none !important }
        .print-only { display:block !important }
    }
</style>

<section class="panel-card">
    <div class="pc-head no-print">
        <div>
            <h2>Sertifika: <?= e($cert['exam_title']) ?></h2>
            <p class="pc-sub">Sertifika Kodu: <strong><?= e($cert['certificate_code']) ?></strong></p>
        </div>
        <a href="certificate-download.php?id=<?= (int) $cert['id'] ?>" class="btn btn-accent btn-sm"><?= icon('download', '', 16) ?> PDF İndir</a>
        <button class="btn btn-outline btn-sm" onclick="window.print()"><?= icon('printer', '', 16) ?> Yazdır</button>
    </div>

    <div class="pc-body print-only" style="padding:0">
        <div class="certificate">
            <div class="cert-header">
                <div class="cert-logo">AKILLI KARİYER<br><small>Geleceğini Keşfet</small></div>
                <div class="cert-code"><?= e($cert['certificate_code']) ?></div>
            </div>
            <div class="cert-title">Başarı Sertifikası</div>
            <p class="cert-sub">Bu sertifika, <?= e($cert['first_name'] . ' ' . $cert['last_name']) ?> adlı kullanıcıya verilmiştir.</p>
            <div class="cert-meta">
                <div class="item"><span class="label">Sınav</span><div class="value"><?= e($cert['exam_title']) ?></div></div>
                <div class="item"><span class="label">Kategori</span><div class="value"><?= e($cert['category'] ?: 'Genel') ?></div></div>
                <div class="item"><span class="label">Tarih</span><div class="value"><?= format_date($cert['issued_at'], 'd F Y') ?></div></div>
                <div class="item"><span class="label">E-posta</span><div class="value"><?= e($cert['email']) ?></div></div>
            </div>
            <div class="cert-sign">
                <div>
                    <div class="sig-line"></div>
                    <div class="name">Akıllı Kariyer Rehberi</div>
                    <div class="role">Kariyer Rehberi Platformu</div>
                </div>
                <div style="text-align:right">
                    <div class="sig-line"></div>
                    <div class="name"><?= e($cert['first_name'] . ' ' . $cert['last_name']) ?></div>
                    <div class="role">Sahip</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
