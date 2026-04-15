<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'دمشق باركينغ')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @yield('styles')
</head>
<body>

<div class="app-layout">

    {{-- ════════════════════════════════════════
         SIDEBAR  (appears on the RIGHT in RTL
         because it is the first flex child)
    ════════════════════════════════════════ --}}
    <aside class="app-sidebar" id="appSidebar">

        {{-- Logo --}}
        <a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
            <div class="logo-icon">
                <i class="bi bi-p-square-fill"></i>
            </div>
            <div>
                <div class="logo-text">دمشق باركينغ</div>
                <div class="logo-sub">لوحة الإدارة</div>
            </div>
        </a>

        {{-- Navigation --}}
        <nav class="sidebar-nav">

            <div class="sidebar-section">الرئيسية</div>

            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 sidebar-icon"></i>
                <span>لوحة التحكم</span>
            </a>

            <div class="sidebar-section">المواقف والحجوزات</div>

            <a href="{{ route('admin.parking-lots.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.parking-lots.*') ? 'active' : '' }}">
                <i class="bi bi-buildings sidebar-icon"></i>
                <span>المواقف</span>
            </a>

            <a href="{{ route('admin.bookings.active') }}"
               class="sidebar-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check sidebar-icon"></i>
                <span>الحجوزات النشطة</span>
            </a>

            <div class="sidebar-section">التشغيل</div>

            <a href="{{ route('operator.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('operator.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge sidebar-icon"></i>
                <span>لوحة المشغّل</span>
            </a>

        </nav>

        {{-- Footer: user info + logout --}}
        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="user-avatar">
                    {{ mb_substr(auth()->user()?->name ?? 'م', 0, 1) }}
                </div>
                <div style="min-width:0">
                    <div class="user-name text-truncate">{{ auth()->user()?->name ?? 'المستخدم' }}</div>
                    <div class="user-role">
                        {{ auth()->user()?->role === 'admin' ? 'مدير النظام' : 'مشغّل' }}
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="btn btn-sm w-100 mt-1 text-start"
                        style="background:rgba(239,68,68,.12);color:#f87171;border:none;border-radius:.5rem;padding:.45rem .75rem;font-family:'Cairo',sans-serif;font-size:.82rem;">
                    <i class="bi bi-box-arrow-left me-2"></i>تسجيل الخروج
                </button>
            </form>
        </div>

    </aside>

    {{-- ════════════════════════════════════════
         MAIN BODY
    ════════════════════════════════════════ --}}
    <div class="app-body">

        {{-- Topbar --}}
        <header class="app-topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="قائمة التنقل">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="topbar-title">@yield('page-title', 'لوحة التحكم')</h1>
            </div>
            <div class="topbar-actions d-flex align-items-center gap-2">
                {{-- Desktop: link to public site --}}
                <a href="{{ route('parking.index') }}"
                   class="btn btn-sm d-none d-md-inline-flex align-items-center"
                   style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
                   target="_blank">
                    <i class="bi bi-globe2 me-1"></i>الموقع العام
                </a>
                {{-- Mobile: user avatar + logout --}}
                <div class="d-flex d-md-none align-items-center gap-2">
                    <div style="width:30px;height:30px;background:rgba(99,102,241,.12);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#6366f1;font-weight:800;font-size:.82rem;flex-shrink:0;">
                        {{ mb_substr(auth()->user()?->name ?? 'م', 0, 1) }}
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit"
                                style="background:none;border:none;color:#94a3b8;padding:4px 6px;font-size:1.15rem;cursor:pointer;line-height:1;"
                                title="تسجيل الخروج">
                            <i class="bi bi-box-arrow-left"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Flash Messages --}}
        <div class="px-4 pt-3">
            @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 border-0 rounded-3 py-2 mb-0"
                 role="alert">
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2 border-0 rounded-3 py-2 mb-0"
                 role="alert">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
            @endif
        </div>

        {{-- Page Content --}}
        <main class="app-content">
            @yield('content')
        </main>

    </div>{{-- /app-body --}}

</div>{{-- /app-layout --}}

{{-- Mobile sidebar overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- ══ MOBILE BOTTOM NAVIGATION ═══════════════════════════════════════════════ --}}
<nav class="mobile-bottom-nav" aria-label="التنقل">
    <a href="{{ route('admin.dashboard') }}"
       class="mob-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        <span>الرئيسية</span>
    </a>
    <a href="{{ route('admin.parking-lots.index') }}"
       class="mob-nav-item {{ request()->routeIs('admin.parking-lots.*') ? 'active' : '' }}">
        <i class="bi bi-buildings"></i>
        <span>المواقف</span>
    </a>
    <a href="{{ route('admin.bookings.active') }}"
       class="mob-nav-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
        <i class="bi bi-calendar-check"></i>
        <span>الحجوزات</span>
    </a>
    <a href="{{ route('operator.dashboard') }}"
       class="mob-nav-item {{ request()->routeIs('operator.*') ? 'active' : '' }}">
        <i class="bi bi-person-badge"></i>
        <span>التشغيل</span>
    </a>
</nav>

<script>
    // Mobile sidebar toggle
    const toggle   = document.getElementById('sidebarToggle');
    const sidebar  = document.getElementById('appSidebar');
    const overlay  = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    toggle?.addEventListener('click', openSidebar);
    overlay.addEventListener('click', closeSidebar);
</script>

@stack('scripts')
</body>
</html>
