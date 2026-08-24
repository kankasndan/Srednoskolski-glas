<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Sanction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AcknowledgeSanctionController extends Controller
{
    /**
     * Mark a sanction popup as seen so it is not shown again.
     */
    public function __invoke(Request $request, Sanction $sanction): Response
    {
        abort_unless((int) $sanction->user_id === (int) $request->user()->id, 404);

        $sanction->forceFill(['acknowledged_at' => now()])->save();

        return response()->noContent();
    }
}
