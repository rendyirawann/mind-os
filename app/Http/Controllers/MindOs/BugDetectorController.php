<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\BugAssessment;
use App\Models\MindOs\BugQuestion;
use App\Models\MindOs\MindBug;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Cek Kesehatan Pikiran: a 1-5 Likert questionnaire across the seven bugs. Each bug
 * gets a 0-100% score; the highest is the dominant bug and the overall
 * level decides which modality (Web App / Online Live / Tatap Muka) fits.
 */
class BugDetectorController extends Controller
{
    public function index(Request $request): View
    {
        $questions = BugQuestion::with('bug:id,code,name,color')
            ->where('is_active', true)
            ->orderBy('mind_bug_id')
            ->orderBy('sort_order')
            ->get(['id', 'mind_bug_id', 'question']);

        return view('mindos.bug-detector.index', [
            'questions' => $questions,
            'history' => BugAssessment::with('dominantBug:id,name,code')
                ->where('user_id', $request->user()->getKey())
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $questions = BugQuestion::where('is_active', true)->get(['id', 'mind_bug_id']);

        $rules = [];
        foreach ($questions as $q) {
            $rules["answers.{$q->id}"] = ['required', 'integer', 'between:1,5'];
        }

        $data = $request->validate($rules, [
            'answers.*.required' => 'Semua pertanyaan wajib dijawab.',
        ]);

        $answers = $data['answers'];
        $scores = [];

        foreach ($questions->groupBy('mind_bug_id') as $bugId => $items) {
            $sum = $items->sum(fn ($q) => (int) $answers[$q->id]);
            $n = $items->count();
            $scores[$bugId] = (int) round(($sum - $n) / (4 * $n) * 100);
        }

        arsort($scores);
        $dominantId = (int) array_key_first($scores);
        $percentage = (int) round(array_sum($scores) / max(1, count($scores)));
        $peak = $scores[$dominantId] ?? 0;

        $level = match (true) {
            $peak >= 70 || $percentage >= 60 => 'berat',
            $peak >= 45 || $percentage >= 35 => 'sedang',
            default => 'ringan',
        };

        // Unresolved past wounds (B5) at a high score belong face to face.
        $traumaId = MindBug::cachedAll()->firstWhere('code', 'B5')?->id;
        $recommendation = match (true) {
            $level === 'berat', $traumaId && ($scores[$traumaId] ?? 0) >= 60 => 'tatap_muka',
            $level === 'sedang' => 'online_live',
            default => 'web_app',
        };

        $assessment = BugAssessment::create([
            'user_id' => $request->user()->getKey(),
            'dominant_bug_id' => $dominantId ?: null,
            'total_score' => array_sum(array_map('intval', $answers)),
            'percentage' => $percentage,
            'level' => $level,
            'recommendation' => $recommendation,
            'scores' => $scores,
        ]);

        return redirect()->route('mindos.bug-detector.show', $assessment)
            ->with('success', 'Cek Kesehatan Pikiran selesai. Berikut gambaran kesehatan pikiran Anda.');
    }

    public function show(Request $request, BugAssessment $assessment): View
    {
        $this->authorizeOwner($request, $assessment->user_id);

        return view('mindos.bug-detector.show', [
            'assessment' => $assessment->load('dominantBug'),
            'bugs' => MindBug::cachedAll()->keyBy('id'),
        ]);
    }

    private function authorizeOwner(Request $request, string $ownerId): void
    {
        abort_unless($ownerId === $request->user()->getKey() || $request->user()->can('mindos.manage'), 403);
    }
}
