@extends('backend.layout.app')

@section('title', 'Booking ' . $service->name)

@section('content')
    @include('mindos.partials.header', [
        'chip' => $service->modality_label,
        'title' => 'Booking: ' . $service->name,
        'subtitle' => $service->description,
    ])

    <div class="row g-6">
        <div class="col-xl-7">
            <form method="POST" action="{{ route('mindos.bookings.store', $service) }}" class="card card-flush shadow-sm">
                @csrf
                <div class="card-body p-5 p-lg-8">
                    <div class="mb-5">
                        <label class="form-label required" for="scheduled_at">Tanggal & jam yang diinginkan</label>
                        <input id="scheduled_at" type="datetime-local" name="scheduled_at" class="form-control"
                            min="{{ now()->addHours(12)->format('Y-m-d\TH:i') }}" value="{{ old('scheduled_at') }}" required />
                        <div class="form-text">Jadwal final dikonfirmasi tim kami. Minimal 12 jam dari sekarang.</div>
                    </div>
                    <div class="mb-5">
                        <label class="form-label required" for="complaint">Keluhan / tujuan sesi</label>
                        <textarea id="complaint" name="complaint" rows="5" maxlength="1000" class="form-control" required
                            placeholder="Ceritakan singkat apa yang ingin Anda selesaikan.">{{ old('complaint') }}</textarea>
                        <div class="form-text">Hanya dibaca oleh Practitioner yang menangani Anda.</div>
                    </div>
                    <button class="btn btn-primary">Kirim Booking</button>
                </div>
            </form>
        </div>
        <div class="col-xl-5">
            <div class="card card-flush shadow-sm">
                <div class="card-body">
                    <h2 class="fs-5 fw-bold mb-4">Ringkasan</h2>
                    <dl class="row mb-0 fs-7">
                        <dt class="col-5 text-muted fw-normal">Layanan</dt><dd class="col-7 fw-semibold">{{ $service->name }}</dd>
                        <dt class="col-5 text-muted fw-normal">Modalitas</dt><dd class="col-7 fw-semibold">{{ $service->modality_label }}</dd>
                        <dt class="col-5 text-muted fw-normal">Tahap 6R</dt><dd class="col-7 fw-semibold">{{ $service->stage ? $service->stage . ' — ' . \App\Support\SixR::stage($service->stage)['name'] : '-' }}</dd>
                        <dt class="col-5 text-muted fw-normal">Durasi</dt><dd class="col-7 fw-semibold">{{ $service->duration_minutes }} menit</dd>
                        <dt class="col-5 text-muted fw-normal">Investasi</dt><dd class="col-7 fw-bolder fs-5 text-primary">{{ $service->price_label }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
