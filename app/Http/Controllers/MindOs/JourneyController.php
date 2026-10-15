<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\ActionPlan;
use App\Models\MindOs\BugAssessment;
use App\Models\MindOs\Booking;
use App\Models\MindOs\CertificationEnrollment;
use App\Models\MindOs\Evaluation;
use App\Models\MindOs\FoundationModule;
use App\Models\MindOs\Journal;
use App\Models\MindOs\SixRLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * "Perjalanan Saya" — the member home that walks a client through the
 * Mind OS user journey (A → P) based on what they have actually done.
 */
class JourneyController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('mindos.journey.index', self::summary($user) + ['user' => $user]);
    }

    /**
     * Everything the journey page and the dashboard need, in a handful of
     * indexed queries. Shared with DashboardAdminController.
     *
     * @return array<string,mixed>
     */
    public static function summary(User $user): array
    {
        $uid = $user->getKey();

        $moduleTotal = Cache::remember('mindos.modules.count', 600, fn () => FoundationModule::where('is_published', true)->count());
        $moduleDone = DB::table('module_progress')->where('user_id', $uid)->count();

        $assessment = BugAssessment::with('dominantBug')->where('user_id', $uid)->latest()->first();
        $plan = ActionPlan::withCount(['days', 'days as days_done' => fn ($q) => $q->whereNotNull('done_at')])
            ->where('user_id', $uid)->where('status', 'active')->latest()->first();
        $evaluation = Evaluation::where('user_id', $uid)->latest()->first();
        $nextBooking = Booking::with('service:id,name,modality')
            ->where('user_id', $uid)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_at', '>=', now()->subHours(3))
            ->orderBy('scheduled_at')
            ->first();
        $enrollment = CertificationEnrollment::with('program:id,name,level')->where('user_id', $uid)->latest()->first();

        // One grouped query instead of seven counts.
        $stageCounts = SixRLog::where('user_id', $uid)
            ->select('stage', DB::raw('COUNT(*) as total'))
            ->groupBy('stage')
            ->pluck('total', 'stage');

        $counts = [
            'journals' => Journal::where('user_id', $uid)->count(),
            'six_r' => (int) $stageCounts->sum(),
            'bookings' => Booking::where('user_id', $uid)->count(),
            'audio' => DB::table('audio_listens')->where('user_id', $uid)->count(),
        ];

        $foundationPct = $moduleTotal > 0 ? (int) round($moduleDone / $moduleTotal * 100) : 0;

        $steps = [
            ['A', 'Daftar sebagai User Baru', true, route('account.index')],
            ['B', 'Akses Web App', true, route('mindos.journey')],
            ['C', 'Ikuti Modul Foundation', $foundationPct >= 100, route('mindos.foundation.index')],
            ['D', 'Isi Cek Kesehatan Pikiran', (bool) $assessment, route('mindos.bug-detector.index')],
            ['E', 'Lihat Hasil & Rekomendasi', (bool) $assessment, $assessment ? route('mindos.bug-detector.show', $assessment) : route('mindos.bug-detector.index')],
            ['F/G/H', 'Lanjut Web App / Booking Online Live / Tatap Muka', $counts['six_r'] > 0 || $counts['bookings'] > 0, route('mindos.services.index')],
            ['I', 'Action Plan 30 Hari & Komunitas', (bool) $plan || ActionPlan::where('user_id', $uid)->exists(), route('mindos.action-plan.index')],
            ['J', 'Evaluasi 30 Hari', (bool) $evaluation, route('mindos.evaluation.index')],
            ['K–P', 'Self-Mastery, Konsultasi Ulang, atau Sertifikasi', (bool) $enrollment || ($evaluation && $evaluation->next_step === 'self_mastery'), route('mindos.certification.index')],
        ];

        $doneSteps = collect($steps)->where(2, true)->count();
        $next = collect($steps)->firstWhere(2, false);

        return [
            'moduleTotal' => $moduleTotal,
            'moduleDone' => $moduleDone,
            'foundationPct' => $foundationPct,
            'assessment' => $assessment,
            'plan' => $plan,
            'evaluation' => $evaluation,
            'nextBooking' => $nextBooking,
            'enrollment' => $enrollment,
            'stageCounts' => $stageCounts,
            'counts' => $counts,
            'steps' => $steps,
            'journeyPct' => (int) round($doneSteps / count($steps) * 100),
            'nextStep' => $next,
        ];
    }
}
