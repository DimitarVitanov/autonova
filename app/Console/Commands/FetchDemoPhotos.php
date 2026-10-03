<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Downloads the curated set of REAL vehicle photos (see
 * database/data/demo_photo_manifest.php) into storage/app/demo-photos so the
 * VehicleSeeder can brand and normalise them without hitting the network on
 * every reseed. Run once: `php artisan demo:photos`.
 */
class FetchDemoPhotos extends Command
{
    protected $signature = 'demo:photos {--force : Re-download photos that already exist}';

    protected $description = 'Download curated real vehicle photos for demo seeding';

    private const UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36';

    public function handle(): int
    {
        $manifest = require database_path('data/demo_photo_manifest.php');
        $disk = Storage::disk('local');
        $ok = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($manifest as $category => $photos) {
            foreach ($photos as $photo) {
                $path = "demo-photos/{$category}/{$photo['key']}.jpg";

                if (! $this->option('force') && $disk->exists($path)) {
                    $skipped++;
                    continue;
                }

                $url = $this->url($photo);
                try {
                    $res = Http::withHeaders(['User-Agent' => self::UA])->timeout(30)->get($url);
                } catch (\Throwable $e) {
                    $res = null;
                }

                if ($res && $res->successful() && strlen($res->body()) > 5000) {
                    $disk->put($path, $res->body());
                    $ok++;
                    $this->line("  <info>✓</info> {$category}/{$photo['key']}");
                } else {
                    $failed++;
                    $code = $res?->status() ?? 'ERR';
                    $this->line("  <error>✗</error> {$category}/{$photo['key']} ({$code})");
                }
            }
        }

        $this->info("Done — {$ok} downloaded, {$skipped} already present, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function url(array $photo): string
    {
        return $photo['src'] === 'p'
            ? "https://images.pexels.com/photos/{$photo['id']}/pexels-photo-{$photo['id']}.jpeg?auto=compress&cs=tinysrgb&w=1600"
            : "https://images.unsplash.com/photo-{$photo['id']}?auto=format&fit=crop&w=1600&q=80";
    }
}
