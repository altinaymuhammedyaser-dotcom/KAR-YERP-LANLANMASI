<?php
/**
 * Akilli Kariyer - Cikis islemi
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\Auth;

if (Auth::check()) {
    $user = Auth::user();
    \App\Models\Activity::log((int) $user->id, 'login', 'Oturumu kapattınız.', 'user', (int) $user->id);
}

Auth::logout();
session_start();

set_flash('success', 'Oturumunuz güvenli bir şekilde kapatıldı. Tekrar görüşmek üzere!');
redirect(url('index.php'));
