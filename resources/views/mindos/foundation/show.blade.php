@extends('backend.layout.app')

@section('title', $module->title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-9 col-xxl-8">
            <a href="{{ route('mindos.foundation.index') }}" class="fs-7 fw-semibold d-inline-block mb-4">← Semua modul</a>

            <article class="card card-flush shadow-sm">
                <div class="card-body p-6 p-lg-12">
                    <span class="mos-chip mb-3">{{ $module->category_label }} · {{ $module->duration_minutes }} menit</span>
                    <h1 class="fs-2hx fw-bolder text-gray-900 mb-3">{{ $module->title }}</h1>
                    <p class="fs-5 text-gray-600 mb-8">{{ $module->summary }}</p>
                    <div class="mos-prose">{{ $module->content }}</div>

                    <div class="separator my-8"></div>

                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between align-items-sm-center">
                        <div class="d-flex gap-2">
                            @if ($prev)
                                <a href="{{ route('mindos.foundation.show', $prev->slug) }}" class="btn btn-light btn-sm">← Sebelumnya</a>
                            @endif
                            @if ($next)
                                <a href="{{ route('mindos.foundation.show', $next->slug) }}" class="btn btn-light btn-sm">Berikutnya →</a>
                            @endif
                        </div>
                        @if ($isDone)
                            <span class="badge badge-light-success py-3 px-4 fs-7">Modul sudah selesai ✓</span>
                        @else
                            <form method="POST" action="{{ route('mindos.foundation.complete', $module) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary">Tandai selesai & lanjut</button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        </div>
    </div>
@endsection
