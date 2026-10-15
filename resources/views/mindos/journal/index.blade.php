@extends('backend.layout.app')

@section('title', 'Jurnal Recognize')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'R1 · Recognize',
        'title' => 'Jurnal Recognize',
        'subtitle' => 'Catat situasi → pikiran → perasaan → respons tubuh. Menyadari adalah langkah pertama untuk pulih.',
    ])

    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-5">
        <form method="GET" class="d-flex gap-2">
            <select name="bug" class="form-select form-select-sm w-auto" aria-label="Filter pola" onchange="this.form.submit()">
                <option value="">Semua pola</option>
                @foreach ($bugs as $bug)
                    <option value="{{ $bug->id }}" @selected(request('bug') == $bug->id)>{{ $bug->name }}</option>
                @endforeach
            </select>
        </form>
        <div class="d-flex align-items-center gap-4">
            <span class="text-muted fs-7">Rata-rata intensitas 30 hari: <strong class="text-gray-900">{{ $avgIntensity ?: '-' }}</strong>/10</span>
            <a href="{{ route('mindos.journal.create') }}" class="btn btn-primary btn-sm"><i class="ki-outline ki-plus fs-4"></i> Tulis Jurnal</a>
        </div>
    </div>

    <div class="row g-4">
        @forelse ($journals as $journal)
            <div class="col-md-6 col-xxl-4">
                <a href="{{ route('mindos.journal.show', $journal) }}" class="mos-card d-flex flex-column h-100 p-5 text-decoration-none">
                    <div class="d-flex justify-content-between gap-2 mb-3">
                        <span class="fs-8 text-muted">{{ $journal->created_at->translatedFormat('d M Y · H:i') }}</span>
                        <span class="badge badge-light-{{ $journal->intensity >= 7 ? 'danger' : ($journal->intensity >= 4 ? 'warning' : 'success') }}">Intensitas {{ $journal->intensity }}/10</span>
                    </div>
                    <h2 class="fs-5 fw-bold text-gray-900 mb-2">{{ $journal->title }}</h2>
                    <p class="text-gray-600 fs-7 mb-3">{{ Str::limit($journal->thought, 120) }}</p>
                    <div class="mt-auto d-flex flex-wrap gap-2">
                        <span class="mos-chip">{{ $journal->feeling }}</span>
                        @if ($journal->bug)
                            <span class="badge badge-light-{{ $journal->bug->color }}">{{ $journal->bug->name }}</span>
                        @endif
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-flush shadow-sm"><div class="card-body text-center py-15">
                    <p class="text-muted mb-4">Belum ada jurnal. Tulis jurnal pertama Anda saat emosi kuat muncul hari ini.</p>
                    <a href="{{ route('mindos.journal.create') }}" class="btn btn-primary">Tulis Jurnal Pertama</a>
                </div></div>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $journals->links() }}</div>
@endsection
