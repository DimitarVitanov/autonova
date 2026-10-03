<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Services\DemoImageGenerator;
use App\Services\DemoPhotoLibrary;
use App\Services\ImageService;
use App\Services\VehicleImageBrander;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Replaces every existing listing's photos with real, AutoNova-branded vehicle
 * photographs — in place, WITHOUT touching the schema or any other table. Safe
 * to run against a live demo database (unlike `migrate:fresh`).
 *
 *   php artisan demo:rebrand-images
 */
class RebrandVehicleImages extends Command
{
    protected $signature = 'demo:rebrand-images {--purge : also delete the superseded image files from disk}';

    protected $description = 'Swap seeded listing photos for real, branded vehicle images (non-destructive)';

    public function handle(
        DemoPhotoLibrary $library,
        VehicleImageBrander $brander,
        ImageService $images,
        DemoImageGenerator $generator,
    ): int {
        $total = Vehicle::count();
        $this->info("Rebranding photos for {$total} listings…");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $done = 0;
        $failed = 0;

        Vehicle::with('category', 'images')->chunkById(50, function ($chunk) use (
            $library, $brander, $images, $generator, $bar, &$done, &$failed
        ) {
            foreach ($chunk as $vehicle) {
                try {
                    $this->rebrand($vehicle, $library, $brander, $images, $generator);
                    $done++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->newLine();
                    $this->warn("  #{$vehicle->id} {$vehicle->title}: {$e->getMessage()}");
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Done — {$done} listings rebranded, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function rebrand(
        Vehicle $vehicle,
        DemoPhotoLibrary $library,
        VehicleImageBrander $brander,
        ImageService $images,
        DemoImageGenerator $generator,
    ): void {
        $catSlug = $vehicle->category?->slug ?? 'cars';
        $existing = $vehicle->images;
        $count = $vehicle->is_featured ? 6 : 4;

        $sources = $library->sources(
            $catSlug,
            $vehicle->body_type,
            (bool) $vehicle->is_featured,
            $count,
            $vehicle->id,
        );

        $variants = $sources === [] ? range(0, $count - 1) : array_keys($sources);
        $newRows = [];

        foreach ($variants as $v) {
            $binary = isset($sources[$v]) && is_file($sources[$v])
                ? $brander->brand($sources[$v])
                : $generator->generate([
                    'category' => $catSlug,
                    'make' => $vehicle->title,
                    'model' => '',
                    'version' => $vehicle->version,
                    'year' => (string) $vehicle->year,
                    'fuel' => config("marketplace.fuels.{$vehicle->fuel}", $vehicle->fuel),
                    'color' => $vehicle->color,
                    'seed' => $vehicle->id,
                    'variant' => $v,
                ]);

            $stored = $images->storeBinary($binary, 'vehicles/' . now()->format('Y/m'));

            $newRows[] = VehicleImage::create([
                'vehicle_id' => $vehicle->id,
                'path' => $stored['path'],
                'thumb_path' => $stored['thumb_path'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'sort' => $v,
                'is_cover' => $v === 0,
            ]);
        }

        if ($newRows === []) {
            return; // never strip a listing of all photos
        }

        // Remove the superseded rows (files optionally purged).
        foreach ($existing as $old) {
            if ($this->option('purge')) {
                Storage::disk('public')->delete(array_filter([$old->path, $old->thumb_path]));
            }
            $old->delete();
        }
    }
}
