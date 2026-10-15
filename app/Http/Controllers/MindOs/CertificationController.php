<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\CertificationEnrollment;
use App\Models\MindOs\CertificationProgram;
use App\Models\MindOs\FoundationModule;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Sertifikasi & Lisensi: Alumni Foundation → Practitioner → Master → Trainer.
 * Each level requires the previous one to be certified.
 */
class CertificationController extends Controller
{
    private const LADDER = ['practitioner' => null, 'master' => 'practitioner', 'trainer' => 'master'];

    public function index(Request $request): View
    {
        $enrollments = CertificationEnrollment::with('program:id,level,name,hours,practice_clients')
            ->where('user_id', $request->user()->getKey())
            ->get()
            ->keyBy('certification_program_id');

        return view('mindos.certification.index', [
            'programs' => CertificationProgram::orderBy('sort_order')->get(),
            'enrollments' => $enrollments,
            'certifiedLevels' => $enrollments->where('status', 'certified')->map(fn ($e) => $e->program->level)->values()->all(),
            'isAlumni' => $this->isFoundationAlumni($request),
        ]);
    }

    public function apply(Request $request, CertificationProgram $program): RedirectResponse
    {
        $user = $request->user();
        $request->validate(['motivation' => ['required', 'string', 'min:20', 'max:1000']]);

        $required = self::LADDER[$program->level] ?? null;

        if ($required === null && ! $this->isFoundationAlumni($request)) {
            return back()->with('warning', 'Selesaikan seluruh Modul Foundation terlebih dahulu untuk menjadi Alumni Foundation.');
        }

        if ($required !== null) {
            $hasPrereq = CertificationEnrollment::where('user_id', $user->getKey())
                ->where('status', 'certified')
                ->whereHas('program', fn ($q) => $q->where('level', $required))
                ->exists();

            if (! $hasPrereq) {
                return back()->with('warning', 'Program ini mensyaratkan sertifikasi ' . CertificationProgram::LEVELS[$required] . '.');
            }
        }

        CertificationEnrollment::firstOrCreate(
            ['user_id' => $user->getKey(), 'certification_program_id' => $program->id],
            ['motivation' => $request->input('motivation'), 'status' => 'applied']
        );

        return back()->with('success', 'Pendaftaran ' . $program->name . ' terkirim. Tim akademik akan menghubungi Anda.');
    }

    private function isFoundationAlumni(Request $request): bool
    {
        $total = FoundationModule::where('is_published', true)->count();

        return $total > 0 && DB::table('module_progress')
            ->join('foundation_modules', 'foundation_modules.id', '=', 'module_progress.foundation_module_id')
            ->where('foundation_modules.is_published', true)
            ->where('module_progress.user_id', $request->user()->getKey())
            ->count() >= $total;
    }
}
