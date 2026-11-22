<?php
/**
 * Akilli Kariyer - TrueType font ayristirici
 *
 * PDF'e gomulu (embedded) yazi tipi olusturmak icin cmap (unicode -> glyph id)
 * ve hmtx (genislik) tablolarini okur.
 */

namespace App\Core;

class TrueTypeFont
{
    private string $data = '';
    private array $tables = [];
    private int $unitsPerEm = 1000;
    private int $numGlyphs = 0;
    private int $numberOfHMetrics = 0;
    private array $advanceWidths = [];
    private array $cmap = [];

    public int $ascent = 900;
    public int $descent = -200;
    public int $capHeight = 700;
    public array $bbox = [0, -200, 1000, 900];
    public float $italicAngle = 0.0;
    public int $stemV = 80;
    public int $flags = 4;

    private string $postScriptName = 'Roboto';

    public function __construct(string $path, string $postScriptName = 'Roboto')
    {
        $this->postScriptName = $postScriptName;

        if (!is_file($path) || !($this->data = file_get_contents($path))) {
            throw new \RuntimeException('Yazı tipi dosyası okunamadı: ' . $path);
        }

        $this->parseDirectory();
        $this->parseHead();
        $this->parseMaxp();
        $this->parseHhea();
        $this->parseHmtx();
        $this->parseCmap();
        $this->parseOs2();
        $this->parsePost();
    }

    private function parseDirectory(): void
    {
        $numTables = $this->u16(4);
        for ($i = 0; $i < $numTables; $i++) {
            $pos = 12 + ($i * 16);
            $tag = substr($this->data, $pos, 4);
            $this->tables[$tag] = [
                'offset' => $this->u32($pos + 8),
                'length' => $this->u32($pos + 12),
            ];
        }
    }

    private function parseHead(): void
    {
        $o = $this->tables['head']['offset'];
        $this->unitsPerEm = $this->u16($o + 18);
        $this->bbox = [
            $this->i16($o + 36),
            $this->i16($o + 38),
            $this->i16($o + 40),
            $this->i16($o + 42),
        ];
    }

    private function parseMaxp(): void
    {
        $o = $this->tables['maxp']['offset'];
        $this->numGlyphs = $this->u16($o + 4);
    }

    private function parseHhea(): void
    {
        $o = $this->tables['hhea']['offset'];
        $this->ascent    = $this->i16($o + 4);
        $this->descent   = $this->i16($o + 6);
        $this->numberOfHMetrics = $this->u16($o + 34);
    }

    private function parseHmtx(): void
    {
        $o = $this->tables['hmtx']['offset'];
        $count = $this->numberOfHMetrics;
        for ($i = 0; $i < $count; $i++) {
            $this->advanceWidths[$i] = $this->u16($o + ($i * 4));
        }
    }

    private function parseOs2(): void
    {
        if (!isset($this->tables['OS/2'])) {
            return;
        }
        $o = $this->tables['OS/2']['offset'];
        $version = $this->u16($o);
        $this->stemV = 80;
        if ($version >= 2 && strlen($this->data) >= $o + 94) {
            $cap = $this->i16($o + 88);
            if ($cap > 0) {
                $this->capHeight = $cap;
            }
        }
        // fsSelection bit 5 (italic) & bit 7 (bold)
        $fsSelection = $this->u16($o + 62);
        $this->flags = 32; // Nonsymbolic
        if ($fsSelection & 0x01) {
            $this->flags |= 1; // Italic
        }
        if ($fsSelection & 0x20) {
            $this->flags |= 1 << 18; // ForceBold
        }
    }

    private function parsePost(): void
    {
        if (!isset($this->tables['post'])) {
            return;
        }
        $o = $this->tables['post']['offset'];
        $fixed = $this->i32($o + 4);
        $this->italicAngle = $fixed / 65536;
    }

    /**
     * cmap tablosunu okur (format 4 ve format 12 desteklenir).
     */
    private function parseCmap(): void
    {
        $base = $this->tables['cmap']['offset'];
        $numTables = $this->u16($base + 2);

        $bestOffset = null;
        $bestScore  = -1;

        for ($i = 0; $i < $numTables; $i++) {
            $rec = $base + 4 + ($i * 8);
            $platform = $this->u16($rec);
            $encoding = $this->u16($rec + 2);
            $offset = $this->u32($rec + 4);

            $score = 0;
            if ($platform === 3 && $encoding === 10) {
                $score = 5;
            } elseif ($platform === 0 && $encoding === 4) {
                $score = 4;
            } elseif ($platform === 3 && $encoding === 1) {
                $score = 3;
            } elseif ($platform === 0 && $encoding === 3) {
                $score = 2;
            } elseif ($platform === 3 && $encoding === 0) {
                $score = 1;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestOffset = $base + $offset;
            }
        }

        if ($bestOffset === null) {
            throw new \RuntimeException('cmap alt tablosu bulunamadı.');
        }

        $format = $this->u16($bestOffset);
        if ($format === 4) {
            $this->parseCmap4($bestOffset);
        } elseif ($format === 12) {
            $this->parseCmap12($bestOffset);
        } else {
            throw new \RuntimeException('Desteklenmeyen cmap formatı: ' . $format);
        }
    }

    private function parseCmap4(int $offset): void
    {
        $segCount = (int) ($this->u16($offset + 6) / 2);
        $endCodes   = $offset + 14;
        $startCodes = $endCodes + ($segCount * 2) + 2;
        $idDeltas   = $startCodes + ($segCount * 2);
        $idRanges   = $idDeltas + ($segCount * 2);

        for ($s = 0; $s < $segCount; $s++) {
            $end   = $this->u16($endCodes + ($s * 2));
            $start = $this->u16($startCodes + ($s * 2));
            $delta = $this->i16($idDeltas + ($s * 2));
            $range = $this->u16($idRanges + ($s * 2));

            if ($start === 0xFFFF) {
                continue;
            }

            for ($c = $start; $c <= $end && $c !== 0x10000; $c++) {
                if ($range === 0) {
                    $gid = ($c + $delta) & 0xFFFF;
                } else {
                    $glyphPos = $idRanges + ($s * 2) + $range + (($c - $start) * 2);
                    if ($glyphPos + 1 >= strlen($this->data)) {
                        continue;
                    }
                    $gid = $this->u16($glyphPos);
                    if ($gid !== 0) {
                        $gid = ($gid + $delta) & 0xFFFF;
                    }
                }
                if ($gid !== 0) {
                    $this->cmap[$c] = $gid;
                }
            }
        }
    }

    private function parseCmap12(int $offset): void
    {
        $nGroups = $this->u32($offset + 12);
        for ($g = 0; $g < $nGroups; $g++) {
            $rec = $offset + 16 + ($g * 12);
            $start = $this->u32($rec);
            $end = $this->u32($rec + 4);
            $startGid = $this->u32($rec + 8);
            for ($c = $start; $c <= $end; $c++) {
                $this->cmap[$c] = $startGid + ($c - $start);
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /* Genel API                                                            */
    /* ------------------------------------------------------------------ */

    public function glyphId(int $codepoint): int
    {
        return $this->cmap[$codepoint] ?? 0;
    }

    public function hasGlyph(int $codepoint): bool
    {
        return isset($this->cmap[$codepoint]);
    }

    /**
     * Glyph ilerleme genisligi (font birimi cinsinden).
     */
    public function advanceWidth(int $gid): int
    {
        if ($gid < $this->numberOfHMetrics) {
            return $this->advanceWidths[$gid] ?? 0;
        }
        return $this->advanceWidths[$this->numberOfHMetrics - 1] ?? 0;
    }

    /**
     * Metin genisligi (PDF birim / pt).
     */
    public function textWidth(string $text, float $fontSize): float
    {
        $width = 0;
        $len = mb_strlen($text, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $cp = mb_ord(mb_substr($text, $i, 1, 'UTF-8'), 'UTF-8');
            $width += $this->advanceWidth($this->glyphId($cp));
        }
        return ($width * $fontSize) / $this->unitsPerEm;
    }

    /**
     * Metni PDF Identity-H onaltilik dizisine cevirir.
     *
     * @return array{hex:string, missing:string[]}
     */
    public function encode(string $text): array
    {
        $hex = '';
        $missing = [];
        $len = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $cp = mb_ord($char, 'UTF-8');
            $gid = $this->glyphId($cp);

            if ($gid === 0 && $cp !== 32) {
                $missing[] = $char;
                $gid = $this->glyphId(63) ?: $gid; // '?' fallback
            }

            $hex .= sprintf('%04X', $gid);
        }

        return ['hex' => $hex, 'missing' => $missing];
    }

    public function rawData(): string
    {
        return $this->data;
    }

    public function postScriptName(): string
    {
        return $this->postScriptName;
    }

    public function unitsPerEm(): int
    {
        return $this->unitsPerEm;
    }

    public function numGlyphs(): int
    {
        return $this->numGlyphs;
    }

    /**
     * PDF W dizisi (glyph -> genislik / 1000).
     */
    public function widthsArray(): string
    {
        $scale = 1000 / $this->unitsPerEm;
        $parts = [];

        for ($gid = 0; $gid < $this->numberOfHMetrics; $gid++) {
            $w = (int) round($this->advanceWidth($gid) * $scale);
            $parts[] = $gid . ' [' . $w . ']';
        }

        // Kalan glyph'ler son metrik ile ayni genislige sahiptir
        if ($this->numGlyphs > $this->numberOfHMetrics) {
            $w = (int) round($this->advanceWidth($this->numberOfHMetrics - 1) * $scale);
            $parts[] = $this->numberOfHMetrics . ' [' . $w . ']';
        }

        return '[' . implode(' ', $parts) . ']';
    }

    /* ------------------------------------------------------------------ */
    /* Ikili okuma yardimcilari                                              */
    /* ------------------------------------------------------------------ */

    private function u16(int $offset): int
    {
        return (ord($this->data[$offset]) << 8) | ord($this->data[$offset + 1]);
    }

    private function i16(int $offset): int
    {
        $v = $this->u16($offset);
        return $v >= 0x8000 ? $v - 0x10000 : $v;
    }

    private function u32(int $offset): int
    {
        return (ord($this->data[$offset]) << 24)
             | (ord($this->data[$offset + 1]) << 16)
             | (ord($this->data[$offset + 2]) << 8)
             | ord($this->data[$offset + 3]);
    }

    private function i32(int $offset): int
    {
        $v = $this->u32($offset);
        return $v >= 0x80000000 ? $v - 0x100000000 : $v;
    }
}
