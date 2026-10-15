@extends('backend.layout.app')

@section('title', 'Perjalanan Saya')

@section('content')
    <div class="mos-hero mb-6">
        <div class="row g-6 align-items-center position-relative" style="z-index:1">
            <div class="col-lg-7">
                <span class="badge bg-white bg-opacity-25 text-white mb-3">Mind OS · User Journey</span>
                <h1 class="fs-2hx fw-bolder mb-2">Halo, {{ Str::before($user->name, ' ') }}. Selamat datang di perjalanan pulih Anda.</h1>
                <p class="fs-5 mb-5">Ikuti langkah demi langkah: Foundation → Cek Kesehatan Pikiran → 6R → Action Plan 30 Hari → Evaluasi.</p>
                @if ($nextStep)
                    <a href="{{ $nextStep[3] }}" class="btn btn-light fw-bold">
                        Langkah berikutnya: {{ $nextStep[1] }} <i class="ki-outline ki-arrow-right fs-4 ms-1"></i>
                    </a>
                @else
                    <span class="btn btn-light fw-bold disabled">Semua tahap selesai — Anda Alumni Mind OS 🎉</span>
                @endif
            </div>
            <div class="col-lg-5">
                <div class="bg-white bg-opacity-10 rounded-4 p-5">
                    <div class="d-flex justify-content-between fs-7 mb-2"><span>Progres perjalanan</span><strong>{{ $journeyPct }}%</strong></div>
                    <div class="mos-progress bg-white bg-opacity-25 mb-5"><span style="width: {{ $journeyPct }}%"></span></div>
                    <div class="row g-3 text-center">
                        <div class="col-4"><div class="fs-2 fw-bolder">{{ $foundationPct }}%</div><div class="fs-8 opacity-75">Foundation</div></div>
                        <div class="col-4"><div class="fs-2 fw-bolder">{{ $counts['journals'] }}</div><div class="fs-8 opacity-75">Jurnal</div></div>
                        <div class="col-4"><div class="fs-2 fw-bolder">{{ $counts['six_r'] }}</div><div class="fs-8 opacity-75">Langkah 6R</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-6">
        <div class="col-xl-7">
            <div class="card card-flush shadow-sm h-100">
                <div class="card-header pt-6 border-0">
                    <h2 class="card-title fw-bold fs-4">Peta Perjalanan (A → P)</h2>
                </div>
                <div class="card-body pt-2">
                    <ol class="list-unstyled mb-0">
                        @foreach ($steps as [$code, $label, $done, $url])
                            <li class="d-flex align-items-center gap-4 py-3 {{ ! $loop->last ? 'border-bottom border-gray-200' : '' }}">
                                <span class="mos-r {{ $done ? 'mos-r--web' : '' }} {{ $done ? '' : 'bg-light text-gray-600' }}">{{ $code }}</span>
                                <a href="{{ $url }}" class="flex-grow-1 fw-semibold text-gray-800 text-hover-primary">{{ $label }}</a>
                                @if ($done)
                                    <i class="ki-solid ki-check-circle fs-2 text-success" aria-label="Selesai"></i>
                                @else
                                    <i class="ki-outline ki-time fs-2 text-gray-400" aria-label="Belum"></i>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>

        <div class="col-xl-5 d-flex flex-column gap-5">
            <div class="card card-flush shadow-sm">
                <div class="card-body">
                    <h2 class="fw-bold fs-5 mb-4">Hasil Cek Kesehatan Pikiran Terakhir</h2>
                    @if ($assessment)
                        <div class="d-flex align-items-center gap-4 mb-3">
                            <div class="fs-2hx fw-bolder text-primary">{{ $assessment->percentage }}%</div>
                            <div>
                                <div class="fw-bold">{{ $assessment->dominantBug->name ?? '-' }}</div>
                                <div class="text-muted fs-7">Level {{ ucfirst($assessment->level) }} · {{ $assessment->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="alert alert-primary py-3 mb-3 fs-7">Rekomendasi: <strong>{{ $assessment->recommendation_label }}</strong></div>
                        <a href="{{ route('mindos.bug-detector.show', $assessment) }}" class="btn btn-sm btn-light-primary">Lihat detail</a>
                    @else
                        <p class="text-muted">Belum ada. Temukan pola pikiran dominan pikiran Anda dalam 5 menit.</p>
                        <a href="{{ route('mindos.bug-detector.index') }}" class="btn btn-sm btn-primary">Mulai Cek Kesehatan Pikiran</a>
                    @endif
                </div>
            </div>

            <div class="card card-flush shadow-sm">
                <div class="card-body">
                    <h2 class="fw-bold fs-5 mb-4">Sesi Terdekat</h2>
                    @if ($nextBooking)
                        <div class="fw-bold">{{ $nextBooking->service->name }}</div>
                        <div class="text-muted fs-7 mb-2">{{ $nextBooking->scheduled_at->translatedFormat('l, d F Y · H:i') }} · {{ $nextBooking->service->modality_label }}</div>
                        <span class="badge badge-light-{{ $nextBooking->status_color }}">{{ $nextBooking->status_label }}</span>
                    @else
                        <p class="text-muted mb-3">Belum ada sesi terjadwal.</p>
                        <a href="{{ route('mindos.services.index') }}" class="btn btn-sm btn-light-primary">Lihat layanan</a>
                    @endif
                </div>
            </div>

            <div class="card card-flush shadow-sm">
                <div class="card-body">
                    <h2 class="fw-bold fs-5 mb-4">Action Plan 30 Hari</h2>
                    @if ($plan)
                        @php $pct = $plan->days_count ? (int) round($plan->days_done / $plan->days_count * 100) : 0; @endphp
                        <div class="fw-bold mb-1">{{ $plan->title }}</div>
                        <div class="text-muted fs-7 mb-3">Hari ke-{{ $plan->current_day }} · {{ $plan->days_done }}/{{ $plan->days_count }} selesai</div>
                        <div class="mos-progress mb-3"><span style="width: {{ $pct }}%"></span></div>
                        <a href="{{ route('mindos.action-plan.index') }}" class="btn btn-sm btn-light-primary">Checklist hari ini</a>
                    @else
                        <p class="text-muted mb-3">Belum ada Action Plan aktif.</p>
                        <a href="{{ route('mindos.action-plan.index') }}" class="btn btn-sm btn-light-primary">Buat Action Plan</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card card-flush shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                        <h2 class="fw-bold fs-5 mb-0">Progres Metode 6R Anda</h2>
                        <a href="{{ route('mindos.six-r.index') }}" class="fs-7 fw-semibold">Buka 6R Tracker →</a>
                    </div>
                    @include('mindos.partials.six-r-flow', ['counts' => $stageCounts])
                </div>
            </div>
        </div>
    </div>
@endsection
