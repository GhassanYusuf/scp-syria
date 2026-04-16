@extends('layouts.auth')
@section('title', 'إنشاء حساب — دمشق باركينغ')

@section('content')

<h2 class="fw-800 mb-1" style="font-size:1.35rem;color:#0f172a;">إنشاء حساب جديد</h2>
<p class="text-sm mb-4" style="color:#64748b;">انضم إلى دمشق باركينغ الآن</p>

@if($errors->any())
<div class="alert alert-danger border-0 rounded-3 d-flex align-items-center gap-2 py-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
    <span class="text-sm">{{ $errors->first() }}</span>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('register.action') }}">
    @csrf

    <div class="mb-3">
        <label class="form-label">الاسم الكامل</label>
        <div class="input-group">
            <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;border-left:none;">
                <i class="bi bi-person" style="color:#94a3b8;"></i>
            </span>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="form-control @error('name') is-invalid @enderror"
                   style="border-right:none;border-color:#e2e8f0;"
                   placeholder="الاسم الكامل">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

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
        <label class="form-label">رقم الهاتف</label>
        @php
        $countries = [
            ['🇸🇾','سوريا','+963'],['🇸🇦','السعودية','+966'],['🇦🇪','الإمارات','+971'],
            ['🇯🇴','الأردن','+962'],['🇱🇧','لبنان','+961'],['🇮🇶','العراق','+964'],
            ['🇪🇬','مصر','+20'],['🇴🇲','عُمان','+968'],['🇶🇦','قطر','+974'],
            ['🇰🇼','الكويت','+965'],['🇧🇭','البحرين','+973'],['🇾🇪','اليمن','+967'],
            ['🇵🇸','فلسطين','+970'],['🇲🇦','المغرب','+212'],['🇩🇿','الجزائر','+213'],
            ['🇹🇳','تونس','+216'],['🇱🇾','ليبيا','+218'],['🇸🇩','السودان','+249'],
            ['🇹🇷','تركيا','+90'],['🇮🇷','إيران','+98'],['🇩🇪','ألمانيا','+49'],
            ['🇫🇷','فرنسا','+33'],['🇬🇧','بريطانيا','+44'],['🇺🇸','أمريكا','+1'],
            ['🇷🇺','روسيا','+7'],['🇨🇳','الصين','+86'],['🇮🇳','الهند','+91'],
            ['🇦🇺','أستراليا','+61'],['🇮🇹','إيطاليا','+39'],['🇪🇸','إسبانيا','+34'],
            ['🇧🇷','البرازيل','+55'],['🇵🇰','باكستان','+92'],['🇳🇬','نيجيريا','+234'],
        ];
        $oldCountry = old('phone_country', '+963');
        @endphp
        <div class="input-group @error('phone') is-invalid @enderror">
            <input type="tel" name="phone_local" value="{{ old('phone_local') }}" required
                   class="form-control @error('phone') is-invalid @enderror"
                   style="border-color:#e2e8f0;"
                   placeholder="912345678" dir="ltr">
            <select name="phone_country" dir="ltr"
                    style="max-width:130px;background:#f8fafc;border-color:#e2e8f0;color:#374151;font-family:'Cairo',sans-serif;font-size:.875rem;cursor:pointer;"
                    class="form-select">
                @foreach($countries as [$flag, $label, $code])
                <option value="{{ $code }}" {{ $oldCountry === $code ? 'selected' : '' }}>
                    {{ $flag }} {{ $code }}
                </option>
                @endforeach
            </select>
        </div>
        @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label class="form-label">كلمة السر</label>
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

    <div class="mb-4">
        <label class="form-label">تأكيد كلمة السر</label>
        <div class="input-group">
            <span class="input-group-text" style="background:#f8fafc;border-color:#e2e8f0;border-left:none;">
                <i class="bi bi-lock-fill" style="color:#94a3b8;"></i>
            </span>
            <input type="password" name="password_confirmation" required
                   class="form-control"
                   style="border-right:none;border-color:#e2e8f0;"
                   placeholder="••••••••" dir="ltr">
        </div>
    </div>

    <button type="submit"
            class="btn btn-lg w-100 fw-700"
            style="background:#059669;color:#fff;border:none;font-family:'Cairo',sans-serif;border-radius:.5rem;">
        <i class="bi bi-person-check me-2"></i>
        إنشاء الحساب
    </button>
</form>

<div class="auth-divider"><span>أو</span></div>

<a href="{{ route('login') }}"
   class="btn btn-lg w-100 fw-600"
   style="background:#f8fafc;color:#0f172a;border:1px solid #e2e8f0;font-family:'Cairo',sans-serif;border-radius:.5rem;">
    <i class="bi bi-box-arrow-in-left me-2"></i>
    تسجيل الدخول
</a>

@endsection
