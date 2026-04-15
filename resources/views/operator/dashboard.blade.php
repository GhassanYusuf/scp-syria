@extends('layouts.admin')
@section('title', 'لوحة المشغّل — دمشق باركينغ')
@section('page-title', 'لوحة المشغّل')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<style>
    /* ── Leaflet ──────────────────────────────────────────────────────── */
    .leaflet-container img { max-width:none !important; box-shadow:none !important; }
    .leaflet-container     { direction:ltr; }

    /* ── Lot picker cards ─────────────────────────────────────────────── */
    .lot-picker-card {
        background:#fff; border:2px solid #e2e8f0; border-radius:.875rem;
        padding:1.125rem 1.25rem; cursor:pointer;
        transition:border-color .18s, box-shadow .18s, transform .18s;
        position:relative; overflow:hidden;
    }
    .lot-picker-card::before {
        content:''; position:absolute; inset-inline-start:0; top:0; bottom:0;
        width:4px; background:#6366f1; opacity:0; transition:opacity .18s;
    }
    .lot-picker-card:hover { border-color:#a5b4fc; box-shadow:0 4px 20px rgba(99,102,241,.12); transform:translateY(-2px); }
    .lot-picker-card:hover::before,
    .lot-picker-card.highlighted::before { opacity:1; }
    .lot-picker-card.highlighted { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.15); }
    .lot-picker-card .select-btn {
        display:none; width:100%; margin-top:.875rem; padding:.45rem;
        background:#6366f1; color:#fff; border:none; border-radius:.5rem;
        font-family:'Cairo',sans-serif; font-weight:700; font-size:.875rem; cursor:pointer;
    }
    .lot-picker-card:hover .select-btn,
    .lot-picker-card.highlighted .select-btn { display:block; }
    .lot-picker-card .select-btn:hover { background:#4f46e5; }
    .occ-bar { height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden; margin:.5rem 0 .625rem; }
    .occ-bar-fill { height:100%; border-radius:3px; transition:width .4s; }

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
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════════════════
     LOT PICKER — no lot selected
══════════════════════════════════════════════════════════════════════ --}}
@if(!$selectedLot)

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h2 class="fw-800 mb-0" style="font-size:1.1rem;color:#0f172a;">اختر موقف السيارات</h2>
        <p class="text-sm mb-0" style="color:#64748b;">ابحث عن موقفك وابدأ إدارة السيارات</p>
    </div>
    <span class="badge badge-soft-primary">{{ $parkingLots->count() }} موقف متاح</span>
</div>

<div class="input-group mb-4" style="max-width:480px;">
    <span class="input-group-text" style="background:#fff;border-color:#e2e8f0;">
        <i class="bi bi-search" style="color:#94a3b8;"></i>
    </span>
    <input type="text" id="lotSearch" class="form-control" style="border-color:#e2e8f0;"
           placeholder="ابحث باسم الموقف أو العنوان...">
    <button id="clearSearch" class="btn" style="display:none;background:#f1f5f9;border:1px solid #e2e8f0;color:#475569;">
        <i class="bi bi-x-lg"></i>
    </button>
</div>

<div class="row g-3">
    <div class="col-lg-6 col-xl-5" id="cardsCol">
        <p id="searchMeta" class="text-xs mb-2" style="color:#94a3b8;display:none;"></p>
        <div id="cardGrid" class="row g-3">
            @forelse($parkingLots as $lot)
            @php
                $pct   = $lot['total'] > 0 ? round($lot['occupied'] / $lot['total'] * 100) : 0;
                $avail = $lot['avail'];
                if ($avail === 0)                      { $badgeCls='badge-soft-danger';  $badgeTxt='ممتلئ';            $barCol='#ef4444'; }
                elseif ($avail < $lot['total'] * 0.2) { $badgeCls='badge-soft-warning'; $badgeTxt=$avail.' محدود';   $barCol='#f59e0b'; }
                else                                   { $badgeCls='badge-soft-success'; $badgeTxt=$avail.' متاح';    $barCol='#10b981'; }
            @endphp
            <div class="col-12 lot-card-wrap"
                 data-name="{{ mb_strtolower($lot['name']) }}"
                 data-address="{{ mb_strtolower($lot['address']) }}">
                <div class="lot-picker-card" id="lcard-{{ $lot['id'] }}" onclick="selectLot({{ $lot['id'] }})">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <div class="fw-700" style="color:#0f172a;font-size:.95rem;">{{ $lot['name'] }}</div>
                            <div class="text-xs mt-1" style="color:#94a3b8;">
                                <i class="bi bi-geo-alt me-1"></i>{{ $lot['address'] }}
                            </div>
                        </div>
                        <span class="badge {{ $badgeCls }} flex-shrink-0" style="font-size:.72rem;">{{ $badgeTxt }}</span>
                    </div>
                    <div class="occ-bar mt-2">
                        <div class="occ-bar-fill" style="width:{{ $pct }}%;background:{{ $barCol }};"></div>
                    </div>
                    <div class="d-flex gap-3 text-xs" style="color:#64748b;">
                        <span><i class="bi bi-car-front me-1"></i>{{ $lot['total'] }} مكان</span>
                        <span><i class="bi bi-currency-exchange me-1"></i>{{ number_format($lot['price']) }}/ساعة</span>
                        <span><i class="bi bi-clock me-1"></i>{{ $lot['hours'] }}</span>
                    </div>
                    <button class="select-btn" onclick="event.stopPropagation();selectLot({{ $lot['id'] }})">
                        <i class="bi bi-check2-circle me-2"></i>اختر هذا الموقف
                    </button>
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5" style="color:#94a3b8;">
                <i class="bi bi-buildings d-block mb-2" style="font-size:2.5rem;opacity:.3;"></i>
                <span class="text-sm">لا توجد مواقف نشطة</span>
            </div>
            @endforelse
            <div id="noResults" class="col-12 text-center py-4" style="color:#94a3b8;display:none;">
                <i class="bi bi-search d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
                <span class="text-sm">لا توجد نتائج مطابقة</span>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-xl-7" id="mapCol">
        <div class="card" style="height:520px;overflow:hidden;">
            <div class="card-header py-2 d-flex align-items-center justify-content-between">
                <span class="fw-700 text-sm" style="color:#0f172a;">
                    <i class="bi bi-map me-1" style="color:#6366f1;"></i>خريطة المواقف
                </span>
                <span class="badge badge-soft-info text-xs">اضغط على الموقف للتحديد</span>
            </div>
            <div id="opMap" style="height:calc(100% - 49px);"></div>
        </div>
    </div>
</div>

<div class="d-flex d-lg-none gap-2 mt-3">
    <button class="btn btn-sm fw-600 flex-fill" id="mTabCards" onclick="mobileTab('cards')"
            style="background:#6366f1;color:#fff;border:none;font-family:'Cairo',sans-serif;">
        <i class="bi bi-grid me-1"></i>البطاقات
    </button>
    <button class="btn btn-sm fw-600 flex-fill" id="mTabMap" onclick="mobileTab('map')"
            style="background:#f1f5f9;color:#475569;border:none;font-family:'Cairo',sans-serif;">
        <i class="bi bi-map me-1"></i>الخريطة
    </button>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const lots = {!! $parkingLots->toJson() !!};
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function selectLot(id) { window.location = '/operator/dashboard?lot_id=' + id; }

@if(!$selectedLot)
// ═══════════════════════════════════════════════════════════
// LOT PICKER
// ═══════════════════════════════════════════════════════════
const map = L.map('opMap').setView([33.5138, 36.2765], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(map);
const markers = {};

lots.forEach(l => {
    const col  = l.avail === 0 ? '#ef4444' : (l.avail < l.total * .2 ? '#f59e0b' : '#10b981');
    const pct  = l.total > 0 ? Math.round(l.occupied / l.total * 100) : 0;
    const icon = L.divIcon({
        html:`<div style="width:40px;height:40px;background:${col};border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 3px 12px rgba(0,0,0,.3);"></div>`,
        iconSize:[40,40], iconAnchor:[20,40], className:''
    });
    const m = L.marker([l.lat, l.lng], { icon }).addTo(map);
    markers[l.id] = m;
    m.bindPopup(`
        <div style="font-family:'Cairo',sans-serif;min-width:190px;padding:4px 2px;">
            <strong style="color:#0f172a;">${l.name}</strong>
            <p style="margin:4px 0 8px;font-size:.78rem;color:#64748b;">${l.address}</p>
            <div style="height:5px;background:#e2e8f0;border-radius:3px;overflow:hidden;margin-bottom:8px;">
                <div style="height:100%;width:${pct}%;background:${col};border-radius:3px;"></div>
            </div>
            <button onclick="selectLot(${l.id})"
                style="width:100%;padding:7px;background:${col};color:#fff;border:none;border-radius:6px;font-family:'Cairo',sans-serif;font-size:.82rem;font-weight:700;cursor:pointer;">
                اختر هذا الموقف
            </button>
        </div>
    `);
    m.on('click', () => highlightCard(l.id));
});

if (lots.length) {
    const g = L.featureGroup(lots.map(l => L.marker([l.lat, l.lng])));
    map.fitBounds(g.getBounds().pad(.15));
}

let hlId = null;
function highlightCard(id) {
    if (hlId) document.getElementById('lcard-' + hlId)?.classList.remove('highlighted');
    hlId = id;
    const card = document.getElementById('lcard-' + id);
    if (card) { card.classList.add('highlighted'); card.scrollIntoView({ behavior:'smooth', block:'nearest' }); }
    const l = lots.find(x => x.id === id);
    if (l) map.setView([l.lat, l.lng], 15, { animate:true });
}

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
    document.getElementById('noResults').style.display = vis === 0 ? 'block' : 'none';
    const meta = document.getElementById('searchMeta');
    meta.style.display = q ? 'block' : 'none';
    meta.textContent = `${vis} نتيجة من أصل ${lots.length}`;
});
clearBtn.addEventListener('click', () => {
    searchEl.value = ''; searchEl.dispatchEvent(new Event('input')); searchEl.focus();
});

function mobileTab(tab) {
    const cc = document.getElementById('cardsCol'), mc = document.getElementById('mapCol');
    const bc = document.getElementById('mTabCards'), bm = document.getElementById('mTabMap');
    if (tab === 'map') {
        cc.style.display='none'; mc.style.display='';
        bm.style.cssText='background:#6366f1;color:#fff;border:none;font-family:Cairo,sans-serif;';
        bc.style.cssText='background:#f1f5f9;color:#475569;border:none;font-family:Cairo,sans-serif;';
        setTimeout(() => map.invalidateSize(), 80);
    } else {
        cc.style.display=''; mc.style.display='';
        bc.style.cssText='background:#6366f1;color:#fff;border:none;font-family:Cairo,sans-serif;';
        bm.style.cssText='background:#f1f5f9;color:#475569;border:none;font-family:Cairo,sans-serif;';
    }
}
if (window.innerWidth < 992) document.getElementById('mapCol').style.display = 'none';

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
            alert(data.message || 'حدث خطأ');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>تأكيد الدخول';
        }
    } catch {
        alert('خطأ في الاتصال');
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
async function activateRes(id, btn) {
    if (!confirm('تأكيد فتح البوابة لهذا الحجز؟')) return;
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    try {
        const res  = await fetch(`/operator/${id}/activate`, {
            method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Content-Type':'application/json'}
        });
        const data = await res.json();
        if (data.success) {
            // Check-in button → success state
            btn.classList.replace('btn-primary', 'btn-success');
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>تم الدخول';
            // Enable checkout button
            const co = document.getElementById('checkout-' + id);
            if (co) { co.disabled = false; }
        } else {
            alert(data.message || 'حدث خطأ');
            btn.innerHTML = orig; btn.disabled = false;
        }
    } catch {
        alert('خطأ في الاتصال');
        btn.innerHTML = orig; btn.disabled = false;
    }
}

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
        if (!data.success) { alert(data.message); return; }
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
    } catch { alert('تعذّر تحميل بيانات الفاتورة'); }
}

function selectPayment(method) {
    selectedPayment = method;
    document.getElementById('uploadArea').style.display = method === 'upload' ? 'block' : 'none';
}

document.getElementById('confirmPayBtn').addEventListener('click', async () => {
    if (!currentBookingId) return;
    if (selectedPayment === 'upload' && !document.getElementById('paymentProofFile').files.length) {
        alert('يرجى رفع إيصال الدفع'); return;
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
            alert(data.message || 'حدث خطأ');
            btn.disabled  = false;
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>تأكيد الدفع';
        }
    } catch {
        alert('خطأ في الاتصال');
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

// ── Auto-refresh ───────────────────────────────────────────────────────
let t = 30;
const badge = document.getElementById('refresh-badge');
setInterval(() => {
    t--;
    if (badge) badge.textContent = `تحديث بعد ${t}ث`;
    if (t <= 0) location.reload();
}, 1000);

@endif
</script>
@endpush
@endsection
