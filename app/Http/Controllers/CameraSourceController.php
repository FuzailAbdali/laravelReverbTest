<?php

namespace App\Http\Controllers;

use App\Models\CameraSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CameraSourceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(CameraSource::query()->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'stream_key' => ['required', 'string', 'max:255', 'unique:camera_sources,stream_key'],
            'ingest_mode' => ['required', 'in:camera_push,server_pull'],
            'source_rtsp_url' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $camera = CameraSource::create($payload);

        return response()->json([
            'status' => 'created',
            'camera' => $camera,
            'ingest_endpoints' => [
                'rtsp_publish' => sprintf('rtsp://%s:8554/%s', request()->getHost(), $camera->stream_key),
                'rtmp_publish' => sprintf('rtmp://%s:1935/%s', request()->getHost(), $camera->stream_key),
                'hls_playback' => sprintf('http://%s:8888/%s/index.m3u8', request()->getHost(), $camera->stream_key),
            ],
        ], 201);
    }
}
