<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    /**
     * List cities with their schools, for the onboarding school dropdown.
     */
    public function index(): JsonResponse
    {
        $cities = City::query()
            ->with(['schools' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(fn (City $city) => [
                'id' => $city->id,
                'name' => $city->name,
                'schools' => $city->schools->map(fn ($school) => [
                    'id' => $school->id,
                    'name' => $school->name,
                ])->values(),
            ]);

        return response()->json([
            'cities' => $cities,
        ]);
    }
}
