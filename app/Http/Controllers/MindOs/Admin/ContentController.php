<?php

namespace App\Http\Controllers\MindOs\Admin;

use App\Http\Controllers\Controller;
use App\Models\MindOs\AudioTrack;
use App\Models\MindOs\FoundationModule;
use App\Models\MindOs\MindBug;
use App\Models\MindOs\MindService;
use App\Support\SixR;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * One CRUD for the editable Mind OS catalogues (modules, audio, services,
 * bugs). Each type is described by a field schema, so the list and form
 * views are shared.
 */
class ContentController extends Controller
{
    private function types(): array
    {
        $stages = array_combine(SixR::codes(), array_map(fn ($c) => $c . ' — ' . SixR::STAGES[$c]['name'], SixR::codes()));

        return [
            'modules' => [
                'label' => 'Modul Foundation',
                'model' => FoundationModule::class,
                'columns' => ['title' => 'Judul', 'category_label' => 'Kategori', 'duration_minutes' => 'Menit', 'is_published' => 'Terbit'],
                'fields' => [
                    'title' => ['Judul', 'text', ['required', 'string', 'max:150']],
                    'category' => ['Kategori', 'select', ['required', Rule::in(array_keys(FoundationModule::CATEGORIES))], FoundationModule::CATEGORIES],
                    'summary' => ['Ringkasan', 'textarea', ['required', 'string', 'max:500']],
                    'content' => ['Isi Modul', 'textarea-lg', ['required', 'string', 'max:20000']],
                    'duration_minutes' => ['Durasi (menit)', 'number', ['required', 'integer', 'min:1', 'max:600']],
                    'sort_order' => ['Urutan', 'number', ['required', 'integer', 'min:0']],
                    'is_published' => ['Terbitkan', 'switch', ['boolean']],
                ],
            ],
            'audio' => [
                'label' => 'Audio Guided Healing',
                'model' => AudioTrack::class,
                'columns' => ['title' => 'Judul', 'stage' => 'Tahap', 'duration_label' => 'Durasi', 'is_published' => 'Terbit'],
                'fields' => [
                    'title' => ['Judul', 'text', ['required', 'string', 'max:150']],
                    'stage' => ['Tahap 6R', 'select', ['required', Rule::in(SixR::codes())], $stages],
                    'description' => ['Deskripsi', 'textarea', ['required', 'string', 'max:500']],
                    'duration_seconds' => ['Durasi (detik)', 'number', ['required', 'integer', 'min:30', 'max:14400']],
                    'audio_url' => ['URL Audio (mp3)', 'url', ['nullable', 'url', 'max:255']],
                    'script' => ['Naskah Panduan', 'textarea-lg', ['nullable', 'string', 'max:20000']],
                    'sort_order' => ['Urutan', 'number', ['required', 'integer', 'min:0']],
                    'is_published' => ['Terbitkan', 'switch', ['boolean']],
                ],
            ],
            'services' => [
                'label' => 'Layanan Online & Tatap Muka',
                'model' => MindService::class,
                'columns' => ['name' => 'Nama', 'modality_label' => 'Modalitas', 'stage' => 'Tahap', 'price_label' => 'Harga', 'is_active' => 'Aktif'],
                'fields' => [
                    'name' => ['Nama Layanan', 'text', ['required', 'string', 'max:150']],
                    'modality' => ['Modalitas', 'select', ['required', Rule::in(array_keys(MindService::MODALITIES))], MindService::MODALITIES],
                    'stage' => ['Tahap 6R', 'select', ['nullable', Rule::in(SixR::codes())], ['' => '— Tidak spesifik —'] + $stages],
                    'description' => ['Deskripsi', 'textarea', ['required', 'string', 'max:600']],
                    'price' => ['Harga (Rp)', 'number', ['required', 'integer', 'min:0']],
                    'duration_minutes' => ['Durasi (menit)', 'number', ['required', 'integer', 'min:15', 'max:600']],
                    'capacity' => ['Kapasitas', 'number', ['required', 'integer', 'min:1', 'max:1000']],
                    'is_group' => ['Sesi Grup', 'switch', ['boolean']],
                    'sort_order' => ['Urutan', 'number', ['required', 'integer', 'min:0']],
                    'is_active' => ['Aktif', 'switch', ['boolean']],
                ],
            ],
            'bugs' => [
                'label' => '7 Pola Pikiran',
                'model' => MindBug::class,
                'columns' => ['code' => 'Kode', 'name' => 'Nama', 'tagline' => 'Tagline'],
                'fields' => [
                    'code' => ['Kode', 'text', ['required', 'string', 'max:10']],
                    'name' => ['Nama Pola', 'text', ['required', 'string', 'max:150']],
                    'tagline' => ['Tagline', 'text', ['required', 'string', 'max:255']],
                    'description' => ['Deskripsi', 'textarea', ['required', 'string', 'max:3000']],
                    'symptoms' => ['Gejala', 'textarea', ['required', 'string', 'max:3000']],
                    'reframe_hint' => ['Kunci Reframe', 'textarea', ['required', 'string', 'max:1000']],
                    'color' => ['Warna', 'select', ['required', Rule::in(['primary', 'info', 'success', 'warning', 'danger'])], ['primary' => 'Teal', 'info' => 'Biru', 'success' => 'Hijau', 'warning' => 'Oranye', 'danger' => 'Merah']],
                    'sort_order' => ['Urutan', 'number', ['required', 'integer', 'min:0']],
                ],
            ],
        ];
    }

    private function type(string $type): array
    {
        $types = $this->types();
        abort_unless(isset($types[$type]), 404);

        return $types[$type] + ['key' => $type];
    }

    public function index(string $type): View
    {
        $def = $this->type($type);
        $model = $def['model'];

        return view('mindos.admin.content-index', [
            'def' => $def,
            'types' => collect($this->types())->map(fn ($t) => $t['label']),
            'items' => $model::query()->orderBy('sort_order')->paginate(25),
        ]);
    }

    public function create(string $type): View
    {
        $def = $this->type($type);

        return view('mindos.admin.content-form', ['def' => $def, 'item' => new $def['model']()]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $def = $this->type($type);
        $item = new $def['model']();
        $this->fill($request, $def, $item);

        return redirect()->route('mindos.admin.content.index', $type)->with('success', $def['label'] . ' ditambahkan.');
    }

    public function edit(string $type, int $id): View
    {
        $def = $this->type($type);

        return view('mindos.admin.content-form', ['def' => $def, 'item' => $def['model']::findOrFail($id)]);
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $def = $this->type($type);
        $this->fill($request, $def, $def['model']::findOrFail($id));

        return redirect()->route('mindos.admin.content.index', $type)->with('success', $def['label'] . ' diperbarui.');
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $def = $this->type($type);

        try {
            $def['model']::findOrFail($id)->delete();
        } catch (\Illuminate\Database\QueryException) {
            return back()->with('error', 'Data masih dipakai (misalnya oleh booking) — nonaktifkan saja.');
        }

        Cache::forget('mindos.modules.count');
        Cache::forget('mindos.landing');

        return back()->with('success', 'Data dihapus.');
    }

    private function fill(Request $request, array $def, Model $item): void
    {
        $rules = array_map(fn ($f) => $f[2], $def['fields']);
        $data = $request->validate($rules);

        foreach ($def['fields'] as $name => $field) {
            if ($field[1] === 'switch') {
                $data[$name] = $request->boolean($name);
            }
        }

        if (array_key_exists('stage', $data) && $data['stage'] === '') {
            $data['stage'] = null;
        }

        if (in_array('slug', $item->getFillable(), true) && ! $item->exists) {
            $base = Str::slug($data['title'] ?? $data['name'] ?? Str::random(8));
            $slug = $base;
            for ($i = 2; $item::where('slug', $slug)->exists(); $i++) {
                $slug = $base . '-' . $i;
            }
            $data['slug'] = $slug;
        }

        $item->fill($data)->save();
        Cache::forget('mindos.modules.count');
        Cache::forget('mindos.landing');
    }
}
