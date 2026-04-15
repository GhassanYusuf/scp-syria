<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'دمشق باركينغ') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row w-100">
            <div class="col-lg-6 offset-lg-3">
                <div class="card border-0 shadow-lg rounded-5 overflow-hidden">
                    <div class="card-body p-5 text-center">
                        <h1 class="display-3 fw-bold text-primary mb-4">
                            <i class="bi bi-car-front"></i>
                            دمشق باركينغ
                        </h1>
                        <p class="lead text-muted mb-5">نظام مواقف السيارات المتكامل</p>
                        <div class="row g-4 justify-content-center">
                            @guest
                                <div class="col-md-6">
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-100 py-3 rounded-4 shadow-lg fs-5">
                                        <i class="bi bi-box-arrow-in-left me-2"></i>تسجيل الدخول
                                    </a>
                                </div>
                                @if (Route::has('register'))
                                    <div class="col-md-6">
                                        <a href="{{ route('register') }}" class="btn btn-success btn-lg w-100 py-3 rounded-4 shadow-lg fs-5">
                                            <i class="bi bi-person-plus me-2"></i>إنشاء حساب
                                        </a>
                                    </div>
                                @endif
                            @else
                                <div class="col-md-6">
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg w-100 py-3 rounded-4 shadow-lg fs-5">
                                        <i class="bi bi-house-door me-2"></i>لوحة الإدارة
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('operator.dashboard') }}" class="btn btn-warning btn-lg w-100 py-3 rounded-4 shadow-lg fs-5">
                                        <i class="bi bi-gear me-2"></i>لوحة المشغل
                                    </a>
                                </div>
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
