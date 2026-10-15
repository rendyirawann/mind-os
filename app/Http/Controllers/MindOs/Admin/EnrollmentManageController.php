<?php

namespace App\Http\Controllers\MindOs\Admin;

use App\Http\Controllers\Controller;
use App\Models\MindOs\CertificationEnrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EnrollmentManageController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        return view('mindos.admin.enrollments', [
            'enrollments' => CertificationEnrollment::with(['program:id,name,level,hours,practice_clients', 'user:id,name,email'])
                ->when(array_key_exists((string) $status, CertificationEnrollment::STATUSES), fn ($q) => $q->where('status', $status))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'status' => $status,
        ]);
    }

    public function update(Request $request, CertificationEnrollment $enrollment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(CertificationEnrollment::STATUSES))],
            'hours_done' => ['required', 'integer', 'min:0', 'max:500'],
            'clients_done' => ['required', 'integer', 'min:0', 'max:500'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $enrollment->load('program', 'user');

        if ($data['status'] === 'certified' && ! $enrollment->certificate_number) {
            $data['certificate_number'] = 'MOS-' . Str::upper(Str::substr($enrollment->program->level, 0, 3))
                . '-' . now()->format('Y') . '-' . Str::upper(Str::random(6));
            $data['certified_at'] = now();

            // A certified practitioner/master can take paid clients; a trainer can teach.
            $role = $enrollment->program->level === 'trainer' ? 'Trainer' : 'Practitioner';
            if (! $enrollment->user->hasRole($role)) {
                $enrollment->user->assignRole($role);
            }
        }

        $enrollment->update($data);

        return back()->with('success', 'Status sertifikasi ' . $enrollment->user->name . ' diperbarui.');
    }
}
