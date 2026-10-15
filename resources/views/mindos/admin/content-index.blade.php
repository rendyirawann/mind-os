@extends('backend.layout.app')

@section('title', $def['label'])

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'Konten Mind OS',
        'title' => $def['label'],
    ])

    <div class="d-flex flex-wrap gap-2 mb-5">
        @foreach ($types as $key => $label)
            <a href="{{ route('mindos.admin.content.index', $key) }}" class="btn btn-sm {{ $def['key'] === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
        @endforeach
        <a href="{{ route('mindos.admin.content.create', $def['key']) }}" class="btn btn-sm btn-light-primary ms-auto"><i class="ki-outline ki-plus fs-4"></i> Tambah</a>
    </div>

    <div class="card card-flush shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed align-middle gs-5 gy-4 mb-0">
                    <thead><tr class="text-muted fw-bold fs-8 text-uppercase">
                        @foreach ($def['columns'] as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                        <th class="text-end">Aksi</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                @foreach ($def['columns'] as $attr => $label)
                                    @php $value = $item->{$attr}; @endphp
                                    <td>
                                        @if (is_bool($value))
                                            <span class="badge badge-light-{{ $value ? 'success' : 'secondary' }}">{{ $value ? 'Ya' : 'Tidak' }}</span>
                                        @else
                                            {{ Str::limit((string) $value, 70) }}
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('mindos.admin.content.edit', [$def['key'], $item->id]) }}" class="btn btn-sm btn-light-primary">Edit</a>
                                    <form method="POST" action="{{ route('mindos.admin.content.destroy', [$def['key'], $item->id]) }}" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($def['columns']) + 1 }}" class="text-center text-muted py-10">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-5">{{ $items->links() }}</div>
@endsection
