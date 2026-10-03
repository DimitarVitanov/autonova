<?php

namespace App\Services;

/**
 * Overlays the AutoNova identity onto a REAL vehicle photograph.
 *
 * The source photo is cover-cropped to the canonical 1600×1200 canvas, given a
 * soft bottom scrim for depth + legibility, and finished with a restrained
 * brand lockup (the AutoNova spark mark + wordmark) in the lower-left corner —
 * premium and unobtrusive, never plastered across the car.
 */
class VehicleImageBrander
{
    public const W = 1600;
    public const H = 1200;

    private string $font;

    public function __construct()
    {
        $this->font = resource_path('fonts/Archivo-Variable.ttf');
    }

    /**
     * @param  string  $sourcePath  Absolute path to the real source photo.
     * @return string  Branded JPEG binary at exactly 1600×1200.
     */
    public function brand(string $sourcePath): string
    {
        $src = @imagecreatefromstring((string) file_get_contents($sourcePath));
        if (! $src) {
            throw new \RuntimeException("Unreadable source image: {$sourcePath}");
        }

        $canvas = imagecreatetruecolor(self::W, self::H);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, false);

        $this->coverInto($canvas, $src);
        imagedestroy($src);

        $this->bottomScrim($canvas);
        $this->accentEdge($canvas);
        $this->lockup($canvas);

        ob_start();
        imagejpeg($canvas, null, 88);
        $data = ob_get_clean();
        imagedestroy($canvas);

        return $data;
    }

    /** Cover-crop the source to fill 1600×1200 without distortion. */
    private function coverInto($canvas, $src): void
    {
        $sw = imagesx($src);
        $sh = imagesy($src);
        $dstRatio = self::W / self::H;
        $srcRatio = $sw / $sh;

        if ($srcRatio > $dstRatio) {
            $cropW = (int) round($sh * $dstRatio);
            $cropH = $sh;
            $cropX = (int) round(($sw - $cropW) / 2);
            $cropY = 0;
        } else {
            $cropW = $sw;
            $cropH = (int) round($sw / $dstRatio);
            $cropX = 0;
            $cropY = (int) round(($sh - $cropH) / 2);
        }

        imagecopyresampled($canvas, $src, 0, 0, $cropX, $cropY, self::W, self::H, $cropW, $cropH);
    }

    /** Soft dark gradient rising from the bottom edge. */
    private function bottomScrim($canvas): void
    {
        $start = (int) (self::H * 0.58);
        for ($y = $start; $y < self::H; $y++) {
            $t = ($y - $start) / (self::H - $start); // 0 → 1 toward the bottom
            $alpha = 127 - (int) ($t * $t * 96);     // transparent → ~0.75 opaque
            $c = imagecolorallocatealpha($canvas, 9, 11, 14, max(0, min(127, $alpha)));
            imageline($canvas, 0, $y, self::W, $y, $c);
        }
    }

    /** A thin accent bar hugging the left edge — the AutoNova signature. */
    private function accentEdge($canvas): void
    {
        $accent = imagecolorallocatealpha($canvas, 236, 48, 19, 18);
        imagefilledrectangle($canvas, 0, 0, 7, self::H, $accent);
    }

    /** Brand lockup: spark mark + AUTO/NOVA wordmark, lower-left. */
    private function lockup($canvas): void
    {
        $x = 58;
        $baseline = self::H - 62;
        $mark = 46;
        $markTop = $baseline - $mark + 6;

        // Spark mark — accent rounded square with a white four-point star.
        $accent = imagecolorallocate($canvas, 236, 48, 19);
        $this->roundedRect($canvas, $x, $markTop, $x + $mark, $markTop + $mark, 12, $accent);
        $this->spark($canvas, $x + $mark / 2, $markTop + $mark / 2, $mark * 0.40);

        // Wordmark
        $white = imagecolorallocate($canvas, 247, 246, 245);
        $wx = $x + $mark + 16;
        $size = 25;
        $auto = 'AUTO';
        imagettftext($canvas, $size, 0, $wx, $baseline, $white, $this->font, $auto);
        imagettftext($canvas, $size, 0, $wx + 1, $baseline, $white, $this->font, $auto); // faux-bold
        $bbox = imagettfbbox($size, 0, $this->font, $auto);
        $autoW = $bbox[2] - $bbox[0];
        imagettftext($canvas, $size, 0, $wx + $autoW + 2, $baseline, $accent, $this->font, 'NOVA');
        imagettftext($canvas, $size, 0, $wx + $autoW + 3, $baseline, $accent, $this->font, 'NOVA');
    }

    /** Four-point sparkle (matches the SVG brand mark). */
    private function spark($canvas, float $cx, float $cy, float $r): void
    {
        $white = imagecolorallocate($canvas, 250, 248, 247);
        $inner = $r * 0.34;
        $pts = [
            $cx,          $cy - $r,
            $cx + $inner, $cy - $inner,
            $cx + $r,     $cy,
            $cx + $inner, $cy + $inner,
            $cx,          $cy + $r,
            $cx - $inner, $cy + $inner,
            $cx - $r,     $cy,
            $cx - $inner, $cy - $inner,
        ];
        imagefilledpolygon($canvas, array_map('intval', $pts), $white);
    }

    /** Filled rounded rectangle (GD has no native primitive). */
    private function roundedRect($canvas, int $x1, int $y1, int $x2, int $y2, int $r, $color): void
    {
        imagefilledrectangle($canvas, $x1 + $r, $y1, $x2 - $r, $y2, $color);
        imagefilledrectangle($canvas, $x1, $y1 + $r, $x2, $y2 - $r, $color);
        $d = $r * 2;
        imagefilledellipse($canvas, $x1 + $r, $y1 + $r, $d, $d, $color);
        imagefilledellipse($canvas, $x2 - $r, $y1 + $r, $d, $d, $color);
        imagefilledellipse($canvas, $x1 + $r, $y2 - $r, $d, $d, $color);
        imagefilledellipse($canvas, $x2 - $r, $y2 - $r, $d, $d, $color);
    }
}
