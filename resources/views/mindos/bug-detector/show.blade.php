@extends('backend.layout.app')

@section('title', 'Hasil Cek Kesehatan Pikiran')

@section('content')
    @php
        $dominant = $assessment->dominantBug;
        $levelColor = ['ringan' => 'success', 'sedang' => 'warning', 'berat' => 'danger'][$assessment->level] ?? 'primary';
        $cta = [
            'web_app' => [route('mindos.foundation.index'), 'Lanjut Self-Healing di Web App', 'Bug Anda masih ringan. Modul Foundation, Jurnal Recognize, audio, dan Action Plan 30 Hari cukup untuk mulai memperbaikinya secara mandiri.'],
            'online_live' => [route('mindos.services.index') . '#online', 'Booking Sesi Online Live', 'Bug Anda di level sedang. Sesi Reframe (R4) dan Reprogram Guided (R5A) online akan mempercepat perubahan.'],
            'tatap_muka' => [route('mindos.services.index') . '#offline', 'Booking Sesi Tatap Muka', 'Bug Anda cukup kuat atau terkait luka masa lalu. Release (R3) dan Reprogram Deep (R5B) bersama Practitioner sangat disarankan.'],
        ][$assessment->recommendation];
    @endphp

    @include('mindos.partials.header', [
        'chip' => 'Hasil · ' . $assessment->created_at->translatedFormat('d F Y'),
        'title' => 'Hasil Pemeriksaan Kesehatan Pikiran',
    ])

    <div class="row g-6">
        <div class="col-xl-5">
            <div class="mos-hero h-100">
                <div class="position-relative" style="z-index:1">
                    <div class="fs-7 opacity-75 mb-1">Pola pikiran dominan</div>
                    <h2 class="fs-2hx fw-bolder mb-1">{{ $dominant->name ?? '-' }}</h2>
                    <p class="fs-6 mb-6">“{{ $dominant->tagline ?? '' }}”</p>
                    <div class="d-flex gap-6 mb-6">
                        <div><div class="fs-2x fw-bolder">{{ $assessment->percentage }}%</div><div class="fs-8 opacity-75">Rata-rata beban bug</div></div>
                        <div><div class="fs-2x fw-bolder text-capitalize">{{ $assessment->level }}</div><div class="fs-8 opacity-75">Level</div></div>
                    </div>
                    @if ($dominant)
                        <p class="mb-2">{{ $dominant->description }}</p>
                        <p class="mb-0"><strong>Kunci reframe:</strong> {{ $dominant->reframe_hint }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card card-flush shadow-sm h-100">
                <div class="card-body">
                    <h2 class="fs-5 fw-bold mb-5">Skor per Pola Pikiran</h2>
                    @foreach ($assessment->scores as $bugId => $score)
                        @php $bug = $bugs[$bugId] ?? null; @endphp
                        @continue(! $bug)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between fs-7 mb-1">
                                <span class="fw-semibold text-gray-800">{{ $bug->code }} · {{ $bug->name }}</span>
                                <strong>{{ $score }}%</strong>
                            </div>
                            <div class="progress h-8px">
                                <div class="progress-bar bg-{{ $bug->color }}" role="progressbar" style="width: {{ $score }}%"
                                    aria-valuenow="{{ $score }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $bug->name }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-flush shadow-sm border border-{{ $levelColor }} border-dashed">
                <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center gap-5">
                    <div class="flex-grow-1">
                        <span class="badge badge-light-{{ $levelColor }} mb-2">Rekomendasi Mind OS</span>
                        <h2 class="fs-3 fw-bold mb-2">{{ $assessment->recommendation_label }}</h2>
                        <p class="text-gray-600 mb-0">{{ $cta[2] }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $cta[0] }}" class="btn btn-primary">{{ $cta[1] }}</a>
                        <a href="{{ route('mindos.action-plan.index') }}" class="btn btn-light-primary">Buat Action Plan</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
