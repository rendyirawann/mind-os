{{-- Horizontal primary navigation (desktop only; mobile uses the drawer). --}}
@php
    $groups = [
        ['Belajar', 'ki-book-open', ['mindos.foundation.*', 'mindos.bug-detector.*', 'mindos.audio.*'], [
            ['Modul Foundation', 'mindos.foundation.index', 'mindos.foundation.*'],
            ['Cek Kesehatan Pikiran', 'mindos.bug-detector.index', 'mindos.bug-detector.*'],
            ['Audio Guided Healing', 'mindos.audio.index', 'mindos.audio.*'],
        ]],
        ['Praktik 6R', 'ki-abstract-26', ['mindos.journal.*', 'mindos.six-r.*', 'mindos.action-plan.*', 'mindos.evaluation.*'], [
            ['Jurnal Recognize', 'mindos.journal.index', 'mindos.journal.*'],
            ['6R Tracker', 'mindos.six-r.index', 'mindos.six-r.*'],
            ['Action Plan 30 Hari', 'mindos.action-plan.index', 'mindos.action-plan.*'],
            ['Evaluasi 30 Hari', 'mindos.evaluation.index', 'mindos.evaluation.*'],
        ]],
        ['Layanan', 'ki-calendar-tick', ['mindos.services.*', 'mindos.bookings.*', 'mindos.certification.*'], [
            ['Online Live & Tatap Muka', 'mindos.services.index', 'mindos.services.*'],
            ['Booking Saya', 'mindos.bookings.index', 'mindos.bookings.*'],
            ['Sertifikasi & Lisensi', 'mindos.certification.index', 'mindos.certification.*'],
        ]],
    ];
@endphp
<nav class="menu menu-rounded menu-column menu-lg-row menu-root-here-bg-desktop menu-active-bg menu-state-primary menu-title-gray-800 menu-arrow-gray-500 align-items-stretch fw-semibold fs-6 py-1"
    data-kt-menu="true" aria-label="Navigasi utama">

    <div class="menu-item me-lg-2 {{ request()->routeIs('mindos.journey') ? 'here show menu-here-bg' : '' }}">
        <a class="menu-link py-3" href="{{ route('mindos.journey') }}">
            <span class="menu-icon"><i class="ki-outline ki-route fs-3"></i></span>
            <span class="menu-title">Perjalanan Saya</span>
        </a>
    </div>

    @foreach ($groups as [$title, $icon, $patterns, $links])
        <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start"
            class="menu-item menu-lg-down-accordion me-lg-2 {{ request()->routeIs(...$patterns) ? 'here show menu-here-bg' : '' }}">
            <span class="menu-link py-3">
                <span class="menu-icon"><i class="ki-outline {{ $icon }} fs-3"></i></span>
                <span class="menu-title">{{ $title }}</span>
                <span class="menu-arrow d-lg-none"></span>
            </span>
            <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown py-4 w-250px">
                @foreach ($links as [$label, $route, $pattern])
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title">{{ $label }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="menu-item me-lg-2 {{ request()->routeIs('mindos.community.*') ? 'here show menu-here-bg' : '' }}">
        <a class="menu-link py-3" href="{{ route('mindos.community.index') }}">
            <span class="menu-icon"><i class="ki-outline ki-people fs-3"></i></span>
            <span class="menu-title">Komunitas</span>
        </a>
    </div>

    @can('mindos.practice')
        <div class="menu-item me-lg-2 {{ request()->routeIs('mindos.practitioner.*') ? 'here show menu-here-bg' : '' }}">
            <a class="menu-link py-3" href="{{ route('mindos.practitioner.index') }}">
                <span class="menu-icon"><i class="ki-outline ki-briefcase fs-3"></i></span>
                <span class="menu-title">Sesi Klien</span>
            </a>
        </div>
    @endcan

    @can('mindos.manage')
        <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start"
            class="menu-item menu-lg-down-accordion me-lg-2 {{ request()->routeIs('mindos.admin.*', 'dashboard', 'users.*', 'roles.*', 'settings.*', 'log-activity.*') ? 'here show menu-here-bg' : '' }}">
            <span class="menu-link py-3">
                <span class="menu-icon"><i class="ki-outline ki-setting-2 fs-3"></i></span>
                <span class="menu-title">Kelola</span>
                <span class="menu-arrow d-lg-none"></span>
            </span>
            <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown py-4 w-250px">
                @foreach ([
                    ['Dashboard Admin', route('dashboard')],
                    ['Booking', route('mindos.admin.bookings.index')],
                    ['Sertifikasi', route('mindos.admin.enrollments.index')],
                    ['Modul Foundation', route('mindos.admin.content.index', 'modules')],
                    ['7 Pola Pikiran', route('mindos.admin.content.index', 'bugs')],
                    ['Audio', route('mindos.admin.content.index', 'audio')],
                    ['Layanan', route('mindos.admin.content.index', 'services')],
                ] as [$label, $url])
                    <div class="menu-item">
                        <a class="menu-link {{ url()->current() === $url ? 'active' : '' }}" href="{{ $url }}">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title">{{ $label }}</span>
                        </a>
                    </div>
                @endforeach
                @role('Superadmin|superadmin')
                    <div class="separator my-2"></div>
                    @foreach ([['Users', route('users.index')], ['Roles & Permissions', route('roles.index')], ['Settings', route('settings.index')], ['Log Aktivitas', route('log-activity.index')]] as [$label, $url])
                        <div class="menu-item">
                            <a class="menu-link" href="{{ $url }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">{{ $label }}</span>
                            </a>
                        </div>
                    @endforeach
                @endrole
            </div>
        </div>
    @endcan
</nav>
