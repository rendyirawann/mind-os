<?php

namespace App\Http\Controllers\MindOs;

use App\Http\Controllers\Controller;
use App\Models\MindOs\CommunityComment;
use App\Models\MindOs\CommunityPost;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Komunitas. Comment and like totals are denormalised onto the post so the
 * feed is a single indexed query with no per-row COUNT().
 */
class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('kategori')->toString();

        $posts = CommunityPost::with('user:id,name,avatar')
            ->when(array_key_exists($category, CommunityPost::CATEGORIES), fn ($q) => $q->where('category', $category))
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $liked = DB::table('community_likes')
            ->where('user_id', $request->user()->getKey())
            ->whereIn('community_post_id', $posts->pluck('id'))
            ->pluck('community_post_id')
            ->flip();

        return view('mindos.community.index', compact('posts', 'liked', 'category'));
    }

    public function show(Request $request, CommunityPost $post): View
    {
        $post->load(['user:id,name,avatar', 'comments.user:id,name,avatar']);

        return view('mindos.community.show', [
            'post' => $post,
            'liked' => DB::table('community_likes')
                ->where('community_post_id', $post->id)
                ->where('user_id', $request->user()->getKey())
                ->exists(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(CommunityPost::CATEGORIES))],
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $post = $request->user()->communityPosts()->create($data + ['is_anonymous' => $request->boolean('is_anonymous')]);

        return redirect()->route('mindos.community.show', $post)->with('success', 'Postingan dibagikan ke komunitas.');
    }

    public function comment(Request $request, CommunityPost $post): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        DB::transaction(function () use ($request, $post, $data) {
            CommunityComment::create($data + ['community_post_id' => $post->id, 'user_id' => $request->user()->getKey()]);
            $post->increment('comments_count');
        });

        return back()->with('success', 'Komentar terkirim.');
    }

    public function like(Request $request, CommunityPost $post): RedirectResponse
    {
        $key = ['community_post_id' => $post->id, 'user_id' => $request->user()->getKey()];

        DB::transaction(function () use ($key, $post) {
            if (DB::table('community_likes')->where($key)->delete()) {
                $post->decrement('likes_count');
            } elseif (DB::table('community_likes')->insertOrIgnore($key)) {
                $post->increment('likes_count');
            }
        });

        return back();
    }

    public function destroy(Request $request, CommunityPost $post): RedirectResponse
    {
        abort_unless($post->user_id === $request->user()->getKey() || $request->user()->can('mindos.manage'), 403);
        $post->delete();

        return redirect()->route('mindos.community.index')->with('success', 'Postingan dihapus.');
    }

    public function pin(Request $request, CommunityPost $post): RedirectResponse
    {
        abort_unless($request->user()->can('mindos.manage'), 403);
        $post->update(['is_pinned' => ! $post->is_pinned]);

        return back()->with('success', $post->is_pinned ? 'Postingan disematkan.' : 'Sematan dilepas.');
    }
}
