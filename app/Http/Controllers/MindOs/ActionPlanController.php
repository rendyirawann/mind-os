<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\ActionPlan;
use App\Models\MindOs\ActionPlanDay;
use App\Models\MindOs\MindBug;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Action Plan 30 Hari — one small daily task that reinforces the new program. */
class ActionPlanController extends Controller
{
    /** A weekly rhythm repeated over 30 days (Recognize → Reinforce). */
    private const TEMPLATE = [
        'Recognize: tulis 1 Jurnal Recognize saat pola lama muncul hari ini.',
        'Napas 4-7-8 tiga kali, pagi dan malam.',
        'Dengarkan 1 audio Reinforce sebelum tidur.',
        'Tulis 3 bukti kecil bahwa keyakinan lama tidak sepenuhnya benar.',
        'Lakukan 1 tindakan kecil yang biasanya Anda hindari karena takut.',
        'Bagikan 1 insight atau kemenangan kecil di Komunitas.',
        'Refleksi mingguan: apa yang berubah? catat intensitas emosi (0-10).',
    ];

    public function index(Request $request): View
    {
        $uid = $request->user()->getKey();

        $plan = ActionPlan::with(['days', 'bug:id,name'])
            ->where('user_id', $uid)
            ->where('status', 'active')
            ->latest()
            ->first();

        return view('mindos.action-plan.index', [
            'plan' => $plan,
            'history' => ActionPlan::withCount(['days as days_done' => fn ($q) => $q->whereNotNull('done_at')])
                ->where('user_id', $uid)->where('status', '!=', 'active')->latest()->limit(5)->get(),
            'bugs' => MindBug::cachedAll(),
            'suggestedBug' => $request->user()->bugAssessments()->latest()->value('dominant_bug_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'goal' => ['required', 'string', 'max:1000'],
            'mind_bug_id' => ['nullable', Rule::exists('mind_bugs', 'id')],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $data) {
            // Only one active plan at a time keeps the daily view focused.
            $user->actionPlans()->where('status', 'active')->update(['status' => 'stopped']);

            $plan = $user->actionPlans()->create($data + ['status' => 'active']);

            $rows = [];
            for ($day = 1; $day <= ActionPlan::DAYS; $day++) {
                $rows[] = [
                    'action_plan_id' => $plan->id,
                    'day_number' => $day,
                    'task' => self::TEMPLATE[($day - 1) % count(self::TEMPLATE)],
                ];
            }
            ActionPlanDay::insert($rows); // single bulk insert, not 30 queries
        });

        return redirect()->route('mindos.action-plan.index')->with('success', 'Action Plan 30 Hari dimulai. Konsisten adalah kuncinya!');
    }

    public function toggleDay(Request $request, ActionPlanDay $day): RedirectResponse
    {
        $plan = $day->plan;
        abort_unless($plan->user_id === $request->user()->getKey() && $plan->status === 'active', 404);

        if ($day->day_number > $plan->current_day) {
            return back()->with('warning', 'Hari ' . $day->day_number . ' belum tiba. Fokus pada hari ini.');
        }

        $request->validate(['reflection' => ['nullable', 'string', 'max:500']]);

        $day->update([
            'done_at' => $day->done_at ? null : now(),
            'reflection' => $request->input('reflection', $day->reflection),
        ]);

        if ($plan->days()->whereNull('done_at')->doesntExist()) {
            $plan->update(['status' => 'completed']);

            return redirect()->route('mindos.evaluation.create')->with('success', '30 hari selesai! Saatnya Evaluasi 30 Hari.');
        }

        return back()->with('success', $day->done_at ? 'Hari ' . $day->day_number . ' selesai. Mantap!' : 'Tanda selesai dibatalkan.');
    }

    public function stop(Request $request, ActionPlan $plan): RedirectResponse
    {
        abort_unless($plan->user_id === $request->user()->getKey(), 404);
        $plan->update(['status' => 'stopped']);

        return redirect()->route('mindos.evaluation.create')->with('info', 'Action Plan dihentikan. Yuk evaluasi apa yang sudah Anda capai.');
    }
}
