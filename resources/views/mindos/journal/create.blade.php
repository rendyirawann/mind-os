@extends('backend.layout.app')

@section('title', 'Tulis Jurnal Recognize')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'R1 · Recognize',
        'title' => 'Tulis Jurnal Recognize',
        'subtitle' => 'Tulis apa adanya. Jurnal ini hanya bisa dibaca oleh Anda.',
    ])

    <form method="POST" action="{{ route('mindos.journal.store') }}" class="card card-flush shadow-sm">
        @csrf
        <div class="card-body p-5 p-lg-8">
            <div class="row g-5">
                <div class="col-lg-8">
                    <label class="form-label required" for="title">Judul singkat</label>
                    <input id="title" name="title" class="form-control" maxlength="150" value="{{ old('title') }}" placeholder="Contoh: Panik sebelum meeting" required />
                </div>
                <div class="col-lg-4">
                    <label class="form-label" for="mind_bug_id">Pola pikiran yang muncul</label>
                    <select id="mind_bug_id" name="mind_bug_id" class="form-select">
                        <option value="">Belum tahu</option>
                        @foreach ($bugs as $bug)
                            <option value="{{ $bug->id }}" @selected(old('mind_bug_id') == $bug->id)>{{ $bug->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-6">
                    <label class="form-label required" for="situation">1. Situasi — apa yang terjadi?</label>
                    <textarea id="situation" name="situation" rows="4" class="form-control" required>{{ old('situation') }}</textarea>
                </div>
                <div class="col-lg-6">
                    <label class="form-label required" for="thought">2. Pikiran otomatis — apa yang terlintas?</label>
                    <textarea id="thought" name="thought" rows="4" class="form-control" required>{{ old('thought') }}</textarea>
                </div>
                <div class="col-lg-4">
                    <label class="form-label required" for="feeling">3. Perasaan</label>
                    <input id="feeling" name="feeling" class="form-control" maxlength="100" list="feelings" value="{{ old('feeling') }}" required />
                    <datalist id="feelings">
                        @foreach (['Cemas', 'Takut', 'Marah', 'Sedih', 'Malu', 'Bersalah', 'Kecewa', 'Hampa', 'Kesal'] as $f)
                            <option value="{{ $f }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-lg-4">
                    <label class="form-label" for="body_response">4. Respons tubuh</label>
                    <input id="body_response" name="body_response" class="form-control" maxlength="255" value="{{ old('body_response') }}" placeholder="Dada sesak, tangan dingin..." />
                </div>
                <div class="col-lg-4">
                    <label class="form-label required" for="intensity">Intensitas: <strong id="intensityOut">{{ old('intensity', 5) }}</strong>/10</label>
                    <input id="intensity" type="range" name="intensity" min="1" max="10" value="{{ old('intensity', 5) }}" class="form-range"
                        oninput="document.getElementById('intensityOut').textContent=this.value" />
                </div>
                <div class="col-12">
                    <label class="form-label" for="insight">Insight / apa yang saya sadari</label>
                    <textarea id="insight" name="insight" rows="3" class="form-control">{{ old('insight') }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('mindos.journal.index') }}" class="btn btn-light">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Jurnal</button>
        </div>
    </form>
@endsection
