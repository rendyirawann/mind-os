@extends('backend.layout.app')

@section('title', 'Layanan Online Live & Tatap Muka')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'F · G · H',
        'title' => 'Layanan Mind OS',
        'subtitle' => 'Pilih modalitas sesuai hasil Cek Kesehatan Pikiran: Web App (gratis), Online Live, atau Tatap Muka bersama Practitioner bersertifikat.',
    ])

    @if ($recommendation)
        <div class="alert alert-primary d-flex align-items-center gap-3 mb-6">
            <i class="ki-outline ki-information-5 fs-2 text-primary"></i>
            <div>Rekomendasi dari Cek Kesehatan Pikiran Anda: <strong>{{ \App\Models\MindOs\BugAssessment::RECOMMENDATIONS[$recommendation] }}</strong></div>
        </div>
    @endif

    @foreach (['online' => ['Online Live', 'R4 Reframe · R5A Reprogram Guided · Workshop & Coaching', 'info'], 'offline' => ['Tatap Muka', 'R3 Release · R5B Reprogram Deep · Trauma & Couple', 'warning']] as $modality => [$label, $desc, $color])
        <section id="{{ $modality }}" class="mb-10">
            <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                <h2 class="fs-3 fw-bold mb-0">{{ $label }}</h2>
                <span class="text-muted fs-7">{{ $desc }}</span>
                @if (($modality === 'online' && $recommendation === 'online_live') || ($modality === 'offline' && $recommendation === 'tatap_muka'))
                    <span class="badge badge-{{ $color }}">Direkomendasikan</span>
                @endif
            </div>
            <div class="row g-4">
                @foreach ($services[$modality] ?? [] as $service)
                    <div class="col-md-6 col-xxl-4">
                        <div class="mos-card d-flex flex-column h-100 p-5 is-hoverable">
                            <div class="d-flex justify-content-between gap-2 mb-3">
                                <span class="badge badge-light-{{ $color }}">{{ $service->stage ?: ($service->is_group ? 'Grup' : 'Privat') }}</span>
                                <span class="fs-8 text-muted">{{ $service->duration_minutes }} menit{{ $service->is_group ? ' · maks ' . $service->capacity . ' org' : '' }}</span>
                            </div>
                            <h3 class="fs-5 fw-bold text-gray-900 mb-2">{{ $service->name }}</h3>
                            <p class="text-gray-600 fs-7 mb-5">{{ $service->description }}</p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="fs-4 fw-bolder text-gray-900">{{ $service->price_label }}</span>
                                <a href="{{ route('mindos.bookings.create', $service) }}" class="btn btn-sm btn-primary">Booking</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
@endsection
