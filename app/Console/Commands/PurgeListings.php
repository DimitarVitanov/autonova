<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Permanently removes EVERY vehicle listing (including soft-deleted ones) and
 * all listing photo files — e.g. to clear demo cars before real stock goes in.
 * Their favourites, conversations, messages and equipment links go with them
 * (FK cascade). Users, dealers, catalogue and translations are not touched.
 *
 *   php artisan listings:purge
 */
class PurgeListings extends Command
{
    protected $signature = 'listings:purge {--force : skip the confirmation prompt}';

    protected $description = 'Permanently delete all vehicle listings and their photo files';

    public function handle(): int
    {
        $total = Vehicle::withTrashed()->count();

        if (! $this->option('force') && ! $this->confirm("Permanently delete all {$total} listings and their photos?")) {
            $this->warn('Nothing deleted.');

            return self::FAILURE;
        }

        Vehicle::withTrashed()->forceDelete();

        // With no listings left, nothing under vehicles/ is referenced any more —
        // this also sweeps files orphaned by earlier image rebrands.
        Storage::disk('public')->deleteDirectory('vehicles');

        $this->info("Deleted {$total} listings and all listing photos.");

        return self::SUCCESS;
    }
}
