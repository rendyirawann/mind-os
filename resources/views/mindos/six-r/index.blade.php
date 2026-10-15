@extends('backend.layout.app')

@section('title', '6R Tracker')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'Metode 6R',
        'title' => '6R Tracker',
        'subtitle' => 'Recognize → Reveal → Release → Reframe → Reprogram → Reinforce. Catat setiap langkah dan lihat intensitas emosi Anda perlahan mereda.',
    ])

    <div class="card card-flush shadow-sm mb-6">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                <h2 class="fs-5 fw-bold mb-0">Flow 6R berdasarkan modalitas</h2>
                <span class="text-muted fs-7">Rata-rata penurunan intensitas: <strong class="text-success">{{ $avgDrop > 0 ? '-' . $avgDrop : $avgDrop }}</strong> poin</span>
            </div>
            @include('mindos.partials.six-r-flow', ['counts' => $stageCounts])
        </div>
    </div>

    <div class="row g-6">
        <div class="col-xl-4">
            <form method="POST" action="{{ route('mindos.six-r.store') }}" class="card card-flush shadow-sm">
                @csrf
                <div class="card-body">
                    <h2 class="fs-5 fw-bold mb-5">Catat langkah</h2>
                    <div class="mb-4">
                        <label class="form-label required" for="stage">Tahap</label>
                        <select id="stage" name="stage" class="form-select" required>
                            @foreach (\App\Support\SixR::STAGES as $code => $stage)
                                <option value="{{ $code }}" @selected(old('stage') === $code)>{{ $code }} — {{ $stage['name'] }} ({{ \App\Support\SixR::MODALITIES[$stage['modality']]['label'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="mind_bug_id">Pola yang sedang dipulihkan</label>
                        <select id="mind_bug_id" name="mind_bug_id" class="form-select">
                            <option value="">—</option>
                            @foreach ($bugs as $bug)
                                <option value="{{ $bug->id }}" @selected(old('mind_bug_id') == $bug->id)>{{ $bug->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label" for="intensity_before">Intensitas sebelum</label>
                            <input id="intensity_before" type="number" name="intensity_before" min="0" max="10" class="form-control" value="{{ old('intensity_before') }}" />
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="intensity_after">Sesudah</label>
                            <input id="intensity_after" type="number" name="intensity_after" min="0" max="10" class="form-control" value="{{ old('intensity_after') }}" />
                        </div>
                    </div>
                    <div class="mb-5">
                        <label class="form-label required" for="note">Catatan / insight</label>
                        <textarea id="note" name="note" rows="4" class="form-control" required>{{ old('note') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Simpan ke Tracker</button>
                </div>
            </form>
        </div>

        <div class="col-xl-8">
            <div class="card card-flush shadow-sm">
                <div class="card-body">
                    <h2 class="fs-5 fw-bold mb-4">Riwayat</h2>
                    @forelse ($logs as $log)
                        @php $info = $log->stage_info; @endphp
                        <div class="d-flex gap-4 py-4 {{ ! $loop->last ? 'border-bottom border-gray-200' : '' }}">
                            <span class="mos-r mos-r--{{ $info['modality'] }} flex-shrink-0">{{ $log->stage }}</span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <span class="fw-bold text-gray-900">{{ $info['name'] }} <span class="text-muted fw-normal fs-8">· {{ $info['modality_label'] }}</span></span>
                                    <span class="fs-8 text-muted">{{ $log->created_at->translatedFormat('d M Y H:i') }}</span>
                                </div>
                                <p class="text-gray-700 fs-7 mb-2 mt-1">{{ $log->note }}</p>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    @if ($log->bug)
                                        <span class="badge badge-light">{{ $log->bug->name }}</span>
                                    @endif
                                    @if (! is_null($log->intensity_before) && ! is_null($log->intensity_after))
                                        <span class="badge badge-light-success">{{ $log->intensity_before }} → {{ $log->intensity_after }}</span>
                                    @endif
                                    <form method="POST" action="{{ route('mindos.six-r.destroy', $log) }}" class="ms-auto" onsubmit="return confirm('Hapus catatan ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-icon btn-light-danger w-25px h-25px" aria-label="Hapus"><i class="ki-outline ki-trash fs-6"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Belum ada catatan. Mulai dari R1 — Recognize.</p>
                    @endforelse
                    <div class="mt-4">{{ $logs->links() }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
