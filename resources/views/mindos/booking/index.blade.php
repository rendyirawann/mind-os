@extends('backend.layout.app')

@section('title', 'Booking Saya')

@section('content')
    @include('mindos.partials.header', [
        'title' => 'Booking Saya',
        'subtitle' => 'Jadwal sesi Online Live dan Tatap Muka Anda.',
    ])

    <div class="mb-5"><a href="{{ route('mindos.services.index') }}" class="btn btn-primary btn-sm">Booking sesi baru</a></div>

    <div class="card card-flush shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed align-middle gs-5 gy-4 mb-0">
                    <thead><tr class="text-muted fw-bold fs-8 text-uppercase">
                        <th>Kode</th><th>Layanan</th><th>Jadwal</th><th>Practitioner</th><th>Status</th><th>Pembayaran</th><th></th>
                    </tr></thead>
                    <tbody>
                        @forelse ($bookings as $b)
                            <tr>
                                <td class="fw-bold">{{ $b->code }}</td>
                                <td>{{ $b->service->name }}<div class="fs-8 text-muted">{{ $b->service->modality_label }}</div></td>
                                <td class="text-nowrap">{{ $b->scheduled_at->translatedFormat('d M Y H:i') }}</td>
                                <td>{{ $b->practitioner->name ?? 'Belum ditentukan' }}</td>
                                <td><span class="badge badge-light-{{ $b->status_color }}">{{ $b->status_label }}</span></td>
                                <td><span class="badge badge-light-{{ $b->payment_status === 'unpaid' ? 'warning' : 'success' }}">{{ ['unpaid' => 'Belum bayar', 'paid' => 'Lunas', 'free' => 'Gratis'][$b->payment_status] }}</span>
                                    <div class="fs-8 text-muted">Rp {{ number_format($b->amount, 0, ',', '.') }}</div></td>
                                <td class="text-end text-nowrap">
                                    @if ($b->meeting_link && $b->status === 'confirmed')
                                        <a href="{{ $b->meeting_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-light-primary">Masuk sesi</a>
                                    @endif
                                    @if (in_array($b->status, ['pending', 'confirmed'], true))
                                        <form method="POST" action="{{ route('mindos.bookings.cancel', $b) }}" class="d-inline" onsubmit="return confirm('Batalkan booking ini?')">
                                            @csrf
                                            <button class="btn btn-sm btn-light-danger">Batal</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-10">Belum ada booking.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-5">{{ $bookings->links() }}</div>
@endsection
