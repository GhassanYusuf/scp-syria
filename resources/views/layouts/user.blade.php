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
</head>
<body style="background:#f1f5f9;font-family:'Cairo',sans-serif;">

    {{-- ══ HEADER ══════════════════════════════════════════════════════════════ --}}
    <header class="public-header">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">

                {{-- Logo --}}
                <a href="{{ route('parking.index') }}" class="d-flex align-items-center gap-3 text-decoration-none">
                    <div style="width:40px;height:40px;background:#6366f1;border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-p-square-fill text-white" style="font-size:1.25rem;"></i>
                    </div>
                    <div>
                        <div class="fw-800" style="color:#f8fafc;font-size:1.05rem;line-height:1.2;">دمشق باركينغ</div>
                        <div style="color:#94a3b8;font-size:.72rem;">مواقف السيارات في دمشق</div>
                    </div>
                </a>

                {{-- User Dropdown --}}
                @include('partials.user-dropdown')

            </div>
        </div>
    </header>

    {{-- ══ CONTENT ═════════════════════════════════════════════════════════════ --}}
    <main class="container py-4" style="max-width:820px;">

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 border-0 rounded-3 py-2 mb-4">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 border-0 rounded-3 py-2 mb-4">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </main>

</body>
</html>
