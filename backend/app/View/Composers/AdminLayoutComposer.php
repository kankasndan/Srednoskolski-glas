<?php

namespace App\View\Composers;

use Illuminate\View\View;

class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $admin = auth()->user();

        $view->with([
            'currentAdmin' => $admin,
            'currentAdminRole' => $admin?->getRoleNames()->first() ?? 'Guest',
        ]);
    }
}