<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default cleanup settings
        Setting::setValue(
            'favorites_cleanup_days',
            30,
            'integer',
            'Number of days to keep favorites before automatic cleanup'
        );

        Setting::setValue(
            'favorites_cleanup_hours',
            0,
            'integer',
            'Number of hours to keep favorites before automatic cleanup'
        );

        Setting::setValue(
            'favorites_cleanup_minutes',
            0,
            'integer',
            'Number of minutes to keep favorites before automatic cleanup'
        );

        Setting::setValue(
            'favorites_cleanup_enabled',
            true,
            'boolean',
            'Whether automatic cleanup of old favorites is enabled'
        );
    }
}
