<?php

namespace App\Services;

/**
 * Generates attractive, uniform demo vehicle photos with GD — gradient studio
 * backdrops, a category-appropriate silhouette and clean typography. Every image
 * is exactly 1600×1200 so seeded listings look as consistent as real uploads.
 */
class DemoImageGenerator
{
    public const W = 1600;
    public const H = 1200;

    private string $font;

    /** Modern automotive "studio" gradients: [topHex, bottomHex, floorHex]. */
    private array $palettes = [
        ['#2b3a55', '#141b2b', '#0d1119'], // midnight blue
        ['#3a3f4b', '#1c1f27', '#12141a'], // graphite
        ['#264653', '#152a30', '#0d181c'], // deep teal
        ['#4a2c33', '#241417', '#160c0e'], // burgundy
        ['#2f4739', '#16241c', '#0e150f'], // forest
        ['#3d3346', '#1e1826', '#130f18'], // plum
        ['#42403a', '#22211d', '#151410'], // warm slate
        ['#233b4a', '#0f2430', '#08161d'], // ocean
    ];

    public function __construct()
    {
        $this->font = resource_path('fonts/Archivo-Variable.ttf');
    }

    /**
     * @param array $o keys: category, make, model, version, year, fuel, color, seed, variant
     */
    public function generate(array $o): string
    {
        $seed = (int) ($o['seed'] ?? 0);
        $variant = (int) ($o['variant'] ?? 0);
        $palette = $this->palettes[($seed + $variant) % count($this->palettes)];

        $img = imagecreatetruecolor(self::W, self::H);

        $this->verticalGradient($img, $palette[0], $palette[1]);
        $this->studioGlow($img, $variant);
        $this->floor($img, $palette[2]);
        $this->textScrim($img);

        // Framing changes per gallery variant so each photo feels like a new angle.
        $scale = [0.62, 0.72, 0.54, 0.66, 0.58][$variant % 5];
        $offsetX = [0, -120, 140, 60, -70][$variant % 5];

        $this->silhouette($img, $o['category'] ?? 'cars', $scale, $offsetX, $variant);

        $this->accentLine($img);
        $this->labels($img, $o);
        $this->watermark($img);

        ob_start();
        imagejpeg($img, null, 86);
        $data = ob_get_clean();
        imagedestroy($img);

        return $data;
    }

    // ---- Background layers --------------------------------------------------

    private function verticalGradient($img, string $top, string $bottom): void
    {
        [$tr, $tg, $tb] = $this->hex($top);
        [$br, $bg, $bb] = $this->hex($bottom);
        for ($y = 0; $y < self::H; $y++) {
            $t = $y / self::H;
            $c = imagecolorallocate(
                $img,
                (int) ($tr + ($br - $tr) * $t),
                (int) ($tg + ($bg - $tg) * $t),
                (int) ($tb + ($bb - $tb) * $t)
            );
            imageline($img, 0, $y, self::W, $y, $c);
        }
    }

    private function studioGlow($img, int $variant): void
    {
        $cx = (int) (self::W * [0.55, 0.46, 0.66, 0.58, 0.5][$variant % 5]);
        $cy = (int) (self::H * 0.40);
        // Soft, restrained studio light — brightest at the centre, fading out.
        for ($r = 620; $r > 0; $r -= 5) {
            $t = $r / 620;                 // 1 at edge → 0 at centre
            $alpha = 92 + (int) ($t * 35); // 92 (centre) → 127 (transparent edge)
            $c = imagecolorallocatealpha($img, 235, 238, 244, min(127, $alpha));
            imagefilledellipse($img, $cx, $cy, (int) ($r * 1.55), $r, $c);
        }
    }

    private function textScrim($img): void
    {
        // Vertical dark scrim from the left edge so labels stay legible over the glow.
        for ($x = 0; $x < 620; $x++) {
            $alpha = 90 + (int) (($x / 620) * 37); // 90 → 127 (fades to transparent)
            $c = imagecolorallocatealpha($img, 10, 12, 16, min(127, $alpha));
            imageline($img, $x, 0, $x, (int) (self::H * 0.42), $c);
        }
    }

    private function floor($img, string $floorHex): void
    {
        [$r, $g, $b] = $this->hex($floorHex);
        $floorY = (int) (self::H * 0.78);
        $c = imagecolorallocate($img, $r, $g, $b);
        imagefilledrectangle($img, 0, $floorY, self::W, self::H, $c);
        // reflection band
        $refl = imagecolorallocatealpha($img, 255, 255, 255, 116);
        imagefilledrectangle($img, 0, $floorY, self::W, $floorY + 3, $refl);
    }

    private function accentLine($img): void
    {
        $accent = imagecolorallocate($img, 236, 48, 19);
        imagefilledrectangle($img, 0, 0, 10, self::H, $accent);
    }

    // ---- Silhouettes --------------------------------------------------------

    private function silhouette($img, string $category, float $scale, int $offsetX, int $variant): void
    {
        $baseW = self::W * $scale;
        $baseH = $baseW * 0.42;
        $cx = self::W / 2 + $offsetX;
        $groundY = self::H * 0.76;
        $left = $cx - $baseW / 2;

        $body = imagecolorallocatealpha($img, 8, 10, 14, 40);
        $hi = imagecolorallocatealpha($img, 255, 255, 255, 108);
        $glass = imagecolorallocatealpha($img, 150, 190, 220, 96);

        match ($category) {
            'motorcycles' => $this->drawMotorcycle($img, $left, $groundY, $baseW, $baseH, $body, $hi, $glass),
            'vans' => $this->drawVan($img, $left, $groundY, $baseW, $baseH, $body, $hi, $glass),
            'trucks' => $this->drawTruck($img, $left, $groundY, $baseW, $baseH, $body, $hi, $glass),
            'machinery' => $this->drawTractor($img, $left, $groundY, $baseW, $baseH, $body, $hi, $glass),
            'trailers' => $this->drawTrailer($img, $left, $groundY, $baseW, $baseH, $body, $hi, $glass),
            default => $this->drawCar($img, $left, $groundY, $baseW, $baseH, $body, $hi, $glass, $variant),
        };
    }

    private function wheel($img, float $x, float $y, float $r, $body): void
    {
        $tyre = imagecolorallocatealpha($img, 5, 6, 9, 30);
        $rim = imagecolorallocatealpha($img, 210, 214, 220, 70);
        $hub = imagecolorallocatealpha($img, 120, 126, 134, 80);
        imagefilledellipse($img, (int) $x, (int) $y, (int) ($r * 2), (int) ($r * 2), $tyre);
        imagefilledellipse($img, (int) $x, (int) $y, (int) ($r * 1.15), (int) ($r * 1.15), $rim);
        imagefilledellipse($img, (int) $x, (int) $y, (int) ($r * 0.5), (int) ($r * 0.5), $hub);
    }

    private function poly($img, array $pts, $color): void
    {
        imagefilledpolygon($img, $pts, $color);
    }

    private function drawCar($img, $l, $g, $w, $h, $body, $hi, $glass, $variant): void
    {
        $roofPeak = ($variant % 2 === 0) ? 0.30 : 0.36; // sedan vs coupe-ish
        $pts = [
            $l + $w * 0.02, $g,
            $l + $w * 0.06, $g - $h * 0.42,
            $l + $w * 0.20, $g - $h * 0.55,
            $l + $w * 0.34, $g - $h * $roofPeak - $h * 0.55,
            $l + $w * 0.62, $g - $h * $roofPeak - $h * 0.55,
            $l + $w * 0.78, $g - $h * 0.58,
            $l + $w * 0.95, $g - $h * 0.50,
            $l + $w * 0.99, $g - $h * 0.24,
            $l + $w * 0.98, $g,
        ];
        $this->poly($img, $pts, $body);
        // glass
        $this->poly($img, [
            $l + $w * 0.24, $g - $h * 0.56,
            $l + $w * 0.35, $g - $h * ($roofPeak + 0.53),
            $l + $w * 0.60, $g - $h * ($roofPeak + 0.53),
            $l + $w * 0.70, $g - $h * 0.56,
        ], $glass);
        imagesetthickness($img, 3);
        imageline($img, (int) ($l + $w * 0.06), (int) ($g - $h * 0.42), (int) ($l + $w * 0.20), (int) ($g - $h * 0.55), $hi);
        imagesetthickness($img, 1);
        $this->wheel($img, $l + $w * 0.24, $g, $h * 0.30, $body);
        $this->wheel($img, $l + $w * 0.80, $g, $h * 0.30, $body);
    }

    private function drawVan($img, $l, $g, $w, $h, $body, $hi, $glass): void
    {
        $pts = [
            $l + $w * 0.02, $g,
            $l + $w * 0.03, $g - $h * 0.85,
            $l + $w * 0.55, $g - $h * 0.98,
            $l + $w * 0.70, $g - $h * 0.95,
            $l + $w * 0.86, $g - $h * 0.55,
            $l + $w * 0.99, $g - $h * 0.40,
            $l + $w * 0.99, $g,
        ];
        $this->poly($img, $pts, $body);
        $this->poly($img, [
            $l + $w * 0.72, $g - $h * 0.90,
            $l + $w * 0.83, $g - $h * 0.58,
            $l + $w * 0.96, $g - $h * 0.44,
            $l + $w * 0.85, $g - $h * 0.90,
        ], $glass);
        $this->wheel($img, $l + $w * 0.22, $g, $h * 0.28, $body);
        $this->wheel($img, $l + $w * 0.82, $g, $h * 0.28, $body);
    }

    private function drawTruck($img, $l, $g, $w, $h, $body, $hi, $glass): void
    {
        // trailer box
        $this->poly($img, [
            $l + $w * 0.02, $g,
            $l + $w * 0.02, $g - $h * 1.05,
            $l + $w * 0.62, $g - $h * 1.05,
            $l + $w * 0.62, $g,
        ], $body);
        // cab
        $this->poly($img, [
            $l + $w * 0.64, $g,
            $l + $w * 0.64, $g - $h * 0.92,
            $l + $w * 0.82, $g - $h * 0.92,
            $l + $w * 0.98, $g - $h * 0.55,
            $l + $w * 0.99, $g,
        ], $body);
        $this->poly($img, [
            $l + $w * 0.83, $g - $h * 0.88,
            $l + $w * 0.95, $g - $h * 0.58,
            $l + $w * 0.85, $g - $h * 0.58,
        ], $glass);
        $this->wheel($img, $l + $w * 0.14, $g, $h * 0.24, $body);
        $this->wheel($img, $l + $w * 0.30, $g, $h * 0.24, $body);
        $this->wheel($img, $l + $w * 0.74, $g, $h * 0.24, $body);
    }

    private function drawTractor($img, $l, $g, $w, $h, $body, $hi, $glass): void
    {
        $this->poly($img, [
            $l + $w * 0.30, $g - $h * 0.30,
            $l + $w * 0.30, $g - $h * 0.55,
            $l + $w * 0.55, $g - $h * 0.55,
            $l + $w * 0.58, $g - $h * 1.05,
            $l + $w * 0.86, $g - $h * 1.05,
            $l + $w * 0.86, $g - $h * 0.30,
        ], $body);
        // engine hood
        $this->poly($img, [
            $l + $w * 0.05, $g - $h * 0.30,
            $l + $w * 0.05, $g - $h * 0.55,
            $l + $w * 0.30, $g - $h * 0.55,
            $l + $w * 0.30, $g - $h * 0.30,
        ], $body);
        $this->poly($img, [
            $l + $w * 0.60, $g - $h * 1.00,
            $l + $w * 0.84, $g - $h * 1.00,
            $l + $w * 0.84, $g - $h * 0.60,
            $l + $w * 0.60, $g - $h * 0.60,
        ], $glass);
        $this->wheel($img, $l + $w * 0.16, $g, $h * 0.26, $body); // small front
        $this->wheel($img, $l + $w * 0.70, $g, $h * 0.50, $body); // big rear
    }

    private function drawTrailer($img, $l, $g, $w, $h, $body, $hi, $glass): void
    {
        $this->poly($img, [
            $l + $w * 0.08, $g - $h * 0.30,
            $l + $w * 0.08, $g - $h * 0.95,
            $l + $w * 0.95, $g - $h * 0.95,
            $l + $w * 0.95, $g - $h * 0.30,
        ], $body);
        // drawbar
        imagesetthickness($img, 6);
        imageline($img, (int) ($l), (int) ($g - $h * 0.20), (int) ($l + $w * 0.10), (int) ($g - $h * 0.32), $body);
        imagesetthickness($img, 1);
        $this->wheel($img, $l + $w * 0.40, $g, $h * 0.24, $body);
        $this->wheel($img, $l + $w * 0.60, $g, $h * 0.24, $body);
    }

    private function drawMotorcycle($img, $l, $g, $w, $h, $body, $hi, $glass): void
    {
        $this->wheel($img, $l + $w * 0.20, $g, $h * 0.40, $body);
        $this->wheel($img, $l + $w * 0.80, $g, $h * 0.40, $body);
        // frame + tank + seat
        $this->poly($img, [
            $l + $w * 0.20, $g - $h * 0.40,
            $l + $w * 0.40, $g - $h * 0.75,
            $l + $w * 0.62, $g - $h * 0.75,
            $l + $w * 0.70, $g - $h * 0.55,
            $l + $w * 0.80, $g - $h * 0.40,
            $l + $w * 0.55, $g - $h * 0.42,
        ], $body);
        imagesetthickness($img, 8);
        imageline($img, (int) ($l + $w * 0.36), (int) ($g - $h * 0.78), (int) ($l + $w * 0.20), (int) ($g - $h * 0.42), $hi);
        imagesetthickness($img, 1);
    }

    // ---- Typography ---------------------------------------------------------

    private function labels($img, array $o): void
    {
        $white = imagecolorallocate($img, 245, 244, 244);
        $muted = imagecolorallocatealpha($img, 245, 244, 244, 55);
        $accent = imagecolorallocate($img, 255, 120, 96);

        $make = strtoupper((string) ($o['make'] ?? 'AutoNova'));
        $model = (string) ($o['model'] ?? '');
        $version = (string) ($o['version'] ?? '');
        $meta = trim(($o['year'] ?? '') . '   ·   ' . ($o['fuel'] ?? ''));

        $this->text($img, 70, 150, 58, $make, $white, true);
        $this->text($img, 70, 215, 40, $model, $white, false);
        if ($version !== '') {
            $this->text($img, 70, 262, 26, $version, $muted, false);
        }
        $this->text($img, 72, 320, 18, $meta, $accent, false);
    }

    private function watermark($img): void
    {
        $white = imagecolorallocatealpha($img, 245, 244, 244, 30);
        $accent = imagecolorallocate($img, 236, 48, 19);
        $y = self::H - 60;
        // "AUTONOVA" bottom-right with an accent dot
        $text = 'AUTONOVA';
        $bbox = imagettfbbox(22, 0, $this->font, $text);
        $tw = $bbox[2] - $bbox[0];
        $x = self::W - $tw - 70;
        $this->text($img, $x, $y, 22, $text, $white, true);
        imagefilledellipse($img, (int) ($x - 22), (int) ($y - 8), 12, 12, $accent);
    }

    private function text($img, float $x, float $y, float $size, string $str, $color, bool $bold): void
    {
        if ($str === '') {
            return;
        }
        imagettftext($img, $size, 0, (int) $x, (int) $y, $color, $this->font, $str);
        if ($bold) {
            // faux-bold: over-draw with sub-pixel offsets (variable font renders at Regular)
            imagettftext($img, $size, 0, (int) $x + 1, (int) $y, $color, $this->font, $str);
            imagettftext($img, $size, 0, (int) $x, (int) $y - 1, $color, $this->font, $str);
        }
    }

    // ---- Helpers ------------------------------------------------------------

    private function hex(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
