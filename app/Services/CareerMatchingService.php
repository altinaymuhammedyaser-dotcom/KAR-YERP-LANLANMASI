<?php
/**
 * Akilli Kariyer - Akilli meslek eslestirme algoritmasi
 *
 * Eslesme puani dort bilesenden olusur (agirliklar yonetim panelinden degistirilebilir):
 *   - Ilgi alani uyumu   : %35
 *   - Yetenek uyumu      : %30
 *   - Calisma tercihi    : %20  (tercih %12 + calisma ortami %8)
 *   - Beceri uyumu       : %15
 *
 * Kullanici boyut puanlari 0..1 araliginda, 0.5 = notr seviyedir.
 */

namespace App\Services;

use App\Models\Career;
use App\Models\Setting;

class CareerMatchingService
{
    /* ------------------------------------------------------------------ */
    /* Boyut etiketleri                                                    */
    /* ------------------------------------------------------------------ */

    public const INTEREST_LABELS = [
        'teknoloji'       => 'Teknoloji',
        'bilim'           => 'Bilim',
        'saglik'          => 'Sağlık',
        'egitim'          => 'Eğitim',
        'sanat'           => 'Sanat',
        'tasarim'         => 'Tasarım',
        'spor'            => 'Spor',
        'isletme'         => 'İşletme',
        'ekonomi'         => 'Ekonomi',
        'hukuk'           => 'Hukuk',
        'iletisim'        => 'İletişim',
        'sosyal_hizmet'   => 'Sosyal Hizmetler',
        'muhendislik'     => 'Mühendislik',
        'arastirma'       => 'Araştırma',
    ];

    public const ABILITY_LABELS = [
        'analitik'           => 'Analitik düşünme',
        'matematik'          => 'Matematiksel düşünme',
        'problem_cozme'      => 'Problem çözme',
        'iletisim_becerisi'  => 'İletişim becerisi',
        'yaraticilik'        => 'Yaratıcılık',
        'liderlik'           => 'Liderlik',
        'organizasyon'       => 'Organizasyon',
        'el_becerisi'        => 'El becerisi',
        'arastirma_yetenegi' => 'Araştırma yeteneği',
        'teknoloji_becerisi' => 'Teknolojik beceriler',
        'gorsel_dusunme'     => 'Görsel düşünme',
        'sozsel_ifade'       => 'Sözel ifade',
    ];

    public const PREFERENCE_LABELS = [
        'takim_calismasi'     => 'Takım çalışması',
        'bireysel_calisma'    => 'Bireysel çalışma',
        'insanlarla_iletisim' => 'İnsanlarla iletişim',
        'bilgisayar_basinda' => 'Bilgisayar başında çalışma',
        'saha_calismasi'      => 'Saha çalışması',
        'arastirma_yapma'     => 'Araştırma yapma',
        'uretim_tasarim'      => 'Üretim ve tasarım',
        'planlama_yonetim'    => 'Planlama ve yönetim',
    ];

    public const ENVIRONMENT_LABELS = [
        'ofis'             => 'Ofis ortamı',
        'laboratuvar'      => 'Laboratuvar ortamı',
        'hastane'          => 'Hastane ortamı',
        'okul'             => 'Eğitim ortamı',
        'atolye'           => 'Atölye ortamı',
        'acik_alan'        => 'Açık alan',
        'uzaktan_calisma'  => 'Uzaktan çalışma',
        'karma_calisma'    => 'Karma çalışma',
    ];

    /** @var array<string,array<string,float>>|null */
    private ?array $criteriaCache = null;

    /**
     * Ilgi alani cumleleri ("... ilgin")
     */
    private const INTEREST_PHRASES = [
        'teknoloji'     => 'teknolojiye olan ilgin',
        'bilim'         => 'bilime olan ilgin',
        'saglik'        => 'sağlığa olan ilgin',
        'egitim'        => 'eğitime olan ilgin',
        'sanat'         => 'sanata olan ilgin',
        'tasarim'       => 'tasarıma olan ilgin',
        'spor'          => 'spora olan ilgin',
        'isletme'       => 'işletmeye olan ilgin',
        'ekonomi'       => 'ekonomiye olan ilgin',
        'hukuk'         => 'hukuka olan ilgin',
        'iletisim'      => 'iletişime olan ilgin',
        'sosyal_hizmet' => 'sosyal hizmetlere olan ilgin',
        'muhendislik'   => 'mühendislik alanına ilgin',
        'arastirma'     => 'araştırmaya olan ilgin',
    ];

    /**
     * Yetenek cumleleri ("... becerin")
     */
    private const ABILITY_PHRASES = [
        'analitik'           => 'analitik düşünme gücün',
        'matematik'          => 'matematiksel düşünme gücün',
        'problem_cozme'      => 'problem çözme becerin',
        'iletisim_becerisi'  => 'iletişim becerin',
        'yaraticilik'        => 'yaratıcılık gücün',
        'liderlik'           => 'liderlik özelliğin',
        'organizasyon'       => 'organizasyon becerin',
        'el_becerisi'        => 'el becerin',
        'arastirma_yetenegi' => 'araştırma yeteneğin',
        'teknoloji_becerisi' => 'teknolojik becerilerin',
        'gorsel_dusunme'     => 'görsel düşünme yeteneğin',
        'sozsel_ifade'       => 'sözel ifade gücün',
    ];

    /**
     * Calisma tercihi cumleleri
     */
    private const PREFERENCE_PHRASES = [
        'takim_calismasi'     => 'takım çalışmasına yatkınlığın',
        'bireysel_calisma'    => 'bireysel çalışma tercihin',
        'insanlarla_iletisim' => 'insanlarla iletişim kurma becerin',
        'bilgisayar_basinda' => 'bilgisayar başında çalışma tercihin',
        'saha_calismasi'      => 'saha çalışmasına isteğin',
        'arastirma_yapma'     => 'araştırmaya merakın',
        'uretim_tasarim'      => 'üretim ve tasarıma ilgin',
        'planlama_yonetim'    => 'planlama ve yönetim becerin',
    ];

    /**
     * Gecerli boyut anahtarlari (yanlis eslesmeleri onlemek icin).
     */
    private const DIMENSIONS = [
        'interest'    => ['teknoloji', 'bilim', 'saglik', 'egitim', 'sanat', 'tasarim', 'spor', 'isletme', 'ekonomi', 'hukuk', 'iletisim', 'sosyal_hizmet', 'muhendislik', 'arastirma'],
        'ability'     => ['analitik', 'matematik', 'problem_cozme', 'iletisim_becerisi', 'yaraticilik', 'liderlik', 'organizasyon', 'el_becerisi', 'arastirma_yetenegi', 'teknoloji_becerisi', 'gorsel_dusunme', 'sozsel_ifade'],
        'preference'  => ['takim_calismasi', 'bireysel_calisma', 'insanlarla_iletisim', 'bilgisayar_basinda', 'saha_calismasi', 'arastirma_yapma', 'uretim_tasarim', 'planlama_yonetim'],
        'environment' => ['ofis', 'laboratuvar', 'hastane', 'okul', 'atolye', 'acik_alan', 'uzaktan_calisma', 'karma_calisma'],
    ];

    /**
     * Tum aktif mesleklerin kriterlerini tek sorguda getirir.
     */
    private function criteria(): array
    {
        if ($this->criteriaCache !== null) {
            return $this->criteriaCache;
        }

        $data = [];

        foreach (Career::list(['limit' => 500]) as $career) {
            $id = (int) $career['id'];
            $data[$id] = [
                'interest'    => [],
                'ability'     => [],
                'preference'  => [],
                'environment' => [],
                'competency'  => [],
            ];
        }

        $rows = \App\Core\Database::fetchAll('SELECT `career_id`,`interest_key`,`weight` FROM `career_interests`');
        foreach ($rows as $r) {
            $data[(int) $r['career_id']]['interest'][$r['interest_key']] = (int) $r['weight'];
        }

        $rows = \App\Core\Database::fetchAll('SELECT `career_id`,`ability_key`,`weight` FROM `career_skills`');
        foreach ($rows as $r) {
            $data[(int) $r['career_id']]['ability'][$r['ability_key']] = (int) $r['weight'];
        }

        $rows = \App\Core\Database::fetchAll('SELECT `career_id`,`preference_key`,`weight` FROM `career_preferences`');
        foreach ($rows as $r) {
            $data[(int) $r['career_id']]['preference'][$r['preference_key']] = (int) $r['weight'];
        }

        $rows = \App\Core\Database::fetchAll('SELECT `career_id`,`environment_key`,`weight` FROM `career_environments`');
        foreach ($rows as $r) {
            $data[(int) $r['career_id']]['environment'][$r['environment_key']] = (int) $r['weight'];
        }

        $rows = \App\Core\Database::fetchAll('SELECT `career_id`,`ability_key`,`weight` FROM `career_competencies`');
        foreach ($rows as $r) {
            $data[(int) $r['career_id']]['competency'][] = [
                'ability_key' => $r['ability_key'],
                'weight'      => (int) $r['weight'],
            ];
        }

        $this->criteriaCache = $data;
        return $data;
    }

    /* ------------------------------------------------------------------ */
    /* Kullanici boyut puanlarinin hesaplanmasi                             */
    /* ------------------------------------------------------------------ */

    /**
     * Cevaplardan boyut puanlarini hesaplar.
     *
     * @param array<int,array> $answers   Soru id => ['option_ids'=>[], 'option_keys'=>[]]
     * @param array<int,array> $questions Sorular (options dahil)
     *
     * @return array{interest:array,ability:array,preference:array,environment:array}
     */
    public function calculateDimensionScores(array $answers, array $questions): array
    {
        $sum   = ['interest' => [], 'ability' => [], 'preference' => [], 'environment' => []];
        $total = ['interest' => [], 'ability' => [], 'preference' => [], 'environment' => []];

        foreach ($questions as $question) {
            $qid = (int) $question['id'];
            if (!isset($answers[$qid])) {
                continue;
            }

            $answer  = $answers[$qid];
            $qWeight = (float) ($question['category_weight'] ?? 1);

            $selectedIds = array_map('intval', $answer['option_ids'] ?? []);
            if (!$selectedIds) {
                continue;
            }

            foreach ($question['options'] as $option) {
                if (!in_array((int) $option['id'], $selectedIds, true)) {
                    continue;
                }

                $type = $option['mapping_type'];
                $key  = $option['mapping_key'];

                if ($type === 'none' || !$key) {
                    continue;
                }
                if (!isset(self::DIMENSIONS[$type]) || !in_array($key, self::DIMENSIONS[$type], true)) {
                    continue;
                }

                // 1..5 puani 0..1 araligina donusturulur (3 = notr = 0.5)
                $normalized = ((int) $option['score_value'] - 1) / 4;
                $sum[$type][$key]   = ($sum[$type][$key] ?? 0) + ($normalized * $qWeight);
                $total[$type][$key] = ($total[$type][$key] ?? 0) + $qWeight;
            }
        }

        $scores = [];
        foreach (['interest', 'ability', 'preference', 'environment'] as $type) {
            $scores[$type] = [];
            foreach (array_keys($sum[$type]) as $key) {
                $den = $total[$type][$key] ?? 0;
                $scores[$type][$key] = $den > 0 ? round($sum[$type][$key] / $den, 4) : 0.0;
            }
        }

        return $scores;
    }

    /* ------------------------------------------------------------------ */
    /* Eslestirme                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Kullanici boyut puanlarina gore meslekleri puanlar ve siralar.
     *
     * @return array<int,array{career:array,score:float,components:array,reason:string}>
     */
    public function match(array $scores, ?int $limit = null): array
    {
        $criteria = $this->criteria();

        $wInterest   = max(0, (float) Setting::int('weight_interest', 35));
        $wAbility    = max(0, (float) Setting::int('weight_ability', 30));
        $wPreference = max(0, (float) Setting::int('weight_preference', 20));
        $wSkill      = max(0, (float) Setting::int('weight_skill', 15));
        $envShare    = (float) Setting::int('sub_weight_environment', 8);

        // Ortam payi tercih payini asamaz
        $envShare = max(0, min($envShare, $wPreference));
        $prefShare = $wPreference - $envShare;

        $totalWeight = $wInterest + $wAbility + $wPreference + $wSkill;
        if ($totalWeight <= 0) {
            $totalWeight = 100;
            $wInterest = 35; $wAbility = 30; $wPreference = 20; $wSkill = 15;
        }

        $results = [];

        foreach (Career::list(['limit' => 500]) as $career) {
            $careerId = (int) $career['id'];
            $c = $criteria[$careerId] ?? null;
            if ($c === null) {
                continue;
            }

            $interestFit    = $this->fit($scores['interest']    ?? [], $c['interest']);
            $abilityFit     = $this->fit($scores['ability']     ?? [], $c['ability']);
            $preferenceFit  = $this->fit($scores['preference']  ?? [], $c['preference']);
            $environmentFit = $this->fit($scores['environment'] ?? [], $c['environment']);
            $skillFit       = $this->competencyFit($scores['ability'] ?? [], $c['competency']);

            // Calisma tercihi bileseni = tercih + ortam agirligi
            $preferenceComponent = ($prefShare * $preferenceFit + $envShare * $environmentFit) / max(0.0001, $wPreference);

            $final = (
                $wInterest * $interestFit +
                $wAbility * $abilityFit +
                $wPreference * $preferenceComponent +
                $wSkill * $skillFit
            ) / $totalWeight;

            $results[] = [
                'career'     => $career,
                'score'      => round($final * 100, 2),
                'components' => [
                    'interest'    => round($interestFit * 100, 2),
                    'ability'     => round($abilityFit * 100, 2),
                    'preference'  => round($preferenceFit * 100, 2),
                    'environment' => round($environmentFit * 100, 2),
                    'skill'       => round($skillFit * 100, 2),
                ],
                'reason'     => $this->buildReason($career, $c, $scores),
            ];
        }

        usort($results, static function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return $a['career']['name'] <=> $b['career']['name'];
            }
            return $b['score'] <=> $a['score'];
        });

        if ($limit !== null && $limit > 0) {
            $results = array_slice($results, 0, $limit);
        }

        return $results;
    }

    /**
     * Bir boyut grubunun uyum puani (0..1).
     *
     * Kullanici bu boyutu hic olculmemisse (ilgili soruya hic yonlenmediyse)
     * deger notr (0.5) kabul edilir; aksi halde hic olculmemis bir boyut
     * meslegi haksiz yere cezalandirir.
     */
    private function fit(array $userScores, array $weights): float
    {
        if (!$weights) {
            return 0.5; // Kriter tanimlanmamissa notr kabul edilir.
        }

        $numerator = 0.0;
        $denominator = 0.0;

        foreach ($weights as $key => $weight) {
            $user = array_key_exists($key, $userScores) ? (float) $userScores[$key] : 0.5;
            $numerator   += $user * $weight;
            $denominator += $weight;
        }

        return $denominator > 0 ? $numerator / $denominator : 0.5;
    }

    /**
     * Beceri uyum puani: meslegin gerektirdigi becerilerin kullanicinin
     * yetenek puanlariyla karsilastirilmasi.
     */
    private function competencyFit(array $abilityScores, array $competencies): float
    {
        if (!$competencies) {
            return 0.5;
        }

        $numerator = 0.0;
        $denominator = 0.0;

        foreach ($competencies as $comp) {
            if (!$comp['ability_key']) {
                continue;
            }
            $user = array_key_exists($comp['ability_key'], $abilityScores)
                ? (float) $abilityScores[$comp['ability_key']]
                : 0.5;
            $numerator   += $user * $comp['weight'];
            $denominator += $comp['weight'];
        }

        return $denominator > 0 ? $numerator / $denominator : 0.5;
    }

    /* ------------------------------------------------------------------ */
    /* Oneri gerekcesi olusturma                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Kullanici cevaplarindan hareketle meslek onerisi gerekcesi uretir.
     */
    private function buildReason(array $career, array $criteria, array $scores): string
    {
        $matches = [];

        foreach ($criteria['interest'] as $key => $weight) {
            $value = $scores['interest'][$key] ?? 0;
            if ($value >= 0.5) {
                $matches[] = [
                    'weight'  => $value * $weight,
                    'phrase'  => self::INTEREST_PHRASES[$key] ?? (self::INTEREST_LABELS[$key] ?? $key) . ' alanına ilgin',
                    'label'   => self::INTEREST_LABELS[$key] ?? $key,
                    'value'   => $value,
                ];
            }
        }

        foreach ($criteria['ability'] as $key => $weight) {
            $value = $scores['ability'][$key] ?? 0;
            if ($value >= 0.5) {
                $matches[] = [
                    'weight'  => $value * $weight * 1.1,
                    'phrase'  => self::ABILITY_PHRASES[$key] ?? (self::ABILITY_LABELS[$key] ?? $key) . ' becerin',
                    'label'   => self::ABILITY_LABELS[$key] ?? $key,
                    'value'   => $value,
                ];
            }
        }

        foreach ($criteria['preference'] as $key => $weight) {
            $value = $scores['preference'][$key] ?? 0;
            if ($value >= 0.5) {
                $matches[] = [
                    'weight'  => $value * $weight * 0.85,
                    'phrase'  => self::PREFERENCE_PHRASES[$key] ?? (self::PREFERENCE_LABELS[$key] ?? $key) . ' tercihin',
                    'label'   => self::PREFERENCE_LABELS[$key] ?? $key,
                    'value'   => $value,
                ];
            }
        }

        usort($matches, static fn($a, $b) => $b['weight'] <=> $a['weight']);
        $top = array_slice($matches, 0, 3);

        $name = $career['name'];

        if (!$top) {
            return sprintf(
                '%s mesleği, genel yetenek profilin ile uyumlu görünüyor. Bu alanda kendini deneyerek daha net bir tablo oluşturabilirsin.',
                $name
            );
        }

        $phrases = array_column($top, 'phrase');

        if (count($phrases) === 1) {
            $list = $phrases[0];
        } elseif (count($phrases) === 2) {
            $list = $phrases[0] . ' ve ' . $phrases[1];
        } else {
            $list = $phrases[0] . ', ' . $phrases[1] . ' ve ' . $phrases[2];
        }

        // Yalnizca ilk harf buyutulur
        $list = mb_strtoupper(mb_substr($list, 0, 1), 'UTF-8') . mb_substr($list, 1);

        return sprintf(
            '%s nedeniyle %s senin için araştırmaya değer alanlardan biri olarak öne çıkıyor. Bu alandaki güçlü yanlarını tanıman, kariyer planını netleştirmene yardımcı olabilir.',
            $list,
            $name
        );
    }

    /* ------------------------------------------------------------------ */
    /* Kisisel degerlendirme                                                 */
    /* ------------------------------------------------------------------ */

    /**
     * Kullanici icin kisisel degerlendirme metni uretir.
     */
    public function buildAssessment(array $scores, array $recommendations): array
    {
        $topInterests = $this->topKeys($scores['interest'] ?? [], self::INTEREST_LABELS, 3);
        $topAbilities = $this->topKeys($scores['ability'] ?? [], self::ABILITY_LABELS, 3);
        $lowAbilities = $this->lowKeys($scores['ability'] ?? [], self::ABILITY_LABELS, 3);
        $topPrefs     = $this->topKeys($scores['preference'] ?? [], self::PREFERENCE_LABELS, 3);
        $topEnvironments = $this->topKeys($scores['environment'] ?? [], self::ENVIRONMENT_LABELS, 2);

        // Dominant tip: en yuksek ortalamali boyut grubu
        $averages = [];
        foreach (['interest', 'ability', 'preference', 'environment'] as $type) {
            $values = array_values($scores[$type] ?? []);
            $averages[$type] = $values ? array_sum($values) / count($values) : 0;
        }
        arsort($averages);
        $dominant = array_key_first($averages);

        $dominantLabels = [
            'interest'    => 'İlgi Odaklı',
            'ability'     => 'Yetenek Odaklı',
            'preference'  => 'Çalışma Tarzı Odaklı',
            'environment' => 'Ortam Odaklı',
        ];

        // Önerilen egitim alanlari (en yuksek puanli mesleklerin bolumlerinden)
        $departments = [];
        foreach (array_slice($recommendations, 0, 3) as $rec) {
            $rows = \App\Core\Database::fetchAll(
                "SELECT `content` FROM `career_requirements` WHERE `career_id` = ? AND `requirement_type` = 'department'",
                [$rec['career']['id']]
            );
            foreach ($rows as $row) {
                if (!in_array($row['content'], $departments, true)) {
                    $departments[] = $row['content'];
                }
            }
        }

        // Gelistirilebilecek beceriler icin oneriler
        $improvements = [];
        foreach ($lowAbilities as $key) {
            $improvements[] = [
                'skill'    => self::ABILITY_LABELS[$key] ?? $key,
                'score'    => (int) round(($scores['ability'][$key] ?? 0) * 100),
                'suggestion' => $this->improvementSuggestion($key),
            ];
        }

        return [
            'dominant_type'    => $dominantLabels[$dominant] ?? 'Genel Profil',
            'dominant_key'     => $dominant,
            'top_interests'    => $topInterests,
            'top_abilities'    => $topAbilities,
            'low_abilities'    => $lowAbilities,
            'top_preferences'  => $topPrefs,
            'top_environments' => $topEnvironments,
            'departments'      => array_slice($departments, 0, 5),
            'improvements'     => $improvements,
            'averages'         => $averages,
        ];
    }

    private function improvementSuggestion(string $key): string
    {
        $map = [
            'analitik'           => 'Veri okuma, senaryo analizi ve karar verme egzersizleriyle guclendirilebilir.',
            'matematik'          => 'Temel matematik calismalari ve sayisal oyunler ile artirilabilir.',
            'problem_cozme'      => 'Farkli alanlardan problem cozme vakalari incelenerek gelistirilebilir.',
            'iletisim_becerisi'  => 'Grup calismalari, sunumlar ve topluluk projeleriyle pratiklenebilir.',
            'yaraticilik'        => 'Yaratici yazma, tasarim atölyeleri ve fikir gelistirme egzersizleri onerilir.',
            'liderlik'           => 'Proje yonetimi, ekip leadershipu ve organizasyon deneyimi onerilir.',
            'organizasyon'       => 'Planlama araclari ve zaman yonetimi teknikleriyle gelistirilebilir.',
            'el_becerisi'        => 'Atölye kurslari ve teknik uygulama ile artirilabilir.',
            'arastirma_yetenegi' => 'Kaynak okuma aliskanligi ve kucuk arastirma projeleri onerilir.',
            'teknoloji_becerisi' => 'Yeni yazilim araclarini deneme ve kucuk projelerle pratiklenebilir.',
            'gorsel_dusunme'     => 'Tasarim calismalari ve gozle gorerek calisma onerilir.',
            'sozsel_ifade'       => 'Yazili icerik uretme ve topluluk onunde konusma pratiği onerilir.',
        ];
        return $map[$key] ?? 'Bu alanda kisisel gelisim planinizi gozden gecirmen faydali olabilir.';
    }

    /**
     * En yuksek puanli boyut anahtarlari.
     */
    public function topKeys(array $scores, array $labels, int $limit = 3): array
    {
        arsort($scores);
        $keys = array_slice(array_keys($scores), 0, $limit);
        return array_values(array_filter($keys, static fn($k) => isset($labels[$k])));
    }

    /**
     * En dusuk puanli boyut anahtarlari.
     */
    public function lowKeys(array $scores, array $labels, int $limit = 3): array
    {
        asort($scores);
        $keys = array_slice(array_keys($scores), 0, $limit);
        return array_values(array_filter($keys, static fn($k) => isset($labels[$k])));
    }

    /**
     * Bir boyut tipinin etiketlerini dondurur.
     */
    public static function labelsFor(string $type): array
    {
        return match ($type) {
            'interest'    => self::INTEREST_LABELS,
            'ability'     => self::ABILITY_LABELS,
            'preference'  => self::PREFERENCE_LABELS,
            'environment' => self::ENVIRONMENT_LABELS,
            default       => [],
        };
    }
}
