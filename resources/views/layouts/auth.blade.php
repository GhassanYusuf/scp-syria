<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'دمشق باركينغ')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="auth-page">

    <div class="auth-box">

        {{-- Brand --}}
        <div class="auth-brand">
            <div class="brand-icon">
                <i class="bi bi-p-square-fill"></i>
            </div>
            <h1>دمشق باركينغ</h1>
            <p>نظام إدارة مواقف السيارات</p>
        </div>

        {{-- Card --}}
        <div class="auth-card">
            @yield('content')
        </div>

        {{-- Back link --}}
        <div class="text-center mt-4">
            <a href="{{ route('parking.index') }}"
               class="text-sm"
               style="color:#64748b;text-decoration:none;">
                <i class="bi bi-arrow-left me-1"></i>
                العودة إلى الموقع الرئيسي
            </a>
        </div>

    </div>

</body>
</html>
