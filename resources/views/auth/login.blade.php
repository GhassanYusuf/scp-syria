@extends('layouts.auth')
@section('title', 'تسجيل الدخول — دمشق باركينغ')

@section('content')

<h2 class="fw-800 mb-1" style="font-size:1.35rem;color:#0f172a;">تسجيل الدخول</h2>
<p class="text-sm mb-4" style="color:#64748b;">أدخل بياناتك للوصول إلى لوحة التحكم</p>

@if($errors->any())
<div class="alert alert-danger border-0 rounded-3 d-flex align-items-center gap-2 py-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
    <span class="text-sm">{{ $errors->first() }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label">البريد الإلكتروني</label>
        <div class="input-group">
            <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;border-left:none;">
                <i class="bi bi-envelope" style="color:#94a3b8;"></i>
            </span>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="form-control @error('email') is-invalid @enderror"
                   style="border-right:none;border-color:#e2e8f0;"
                   placeholder="your@email.com" dir="ltr">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0">كلمة السر</label>
            <a href="#" class="text-xs" style="color:#6366f1;text-decoration:none;">نسيت كلمة السر؟</a>
        </div>
        <div class="input-group">
            <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;border-left:none;">
                <i class="bi bi-lock" style="color:#94a3b8;"></i>
            </span>
            <input type="password" name="password" required
                   class="form-control @error('password') is-invalid @enderror"
                   style="border-right:none;border-color:#e2e8f0;"
                   placeholder="••••••••" dir="ltr">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="d-flex align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label text-sm" for="remember" style="color:#64748b;">تذكرني</label>
        </div>
    </div>

    <button type="submit"
            class="btn btn-lg w-100 fw-700"
            style="background:#6366f1;color:#fff;border:none;font-family:'Cairo',sans-serif;border-radius:.5rem;">
        <i class="bi bi-box-arrow-in-left me-2"></i>
        دخول
    </button>
</form>

<div class="auth-divider"><span>أو</span></div>

<a href="{{ route('register') }}"
   class="btn btn-lg w-100 fw-600"
   style="background:#f8fafc;color:#0f172a;border:1px solid #e2e8f0;font-family:'Cairo',sans-serif;border-radius:.5rem;">
    <i class="bi bi-person-plus me-2"></i>
    إنشاء حساب جديد
</a>

<div class="mt-4 p-3 rounded-3" style="background:#f0f9ff;border:1px solid #bae6fd;">
    <p class="text-xs fw-700 mb-2" style="color:#0369a1;">
        <i class="bi bi-key-fill me-1"></i>حسابات الاختبار
    </p>
    <p class="text-xs mb-1" style="color:#0369a1;">
        <strong>مدير:</strong> admin@damascusparking.com / admin123
    </p>
    <p class="text-xs mb-0" style="color:#0369a1;">
        <strong>مشغّل:</strong> operator@damascusparking.com / operator123
    </p>
</div>

@endsection
