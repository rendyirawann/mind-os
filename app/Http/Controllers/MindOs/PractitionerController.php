<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Sesi Klien — the practitioner's own schedule of assigned bookings. */
class PractitionerController extends Controller
{
    public function index(Request $request): View
    {
        $uid = $request->user()->getKey();
        $tab = $request->query('tab') === 'riwayat' ? 'riwayat' : 'mendatang';

        $bookings = Booking::with(['service:id,name,modality,duration_minutes', 'user:id,name,email,no_wa'])
            ->where('practitioner_id', $uid)
            ->when($tab === 'mendatang',
                fn ($q) => $q->whereIn('status', ['pending', 'confirmed'])->orderBy('scheduled_at'),
                fn ($q) => $q->whereIn('status', ['completed', 'cancelled'])->latest('scheduled_at'))
            ->paginate(15)
            ->withQueryString();

        return view('mindos.practitioner.index', [
            'bookings' => $bookings,
            'tab' => $tab,
            'stats' => Booking::where('practitioner_id', $uid)
                ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
                ->selectRaw("SUM(CASE WHEN status IN ('pending','confirmed') THEN 1 ELSE 0 END) as upcoming")
                ->selectRaw('COUNT(DISTINCT user_id) as clients')
                ->first(),
        ]);
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->practitioner_id === $request->user()->getKey(), 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'completed', 'cancelled'])],
            'meeting_link' => ['nullable', 'url', 'max:255'],
            'practitioner_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $booking->update($data);

        return back()->with('success', 'Sesi ' . $booking->code . ' diperbarui.');
    }
}
