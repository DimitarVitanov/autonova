<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Resolves curated REAL vehicle photos (database/data/demo_photo_manifest.php)
 * for a listing, matched to its category and — for cars — body type. Shared by
 * the VehicleSeeder and the `demo:rebrand-images` command so both pick photos
 * identically.
 */
class DemoPhotoLibrary
{
    private array $manifest;

    public function __construct()
    {
        $this->manifest = require database_path('data/demo_photo_manifest.php');
    }

    /**
     * Up to $count distinct source photo paths for a listing. Empty when the
     * category has no curated photos (caller should fall back to the generator).
     *
     * @return string[] absolute file paths
     */
    public function sources(string $catSlug, ?string $bodyType, bool $featured, int $count, int $seed): array
    {
        $pool = $this->manifest[$catSlug] ?? [];
        if ($pool === []) {
            return [];
        }

        $candidates = $pool;

        if ($catSlug === 'cars') {
            // Supercars stay aspirational — only on featured or genuinely sporty listings.
            $allowExotic = $featured || in_array($bodyType, ['Coupe', 'Convertible', 'Roadster'], true);
            if (! $allowExotic) {
                $candidates = array_values(array_filter($candidates, fn ($p) => ! $p['exotic']));
            }

            if ($bodyType) {
                $byBody = array_values(array_filter(
                    $candidates,
                    fn ($p) => $p['body'] === [] || in_array($bodyType, $p['body'], true)
                ));
                // Only lock to the body-matched set when it's rich enough to fill a
                // varied gallery; otherwise keep the wider pool so listings don't all
                // share one photo (and galleries aren't reduced to a single image).
                if (count($byBody) >= 6) {
                    $candidates = $byBody;
                }
            }
        }

        if ($candidates === []) {
            $candidates = $pool;
        }

        $n = count($candidates);
        $take = min($count, $n);
        $paths = [];
        for ($i = 0; $i < $take; $i++) {
            $photo = $candidates[($seed + $i) % $n];
            $paths[] = Storage::disk('local')->path("demo-photos/{$catSlug}/{$photo['key']}.jpg");
        }

        return $paths;
    }
}
