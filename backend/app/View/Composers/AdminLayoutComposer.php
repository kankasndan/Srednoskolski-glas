<?php

namespace App\View\Composers;

use App\Models\Appeal;
use App\Models\Feedback;
use App\Models\Report;
use Illuminate\View\View;

class AdminLayoutComposer
{
    public function compose(View $view): void
    {
        $admin = auth()->user();

        $view->with([
            'currentAdmin' => $admin,
            'currentAdminRole' => $admin?->getRoleNames()->first() ?? 'Guest',
            'pendingReportsCount' => Report::pendingTargetCount(),
            'pendingAppealsCount' => Appeal::query()->where('status', 'pending')->count(),
            'unreviewedFeedbackCount' => Feedback::unreviewedCount(),
        ]);
    }
}
