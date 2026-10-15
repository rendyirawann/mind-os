@extends('backend.layout.app')

@section('title', 'Kelola Booking')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'Manajemen',
        'title' => 'Kelola Booking',
        'subtitle' => 'Konfirmasi jadwal, tugaskan Practitioner, dan catat pembayaran.',
    ])

    <div class="d-flex flex-wrap gap-2 mb-5">
        <a href="{{ route('mindos.admin.bookings.index') }}" class="btn btn-sm {{ ! $status ? 'btn-primary' : 'btn-light' }}">Semua ({{ $totals->sum() }})</a>
        @foreach (\App\Models\MindOs\Booking::STATUSES as $key => [$label, $color])
            <a href="{{ route('mindos.admin.bookings.index', ['status' => $key]) }}" class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }} ({{ (int) ($totals[$key] ?? 0) }})</a>
        @endforeach
        <form method="GET" class="ms-auto d-flex gap-2">
            <input type="hidden" name="status" value="{{ $status }}" />
            <input name="q" value="{{ $search }}" class="form-control form-control-sm" placeholder="Kode / nama / email" aria-label="Cari" />
            <button class="btn btn-sm btn-light-primary">Cari</button>
        </form>
    </div>

    @forelse ($bookings as $b)
        <form method="POST" action="{{ route('mindos.admin.bookings.update', $b) }}" class="mos-card p-5 mb-4">
            @csrf @method('PUT')
            <div class="row g-4 align-items-end">
                <div class="col-xl-3">
                    <div class="d-flex gap-2 mb-1"><span class="fw-bold">{{ $b->code }}</span><span class="badge badge-light-{{ $b->status_color }}">{{ $b->status_label }}</span></div>
                    <div class="fw-semibold">{{ $b->user->name ?? '-' }}</div>
                    <div class="fs-8 text-muted">{{ $b->user->email ?? '' }}</div>
                    <div class="fs-7 mt-1">{{ $b->service->name }} · {{ $b->service->modality_label }}</div>
                    <div class="fs-8 text-muted">Rp {{ number_format($b->amount, 0, ',', '.') }}</div>
                </div>
                <div class="col-sm-6 col-xl-2">
                    <label class="form-label fs-8">Jadwal</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm" value="{{ $b->scheduled_at->format('Y-m-d\TH:i') }}" required />
                </div>
                <div class="col-sm-6 col-xl-2">
                    <label class="form-label fs-8">Practitioner</label>
                    <select name="practitioner_id" class="form-select form-select-sm">
                        <option value="">—</option>
                        @foreach ($practitioners as $p)
                            <option value="{{ $p->id }}" @selected($b->practitioner_id === $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 col-xl-1">
                    <label class="form-label fs-8">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        @foreach (\App\Models\MindOs\Booking::STATUSES as $k => [$label])
                            <option value="{{ $k }}" @selected($b->status === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 col-xl-1">
                    <label class="form-label fs-8">Bayar</label>
                    <select name="payment_status" class="form-select form-select-sm">
                        @foreach (['unpaid' => 'Belum', 'paid' => 'Lunas', 'free' => 'Gratis'] as $k => $label)
                            <option value="{{ $k }}" @selected($b->payment_status === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 col-xl-2">
                    <label class="form-label fs-8">{{ $b->service->modality === 'online' ? 'Link meeting' : 'Lokasi' }}</label>
                    @if ($b->service->modality === 'online')
                        <input type="url" name="meeting_link" class="form-control form-control-sm" value="{{ $b->meeting_link }}" />
                    @else
                        <input name="location" class="form-control form-control-sm" value="{{ $b->location }}" />
                    @endif
                </div>
                <div class="col-xl-1 text-end"><button class="btn btn-sm btn-primary w-100">Simpan</button></div>
                <div class="col-12 fs-7 text-gray-700"><strong>Keluhan:</strong> {{ $b->complaint }}</div>
            </div>
        </form>
    @empty
        <p class="text-muted">Tidak ada booking.</p>
    @endforelse
    {{ $bookings->links() }}
@endsection
