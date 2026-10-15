@extends('backend.layout.app')

@section('title', ($item->exists ? 'Edit ' : 'Tambah ') . $def['label'])

@section('content')
    @include('mindos.partials.header', [
        'chip' => $def['label'],
        'title' => $item->exists ? 'Edit data' : 'Tambah data',
    ])

    <form method="POST" action="{{ $item->exists ? route('mindos.admin.content.update', [$def['key'], $item->id]) : route('mindos.admin.content.store', $def['key']) }}" class="card card-flush shadow-sm">
        @csrf
        @if ($item->exists) @method('PUT') @endif
        <div class="card-body p-5 p-lg-8">
            <div class="row g-5">
                @foreach ($def['fields'] as $name => $field)
                    @php
                        [$label, $type] = $field;
                        $options = $field[3] ?? [];
                        $value = old($name, $item->{$name});
                        $required = in_array('required', $field[2], true);
                        $wide = in_array($type, ['textarea', 'textarea-lg'], true);
                    @endphp
                    <div class="{{ $wide ? 'col-12' : 'col-md-6 col-xl-4' }}">
                        @if ($type === 'switch')
                            <label class="form-check form-switch form-check-custom mt-8">
                                <input type="checkbox" class="form-check-input" name="{{ $name }}" value="1" @checked($item->exists ? (bool) $value : true) />
                                <span class="form-check-label fw-semibold">{{ $label }}</span>
                            </label>
                        @else
                            <label class="form-label {{ $required ? 'required' : '' }}" for="f-{{ $name }}">{{ $label }}</label>
                            @if ($type === 'select')
                                <select id="f-{{ $name }}" name="{{ $name }}" class="form-select" @required($required)>
                                    @foreach ($options as $optValue => $optLabel)
                                        <option value="{{ $optValue }}" @selected((string) $value === (string) $optValue)>{{ $optLabel }}</option>
                                    @endforeach
                                </select>
                            @elseif ($wide)
                                <textarea id="f-{{ $name }}" name="{{ $name }}" rows="{{ $type === 'textarea-lg' ? 12 : 3 }}" class="form-control" @required($required)>{{ $value }}</textarea>
                            @else
                                <input id="f-{{ $name }}" type="{{ $type }}" name="{{ $name }}" class="form-control" value="{{ $value ?? ($type === 'number' ? 0 : '') }}" @required($required) />
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('mindos.admin.content.index', $def['key']) }}" class="btn btn-light">Batal</a>
            <button class="btn btn-primary">Simpan</button>
        </div>
    </form>
@endsection
