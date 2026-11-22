<?php
/**
 * Akilli Kariyer - Kariyer raporu PDF uretici
 *
 * Test sonuclarini, gomulu Turkce yazi tipi ile PDF olarak olusturur.
 */

namespace App\Services;

use App\Core\PdfBuilder;
use App\Models\Result;
use App\Services\CareerMatchingService;

class ReportService
{
    private const COLORS = [
        'primary'   => [0x14, 0x2B, 0x4A],
        'secondary' => [0x1C, 0x46, 0x6D],
        'accent'    => [0x16, 0xB8, 0xB0],
        'light'     => [0xEA, 0xF4, 0xFA],
        'bg'        => [0xF6, 0xF8, 0xFC],
        'text'      => [0x17, 0x2B, 0x42],
        'muted'     => [0x74, 0x83, 0x99],
        'success'   => [0x20, 0xA7, 0x79],
        'warning'   => [0xF0, 0xA4, 0x3A],
        'white'     => [0xFF, 0xFF, 0xFF],
        'border'    => [0xE6, 0xEC, 0xF5],
    ];

    private PdfBuilder $pdf;
    private float $y = 0;
    private float $margin = 48;
    private float $contentWidth;
    private int $pageNumber = 0;

    private array $result;
    private array $recommendations;
    private array $assessment;
    private array $user;

    public function __construct(array $result, array $recommendations, array $assessment, array $user)
    {
        $this->result         = $result;
        $this->recommendations = $recommendations;
        $this->assessment     = $assessment;
        $this->user           = $user;

        $this->pdf = new PdfBuilder([
            'F1' => APP_ROOT . '/assets/fonts/Roboto-Regular.ttf',
            'F2' => APP_ROOT . '/assets/fonts/Roboto-Medium.ttf',
        ]);
        $this->pdf->setMargin($this->margin);
        $this->contentWidth = $this->pdf->width() - ($this->margin * 2);
    }

    /**
     * PDF'i olusturur.
     */
    public function build(string $filename, bool $download = true): string
    {
        $this->pdf->addPage();
        $this->pageNumber = 1;
        $this->y = $this->margin;

        $this->drawHeaderBand();
        $this->drawSummary();
        $this->drawTopMatches();
        $this->drawDimensionCharts();
        $this->drawPersonalAssessment();
        $this->drawAlternatives();
        $this->drawDisclaimer();

        return $this->pdf->output($filename, $download);
    }

    /* ------------------------------------------------------------------ */
    /* Bölüm çizimleri                                                       */
    /* ------------------------------------------------------------------ */

    private function drawHeaderBand(): void
    {
        $w = $this->pdf->width();
        $bandHeight = 168;

        $this->pdf->setFillColor(...self::COLORS['primary']);
        $this->pdf->rect(0, 0, $w, $bandHeight, 'F');

        // Aksan çubuğu
        $this->pdf->setFillColor(...self::COLORS['accent']);
        $this->pdf->rect(0, $bandHeight - 5, $w, 5, 'F');

        // Dekoratif daireler
        $this->pdf->setFillColor(0x1E, 0x55, 0x7A);
        $this->pdf->circle($w - 70, 34, 52, 'F');
        $this->pdf->setFillColor(0x1C, 0x4F, 0x66);
        $this->pdf->circle($w - 118, 140, 30, 'F');

        $this->pdf->setFont('F2', 10);
        $this->pdf->setFillColor(...self::COLORS['accent']);
        $this->pdf->text($this->margin, $this->margin + 8, 'AKILLI KARİYER — KARİYER PROFİLİ RAPORU');

        $this->pdf->setFont('F2', 25);
        $this->pdf->setFillColor(...self::COLORS['white']);
        $this->pdf->text($this->margin, $this->margin + 46, $this->user['full_name'] . ' — Kariyer Profilin Hazır');

        $this->pdf->setFont('F1', 10.5);
        $this->pdf->setFillColor(0xC9, 0xD8, 0xE6);
        $this->pdf->text(
            $this->margin,
            $this->margin + 70,
            'Test tarihi: ' . format_date($this->result['completed_at'], 'd F Y') . '   •   Profil tipi: ' . $this->result['dominant_type']
        );

        // Alt şeride özet kutucukları
        $boxY = $this->margin + 88;
        $boxH = 54;
        $gap = 10;
        $topScore = (float) ($this->recommendations[0]['match_score'] ?? 0);

        $boxes = [
            ['Önerilen Meslek Sayısı', (string) count($this->recommendations)],
            ['En Yüksek Uyum', '%' . number_format_tr($topScore)],
            ['Değerlendirilen Soru', (string) ($this->result['total_questions'] ?? 0)],
        ];

        $boxW = ($this->contentWidth - ($gap * (count($boxes) - 1))) / count($boxes);

        foreach ($boxes as $i => $box) {
            $bx = $this->margin + ($i * ($boxW + $gap));
            $this->pdf->setFillColor(0x1E, 0x3B, 0x5E);
            $this->pdf->rect($bx, $boxY, $boxW, $boxH, 'F');

            $this->pdf->setFont('F1', 8);
            $this->pdf->setFillColor(0x9F, 0xB6, 0xCC);
            $this->pdf->text($bx + 12, $boxY + 17, mb_strtoupper($box[0], 'UTF-8'));

            $this->pdf->setFont('F2', 18);
            $this->pdf->setFillColor(...self::COLORS['white']);
            $this->pdf->text($bx + 12, $boxY + 41, $box[1]);
        }

        $this->y = $bandHeight + 30;
    }

    private function drawSummary(): void
    {
        $this->sectionTitle('Profil Özeti', 'Kariyer testinizden üretilen genel değerlendirme');

        $this->pdf->setFillColor(...self::COLORS['bg']);
        $this->pdf->rect($this->margin, $this->y, $this->contentWidth, 76, 'F');
        $this->pdf->setFillColor(...self::COLORS['accent']);
        $this->pdf->rect($this->margin, $this->y, 4, 76, 'F');

        $text = (string) ($this->result['summary'] ?? '');
        $this->pdf->setFont('F1', 10.5);
        $this->pdf->setFillColor(...self::COLORS['text']);
        $this->pdf->paragraph($this->margin + 18, $this->y + 22, $text, $this->contentWidth - 36, 15);

        $this->y += 76 + 26;
    }

    private function drawTopMatches(): void
    {
        $this->ensureSpace(150);
        $this->sectionTitle('Size En Uygun Meslekler', 'İlgi alanı, yetenek, çalışma tercihi ve beceri bileşenlerinin ağırlıklı ortalamasıyla sıralanmıştır');

        $top = array_slice($this->recommendations, 0, 5);

        foreach ($top as $i => $rec) {
            $this->ensureSpace(118);

            $rowH = 106;
            $this->pdf->setFillColor(...self::COLORS['white']);
            $this->pdf->rect($this->margin, $this->y, $this->contentWidth, $rowH, 'F');
            $this->pdf->setFillColor(...self::COLORS['border']);
            $this->pdf->rect($this->margin, $this->y, $this->contentWidth, 0.8, 'F');

            // Sıra rozeti
            $this->pdf->setFillColor(...self::COLORS['primary']);
            $this->pdf->rect($this->margin, $this->y, 42, $rowH, 'F');
            $this->pdf->setFont('F2', 18);
            $this->pdf->setFillColor(...self::COLORS['white']);
            $this->pdf->textCenter($this->margin + 21, $this->y + 40, '#' . ($i + 1));
            $this->pdf->setFont('F1', 7);
            $this->pdf->setFillColor(0x8F, 0xA6, 0xBE);
            $this->pdf->textCenter($this->margin + 21, $this->y + 56, 'SIRA');

            // İçerik
            $tx = $this->margin + 58;
            $tw = $this->contentWidth - 58 - 74;

            $this->pdf->setFont('F2', 12.5);
            $this->pdf->setFillColor(...self::COLORS['primary']);
            $this->pdf->text($tx, $this->y + 24, $rec['name']);

            $this->pdf->setFont('F1', 8.5);
            $this->pdf->setFillColor(...self::COLORS['muted']);
            $this->pdf->text($tx, $this->y + 40, $rec['category_name'] . '   •   ' . education_label($rec['education_level'])
                . (!empty($rec['salary_range']) ? '   •   ' . $rec['salary_range'] : ''));

            // Uyum çubuğu
            $score = (float) $rec['match_score'];
            $barY = $this->y + 52;
            $barW = $tw;
            $this->pdf->setFillColor(...self::COLORS['border']);
            $this->pdf->rect($tx, $barY, $barW, 6, 'F');
            $this->pdf->setFillColor(...self::COLORS['accent']);
            $this->pdf->rect($tx, $barY, $barW * min(1, $score / 100), 6, 'F');

            // Bileşen kırılımları
            $comp = $rec['components'] ?? [];
            $labels = [
                'interest'    => 'İlgi',
                'ability'     => 'Yetenek',
                'preference'  => 'Tercih',
                'environment' => 'Ortam',
                'skill'       => 'Beceri',
            ];
            $cx = $tx;
            $this->pdf->setFont('F1', 7.5);
            foreach ($labels as $key => $label) {
                $val = (float) ($comp[$key . '_score'] ?? 0);
                $text = $label . ' %' . number_format_tr($val);
                $tw2 = $this->pdf->textWidth($text, 7.5);
                $this->pdf->setFillColor(...self::COLORS['light']);
                $this->pdf->rect($cx, $this->y + 64, $tw2 + 12, 14, 'F');
                $this->pdf->setFillColor(...self::COLORS['secondary']);
                $this->pdf->text($cx + 6, $this->y + 74, $text);
                $cx += $tw2 + 18;
            }

            // Gerekçe
            $this->pdf->setFont('F1', 8.8);
            $this->pdf->setFillColor(...self::COLORS['text']);
            $this->pdf->paragraph($tx, $this->y + 92, (string) $rec['reason'], $tw, 11);

            // Skor
            $this->pdf->setFont('F2', 19);
            $this->pdf->setFillColor(...self::COLORS['accent']);
            $this->pdf->textRight($this->margin + $this->contentWidth - 16, $this->y + 32, '%' . number_format_tr($score));
            $this->pdf->setFont('F1', 7.5);
            $this->pdf->setFillColor(...self::COLORS['muted']);
            $this->pdf->textRight($this->margin + $this->contentWidth - 16, $this->y + 46, 'UYUM PUANI');

            $this->y += $rowH + 10;
        }

        $this->y += 14;
    }

    private function drawDimensionCharts(): void
    {
        $this->ensureSpace(210);
        $this->sectionTitle('Profil Boyutları', 'Dört değerlendirme alanındaki puan dağılımınız');

        $groups = [
            ['İlgi Alanı Dağılımı', $this->decodeScores($this->result['interest_scores'] ?? []), CareerMatchingService::INTEREST_LABELS, self::COLORS['accent']],
            ['Yetenek Puanları', $this->decodeScores($this->result['ability_scores'] ?? []), CareerMatchingService::ABILITY_LABELS, self::COLORS['secondary']],
            ['Çalışma Tercihi Analizi', $this->decodeScores($this->result['preference_scores'] ?? []), CareerMatchingService::PREFERENCE_LABELS, self::COLORS['success']],
            ['Çalışma Ortamı Tercihi', $this->decodeScores($this->result['environment_scores'] ?? []), CareerMatchingService::ENVIRONMENT_LABELS, self::COLORS['warning']],
        ];

        foreach ($groups as [$title, $scores, $labels, $color]) {
            $this->ensureSpace(96);
            $this->y += 8;

            $this->pdf->setFont('F2', 10);
            $this->pdf->setFillColor(...self::COLORS['primary']);
            $this->pdf->text($this->margin, $this->y, $title);
            $this->y += 10;

            $sorted = $scores;
            arsort($sorted);
            $sorted = array_slice($sorted, 0, 6, true);

            foreach ($sorted as $key => $value) {
                $label = $labels[$key] ?? $key;
                $pct = (float) $value * 100;

                $this->pdf->setFont('F1', 8.2);
                $this->pdf->setFillColor(...self::COLORS['text']);
                $this->pdf->text($this->margin, $this->y + 8, $label);

                $barX = $this->margin + 132;
                $barW = $this->contentWidth - 132 - 44;
                $this->pdf->setFillColor(...self::COLORS['border']);
                $this->pdf->rect($barX, $this->y + 2, $barW, 8, 'F');
                $this->pdf->setFillColor(...$color);
                $this->pdf->rect($barX, $this->y + 2, $barW * min(1, $pct / 100), 8, 'F');

                $this->pdf->setFont('F2', 8.2);
                $this->pdf->setFillColor(...self::COLORS['muted']);
                $this->pdf->textRight($this->margin + $this->contentWidth, $this->y + 9, '%' . number_format_tr($pct));

                $this->y += 15;
            }
        }

        $this->y += 16;
    }

    private function drawPersonalAssessment(): void
    {
        $this->ensureSpace(170);
        $this->sectionTitle('Kişisel Değerlendirme', 'Güçlü yönleriniz ve gelişim alanlarınız');

        // Güçlü yönler
        $this->pdf->setFont('F2', 10);
        $this->pdf->setFillColor(...self::COLORS['primary']);
        $this->pdf->text($this->margin, $this->y, 'Güçlü Yönlerin');
        $this->y += 14;

        $chipY = $this->y;
        $chipX = $this->margin;
        foreach ($this->assessment['top_interests'] as $key) {
            $chipX = $this->drawChip($chipX, $chipY, CareerMatchingService::INTEREST_LABELS[$key] ?? $key, self::COLORS['accent']);
        }
        foreach ($this->assessment['top_abilities'] as $key) {
            $chipX = $this->drawChip($chipX, $chipY, CareerMatchingService::ABILITY_LABELS[$key] ?? $key, self::COLORS['success']);
        }
        foreach ($this->assessment['top_preferences'] as $key) {
            $chipX = $this->drawChip($chipX, $chipY, CareerMatchingService::PREFERENCE_LABELS[$key] ?? $key, self::COLORS['secondary']);
        }
        $this->y += 28;

        // Gelişim alanları
        $this->pdf->setFont('F2', 10);
        $this->pdf->setFillColor(...self::COLORS['primary']);
        $this->pdf->text($this->margin, $this->y, 'Geliştirilebilecek Beceriler');
        $this->y += 14;

        foreach ($this->assessment['improvements'] as $imp) {
            $this->ensureSpace(34);
            $this->pdf->setFont('F2', 9);
            $this->pdf->setFillColor(...self::COLORS['text']);
            $this->pdf->text($this->margin, $this->y, $imp['skill']);

            $this->pdf->setFont('F1', 8.6);
            $this->pdf->setFillColor(...self::COLORS['muted']);
            $this->pdf->paragraph($this->margin, $this->y + 13, $imp['suggestion'], $this->contentWidth, 11);
            $this->y += 30;
        }

        // Önerilen eğitim alanları
        $this->ensureSpace(80);
        $this->y += 6;
        $this->pdf->setFont('F2', 10);
        $this->pdf->setFillColor(...self::COLORS['primary']);
        $this->pdf->text($this->margin, $this->y, 'Önerilen Eğitim Alanları');
        $this->y += 15;

        $this->pdf->setFont('F1', 9.2);
        $this->pdf->setFillColor(...self::COLORS['text']);
        $this->pdf->paragraph(
            $this->margin,
            $this->y,
            $this->assessment['departments'] ? implode('  •  ', $this->assessment['departments']) : 'Belirlenemedi.',
            $this->contentWidth,
            13
        );
        $this->y += 30;
    }

    private function drawAlternatives(): void
    {
        $alternatives = array_slice($this->recommendations, 5, 5);
        if (!$alternatives) {
            return;
        }

        $this->ensureSpace(120);
        $this->sectionTitle('Keşfedebileceğin Alternatifler', 'Sıralamadaki diğer uyumlu meslekler');

        foreach ($alternatives as $rec) {
            $this->ensureSpace(26);
            $this->pdf->setFont('F2', 9.4);
            $this->pdf->setFillColor(...self::COLORS['text']);
            $this->pdf->text($this->margin, $this->y, $rec['name']);

            $this->pdf->setFont('F1', 8.4);
            $this->pdf->setFillColor(...self::COLORS['muted']);
            $this->pdf->text($this->margin + 180, $this->y, $rec['category_name']);

            $this->pdf->setFont('F2', 9.4);
            $this->pdf->setFillColor(...self::COLORS['accent']);
            $this->pdf->textRight($this->margin + $this->contentWidth, $this->y, '%' . number_format_tr((float) $rec['match_score']));

            $this->pdf->setFillColor(...self::COLORS['border']);
            $this->pdf->rect($this->margin, $this->y + 5, $this->contentWidth, 0.6, 'F');
            $this->y += 17;
        }

        $this->y += 16;
    }

    private function drawDisclaimer(): void
    {
        $this->ensureSpace(90);
        $text = 'Bu rapor, kariyer testinizde verdiğiniz yanıtlara dayanan bir keşif rehberidir. '
              . 'Kesin bir meslek belirleme veya bilimsel kişilik tanısı niteliği taşımaz. '
              . 'Mesleklerin detay sayfalarını, eğitim seçeneklerini ve çalışma koşullarını '
              . 'incelemeden karar vermeyiniz. Önerilerinizi meslek detaylarında inceleyerek '
              . 'kendi hedefinize göre değerlendiriniz.';

        $this->pdf->setFillColor(...self::COLORS['light']);
        $this->pdf->rect($this->margin, $this->y, $this->contentWidth, 68, 'F');
        $this->pdf->setFillColor(...self::COLORS['secondary']);
        $this->pdf->rect($this->margin, $this->y, 4, 68, 'F');

        $this->pdf->setFont('F2', 8.6);
        $this->pdf->text($this->margin + 16, $this->y + 16, 'ÖNEMLİ NOT');

        $this->pdf->setFont('F1', 8.4);
        $this->pdf->setFillColor(...self::COLORS['text']);
        $this->pdf->paragraph($this->margin + 16, $this->y + 30, $text, $this->contentWidth - 32, 11);

        // Alt bilgi
        $footerY = $this->pdf->height() - 34;
        $this->pdf->setFillColor(...self::COLORS['border']);
        $this->pdf->rect($this->margin, $footerY - 10, $this->contentWidth, 0.6, 'F');
        $this->pdf->setFont('F1', 7.5);
        $this->pdf->setFillColor(...self::COLORS['muted']);
        $this->pdf->text($this->margin, $footerY, 'Akıllı Kariyer — ' . setting('site_slogan', 'Geleceğini Keşfet'));
        $this->pdf->textRight($this->margin + $this->contentWidth, $footerY, format_date(date('Y-m-d H:i:s'), 'd F Y H:i'));
    }

    /* ------------------------------------------------------------------ */
    /* Yardimcilar                                                           */
    /* ------------------------------------------------------------------ */

    /**
     * JSON olarak saklanan puan dizilerini cozumler.
     */
    private function decodeScores(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($value) ? $value : [];
    }

    private function drawChip(float $x, float $y, string $text, array $color): float
    {
        $this->pdf->setFont('F1', 8.2);
        $w = $this->pdf->textWidth($text, 8.2) + 16;

        if ($x + $w > $this->margin + $this->contentWidth) {
            $x = $this->margin;
            $y += 22;
        }

        $this->pdf->setFillColor(...$color);
        $this->pdf->rect($x, $y, $w, 17, 'F');
        $this->pdf->setFillColor(...self::COLORS['white']);
        $this->pdf->text($x + 8, $y + 12, $text);

        return $x + $w + 6;
    }

    private function sectionTitle(string $title, string $subtitle = ''): void
    {
        $this->ensureSpace(64);
        $this->y += 10;

        $this->pdf->setFillColor(...self::COLORS['accent']);
        $this->pdf->rect($this->margin, $this->y + 2, 3, 15, 'F');

        $this->pdf->setFont('F2', 14);
        $this->pdf->setFillColor(...self::COLORS['primary']);
        $this->pdf->text($this->margin + 11, $this->y + 14, $title);

        if ($subtitle !== '') {
            $this->pdf->setFont('F1', 8.6);
            $this->pdf->setFillColor(...self::COLORS['muted']);
            $this->pdf->text($this->margin + 11, $this->y + 28, $subtitle);
            $this->y += 32;
        } else {
            $this->y += 24;
        }
        $this->y += 8;
    }

    /**
     * Yeni sayfa gerekliyse sayfa ekler.
     */
    private function ensureSpace(float $needed): void
    {
        $limit = $this->pdf->height() - 56;
        if ($this->y + $needed > $limit) {
            $this->pdf->addPage();
            $this->pageNumber++;
            $this->y = $this->margin;
        }
    }
}
