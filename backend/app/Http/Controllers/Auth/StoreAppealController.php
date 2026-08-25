<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppealRequest;
use App\Models\Appeal;
use App\Models\Sanction;
use App\Notifications\NewAppealNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StoreAppealController extends Controller
{
    public function __invoke(StoreAppealRequest $request, Sanction $sanction): JsonResponse
    {
        abort_unless((int) $sanction->user_id === (int) $request->user()->id, 404);

        $appeal = DB::transaction(function () use ($request, $sanction): ?Appeal {
            $locked = Sanction::query()->whereKey($sanction->getKey())->lockForUpdate()->first();

            if ($locked === null || ! $locked->isAppealable()) {
                return null;
            }

            return Appeal::create([
                'sanction_id' => $locked->id,
                'user_id' => $request->user()->id,
                'explanation' => $request->validated('explanation'),
                'status' => 'pending',
            ]);
        });

        if ($appeal === null) {
            return response()->json([
                'message' => 'Не можеш да поднесеш жалба за оваа санкција.',
            ], 422);
        }

        NewAppealNotification::syncForAppeal($appeal);

        return response()->json([
            'data' => [
                'id' => $appeal->id,
                'status' => $appeal->status,
            ],
        ], 201);
    }
}
