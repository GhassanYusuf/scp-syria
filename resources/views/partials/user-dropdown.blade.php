@auth
{{-- ── Logged-in: profile avatar + dropdown ──────────────────────────────── --}}
<div class="dropdown">
    <button type="button"
            class="btn btn-sm d-flex align-items-center gap-2"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            style="background:rgba(255,255,255,.1);color:#f8fafc;border:1px solid rgba(255,255,255,.18);border-radius:.625rem;padding:.35rem .75rem;font-family:'Cairo',sans-serif;">
        {{-- Avatar circle --}}
        <div style="width:32px;height:32px;background:#6366f1;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;flex-shrink:0;color:#fff;border:2px solid rgba(255,255,255,.25);">
            {{ mb_substr(auth()->user()->name, 0, 1) }}
        </div>
        <span class="d-none d-sm-inline fw-600" style="font-size:.875rem;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
            {{ auth()->user()->name }}
        </span>
        <i class="bi bi-chevron-down" style="font-size:.65rem;opacity:.7;"></i>
    </button>

    <ul class="dropdown-menu dropdown-menu-end"
        style="min-width:230px;border:1px solid #e2e8f0;box-shadow:0 8px 30px rgba(0,0,0,.12);border-radius:.75rem;padding:.5rem;font-family:'Cairo',sans-serif;">

        {{-- User info header --}}
        <li>
            <div class="d-flex align-items-center gap-3 px-2 py-2">
                <div style="width:42px;height:42px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1rem;color:#fff;flex-shrink:0;">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>
                <div style="min-width:0;">
                    <div class="fw-700" style="color:#0f172a;font-size:.9rem;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ auth()->user()->name }}
                    </div>
                    <div style="color:#64748b;font-size:.75rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ auth()->user()->email }}
                    </div>
                    {{-- Role badge --}}
                    @php
                        $role = auth()->user()->role;
                        $roleLabel = match($role) {
                            'admin'    => 'مدير النظام',
                            'operator' => 'مشغّل',
                            default    => 'مستخدم',
                        };
                        $roleColor = match($role) {
                            'admin'    => 'background:rgba(239,68,68,.1);color:#b91c1c;',
                            'operator' => 'background:rgba(245,158,11,.1);color:#92400e;',
                            default    => 'background:rgba(99,102,241,.1);color:#4338ca;',
                        };
                    @endphp
                    <span class="badge mt-1" style="{{ $roleColor }}font-size:.68rem;padding:.2em .55em;">{{ $roleLabel }}</span>
                </div>
            </div>
        </li>

        <li><hr class="dropdown-divider my-1" style="border-color:#f1f5f9;"></li>

        {{-- My profile --}}
        <li>
            <a href="{{ route('profile.show') }}"
               class="dropdown-item d-flex align-items-center gap-2 rounded-2"
               style="color:#374151;font-size:.875rem;padding:.5rem .75rem;">
                <i class="bi bi-person-circle" style="color:#6366f1;font-size:1rem;width:18px;text-align:center;"></i>
                معلوماتي
            </a>
        </li>

        {{-- My reservations --}}
        <li>
            <a href="{{ route('user.dashboard') }}"
               class="dropdown-item d-flex align-items-center gap-2 rounded-2"
               style="color:#374151;font-size:.875rem;padding:.5rem .75rem;">
                <i class="bi bi-calendar3" style="color:#0ea5e9;font-size:1rem;width:18px;text-align:center;"></i>
                حجوزاتي
            </a>
        </li>

        {{-- Operator panel (operators & admins) --}}
        @if(in_array(auth()->user()->role, ['operator', 'admin']))
        <li>
            <a href="{{ route('operator.dashboard') }}"
               class="dropdown-item d-flex align-items-center gap-2 rounded-2"
               style="color:#374151;font-size:.875rem;padding:.5rem .75rem;">
                <i class="bi bi-person-badge" style="color:#f59e0b;font-size:1rem;width:18px;text-align:center;"></i>
                لوحة المشغّل
            </a>
        </li>
        @endif

        {{-- Admin panel (admins only) --}}
        @if(auth()->user()->role === 'admin')
        <li>
            <a href="{{ route('admin.dashboard') }}"
               class="dropdown-item d-flex align-items-center gap-2 rounded-2"
               style="color:#374151;font-size:.875rem;padding:.5rem .75rem;">
                <i class="bi bi-speedometer2" style="color:#10b981;font-size:1rem;width:18px;text-align:center;"></i>
                لوحة الإدارة
            </a>
        </li>
        @endif

        <li><hr class="dropdown-divider my-1" style="border-color:#f1f5f9;"></li>

        {{-- Sign out --}}
        <li>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit"
                        class="dropdown-item d-flex align-items-center gap-2 rounded-2"
                        style="color:#ef4444;font-size:.875rem;padding:.5rem .75rem;width:100%;background:none;border:none;cursor:pointer;font-family:'Cairo',sans-serif;">
                    <i class="bi bi-box-arrow-left" style="font-size:1rem;width:18px;text-align:center;"></i>
                    تسجيل الخروج
                </button>
            </form>
        </li>

    </ul>
</div>

@else
{{-- ── Guest: login button ──────────────────────────────────────────────── --}}
<a href="{{ route('login') }}"
   class="btn btn-sm fw-600"
   style="background:#6366f1;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
    <i class="bi bi-box-arrow-in-left me-1"></i>
    <span class="d-none d-sm-inline">تسجيل الدخول</span>
</a>
@endauth
