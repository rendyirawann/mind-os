@extends('backend.layout.app')

@section('title', 'Evaluasi 30 Hari')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'J · Evaluasi',
        'title' => 'Evaluasi 30 Hari',
        'subtitle' => 'Ukur perubahan Anda, lalu pilih langkah berikutnya: Self-Mastery, konsultasi ulang, sertifikasi, atau komunitas.',
    ])

    <div class="mb-5"><a href="{{ route('mindos.evaluation.create') }}" class="btn btn-primary btn-sm">Isi Evaluasi Baru</a></div>

    <div class="row g-4">
        @forelse ($evaluations as $ev)
            <div class="col-md-6 col-xxl-4">
                <div class="mos-card p-5 h-100">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fs-8 text-muted">{{ $ev->created_at->translatedFormat('d M Y') }}</span>
                        <span class="badge badge-light-{{ $ev->result === 'berhasil' ? 'success' : 'warning' }}">{{ $ev->result === 'berhasil' ? 'Ada kemajuan' : 'Belum berubah' }}</span>
                    </div>
                    <div class="fs-2x fw-bolder text-gray-900 mb-1">{{ $ev->score_before }} → {{ $ev->score_after }}</div>
                    <div class="fs-8 text-muted mb-3">Beban masalah (0–10){{ $ev->plan ? ' · ' . $ev->plan->title : '' }}</div>
                    <p class="text-gray-700 fs-7">{{ Str::limit($ev->reflection, 160) }}</p>
                    <span class="mos-chip">{{ $ev->next_step_label }}</span>
                </div>
            </div>
        @empty
            <div class="col-12"><p class="text-muted">Belum ada evaluasi.</p></div>
        @endforelse
    </div>
    <div class="mt-6">{{ $evaluations->links() }}</div>
@endsection
