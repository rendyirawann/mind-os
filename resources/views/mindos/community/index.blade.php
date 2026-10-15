@extends('backend.layout.app')

@section('title', 'Komunitas')

@section('content')
    @include('mindos.partials.header', [
        'chip' => 'I · Komunitas',
        'title' => 'Komunitas Mind OS',
        'subtitle' => 'Ruang aman untuk berbagi perjalanan, bertanya, dan merayakan kemenangan kecil. Saling hormati, jaga kerahasiaan.',
    ])

    <div class="row g-6">
        <div class="col-xl-8 order-2 order-xl-1">
            <div class="d-flex flex-wrap gap-2 mb-5">
                <a href="{{ route('mindos.community.index') }}" class="btn btn-sm {{ $category === '' ? 'btn-primary' : 'btn-light' }}">Semua</a>
                @foreach (\App\Models\MindOs\CommunityPost::CATEGORIES as $key => $label)
                    <a href="{{ route('mindos.community.index', ['kategori' => $key]) }}" class="btn btn-sm {{ $category === $key ? 'btn-primary' : 'btn-light' }}">{{ $label }}</a>
                @endforeach
            </div>

            @forelse ($posts as $post)
                <article class="mos-card p-5 mb-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @if ($post->is_anonymous)
                            <span class="symbol symbol-35px"><span class="symbol-label bg-light-primary text-primary fw-bold">?</span></span>
                        @else
                            <x-avatar :user="$post->user" :size="35" />
                        @endif
                        <div class="min-w-0">
                            <div class="fw-bold text-gray-900 fs-7">{{ $post->author_name }}</div>
                            <div class="fs-8 text-muted">{{ $post->created_at->diffForHumans() }} · {{ \App\Models\MindOs\CommunityPost::CATEGORIES[$post->category] ?? $post->category }}</div>
                        </div>
                        @if ($post->is_pinned)
                            <span class="badge badge-light-warning ms-auto">Disematkan</span>
                        @endif
                    </div>
                    <a href="{{ route('mindos.community.show', $post) }}" class="fs-5 fw-bold text-gray-900 text-hover-primary d-block mb-2">{{ $post->title }}</a>
                    <p class="text-gray-700 fs-7 mb-4">{{ Str::limit($post->body, 220) }}</p>
                    <div class="d-flex gap-4 fs-7">
                        <form method="POST" action="{{ route('mindos.community.like', $post) }}">
                            @csrf
                            <button class="btn btn-sm btn-link p-0 {{ isset($liked[$post->id]) ? 'text-danger' : 'text-gray-600' }}">
                                <i class="ki-{{ isset($liked[$post->id]) ? 'solid' : 'outline' }} ki-heart fs-4 {{ isset($liked[$post->id]) ? 'text-danger' : '' }}"></i> {{ $post->likes_count }}
                            </button>
                        </form>
                        <a href="{{ route('mindos.community.show', $post) }}" class="text-gray-600"><i class="ki-outline ki-message-text-2 fs-4"></i> {{ $post->comments_count }} komentar</a>
                    </div>
                </article>
            @empty
                <p class="text-muted">Belum ada postingan di kategori ini. Jadilah yang pertama!</p>
            @endforelse
            {{ $posts->links() }}
        </div>

        <div class="col-xl-4 order-1 order-xl-2">
            <form method="POST" action="{{ route('mindos.community.store') }}" class="card card-flush shadow-sm position-sticky" style="top: 90px">
                @csrf
                <div class="card-body">
                    <h2 class="fs-5 fw-bold mb-4">Bagikan sesuatu</h2>
                    <select name="category" class="form-select mb-3" aria-label="Kategori" required>
                        @foreach (\App\Models\MindOs\CommunityPost::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input name="title" class="form-control mb-3" maxlength="150" placeholder="Judul" aria-label="Judul" value="{{ old('title') }}" required />
                    <textarea name="body" rows="5" class="form-control mb-3" placeholder="Ceritakan..." aria-label="Isi" required>{{ old('body') }}</textarea>
                    <label class="form-check form-check-sm mb-4">
                        <input type="checkbox" name="is_anonymous" value="1" class="form-check-input" @checked(old('is_anonymous')) />
                        <span class="form-check-label">Posting sebagai anonim</span>
                    </label>
                    <button class="btn btn-primary w-100">Posting</button>
                </div>
            </form>
        </div>
    </div>
@endsection
