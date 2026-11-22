# Akıllı Kariyer - Güvenli 404 Sayfası
require_once __DIR__ . '/app/bootstrap.php';

abort_page(
    404,
    'Sayfa Bulunamadı',
    'Aradığınız sayfa taşınmış, adı değişmiş veya hiç var olmamış olabilir. '
    . 'Aşağıdaki bağlantılardan devam edebilirsiniz.',
    url('index.php')
);
