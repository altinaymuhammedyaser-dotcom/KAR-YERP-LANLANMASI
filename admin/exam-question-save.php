<?php
/**
 * Akilli Kariyer - Admin: Sinav Soru Islemleri
 */
require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Auth;

Auth::requireAdmin();

$action   = $_POST['action'] ?? $_GET['action'] ?? '';
$id       = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$examId   = (int) ($_POST['exam_id'] ?? $_GET['exam_id'] ?? 0);

if ($action === 'create' && $examId > 0) {
    $question     = $_POST['question'] ?? '';
    $type         = $_POST['type'] ?? 'single';
    $optionsInput = $_POST['options'] ?? '';
    $correctInput = $_POST['correct_answer'] ?? '';
    $points       = (int) ($_POST['points'] ?? 10);

    if (trim($question) === '') {
        $_SESSION['error'] = 'Soru metni gereklidir.';
        header('Location: ' . url('admin/exam-questions.php?id=' . $examId));
        exit;
    }

    $options = [];
    if ($type !== 'true_false' && $optionsInput !== '') {
        $raw = explode(',', $optionsInput);
        foreach ($raw as $o) {
            $o = trim($o);
            if ($o !== '') $options[] = $o;
        }
    } elseif ($type === 'true_false') {
        $options = ['Doğru', 'Yanlış'];
    }

    $correct = [];
    if ($correctInput !== '') {
        $raw = explode(',', $correctInput);
        foreach ($raw as $c) {
            $c = trim($c);
            if ($c !== '') $correct[] = $c;
        }
    }

    \App\Core\Database::execute(
        "INSERT INTO exam_questions (exam_id, question, type, options, correct_answer, points, sort_order, created_at)
         VALUES (?, ?, ?, ?, ?, ?, (SELECT COALESCE(MAX(sort_order),0)+1 FROM exam_questions WHERE exam_id = ?), NOW())",
        [$examId, $question, $type, json_encode($options, JSON_UNESCAPED_UNICODE), json_encode($correct, JSON_UNESCAPED_UNICODE), $points, $examId]
    );
    $_SESSION['success'] = 'Soru başarıyla eklendi.';
    header('Location: ' . url('admin/exam-questions.php?id=' . $examId));
    exit;
}

if ($action === 'delete' && $id > 0 && $examId > 0) {
    \App\Core\Database::execute("DELETE FROM exam_questions WHERE id = ? AND exam_id = ?", [$id, $examId]);
    $_SESSION['success'] = 'Soru silindi.';
    header('Location: ' . url('admin/exam-questions.php?id=' . $examId));
    exit;
}

header('Location: ' . url('admin/exams.php'));
exit;
