<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Normalises every uploaded image to identical dimensions.
 *
 * All listing photos are cropped-to-fill a fixed 4:3 canvas (1600×1200) with a
 * matching 800×600 thumbnail, so the gallery and cards always line up perfectly
 * regardless of what the user uploaded — portrait, panorama or tiny phone shot.
 * Everything is stored as WebP, whatever format came in.
 */
class ImageService
{
    public const FULL_W = 1600;
    public const FULL_H = 1200;
    public const THUMB_W = 800;
    public const THUMB_H = 600;

    /** Accepted upload dimensions — smaller would be upscaled into a blur, larger exhausts GD. */
    public const MIN_W = 640;
    public const MIN_H = 480;
    public const MAX_SIDE = 8192;

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Store a normalised listing image + thumbnail on the public disk.
     *
     * @return array{path:string, thumb_path:string, width:int, height:int}
     */
    public function storeListingImage(UploadedFile $file, string $dir = 'vehicles'): array
    {
        return $this->normalise($file->getRealPath(), false, $dir);
    }

    /**
     * Normalise raw JPEG/PNG/WebP binary (e.g. a generated demo image) through the
     * exact same pipeline used for uploads.
     *
     * @return array{path:string, thumb_path:string, width:int, height:int}
     */
    public function storeBinary(string $binary, string $dir = 'vehicles'): array
    {
        return $this->normalise($binary, true, $dir);
    }

    private function normalise(string $source, bool $isBinary, string $dir): array
    {
        $name = Str::uuid()->toString();
        $path = "{$dir}/{$name}.webp";
        $thumbPath = "{$dir}/{$name}_thumb.webp";

        $full = $this->read($source, $isBinary)
            ->cover(self::FULL_W, self::FULL_H)
            ->encode(new WebpEncoder(quality: 84));

        $thumb = $this->read($source, $isBinary)
            ->cover(self::THUMB_W, self::THUMB_H)
            ->encode(new WebpEncoder(quality: 80));

        Storage::disk('public')->put($path, (string) $full);
        Storage::disk('public')->put($thumbPath, (string) $thumb);

        return [
            'path' => $path,
            'thumb_path' => $thumbPath,
            'width' => self::FULL_W,
            'height' => self::FULL_H,
        ];
    }

    /**
     * Store a square avatar / dealer logo (also normalised to one size).
     */
    public function storeSquare(UploadedFile $file, string $dir, int $size = 400): string
    {
        $path = "{$dir}/" . Str::uuid() . '.webp';

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
        $path = "{$dir}/" . Str::uuid() . '.webp';

        $encoded = $this->manager->decode($file->getRealPath())
            ->cover($w, $h)
            ->encode(new WebpEncoder(quality: 84));

        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    private function read(string $source, bool $isBinary)
    {
        return $isBinary
            ? $this->manager->decodeBinary($source)
            : $this->manager->decode($source);
    }
}
