<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\Evaluation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Evaluasi 30 Hari → Hasil? → Lanjut Self-Mastery / Alumni,
 * Konsultasi Ulang, Minat Sertifikasi, atau Tetap di Komunitas.
 */
class EvaluationController extends Controller
{
    public function index(Request $request): View
    {
        return view('mindos.evaluation.index', [
            'evaluations' => Evaluation::with('plan:id,title')
                ->where('user_id', $request->user()->getKey())
                ->latest()
                ->paginate(10),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('mindos.evaluation.create', [
            'plans' => $user->actionPlans()->latest()->limit(5)->get(['id', 'title', 'status']),
            'firstAssessment' => $user->bugAssessments()->oldest()->value('percentage'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'action_plan_id' => ['nullable', Rule::exists('action_plans', 'id')->where('user_id', $user->getKey())],
            'score_before' => ['required', 'integer', 'between:0,10'],
            'score_after' => ['required', 'integer', 'between:0,10'],
            'next_step' => ['required', Rule::in(array_keys(Evaluation::NEXT_STEPS))],
            'reflection' => ['required', 'string', 'max:2000'],
        ]);

        $data['result'] = $data['score_after'] < $data['score_before'] ? 'berhasil' : 'belum';
        $user->evaluations()->create($data);

        return match ($data['next_step']) {
            'konsultasi' => redirect()->route('mindos.services.index')->with('info', 'Evaluasi tersimpan. Pilih sesi konsultasi ulang yang sesuai.'),
            'sertifikasi' => redirect()->route('mindos.certification.index')->with('success', 'Evaluasi tersimpan. Lihat jalur sertifikasi Mind OS.'),
            'komunitas' => redirect()->route('mindos.community.index')->with('success', 'Evaluasi tersimpan. Bagikan perjalanan Anda di Komunitas.'),
            default => redirect()->route('mindos.journey')->with('success', 'Evaluasi tersimpan. Selamat menjadi Alumni Mind OS!'),
        };
    }
}
