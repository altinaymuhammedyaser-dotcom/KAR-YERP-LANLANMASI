<?php
/**
 * Akilli Kariyer - Admin: Sinav Islemleri (Ekle/Duzenle/Sil/Pasif-Yap)
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireAdmin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$id     = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);

if ($action === 'create') {
    $title        = $_POST['title'] ?? '';
    $category     = $_POST['category'] ?? null;
    $description  = $_POST['description'] ?? null;
    $time_limit   = (int) ($_POST['time_limit_minutes'] ?? 30);
    $passing_score= (int) ($_POST['passing_score'] ?? 70);
    $certificate  = isset($_POST['certificate']) ? 1 : 0;

    if (trim($title) === '') {
        $_SESSION['error'] = 'Sınav adı gereklidir.';
        header('Location: ' . url('admin/exams.php'));
        exit;
    }

    \App\Core\Database::execute(
        "INSERT INTO exams (title, category, description, time_limit_minutes, passing_score, certificate, is_active, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())",
        [$title, $category, $description, $time_limit, $passing_score, $certificate]
    );
    $_SESSION['success'] = 'Sınav başarıyla oluşturuldu.';
    header('Location: ' . url('admin/exams.php'));
    exit;
}

if ($action === 'toggle' && $id > 0) {
    $exam = \App\Core\Database::fetch("SELECT is_active FROM exams WHERE id = ?", [$id]);
    if ($exam) {
        \App\Core\Database::execute("UPDATE exams SET is_active = ? WHERE id = ?", [1 - (int) $exam['is_active'], $id]);
    }
    header('Location: ' . url('admin/exams.php'));
    exit;
}

if ($action === 'delete' && $id > 0) {
    \App\Core\Database::execute("DELETE FROM certificates WHERE exam_id = ?", [$id]);
    \App\Core\Database::execute("DELETE FROM exam_results WHERE exam_id = ?", [$id]);
    \App\Core\Database::execute("DELETE FROM exam_questions WHERE exam_id = ?", [$id]);
    \App\Core\Database::execute("DELETE FROM exams WHERE id = ?", [$id]);
    $_SESSION['success'] = 'Sınav ve ilgili tüm veriler silindi.';
    header('Location: ' . url('admin/exams.php'));
    exit;
}

header('Location: ' . url('admin/exams.php'));
exit;
