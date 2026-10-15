@extends('backend.layout.app')

@section('title', 'Audio Guided Healing')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'Web App · R1 R2 R6',
        'title' => 'Audio Guided Healing',
        'subtitle' => 'Self-hypnosis terpandu untuk menyadari, menelusuri, dan menguatkan program baru. Gunakan headphone di tempat yang aman.',
    ])

    @forelse ($tracks as $stage => $items)
        @php $info = \App\Support\SixR::stage($stage); @endphp
        <h2 class="fs-4 fw-bold mb-4 mt-2 d-flex align-items-center gap-3">
            <span class="mos-r mos-r--{{ $info['modality'] }}">{{ $stage }}</span> {{ $info['name'] }}
        </h2>
        <div class="row g-4 mb-8">
            @foreach ($items as $track)
                <div class="col-md-6 col-xxl-4">
                    <a href="{{ route('mindos.audio.show', $track) }}" class="mos-card d-flex gap-4 h-100 p-5 text-decoration-none">
                        <span class="d-grid place-items-center rounded-circle bg-light-primary flex-shrink-0" style="width:52px;height:52px;display:grid;place-items:center">
                            <i class="ki-solid ki-to-right fs-1 text-primary"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="fs-5 fw-bold text-gray-900 d-block mb-1">{{ $track->title }}</span>
                            <span class="text-gray-600 fs-7 d-block mb-2">{{ $track->description }}</span>
                            <span class="fs-8 text-muted">{{ $track->duration_label }} · diputar {{ (int) ($listens[$track->id] ?? 0) }}×</span>
                        </span>
                    </a>
                </div>
            @endforeach
        </div>
    @empty
        <p class="text-muted">Belum ada audio yang diterbitkan.</p>
    @endforelse
@endsection
