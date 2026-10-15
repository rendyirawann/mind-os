@php
    $faqs = [
        ['Apa itu Mind OS?', 'Mind OS adalah layanan hipnoterapi dan pendampingan kesehatan mental. Anda belajar mengenali pola pikiran dan luka emosional yang menghambat (kami menyebutnya 7 Pola Pikiran), lalu memulihkannya dengan metode 6R: Recognize, Reveal, Release, Reframe, Reprogram, dan Reinforce.'],
        ['Apakah Mind OS bisa digunakan gratis?', 'Ya. Akun gratis memberi akses Modul Foundation, Cek Kesehatan Pikiran, Jurnal Recognize, Audio Guided Healing, 6R Tracker, Action Plan 30 Hari, dan Komunitas. Sesi Online Live dan Tatap Muka bersama Practitioner bersifat berbayar.'],
        ['Apa saja 7 Pola Pikiran pikiran?', 'Takut Gagal, Tidak Layak, People Pleaser, Overthinking, Luka Masa Lalu, Kontrol & Perfeksionis, serta Kelangkaan. Cek Kesehatan Pikiran mengukur seberapa kuat masing-masing pola aktif dalam diri Anda.'],
        ['Kapan saya perlu sesi Online Live atau Tatap Muka?', 'Cek Kesehatan Pikiran memberi rekomendasi otomatis. Level ringan cukup dengan Web App, level sedang disarankan Online Live (Reframe & Reprogram Guided), dan level berat atau luka masa lalu disarankan Tatap Muka (Release & Reprogram Deep).'],
        ['Apakah hipnoterapi Mind OS aman?', 'Sesi dipandu Practitioner bersertifikat dengan kode etik dan supervisi. Anda tetap sadar dan memegang kendali penuh. Mind OS bukan pengganti layanan medis atau psikiatri untuk kondisi darurat.'],
        ['Bagaimana cara menjadi Practitioner Mind OS?', 'Selesaikan Modul Foundation (Alumni Foundation), lalu ikuti Practitioner Program: 24 jam teori & praktik, praktik 3 klien, laporan kasus, ujian dan sertifikasi. Jenjang berikutnya adalah Master Practitioner dan Trainer berlisensi.'],
    ];

    $bugFallback = [
        ['P1', 'Takut Gagal', 'Saya harus aman dulu baru bergerak', 'danger'],
        ['P2', 'Merasa Tidak Layak', 'Saya tidak cukup baik', 'warning'],
        ['P3', 'People Pleaser', 'Saya harus membuat semua orang senang', 'info'],
        ['P4', 'Overthinking', 'Pikiran saya tidak bisa berhenti', 'primary'],
        ['P5', 'Luka Masa Lalu', 'Yang dulu masih terasa sekarang', 'danger'],
        ['P6', 'Kontrol & Perfeksionis', 'Semua harus sesuai rencana', 'success'],
        ['P7', 'Merasa Selalu Kurang', 'Tidak pernah cukup', 'warning'],
    ];

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => url('/') . '#organization',
                'name' => $brand['name'],
                'url' => url('/'),
                'logo' => $brand['logo_url'],
                'description' => $brand['description'],
                'slogan' => $brand['tagline'],
            ],
            [
                '@type' => 'WebSite',
                '@id' => url('/') . '#website',
                'url' => url('/'),
                'name' => $brand['name'],
                'inLanguage' => 'id-ID',
                'publisher' => ['@id' => url('/') . '#organization'],
            ],
            [
                '@type' => 'ItemList',
                'name' => 'Layanan Hipnoterapi Mind OS',
                'itemListElement' => $services->flatten()->values()->map(fn ($s, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'item' => [
                        '@type' => 'Service',
                        'name' => $s->name,
                        'description' => $s->description,
                        'serviceType' => 'Hipnoterapi ' . $s->modality_label,
                        'provider' => ['@id' => url('/') . '#organization'],
                        'areaServed' => 'ID',
                        'offers' => ['@type' => 'Offer', 'price' => (string) $s->price, 'priceCurrency' => 'IDR'],
                    ],
                ])->all(),
            ],
            ...$programs->map(fn ($p) => [
                '@type' => 'Course',
                'name' => $p->name,
                'description' => $p->description,
                'provider' => ['@id' => url('/') . '#organization'],
                'educationalCredentialAwarded' => \App\Models\MindOs\CertificationProgram::LEVELS[$p->level] ?? $p->name,
                'hasCourseInstance' => ['@type' => 'CourseInstance', 'courseMode' => 'blended', 'courseWorkload' => 'PT' . $p->hours . 'H'],
                'offers' => ['@type' => 'Offer', 'price' => (string) $p->price, 'priceCurrency' => 'IDR', 'category' => 'Paid'],
            ])->all(),
            [
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn ($f) => [
                    '@type' => 'Question',
                    'name' => $f[0],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
                ], $faqs),
            ],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" dir="ltr">

<head>
    @section('robots', $brand['robots'])
    @include('partials.meta')

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="preload" as="style"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Nunito:wght@400;600;700&display=swap" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Nunito:wght@400;600;700&display=swap" />
    <link rel="preload" as="image" href="{{ asset('assets/media/mindos/hero.jpg') }}" media="(min-width: 900px)" />
    <link rel="preload" as="image" href="{{ asset('assets/media/mindos/hero-sm.jpg') }}" media="(max-width: 899px)" />
    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}?v={{ filemtime(public_path('assets/css/landing.css')) }}" />
    <link rel="alternate" hreflang="id-ID" href="{{ url('/') }}" />
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}" />

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
</head>

<body>
    <a class="skip-link" href="#main">Lewati ke konten utama</a>

    <!-- ================= Header ================= -->
    <header class="site-header">
        <div class="inner site-header__bar">
            <a href="{{ url('/') }}" class="brand" aria-label="{{ $brand['name'] }} — beranda">
                <img src="{{ $brand['logo_url'] }}" alt="" width="40" height="40" />
                <span>{{ $brand['name'] }}<small>{{ $brand['tagline'] }}</small></span>
            </a>

            <button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" aria-label="Buka menu">
                <span></span>
            </button>

            <nav id="site-nav" class="nav" aria-label="Navigasi utama">
                <a href="#ekosistem">Ekosistem</a>
                <a href="#pola-pikiran">7 Pola Pikiran</a>
                <a href="#metode-6r">Metode 6R</a>
                <a href="#journey">Alur</a>
                <a href="#layanan">Layanan</a>
                <a href="#sertifikasi">Sertifikasi</a>
                <a href="#faq">FAQ</a>
                <div class="nav__cta">
                    @auth
                        <a href="{{ route('mindos.journey') }}" class="btn btn--primary">Buka Web App</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn--ghost">Masuk</a>
                        <a href="{{ route('register') }}" class="btn btn--primary">Daftar Gratis</a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <main id="main">
        <!-- ================= Hero ================= -->
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero__photo" role="img" aria-label="Seseorang bermeditasi dengan tenang saat matahari terbit"></div>
            <div class="inner hero__layout">
                <div>
                    <span class="chip">Hipnoterapi &amp; Kesehatan Mental · 15 Tahun Pengalaman</span>
                    <h1 id="hero-title">Pikiran <em>tenang</em>, hati lebih ringan, hidup lebih utuh.</h1>
                    <p class="hero__lead">
                        {{ $brand['name'] }} mendampingi Anda merawat kesehatan pikiran lewat hipnoterapi dan <strong>metode 6R</strong>
                        — mulai dari latihan mandiri gratis, sesi online, hingga sesi tatap muka bersama praktisi bersertifikat.
                    </p>
                    <div class="hero__actions">
                        <a href="{{ auth()->check() ? route('mindos.bug-detector.index') : route('register') }}" class="btn btn--primary">Mulai Cek Kesehatan Pikiran</a>
                        <a href="#layanan" class="btn btn--ghost">Lihat Layanan</a>
                    </div>
                    <ul class="hero__trust">
                        <li><strong>Gratis</strong><span>untuk memulai</span></li>
                        <li><strong>Aman</strong><span>dipandu praktisi</span></li>
                        <li><strong>Privat</strong><span>jurnal hanya untuk Anda</span></li>
                    </ul>
                </div>

                <figure class="calm reveal">
                    <figcaption class="calm__card">
                        <p class="calm__quote">“Yang dulu melindungi Anda, kini boleh dilepaskan dengan lembut.”</p>
                        <div class="calm__steps">
                            @foreach (\App\Support\SixR::STAGES as $code => $stage)
                                <span title="{{ $stage['name'] }}">{{ $stage['name'] === 'Reprogram — Guided' ? 'Reprogram' : ($stage['name'] === 'Reprogram — Deep' ? 'Deep' : $stage['name']) }}</span>
                            @endforeach
                        </div>
                    </figcaption>
                </figure>
            </div>
        </section>

        <!-- ================= Ekosistem ================= -->
        <section id="ekosistem" class="section" aria-labelledby="eco-title">
            <div class="inner">
                <div class="section__head reveal">
                    <span class="eyebrow">01 · Ekosistem Mind OS</span>
                    <h2 id="eco-title" class="section__title">Satu ekosistem, tiga jalur penyembuhan.</h2>
                    <p class="section__lead">Mulai mandiri di Web App, naik ke Online Live saat butuh pendampingan, dan Tatap Muka untuk proses yang paling dalam.</p>
                </div>

                <div class="eco">
                    <article class="eco__card reveal" style="--accent: var(--sage)">
                        <span class="eco__badge">Web App</span>
                        <h3>Self-Healing Mandiri</h3>
                        <p class="eco__price">Gratis untuk semua user</p>
                        <ul class="ticks">
                            <li><div>Modul Foundation
                                <ul><li>Filosofi Dasar · 5 Lapisan Diri · 7 Pola Pikiran</li></ul></div></li>
                            <li>Cek Kesehatan Pikiran &amp; hasil pemeriksaan</li>
                            <li>Jurnal Recognize</li>
                            <li>Audio Guided Healing</li>
                            <li>6R Tracker</li>
                            <li>Action Plan 30 Hari</li>
                            <li>Komunitas</li>
                        </ul>
                    </article>
                    <article class="eco__card reveal" style="--accent: var(--gold)">
                        <span class="eco__badge">Online Live</span>
                        <h3>Pendampingan Virtual</h3>
                        <p class="eco__price">Berbayar · dari mana saja</p>
                        <ul class="ticks">
                            <li>Workshop Foundation</li>
                            <li>Diskusi 7 Pola Pikiran</li>
                            <li>6R — Reframe</li>
                            <li>6R — Reprogram Guided</li>
                            <li>Group Coaching</li>
                            <li>Supervisi Practitioner</li>
                        </ul>
                    </article>
                    <article class="eco__card reveal" style="--accent: var(--rose)">
                        <span class="eco__badge">Tatap Muka</span>
                        <h3>Sesi Mendalam</h3>
                        <p class="eco__price">Berbayar · bersama Practitioner</p>
                        <ul class="ticks">
                            <li>Personal Session</li>
                            <li>6R — Release</li>
                            <li>Recovery Trauma</li>
                            <li>Relationship Couple</li>
                            <li>Sertifikasi Practitioner &amp; Master</li>
                            <li>Trainer &amp; Lisensi</li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        <!-- ================= 5 Lapisan ================= -->
        <section class="section section--photo" style="--photo: url('{{ asset('assets/media/mindos/mist.jpg') }}')" aria-labelledby="layers-title">
            <div class="inner layers">
                <div class="reveal">
                    <span class="eyebrow">02 · Filosofi Dasar</span>
                    <h2 id="layers-title" class="section__title">Kesehatan mental terbentuk dari lima lapisan diri.</h2>
                    <p class="section__lead">Kecemasan, trauma, dan pola yang berulang berakar di salah satu lapisan ini. Modul Foundation mengajarkan cara membaca kelima lapisan ini
                        @if (($modules['lapisan'] ?? 0) > 0) melalui {{ $modules['lapisan'] }} modul interaktif @endif
                        — gratis.</p>
                    <a href="{{ route('register') }}" class="btn btn--teal" style="margin-top:24px">Mulai Modul Foundation</a>
                </div>
                <div class="stack reveal" role="list" aria-label="5 Lapisan Diri">
                    @foreach ([
                        ['Emosi & Relasi', 'Cara kita merasa dan terhubung', '#f6dccb'],
                        ['Kebiasaan & Perilaku', 'Yang kita lakukan berulang', '#f3e8cc'],
                        ['Keyakinan Inti', 'Cara kita memandang diri & dunia', '#dbe8f3'],
                        ['Pikiran Bawah Sadar', 'Tempat hipnoterapi bekerja', '#d6ece5'],
                        ['Tubuh & Sistem Saraf', 'Tempat stres dan emosi tersimpan', '#e6e1f1'],
                    ] as $i => [$name, $desc, $color])
                        <div class="stack__layer" role="listitem" style="background: {{ $color }}">
                            <b>{{ 5 - $i }}</b>
                            <span>{{ $name }}<small>{{ $desc }}</small></span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ================= 7 Pola Pikiran ================= -->
        <section id="pola-pikiran" class="section" aria-labelledby="pola-title">
            <div class="inner">
                <div class="section__head section__head--center reveal">
                    <span class="eyebrow">03 · 7 Pola Pikiran</span>
                    <h2 id="pola-title" class="section__title">Pola pikiran mana yang sedang membebani Anda?</h2>
                    <p class="section__lead">Pola lama yang dulu melindungi, kini justru membuat lelah dan cemas. Cek Kesehatan Pikiran mengukur ketujuhnya dalam ±5 menit.</p>
                </div>

                <div class="grid grid--auto">
                    @forelse ($bugs as $bug)
                        <article class="card bug reveal">
                            <span class="bug__code c-{{ $bug->color }}">{{ $bug->code }}</span>
                            <h3>{{ $bug->name }}</h3>
                            <span class="bug__tag">“{{ $bug->tagline }}”</span>
                            <p style="margin:6px 0 0">{{ Str::limit($bug->description, 150) }}</p>
                        </article>
                    @empty
                        @foreach ($bugFallback as [$code, $name, $tagline, $color])
                            <article class="card bug reveal">
                                <span class="bug__code c-{{ $color }}">{{ $code }}</span>
                                <h3>{{ $name }}</h3>
                                <span class="bug__tag">“{{ $tagline }}”</span>
                            </article>
                        @endforeach
                    @endforelse
                    <article class="card bug reveal" style="border-color: var(--line); background: linear-gradient(160deg, rgba(214,178,122,.16), rgba(214,178,122,.03))">
                        <h3>Temukan pola pikiran dominan Anda</h3>
                        <p>Dapatkan hasil per pola dan rekomendasi: lanjut Web App, Online Live, atau Tatap Muka.</p>
                        <a href="{{ auth()->check() ? route('mindos.bug-detector.index') : route('register') }}" class="btn btn--primary" style="margin-top:auto;align-self:flex-start">Mulai Cek Kesehatan Pikiran</a>
                    </article>
                </div>
            </div>
        </section>

        <!-- ================= 6R ================= -->
        <section id="metode-6r" class="section section--photo" style="--photo: url('{{ asset('assets/media/mindos/lake.jpg') }}')" aria-labelledby="sixr-title">
            <div class="inner">
                <div class="section__head reveal">
                    <span class="eyebrow">04 · Metode 6R</span>
                    <h2 id="sixr-title" class="section__title">Recognize → Reinforce, di modalitas yang tepat.</h2>
                    <p class="section__lead">Setiap tahap dikerjakan di tempat yang paling efektif: Web App untuk kesadaran &amp; penguatan, Online Live untuk reframe &amp; reprogram terpandu, Tatap Muka untuk release &amp; reprogram mendalam.</p>
                </div>

                <ol class="sixr" style="list-style:none;margin:0;padding:0">
                    @foreach (\App\Support\SixR::STAGES as $code => $stage)
                        <li class="sixr__step reveal">
                            <span class="r r--{{ $stage['modality'] }}" style="align-self:flex-start">{{ $code }}</span>
                            <h3>{{ $stage['name'] }}</h3>
                            <p>{{ $stage['desc'] }}</p>
                            <span class="sixr__mod">{{ \App\Support\SixR::MODALITIES[$stage['modality']]['label'] }}</span>
                        </li>
                    @endforeach
                </ol>
                <div class="legend" aria-hidden="true">
                    <span><i class="r--web"></i> Web App (R1, R2, R6)</span>
                    <span><i class="r--online"></i> Online Live (R4, R5A)</span>
                    <span><i class="r--offline"></i> Offline / Tatap Muka (R3, R5B)</span>
                </div>
            </div>
        </section>

        <!-- ================= Journey ================= -->
        <section id="journey" class="section" aria-labelledby="journey-title">
            <div class="inner">
                <div class="section__head section__head--center reveal">
                    <span class="eyebrow">05 · Alur User</span>
                    <h2 id="journey-title" class="section__title">Perjalanan Anda, langkah demi langkah.</h2>
                    <p class="section__lead">Web App memandu Anda otomatis — Anda selalu tahu langkah berikutnya.</p>
                </div>
                <ol class="journey">
                    @foreach ([
                        ['A–B', 'Daftar & akses Web App', 'Buat akun gratis dan masuk ke dashboard Perjalanan Saya.'],
                        ['C', 'Ikuti Modul Foundation', 'Filosofi Dasar, 5 Lapisan Diri, dan 7 Pola Pikiran.'],
                        ['D–E', 'Isi Cek Kesehatan Pikiran', 'Dapatkan gambaran pola pikiran yang paling memengaruhi Anda.'],
                        ['F–H', 'Pilih jalur', 'Lanjut Web App, booking Online Live, atau Tatap Muka.'],
                        ['I', 'Action Plan & Komunitas', '30 hari tugas kecil harian dan dukungan sesama anggota.'],
                        ['J–K', 'Evaluasi 30 Hari', 'Ukur hasil: sudah berubah atau perlu pendekatan lain?'],
                        ['L–M', 'Self-Mastery / Konsultasi Ulang', 'Jadi alumni, atau ganti pendekatan bersama Practitioner.'],
                        ['N–P', 'Sertifikasi atau Komunitas', 'Lanjut jadi Practitioner/Master, atau tetap di komunitas.'],
                    ] as [$key, $title, $desc])
                        <li class="reveal">
                            <span class="journey__key">{{ $key }}</span>
                            <h3>{{ $title }}</h3>
                            <p>{{ $desc }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <!-- ================= Layanan ================= -->
        <section id="layanan" class="section section--tint" aria-labelledby="svc-title">
            <div class="inner">
                <div class="section__head reveal">
                    <span class="eyebrow">06 · Layanan</span>
                    <h2 id="svc-title" class="section__title">Pendampingan saat Anda membutuhkannya.</h2>
                    <p class="section__lead">Booking langsung dari Web App. Jadwal dikonfirmasi tim kami dan ditangani Practitioner bersertifikat.</p>
                </div>

                <div class="tabs reveal" role="tablist" aria-label="Modalitas layanan">
                    <button type="button" role="tab" id="tab-online" aria-controls="panel-online" aria-selected="true">Online Live</button>
                    <button type="button" role="tab" id="tab-offline" aria-controls="panel-offline" aria-selected="false" tabindex="-1">Tatap Muka</button>
                </div>

                @foreach (['online', 'offline'] as $modality)
                    <div id="panel-{{ $modality }}" role="tabpanel" aria-labelledby="tab-{{ $modality }}" class="grid grid--auto" @if ($modality === 'offline') hidden @endif>
                        @foreach ($services[$modality] ?? [] as $service)
                            <article class="card service">
                                <div class="service__meta">
                                    @if ($service->stage)<span class="chip">{{ $service->stage }}</span>@endif
                                    <span>{{ $service->duration_minutes }} menit</span>
                                    <span>· {{ $service->is_group ? 'Grup' : 'Privat' }}</span>
                                </div>
                                <h3>{{ $service->name }}</h3>
                                <p>{{ $service->description }}</p>
                                <div class="service__price">
                                    <strong>{{ $service->price_label }}</strong>
                                    <a href="{{ auth()->check() ? route('mindos.bookings.create', $service) : route('register') }}">Booking →</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>

        <!-- ================= Sertifikasi ================= -->
        <section id="sertifikasi" class="section" aria-labelledby="cert-title">
            <div class="inner">
                <div class="section__head reveal">
                    <span class="eyebrow">07 · Sertifikasi &amp; Lisensi</span>
                    <h2 id="cert-title" class="section__title">Dari klien, menjadi penyembuh.</h2>
                    <p class="section__lead">Alumni Foundation dapat melanjutkan ke jenjang Practitioner, Master Practitioner, hingga Trainer berlisensi — untuk menangani klien berbayar, online maupun tatap muka.</p>
                </div>

                <div class="ladder">
                    @foreach ($programs as $program)
                        <article class="card reveal">
                            <span class="ladder__step">Jenjang {{ $loop->iteration }} · syarat: {{ $program->prerequisite }}</span>
                            <h3 style="margin-top:8px">{{ $program->name }}</h3>
                            <div class="ladder__hours">
                                <div><strong>{{ $program->hours }}</strong><span>jam</span></div>
                                @if ($program->practice_clients)
                                    <div><strong>{{ $program->practice_clients }}</strong><span>klien praktik</span></div>
                                @endif
                            </div>
                            <p>{{ $program->description }}</p>
                            <ul class="ticks" style="margin-top:4px">
                                @foreach ($program->curriculum as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ================= FAQ ================= -->
        <section id="faq" class="section section--tint" aria-labelledby="faq-title">
            <div class="inner">
                <div class="section__head section__head--center reveal">
                    <span class="eyebrow">08 · FAQ</span>
                    <h2 id="faq-title" class="section__title">Pertanyaan yang sering diajukan</h2>
                </div>
                <div class="faq">
                    @foreach ($faqs as [$q, $a])
                        <details class="reveal" @if ($loop->first) open @endif>
                            <summary>{{ $q }}</summary>
                            <p>{{ $a }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ================= CTA ================= -->
        <section class="section section--photo" style="--photo: url('{{ asset('assets/media/mindos/calm.jpg') }}')" aria-labelledby="cta-title">
            <div class="inner">
                <div class="cta reveal">
                    <h2 id="cta-title">Saatnya merawat kesehatan pikiran Anda.</h2>
                    <p>Daftar gratis, ikuti Modul Foundation, dan temukan pola pikiran dominan Anda hari ini. Tanpa kartu kredit.</p>
                    <div class="hero__actions">
                        <a href="{{ auth()->check() ? route('mindos.journey') : route('register') }}" class="btn btn--primary">Daftar Gratis Sekarang</a>
                        <a href="#layanan" class="btn btn--light">Lihat Layanan</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- ================= Footer ================= -->
    <footer class="site-footer">
        <div class="inner">
            <div class="site-footer__grid">
                <div>
                    <a href="{{ url('/') }}" class="brand">
                        <img src="{{ $brand['logo_url'] }}" alt="" width="40" height="40" loading="lazy" />
                        <span>{{ $brand['name'] }}<small>{{ $brand['tagline'] }}</small></span>
                    </a>
                    <p>{{ Str::limit($brand['description'], 220) }}</p>
                </div>
                <div>
                    <h3>Web App</h3>
                    <ul>
                        <li><a href="#ekosistem">Modul Foundation</a></li>
                        <li><a href="#pola-pikiran">Cek Kesehatan Pikiran</a></li>
                        <li><a href="#metode-6r">6R Tracker</a></li>
                        <li><a href="#journey">Action Plan 30 Hari</a></li>
                    </ul>
                </div>
                <div>
                    <h3>Layanan</h3>
                    <ul>
                        <li><a href="#layanan">Online Live</a></li>
                        <li><a href="#layanan">Tatap Muka</a></li>
                        <li><a href="#sertifikasi">Sertifikasi</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h3>Akun</h3>
                    <ul>
                        <li><a href="{{ route('register') }}">Daftar Gratis</a></li>
                        <li><a href="{{ route('login') }}">Masuk</a></li>
                    </ul>
                </div>
            </div>
            <div class="site-footer__bottom">
                <span>&copy; <span id="year">{{ now()->year }}</span> {{ $brand['owner'] }} · {{ $brand['name'] }}. Hak cipta dilindungi.</span>
                <span>Mind OS bukan pengganti layanan medis darurat.</span>
            </div>
        </div>
    </footer>

    <script src="{{ asset('assets/js/landing.js') }}?v={{ filemtime(public_path('assets/js/landing.js')) }}" defer></script>
</body>

</html>
