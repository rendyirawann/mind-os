{{-- R1 → R6 strip, coloured by modality. Optional $counts (stage => total). --}}
<div class="mos-flow" role="list" aria-label="Alur metode 6R">
    @foreach (\App\Support\SixR::STAGES as $code => $stage)
        <span role="listitem" class="d-inline-flex flex-column align-items-center gap-1">
            <span class="mos-r mos-r--{{ $stage['modality'] }}" title="{{ $stage['name'] }} — {{ \App\Support\SixR::MODALITIES[$stage['modality']]['label'] }}">{{ $code }}</span>
            @isset($counts)
                <span class="fs-8 text-muted">{{ (int) ($counts[$code] ?? 0) }}×</span>
            @endisset
        </span>
        @unless ($loop->last)
            <span class="mos-flow__arrow" aria-hidden="true">→</span>
        @endunless
    @endforeach
</div>
<div class="d-flex flex-wrap gap-4 mt-3 fs-8 text-muted">
    <span><span class="mos-r mos-r--web" style="min-width:12px;height:12px;padding:0"></span> Web App</span>
    <span><span class="mos-r mos-r--online" style="min-width:12px;height:12px;padding:0"></span> Online Live</span>
    <span><span class="mos-r mos-r--offline" style="min-width:12px;height:12px;padding:0"></span> Tatap Muka</span>
</div>
