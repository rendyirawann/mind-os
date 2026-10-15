@extends('backend.layout.app')

@section('title', 'Cek Kesehatan Pikiran')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'R1 · Recognize',
        'title' => 'Cek Kesehatan Pikiran',
        'subtitle' => 'Jawab jujur sesuai yang Anda rasakan 2–4 minggu terakhir. Tidak ada jawaban benar atau salah.',
    ])

    <div class="row g-6">
        <div class="col-xl-8">
            <form method="POST" action="{{ route('mindos.bug-detector.store') }}" class="card card-flush shadow-sm">
                @csrf
                <div class="card-body p-5 p-lg-8">
                    <div class="d-flex flex-wrap gap-3 fs-8 text-muted mb-6">
                        <span><strong>1</strong> Tidak pernah</span><span><strong>2</strong> Jarang</span>
                        <span><strong>3</strong> Kadang</span><span><strong>4</strong> Sering</span><span><strong>5</strong> Hampir selalu</span>
                    </div>

                    @foreach ($questions as $q)
                        <fieldset class="py-5 {{ ! $loop->last ? 'border-bottom border-gray-200' : '' }}">
                            <legend class="fs-6 fw-semibold text-gray-800 mb-3 float-none w-auto">
                                <span class="text-muted me-1">{{ $loop->iteration }}.</span> {{ $q->question }}
                            </legend>
                            <div class="mos-scale">
                                @foreach ([1 => 'Tidak pernah', 2 => 'Jarang', 3 => 'Kadang', 4 => 'Sering', 5 => 'Hampir selalu'] as $val => $label)
                                    <div class="position-relative">
                                        <input type="radio" id="q{{ $q->id }}-{{ $val }}" name="answers[{{ $q->id }}]" value="{{ $val }}"
                                            @checked((int) old('answers.' . $q->id) === $val) required />
                                        <label for="q{{ $q->id }}-{{ $val }}"><strong>{{ $val }}</strong><span>{{ $label }}</span></label>
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Lihat Hasil Saya</button>
                </div>
            </form>
        </div>

        <div class="col-xl-4">
            <div class="card card-flush shadow-sm position-sticky" style="top: 90px">
                <div class="card-body">
                    <h2 class="fs-5 fw-bold mb-4">Riwayat Tes</h2>
                    @forelse ($history as $item)
                        <a href="{{ route('mindos.bug-detector.show', $item) }}" class="d-flex justify-content-between align-items-center py-3 border-bottom border-gray-200 text-gray-800 text-hover-primary">
                            <span>
                                <span class="fw-semibold d-block">{{ $item->dominantBug->name ?? '-' }}</span>
                                <span class="fs-8 text-muted">{{ $item->created_at->translatedFormat('d M Y H:i') }}</span>
                            </span>
                            <span class="badge badge-light-primary">{{ $item->percentage }}%</span>
                        </a>
                    @empty
                        <p class="text-muted mb-0">Belum ada riwayat. Ini tes pertama Anda.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
