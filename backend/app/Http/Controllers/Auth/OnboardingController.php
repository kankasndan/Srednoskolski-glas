<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'school' => ['required', 'string', 'max:255'],
            'area' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $user->fill([
            'name' => $validated['username'],
            'city' => $validated['city'],
            'school' => $validated['school'],
            'area' => $validated['area'],
            'year' => $validated['year'] ?? null,
            'onboarding_completed_at' => now(),
            'signed_up' => now()->toDateTimeString(),
        ]);
        $user->save();

        return response()->json([
            'message' => 'Onboarding saved',
            'user' => $user->fresh(),
        ]);
    }
}