@extends('backend.layout.app')

@section('title', 'Sertifikasi & Lisensi')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'Flow Sertifikasi',
        'title' => 'Sertifikasi & Lisensi Mind OS',
        'subtitle' => 'Alumni Foundation → Practitioner → Master Practitioner → Trainer berlisensi.',
    ])

    <div class="card card-flush shadow-sm mb-6">
        <div class="card-body">
            <div class="mos-flow">
                <span class="mos-r {{ $isAlumni ? 'mos-r--web' : 'bg-light text-gray-600' }}">A · Alumni Foundation</span>
                @foreach ($programs as $p)
                    <span class="mos-flow__arrow">→</span>
                    <span class="mos-r {{ in_array($p->level, $certifiedLevels, true) ? 'mos-r--online' : 'bg-light text-gray-600' }}">{{ \App\Models\MindOs\CertificationProgram::LEVELS[$p->level] }}</span>
                @endforeach
            </div>
            @unless ($isAlumni)
                <p class="text-muted fs-7 mt-4 mb-0">Selesaikan semua <a href="{{ route('mindos.foundation.index') }}">Modul Foundation</a> untuk membuka program Practitioner.</p>
            @endunless
        </div>
    </div>

    <div class="row g-5">
        @foreach ($programs as $program)
            @php $enrollment = $enrollments[$program->id] ?? null; @endphp
            <div class="col-xl-4">
                <div class="mos-card d-flex flex-column h-100 p-6">
                    <span class="mos-chip mb-3">Syarat: {{ $program->prerequisite }}</span>
                    <h2 class="fs-4 fw-bold mb-2">{{ $program->name }}</h2>
                    <p class="text-gray-600 fs-7">{{ $program->description }}</p>
                    <ul class="list-unstyled mb-5">
                        @foreach ($program->curriculum as $item)
                            <li class="d-flex gap-2 py-1 fs-7"><i class="ki-solid ki-check-circle text-primary fs-4"></i> {{ $item }}</li>
                        @endforeach
                    </ul>
                    <div class="fs-3 fw-bolder mb-5">{{ $program->price_label }}</div>

                    <div class="mt-auto">
                        @if ($enrollment)
                            <div class="p-4 rounded-3 bg-light">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-semibold">Status</span>
                                    <span class="badge badge-light-{{ $enrollment->status_color }}">{{ $enrollment->status_label }}</span>
                                </div>
                                <div class="fs-8 text-muted mb-1">Jam: {{ $enrollment->hours_done }}/{{ $program->hours }} · Klien praktik: {{ $enrollment->clients_done }}/{{ $program->practice_clients }}</div>
                                @if ($enrollment->certificate_number)
                                    <div class="fs-7 fw-bold text-success mt-2">No. Sertifikat: {{ $enrollment->certificate_number }}</div>
                                @endif
                            </div>
                        @else
                            <form method="POST" action="{{ route('mindos.certification.apply', $program) }}">
                                @csrf
                                <label class="form-label fs-7" for="motivation-{{ $program->id }}">Motivasi Anda</label>
                                <textarea id="motivation-{{ $program->id }}" name="motivation" rows="3" minlength="20" maxlength="1000" class="form-control mb-3" required
                                    placeholder="Mengapa Anda ingin mengikuti program ini? (min. 20 karakter)"></textarea>
                                <button class="btn btn-primary w-100">Daftar Program</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
