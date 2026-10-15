@extends('backend.layout.app')

@section('title', 'Sesi Klien')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'Practitioner',
        'title' => 'Sesi Klien',
        'subtitle' => 'Kelola sesi Online Live dan Tatap Muka yang ditugaskan kepada Anda.',
    ])

    <div class="row g-4 mb-6">
        @foreach ([['Sesi mendatang', $stats->upcoming ?? 0, 'primary'], ['Sesi selesai', $stats->completed ?? 0, 'success'], ['Total klien', $stats->clients ?? 0, 'warning']] as [$label, $value, $color])
            <div class="col-4"><div class="mos-card p-5"><div class="fs-2x fw-bolder text-{{ $color }}">{{ (int) $value }}</div><div class="fs-7 text-muted">{{ $label }}</div></div></div>
        @endforeach
    </div>

    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
        <li class="nav-item"><a class="nav-link {{ $tab === 'mendatang' ? 'active' : '' }}" href="?tab=mendatang">Mendatang</a></li>
        <li class="nav-item"><a class="nav-link {{ $tab === 'riwayat' ? 'active' : '' }}" href="?tab=riwayat">Riwayat</a></li>
    </ul>

    @forelse ($bookings as $b)
        <div class="mos-card p-5 mb-4">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="d-flex gap-2 mb-2">
                        <span class="badge badge-light-{{ $b->status_color }}">{{ $b->status_label }}</span>
                        <span class="badge badge-light">{{ $b->code }}</span>
                    </div>
                    <div class="fs-5 fw-bold">{{ $b->user->name }}</div>
                    <div class="fs-8 text-muted mb-2">{{ $b->user->email }}{{ $b->user->no_wa ? ' · ' . $b->user->no_wa : '' }}</div>
                    <div class="fs-7"><strong>{{ $b->service->name }}</strong> · {{ $b->service->modality_label }} · {{ $b->scheduled_at->translatedFormat('l, d M Y H:i') }}</div>
                    <p class="fs-7 text-gray-700 mt-3 mb-0"><strong>Keluhan:</strong> {{ $b->complaint }}</p>
                </div>
                <div class="col-lg-7">
                    <form method="POST" action="{{ route('mindos.practitioner.update', $b) }}" class="row g-3">
                        @csrf @method('PUT')
                        <div class="col-sm-5">
                            <select name="status" class="form-select form-select-sm" aria-label="Status">
                                @foreach (['confirmed' => 'Terkonfirmasi', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $k => $v)
                                    <option value="{{ $k }}" @selected($b->status === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-7">
                            <input name="meeting_link" type="url" class="form-control form-control-sm" placeholder="Link meeting (online)" aria-label="Link meeting" value="{{ $b->meeting_link }}" />
                        </div>
                        <div class="col-12">
                            <textarea name="practitioner_notes" rows="2" class="form-control form-control-sm" placeholder="Catatan sesi (privat)" aria-label="Catatan sesi">{{ $b->practitioner_notes }}</textarea>
                        </div>
                        <div class="col-12 text-end"><button class="btn btn-sm btn-primary">Simpan</button></div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">Tidak ada sesi.</p>
    @endforelse
    {{ $bookings->links() }}
@endsection
