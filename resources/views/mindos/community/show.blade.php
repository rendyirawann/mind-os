@extends('backend.layout.app')

@section('title', $post->title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <a href="{{ route('mindos.community.index') }}" class="fs-7 fw-semibold d-inline-block mb-4">← Komunitas</a>

            <article class="card card-flush shadow-sm mb-6">
                <div class="card-body p-6 p-lg-10">
                    <div class="d-flex align-items-center gap-3 mb-5">
                        @unless ($post->is_anonymous)
                            <x-avatar :user="$post->user" :size="42" />
                        @endunless
                        <div>
                            <div class="fw-bold">{{ $post->author_name }}</div>
                            <div class="fs-8 text-muted">{{ $post->created_at->translatedFormat('d F Y H:i') }}</div>
                        </div>
                    </div>
                    <h1 class="fs-2x fw-bolder mb-5">{{ $post->title }}</h1>
                    <div class="mos-prose mb-6">{{ $post->body }}</div>

                    <div class="d-flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('mindos.community.like', $post) }}">
                            @csrf
                            <button class="btn btn-sm {{ $liked ? 'btn-light-danger' : 'btn-light' }}"><i class="ki-{{ $liked ? 'solid' : 'outline' }} ki-heart fs-5"></i> {{ $post->likes_count }}</button>
                        </form>
                        @can('mindos.manage')
                            <form method="POST" action="{{ route('mindos.community.pin', $post) }}">
                                @csrf
                                <button class="btn btn-sm btn-light-warning">{{ $post->is_pinned ? 'Lepas sematan' : 'Sematkan' }}</button>
                            </form>
                        @endcan
                        @if ($post->user_id === auth()->id() || auth()->user()->can('mindos.manage'))
                            <form method="POST" action="{{ route('mindos.community.destroy', $post) }}" onsubmit="return confirm('Hapus postingan ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-light-danger">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>

            <div class="card card-flush shadow-sm">
                <div class="card-body p-6">
                    <h2 class="fs-5 fw-bold mb-5">{{ $post->comments_count }} Komentar</h2>
                    @foreach ($post->comments as $comment)
                        <div class="d-flex gap-3 mb-5">
                            <x-avatar :user="$comment->user" :size="34" />
                            <div class="bg-light rounded-3 px-4 py-3 flex-grow-1">
                                <div class="fw-bold fs-7">{{ $comment->user->name ?? 'Anggota' }} <span class="text-muted fw-normal fs-8">· {{ $comment->created_at->diffForHumans() }}</span></div>
                                <div class="fs-7 text-gray-800" style="white-space:pre-line">{{ $comment->body }}</div>
                            </div>
                        </div>
                    @endforeach

                    <form method="POST" action="{{ route('mindos.community.comment', $post) }}">
                        @csrf
                        <textarea name="body" rows="3" class="form-control mb-3" placeholder="Tulis dukungan atau tanggapan..." aria-label="Komentar" required></textarea>
                        <button class="btn btn-primary btn-sm">Kirim komentar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
