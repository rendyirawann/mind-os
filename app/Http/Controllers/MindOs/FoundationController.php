<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\FoundationModule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FoundationController extends Controller
{
    public function index(Request $request): View
    {
        $modules = FoundationModule::published()
            ->get(['id', 'slug', 'category', 'title', 'summary', 'duration_minutes', 'sort_order']);

        $done = DB::table('module_progress')
            ->where('user_id', $request->user()->getKey())
            ->pluck('foundation_module_id')
            ->flip();

        return view('mindos.foundation.index', [
            'groups' => $modules->groupBy('category'),
            'done' => $done,
            'total' => $modules->count(),
        ]);
    }

    public function show(Request $request, FoundationModule $module): View
    {
        abort_unless($module->is_published, 404);

        $siblings = FoundationModule::published()->get(['id', 'slug', 'title', 'sort_order']);
        $index = $siblings->search(fn ($m) => $m->id === $module->id);

        return view('mindos.foundation.show', [
            'module' => $module,
            'isDone' => DB::table('module_progress')
                ->where('user_id', $request->user()->getKey())
                ->where('foundation_module_id', $module->id)
                ->exists(),
            'prev' => $index > 0 ? $siblings[$index - 1] : null,
            'next' => $siblings[$index + 1] ?? null,
        ]);
    }

    public function complete(Request $request, FoundationModule $module): RedirectResponse
    {
        DB::table('module_progress')->insertOrIgnore([
            'user_id' => $request->user()->getKey(),
            'foundation_module_id' => $module->id,
            'completed_at' => now(),
        ]);

        $next = FoundationModule::published()->where('sort_order', '>', $module->sort_order)->first(['slug']);

        return $next
            ? redirect()->route('mindos.foundation.show', $next)->with('success', 'Modul selesai. Lanjut ke modul berikutnya!')
            : redirect()->route('mindos.bug-detector.index')->with('success', 'Selamat! Modul Foundation selesai. Sekarang isi Cek Kesehatan Pikiran.');
    }
}
