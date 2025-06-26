<?php

namespace App\Console\Commands;

use App\Models\Favorite;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class CleanupOldFavorites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'favorites:cleanup {--days= : Number of days to keep favorites (overrides setting)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old favorites based on configurable retention period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if cleanup is enabled
        $enabled = Setting::getValue('favorites_cleanup_enabled', true);
        if (!$enabled) {
            $this->info('Favorites cleanup is disabled.');
            Log::info('Favorites cleanup skipped - disabled in settings');
            return 0;
        }

        // Get days from command option or settings
        $days = $this->option('days') ?? Setting::getValue('favorites_cleanup_days', 30);
        $cutoffDate = now()->subDays($days);

        $this->info("Starting cleanup of favorites older than {$days} days...");
        Log::info("Starting favorites cleanup for items older than {$days} days");

        try {
            // Get count of favorites to be deleted
            $countToDelete = Favorite::where('created_at', '<', $cutoffDate)->count();

            if ($countToDelete === 0) {
                $this->info('No old favorites found to delete.');
                Log::info('No old favorites found to delete');
                return 0;
            }

            $this->info("Found {$countToDelete} favorites to delete.");

            // Delete old favorites
            $deletedCount = Favorite::where('created_at', '<', $cutoffDate)->delete();

            $this->info("Successfully deleted {$deletedCount} old favorites.");
            Log::info("Successfully deleted {$deletedCount} old favorites", [
                'cutoff_date' => $cutoffDate->toDateTimeString(),
                'retention_days' => $days
            ]);

            return 0;

        } catch (\Exception $e) {
            $errorMessage = "Error during favorites cleanup: " . $e->getMessage();
            $this->error($errorMessage);
            Log::error($errorMessage, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'cutoff_date' => $cutoffDate->toDateTimeString(),
                'retention_days' => $days
            ]);

            return 1;
        }
    }
}
