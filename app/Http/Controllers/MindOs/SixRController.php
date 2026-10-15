<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\MindBug;
use App\Models\MindOs\SixRLog;
use App\Support\SixR;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** 6R Tracker: log every step of Recognize → Reinforce across modalities. */
class SixRController extends Controller
{
    public function index(Request $request): View
    {
        $uid = $request->user()->getKey();

        return view('mindos.six-r.index', [
            'logs' => SixRLog::with('bug:id,name,code')->where('user_id', $uid)->latest()->paginate(12),
            'stageCounts' => SixRLog::where('user_id', $uid)
                ->select('stage', DB::raw('COUNT(*) as total'))
                ->groupBy('stage')
                ->pluck('total', 'stage'),
            'avgDrop' => round((float) SixRLog::where('user_id', $uid)
                ->whereNotNull('intensity_before')->whereNotNull('intensity_after')
                ->avg(DB::raw('intensity_before - intensity_after')), 1),
            'bugs' => MindBug::cachedAll(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'stage' => ['required', Rule::in(SixR::codes())],
            'note' => ['required', 'string', 'max:2000'],
            'mind_bug_id' => ['nullable', Rule::exists('mind_bugs', 'id')],
            'intensity_before' => ['nullable', 'integer', 'between:0,10'],
            'intensity_after' => ['nullable', 'integer', 'between:0,10'],
        ]);

        $data['modality'] = SixR::STAGES[$data['stage']]['modality'];
        $request->user()->sixRLogs()->create($data);

        return back()->with('success', 'Langkah ' . $data['stage'] . ' tercatat di 6R Tracker.');
    }

    public function destroy(Request $request, SixRLog $log): RedirectResponse
    {
        abort_unless($log->user_id === $request->user()->getKey(), 404);
        $log->delete();

        return back()->with('success', 'Catatan 6R dihapus.');
    }
}
