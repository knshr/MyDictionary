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
    protected $signature = 'favorites:cleanup {--days= : Number of days to keep favorites (overrides setting)} {--hours= : Number of hours to keep favorites (overrides setting)} {--minutes= : Number of minutes to keep favorites (overrides setting)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old favorites based on configurable retention period (days, hours, minutes)';

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

        // Get time values from command options or settings
        $days = $this->option('days') ?? Setting::getValue('favorites_cleanup_days', 30);
        $hours = $this->option('hours') ?? Setting::getValue('favorites_cleanup_hours', 0);
        $minutes = $this->option('minutes') ?? Setting::getValue('favorites_cleanup_minutes', 0);

        // Calculate cutoff date
        $cutoffDate = now()->subDays($days)->subHours($hours)->subMinutes($minutes);

        // Build time description
        $timeDescription = $this->buildTimeDescription($days, $hours, $minutes);

        $this->info("Starting cleanup of favorites older than {$timeDescription}...");
        Log::info("Starting favorites cleanup for items older than {$timeDescription}");

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
                'retention_days' => $days,
                'retention_hours' => $hours,
                'retention_minutes' => $minutes
            ]);

            return 0;

        } catch (\Exception $e) {
            $errorMessage = "Error during favorites cleanup: " . $e->getMessage();
            $this->error($errorMessage);
            Log::error($errorMessage, [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'cutoff_date' => $cutoffDate->toDateTimeString(),
                'retention_days' => $days,
                'retention_hours' => $hours,
                'retention_minutes' => $minutes
            ]);

            return 1;
        }
    }

    /**
     * Build a human-readable time description
     */
    private function buildTimeDescription(int $days, int $hours, int $minutes): string
    {
        $parts = [];

        if ($days > 0) {
            $parts[] = $days . ' ' . ($days === 1 ? 'day' : 'days');
        }

        if ($hours > 0) {
            $parts[] = $hours . ' ' . ($hours === 1 ? 'hour' : 'hours');
        }

        if ($minutes > 0) {
            $parts[] = $minutes . ' ' . ($minutes === 1 ? 'minute' : 'minutes');
        }

        if (empty($parts)) {
            return '0 minutes';
        }

        return implode(', ', $parts);
    }
}
