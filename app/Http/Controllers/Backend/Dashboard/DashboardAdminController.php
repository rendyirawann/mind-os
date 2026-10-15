<?php

namespace App\Http\Controllers\Backend\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityDiff;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class DashboardAdminController extends Controller
{
    /** How many days the activity trend chart covers. */
    private const TREND_DAYS = 14;

    public function index(): View
    {
        $user = auth()->user();
        $isSuperadmin = $user->hasRole(['Superadmin', 'superadmin']);

        return view('backend.dashboard.index', [
            'isSuperadmin' => $isSuperadmin,
            'stats' => $this->stats($isSuperadmin, $user),
            'trend' => $this->trend($isSuperadmin, $user),
            'breakdown' => $this->breakdown($isSuperadmin, $user),
            'recent' => $this->recent($isSuperadmin, $user),
            'topUsers' => $isSuperadmin ? $this->topUsers() : collect(),
            'mindos' => $user->can('mindos.manage') ? $this->mindOsStats() : null,
        ]);
    }

    /** Base activity query, narrowed to the current user unless Superadmin. */
    private function activityQuery(bool $isSuperadmin, User $user)
    {
        $query = Activity::query();

        if (! $isSuperadmin) {
            $query->where('causer_id', $user->getKey());
        }

        return $query;
    }

    /** @return array<string,int|string> */
    private function stats(bool $isSuperadmin, User $user): array
    {
        $today = (clone $this->activityQuery($isSuperadmin, $user))
            // Range instead of whereDate() so the created_at index is usable.
            ->where('created_at', '>=', today())
            ->count();

        $week = (clone $this->activityQuery($isSuperadmin, $user))
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        return [
            'users' => $isSuperadmin ? User::count() : null,
            'active_users' => $isSuperadmin ? User::where('is_active', true)->count() : null,
            'roles' => $isSuperadmin ? Role::count() : null,
            'online' => $isSuperadmin ? $this->onlineSessions() : null,
            'activity_today' => $today,
            'activity_week' => $week,
            'last_login' => optional($user->last_login)->diffForHumans() ?? 'Pertama kali',
            'last_ip' => $user->last_ip ?: '-',
        ];
    }

    /**
     * Sessions touched in the last five minutes.
     *
     * Only meaningful with the database session driver; returns null for any
     * other driver so the card can be hidden instead of showing a wrong zero.
     */
    private function onlineSessions(): ?int
    {
        if (config('session.driver') !== 'database' || ! Schema::hasTable('sessions')) {
            return null;
        }

        return DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * Daily activity counts for the trend chart.
     *
     * Rows are grouped in PHP rather than SQL so the query stays portable
     * across MySQL/PostgreSQL date functions.
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    private function trend(bool $isSuperadmin, User $user): array
    {
        $from = today()->subDays(self::TREND_DAYS - 1);

        // Aggregate in SQL: DATE() works on MySQL, PostgreSQL and SQLite, so
        // only ~14 rows travel to PHP instead of every activity row.
        $counts = (clone $this->activityQuery($isSuperadmin, $user))
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'day')
            ->mapWithKeys(fn ($total, $day) => [substr((string) $day, 0, 10) => (int) $total]);

        $labels = [];
        $data = [];

        foreach (CarbonPeriod::create($from, today()) as $day) {
            $labels[] = $day->translatedFormat('d M');
            $data[] = (int) ($counts[$day->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Share of create / update / delete / auth events over the trend window.
     *
     * @return array<string,array{label:string,count:int,color:string,icon:string}>
     */
    private function breakdown(bool $isSuperadmin, User $user): array
    {
        $rows = (clone $this->activityQuery($isSuperadmin, $user))
            ->where('created_at', '>=', today()->subDays(self::TREND_DAYS - 1))
            ->get(['id', 'log_name', 'description', 'properties']);

        $buckets = [
            'create' => ['label' => 'Tambah data', 'count' => 0, 'color' => 'primary', 'icon' => 'ki-plus-square'],
            'update' => ['label' => 'Ubah data', 'count' => 0, 'color' => 'warning', 'icon' => 'ki-pencil'],
            'delete' => ['label' => 'Hapus data', 'count' => 0, 'color' => 'danger', 'icon' => 'ki-trash'],
            'auth' => ['label' => 'Login & logout', 'count' => 0, 'color' => 'success', 'icon' => 'ki-entrance-right'],
        ];

        foreach ($rows as $row) {
            $buckets[$this->bucketFor($row)]['count']++;
        }

        return $buckets;
    }

    /** Map an activity onto one of the breakdown buckets. */
    private function bucketFor(Activity $activity): string
    {
        return match (ActivityDiff::type($activity)) {
            'login', 'logout' => 'auth',
            'delete' => 'delete',
            'update' => 'update',
            default => 'create',
        };
    }

    private function recent(bool $isSuperadmin, User $user)
    {
        return (clone $this->activityQuery($isSuperadmin, $user))
            ->with('causer')
            ->latest()
            ->limit(8)
            ->get();
    }

    /**
     * Mind OS business numbers, cached briefly — they are aggregate counts
     * over the whole platform and do not need to be real-time.
     *
     * @return array<string,mixed>
     */
    private function mindOsStats(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('mindos.dashboard', 300, function () {
            $bookings = DB::table('bookings')
                ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
                ->selectRaw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed")
                ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
                ->selectRaw("SUM(CASE WHEN payment_status = 'paid' THEN amount ELSE 0 END) as revenue")
                ->first();

            $bugDistribution = DB::table('bug_assessments')
                ->join('mind_bugs', 'mind_bugs.id', '=', 'bug_assessments.dominant_bug_id')
                ->select('mind_bugs.name', DB::raw('COUNT(*) as total'))
                ->groupBy('mind_bugs.name')
                ->orderByDesc('total')
                ->pluck('total', 'name');

            return [
                'clients' => User::role('Klien')->count(),
                'assessments' => DB::table('bug_assessments')->count(),
                'journals' => DB::table('journals')->count(),
                'active_plans' => DB::table('action_plans')->where('status', 'active')->count(),
                'enrollments_open' => DB::table('certification_enrollments')->whereIn('status', ['applied', 'learning', 'exam'])->count(),
                'certified' => DB::table('certification_enrollments')->where('status', 'certified')->count(),
                'bookings' => $bookings,
                'bug_distribution' => $bugDistribution,
                'recommendations' => DB::table('bug_assessments')->select('recommendation', DB::raw('COUNT(*) as total'))
                    ->groupBy('recommendation')->pluck('total', 'recommendation'),
            ];
        });
    }

    /** Most active users over the trend window. */
    private function topUsers()
    {
        $counts = Activity::query()
            ->where('created_at', '>=', today()->subDays(self::TREND_DAYS - 1))
            ->whereNotNull('causer_id')
            ->select('causer_id', DB::raw('COUNT(*) as total'))
            ->groupBy('causer_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $users = User::whereIn('id', $counts->pluck('causer_id'))->get()->keyBy('id');

        return $counts
            ->map(fn ($row) => [
                'user' => $users->get($row->causer_id),
                'total' => (int) $row->total,
            ])
            ->filter(fn ($row) => $row['user'] !== null)
            ->values();
    }
}
