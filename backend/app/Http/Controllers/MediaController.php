<?php

namespace App\Http\Controllers;

use App\Contracts\MediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(private readonly MediaStorage $storage) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'max:102400',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,application/pdf',
            ],
            'directory' => ['nullable', 'string', 'max:255'],
        ]);

        $media = $this->storage->upload(
            $request->file('file'),
            $validated['directory'] ?? config('media.directory'),
        );

        return response()->json($media, 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'string'],
        ]);

        return response()->json([
            'deleted' => $this->storage->delete($validated['id']),
        ]);
    }
}
