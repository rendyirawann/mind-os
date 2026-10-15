<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\Booking;
use App\Models\MindOs\MindService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Layanan Online Live & Tatap Muka, plus the client's own bookings. */
class BookingController extends Controller
{
    public function services(Request $request): View
    {
        return view('mindos.booking.services', [
            'services' => MindService::active()->get()->groupBy('modality'),
            'recommendation' => $request->user()->bugAssessments()->latest()->value('recommendation'),
        ]);
    }

    public function index(Request $request): View
    {
        return view('mindos.booking.index', [
            'bookings' => Booking::with(['service:id,name,modality,duration_minutes', 'practitioner:id,name'])
                ->where('user_id', $request->user()->getKey())
                ->latest('scheduled_at')
                ->paginate(10),
        ]);
    }

    public function create(MindService $service): View
    {
        abort_unless($service->is_active, 404);

        return view('mindos.booking.create', ['service' => $service]);
    }

    public function store(Request $request, MindService $service): RedirectResponse
    {
        abort_unless($service->is_active, 404);

        $data = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:' . now()->addHours(12)->toDateTimeString()],
            'complaint' => ['required', 'string', 'max:1000'],
        ], [
            'scheduled_at.after' => 'Jadwal minimal 12 jam dari sekarang.',
        ]);

        // Group sessions share a slot; private ones must not overlap the
        // same client's other active booking at the same time.
        $clash = Booking::where('user_id', $request->user()->getKey())
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('scheduled_at', [
                now()->parse($data['scheduled_at'])->subMinutes($service->duration_minutes - 1),
                now()->parse($data['scheduled_at'])->addMinutes($service->duration_minutes - 1),
            ])
            ->exists();

        if ($clash) {
            return back()->withInput()->withErrors(['scheduled_at' => 'Anda sudah punya sesi lain di jam tersebut.']);
        }

        $booking = Booking::create([
            'user_id' => $request->user()->getKey(),
            'mind_service_id' => $service->id,
            'scheduled_at' => $data['scheduled_at'],
            'complaint' => $data['complaint'],
            'amount' => $service->price,
            'payment_status' => $service->price > 0 ? 'unpaid' : 'free',
            'status' => 'pending',
            'location' => $service->modality === 'offline' ? 'Studio Mind OS (alamat dikirim setelah konfirmasi)' : null,
        ]);

        return redirect()->route('mindos.bookings.index')
            ->with('success', 'Booking ' . $booking->code . ' terkirim. Tim kami akan mengonfirmasi jadwal Anda.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->getKey(), 404);

        if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
            return back()->with('warning', 'Booking ini sudah tidak bisa dibatalkan.');
        }

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking ' . $booking->code . ' dibatalkan.');
    }
}
