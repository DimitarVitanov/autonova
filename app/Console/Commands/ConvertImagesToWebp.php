<?php

namespace App\Console\Commands;

use App\Models\VehicleImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Re-encodes listing photos stored before the WebP switch (JPG/PNG) as WebP —
 * same dimensions, same folder — and repoints their `vehicle_images` rows.
 * Originals stay on disk unless --purge is given.
 *
 *   php artisan images:convert-webp --dry-run
 */
class ConvertImagesToWebp extends Command
{
    protected $signature = 'images:convert-webp
        {--dry-run : only report what would be converted}
        {--purge : also delete the original files from disk}';

    protected $description = 'Convert existing listing photos to WebP (non-destructive)';

    public function handle(): int
    {
        $query = VehicleImage::where(fn ($q) => $q
            ->where('path', 'not like', '%.webp')
            ->orWhere(fn ($q) => $q->whereNotNull('thumb_path')->where('thumb_path', 'not like', '%.webp')));

        $total = $query->count();

        if ($this->option('dry-run')) {
            $this->info("{$total} listing photos would be converted to WebP.");

            return self::SUCCESS;
        }

        $manager = new ImageManager(new Driver());
        $disk = Storage::disk('public');
        $done = 0;
        $failed = 0;

        $query->chunkById(100, function ($chunk) use ($manager, $disk, &$done, &$failed) {
            foreach ($chunk as $image) {
                try {
                    $old = [];
                    $new = [];

                    foreach (['path' => 84, 'thumb_path' => 80] as $column => $quality) {
                        $from = $image->{$column};
                        if (! $from || str_ends_with($from, '.webp')) {
                            continue;
                        }

                        $to = preg_replace('/\.[^.\/]+$/', '', $from) . '.webp';
                        $disk->put($to, (string) $manager->decodeBinary($disk->get($from))->encode(new WebpEncoder(quality: $quality)));
                        $new[$column] = $to;
                        $old[] = $from;
                    }

                    $image->update($new);

                    if ($this->option('purge')) {
                        $disk->delete($old);
                    }

                    $done++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->warn("  image #{$image->id}: {$e->getMessage()}");
                }
            }
        });

        $this->info("Done — {$done} photos converted, {$failed} failed.");

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
