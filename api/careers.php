<?php
/**
 * Akilli Kariyer - Meslek listesi API (AJAX filtreleme)
 *
 * GET /api/careers.php?q=&category=&education=&interest=&sort=&sayfa=
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Models\Career;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

try {
    $search    = trim((string) ($_GET['q'] ?? ''));
    $category  = trim((string) ($_GET['category'] ?? ''));
    $education = trim((string) ($_GET['education'] ?? ''));
    $interest  = trim((string) ($_GET['interest'] ?? ''));
    $sort      = trim((string) ($_GET['sort'] ?? 'popular'));
    $page      = max(1, (int) ($_GET['sayfa'] ?? $_GET['page'] ?? 1));

    $filters = array_filter([
        'search'    => $search,
        'category'  => $category,
        'education' => $education,
        'interest'  => $interest,
    ], static fn($v) => $v !== '');

    $total   = Career::count($filters);
    $perPage = CAREERS_PER_PAGE;
    $offset  = ($page - 1) * $perPage;
    $careers = Career::list($filters + ['sort' => $sort, 'limit' => $perPage, 'offset' => $offset]);

    ob_start();
    if (!$careers) {
        echo '<div class="empty-state">'
           . '<div class="es-ico">' . icon('search-x', '', 30) . '</div>'
           . '<h3>Sonuç bulunamadı</h3>'
           . '<p>Arama kriterlerinize uygun meslek bulunamadı. Filtreleri değiştirerek tekrar deneyin.</p>'
           . '<a href="' . e(url('careers.php')) . '" class="btn btn-primary">' . icon('refresh-cw', '', 18) . ' Filtreleri Temizle</a>'
           . '</div>';
    } else {
        echo '<div class="grid g-3">';
        foreach ($careers as $job) {
            require APP_ROOT . '/includes/career-card.php';
        }
        echo '</div>';

        echo pagination($page, $total, url('careers.php'), array_filter([
            'q'         => $search,
            'category'  => $category,
            'education' => $education,
            'interest'  => $interest,
            'sort'      => $sort,
        ]));
    }
    $html = ob_get_clean();

    json_response([
        'success' => true,
        'html'    => $html,
        'total'   => $total,
        'page'    => $page,
        'pages'   => (int) ceil($total / $perPage),
    ]);
} catch (\Throwable $e) {
    error_log('[API careers] ' . $e->getMessage());
    json_response([
        'success' => false,
        'message' => APP_DEBUG ? $e->getMessage() : 'Meslekler yüklenirken bir hata oluştu.',
    ], 500);
}
