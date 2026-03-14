<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CameraController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'path' => ['required', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'mode' => ['required', 'in:push,pull'],
        ]);

        // Save camera details to DB and sync mediamtx path generation flow.
        // In production: write to DB, render mediamtx template, hot-reload config.

        return response()->json([
            'ok' => true,
            'camera' => $data,
            'note' => 'Persist camera and apply MediaMTX path config in production.',
        ]);
    }
}
