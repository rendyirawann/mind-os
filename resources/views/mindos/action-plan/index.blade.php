@extends('backend.layout.app')

@section('title', 'Action Plan 30 Hari')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'R6 · Reinforce',
        'title' => 'Action Plan 30 Hari',
        'subtitle' => 'Satu langkah kecil setiap hari untuk memasang program baru secara permanen.',
    ])

    @if ($plan)
        @php
            $doneCount = $plan->days->whereNotNull('done_at')->count();
            $pct = (int) round($doneCount / max(1, $plan->days->count()) * 100);
            $today = $plan->days->firstWhere('day_number', $plan->current_day);
        @endphp
        <div class="row g-6">
            <div class="col-xl-5">
                <div class="mos-hero mb-6">
                    <div class="position-relative" style="z-index:1">
                        <span class="badge bg-white bg-opacity-25 text-white mb-3">Hari ke-{{ $plan->current_day }} dari 30</span>
                        <h2 class="fs-2x fw-bolder mb-2">{{ $plan->title }}</h2>
                        <p class="mb-5">{{ $plan->goal }}</p>
                        <div class="d-flex justify-content-between fs-7 mb-2"><span>{{ $doneCount }}/30 hari selesai</span><strong>{{ $pct }}%</strong></div>
                        <div class="mos-progress bg-white bg-opacity-25"><span style="width: {{ $pct }}%"></span></div>
                    </div>
                </div>

                @if ($today)
                    <form method="POST" action="{{ route('mindos.action-plan.day', $today) }}" class="card card-flush shadow-sm mb-6">
                        @csrf
                        <div class="card-body">
                            <h3 class="fs-5 fw-bold mb-2">Tugas hari ini</h3>
                            <p class="text-gray-700 mb-4">{{ $today->task }}</p>
                            <label class="form-label" for="reflection">Refleksi singkat</label>
                            <textarea id="reflection" name="reflection" rows="2" maxlength="500" class="form-control mb-4">{{ $today->reflection }}</textarea>
                            <button class="btn {{ $today->done_at ? 'btn-light' : 'btn-primary' }} w-100">
                                {{ $today->done_at ? 'Batalkan tanda selesai' : 'Tandai hari ini selesai ✓' }}
                            </button>
                        </div>
                    </form>
                @endif

                <form method="POST" action="{{ route('mindos.action-plan.stop', $plan) }}" onsubmit="return confirm('Hentikan Action Plan dan lanjut evaluasi?')">
                    @csrf
                    <button class="btn btn-sm btn-light-danger">Hentikan & evaluasi</button>
                </form>
            </div>

            <div class="col-xl-7">
                <div class="card card-flush shadow-sm">
                    <div class="card-body">
                        <h3 class="fs-5 fw-bold mb-5">Kalender 30 hari</h3>
                        <div class="mos-days mb-6">
                            @foreach ($plan->days as $day)
                                @if ($day->day_number <= $plan->current_day)
                                    <form method="POST" action="{{ route('mindos.action-plan.day', $day) }}" class="m-0">
                                        @csrf
                                        <button type="submit" class="mos-day w-100 {{ $day->done_at ? 'is-done' : '' }} {{ $day->day_number === $plan->current_day ? 'is-today' : '' }}"
                                            title="{{ $day->task }}" aria-label="Hari {{ $day->day_number }}{{ $day->done_at ? ' selesai' : '' }}">{{ $day->day_number }}</button>
                                    </form>
                                @else
                                    <span class="mos-day opacity-50" title="{{ $day->task }}">{{ $day->day_number }}</span>
                                @endif
                            @endforeach
                        </div>
                        <ul class="list-unstyled mb-0 fs-7">
                            @foreach ($plan->days->take(7) as $day)
                                <li class="py-2 border-bottom border-gray-200"><strong>Pola hari {{ $day->day_number }}:</strong> {{ $day->task }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row g-6">
            <div class="col-xl-7">
                <form method="POST" action="{{ route('mindos.action-plan.store') }}" class="card card-flush shadow-sm">
                    @csrf
                    <div class="card-body p-5 p-lg-8">
                        <h2 class="fs-4 fw-bold mb-5">Buat Action Plan baru</h2>
                        <div class="mb-4">
                            <label class="form-label required" for="title">Nama rencana</label>
                            <input id="title" name="title" class="form-control" maxlength="150" value="{{ old('title', '30 Hari Menuju Pikiran Lebih Tenang') }}" required />
                        </div>
                        <div class="mb-4">
                            <label class="form-label required" for="goal">Tujuan — apa yang ingin berubah dalam 30 hari?</label>
                            <textarea id="goal" name="goal" rows="3" class="form-control" required>{{ old('goal') }}</textarea>
                        </div>
                        <div class="row g-4 mb-5">
                            <div class="col-md-7">
                                <label class="form-label" for="mind_bug_id">Pola pikiran fokus</label>
                                <select id="mind_bug_id" name="mind_bug_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach ($bugs as $bug)
                                        <option value="{{ $bug->id }}" @selected(old('mind_bug_id', $suggestedBug) == $bug->id)>{{ $bug->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label required" for="start_date">Mulai</label>
                                <input id="start_date" type="date" name="start_date" class="form-control" min="{{ today()->toDateString() }}" value="{{ old('start_date', today()->toDateString()) }}" required />
                            </div>
                        </div>
                        <button class="btn btn-primary">Mulai 30 Hari</button>
                    </div>
                </form>
            </div>
            <div class="col-xl-5">
                <div class="card card-flush shadow-sm">
                    <div class="card-body">
                        <h2 class="fs-5 fw-bold mb-4">Riwayat Action Plan</h2>
                        @forelse ($history as $item)
                            <div class="d-flex justify-content-between py-3 border-bottom border-gray-200">
                                <span><span class="fw-semibold d-block">{{ $item->title }}</span><span class="fs-8 text-muted">{{ $item->start_date->translatedFormat('d M Y') }}</span></span>
                                <span class="badge badge-light-{{ $item->status === 'completed' ? 'success' : 'secondary' }}">{{ $item->days_done }}/30</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Belum ada riwayat.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
