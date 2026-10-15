<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\Journal;
use App\Models\MindOs\MindBug;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Jurnal Recognize — the R1 habit: situation → thought → feeling → body. */
class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $uid = $request->user()->getKey();

        $journals = Journal::with('bug:id,name,code,color')
            ->where('user_id', $uid)
            ->when($request->filled('bug'), fn ($q) => $q->where('mind_bug_id', $request->integer('bug')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('mindos.journal.index', [
            'journals' => $journals,
            'bugs' => MindBug::cachedAll(),
            'avgIntensity' => round((float) Journal::where('user_id', $uid)->where('created_at', '>=', now()->subDays(30))->avg('intensity'), 1),
        ]);
    }

    public function create(): View
    {
        return view('mindos.journal.create', ['bugs' => MindBug::cachedAll()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'situation' => ['required', 'string', 'max:2000'],
            'thought' => ['required', 'string', 'max:2000'],
            'feeling' => ['required', 'string', 'max:100'],
            'body_response' => ['nullable', 'string', 'max:255'],
            'intensity' => ['required', 'integer', 'between:1,10'],
            'mind_bug_id' => ['nullable', Rule::exists('mind_bugs', 'id')],
            'insight' => ['nullable', 'string', 'max:2000'],
        ]);

        $request->user()->journals()->create($data);

        return redirect()->route('mindos.journal.index')->with('success', 'Jurnal tersimpan. Anda baru saja melatih Recognize (R1).');
    }

    public function show(Request $request, Journal $journal): View
    {
        abort_unless($journal->user_id === $request->user()->getKey(), 404);

        return view('mindos.journal.show', ['journal' => $journal->load('bug')]);
    }

    public function destroy(Request $request, Journal $journal): RedirectResponse
    {
        abort_unless($journal->user_id === $request->user()->getKey(), 404);
        $journal->delete();

        return redirect()->route('mindos.journal.index')->with('success', 'Jurnal dihapus.');
    }
}
