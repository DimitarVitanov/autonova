<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Alignment;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Normalises every uploaded image to identical dimensions.
 *
 * All listing photos land on a fixed 4:3 canvas (1600×1200) so the gallery and
 * cards always line up perfectly regardless of what the user uploaded — portrait,
 * panorama or tiny phone shot. Each photo is then stored as WebP in four widths
 * (thumb / mobile / tablet / desktop) and the browser picks one via srcset.
 */
class ImageService
{
    public const FULL_W = 1600;

    public const FULL_H = 1200;

    public const THUMB_W = 400;

    public const THUMB_H = 300;

    /** Stored renditions of every listing photo: width => [file suffix, WebP quality]. */
    public const RENDITIONS = [
        self::THUMB_W => ['_thumb', 78],
        640 => ['_sm', 80],
        1024 => ['_md', 82],
        self::FULL_W => ['', 84],
    ];

    /**
     * Largest share of a photo a centre-crop may cut away. Shapes close to 4:3
     * (3:2, 16:10, 5:4) are cropped to fill; portraits, squares and panoramas are
     * shown whole over a blurred fill of themselves, so no bumper or wheel is lost.
     */
    public const MAX_CROP_LOSS = 0.2;

    /** Accepted upload dimensions — smaller would be upscaled into a blur, larger exhausts GD. */
    public const MIN_W = 640;

    public const MIN_H = 480;

    public const MAX_SIDE = 8192;

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * Store a normalised listing image and its responsive renditions on the public disk.
     *
     * @return array{path:string, thumb_path:string, variants:array<int,string>, width:int, height:int}
     */
    public function storeListingImage(UploadedFile $file, string $dir = 'vehicles'): array
    {
        return $this->normalise($file->getRealPath(), false, $dir);
    }

    /**
     * Normalise raw JPEG/PNG/WebP binary (e.g. a generated demo image) through the
     * exact same pipeline used for uploads.
     *
     * @return array{path:string, thumb_path:string, variants:array<int,string>, width:int, height:int}
     */
    public function storeBinary(string $binary, string $dir = 'vehicles'): array
    {
        return $this->normalise($binary, true, $dir);
    }

    /**
     * @return array{path:string, thumb_path:string, variants:array<int,string>, width:int, height:int}
     */
    private function normalise(string $source, bool $isBinary, string $dir): array
    {
        $name = Str::uuid()->toString();
        $canvas = $this->fitToCanvas($this->read($source, $isBinary));
        $variants = [];

        foreach (self::RENDITIONS as $width => [$suffix, $quality]) {
            $rendition = $width === self::FULL_W
                ? $canvas
                : (clone $canvas)->resize($width, intdiv($width * self::FULL_H, self::FULL_W));

            $path = "{$dir}/{$name}{$suffix}.webp";
            Storage::disk('public')->put($path, (string) $rendition->encode(new WebpEncoder(quality: $quality)));
            $variants[$width] = $path;
        }

        return [
            'path' => $variants[self::FULL_W],
            'thumb_path' => $variants[self::THUMB_W],
            'variants' => $variants,
            'width' => self::FULL_W,
            'height' => self::FULL_H,
        ];
    }

    /**
     * Bring any photo to exactly FULL_W × FULL_H without distorting it.
     */
    private function fitToCanvas(ImageInterface $image): ImageInterface
    {
        if ($image->isAnimated()) {
            $image->removeAnimation();
        }

        $ratio = $image->width() / $image->height();
        $target = self::FULL_W / self::FULL_H;

        if (1 - min($ratio / $target, $target / $ratio) <= self::MAX_CROP_LOSS) {
            return $image->cover(self::FULL_W, self::FULL_H);
        }

        $photo = $image->scale(self::FULL_W, self::FULL_H);

        // Blur at a tiny size, then upscale in doublings with a light blur per
        // step — a single GD upscale of a blurred thumbnail comes out blocky.
        $backdrop = (clone $photo)->cover(100, 75)->blur(6);
        foreach ([200, 400, 800, self::FULL_W] as $width) {
            $backdrop->resize($width, intdiv($width * self::FULL_H, self::FULL_W))->blur(1);
        }

        return $backdrop->brightness(-22)->insert($photo, 0, 0, Alignment::CENTER);
    }

    /**
     * Store a square avatar / dealer logo (also normalised to one size).
     */
    public function storeSquare(UploadedFile $file, string $dir, int $size = 400): string
    {
        $path = "{$dir}/".Str::uuid().'.webp';

        $encoded = $this->manager->decode($file->getRealPath())
            ->cover($size, $size)
            ->encode(new WebpEncoder(quality: 85));

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    /**
     * Store a wide cover banner (e.g. dealer cover, 1600×480).
     */
    public function storeCover(UploadedFile $file, string $dir, int $w = 1600, int $h = 480): string
    {
        $path = "{$dir}/".Str::uuid().'.webp';

        $encoded = $this->manager->decode($file->getRealPath())
            ->cover($w, $h)
            ->encode(new WebpEncoder(quality: 84));

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    private function read(string $source, bool $isBinary): ImageInterface
    {
        return $isBinary
            ? $this->manager->decodeBinary($source)
            : $this->manager->decode($source);
    }
}
