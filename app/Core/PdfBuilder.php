<?php
/**
 * Akilli Kariyer - Minimal PDF yazici
 *
 * TrueType yazi tiplerini gomerek (CIDFontType2 / Identity-H) Turkce karakter
 * destekli PDF uretir. Bagimlilik gerektirmez.
 */

namespace App\Core;

class PdfBuilder
{
    public const A4_WIDTH  = 595.28;
    public const A4_HEIGHT = 841.89;

    private string $content = '';
    private int $pageCount = 0;
    private array $pageObjects = [];

    private array $fonts = [];        // alias => ['font'=>TrueTypeFont,'subtype'=>..]
    private string $currentFont = 'F1';
    private float $currentSize = 11;
    private array $fillColor = [0, 0, 0];
    private array $strokeColor = [0, 0, 0];
    private float $lineWidth = 1;

    /** @var array<int,int> unicode => gid */
    private array $usedChars = [];

    private float $pageWidth = self::A4_WIDTH;
    private float $pageHeight = self::A4_HEIGHT;
    private float $margin = 48;

    public function __construct(array $fontFiles = [])
    {
        foreach ($fontFiles as $alias => $path) {
            $this->registerFont($alias, $path);
        }
    }

    public function registerFont(string $alias, string $path, string $postScriptName = 'Roboto'): void
    {
        $this->fonts[$alias] = new TrueTypeFont($path, $postScriptName);
    }

    public function font(string $alias): TrueTypeFont
    {
        return $this->fonts[$alias];
    }

    public function setPageSize(float $width, float $height): void
    {
        $this->pageWidth = $width;
        $this->pageHeight = $height;
    }

    public function setMargin(float $margin): void
    {
        $this->margin = $margin;
    }

    public function width(): float
    {
        return $this->pageWidth;
    }

    public function height(): float
    {
        return $this->pageHeight;
    }

    public function margin(): float
    {
        return $this->margin;
    }

    /* ------------------------------------------------------------------ */
    /* Sayfa yonetimi                                                       */
    /* ------------------------------------------------------------------ */

    public function addPage(): void
    {
        $this->pageCount++;
        $this->pageObjects[] = strlen($this->content);
        $this->content .= "\n%%PAGE_BREAK%%\n";
    }

    public function pageCount(): int
    {
        return $this->pageCount;
    }

    /* ------------------------------------------------------------------ */
    /* Bicimlendirme                                                         */
    /* ------------------------------------------------------------------ */

    public function setFont(string $alias, float $size): void
    {
        $this->currentFont = $alias;
        $this->currentSize = $size;
    }

    public function setFillColor(int $r, int $g, int $b): void
    {
        $this->fillColor = [$r, $g, $b];
    }

    public function setFillHex(string $hex): void
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $this->setFillColor($r, $g, $b);
    }

    public function setStrokeColor(int $r, int $g, int $b): void
    {
        $this->strokeColor = [$r, $g, $b];
    }

    public function setStrokeHex(string $hex): void
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $this->setStrokeColor($r, $g, $b);
    }

    public function setLineWidth(float $width): void
    {
        $this->lineWidth = $width;
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    private function colorOp(array $rgb): string
    {
        return sprintf('%.3F %.3F %.3F', $rgb[0] / 255, $rgb[1] / 255, $rgb[2] / 255);
    }

    /* ------------------------------------------------------------------ */
    /* Cizim islemleri                                                        */
    /* ------------------------------------------------------------------ */

    /**
     * Doldurulabilir dikdortgen (ust-sol koordinat sistemi).
     */
    public function rect(float $x, float $y, float $w, float $h, string $mode = 'F'): void
    {
        $pdfY = $this->pageHeight - $y - $h;
        $this->content .= sprintf(
            "q %s %s rg %s %s %s %s re %s Q\n",
            $this->colorOp($this->fillColor),
            $this->colorOp($this->strokeColor),
            $this->num($x), $this->num($pdfY), $this->num($w), $this->num($h),
            $mode
        );
    }

    /**
     * Yalnizca cizgi (stroke) dikdortgen.
     */
    public function strokeRect(float $x, float $y, float $w, float $h): void
    {
        $pdfY = $this->pageHeight - $y - $h;
        $this->content .= sprintf(
            "q %s RG %s w %s %s %s %s re S Q\n",
            $this->colorOp($this->strokeColor),
            $this->num($this->lineWidth),
            $this->num($x), $this->num($pdfY), $this->num($w), $this->num($h)
        );
    }

    public function line(float $x1, float $y1, float $x2, float $y2): void
    {
        $this->content .= sprintf(
            "q %s RG %s w %s %s m %s %s l S Q\n",
            $this->colorOp($this->strokeColor),
            $this->num($this->lineWidth),
            $this->num($x1), $this->num($this->pageHeight - $y1),
            $this->num($x2), $this->num($this->pageHeight - $y2)
        );
    }

    public function circle(float $cx, float $cy, float $r, string $mode = 'F'): void
    {
        $k = 0.5523 * $r;
        $pdfCy = $this->pageHeight - $cy;
        $n = fn(float $v): string => $this->num($v);

        $ops = 'q ' . $this->colorOp($this->fillColor) . ' rg '
             . $n($cx + $r) . ' ' . $n($pdfCy) . ' m '
             . $n($cx + $r) . ' ' . $n($pdfCy + $k) . ' ' . $n($cx + $k) . ' ' . $n($pdfCy + $r) . ' ' . $n($cx) . ' ' . $n($pdfCy + $r) . ' c '
             . $n($cx - $k) . ' ' . $n($pdfCy + $r) . ' ' . $n($cx - $r) . ' ' . $n($pdfCy + $k) . ' ' . $n($cx - $r) . ' ' . $n($pdfCy) . ' c '
             . $n($cx - $r) . ' ' . $n($pdfCy - $k) . ' ' . $n($cx - $k) . ' ' . $n($pdfCy - $r) . ' ' . $n($cx) . ' ' . $n($pdfCy - $r) . ' c '
             . $n($cx + $k) . ' ' . $n($pdfCy - $r) . ' ' . $n($cx + $r) . ' ' . $n($pdfCy - $k) . ' ' . $n($cx + $r) . ' ' . $n($pdfCy) . ' c';

        $this->content .= $ops . ' ' . $this->modeOp($mode) . " Q\n";
    }

    private function modeOp(string $mode): string
    {
        return $mode === 'S' ? 'S' : 'f';
    }

    /* ------------------------------------------------------------------ */
    /* Metin                                                                */
    /* ------------------------------------------------------------------ */

    /**
     * Metin yazar (ust-sol koordinat sistemi).
     */
    public function text(float $x, float $y, string $text, ?float $size = null, ?array $rgb = null): void
    {
        if ($text === '') {
            return;
        }
        $size = $size ?? $this->currentSize;
        $font = $this->fonts[$this->currentFont] ?? reset($this->fonts);

        $encoded = $font->encode($text);
        foreach (mb_str_split($text, 1, 'UTF-8') as $i => $char) {
            $cp = mb_ord($char, 'UTF-8');
            if ($font->hasGlyph($cp)) {
                $this->usedChars[$cp] = $font->glyphId($cp);
            }
        }

        $color = $rgb ?? $this->fillColor;
        $this->content .= sprintf(
            "BT %s %s /%s %s Tf %s %s Td <%s> Tj ET\n",
            $this->colorOp($color),
            '',
            $this->currentFont,
            $this->num($size),
            $this->num($x),
            $this->num($this->pageHeight - $y),
            $encoded['hex']
        );
    }

    /**
     * Ortalanmis metin.
     */
    public function textCenter(float $centerX, float $y, string $text, ?float $size = null, ?array $rgb = null): void
    {
        $size = $size ?? $this->currentSize;
        $font = $this->fonts[$this->currentFont] ?? reset($this->fonts);
        $w = $font->textWidth($text, $size);
        $this->text($centerX - ($w / 2), $y, $text, $size, $rgb);
    }

    /**
     * Saga hizali metin.
     */
    public function textRight(float $rightX, float $y, string $text, ?float $size = null, ?array $rgb = null): void
    {
        $size = $size ?? $this->currentSize;
        $font = $this->fonts[$this->currentFont] ?? reset($this->fonts);
        $w = $font->textWidth($text, $size);
        $this->text($rightX - $w, $y, $text, $size, $rgb);
    }

    public function textWidth(string $text, ?float $size = null): float
    {
        $size = $size ?? $this->currentSize;
        $font = $this->fonts[$this->currentFont] ?? reset($this->fonts);
        return $font->textWidth($text, $size);
    }

    /**
     * Metni verilen genislikte sarar, satirlari dizi olarak dondurur.
     */
    public function wrapText(string $text, float $maxWidth, ?float $size = null): array
    {
        $size = $size ?? $this->currentSize;
        $font = $this->fonts[$this->currentFont] ?? reset($this->fonts);

        $lines = [];
        foreach (explode("\n", $text) as $paragraph) {
            $words = preg_split('/\s+/u', trim($paragraph)) ?: [];
            $line = '';

            foreach ($words as $word) {
                $test = $line === '' ? $word : $line . ' ' . $word;
                if ($font->textWidth($test, $size) <= $maxWidth) {
                    $line = $test;
                    continue;
                }
                if ($line !== '') {
                    $lines[] = $line;
                }
                // Cok uzun kelimeleri kes
                if ($font->textWidth($word, $size) > $maxWidth) {
                    $chunk = '';
                    foreach (mb_str_split($word, 1, 'UTF-8') as $ch) {
                        if ($font->textWidth($chunk . $ch, $size) > $maxWidth) {
                            $lines[] = $chunk;
                            $chunk = $ch;
                        } else {
                            $chunk .= $ch;
                        }
                    }
                    $line = $chunk;
                } else {
                    $line = $word;
                }
            }
            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Sarilmis metni yazar; kullanilan yuksekligi dondurur.
     */
    public function paragraph(float $x, float $y, string $text, float $maxWidth, float $lineHeight, ?float $size = null, ?array $rgb = null): float
    {
        $lines = $this->wrapText($text, $maxWidth, $size);
        $cursor = $y;
        foreach ($lines as $line) {
            $this->text($x, $cursor, $line, $size, $rgb);
            $cursor += $lineHeight;
        }
        return $cursor - $y;
    }

    /* ------------------------------------------------------------------ */
    /* Cikti                                                                */
    /* ------------------------------------------------------------------ */

    /**
     * PDF dosyasini uretir ve indirilir.
     */
    public function output(string $filename = 'rapor.pdf', bool $download = true): string
    {
        $pdf = $this->build();
        $path = APP_ROOT . '/storage/cache/' . $filename;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }
        file_put_contents($path, $pdf);

        if ($download) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Content-Length: ' . strlen($pdf));
            header('Cache-Control: private, max-age=0, must-revalidate');
            echo $pdf;
            exit;
        }

        return $path;
    }

    public function build(): string
    {
        // Sayfa iceriklerini ayir
        $chunks = explode("\n%%PAGE_BREAK%%\n", $this->content);
        array_shift($chunks); // ilk bos parca

        $objects = [];

        // 1: Catalog, 2: Pages, 3..: Font objeleri, sonra icerik ve sayfa objeleri
        $fontObjNums = [];
        $nextObj = 3;
        foreach (array_keys($this->fonts) as $alias) {
            $fontObjNums[$alias] = $nextObj;
            $nextObj += 6; // Type0 + CIDFont + FontDescriptor + FontFile2 + ToUnicode (+ reserved)
        }

        $pagesObjNum = 2;
        $catalogObjNum = 1;

        $contentObjNums = [];
        $pageObjNums = [];
        foreach ($chunks as $i => $chunk) {
            $contentObjNums[] = $nextObj++;
            $pageObjNums[] = $nextObj++;
        }

        // Font nesneleri
        $fontObjects = [];
        foreach ($this->fonts as $alias => $font) {
            $type0Num      = $fontObjNums[$alias];
            $cidFontNum    = $type0Num + 1;
            $descriptorNum = $type0Num + 2;
            $fileNum       = $type0Num + 3;
            $toUnicodeNum  = $type0Num + 4;

            $subsetTag = strtoupper(substr(md5($alias . $font->postScriptName()), 0, 6));
            $baseName  = $subsetTag . '+' . $font->postScriptName();

            $ttf = $font->rawData();
            $compressed = gzcompress($ttf, 9);

            $fontObjects[] = [
                'num' => $type0Num,
                'data' => "<< /Type /Font /Subtype /Type0 /BaseFont /{$baseName} /Encoding /Identity-H "
                        . "/DescendantFonts [{$cidFontNum} 0 R] /ToUnicode {$toUnicodeNum} 0 R >>",
            ];

            $fontObjects[] = [
                'num' => $cidFontNum,
                'data' => "<< /Type /Font /Subtype /CIDFontType2 /BaseFont /{$baseName} "
                        . "/CIDSystemInfo << /Registry (Adobe) /Ordering (Identity) /Supplement 0 >> "
                        . "/FontDescriptor {$descriptorNum} 0 R /DW 1000 /W " . $font->widthsArray() . " "
                        . "/CIDToGIDMap /Identity >>",
            ];

            $bbox = $font->bbox;
            $scale = 1000 / $font->unitsPerEm();
            $fontObjects[] = [
                'num' => $descriptorNum,
                'data' => "<< /Type /FontDescriptor /FontName /{$baseName} /Flags {$font->flags} "
                        . "/FontBBox [" . $this->num($bbox[0] * $scale) . ' ' . $this->num($bbox[1] * $scale) . ' '
                        . $this->num($bbox[2] * $scale) . ' ' . $this->num($bbox[3] * $scale) . "] "
                        . "/ItalicAngle " . $this->num($font->italicAngle) . " "
                        . "/Ascent " . (int) round($font->ascent * $scale) . " "
                        . "/Descent " . (int) round($font->descent * $scale) . " "
                        . "/CapHeight " . (int) round($font->capHeight * $scale) . " "
                        . "/StemV {$font->stemV} /FontFile2 {$fileNum} 0 R >>",
            ];

            $fontObjects[] = [
                'num' => $fileNum,
                'stream' => $compressed,
                'dict' => '<< /Length ' . strlen($compressed) . ' /Length1 ' . strlen($ttf) . ' /Filter /FlateDecode >>',
            ];

            $fontObjects[] = [
                'num' => $toUnicodeNum,
                'stream' => gzcompress($this->buildToUnicodeCMap(), 9),
                'dict' => '<< /Length ' . strlen(gzcompress($this->buildToUnicodeCMap(), 9)) . ' /Filter /FlateDecode >>',
            ];
        }

        // Sayfa icerikleri
        $pageRefs = [];
        foreach ($chunks as $i => $chunk) {
            $compressed = gzcompress($chunk, 6);
            $objects[$contentObjNums[$i]] = [
                'stream' => $compressed,
                'dict' => '<< /Length ' . strlen($compressed) . ' /Filter /FlateDecode >>',
            ];

            $fontRes = [];
            foreach (array_keys($this->fonts) as $alias) {
                $fontRes[] = '/' . $alias . ' ' . $fontObjNums[$alias] . ' 0 R';
            }

            $objects[$pageObjNums[$i]] = [
                'data' => "<< /Type /Page /Parent {$pagesObjNum} 0 R "
                        . "/MediaBox [0 0 " . $this->num($this->pageWidth) . ' ' . $this->num($this->pageHeight) . "] "
                        . "/Resources << /Font << " . implode(' ', $fontRes) . " >> >> "
                        . "/Contents {$contentObjNums[$i]} 0 R >>",
            ];

            $pageRefs[] = $pageObjNums[$i] . ' 0 R';
        }

        $objects[$catalogObjNum] = ['data' => "<< /Type /Catalog /Pages {$pagesObjNum} 0 R >>"];
        $objects[$pagesObjNum] = ['data' => "<< /Type /Pages /Kids [" . implode(' ', $pageRefs) . "] /Count " . count($pageRefs) . " >>"];

        foreach ($fontObjects as $fo) {
            $objects[$fo['num']] = $fo;
        }

        // Nesneleri sirala
        ksort($objects);

        // PDF dosyasi
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];

        foreach ($objects as $num => $obj) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $num . " 0 obj\n";

            if (isset($obj['data'])) {
                $pdf .= $obj['data'] . "\n";
            } else {
                $pdf .= $obj['dict'] . "\nstream\n" . $obj['stream'] . "\nendstream\n";
            }
            $pdf .= "endobj\n";
        }

        $xrefOffset = strlen($pdf);
        $maxObj = max(array_keys($objects));

        $pdf .= "xref\n0 " . ($maxObj + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxObj; $i++) {
            if (isset($offsets[$i])) {
                $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
            } else {
                $pdf .= "0000000000 65535 f \n";
            }
        }

        $pdf .= "trailer\n<< /Size " . ($maxObj + 1) . " /Root {$catalogObjNum} 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF\n";

        return $pdf;
    }

    /**
     * ToUnicode CMap (metin kopyalanabilirligi icin).
     */
    private function buildToUnicodeCMap(): string
    {
        $entries = '';
        $count = 0;

        foreach ($this->usedChars as $cp => $gid) {
            $entries .= sprintf("<%04X> <%04X>\n", $gid, $cp);
            $count++;
            if ($count >= 100) {
                $entries = "1 beginbfchar\n" . $entries . "endbfchar\n";
                $count = 0;
            }
        }

        $cmap = "/CIDInit /ProcSet findresource begin\n"
              . "12 dict begin\n"
              . "begincmap\n"
              . "/CIDSystemInfo << /Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def\n"
              . "/CMapName /Adobe-Identity-UCS def\n"
              . "/CMapType 2 def\n"
              . "1 begincodespacerange\n<0000> <FFFF>\nendcodespacerange\n";

        if ($entries !== '') {
            $cmap .= $entries;
        } else {
            $cmap .= "1 beginbfchar\n<0000> <0000>\nendbfchar\n";
        }

        $cmap .= "endcmap\nCMapName currentdict /CMap defineresource pop\nend\nend\n";

        return $cmap;
    }

    private function num(float $value): string
    {
        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.') ?: '0';
    }
}
