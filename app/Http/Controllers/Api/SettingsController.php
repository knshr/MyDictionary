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
            'favorites_cleanup_days' => 'required|integer|min:1|max:365',
            'favorites_cleanup_enabled' => 'required|boolean',
        ]);

        try {
            Setting::setValue(
                'favorites_cleanup_days',
                $request->favorites_cleanup_days,
                'integer',
                'Number of days to keep favorites before automatic cleanup'
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
