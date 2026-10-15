{{-- Off-canvas navigation drawer. --}}
<aside id="kt_app_sidebar" class="d-flex flex-column" aria-label="Navigasi utama" aria-hidden="true">

    <!--begin::Header-->
    <div class="d-flex align-items-center justify-content-between gap-2 px-5 pt-5 pb-4">
        <a href="{{ route('mindos.journey') }}" class="app-brand">
            <img src="{{ $brand['logo_url'] }}" alt="" class="app-brand__mark" width="34" height="34" />
            <span class="min-w-0">
                <span class="app-brand__name d-block">{{ $brand['name'] }}</span>
                <span class="app-brand__tagline">{{ $brand['tagline'] }}</span>
            </span>
        </a>
        <button type="button" class="btn btn-icon btn-sm btn-active-color-primary" data-app-sidebar-toggle
            aria-label="Tutup menu navigasi">
            <i class="ki-outline ki-cross fs-2"></i>
        </button>
    </div>
    <!--end::Header-->

    <div class="separator mx-5 mb-3"></div>

    <!--begin::Menu-->
    <nav class="px-4 flex-column-fluid">
        <div class="menu menu-column menu-rounded menu-sub-indention menu-active-bg fw-semibold fs-6" data-kt-menu="true">

            <div class="menu-item">
                <a class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-element-11 fs-3">
                            <span class="path1"></span><span class="path2"></span>
                            <span class="path3"></span><span class="path4"></span>
                        </i>
                    </span>
                    <span class="menu-title">Dashboard</span>
                </a>
            </div>

            @php
                $mosNav = [
                    'Mind OS' => [
                        ['Perjalanan Saya', 'mindos.journey', 'mindos.journey', 'ki-route'],
                        ['Modul Foundation', 'mindos.foundation.index', 'mindos.foundation.*', 'ki-book-open'],
                        ['Cek Kesehatan Pikiran', 'mindos.bug-detector.index', 'mindos.bug-detector.*', 'ki-scan-barcode'],
                        ['Audio Guided Healing', 'mindos.audio.index', 'mindos.audio.*', 'ki-speaker'],
                    ],
                    'Praktik 6R' => [
                        ['Jurnal Recognize', 'mindos.journal.index', 'mindos.journal.*', 'ki-notepad-edit'],
                        ['6R Tracker', 'mindos.six-r.index', 'mindos.six-r.*', 'ki-abstract-26'],
                        ['Action Plan 30 Hari', 'mindos.action-plan.index', 'mindos.action-plan.*', 'ki-calendar-8'],
                        ['Evaluasi 30 Hari', 'mindos.evaluation.index', 'mindos.evaluation.*', 'ki-chart-line-up'],
                        ['Komunitas', 'mindos.community.index', 'mindos.community.*', 'ki-people'],
                    ],
                    'Layanan' => [
                        ['Online Live & Tatap Muka', 'mindos.services.index', 'mindos.services.*', 'ki-calendar-tick'],
                        ['Booking Saya', 'mindos.bookings.index', 'mindos.bookings.*', 'ki-bookmark'],
                        ['Sertifikasi & Lisensi', 'mindos.certification.index', 'mindos.certification.*', 'ki-award'],
                    ],
                ];
            @endphp

            @foreach ($mosNav as $heading => $links)
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">{{ $heading }}</span></div>
                </div>
                @foreach ($links as [$label, $route, $pattern, $icon])
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}">
                            <span class="menu-icon"><i class="ki-outline {{ $icon }} fs-3"></i></span>
                            <span class="menu-title">{{ $label }}</span>
                        </a>
                    </div>
                @endforeach
            @endforeach

            @can('mindos.practice')
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('mindos.practitioner.*') ? 'active' : '' }}" href="{{ route('mindos.practitioner.index') }}">
                        <span class="menu-icon"><i class="ki-outline ki-briefcase fs-3"></i></span>
                        <span class="menu-title">Sesi Klien (Practitioner)</span>
                    </a>
                </div>
            @endcan

            @can('mindos.manage')
                <div class="menu-item pt-5">
                    <div class="menu-content"><span class="menu-heading fw-bold text-uppercase fs-7">Kelola Mind OS</span></div>
                </div>
                @foreach ([
                    ['Booking', route('mindos.admin.bookings.index'), 'mindos.admin.bookings.*', 'ki-calendar-edit'],
                    ['Sertifikasi', route('mindos.admin.enrollments.index'), 'mindos.admin.enrollments.*', 'ki-medal-star'],
                    ['Konten (Modul, Pola Pikiran, Audio, Layanan)', route('mindos.admin.content.index', 'modules'), 'mindos.admin.content.*', 'ki-folder'],
                ] as [$label, $url, $pattern, $icon])
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ $url }}">
                            <span class="menu-icon"><i class="ki-outline {{ $icon }} fs-3"></i></span>
                            <span class="menu-title">{{ $label }}</span>
                        </a>
                    </div>
                @endforeach
            @endcan

            @role('Superadmin|superadmin')
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Manajemen</span>
                    </div>
                </div>

                <div data-kt-menu-trigger="click"
                    class="menu-item menu-accordion {{ request()->is('admin/users*') || request()->is('admin/roles*') ? 'here show' : '' }}">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-people fs-3">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                <span class="path4"></span><span class="path5"></span>
                            </i>
                        </span>
                        <span class="menu-title">User Management</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion {{ request()->is('admin/users*') || request()->is('admin/roles*') ? 'show' : '' }}">
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Users</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link {{ request()->is('admin/roles*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Roles &amp; Permissions</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endrole

            <div class="menu-item pt-5">
                <div class="menu-content">
                    <span class="menu-heading fw-bold text-uppercase fs-7">Akun Saya</span>
                </div>
            </div>

            <div data-kt-menu-trigger="click"
                class="menu-item menu-accordion {{ request()->is('admin/my-*') || request()->is('admin/mmy-*') ? 'here show' : '' }}">
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-profile-circle fs-3">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                    </span>
                    <span class="menu-title">My Account</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion {{ request()->is('admin/my-*') || request()->is('admin/mmy-*') ? 'show' : '' }}">
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('account.index') ? 'active' : '' }}" href="{{ route('account.index') }}">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title">Overview</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('admin/my-security*') ? 'active' : '' }}" href="{{ route('my-security.index') }}">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title">Security</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('admin/my-activity*') ? 'active' : '' }}" href="{{ route('my-activity.index') }}">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title">Activity</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('admin/mmy-login-session*') ? 'active' : '' }}" href="{{ route('my-login-session.index') }}">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title">Login Sessions</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="menu-item pt-5">
                <div class="menu-content">
                    <span class="menu-heading fw-bold text-uppercase fs-7">Sistem</span>
                </div>
            </div>

            {{-- Visible to every role: Superadmin sees all entries, others
                 only their own (enforced in LogActivityController). --}}
            <div class="menu-item">
                <a class="menu-link {{ request()->is('admin/log-activity*') ? 'active' : '' }}" href="{{ route('log-activity.index') }}">
                    <span class="menu-icon">
                        <i class="ki-duotone ki-notepad fs-3">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            <span class="path4"></span><span class="path5"></span>
                        </i>
                    </span>
                    <span class="menu-title">Log Aktivitas</span>
                </a>
            </div>

            @role('Superadmin|superadmin')

                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                        <span class="menu-icon">
                            <i class="ki-duotone ki-setting-2 fs-3"><span class="path1"></span><span class="path2"></span></i>
                        </span>
                        <span class="menu-title">Settings</span>
                    </a>
                </div>

            @endrole

        </div>
    </nav>
    <!--end::Menu-->

    <!--begin::Footer-->
    @auth
        <div class="px-5 py-5 mt-auto">
            <div class="separator mb-4"></div>
            <div class="d-flex align-items-center gap-3 min-w-0">
                <x-avatar :size="36" />
                <div class="d-flex flex-column min-w-0">
                    <span class="fw-bold fs-7 text-gray-800 text-truncate">{{ auth()->user()->name }}</span>
                    <span class="text-muted fs-8">{{ auth()->user()->role_label }}</span>
                </div>
            </div>
        </div>
    @endauth
    <!--end::Footer-->
</aside>
