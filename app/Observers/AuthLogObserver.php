<?php

namespace App\Observers;

use App\Models\AuthLog;
use Illuminate\Http\Request;

class AuthLogObserver
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the AuthLog "created" event.
     */
    public function created(AuthLog $authLog): void
    {
        // Log additional information if needed
        \Log::info('Auth log created', [
            'user_id' => $authLog->user_id,
            'email' => $authLog->email,
            'action' => $authLog->action,
            'ip_address' => $authLog->ip_address,
            'success' => $authLog->success,
        ]);
    }

    /**
     * Handle the AuthLog "updated" event.
     */
    public function updated(AuthLog $authLog): void
    {
        //
    }

    /**
     * Handle the AuthLog "deleted" event.
     */
    public function deleted(AuthLog $authLog): void
    {
        //
    }

    /**
     * Handle the AuthLog "restored" event.
     */
    public function restored(AuthLog $authLog): void
    {
        //
    }

    /**
     * Handle the AuthLog "force deleted" event.
     */
    public function forceDeleted(AuthLog $authLog): void
    {
        //
    }
}
