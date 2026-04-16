@extends('layouts.user')
@section('title', 'معلوماتي — دمشق باركينغ')

@section('content')

{{-- Page header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('parking.index') }}"
       class="btn btn-sm"
       style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
        <i class="bi bi-arrow-right me-1"></i>العودة
    </a>
    <div>
        <h1 class="fw-800 mb-0" style="font-size:1.25rem;color:#0f172a;">معلوماتي</h1>
        <p class="text-sm mb-0" style="color:#64748b;">إدارة بيانات حسابك الشخصي</p>
    </div>
</div>

<div class="row g-4">

    {{-- ── Identity card ──────────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    {{-- Avatar --}}
                    <div style="width:72px;height:72px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.75rem;color:#fff;flex-shrink:0;">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <h2 class="fw-800 mb-1" style="font-size:1.15rem;color:#0f172a;">{{ $user->name }}</h2>
                        <p class="mb-1 text-sm" style="color:#64748b;">{{ $user->email }}</p>
                        @if($user->phone)
                        <p class="mb-1 text-sm" style="color:#64748b;" dir="ltr">{{ $user->phone }}</p>
                        @endif
                        @php
                            $roleLabel = match($user->role) {
                                'admin'    => 'مدير النظام',
                                'operator' => 'مشغّل',
                                default    => 'مستخدم',
                            };
                            $roleStyle = match($user->role) {
                                'admin'    => 'background:rgba(239,68,68,.1);color:#b91c1c;',
                                'operator' => 'background:rgba(245,158,11,.1);color:#92400e;',
                                default    => 'background:rgba(99,102,241,.1);color:#4338ca;',
                            };
                        @endphp
                        <span class="badge" style="{{ $roleStyle }}font-size:.78rem;padding:.3em .75em;">{{ $roleLabel }}</span>
                        <span class="text-xs ms-2" style="color:#94a3b8;">
                            عضو منذ {{ $user->created_at->translatedFormat('F Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Edit profile ──────────────────────────────────────────────────── --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <span class="fw-700" style="font-size:.9rem;">
                    <i class="bi bi-person me-2" style="color:#6366f1;"></i>تعديل البيانات الشخصية
                </span>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                <div class="alert border-0 rounded-3 py-2 mb-3 text-sm" style="background:rgba(16,185,129,.1);color:#065f46;">
                    <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                </div>
                @endif
                @if($errors->updateName->any())
                <div class="alert alert-danger border-0 rounded-3 py-2 mb-3 text-sm">
                    {{ $errors->updateName->first() }}
                </div>
                @endif
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">الاسم الكامل</label>
                        <input type="text" name="name"
                               class="form-control"
                               value="{{ old('name', $user->name) }}"
                               placeholder="أدخل اسمك الكامل">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        @php
                        // Split stored phone back into country code + local number
                        $storedPhone = $user->phone ?? '';
                        $knownCodes  = ['+380','+351','+234','+249','+218','+216','+213','+212',
                                        '+974','+973','+971','+970','+968','+967','+966','+965',
                                        '+964','+963','+962','+961','+92','+91','+90','+86',
                                        '+81','+61','+55','+49','+39','+34','+33','+20',
                                        '+98','+44','+7','+1'];
                        $savedCountry = old('phone_country', '+963');
                        $savedLocal   = old('phone_local', $storedPhone);
                        foreach ($knownCodes as $kc) {
                            if (str_starts_with($storedPhone, $kc)) {
                                $savedCountry = $kc;
                                $savedLocal   = substr($storedPhone, strlen($kc));
                                break;
                            }
                        }
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
                        @endphp
                        <div class="input-group @if($errors->updateName->has('phone_local')) is-invalid @endif">
                            <input type="tel" name="phone_local"
                                   value="{{ $savedLocal }}"
                                   class="form-control @if($errors->updateName->has('phone_local')) is-invalid @endif"
                                   style="border-color:#e2e8f0;"
                                   placeholder="912345678" dir="ltr">
                            <select name="phone_country" dir="ltr"
                                    style="max-width:130px;background:#f8fafc;border-color:#e2e8f0;color:#374151;font-family:'Cairo',sans-serif;font-size:.875rem;cursor:pointer;"
                                    class="form-select">
                                @foreach($countries as [$flag, $label, $code])
                                <option value="{{ $code }}" {{ $savedCountry === $code ? 'selected' : '' }}>
                                    {{ $flag }} {{ $code }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @if($errors->updateName->has('phone_local'))
                            <div class="invalid-feedback d-block">{{ $errors->updateName->first('phone_local') }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email"
                               class="form-control"
                               value="{{ $user->email }}"
                               disabled
                               style="background:#f8fafc;color:#94a3b8;">
                        <div class="text-xs mt-1" style="color:#94a3b8;">لا يمكن تغيير البريد الإلكتروني.</div>
                    </div>
                    <button type="submit"
                            class="btn fw-700"
                            style="background:#6366f1;color:#fff;border:none;font-family:'Cairo',sans-serif;border-radius:.5rem;padding:.5rem 1.5rem;">
                        <i class="bi bi-check2 me-1"></i>حفظ التغييرات
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Change password ─────────────────────────────────────────────────── --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <span class="fw-700" style="font-size:.9rem;">
                    <i class="bi bi-lock me-2" style="color:#f59e0b;"></i>تغيير كلمة السر
                </span>
            </div>
            <div class="card-body p-4">
                @if($errors->updatePassword->any())
                <div class="alert alert-danger border-0 rounded-3 py-2 mb-3 text-sm">
                    {{ $errors->updatePassword->first() }}
                </div>
                @endif
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">كلمة السر الحالية</label>
                        <input type="password" name="current_password"
                               class="form-control" placeholder="••••••••" dir="ltr">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة السر الجديدة</label>
                        <input type="password" name="password"
                               class="form-control" placeholder="••••••••" dir="ltr">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">تأكيد كلمة السر الجديدة</label>
                        <input type="password" name="password_confirmation"
                               class="form-control" placeholder="••••••••" dir="ltr">
                    </div>
                    <button type="submit"
                            class="btn fw-700"
                            style="background:#f59e0b;color:#fff;border:none;font-family:'Cairo',sans-serif;border-radius:.5rem;padding:.5rem 1.5rem;">
                        <i class="bi bi-shield-lock me-1"></i>تحديث كلمة السر
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

@endsection
