@extends('backend.layout.app')

@section('title', $journal->title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <a href="{{ route('mindos.journal.index') }}" class="fs-7 fw-semibold d-inline-block mb-4">← Semua jurnal</a>
            <article class="card card-flush shadow-sm">
                <div class="card-body p-6 p-lg-10">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="mos-chip">{{ $journal->created_at->translatedFormat('l, d F Y · H:i') }}</span>
                        @if ($journal->bug)
                            <span class="badge badge-light-{{ $journal->bug->color }}">{{ $journal->bug->name }}</span>
                        @endif
                        <span class="badge badge-light-warning">Intensitas {{ $journal->intensity }}/10</span>
                    </div>
                    <h1 class="fs-2x fw-bolder mb-8">{{ $journal->title }}</h1>

                    @foreach ([['Situasi', $journal->situation], ['Pikiran otomatis', $journal->thought], ['Perasaan', $journal->feeling], ['Respons tubuh', $journal->body_response], ['Insight', $journal->insight]] as [$label, $value])
                        @continue(blank($value))
                        <h2 class="fs-7 text-uppercase text-muted fw-bold mb-2">{{ $label }}</h2>
                        <p class="mos-prose mb-6">{{ $value }}</p>
                    @endforeach

                    @if ($journal->bug)
                        <div class="alert alert-primary mb-6"><strong>Kunci reframe:</strong> {{ $journal->bug->reframe_hint }}</div>
                    @endif

                    <form method="POST" action="{{ route('mindos.journal.destroy', $journal) }}" onsubmit="return confirm('Hapus jurnal ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-light-danger">Hapus jurnal</button>
                    </form>
                </div>
            </article>
        </div>
    </div>
@endsection
