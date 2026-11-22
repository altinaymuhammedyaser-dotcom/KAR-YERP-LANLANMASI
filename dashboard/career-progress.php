<?php
require_once __DIR__ . '/../app/bootstrap.php';
use App\Core\Auth;
use App\Models\Result;
use App\Models\Activity;

Auth::requireLogin();
$user = Auth::user();
$userId = (int) $user->id;

$panelTitle = 'Gelişim İzleyici';
$panelSubtitle = 'Kariyer gelişiminizi izleyin.';
$panelActive = 'progress';

// Toplam test sayısı
$totalTests = Result::countByUser($userId);

// Favori sayısı
$favorites = Result::countFavoritesByUser($userId);

// Aktivite sayısı
$activities = Activity::countByUser($userId);

// Son test tarihi
$lastTest = Result::lastTestByUser($userId);

// Son aktivite tarihi
$lastActivity = Activity::lastActivityByUser($userId);

// Son giriş tarihi
$lastLogin = $user->last_login_at;

// Son test sonucu
$lastResult = $lastTest ? $lastTest->result : null;

// Test sonucu (örneğin: %85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';

// Test sonucu (örneğin: 85)
$lastResultScore = $lastResult ? $lastResult->overall_score : 0;

// Test sonucu (örneğin: Harika)
$lastResultText = $lastResult ? $lastResult->dominant_type : 'Yok';
n