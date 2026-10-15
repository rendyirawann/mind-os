<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\AudioListen;
use App\Models\MindOs\AudioTrack;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Audio Guided Healing library (R1, R2, R6 in the web app). */
class AudioController extends Controller
{
    public function index(Request $request): View
    {
        $listens = DB::table('audio_listens')
            ->where('user_id', $request->user()->getKey())
            ->select('audio_track_id', DB::raw('COUNT(*) as total'))
            ->groupBy('audio_track_id')
            ->pluck('total', 'audio_track_id');

        return view('mindos.audio.index', [
            'tracks' => AudioTrack::published()->get()->groupBy('stage'),
            'listens' => $listens,
        ]);
    }

    public function show(AudioTrack $track): View
    {
        abort_unless($track->is_published, 404);

        return view('mindos.audio.show', [
            'track' => $track,
            'others' => AudioTrack::published()->whereKeyNot($track->id)->limit(4)->get(['id', 'slug', 'title', 'stage', 'duration_seconds']),
        ]);
    }

    /** Called by the player when a session is finished. */
    public function listen(Request $request, AudioTrack $track): JsonResponse
    {
        AudioListen::create([
            'user_id' => $request->user()->getKey(),
            'audio_track_id' => $track->id,
            'listened_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}
