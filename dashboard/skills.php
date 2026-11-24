<?php
/**
 * Akilli Kariyer - Panel: Becerilerim
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireLogin();
$user   = Auth::user();
$userId = (int) $user->id;

$panelType     = 'user';
$panelActive   = 'skills';
$panelTitle    = 'Becerilerim';
$panelSubtitle = 'Beceri düzeylerinizi takip edin ve geliştirin';

require_once __DIR__ . '/../includes/panel-header.php';
?>

<section class="panel-card">
    <div class="pc-head">
        <div>
            <h2>Beceri Takibi</h2>
            <p class="pc-sub">Hangi becerilerde ne kadar iyi olduğunuzı ve geliştirmeniz gereken alanları görüntüleyin.</p>
        </div>
    </div>

    <div class="pc-body">
        <?php
        $skills = \App\Core\Database::fetchAll("
            SELECT * FROM user_skills WHERE user_id = ? ORDER BY score DESC, skill_name ASC
        ", [$userId]) ?: [];

        if (!$skills):
        ?>
        <div class="empty-state" style="text-align:center;padding:40px 20px">
            <div class="es-ico"><?= icon('layers', '', 40) ?></div>
            <h3>Henüz beceri eklenmedi</h3>
            <p>Sınavlardan başarılı sonuçlar alarak otomatik olarak beceri puanlarınız oluşur.</p>
            <a href="exams.php" class="btn btn-accent"><?= icon('clipboard-list', '', 17) ?> Sınavlara Git</a>
        </div>
        <?php else: ?>
        <div class="panel-grid" style="grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
            <?php foreach ($skills as $skill): ?>
            <div class="panel-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                    <strong><?= e($skill['skill_name']) ?></strong>
                    <span class="badge badge-muted"><?= e($skill['level'] ?: '-') ?></span>
                </div>
                <div style="margin-bottom:8px">
                    <div class="panel-bar" style="width:100%;height:8px;border-radius:99px;background:var(--c-border);overflow:hidden">
                        <span style="height:100%;border-radius:99px;background:linear-gradient(90deg,#142B4A,#16B8B0);width:<?= (int) $skill['score'] ?>%"></span>
                    </div>
                    <div style="font-size:.72rem;color:var(--c-muted);display:flex;justify-content:space-between">
                        <span>Puan</span>
                        <strong><?= (int) $skill['score'] ?></strong>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/panel-footer.php'; ?>
