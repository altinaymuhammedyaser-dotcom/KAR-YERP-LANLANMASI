<?php
/**
 * Akilli Kariyer - Demo verisi olusturucu
 * Kullanim: php tools/seed_demo.php
 */

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;
use App\Models\Activity;
use App\Models\Result;
use App\Models\Test;
use App\Models\User;
use App\Services\TestService;

$email = $argv[1] ?? 'demo@akillikariyer.com';
$user  = User::findByEmail($email);

if (!$user) {
    fwrite(STDERR, "Kullanici bulunamadi: $email\n");
    exit(1);
}

if (Result::latestForUser((int) $user->id)) {
    echo "Bu kullanici icin zaten bir sonuc var, islem yapilmadi.\n";
    exit(0);
}

$old = Database::fetchAll("SELECT id FROM `test_sessions` WHERE `user_id` = ?", [$user->id]);
foreach ($old as $row) {
    Database::query('DELETE FROM `test_sessions` WHERE `id` = ?', [$row['id']]);
}

$session   = TestService::start((int) $user->id);
$questions = Test::questions();

$profile = [
    'teknoloji' => 10, 'bilim' => 7, 'arastirma' => 6, 'muhendislik' => 7,
    'analitik' => 10, 'problem_cozme' => 9, 'matematik' => 7, 'teknoloji_becerisi' => 9,
    'arastirma_yetenegi' => 6, 'iletisim_becerisi' => 5, 'organizasyon' => 5,
    'yaraticilik' => 4, 'gorsel_dusunme' => 3, 'sozsel_ifade' => 4,
    'liderlik' => 3, 'el_becerisi' => 2,
    'bilgisayar_basinda' => 10, 'bireysel_calisma' => 6, 'arastirma_yapma' => 5,
    'takim_calismasi' => 5, 'uretim_tasarim' => 4, 'insanlarla_iletisim' => 3,
    'planlama_yonetim' => 3, 'saha_calismasi' => 1,
    'uzaktan_calisma' => 8, 'ofis' => 6, 'karma_calisma' => 6, 'laboratuvar' => 2,
    'okul' => 2, 'hastane' => 1, 'atolye' => 1, 'acik_alan' => 2,
];

$count = 0;
foreach ($questions as $question) {
    $options = $question['options'];
    $chosen  = [];

    $targetFor = static function (int $bias): int {
        return match (true) {
            $bias >= 8 => 5,
            $bias >= 5 => 4,
            $bias >= 3 => 3,
            $bias >= 1 => 2,
            default    => 1,
        };
    };

    if ($question['question_type'] === 'scale') {
        $dim  = $options[0]['mapping_key'] ?? null;
        $bias = $dim && isset($profile[$dim]) ? $profile[$dim] : 3;
        $target = $targetFor($bias);

        $best = null;
        foreach ($options as $option) {
            $diff = abs((int) $option['score_value'] - $target);
            if ($best === null || $diff < $best['diff']) {
                $best = ['option' => $option, 'diff' => $diff];
            }
        }
        if ($best) {
            $chosen[] = (int) $best['option']['id'];
        }
    } else {
        $best = null;
        foreach ($options as $option) {
            $key   = $option['mapping_key'];
            $bias  = ($key && isset($profile[$key])) ? $profile[$key] : 0;
            $total = $bias * 3 + (int) $option['score_value'];
            if ($best === null || $total > $best['score']) {
                $best = ['option' => $option, 'score' => $total];
            }
        }
        if ($best) {
            $chosen[] = (int) $best['option']['id'];
        }

        if ($question['question_type'] === 'multiple') {
            foreach ($options as $option) {
                if (count($chosen) >= 2) break;
                $key = $option['mapping_key'];
                if ($key && isset($profile[$key]) && $profile[$key] >= 4
                    && !in_array((int) $option['id'], $chosen, true)) {
                    $chosen[] = (int) $option['id'];
                }
            }
        }
    }

    $keys = [];
    $sum  = 0;
    foreach ($options as $option) {
        if (in_array((int) $option['id'], $chosen, true)) {
            $keys[]   = $option['option_key'];
            $sum     += (int) $option['score_value'];
        }
    }
    $avg = (int) round($sum / max(1, count($chosen)));

    Test::saveAnswer((int) $session['id'], (int) $question['id'], $chosen, $avg, $keys);
    $count++;
}

Test::updateSession((int) $session['id'], ['answered_count' => $count]);

$completed = TestService::complete(Test::findSession((int) $session['id']), (int) $user->id);

$favories = [];
foreach (array_slice($completed['recommendations'], 0, 4) as $rec) {
    $favories[] = (int) $rec['career']['id'];
}
foreach ([1, 13, 25] as $extra) {
    if (!in_array($extra, $favories, true)) {
        $favories[] = $extra;
    }
}
foreach ($favories as $careerId) {
    Result::addFavorite((int) $user->id, $careerId);
}

Activity::log((int) $user->id, 'favorite', 'Ornek kariyer testi tamamlandi.', 'result', $completed['result_id']);

echo "Demo verisi olusturuldu.\n";
echo "  Kullanici : {$user->email}\n";
echo "  Sonuc ID   : {$completed['result_id']}\n";
echo "  Oneri sayisi: " . count($completed['recommendations']) . "\n";
echo "  Ilk 5 oneri:\n";
foreach (array_slice($completed['recommendations'], 0, 5) as $rec) {
    printf("    %-34s %6.2f\n", $rec['career']['name'], $rec['score']);
}
