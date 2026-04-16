@extends('layouts.admin')
@section('title', 'لوحة المشغّل — دمشق باركينغ')
@section('page-title', 'لوحة المشغّل')

@section('styles')
<style>
    /* ── Portrait lot picker cards ───────────────────────────────────── */
    .lot-portrait-card {
        background:#fff;
        border-radius:16px;
        overflow:hidden;
        box-shadow:0 6px 20px rgba(0,0,0,.07);
        cursor:pointer;
        transition:transform .25s, box-shadow .25s;
        height:100%;
        display:flex;
        flex-direction:column;
    }
    .lot-portrait-card:hover {
        transform:translateY(-8px);
        box-shadow:0 18px 40px rgba(0,0,0,.13);
    }

    /* Image area */
    .lot-card-img-wrap {
        position:relative;
        height:190px;
        overflow:hidden;
        flex-shrink:0;
    }
    .lot-card-img-wrap img {
        width:100%; height:100%;
        object-fit:cover;
        transition:transform .4s;
    }
    .lot-portrait-card:hover .lot-card-img-wrap img { transform:scale(1.07); }

    /* Gradient placeholder when no image */
    .lot-card-placeholder {
        width:100%; height:100%;
        display:flex; flex-direction:column;
        align-items:center; justify-content:center;
        color:rgba(255,255,255,.85);
    }
    .lot-card-placeholder i   { font-size:3.5rem; margin-bottom:.5rem; opacity:.7; }
    .lot-card-placeholder span { font-size:.95rem; font-weight:700; text-align:center; padding:0 1rem; opacity:.9; }

    /* Availability badge over image */
    .lot-card-avail-badge {
        position:absolute;
        top:12px;
        inset-inline-end:12px;
        padding:.28em .75em;
        border-radius:20px;
        font-size:.72rem;
        font-weight:700;
        color:#fff;
        backdrop-filter:blur(6px);
        -webkit-backdrop-filter:blur(6px);
    }

    /* Card body */
    .lot-card-body {
        padding:1rem 1.125rem 1.125rem;
        flex:1;
        display:flex;
        flex-direction:column;
    }
    .lot-card-title   { font-size:1rem; font-weight:800; color:#0f172a; margin-bottom:.2rem; line-height:1.3; }
    .lot-card-address { font-size:.78rem; color:#94a3b8; margin-bottom:.625rem; display:flex; align-items:flex-start; gap:.3rem; }

    .occ-bar      { height:5px; background:#e2e8f0; border-radius:3px; overflow:hidden; margin:.5rem 0 .5rem; }
    .occ-bar-fill { height:100%; border-radius:3px; transition:width .4s; }

    .lot-card-stats { display:flex; gap:1rem; font-size:.75rem; color:#64748b; flex-wrap:wrap; }
    .lot-card-stats span { display:flex; align-items:center; gap:.25rem; }

    /* Select button — slides up on hover */
    .lot-card-select-wrap { margin-top:auto; padding-top:.75rem; overflow:hidden; max-height:0; transition:max-height .2s; }
    .lot-portrait-card:hover .lot-card-select-wrap { max-height:60px; }
    .lot-card-select-btn {
        display:block; width:100%; padding:.5rem;
        background:#6366f1; color:#fff; border:none; border-radius:.625rem;
        font-family:'Cairo',sans-serif; font-weight:700; font-size:.875rem; cursor:pointer;
        transition:background .15s;
    }
    .lot-card-select-btn:hover { background:#4f46e5; }

    /* ── Operator panel — search bar area ────────────────────────────── */
    .search-container {
        background:#fff; padding:20px; border-radius:15px;
        box-shadow:0 4px 12px rgba(0,0,0,.05); margin-bottom:24px;
    }

    /* ── Reservation / walk-in cards ─────────────────────────────────── */
    .res-card {
        background:#fff; border:none; border-radius:20px;
        transition:all .3s ease; box-shadow:0 8px 16px rgba(0,0,0,.06);
        height:100%; position:relative;
    }
    .res-card:hover { transform:translateY(-8px); box-shadow:0 16px 32px rgba(0,0,0,.1); }

    .card-header-custom {
        padding:15px; border-radius:20px 20px 0 0;
        font-weight:bold; text-align:center; color:#fff;
        font-family:'Cairo',sans-serif; font-size:.88rem;
    }
    .bg-reserved { background:#1e3c72; }
    .bg-direct   { background:#27ae60; }

    .plate-box {
        font-size:1.8rem; letter-spacing:3px; font-weight:900; color:#2c3e50;
        background:#f8f9fa; margin:15px 0; padding:12px;
        border-radius:12px; border:2px solid #dee2e6; text-align:center;
        direction:ltr; font-family:monospace;
    }

    /* ── Payment grid in checkout modal ──────────────────────────────── */
    .payment-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-top:15px; }
    .pay-radio  { display:none; }
    .pay-option {
        border:2px solid #f0f0f0; padding:18px 8px; border-radius:15px;
        cursor:pointer; text-align:center; transition:all .2s; display:block;
        font-family:'Cairo',sans-serif;
    }
    .pay-option i { display:block; font-size:1.6rem; margin-bottom:6px; }
    .pay-option span { font-size:.85rem; font-weight:600; }
    .pay-radio:checked + .pay-option {
        border-color:#3498db; background:#e3f2fd; color:#3498db;
    }

    /* ── Receipt fee rows ─────────────────────────────────────────────── */
    .fee-row { display:flex; justify-content:space-between; padding:.35rem 0; border-bottom:1px dashed #f1f5f9; font-size:.875rem; }
    .fee-row:last-child { border-bottom:none; }
    .fee-total { display:flex; justify-content:space-between; padding:.625rem 0; border-top:2px solid #e2e8f0; font-weight:800; font-size:1.1rem; color:#0f172a; margin-top:.25rem; }
</style>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════════════════════════
     LOT PICKER — no lot selected
══════════════════════════════════════════════════════════════════════ --}}
@if(!$selectedLot)
@php
$gradients = [
    'linear-gradient(135deg,#1e1b4b 0%,#4338ca 100%)',
    'linear-gradient(135deg,#064e3b 0%,#10b981 100%)',
    'linear-gradient(135deg,#1e3a8a 0%,#3b82f6 100%)',
    'linear-gradient(135deg,#7c2d12 0%,#f97316 100%)',
    'linear-gradient(135deg,#4a044e 0%,#9333ea 100%)',
    'linear-gradient(135deg,#0f172a 0%,#475569 100%)',
];
@endphp

{{-- Header + search bar --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-800 mb-0" style="font-size:1.15rem;color:#0f172a;">اختر موقف السيارات</h2>
        <p class="text-sm mb-0" style="color:#64748b;">اضغط على الموقف للبدء في إدارة السيارات</p>
    </div>
    <span class="badge badge-soft-primary" style="font-size:.8rem;padding:.5em .9em;">
        {{ $parkingLots->count() }} موقف متاح
    </span>
</div>

<div class="input-group mb-4" style="max-width:460px;">
    <span class="input-group-text" style="background:#fff;border-color:#e2e8f0;">
        <i class="bi bi-search" style="color:#94a3b8;"></i>
    </span>
    <input type="text" id="lotSearch" class="form-control" style="border-color:#e2e8f0;"
           placeholder="ابحث باسم الموقف أو العنوان...">
    <button id="clearSearch" class="btn" style="display:none;background:#f1f5f9;border:1px solid #e2e8f0;color:#475569;">
        <i class="bi bi-x-lg"></i>
    </button>
</div>

<p id="searchMeta" class="text-xs mb-3" style="color:#94a3b8;display:none;"></p>

{{-- Portrait card grid --}}
<div class="row g-4" id="cardGrid">

    @forelse($parkingLots as $lot)
    @php
        $pct      = $lot['total'] > 0 ? round($lot['occupied'] / $lot['total'] * 100) : 0;
        $avail    = $lot['avail'];
        $gradient = $gradients[$lot['id'] % 6];
        if ($avail === 0)                     { $badgeColor='rgba(239,68,68,.82)';   $badgeTxt='ممتلئ';          $barCol='#ef4444'; }
        elseif ($avail < $lot['total'] * 0.2) { $badgeColor='rgba(245,158,11,.82)'; $badgeTxt=$avail.' مكان';   $barCol='#f59e0b'; }
        else                                  { $badgeColor='rgba(16,185,129,.82)';  $badgeTxt=$avail.' متاح';   $barCol='#10b981'; }
    @endphp
    <div class="col-sm-6 col-md-4 col-lg-3 lot-card-wrap"
         data-name="{{ mb_strtolower($lot['name']) }}"
         data-address="{{ mb_strtolower($lot['address']) }}">

        <div class="lot-portrait-card" onclick="selectLot({{ $lot['id'] }})">

            {{-- Image or gradient placeholder --}}
            <div class="lot-card-img-wrap">
                @if($lot['image'])
                    <img src="{{ $lot['image'] }}" alt="{{ $lot['name'] }}">
                @else
                    <div class="lot-card-placeholder" style="background:{{ $gradient }};">
                        <i class="bi bi-buildings"></i>
                        <span>{{ $lot['name'] }}</span>
                    </div>
                @endif
                <span class="lot-card-avail-badge" style="background:{{ $badgeColor }};">
                    {{ $badgeTxt }}
                </span>
            </div>

            {{-- Card body --}}
            <div class="lot-card-body">
                <div class="lot-card-title">{{ $lot['name'] }}</div>
                <div class="lot-card-address">
                    <i class="bi bi-geo-alt flex-shrink-0"></i>
                    <span>{{ $lot['address'] }}</span>
                </div>

                <div class="occ-bar">
                    <div class="occ-bar-fill" style="width:{{ $pct }}%;background:{{ $barCol }};"></div>
                </div>

                <div class="lot-card-stats">
                    <span><i class="bi bi-car-front"></i>{{ $lot['total'] }} مكان</span>
                    <span><i class="bi bi-currency-exchange"></i>{{ number_format($lot['price']) }} ل.س/س</span>
                    <span><i class="bi bi-clock"></i>{{ $lot['hours'] }}</span>
                </div>

                <div class="lot-card-select-wrap">
                    <button class="lot-card-select-btn" onclick="event.stopPropagation();selectLot({{ $lot['id'] }})">
                        <i class="bi bi-check2-circle me-1"></i>اختر هذا الموقف
                    </button>
                </div>
            </div>

        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5" style="color:#94a3b8;">
        <i class="bi bi-buildings d-block mb-2" style="font-size:3rem;opacity:.3;"></i>
        <span>لا توجد مواقف نشطة</span>
    </div>
    @endforelse

    <div id="noResults" class="col-12 text-center py-4" style="color:#94a3b8;display:none;">
        <i class="bi bi-search d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
        <span class="text-sm">لا توجد نتائج مطابقة</span>
    </div>

</div>


{{-- ══════════════════════════════════════════════════════════════════════
     OPERATOR PANEL — lot selected
══════════════════════════════════════════════════════════════════════ --}}
@else
@php
    $pct      = $selectedLot->usage_percentage;
    $barColor = $pct >= 90 ? '#ef4444' : ($pct >= 60 ? '#f59e0b' : '#10b981');
@endphp

{{-- Slim lot info bar --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 px-1">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-buildings" style="color:#6366f1;font-size:1.1rem;"></i>
        <span class="fw-700" style="color:#0f172a;font-size:.95rem;">{{ $selectedLot->name }}</span>
        <span class="text-xs" style="color:#94a3b8;">{{ $selectedLot->address }}</span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="badge badge-soft-success">{{ $selectedLot->available_spaces }} متاح</span>
        <span class="badge badge-soft-warning">{{ $selectedLot->occupied_spaces }} مشغول</span>
        <a href="{{ route('operator.dashboard') }}"
           class="btn btn-sm fw-600"
           style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
            <i class="bi bi-arrow-repeat me-1"></i>تغيير الموقف
        </a>
        <span class="badge badge-soft-secondary text-xs" id="refresh-badge"></span>
    </div>
</div>

{{-- Search container --}}
<div class="search-container row align-items-center">
    <div class="col-md-8">
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-white" style="border-color:#e2e8f0;">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" id="searchInput" class="form-control" style="border-color:#e2e8f0;"
                   placeholder="ابحث برقم اللوحة أو اسم العميل...">
        </div>
    </div>
    <div class="col-md-4 mt-3 mt-md-0">
        <button class="btn btn-success btn-lg w-100 fw-700"
                style="font-family:'Cairo',sans-serif;border-radius:10px;"
                data-bs-toggle="modal" data-bs-target="#newEntryModal">
            <i class="bi bi-plus-lg me-1"></i>دخول مباشر
        </button>
    </div>
</div>

{{-- Section heading --}}
<h5 class="mb-4" style="color:#6c757d;font-family:'Cairo',sans-serif;">
    <i class="bi bi-grid-3x3-gap me-2"></i>قائمة الحجوزات والسيارات النشطة
</h5>

{{-- Cards grid --}}
<div class="row g-4" id="reservationsGrid">

    {{-- Reservation cards --}}
    @foreach($reservations as $res)
    <div class="col-md-4 col-lg-3 res-item"
         id="card-res-{{ $res->id }}"
         data-search="{{ mb_strtolower(($res->vehicle_plate ?? '').' '.($res->customer_name ?? '')) }}">
        <div class="card res-card">
            <div class="card-header-custom bg-reserved">
                <i class="bi bi-calendar-check me-1"></i>حجز مسبق #{{ $res->id }}
            </div>
            <div class="card-body text-center px-3 pb-3">
                <div class="plate-box">{{ $res->vehicle_plate ?? '—' }}</div>
                <p class="mb-1 text-muted small">
                    <i class="bi bi-person me-1"></i>{{ $res->customer_name ?? 'غير محدد' }}
                </p>
                @if($res->phone)
                <p class="mb-1 text-muted small" dir="ltr">{{ $res->phone }}</p>
                @endif
                <p class="mb-2 text-muted small">
                    <i class="bi bi-clock me-1"></i>
                    {{ $res->start_time->format('H:i') }} — {{ $res->end_time->format('H:i') }}
                    <span style="color:#adb5bd;">({{ $res->start_time->format('d/m') }})</span>
                </p>
                <hr class="my-2">
                <button class="btn btn-primary w-100 fw-bold mb-2"
                        id="checkin-btn-{{ $res->id }}"
                        onclick="activateRes({{ $res->id }}, this)">
                    <i class="bi bi-box-arrow-in-right me-1"></i>تسجيل دخول
                </button>
                <button class="btn btn-outline-danger btn-sm w-100"
                        id="checkout-{{ $res->id }}" disabled
                        onclick="openReceipt({{ $res->id }})">
                    <i class="bi bi-receipt me-1"></i>خروج وفاتورة
                </button>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Walk-in cards --}}
    @foreach($activeCars as $car)
    @php
        $elapsedMin = $car->start_time->diffInMinutes(now());
        $remainMins = now()->diffInMinutes($car->end_time, false);
        $isOverdue  = $remainMins < 0;
    @endphp
    <div class="col-md-4 col-lg-3 res-item"
         id="card-{{ $car->id }}"
         data-search="{{ mb_strtolower(($car->vehicle_plate ?? '').' '.($car->customer_name ?? '')) }}">
        <div class="card res-card border-success border-2">
            <div class="card-header-custom bg-direct">
                <i class="bi bi-car-front me-1"></i>داخل الموقف
            </div>
            <div class="card-body text-center px-3 pb-3">
                <div class="plate-box">{{ $car->vehicle_plate ?? '—' }}</div>
                <p class="mb-1 text-muted small">
                    <i class="bi bi-person me-1"></i>{{ $car->customer_name ?? 'غير محدد' }}
                </p>
                <p class="mb-1 text-muted small">
                    <i class="bi bi-clock-history me-1"></i>
                    دخل {{ $car->start_time->format('H:i') }} —
                    مرّ {{ floor($elapsedMin/60) }}س {{ $elapsedMin%60 }}د
                </p>
                @if($isOverdue)
                <p class="mb-2 small fw-600 text-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    تجاوز {{ floor(abs($remainMins)/60) }}س {{ abs($remainMins)%60 }}د
                </p>
                @else
                <p class="mb-2 small" style="color:#f59e0b;">
                    <i class="bi bi-hourglass-split me-1"></i>
                    متبقي {{ floor($remainMins/60) }}س {{ $remainMins%60 }}د
                </p>
                @endif
                <hr class="my-2">
                <button class="btn btn-outline-danger w-100 fw-bold"
                        onclick="openReceipt({{ $car->id }})">
                    <i class="bi bi-receipt me-1"></i>خروج وفاتورة
                </button>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Empty state --}}
    @if($activeCars->isEmpty() && $reservations->isEmpty())
    <div class="col-12 text-center py-5" style="color:#94a3b8;">
        <i class="bi bi-car-front d-block mb-3" style="font-size:3rem;opacity:.3;"></i>
        <p class="fw-600 mb-2" style="color:#64748b;font-size:1rem;">لا توجد سيارات أو حجوزات نشطة</p>
        <button class="btn btn-success fw-700 px-4" style="font-family:'Cairo',sans-serif;border-radius:10px;"
                data-bs-toggle="modal" data-bs-target="#newEntryModal">
            <i class="bi bi-plus-lg me-1"></i>تسجيل دخول مباشر
        </button>
    </div>
    @endif

    {{-- No search results --}}
    <div id="noSearchResults" class="col-12 text-center py-4" style="color:#94a3b8;display:none;">
        <i class="bi bi-search d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
        <span>لا توجد نتائج مطابقة</span>
    </div>

</div>

@endif {{-- end selectedLot --}}


{{-- ══════════════════════════════════════════════════════════════════════
     DIRECT ENTRY MODAL
══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="newEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">

            <div class="modal-header bg-success text-white" style="border:none;">
                <h5 class="modal-title fw-bold" style="font-family:'Cairo',sans-serif;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>تسجيل دخول مباشر
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="checkInForm">
                    @csrf
                    <input type="hidden" name="parking_lot_id" value="{{ $selectedLot->id ?? '' }}">

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-family:'Cairo',sans-serif;">
                            رقم لوحة السيارة <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="vehicle_plate" id="newPlateInput" required
                               class="form-control form-control-lg text-center fw-bold"
                               style="letter-spacing:5px;font-size:1.2rem;border-radius:10px;"
                               placeholder="أ ب ج 1234" autocomplete="off">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small" style="font-family:'Cairo',sans-serif;">اسم السائق</label>
                            <input type="text" name="customer_name" class="form-control" style="border-radius:10px;" placeholder="اختياري">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small" style="font-family:'Cairo',sans-serif;">الهاتف</label>
                            <input type="tel" name="phone" class="form-control" style="border-radius:10px;" placeholder="اختياري" dir="ltr">
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-bold small" style="font-family:'Cairo',sans-serif;">
                            المدة المتوقعة <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            @foreach([1,2,3,4,6,8,12,24,48,72] as $h)
                            <div class="col-4 col-sm-3">
                                <label class="d-block text-center py-2 rounded-3 duration-tile"
                                       style="border:2px solid #e2e8f0;cursor:pointer;font-size:.82rem;font-weight:600;color:#475569;transition:all .15s;">
                                    <input type="radio" name="duration_hours" value="{{ $h }}" class="d-none" {{ $h==2 ? 'checked' : '' }}>
                                    {{ $h >= 24 ? ($h/24).' يوم' : $h.'س' }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light px-4" style="border-radius:10px;font-family:'Cairo',sans-serif;"
                        data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="checkInBtn"
                        class="btn btn-success px-5 fw-bold"
                        style="border-radius:10px;font-family:'Cairo',sans-serif;"
                        onclick="submitCheckIn()">
                    <i class="bi bi-check-lg me-1"></i>تأكيد الدخول
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════
     CHECKOUT / RECEIPT MODAL
══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content border-0" style="border-radius:16px;overflow:hidden;">

            <div class="modal-header bg-danger text-white" style="border:none;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" style="font-family:'Cairo',sans-serif;">
                        <i class="bi bi-receipt me-2"></i>إتمام الدفع والمغادرة
                    </h5>
                    <div id="rcpt-lot" style="font-size:.78rem;opacity:.8;"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                {{-- Plate + duration --}}
                <div class="text-center mb-3">
                    <div class="text-muted small mb-1">رقم اللوحة</div>
                    <div class="plate-box mx-auto" style="max-width:280px;" id="rcpt-plate">—</div>
                    <div class="text-muted small mt-1" id="rcpt-name">—</div>
                </div>

                {{-- Times --}}
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-2 rounded-3 text-center" style="background:#f0fdf4;">
                            <div class="text-xs fw-600 mb-1" style="color:#64748b;">وقت الدخول</div>
                            <div class="fw-700 small" id="rcpt-entry" style="color:#059669;">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded-3 text-center" style="background:#fef2f2;">
                            <div class="text-xs fw-600 mb-1" style="color:#64748b;">وقت الخروج</div>
                            <div class="fw-700 small" id="rcpt-exit" style="color:#dc2626;">—</div>
                        </div>
                    </div>
                </div>

                {{-- Fee breakdown --}}
                <div class="mb-3">
                    <div class="fw-bold small mb-2" style="color:#0f172a;font-family:'Cairo',sans-serif;">تفصيل الأجرة</div>
                    <div id="rcpt-breakdown" style="background:#f8fafc;border-radius:10px;padding:10px 14px;">
                        <div class="text-center py-2" style="color:#94a3b8;">
                            <span class="spinner-border spinner-border-sm"></span>
                        </div>
                    </div>
                    <div class="fee-total mt-1 px-1">
                        <span style="font-family:'Cairo',sans-serif;">الإجمالي</span>
                        <span id="rcpt-total" class="h4 fw-bold mb-0" style="color:#27ae60;">—</span>
                    </div>
                </div>

                {{-- Payment method --}}
                <div class="mb-1">
                    <div class="fw-bold small mb-2" style="color:#0f172a;font-family:'Cairo',sans-serif;">طريقة الدفع</div>
                    <div class="payment-grid">
                        <label>
                            <input type="radio" name="payType" value="cash" class="pay-radio" checked
                                   onchange="selectPayment('cash')">
                            <div class="pay-option">
                                <i class="bi bi-cash-coin" style="color:#27ae60;"></i>
                                <span>نقداً</span>
                            </div>
                        </label>
                        <label>
                            <input type="radio" name="payType" value="upload" class="pay-radio"
                                   onchange="selectPayment('upload')">
                            <div class="pay-option">
                                <i class="bi bi-cloud-upload" style="color:#3498db;"></i>
                                <span>إيصال إلكتروني</span>
                            </div>
                        </label>
                    </div>

                    <div id="uploadArea" class="mt-3 p-3 border rounded-3 bg-light" style="display:none;">
                        <label class="form-label small fw-bold text-primary" style="font-family:'Cairo',sans-serif;">
                            إرفاق صورة التحويل:
                        </label>
                        <input type="file" id="paymentProofFile" class="form-control" accept="image/*,.pdf">
                        <div class="text-xs mt-1 text-muted">JPG / PNG / PDF — حد أقصى 4MB</div>
                    </div>
                </div>

            </div>

            <div class="modal-footer bg-light" style="border:none;">
                <button type="button" class="btn btn-light px-4" style="border-radius:10px;font-family:'Cairo',sans-serif;"
                        data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="confirmPayBtn"
                        class="btn btn-danger px-5 fw-bold"
                        style="border-radius:10px;font-family:'Cairo',sans-serif;">
                    <i class="bi bi-check2-circle me-1"></i>تأكيد الدفع
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     ACTIVATE RESERVATION CONFIRM MODAL
══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="activateConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header text-white border-0" style="background:linear-gradient(135deg,#1e3c72,#2a5298);">
                <h6 class="modal-title fw-bold mb-0" style="font-family:'Cairo',sans-serif;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>تأكيد فتح البوابة
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="mb-3" style="font-size:2.5rem;">🚗</div>
                <p class="mb-1 fw-600" style="color:#0f172a;font-family:'Cairo',sans-serif;">
                    هل تريد تفعيل هذا الحجز؟
                </p>
                <p class="text-muted small mb-0">سيتم تسجيل وقت الدخول الآن وفتح البوابة للسيارة.</p>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 gap-2">
                <button type="button" class="btn btn-light flex-fill fw-600" style="font-family:'Cairo',sans-serif;border-radius:10px;"
                        data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="activateConfirmBtn"
                        class="btn btn-primary flex-fill fw-bold"
                        style="font-family:'Cairo',sans-serif;border-radius:10px;">
                    <i class="bi bi-check2-circle me-1"></i>تأكيد الدخول
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const lots = {!! $parkingLots->toJson() !!};
const csrf = document.querySelector('meta[name="csrf-token"]').content;

// ── Toast helper ───────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
    const colors = { success: '#10b981', danger: '#ef4444', warning: '#f59e0b', info: '#3b82f6' };
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:1.25rem;inset-inline-end:1.25rem;z-index:9999;
        background:${colors[type]||colors.success};color:#fff;padding:.75rem 1.25rem;border-radius:10px;
        font-family:'Cairo',sans-serif;font-size:.9rem;font-weight:600;
        box-shadow:0 8px 24px rgba(0,0,0,.18);opacity:0;transition:opacity .25s;max-width:320px;`;
    t.textContent = msg;
    document.body.appendChild(t);
    requestAnimationFrame(() => { t.style.opacity = '1'; });
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3500);
}

function selectLot(id) { window.location = '/operator/dashboard?lot_id=' + id; }

@if(!$selectedLot)
// ═══════════════════════════════════════════════════════════
// LOT PICKER
// ═══════════════════════════════════════════════════════════
const searchEl = document.getElementById('lotSearch');
const clearBtn = document.getElementById('clearSearch');

searchEl.addEventListener('input', () => {
    const q = searchEl.value.trim().toLowerCase();
    clearBtn.style.display = q ? 'block' : 'none';
    let vis = 0;
    document.querySelectorAll('.lot-card-wrap').forEach(w => {
        const match = !q || w.dataset.name.includes(q) || w.dataset.address.includes(q);
        w.style.display = match ? '' : 'none';
        if (match) vis++;
    });
    document.getElementById('noResults').style.display = (vis === 0 && q) ? 'block' : 'none';
    const meta = document.getElementById('searchMeta');
    meta.style.display = q ? 'block' : 'none';
    meta.textContent = `${vis} نتيجة من أصل ${lots.length}`;
});
clearBtn.addEventListener('click', () => {
    searchEl.value = ''; searchEl.dispatchEvent(new Event('input')); searchEl.focus();
});

@else
// ═══════════════════════════════════════════════════════════
// OPERATOR PANEL
// ═══════════════════════════════════════════════════════════

// ── Search ─────────────────────────────────────────────────────────────
document.getElementById('searchInput').addEventListener('keyup', function() {
    const val = this.value.trim().toLowerCase();
    let vis = 0;
    document.querySelectorAll('.res-item').forEach(card => {
        const match = !val || card.dataset.search.includes(val);
        card.style.display = match ? '' : 'none';
        if (match) vis++;
    });
    const nr = document.getElementById('noSearchResults');
    if (nr) nr.style.display = (val && vis === 0) ? 'block' : 'none';
});

// ── Duration tiles ─────────────────────────────────────────────────────
function initDurationTiles() {
    document.querySelectorAll('.duration-tile').forEach(label => {
        const r = label.querySelector('input[type="radio"]');
        const active = 'border:2px solid #27ae60;background:#f0fdf4;color:#15803d;cursor:pointer;font-size:.82rem;font-weight:700;transition:all .15s;border-radius:.75rem;';
        const idle   = 'border:2px solid #e2e8f0;cursor:pointer;font-size:.82rem;font-weight:600;color:#475569;transition:all .15s;border-radius:.75rem;';
        if (r.checked) label.style.cssText = active;
        r.addEventListener('change', () => {
            document.querySelectorAll('.duration-tile').forEach(l => l.style.cssText = idle);
            label.style.cssText = active;
        });
    });
}
initDurationTiles();

// ── Direct entry submit ────────────────────────────────────────────────
async function submitCheckIn() {
    const form = document.getElementById('checkInForm');
    if (!form.vehicle_plate.value.trim()) { form.vehicle_plate.focus(); return; }
    const btn = document.getElementById('checkInBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري التسجيل...';
    try {
        const res  = await fetch('/operator/check-in', { method:'POST', body: new FormData(form) });
        const data = await res.json();
        if (data.success) {
            btn.classList.replace('btn-success', 'btn-primary');
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>تم!';
            setTimeout(() => location.reload(), 700);
        } else {
            showToast(data.message || 'حدث خطأ', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>تأكيد الدخول';
        }
    } catch {
        showToast('خطأ في الاتصال', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>تأكيد الدخول';
    }
}

document.getElementById('newEntryModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('checkInForm').reset();
    const btn = document.getElementById('checkInBtn');
    btn.disabled = false;
    btn.className = 'btn btn-success px-5 fw-bold';
    btn.style.cssText = 'border-radius:10px;font-family:Cairo,sans-serif;';
    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>تأكيد الدخول';
    initDurationTiles();
});

// ── Activate reservation ───────────────────────────────────────────────
let pendingActivateId  = null;
let pendingActivateBtn = null;
let activateModal      = null;

function getActivateModal() {
    if (!activateModal) activateModal = new bootstrap.Modal(document.getElementById('activateConfirmModal'));
    return activateModal;
}

function activateRes(id, btn) {
    pendingActivateId  = id;
    pendingActivateBtn = btn;
    getActivateModal().show();
}

document.getElementById('activateConfirmBtn').addEventListener('click', async () => {
    const id  = pendingActivateId;
    const btn = pendingActivateBtn;
    if (!id || !btn) return;

    getActivateModal().hide();

    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    try {
        const res  = await fetch(`/operator/${id}/activate`, {
            method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Content-Type':'application/json'}
        });
        const data = await res.json();
        if (data.success) {
            btn.classList.replace('btn-primary', 'btn-success');
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>تم الدخول';
            const co = document.getElementById('checkout-' + id);
            if (co) { co.disabled = false; }
        } else {
            showToast(data.message || 'حدث خطأ', 'danger');
            btn.innerHTML = orig; btn.disabled = false;
        }
    } catch {
        showToast('خطأ في الاتصال', 'danger');
        btn.innerHTML = orig; btn.disabled = false;
    }
});

// ── Receipt modal ──────────────────────────────────────────────────────
let receiptModal     = null;
let currentBookingId = null;
let selectedPayment  = 'cash';

function getModal() {
    if (!receiptModal) receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    return receiptModal;
}

async function openReceipt(id) {
    currentBookingId = id;
    selectedPayment  = 'cash';
    // reset radio
    document.querySelector('input[name="payType"][value="cash"]').checked = true;
    selectPayment('cash');

    ['rcpt-plate','rcpt-name','rcpt-duration','rcpt-entry','rcpt-exit','rcpt-total','rcpt-lot'].forEach(i => {
        const el = document.getElementById(i);
        if (el) el.textContent = '—';
    });
    document.getElementById('rcpt-breakdown').innerHTML =
        '<div class="text-center py-2" style="color:#94a3b8;"><span class="spinner-border spinner-border-sm"></span></div>';

    getModal().show();

    try {
        const res  = await fetch(`/operator/${id}/checkout-preview`);
        const data = await res.json();
        if (!data.success) { showToast(data.message || 'حدث خطأ', 'danger'); getModal().hide(); return; }
        const d = data.data;

        document.getElementById('rcpt-lot').textContent   = d.lot_name;
        document.getElementById('rcpt-plate').textContent = d.plate         || '—';
        document.getElementById('rcpt-name').textContent  = d.customer_name || 'غير محدد';
        document.getElementById('rcpt-entry').textContent = d.entry_time;
        document.getElementById('rcpt-exit').textContent  = d.exit_time;
        document.getElementById('rcpt-total').textContent = Number(d.total_fee).toLocaleString('ar-SA') + ' ل.س';

        const rows = d.fee_details.map(r => `
            <div class="fee-row">
                <span>${r.day} <small style="color:#94a3b8;">${r.date}</small></span>
                <span style="color:#64748b;">${r.hours}س × ${Number(r.rate).toLocaleString('ar-SA')}</span>
                <span class="fw-600" style="color:#0f172a;">${Number(r.subtotal).toLocaleString('ar-SA')} ل.س</span>
            </div>`).join('');
        document.getElementById('rcpt-breakdown').innerHTML =
            rows || '<p class="text-xs text-center" style="color:#94a3b8;">لا تفاصيل</p>';
    } catch { showToast('تعذّر تحميل بيانات الفاتورة', 'danger'); getModal().hide(); }
}

function selectPayment(method) {
    selectedPayment = method;
    document.getElementById('uploadArea').style.display = method === 'upload' ? 'block' : 'none';
}

document.getElementById('confirmPayBtn').addEventListener('click', async () => {
    if (!currentBookingId) return;
    if (selectedPayment === 'upload' && !document.getElementById('paymentProofFile').files.length) {
        showToast('يرجى رفع إيصال الدفع', 'warning'); return;
    }
    const btn = document.getElementById('confirmPayBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري المعالجة...';

    const fd = new FormData();
    fd.append('payment_method', selectedPayment);
    if (selectedPayment === 'upload') {
        fd.append('payment_proof', document.getElementById('paymentProofFile').files[0]);
    }

    try {
        const res  = await fetch(`/operator/${currentBookingId}/payment`, {
            method:'POST', headers:{'X-CSRF-TOKEN':csrf}, body: fd
        });
        const data = await res.json();
        if (data.success) {
            getModal().hide();
            const card = document.getElementById('card-' + currentBookingId);
            if (card) { card.style.transition='opacity .4s'; card.style.opacity='0'; setTimeout(()=>card.remove(),400); }
            setTimeout(() => location.reload(), 600);
        } else {
            showToast(data.message || 'حدث خطأ', 'danger');
            btn.disabled  = false;
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>تأكيد الدفع';
        }
    } catch {
        showToast('خطأ في الاتصال', 'danger');
        btn.disabled  = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>تأكيد الدفع';
    }
});

document.getElementById('receiptModal').addEventListener('hidden.bs.modal', () => {
    const btn = document.getElementById('confirmPayBtn');
    btn.disabled  = false;
    btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>تأكيد الدفع';
    document.getElementById('paymentProofFile').value = '';
});

// ── Auto-refresh (pauses while any modal is open) ─────────────────────
let t = 30;
let refreshPaused = false;
const badge = document.getElementById('refresh-badge');

document.querySelectorAll('.modal').forEach(m => {
    m.addEventListener('show.bs.modal',   () => { refreshPaused = true; });
    m.addEventListener('hidden.bs.modal', () => { refreshPaused = false; t = 30; });
});

setInterval(() => {
    if (refreshPaused) return;
    t--;
    if (badge) badge.textContent = `تحديث بعد ${t}ث`;
    if (t <= 0) location.reload();
}, 1000);

@endif
</script>
@endpush
@endsection
