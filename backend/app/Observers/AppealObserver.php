<?php

namespace App\Observers;

use App\Models\Appeal;

class AppealObserver
{
    /**
     * Handle the Appeal "created" event.
     */
    public function created(Appeal $appeal): void
    {
        //
    }

    /**
     * Handle the Appeal "updated" event.
     */
    public function updated(Appeal $appeal): void
    {
        //
    }

    /**
     * Handle the Appeal "deleted" event.
     */
    public function deleted(Appeal $appeal): void
    {
        //
    }

    /**
     * Handle the Appeal "restored" event.
     */
    public function restored(Appeal $appeal): void
    {
        //
    }

    /**
     * Handle the Appeal "force deleted" event.
     */
    public function forceDeleted(Appeal $appeal): void
    {
        //
    }
}
