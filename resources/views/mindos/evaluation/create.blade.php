@extends('backend.layout.app')

@section('title', 'Isi Evaluasi 30 Hari')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'J · Evaluasi',
        'title' => 'Evaluasi 30 Hari',
        'subtitle' => 'Jujur pada diri sendiri. Hasil apa pun adalah data untuk langkah berikutnya.',
    ])

    <form method="POST" action="{{ route('mindos.evaluation.store') }}" class="card card-flush shadow-sm">
        @csrf
        <div class="card-body p-5 p-lg-8">
            <div class="row g-5">
                <div class="col-lg-4">
                    <label class="form-label" for="action_plan_id">Action Plan</label>
                    <select id="action_plan_id" name="action_plan_id" class="form-select">
                        <option value="">—</option>
                        @foreach ($plans as $p)
                            <option value="{{ $p->id }}" @selected(old('action_plan_id') == $p->id)>{{ $p->title }} ({{ $p->status }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <label class="form-label required" for="score_before">Beban masalah 30 hari lalu (0–10)</label>
                    <input id="score_before" type="number" min="0" max="10" name="score_before" class="form-control"
                        value="{{ old('score_before', $firstAssessment !== null ? (int) round($firstAssessment / 10) : '') }}" required />
                </div>
                <div class="col-sm-6 col-lg-4">
                    <label class="form-label required" for="score_after">Beban masalah sekarang (0–10)</label>
                    <input id="score_after" type="number" min="0" max="10" name="score_after" class="form-control" value="{{ old('score_after') }}" required />
                </div>
                <div class="col-12">
                    <label class="form-label required" for="reflection">Apa yang berubah? Apa yang belum?</label>
                    <textarea id="reflection" name="reflection" rows="4" class="form-control" required>{{ old('reflection') }}</textarea>
                </div>
                <div class="col-12">
                    <span class="form-label required d-block mb-3">Langkah berikutnya</span>
                    <div class="row g-3">
                        @foreach (\App\Models\MindOs\Evaluation::NEXT_STEPS as $key => $label)
                            <div class="col-md-6 col-xl-3">
                                <label class="mos-card d-flex gap-3 p-4 h-100 is-hoverable" style="cursor:pointer">
                                    <input type="radio" class="form-check-input mt-1" name="next_step" value="{{ $key }}" @checked(old('next_step', 'self_mastery') === $key) required />
                                    <span class="fw-semibold">{{ $label }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <button class="btn btn-primary">Simpan Evaluasi</button>
        </div>
    </form>
@endsection
