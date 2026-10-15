{{-- Page header: @include('mindos.partials.header', ['title' => ..., 'subtitle' => ..., 'chip' => ...]) + optional @section-free actions via $actions (HTML-safe Htmlable) --}}
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-6">
    <div class="min-w-0">
        @isset($chip)
            <span class="mos-chip mb-2">{{ $chip }}</span>
        @endisset
        <h1 class="fs-2 fw-bold text-gray-900 mb-1">{{ $title }}</h1>
        @isset($subtitle)
            <p class="text-muted fs-6 mb-0">{{ $subtitle }}</p>
        @endisset
    </div>
    @isset($actions)
        <div class="d-flex flex-wrap gap-2">{{ $actions }}</div>
    @endisset
</div>

@if ($errors->any())
    <div class="alert alert-danger d-flex gap-3 align-items-start mb-6" role="alert">
        <i class="ki-outline ki-information-5 fs-2 text-danger"></i>
        <div>
            <div class="fw-bold mb-1">Periksa kembali isian Anda</div>
            <ul class="mb-0 ps-4">
                @foreach (collect($errors->all())->unique()->take(5) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
