<?php

namespace App\Observers;

use App\Models\Sanction;

class SanctionObserver
{
    /**
     * Handle the Sanction "created" event.
     */
    public function created(Sanction $sanction): void
    {
        //
    }

    /**
     * Handle the Sanction "updated" event.
     */
    public function updated(Sanction $sanction): void
    {
        //
    }

    /**
     * Handle the Sanction "deleted" event.
     */
    public function deleted(Sanction $sanction): void
    {
        //
    }

    /**
     * Handle the Sanction "restored" event.
     */
    public function restored(Sanction $sanction): void
    {
        //
    }

    /**
     * Handle the Sanction "force deleted" event.
     */
    public function forceDeleted(Sanction $sanction): void
    {
        //
    }
}
