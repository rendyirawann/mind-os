@extends('backend.layout.app')

@section('title', 'Kelola Sertifikasi')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'Manajemen',
        'title' => 'Kelola Sertifikasi',
        'subtitle' => 'Pantau jam belajar, praktik klien, dan terbitkan sertifikat. Status "Tersertifikasi" otomatis memberi role Practitioner/Trainer.',
    ])

    <div class="d-flex flex-wrap gap-2 mb-5">
        <a href="{{ route('mindos.admin.enrollments.index') }}" class="btn btn-sm {{ ! $status ? 'btn-primary' : 'btn-light' }}">Semua</a>
        @foreach (\App\Models\MindOs\CertificationEnrollment::STATUSES as $key => [$label])
            <a href="{{ route('mindos.admin.enrollments.index', ['status' => $key]) }}" class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
        @endforeach
    </div>

    @forelse ($enrollments as $e)
        <form method="POST" action="{{ route('mindos.admin.enrollments.update', $e) }}" class="mos-card p-5 mb-4">
            @csrf @method('PUT')
            <div class="row g-4 align-items-end">
                <div class="col-xl-4">
                    <div class="d-flex gap-2 mb-1"><span class="fw-bold">{{ $e->user->name ?? '-' }}</span><span class="badge badge-light-{{ $e->status_color }}">{{ $e->status_label }}</span></div>
                    <div class="fs-8 text-muted">{{ $e->user->email ?? '' }} · daftar {{ $e->created_at->translatedFormat('d M Y') }}</div>
                    <div class="fs-7 fw-semibold mt-1">{{ $e->program->name }}</div>
                    <div class="fs-8 text-gray-700 mt-1">“{{ Str::limit($e->motivation, 140) }}”</div>
                    @if ($e->certificate_number)
                        <div class="fs-8 text-success fw-bold mt-1">{{ $e->certificate_number }}</div>
                    @endif
                </div>
                <div class="col-sm-4 col-xl-2">
                    <label class="form-label fs-8">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        @foreach (\App\Models\MindOs\CertificationEnrollment::STATUSES as $k => [$label])
                            <option value="{{ $k }}" @selected($e->status === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 col-xl-1">
                    <label class="form-label fs-8">Jam /{{ $e->program->hours }}</label>
                    <input type="number" min="0" name="hours_done" class="form-control form-control-sm" value="{{ $e->hours_done }}" />
                </div>
                <div class="col-sm-4 col-xl-1">
                    <label class="form-label fs-8">Klien /{{ $e->program->practice_clients }}</label>
                    <input type="number" min="0" name="clients_done" class="form-control form-control-sm" value="{{ $e->clients_done }}" />
                </div>
                <div class="col-sm-9 col-xl-3">
                    <label class="form-label fs-8">Catatan</label>
                    <input name="admin_notes" class="form-control form-control-sm" value="{{ $e->admin_notes }}" />
                </div>
                <div class="col-sm-3 col-xl-1"><button class="btn btn-sm btn-primary w-100">Simpan</button></div>
            </div>
        </form>
    @empty
        <p class="text-muted">Belum ada pendaftar.</p>
    @endforelse
    {{ $enrollments->links() }}
@endsection
