@extends('layouts.admin')
@section('title', 'إدارة المواقف — دمشق باركينغ')
@section('page-title', 'إدارة المواقف')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #modalMap  { height: 280px; border-radius: .5rem; z-index: 0; }
    .map-hint  { font-size: .78rem; color: #64748b; margin-top: .35rem; }
    .leaflet-container { direction: ltr; }

    /* lot portrait cards */
    .lot-admin-card {
        background: #fff;
        border-radius: .75rem;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: box-shadow .2s, transform .2s;
    }
    .lot-admin-card:hover {
        box-shadow: 0 8px 24px rgba(99,102,241,.12);
        transform: translateY(-2px);
    }
    .lot-card-img-wrap {
        position: relative;
        height: 160px;
        flex-shrink: 0;
        overflow: hidden;
    }
    .lot-card-img-wrap img {
        width: 100%; height: 100%; object-fit: cover;
    }
    .lot-card-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
    }
    .lot-card-placeholder i { font-size: 2.5rem; color: rgba(255,255,255,.6); }
    .lot-status-dot {
        position: absolute; top: .6rem; left: .6rem;
        width: 10px; height: 10px; border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,.3);
    }
    .lot-active-badge {
        position: absolute; top: .55rem; right: .55rem;
        background: rgba(0,0,0,.45); color: #fff;
        font-size: .68rem; font-weight: 700;
        padding: .2rem .5rem; border-radius: 2rem;
        backdrop-filter: blur(4px);
    }
    .lot-card-body {
        padding: .9rem 1rem .5rem;
        flex: 1;
    }
    .lot-card-body h6 {
        font-size: .92rem; font-weight: 700; color: #0f172a;
        margin-bottom: .2rem;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .lot-card-address {
        font-size: .75rem; color: #64748b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        margin-bottom: .65rem;
    }
    .lot-card-stats {
        display: flex; gap: .4rem; flex-wrap: wrap; margin-bottom: .5rem;
    }
    .lot-card-stat {
        background: #f8fafc; border: 1px solid #e2e8f0;
        border-radius: .375rem; padding: .2rem .5rem;
        font-size: .72rem; color: #475569; white-space: nowrap;
    }
    .lot-card-stat strong { color: #0f172a; }
    .lot-card-footer {
        padding: .6rem 1rem;
        border-top: 1px solid #f1f5f9;
        display: flex; justify-content: flex-end; gap: .35rem;
    }
    .lot-action-btn {
        width: 30px; height: 30px; border: none; border-radius: .375rem;
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem; cursor: pointer; transition: opacity .15s;
    }
    .lot-action-btn:hover { opacity: .8; }

    /* modal scrollable */
    #lotModal .modal-content          { max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; }
    #lotModal .modal-content > form   { display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; overflow: hidden; }
    #lotModal .modal-body             { overflow-y: auto; flex: 1 1 auto; min-height: 0; }
    #lotModal .modal-footer           { flex-shrink: 0; }
</style>
@endsection

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-800 mb-1" style="font-size:1.15rem;color:#0f172a;">مواقف السيارات</h2>
        <p class="text-sm mb-0" style="color:#64748b;">
            {{ $parkingLots->total() }} موقف مسجّل في النظام
        </p>
    </div>
    <button class="btn fw-600"
            style="background:#6366f1;color:#fff;border:none;border-radius:.5rem;padding:.55rem 1.25rem;font-family:'Cairo',sans-serif;"
            data-bs-toggle="modal" data-bs-target="#lotModal" id="addLotBtn">
        <i class="bi bi-plus-lg me-1"></i>
        إضافة موقف
    </button>
</div>

{{-- ── Search bar ───────────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
    <span class="text-sm fw-600" style="color:#64748b;">
        @if(request('search'))
            نتائج البحث عن «{{ request('search') }}»
        @else
            عرض جميع المواقف
        @endif
    </span>
    <div class="input-group" style="max-width:280px;">
        <input type="text" id="searchInput"
               class="form-control form-control-sm"
               style="border-color:#e2e8f0;border-inline-end:none;"
               placeholder="ابحث باسم الموقف أو العنوان..." value="{{ request('search') }}">
        <button class="btn btn-sm" style="background:#6366f1;color:#fff;border:none;" onclick="doSearch()">
            <i class="bi bi-search"></i>
        </button>
        @if(request('search'))
        <a href="{{ route('admin.parking-lots.index') }}"
           class="btn btn-sm" style="background:#f1f5f9;color:#64748b;border:none;">
            <i class="bi bi-x-lg"></i>
        </a>
        @endif
    </div>
</div>

{{-- ── Cards grid ───────────────────────────────────────────────────────────── --}}
@php
$gradients = [
    'linear-gradient(135deg,#667eea,#764ba2)',
    'linear-gradient(135deg,#f093fb,#f5576c)',
    'linear-gradient(135deg,#4facfe,#00f2fe)',
    'linear-gradient(135deg,#43e97b,#38f9d7)',
    'linear-gradient(135deg,#fa709a,#fee140)',
    'linear-gradient(135deg,#a18cd1,#fbc2eb)',
];
@endphp

@if($parkingLots->isEmpty())
<div class="text-center py-5">
    <i class="bi bi-buildings d-block mb-3" style="font-size:3rem;color:#cbd5e1;"></i>
    <p class="fw-600 mb-1" style="color:#475569;">لا توجد مواقف بعد</p>
    <p class="text-sm mb-3" style="color:#94a3b8;">ابدأ بإضافة أول موقف سيارات</p>
    <button class="btn fw-600"
            style="background:#6366f1;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
            data-bs-toggle="modal" data-bs-target="#lotModal" id="addLotBtnEmpty">
        <i class="bi bi-plus-lg me-1"></i>إضافة موقف
    </button>
</div>
@else
<div class="row g-3">
    @foreach($parkingLots as $lot)
    @php $grad = $gradients[$lot->id % count($gradients)]; @endphp
    <div class="col-sm-6 col-md-4 col-xl-3">
        <div class="lot-admin-card">

            {{-- Image / Placeholder --}}
            <div class="lot-card-img-wrap">
                @if($lot->image)
                    <img src="{{ Storage::url($lot->image) }}" alt="{{ $lot->name }}">
                @else
                    <div class="lot-card-placeholder" style="background: {{ $grad }};">
                        <i class="bi bi-buildings"></i>
                    </div>
                @endif

                {{-- Status dot --}}
                <span class="lot-status-dot"
                      style="background:{{ $lot->is_active ? '#10b981' : '#ef4444' }};"
                      title="{{ $lot->is_active ? 'نشط' : 'معطل' }}">
                </span>

                {{-- Active bookings badge --}}
                @if(($lot->active_bookings_count ?? 0) > 0)
                <span class="lot-active-badge">
                    <i class="bi bi-car-front-fill me-1"></i>{{ $lot->active_bookings_count }} نشط
                </span>
                @endif
            </div>

            {{-- Card body --}}
            <div class="lot-card-body">
                <h6 title="{{ $lot->name }}">{{ $lot->name }}</h6>
                <p class="lot-card-address" title="{{ $lot->address }}">
                    <i class="bi bi-geo-alt me-1" style="color:#94a3b8;"></i>{{ $lot->address }}
                </p>

                <div class="lot-card-stats">
                    <span class="lot-card-stat">
                        <i class="bi bi-car-front" style="color:#6366f1;"></i>
                        <strong>{{ $lot->total_capacity }}</strong> مركبة
                    </span>
                    <span class="lot-card-stat">
                        <i class="bi bi-clock" style="color:#10b981;"></i>
                        {{ $lot->working_hours }}
                    </span>
                    <span class="lot-card-stat" style="{{ !empty($lot->pricing_rules) ? 'border-color:#fbbf24;color:#92400e;' : '' }}">
                        <i class="bi bi-{{ !empty($lot->pricing_rules) ? 'tags' : 'tag' }}" style="color:{{ !empty($lot->pricing_rules) ? '#f59e0b' : '#10b981' }};"></i>
                        <strong>{{ number_format($lot->price_per_hour, 0) }}</strong> ر.س/س
                        @if(!empty($lot->pricing_rules))
                        <span style="font-size:.6rem;color:#f59e0b;"> (مخصص)</span>
                        @endif
                    </span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="lot-card-footer">
                <button class="lot-action-btn"
                        style="background:#f1f5f9;color:#475569;"
                        data-bs-toggle="modal" data-bs-target="#lotModal"
                        onclick="editLot({{ $lot->id }})" title="تعديل">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="lot-action-btn"
                        style="background:rgba(99,102,241,.1);color:#6366f1;"
                        onclick="openPricingModal({{ $lot->id }}, '{{ addslashes($lot->name) }}')"
                        title="التسعير الأسبوعي">
                    <i class="bi bi-tags"></i>
                </button>
                <button class="lot-action-btn"
                        style="background:{{ $lot->is_active ? 'rgba(239,68,68,.1)' : 'rgba(16,185,129,.1)' }};color:{{ $lot->is_active ? '#dc2626' : '#059669' }};"
                        onclick="toggleStatus({{ $lot->id }})"
                        title="{{ $lot->is_active ? 'تعطيل' : 'تفعيل' }}">
                    <i class="bi {{ $lot->is_active ? 'bi-slash-circle' : 'bi-check-circle' }}"></i>
                </button>
                <button class="lot-action-btn"
                        style="background:rgba(239,68,68,.08);color:#dc2626;"
                        onclick="deleteLot({{ $lot->id }}, '{{ addslashes($lot->name) }}')"
                        title="حذف">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>

        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
@if($parkingLots->hasPages())
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-4">
    <span class="text-xs" style="color:#64748b;">
        عرض {{ $parkingLots->firstItem() }}–{{ $parkingLots->lastItem() }}
        من {{ $parkingLots->total() }}
    </span>
    {{ $parkingLots->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>
@endif
@endif

{{-- ── Add / Edit Modal ────────────────────────────────────────────────────── --}}
<div class="modal fade" id="lotModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <form id="lotForm">
                @csrf
                <input type="hidden" id="lotId">

                <div class="modal-body p-4">
                    <p id="modalLabel" class="fw-700 mb-3" style="font-size:1rem;color:#0f172a;">إضافة موقف جديد</p>
                    <div class="row g-3">

                        {{-- Basic Info --}}
                        <div class="col-12">
                            <p class="text-xs fw-700 text-uppercase mb-2" style="color:#94a3b8;letter-spacing:.05em;">
                                المعلومات الأساسية
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">اسم الموقف <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="name" id="f_name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">السعة <span style="color:#ef4444;">*</span></label>
                            <input type="number" name="total_capacity" id="f_capacity" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">السعر / ساعة <span style="color:#ef4444;">*</span></label>
                            <input type="number" name="price_per_hour" id="f_price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">ساعات العمل <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="working_hours" id="f_hours" class="form-control" value="24/7" required>
                        </div>

                        {{-- Image --}}
                        <div class="col-12">
                            <label class="form-label">صورة الموقف</label>
                            <input type="file" name="image" id="f_image" class="form-control"
                                   accept="image/jpg,image/jpeg,image/png,image/webp"
                                   onchange="previewImage(this)">
                            <div class="text-xs mt-1" style="color:#94a3b8;">JPG / PNG / WebP — حد أقصى 3MB. اتركه فارغاً للإبقاء على الصورة الحالية عند التعديل.</div>
                            <div id="imagePreviewWrap" class="mt-2" style="display:none;">
                                <img id="imagePreview" src="" alt=""
                                     style="height:100px;border-radius:.5rem;object-fit:cover;border:2px solid #e2e8f0;">
                            </div>
                        </div>

                        {{-- Location --}}
                        <div class="col-12 mt-2">
                            <p class="text-xs fw-700 text-uppercase mb-2" style="color:#94a3b8;letter-spacing:.05em;">
                                الموقع الجغرافي
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">خط العرض <span style="color:#ef4444;">*</span></label>
                            <input type="number" name="latitude" id="f_lat" class="form-control" step="any" required dir="ltr">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">خط الطول <span style="color:#ef4444;">*</span></label>
                            <input type="number" name="longitude" id="f_lng" class="form-control" step="any" required dir="ltr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">العنوان الكامل <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="address" id="f_address" class="form-control" required>
                        </div>

                        {{-- Map Picker --}}
                        <div class="col-12">
                            <label class="form-label mb-1">
                                <i class="bi bi-cursor-fill me-1" style="color:#6366f1;"></i>
                                انقر على الخريطة لتحديد الموقع
                            </label>
                            <div id="modalMap"></div>
                            <p class="map-hint">انقر على أي نقطة في الخريطة لتعبئة إحداثيات خط العرض والطول تلقائياً.</p>
                        </div>

                    </div>
                </div>

                <div class="modal-footer" style="border-top:1px solid #f1f5f9;">
                    <button type="button" class="btn btn-sm fw-600"
                            style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
                            data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" id="submitBtn" onclick="saveLot()"
                            class="btn btn-sm fw-600"
                            style="background:#6366f1;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
                        <span id="submitSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                        <span id="submitText">حفظ الموقف</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Pricing Modal ──────────────────────────────────────────────────────── --}}
<div class="modal fade" id="pricingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content">

            <div class="modal-header" style="border-bottom:1px solid #f1f5f9;">
                <h5 class="modal-title fw-700" id="pricingModalLabel" style="font-size:1rem;color:#0f172a;">
                    <i class="bi bi-tags me-1" style="color:#6366f1;"></i>
                    التسعير الأسبوعي
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <input type="hidden" id="pricingLotId">

                {{-- Base rate --}}
                <div class="mb-4">
                    <label class="form-label fw-600">
                        السعر الأساسي (لجميع الأيام غير المخصصة)
                        <span style="color:#ef4444;">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" id="p_base" class="form-control"
                               step="0.01" min="0" placeholder="0.00"
                               oninput="syncBaseHints()">
                        <span class="input-group-text" style="font-family:'Cairo',sans-serif;background:#f8fafc;color:#64748b;border-color:#e2e8f0;">ر.س / ساعة</span>
                    </div>
                    <p class="text-xs mt-1 mb-0" style="color:#94a3b8;">
                        الأيام التي لا تحمل سعراً مخصصاً ستستخدم هذا السعر تلقائياً.
                    </p>
                </div>

                {{-- Per-day rules --}}
                <p class="text-xs fw-700 text-uppercase mb-3" style="color:#94a3b8;letter-spacing:.05em;">
                    أسعار مخصصة حسب اليوم <span class="fw-400 text-lowercase" style="color:#b0bec5;">(اختياري)</span>
                </p>

                <div id="dayRulesGrid">
                    @php
                    $days = [
                        1 => ['الاثنين',   'Monday',    '#6366f1'],
                        2 => ['الثلاثاء',  'Tuesday',   '#6366f1'],
                        3 => ['الأربعاء',  'Wednesday', '#6366f1'],
                        4 => ['الخميس',    'Thursday',  '#6366f1'],
                        5 => ['الجمعة',    'Friday',    '#f59e0b'],
                        6 => ['السبت',     'Saturday',  '#10b981'],
                        7 => ['الأحد',     'Sunday',    '#10b981'],
                    ];
                    @endphp
                    @foreach($days as $dow => [$ar, $en, $color])
                    <div class="d-flex align-items-center gap-3 py-2"
                         style="border-bottom:1px solid #f1f5f9;">
                        <div style="width:90px;flex-shrink:0;">
                            <span class="fw-600 text-sm" style="color:#0f172a;">{{ $ar }}</span>
                            <div class="text-xs" style="color:#94a3b8;">{{ $en }}</div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="input-group input-group-sm">
                                <input type="number"
                                       id="p_day_{{ $dow }}"
                                       class="form-control day-rate-input"
                                       data-dow="{{ $dow }}"
                                       step="0.01" min="0"
                                       placeholder="مثل السعر الأساسي"
                                       style="font-family:'Cairo',sans-serif;">
                                <span class="input-group-text" style="background:#f8fafc;color:#94a3b8;border-color:#e2e8f0;font-size:.75rem;">ر.س</span>
                            </div>
                        </div>
                        <div style="width:70px;text-align:center;">
                            <span id="p_badge_{{ $dow }}"
                                  class="badge d-none"
                                  style="font-size:.65rem;background:rgba(99,102,241,.1);color:#6366f1;border-radius:.3rem;">
                                مخصص
                            </span>
                        </div>
                        <button type="button"
                                class="btn btn-sm p-0"
                                style="color:#94a3b8;border:none;background:none;font-size:.75rem;"
                                onclick="clearDayRate({{ $dow }})"
                                title="استخدام السعر الأساسي">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    @endforeach
                </div>

                {{-- Weekly preview --}}
                <div class="mt-4 p-3" style="background:#f8fafc;border-radius:.5rem;border:1px solid #e2e8f0;">
                    <p class="text-xs fw-700 mb-2" style="color:#64748b;">معاينة التسعير الأسبوعي</p>
                    <div id="pricingPreview" class="d-flex gap-1 flex-wrap">
                        @foreach($days as $dow => [$ar, $en, $color])
                        <div id="prev_{{ $dow }}"
                             class="text-center"
                             style="flex:1;min-width:52px;background:#fff;border:1px solid #e2e8f0;border-radius:.375rem;padding:.35rem .25rem;">
                            <div class="text-xs fw-600" style="color:#475569;">{{ $ar }}</div>
                            <div id="prev_val_{{ $dow }}"
                                 class="fw-700 mt-1"
                                 style="font-size:.78rem;color:#6366f1;">—</div>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-xs mt-2 mb-0" style="color:#94a3b8;">
                        السعر المعروض هو السعر الفعلي الذي سيُطبّق على كل يوم.
                        الحجوزات القائمة حالياً <strong>لن تتأثر</strong> بأي تغيير.
                    </p>
                </div>
            </div>

            <div class="modal-footer" style="border-top:1px solid #f1f5f9;">
                <button type="button" class="btn btn-sm fw-600"
                        style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
                        data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="savePricingBtn"
                        class="btn btn-sm fw-600"
                        style="background:#6366f1;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
                        onclick="savePricing()">
                    <span id="pricingSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                    <i class="bi bi-check2 me-1"></i>
                    حفظ الأسعار
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
window.onerror = (msg, src, line) => { alert('JS خطأ في السطر ' + line + ':\n' + msg); };
// ── Parking lots data from server ─────────────────────────────────────────
const lotsData = @json($parkingLots->items());

// Damascus city centre fallback
const DAMASCUS = [33.5138, 36.2765];

// ── Modal map picker ──────────────────────────────────────────────────────
let modalMap    = null;
let modalMarker = null;

function initModalMap(lat, lng) {
    const center = (lat && lng) ? [lat, lng] : DAMASCUS;
    const zoom   = (lat && lng) ? 15 : 13;

    if (!modalMap) {
        modalMap = L.map('modalMap').setView(center, zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(modalMap);

        modalMap.on('click', e => {
            const { lat, lng } = e.latlng;
            document.getElementById('f_lat').value = lat.toFixed(7);
            document.getElementById('f_lng').value = lng.toFixed(7);
            placeModalMarker(lat, lng);
        });
    } else {
        modalMap.setView(center, zoom);
    }

    if (lat && lng) {
        placeModalMarker(lat, lng);
    } else if (modalMarker) {
        modalMap.removeLayer(modalMarker);
        modalMarker = null;
    }

    // Must invalidate after modal finishes animating
    setTimeout(() => modalMap.invalidateSize(), 350);
}

function placeModalMarker(lat, lng) {
    if (modalMarker) {
        modalMarker.setLatLng([lat, lng]);
    } else {
        modalMarker = L.marker([lat, lng], { draggable: true }).addTo(modalMap);
        modalMarker.on('dragend', e => {
            const pos = e.target.getLatLng();
            document.getElementById('f_lat').value = pos.lat.toFixed(7);
            document.getElementById('f_lng').value = pos.lng.toFixed(7);
        });
    }
}

// Sync map marker when user types into lat/lng fields
['f_lat', 'f_lng'].forEach(elId => {
    const el = document.getElementById(elId);
    if (!el) return;
    el.addEventListener('input', () => {
        const lat = parseFloat(document.getElementById('f_lat').value);
        const lng = parseFloat(document.getElementById('f_lng').value);
        if (!isNaN(lat) && !isNaN(lng) && modalMap) {
            modalMap.setView([lat, lng], 15);
            placeModalMarker(lat, lng);
        }
    });
});

// ── Bootstrap modal events ────────────────────────────────────────────────
const lotModalEl = document.getElementById('lotModal');
lotModalEl.addEventListener('shown.bs.modal', () => {
    const lat = parseFloat(document.getElementById('f_lat').value) || null;
    const lng = parseFloat(document.getElementById('f_lng').value) || null;
    initModalMap(lat, lng);
});

// ── Modal logic ───────────────────────────────────────────────────────────
let editingId = null;

document.getElementById('addLotBtn').onclick = () => {
    editingId = null;
    document.getElementById('lotForm').reset();
    document.getElementById('modalLabel').textContent = 'إضافة موقف جديد';
    document.getElementById('submitText').textContent  = 'حفظ الموقف';
    document.getElementById('imagePreviewWrap').style.display = 'none';
    // modal opened by data-bs-toggle on the button
};

function previewImage(input) {
    const wrap = document.getElementById('imagePreviewWrap');
    const img  = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        wrap.style.display = 'block';
    } else {
        wrap.style.display = 'none';
    }
}

function editLot(id) {
    const lot = lotsData.find(l => l.id === id);
    if (!lot) { alert('لم يتم العثور على بيانات الموقف'); return; }

    editingId = id;
    document.getElementById('modalLabel').textContent  = 'تعديل: ' + lot.name;
    document.getElementById('submitText').textContent  = 'تحديث الموقف';
    document.getElementById('f_name').value     = lot.name;
    document.getElementById('f_capacity').value = lot.total_capacity;
    document.getElementById('f_price').value    = lot.price_per_hour;
    document.getElementById('f_hours').value    = lot.working_hours;
    document.getElementById('f_lat').value      = lot.latitude;
    document.getElementById('f_lng').value      = lot.longitude;
    document.getElementById('f_address').value  = lot.address;
    document.getElementById('f_image').value    = '';

    const wrap = document.getElementById('imagePreviewWrap');
    const img  = document.getElementById('imagePreview');
    if (lot.image) {
        img.src = '/storage/' + lot.image;
        wrap.style.display = 'block';
    } else {
        wrap.style.display = 'none';
    }
    // modal opened by data-bs-toggle on the button
}

async function saveLot() {
    setBtnLoading(true);
    try {
        const form = document.getElementById('lotForm');
        const fd   = new FormData(form);
        if (editingId) fd.append('_method', 'PUT');
        const url = editingId ? `/admin/parking-lots/${editingId}` : '/admin/parking-lots';
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: fd
        });
        const result = await res.json();
        if (result.success) {
            location.reload();
        } else if (result.errors) {
            const msgs = Object.values(result.errors).flat().join('\n');
            alert(msgs);
        } else {
            alert(result.message || 'خطأ في العملية');
        }
    } catch(err) { alert('خطأ في الاتصال: ' + err.message); }
    finally { setBtnLoading(false); }
}

function setBtnLoading(on) {
    document.getElementById('submitBtn').disabled = on;
    document.getElementById('submitSpinner').classList.toggle('d-none', !on);
}

function toggleStatus(id) {
    if (!confirm('تغيير حالة الموقف؟')) return;
    fetch(`/admin/parking-lots/${id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    }).then(r => r.json()).then(d => d.success ? location.reload() : alert(d.message));
}

document.getElementById('searchInput').addEventListener('keypress', e => {
    if (e.key === 'Enter') doSearch();
});
function doSearch() {
    const t = document.getElementById('searchInput').value;
    window.location.href = `/admin/parking-lots?search=${encodeURIComponent(t)}`;
}

// ── Pricing Modal ─────────────────────────────────────────────────────────

function openPricingModal(id, name) {
    try {
        const lot   = lotsData.find(l => l.id === id);
        const base  = lot ? (parseFloat(lot.price_per_hour) || 0) : 0;
        const rules = (lot && lot.pricing_rules) ? lot.pricing_rules : {};

        document.getElementById('pricingLotId').value = id;
        document.getElementById('pricingModalLabel').innerHTML =
            `<i class="bi bi-tags me-1" style="color:#6366f1;"></i>التسعير الأسبوعي — ${name}`;

        document.getElementById('p_base').value = base;

        [1,2,3,4,5,6,7].forEach(d => {
            const custom = rules[d] !== undefined ? parseFloat(rules[d]) : null;
            document.getElementById(`p_day_${d}`).value = (custom !== null) ? custom : '';
            document.getElementById(`p_badge_${d}`).classList.add('d-none');
        });

        updatePricingPreview();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('pricingModal')).show();
    } catch(e) {
        alert('خطأ في فتح نافذة التسعير:\n' + e.message);
    }
}

function syncBaseHints() {
    updatePricingPreview();
}

function clearDayRate(dow) {
    document.getElementById(`p_day_${dow}`).value = '';
    updatePricingPreview();
}

function updatePricingPreview() {
    const base = parseFloat(document.getElementById('p_base').value) || 0;
    [1,2,3,4,5,6,7].forEach(d => {
        const raw    = document.getElementById(`p_day_${d}`).value;
        const custom = raw !== '' ? parseFloat(raw) : null;
        const rate   = custom !== null ? custom : base;
        const isCustom = custom !== null && custom !== base;

        document.getElementById(`p_badge_${d}`).classList.toggle('d-none', !isCustom);
        const valEl = document.getElementById(`prev_val_${d}`);
        valEl.textContent  = rate > 0 ? rate.toLocaleString('ar-SA') : '—';
        valEl.style.color  = isCustom ? '#f59e0b' : '#6366f1';
        document.getElementById(`prev_${d}`).style.borderColor = isCustom ? '#fbbf24' : '#e2e8f0';
    });
}

// Update preview when any day input changes
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.day-rate-input').forEach(inp => {
        inp.addEventListener('input', updatePricingPreview);
    });
});

async function savePricing() {
    const id   = document.getElementById('pricingLotId').value;
    const base = parseFloat(document.getElementById('p_base').value);

    if (isNaN(base) || base < 0) {
        alert('يرجى إدخال سعر أساسي صحيح');
        return;
    }

    const rules = {};
    [1,2,3,4,5,6,7].forEach(d => {
        const val = document.getElementById(`p_day_${d}`).value;
        if (val !== '') rules[d] = parseFloat(val);
    });

    document.getElementById('savePricingBtn').disabled = true;
    document.getElementById('pricingSpinner').classList.remove('d-none');

    try {
        const res  = await fetch(`/admin/parking-lots/${id}/pricing`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ price_per_hour: base, pricing_rules: rules }),
        });
        const data = await res.json();

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('pricingModal'))?.hide();
            location.reload();
        } else {
            alert(data.message || 'خطأ في الحفظ');
        }
    } catch {
        alert('خطأ في الاتصال');
    } finally {
        document.getElementById('savePricingBtn').disabled = false;
        document.getElementById('pricingSpinner').classList.add('d-none');
    }
}

async function deleteLot(id, name) {
    if (!confirm(`حذف الموقف "${name}"؟\nسيتم حذف جميع بيانات الموقف نهائياً.`)) return;

    try {
        const res  = await fetch(`/admin/parking-lots/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'تعذّر الحذف');
        }
    } catch {
        alert('خطأ في الاتصال');
    }
}
</script>
@endpush

@endsection
