@extends('backend.layout.app')

@section('title', $track->title)

@section('content')
    @php $info = \App\Support\SixR::stage($track->stage); @endphp
    <div class="row g-6">
        <div class="col-xl-8">
            <a href="{{ route('mindos.audio.index') }}" class="fs-7 fw-semibold d-inline-block mb-4">← Semua audio</a>
            <div class="mos-hero mb-6">
                <div class="position-relative" style="z-index:1">
                    <span class="badge bg-white bg-opacity-25 text-white mb-3">{{ $track->stage }} · {{ $info['name'] }} · {{ $track->duration_label }}</span>
                    <h1 class="fs-2hx fw-bolder mb-2">{{ $track->title }}</h1>
                    <p class="fs-5 mb-6">{{ $track->description }}</p>

                    @if ($track->audio_url)
                        <audio id="mos-player" controls preload="none" class="w-100" src="{{ $track->audio_url }}"></audio>
                    @else
                        {{-- No recording yet: a guided timer with the script keeps the session usable. --}}
                        <div class="d-flex flex-wrap align-items-center gap-4">
                            <button type="button" id="mos-timer-btn" class="btn btn-light fw-bold">Mulai sesi ({{ $track->duration_label }})</button>
                            <span id="mos-timer" class="fs-2 fw-bolder" aria-live="polite">{{ sprintf('%02d:%02d', intdiv($track->duration_seconds, 60), $track->duration_seconds % 60) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            @if ($track->script)
                <div class="card card-flush shadow-sm">
                    <div class="card-body p-6 p-lg-10">
                        <h2 class="fs-5 fw-bold mb-4">Naskah Panduan</h2>
                        <div class="mos-prose">{{ $track->script }}</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-xl-4">
            <div class="card card-flush shadow-sm">
                <div class="card-body">
                    <h2 class="fs-5 fw-bold mb-4">Audio lainnya</h2>
                    @foreach ($others as $other)
                        <a href="{{ route('mindos.audio.show', $other) }}" class="d-flex justify-content-between py-3 border-bottom border-gray-200 text-gray-800 text-hover-primary">
                            <span class="fw-semibold">{{ $other->title }}</span>
                            <span class="badge badge-light-primary">{{ $other->stage }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var url = @json(route('mindos.audio.listen', $track));
                var token = document.querySelector('meta[name="csrf-token"]').content;
                var logged = false;

                function logListen() {
                    if (logged) return;
                    logged = true;
                    fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
                }

                var player = document.getElementById('mos-player');
                if (player) {
                    player.addEventListener('ended', logListen);
                    return;
                }

                var btn = document.getElementById('mos-timer-btn');
                var out = document.getElementById('mos-timer');
                var left = {{ (int) $track->duration_seconds }};
                var timer = null;

                btn && btn.addEventListener('click', function () {
                    if (timer) {
                        clearInterval(timer);
                        timer = null;
                        btn.textContent = 'Lanjutkan sesi';
                        return;
                    }
                    btn.textContent = 'Jeda';
                    timer = setInterval(function () {
                        left--;
                        out.textContent = String(Math.floor(left / 60)).padStart(2, '0') + ':' + String(left % 60).padStart(2, '0');
                        if (left <= 0) {
                            clearInterval(timer);
                            btn.textContent = 'Sesi selesai ✓';
                            btn.disabled = true;
                            logListen();
                        }
                    }, 1000);
                });
            })();
        </script>
    @endpush
@endsection
