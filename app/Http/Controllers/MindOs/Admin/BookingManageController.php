<?php

namespace App\Http\Controllers\MindOs\Admin;

use App\Http\Controllers\Controller;
use App\Models\MindOs\Booking;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingManageController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('q'));

        $bookings = Booking::with(['service:id,name,modality', 'user:id,name,email', 'practitioner:id,name'])
            ->when(array_key_exists((string) $status, Booking::STATUSES), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('code', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))
            ->latest('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        return view('mindos.admin.bookings', [
            'bookings' => $bookings,
            'status' => $status,
            'search' => $search,
            'practitioners' => $this->practitioners(),
            'totals' => Booking::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $practitionerIds = $this->practitioners()->pluck('id')->all();

        $data = $request->validate([
            'practitioner_id' => ['nullable', Rule::in($practitionerIds)],
            'status' => ['required', Rule::in(array_keys(Booking::STATUSES))],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid', 'free'])],
            'scheduled_at' => ['required', 'date'],
            'meeting_link' => ['nullable', 'url', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $booking->update($data);

        return back()->with('success', 'Booking ' . $booking->code . ' diperbarui.');
    }

    private function practitioners()
    {
        return User::role(['Practitioner', 'Trainer'])->orderBy('name')->get(['id', 'name']);
    }
}
