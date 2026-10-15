@extends('backend.layout.app')

@section('title', 'Modul Foundation')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'Web App · Gratis',
        'title' => 'Modul Foundation',
        'subtitle' => 'Filosofi Dasar, 5 Lapisan Diri, dan 7 Pola Pikiran — fondasi untuk memahami pikiran, emosi, dan kesehatan mental Anda.',
    ])

    @php $doneCount = $done->count(); $pct = $total ? (int) round($doneCount / $total * 100) : 0; @endphp
    <div class="card card-flush shadow-sm mb-6">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center gap-5">
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between fs-7 mb-2"><span class="fw-semibold">{{ $doneCount }} dari {{ $total }} modul selesai</span><strong>{{ $pct }}%</strong></div>
                <div class="mos-progress"><span style="width: {{ $pct }}%"></span></div>
            </div>
            @if ($pct >= 100)
                <span class="badge badge-light-success fs-7 py-3 px-4">Alumni Foundation ✓</span>
            @endif
        </div>
    </div>

    @foreach (\App\Models\MindOs\FoundationModule::CATEGORIES as $key => $label)
        @continue(! isset($groups[$key]))
        <h2 class="fs-4 fw-bold mb-4 mt-2">{{ $label }}</h2>
        <div class="row g-4 mb-8">
            @foreach ($groups[$key] as $module)
                @php $isDone = isset($done[$module->id]); @endphp
                <div class="col-md-6 col-xxl-4">
                    <a href="{{ route('mindos.foundation.show', $module) }}" class="mos-card d-flex flex-column h-100 p-5 text-decoration-none">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <span class="mos-chip">{{ $module->duration_minutes }} menit</span>
                            @if ($isDone)
                                <i class="ki-solid ki-check-circle fs-1 text-success" aria-label="Selesai"></i>
                            @endif
                        </div>
                        <h3 class="fs-5 fw-bold text-gray-900 mb-2">{{ $module->title }}</h3>
                        <p class="text-gray-600 fs-7 mb-0">{{ $module->summary }}</p>
                    </a>
                </div>
            @endforeach
        </div>
    @endforeach
@endsection
