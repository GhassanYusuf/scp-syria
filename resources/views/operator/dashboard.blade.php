@extends('layouts.admin')
@section('title', 'لوحة المشغّل — دمشق باركينغ')
@section('page-title', 'لوحة المشغّل')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<style>
    .leaflet-container img { max-width:none !important; box-shadow:none !important; }
    .leaflet-container     { direction:ltr; }

    /* ── Lot picker cards ─────────────────────────────────────────────── */
    .lot-picker-card {
        background:#fff;
        border:2px solid #e2e8f0;
        border-radius:.875rem;
        padding:1.125rem 1.25rem;
        cursor:pointer;
        transition:border-color .18s, box-shadow .18s, transform .18s;
        position:relative;
        overflow:hidden;
    }
    .lot-picker-card::before {
        content:'';
        position:absolute;
        inset-inline-start:0; top:0; bottom:0;
        width:4px;
        background:#6366f1;
        opacity:0;
        transition:opacity .18s;
        border-radius:2px 0 0 2px;
    }
    .lot-picker-card:hover { border-color:#a5b4fc; box-shadow:0 4px 20px rgba(99,102,241,.12); transform:translateY(-2px); }
    .lot-picker-card:hover::before { opacity:1; }
    .lot-picker-card.highlighted   { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.15); }
    .lot-picker-card.highlighted::before { opacity:1; }

    .lot-picker-card .select-btn {
        display:none;
        width:100%;
        margin-top:.875rem;
        padding:.45rem;
        background:#6366f1;
        color:#fff;
        border:none;
        border-radius:.5rem;
        font-family:'Cairo',sans-serif;
        font-weight:700;
        font-size:.875rem;
        cursor:pointer;
        transition:background .15s;
    }
    .lot-picker-card:hover .select-btn,
    .lot-picker-card.highlighted .select-btn { display:block; }
    .lot-picker-card .select-btn:hover { background:#4f46e5; }

    /* Occupancy bar */
    .occ-bar { height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden; margin:.5rem 0 .625rem; }
    .occ-bar-fill { height:100%; border-radius:3px; transition:width .4s; }

    /* ── Operator panel header ───────────────────────────────────────── */
    .op-header {
        background:linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#3730a3 100%);
        border-radius:.875rem;
        padding:1.25rem 1.5rem;
        color:#fff;
        margin-bottom:1.5rem;
    }

    /* ── Booking cards ───────────────────────────────────────────────── */
    .booking-card {
        background:#fff;
        border:none;
        border-radius:1rem;
        box-shadow:0 4px 16px rgba(0,0,0,.06);
        overflow:hidden;
        transition:transform .2s, box-shadow .2s;
        height:100%;
    }
    .booking-card:hover {
        transform:translateY(-5px);
        box-shadow:0 10px 28px rgba(0,0,0,.1);
    }
    .booking-card-header {
        padding:.75rem 1rem;
        font-weight:700;
        font-size:.8rem;
        color:#fff;
        text-align:center;
        letter-spacing:.02em;
    }
    .booking-card-header.reservation { background:linear-gradient(90deg,#1e1b4b,#4338ca); }
    .booking-card-header.walkin      { background:linear-gradient(90deg,#064e3b,#059669); }

    .plate-display {
        font-size:1.6rem;
        font-weight:900;
        letter-spacing:.12em;
        color:#0f172a;
        background:#f8fafc;
        border:2px solid #e2e8f0;
        border-radius:.625rem;
        padding:.6rem 1rem;
        text-align:center;
        margin:.875rem;
        font-family:monospace;
        direction:ltr;
    }

    .booking-card-body {
        padding:0 .875rem .875rem;
    }

    .booking-card-body .customer-name {
        text-align:center;
        color:#64748b;
        font-size:.82rem;
        margin-bottom:.5rem;
    }

    .time-badge {
        display:flex;
        align-items:center;
        justify-content:center;
        gap:.375rem;
        background:#f1f5f9;
        border-radius:.5rem;
        padding:.35rem .625rem;
        font-size:.78rem;
        color:#475569;
        margin-bottom:.75rem;
    }

    .booking-card-divider { border:none; border-top:1px dashed #e2e8f0; margin:.625rem 0; }

    /* ── Receipt modal ────────────────────────────────────────────────── */
    .fee-row { display:flex; justify-content:space-between; padding:.35rem 0; border-bottom:1px dashed #f1f5f9; font-size:.875rem; }
    .fee-row:last-child { border-bottom:none; }
    .fee-total { display:flex; justify-content:space-between; padding:.625rem 0; border-top:2px solid #e2e8f0; font-weight:800; font-size:1.05rem; color:#0f172a; margin-top:.25rem; }
    .pay-method { border:2px solid #e2e8f0; border-radius:.625rem; padding:.875rem 1rem; cursor:pointer; transition:border-color .15s,background .15s; text-align:center; }
    .pay-method:hover { border-color:#a5b4fc; }
    .pay-method.selected { border-color:#6366f1; background:#f0f4ff; }
    .pay-method i { font-size:1.5rem; display:block; margin-bottom:.25rem; }

    /* Empty state */
    .empty-state { text-align:center; padding:3rem 1rem; color:#94a3b8; }
    .empty-state i { font-size:3rem; display:block; margin-bottom:.75rem; opacity:.3; }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════════════════════════════════════════
     LOT PICKER — shown when no lot is selected
══════════════════════════════════════════════════════════════════════ --}}
@if(!$selectedLot)

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h2 class="fw-800 mb-0" style="font-size:1.1rem;color:#0f172a;">اختر موقف السيارات</h2>
        <p class="text-sm mb-0" style="color:#64748b;">ابحث عن موقفك وابدأ إدارة السيارات</p>
    </div>
    <span class="badge badge-soft-primary">{{ $parkingLots->count() }} موقف متاح</span>
</div>

{{-- Search bar --}}
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

    {{-- ── Cards column ──────────────────────────────────────────────── --}}
    <div class="col-lg-6 col-xl-5" id="cardsCol">
        <p id="searchMeta" class="text-xs mb-2" style="color:#94a3b8;display:none;"></p>
        <div id="cardGrid" class="row g-3">

            @forelse($parkingLots as $lot)
            @php
                $pct   = $lot['total'] > 0 ? round($lot['occupied'] / $lot['total'] * 100) : 0;
                $avail = $lot['avail'];
                if ($avail === 0)                     { $badgeCls = 'badge-soft-danger';  $badgeTxt = 'ممتلئ'; $barCol = '#ef4444'; }
                elseif ($avail < $lot['total'] * 0.2) { $badgeCls = 'badge-soft-warning'; $badgeTxt = $avail.' محدود'; $barCol = '#f59e0b'; }
                else                                  { $badgeCls = 'badge-soft-success'; $badgeTxt = $avail.' متاح';   $barCol = '#10b981'; }
            @endphp
            <div class="col-12 lot-card-wrap"
                 data-name="{{ mb_strtolower($lot['name']) }}"
                 data-address="{{ mb_strtolower($lot['address']) }}">
                <div class="lot-picker-card" id="lcard-{{ $lot['id'] }}"
                     onclick="selectLot({{ $lot['id'] }})">

                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <div class="fw-700" style="color:#0f172a;font-size:.95rem;line-height:1.3;">{{ $lot['name'] }}</div>
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

    {{-- ── Map column ─────────────────────────────────────────────────── --}}
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

{{-- Mobile tab toggle --}}
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
     OPERATOR PANEL — shown after lot is selected
══════════════════════════════════════════════════════════════════════ --}}
@else
@php
    $pct      = $selectedLot->usage_percentage;
    $barColor = $pct >= 90 ? '#ef4444' : ($pct >= 60 ? '#f59e0b' : '#10b981');
    $totalCards = $activeCars->count() + $reservations->count();
@endphp

{{-- ── Selected lot header ─────────────────────────────────────────── --}}
<div class="op-header d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <div style="width:48px;height:48px;background:rgba(255,255,255,.15);border-radius:.75rem;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
            <i class="bi bi-buildings"></i>
        </div>
        <div>
            <div class="fw-800" style="font-size:1.1rem;line-height:1.3;">{{ $selectedLot->name }}</div>
            <div style="font-size:.78rem;opacity:.72;"><i class="bi bi-geo-alt me-1"></i>{{ $selectedLot->address }}</div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex gap-3 text-center">
            <div>
                <div class="fw-800" style="font-size:1.3rem;line-height:1;color:#6ee7b7;">{{ $selectedLot->available_spaces }}</div>
                <div style="font-size:.7rem;opacity:.7;">متاح</div>
            </div>
            <div style="width:1px;background:rgba(255,255,255,.2);"></div>
            <div>
                <div class="fw-800" style="font-size:1.3rem;line-height:1;color:#fcd34d;">{{ $selectedLot->occupied_spaces }}</div>
                <div style="font-size:.7rem;opacity:.7;">مشغول</div>
            </div>
            <div style="width:1px;background:rgba(255,255,255,.2);"></div>
            <div>
                <div class="fw-800" style="font-size:1.3rem;line-height:1;color:#e2e8f0;">{{ $selectedLot->total_capacity }}</div>
                <div style="font-size:.7rem;opacity:.7;">إجمالي</div>
            </div>
        </div>

        <div style="min-width:120px;">
            <div class="d-flex justify-content-between mb-1" style="font-size:.7rem;opacity:.75;">
                <span>الإشغال</span><span>{{ round($pct) }}%</span>
            </div>
            <div style="height:6px;background:rgba(255,255,255,.2);border-radius:3px;overflow:hidden;">
                <div style="height:100%;width:{{ $pct }}%;background:{{ $barColor }};border-radius:3px;transition:width .5s;"></div>
            </div>
        </div>

        <a href="{{ route('operator.dashboard') }}"
           class="btn btn-sm fw-600"
           style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:.5rem;font-family:'Cairo',sans-serif;">
            <i class="bi bi-arrow-repeat me-1"></i>تغيير الموقف
        </a>
    </div>
</div>

{{-- ── Toolbar: search + new entry ─────────────────────────────────── --}}
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <div class="input-group flex-grow-1" style="max-width:420px;">
        <span class="input-group-text" style="background:#fff;border-color:#e2e8f0;">
            <i class="bi bi-search" style="color:#94a3b8;"></i>
        </span>
        <input type="text" id="cardSearch" class="form-control" style="border-color:#e2e8f0;"
               placeholder="ابحث برقم اللوحة أو اسم العميل...">
        <button id="clearCardSearch" class="btn" style="display:none;background:#f1f5f9;border:1px solid #e2e8f0;color:#475569;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="d-flex gap-2 me-auto">
        <span class="badge badge-soft-primary" style="font-size:.78rem;padding:.4em .8em;">
            <i class="bi bi-calendar-check me-1"></i>{{ $reservations->count() }} حجز مسبق
        </span>
        <span class="badge badge-soft-success" style="font-size:.78rem;padding:.4em .8em;">
            <i class="bi bi-car-front me-1"></i>{{ $activeCars->count() }} داخل الآن
        </span>
    </div>

    <button class="btn fw-700"
            style="background:#10b981;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.5rem 1.125rem;"
            data-bs-toggle="modal" data-bs-target="#newEntryModal">
        <i class="bi bi-plus-lg me-1"></i>دخول مباشر جديد
    </button>
</div>

{{-- ── Booking cards grid ───────────────────────────────────────────── --}}
<div class="row g-3" id="bookingCards">

    {{-- Reservation cards (not yet arrived) --}}
    @foreach($reservations as $res)
    <div class="col-sm-6 col-lg-4 col-xl-3 booking-card-wrap"
         data-plate="{{ mb_strtolower($res->vehicle_plate ?? '') }}"
         data-name="{{ mb_strtolower($res->customer_name ?? '') }}">
        <div class="booking-card">
            <div class="booking-card-header reservation">
                <i class="bi bi-calendar-check me-1"></i>حجز مسبق #{{ $res->id }}
            </div>

            <div class="plate-display">{{ $res->vehicle_plate ?? '—' }}</div>

            <div class="booking-card-body">
                <div class="customer-name">
                    <i class="bi bi-person me-1"></i>{{ $res->customer_name ?? 'غير محدد' }}
                    @if($res->phone)
                    <div style="direction:ltr;font-size:.75rem;color:#94a3b8;">{{ $res->phone }}</div>
                    @endif
                </div>

                <div class="time-badge">
                    <i class="bi bi-clock" style="color:#6366f1;"></i>
                    {{ $res->start_time->format('H:i') }} — {{ $res->end_time->format('H:i') }}
                    <span style="color:#94a3b8;">{{ $res->start_time->format('d/m') }}</span>
                </div>

                <hr class="booking-card-divider">

                <button class="btn w-100 fw-700 mb-2 btn-checkin-{{ $res->id }}"
                        style="background:#6366f1;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.5rem;"
                        onclick="activateRes({{ $res->id }}, this)">
                    <i class="bi bi-box-arrow-in-right me-1"></i>تسجيل الدخول
                </button>
                <button class="btn btn-outline-danger w-100 fw-600"
                        style="border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.4rem;font-size:.82rem;opacity:.4;cursor:not-allowed;"
                        id="checkout-res-{{ $res->id }}" disabled>
                    <i class="bi bi-receipt me-1"></i>خروج وفاتورة
                </button>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Walk-in cards (already inside) --}}
    @foreach($activeCars as $car)
    @php
        $elapsedMin = $car->start_time->diffInMinutes(now());
        $remainMins = now()->diffInMinutes($car->end_time, false);
        $isOverdue  = $remainMins < 0;
    @endphp
    <div class="col-sm-6 col-lg-4 col-xl-3 booking-card-wrap"
         id="card-{{ $car->id }}"
         data-plate="{{ mb_strtolower($car->vehicle_plate ?? '') }}"
         data-name="{{ mb_strtolower($car->customer_name ?? '') }}">
        <div class="booking-card">
            <div class="booking-card-header walkin">
                <i class="bi bi-car-front me-1"></i>داخل الموقف
            </div>

            <div class="plate-display">{{ $car->vehicle_plate ?? '—' }}</div>

            <div class="booking-card-body">
                <div class="customer-name">
                    <i class="bi bi-person me-1"></i>{{ $car->customer_name ?? 'غير محدد' }}
                    @if($car->phone)
                    <div style="direction:ltr;font-size:.75rem;color:#94a3b8;">{{ $car->phone }}</div>
                    @endif
                </div>

                <div class="time-badge">
                    <i class="bi bi-clock-history" style="color:#10b981;"></i>
                    دخل {{ $car->start_time->format('H:i') }}
                    &nbsp;·&nbsp;
                    {{ floor($elapsedMin/60) }}س {{ $elapsedMin%60 }}د
                </div>

                @if($isOverdue)
                <div class="text-center mb-2">
                    <span class="badge badge-soft-danger" style="font-size:.72rem;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        تجاوز {{ floor(abs($remainMins)/60) }}س {{ abs($remainMins)%60 }}د
                    </span>
                </div>
                @else
                <div class="text-center mb-2">
                    <span class="badge badge-soft-warning" style="font-size:.72rem;">
                        متبقي {{ floor($remainMins/60) }}س {{ $remainMins%60 }}د
                    </span>
                </div>
                @endif

                <hr class="booking-card-divider">

                <button onclick="openReceipt({{ $car->id }})"
                        class="btn w-100 fw-700"
                        style="background:#ef4444;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.5rem;">
                    <i class="bi bi-receipt me-1"></i>خروج وفاتورة
                </button>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Empty state --}}
    @if($totalCards === 0)
    <div class="col-12">
        <div class="empty-state">
            <i class="bi bi-car-front"></i>
            <p class="fw-600 mb-1" style="color:#64748b;font-size:1rem;">لا توجد سيارات أو حجوزات نشطة</p>
            <p class="text-sm mb-3" style="color:#94a3b8;">سجّل دخول سيارة جديدة للبدء</p>
            <button class="btn fw-700"
                    style="background:#10b981;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.5rem 1.5rem;"
                    data-bs-toggle="modal" data-bs-target="#newEntryModal">
                <i class="bi bi-plus-lg me-1"></i>دخول مباشر جديد
            </button>
        </div>
    </div>
    @endif

    {{-- No search results --}}
    <div id="noCardResults" class="col-12" style="display:none;">
        <div class="empty-state">
            <i class="bi bi-search"></i>
            <p class="fw-600 mb-0" style="color:#64748b;">لا توجد نتائج مطابقة</p>
        </div>
    </div>
</div>

{{-- Auto-refresh countdown --}}
<div class="d-flex justify-content-end mt-3">
    <span class="badge badge-soft-secondary text-xs" id="refresh-badge">—</span>
</div>

@endif {{-- end selectedLot --}}


{{-- ══════════════════════════════════════════════════════════════════════
     DIRECT ENTRY MODAL
══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="newEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content" style="border:none;border-radius:1rem;overflow:hidden;">

            <div class="modal-header" style="background:#064e3b;color:#fff;border:none;padding:1.125rem 1.5rem;">
                <div>
                    <h5 class="modal-title fw-800 mb-0" style="font-size:1rem;">
                        <i class="bi bi-box-arrow-in-right me-2"></i>تسجيل دخول مباشر
                    </h5>
                    <div class="text-xs mt-1" style="opacity:.7;">أدخل بيانات السيارة لتسجيل الدخول الفوري</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <form id="checkInForm">
                    @csrf
                    <input type="hidden" name="parking_lot_id" value="{{ $selectedLot->id ?? '' }}">

                    <div class="mb-3">
                        <label class="form-label fw-700" style="font-size:.88rem;">رقم اللوحة <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="vehicle_plate" id="newPlateInput" required
                               class="form-control fw-800 text-center"
                               style="font-size:1.2rem;letter-spacing:.1em;border-color:#e2e8f0;"
                               placeholder="أ ب ج 1234"
                               autocomplete="off">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-700" style="font-size:.88rem;">اسم السائق</label>
                            <input type="text" name="customer_name" class="form-control" style="border-color:#e2e8f0;" placeholder="اختياري">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label fw-700" style="font-size:.88rem;">الهاتف</label>
                            <input type="tel" name="phone" class="form-control" style="border-color:#e2e8f0;" placeholder="اختياري" dir="ltr">
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-700" style="font-size:.88rem;">المدة المتوقعة <span style="color:#ef4444;">*</span></label>
                        <div class="row g-2">
                            @foreach([1,2,3,4,6,8,12,24,48,72] as $h)
                            <div class="col-4 col-sm-3">
                                <label class="d-block text-center px-1 py-2 rounded-3 duration-tile"
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

            <div class="modal-footer" style="border:none;padding:1rem 1.5rem 1.5rem;">
                <button type="button" class="btn fw-600"
                        style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
                        data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="checkInBtn"
                        class="btn fw-700 flex-fill"
                        style="background:#10b981;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.65rem;"
                        onclick="submitCheckIn()">
                    <i class="bi bi-box-arrow-in-right me-2"></i>تأكيد الدخول
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════
     RECEIPT & PAYMENT MODAL
══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content" style="border:none;border-radius:1rem;overflow:hidden;">

            <div class="modal-header" style="background:#1e1b4b;color:#fff;border:none;padding:1.125rem 1.5rem;">
                <div>
                    <h5 class="modal-title fw-800 mb-0" style="font-size:1rem;">
                        <i class="bi bi-receipt me-2"></i>فاتورة الخروج
                    </h5>
                    <div id="rcpt-lot" class="text-xs mt-1" style="opacity:.7;"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">

                {{-- Car info --}}
                <div class="d-flex align-items-center gap-3 mb-3 p-3 rounded-3" style="background:#f8fafc;">
                    <div style="width:46px;height:46px;background:#6366f1;border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-car-front" style="color:#fff;font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <div class="fw-800" style="font-size:1.1rem;color:#0f172a;letter-spacing:.04em;font-family:monospace;" id="rcpt-plate">—</div>
                        <div class="text-xs" style="color:#64748b;" id="rcpt-name">—</div>
                    </div>
                    <div class="me-auto text-center">
                        <div class="fw-800" style="color:#6366f1;font-size:1.1rem;" id="rcpt-duration">—</div>
                        <div class="text-xs" style="color:#64748b;">مدة الإقامة</div>
                    </div>
                </div>

                {{-- Times --}}
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-2 rounded-3 text-center" style="background:#f0fdf4;">
                            <div class="text-xs fw-600 mb-1" style="color:#64748b;">وقت الدخول</div>
                            <div class="fw-700 text-sm" id="rcpt-entry" style="color:#059669;">—</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded-3 text-center" style="background:#fef2f2;">
                            <div class="text-xs fw-600 mb-1" style="color:#64748b;">وقت الخروج</div>
                            <div class="fw-700 text-sm" id="rcpt-exit" style="color:#dc2626;">—</div>
                        </div>
                    </div>
                </div>

                {{-- Fee breakdown --}}
                <div class="mb-3">
                    <div class="fw-700 text-sm mb-2" style="color:#0f172a;">تفصيل الأجرة</div>
                    <div id="rcpt-breakdown" style="background:#f8fafc;border-radius:.625rem;padding:.625rem .875rem;">
                        <div class="text-center py-2" style="color:#94a3b8;">
                            <span class="spinner-border spinner-border-sm"></span>
                        </div>
                    </div>
                    <div class="fee-total px-1">
                        <span>الإجمالي</span>
                        <span id="rcpt-total" style="color:#6366f1;">—</span>
                    </div>
                </div>

                {{-- Payment method --}}
                <div class="mb-3">
                    <div class="fw-700 text-sm mb-2" style="color:#0f172a;">طريقة الدفع</div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="pay-method selected" id="pm-cash" onclick="selectPayment('cash')">
                                <i class="bi bi-cash-coin" style="color:#10b981;"></i>
                                <div class="fw-700 text-sm" style="color:#0f172a;">نقداً</div>
                                <div class="text-xs" style="color:#64748b;">دفع مباشر</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pay-method" id="pm-upload" onclick="selectPayment('upload')">
                                <i class="bi bi-cloud-upload" style="color:#6366f1;"></i>
                                <div class="fw-700 text-sm" style="color:#0f172a;">إيصال إلكتروني</div>
                                <div class="text-xs" style="color:#64748b;">رفع صورة الدفع</div>
                            </div>
                        </div>
                    </div>

                    <div id="uploadArea" class="mt-3" style="display:none;">
                        <label class="form-label text-sm">رفع إيصال الدفع <span style="color:#ef4444;">*</span></label>
                        <input type="file" id="paymentProofFile" accept="image/*,.pdf"
                               class="form-control form-control-sm">
                        <div class="text-xs mt-1" style="color:#94a3b8;">JPG / PNG / PDF — حد أقصى 4MB</div>
                    </div>
                </div>

            </div>

            <div class="modal-footer" style="border:none;padding:1rem 1.5rem 1.5rem;">
                <button type="button" class="btn fw-600"
                        style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
                        data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="confirmPayBtn"
                        class="btn fw-700 flex-fill"
                        style="background:#6366f1;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.65rem;">
                    <i class="bi bi-check2-circle me-2"></i>تأكيد الدفع وإغلاق البوابة
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
// ════════════════════════════════════════════════════════════════════════════
// LOT PICKER SCRIPTS
// ════════════════════════════════════════════════════════════════════════════

const map = L.map('opMap').setView([33.5138, 36.2765], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap' }).addTo(map);
const markers = {};

lots.forEach(l => {
    const col  = l.avail === 0 ? '#ef4444' : (l.avail < l.total * .2 ? '#f59e0b' : '#10b981');
    const pct  = l.total > 0 ? Math.round(l.occupied / l.total * 100) : 0;
    const icon = L.divIcon({
        html: `<div style="width:40px;height:40px;background:${col};border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 3px 12px rgba(0,0,0,.3);"></div>`,
        iconSize:[40,40], iconAnchor:[20,40], className:''
    });
    const m = L.marker([l.lat, l.lng], { icon }).addTo(map);
    markers[l.id] = m;

    m.bindPopup(`
        <div style="font-family:'Cairo',sans-serif;min-width:190px;padding:4px 2px;">
            <strong style="font-size:.95rem;color:#0f172a;">${l.name}</strong>
            <p style="margin:4px 0 8px;font-size:.78rem;color:#64748b;">${l.address}</p>
            <div style="height:5px;background:#e2e8f0;border-radius:3px;overflow:hidden;margin-bottom:8px;">
                <div style="height:100%;width:${pct}%;background:${col};border-radius:3px;"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.78rem;color:#64748b;margin-bottom:8px;">
                <span>${l.avail > 0 ? l.avail + ' مكان متاح' : 'ممتلئ'}</span>
                <span>${pct}% مشغول</span>
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

const searchEl  = document.getElementById('lotSearch');
const clearBtn  = document.getElementById('clearSearch');
const noResults = document.getElementById('noResults');
const searchMeta= document.getElementById('searchMeta');

searchEl.addEventListener('input', () => {
    const q = searchEl.value.trim().toLowerCase();
    clearBtn.style.display = q ? 'block' : 'none';
    let vis = 0;
    document.querySelectorAll('.lot-card-wrap').forEach(w => {
        const match = !q || w.dataset.name.includes(q) || w.dataset.address.includes(q);
        w.style.display = match ? '' : 'none';
        if (match) vis++;
    });
    noResults.style.display = vis === 0 ? 'block' : 'none';
    searchMeta.style.display = q ? 'block' : 'none';
    searchMeta.textContent   = `${vis} نتيجة من أصل ${lots.length}`;
});
clearBtn.addEventListener('click', () => {
    searchEl.value = ''; searchEl.dispatchEvent(new Event('input')); searchEl.focus();
});

function mobileTab(tab) {
    const cc = document.getElementById('cardsCol');
    const mc = document.getElementById('mapCol');
    const bc = document.getElementById('mTabCards');
    const bm = document.getElementById('mTabMap');
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
if (window.innerWidth < 992) {
    document.getElementById('mapCol').style.display = 'none';
}

@else
// ════════════════════════════════════════════════════════════════════════════
// OPERATOR PANEL SCRIPTS
// ════════════════════════════════════════════════════════════════════════════

// ── Card search ───────────────────────────────────────────────────────────────
const cardSearch = document.getElementById('cardSearch');
const clearCardSearch = document.getElementById('clearCardSearch');

cardSearch.addEventListener('input', () => {
    const q = cardSearch.value.trim().toLowerCase();
    clearCardSearch.style.display = q ? 'block' : 'none';
    let vis = 0;
    document.querySelectorAll('.booking-card-wrap').forEach(w => {
        const match = !q || w.dataset.plate.includes(q) || w.dataset.name.includes(q);
        w.style.display = match ? '' : 'none';
        if (match) vis++;
    });
    document.getElementById('noCardResults').style.display =
        (vis === 0 && q) ? 'block' : 'none';
});
clearCardSearch.addEventListener('click', () => {
    cardSearch.value = ''; cardSearch.dispatchEvent(new Event('input')); cardSearch.focus();
});

// ── Duration tile highlight ───────────────────────────────────────────────────
function initDurationTiles() {
    document.querySelectorAll('.duration-tile').forEach(label => {
        const radio = label.querySelector('input[type="radio"]');
        if (radio.checked) label.style.cssText = 'border:2px solid #10b981;cursor:pointer;font-size:.82rem;font-weight:700;color:#059669;background:#f0fdf4;transition:all .15s;border-radius:.75rem;';
        radio.addEventListener('change', () => {
            document.querySelectorAll('.duration-tile').forEach(l =>
                l.style.cssText = 'border:2px solid #e2e8f0;cursor:pointer;font-size:.82rem;font-weight:600;color:#475569;transition:all .15s;border-radius:.75rem;'
            );
            label.style.cssText = 'border:2px solid #10b981;cursor:pointer;font-size:.82rem;font-weight:700;color:#059669;background:#f0fdf4;transition:all .15s;border-radius:.75rem;';
        });
    });
}
initDurationTiles();

// ── Check-in form submit ──────────────────────────────────────────────────────
async function submitCheckIn() {
    const form = document.getElementById('checkInForm');
    const btn  = document.getElementById('checkInBtn');
    if (!form.vehicle_plate.value.trim()) {
        form.vehicle_plate.focus(); return;
    }
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري التسجيل...';
    try {
        const res  = await fetch('/operator/check-in', { method:'POST', body: new FormData(form) });
        const data = await res.json();
        if (data.success) {
            btn.style.background = '#059669';
            btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i>تم التسجيل!';
            setTimeout(() => location.reload(), 700);
        } else {
            alert(data.message || 'حدث خطأ');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>تأكيد الدخول';
        }
    } catch {
        alert('خطأ في الاتصال');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>تأكيد الدخول';
    }
}

// Reset modal form when closed
document.getElementById('newEntryModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('checkInForm').reset();
    document.getElementById('checkInBtn').disabled = false;
    document.getElementById('checkInBtn').innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>تأكيد الدخول';
    document.getElementById('checkInBtn').style.background = '#10b981';
    initDurationTiles();
});

// ── Activate reservation ──────────────────────────────────────────────────────
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
            // swap check-in button to success state, enable checkout
            btn.style.background = '#059669';
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>تم الدخول';
            const checkoutBtn = document.getElementById('checkout-res-' + id);
            if (checkoutBtn) {
                checkoutBtn.disabled = false;
                checkoutBtn.style.opacity = '1';
                checkoutBtn.style.cursor  = 'pointer';
                checkoutBtn.onclick = () => openReceipt(id);
            }
        } else {
            alert(data.message || 'حدث خطأ');
            btn.innerHTML = orig;
            btn.disabled  = false;
        }
    } catch {
        alert('خطأ في الاتصال');
        btn.innerHTML = orig;
        btn.disabled  = false;
    }
}

// ── Receipt modal ─────────────────────────────────────────────────────────────
let receiptModal    = null;
let currentBookingId = null;
let selectedPayment  = 'cash';

function getModal() {
    if (!receiptModal) receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    return receiptModal;
}

async function openReceipt(id) {
    currentBookingId = id;
    selectedPayment  = 'cash';
    selectPayment('cash');

    document.getElementById('rcpt-breakdown').innerHTML =
        '<div class="text-center py-2" style="color:#94a3b8;"><span class="spinner-border spinner-border-sm"></span></div>';
    ['rcpt-plate','rcpt-name','rcpt-duration','rcpt-entry','rcpt-exit','rcpt-total'].forEach(i =>
        document.getElementById(i).textContent = '—'
    );
    document.getElementById('rcpt-lot').textContent = '';

    getModal().show();

    try {
        const res  = await fetch(`/operator/${id}/checkout-preview`);
        const data = await res.json();
        if (!data.success) { alert(data.message); return; }
        const d = data.data;

        document.getElementById('rcpt-lot').textContent      = d.lot_name;
        document.getElementById('rcpt-plate').textContent    = d.plate         || '—';
        document.getElementById('rcpt-name').textContent     = d.customer_name || 'غير محدد';
        document.getElementById('rcpt-duration').textContent = d.duration_label;
        document.getElementById('rcpt-entry').textContent    = d.entry_time;
        document.getElementById('rcpt-exit').textContent     = d.exit_time;
        document.getElementById('rcpt-total').textContent    = Number(d.total_fee).toLocaleString('ar-SA') + ' ل.س';

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
    document.getElementById('pm-cash').classList.toggle('selected',   method === 'cash');
    document.getElementById('pm-upload').classList.toggle('selected', method === 'upload');
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
            btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i>تأكيد الدفع وإغلاق البوابة';
        }
    } catch {
        alert('خطأ في الاتصال');
        btn.disabled  = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i>تأكيد الدفع وإغلاق البوابة';
    }
});

// Reset receipt modal on close
document.getElementById('receiptModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('confirmPayBtn').disabled  = false;
    document.getElementById('confirmPayBtn').innerHTML = '<i class="bi bi-check2-circle me-2"></i>تأكيد الدفع وإغلاق البوابة';
    document.getElementById('paymentProofFile').value  = '';
});

// ── Auto-refresh countdown ────────────────────────────────────────────────────
let t = 30;
const badge = document.getElementById('refresh-badge');
setInterval(() => {
    t--;
    if (badge) badge.textContent = `تحديث تلقائي بعد ${t}ث`;
    if (t <= 0) location.reload();
}, 1000);

@endif
</script>
@endpush
@endsection
