<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    /**
     * Get cleanup settings
     */
    public function getCleanupSettings(): JsonResponse
    {
        $settings = [
            'favorites_cleanup_days' => Setting::getValue('favorites_cleanup_days', 30),
            'favorites_cleanup_hours' => Setting::getValue('favorites_cleanup_hours', 0),
            'favorites_cleanup_minutes' => Setting::getValue('favorites_cleanup_minutes', 0),
            'favorites_cleanup_enabled' => Setting::getValue('favorites_cleanup_enabled', true),
        ];

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Update cleanup settings
     */
    public function updateCleanupSettings(Request $request): JsonResponse
    {
        $request->validate([
            'favorites_cleanup_days' => 'required|integer|min:0|max:365',
            'favorites_cleanup_hours' => 'required|integer|min:0|max:23',
            'favorites_cleanup_minutes' => 'required|integer|min:0|max:59',
            'favorites_cleanup_enabled' => 'required|boolean',
        ]);

        // Ensure at least one time unit is set
        if ($request->favorites_cleanup_days === 0 && 
            $request->favorites_cleanup_hours === 0 && 
            $request->favorites_cleanup_minutes === 0) {
            return response()->json([
                'success' => false,
                'message' => 'At least one time unit (days, hours, or minutes) must be greater than 0',
            ], 422);
        }

        try {
            Setting::setValue(
                'favorites_cleanup_days',
                $request->favorites_cleanup_days,
                'integer',
                'Number of days to keep favorites before automatic cleanup'
            );

            Setting::setValue(
                'favorites_cleanup_hours',
                $request->favorites_cleanup_hours,
                'integer',
                'Number of hours to keep favorites before automatic cleanup'
            );

            Setting::setValue(
                'favorites_cleanup_minutes',
                $request->favorites_cleanup_minutes,
                'integer',
                'Number of minutes to keep favorites before automatic cleanup'
            );

            Setting::setValue(
                'favorites_cleanup_enabled',
                $request->favorites_cleanup_enabled,
                'boolean',
                'Whether automatic cleanup of old favorites is enabled'
            );

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'data' => [
                    'favorites_cleanup_days' => $request->favorites_cleanup_days,
                    'favorites_cleanup_hours' => $request->favorites_cleanup_hours,
                    'favorites_cleanup_minutes' => $request->favorites_cleanup_minutes,
                    'favorites_cleanup_enabled' => $request->favorites_cleanup_enabled,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
