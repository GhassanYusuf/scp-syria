@extends('layouts.admin')
@section('title', 'الحجوزات النشطة — دمشق باركينغ')
@section('page-title', 'الحجوزات النشطة')

@section('styles')
<style>
    /* ── Stat cards ──────────────────────────────────────────────────── */
    .stat-card {
        background:#fff;
        border-radius:.875rem;
        padding:1.125rem 1.25rem;
        box-shadow:0 2px 8px rgba(0,0,0,.05);
        display:flex;
        align-items:center;
        gap:1rem;
        border:1px solid #f1f5f9;
    }
    .stat-icon {
        width:48px; height:48px;
        border-radius:.75rem;
        display:flex; align-items:center; justify-content:center;
        font-size:1.3rem;
        flex-shrink:0;
    }
    .stat-value { font-size:1.6rem; font-weight:800; line-height:1; color:#0f172a; }
    .stat-label { font-size:.75rem; color:#64748b; margin-top:.2rem; }

    /* ── Filter bar ──────────────────────────────────────────────────── */
    .filter-bar {
        background:#fff;
        border-radius:.875rem;
        padding:1rem 1.25rem;
        box-shadow:0 2px 8px rgba(0,0,0,.05);
        border:1px solid #f1f5f9;
    }

    /* ── Table enhancements ──────────────────────────────────────────── */
    .booking-row { transition:background .15s; position:relative; }
    .booking-row:hover { background:#f8fafc !important; }
    .booking-row.is-overdue { background:rgba(239,68,68,.03); }
    .booking-row.is-overdue td:first-child { border-inline-start:3px solid #ef4444; }
    .booking-row.is-warning td:first-child { border-inline-start:3px solid #f59e0b; }
    .booking-row.is-ok td:first-child     { border-inline-start:3px solid #10b981; }

    .plate-tag {
        font-family:monospace;
        font-size:1rem;
        font-weight:800;
        color:#0f172a;
        letter-spacing:.04em;
        background:#f8fafc;
        border:1.5px solid #e2e8f0;
        border-radius:.375rem;
        padding:.2rem .6rem;
        display:inline-block;
    }

    /* Duration bar */
    .dur-bar-wrap { width:80px; height:5px; background:#e2e8f0; border-radius:3px; overflow:hidden; margin-top:.3rem; }
    .dur-bar-fill { height:100%; border-radius:3px; transition:width .3s; }

    /* Source badge */
    .src-badge {
        display:inline-flex; align-items:center; gap:.25rem;
        font-size:.65rem; font-weight:700;
        padding:.15em .55em; border-radius:20px;
        margin-top:.3rem;
    }

    /* Time remaining pill */
    .time-pill {
        display:inline-flex; align-items:center; gap:.3rem;
        padding:.28em .7em; border-radius:20px;
        font-size:.75rem; font-weight:700;
    }
</style>
@endsection

@section('content')

{{-- ── Page header ─────────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-800 mb-1" style="font-size:1.15rem;color:#0f172a;">الحجوزات النشطة</h2>
        <p class="text-sm mb-0" style="color:#64748b;">
            السيارات داخل المواقف حالياً
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge badge-soft-secondary text-xs" id="refresh-badge">تحديث بعد 30ث</span>
        <button onclick="location.reload()"
                class="btn btn-sm fw-600"
                style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
            <i class="bi bi-arrow-clockwise me-1"></i>تحديث الآن
        </button>
    </div>
</div>

{{-- ── Stat cards ───────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(99,102,241,.1);">
                <i class="bi bi-car-front" style="color:#6366f1;"></i>
            </div>
            <div>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">إجمالي النشطة</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,.1);">
                <i class="bi bi-box-arrow-in-right" style="color:#10b981;"></i>
            </div>
            <div>
                <div class="stat-value">{{ $stats['walkin'] }}</div>
                <div class="stat-label">دخول مباشر</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(14,165,233,.1);">
                <i class="bi bi-calendar-check" style="color:#0ea5e9;"></i>
            </div>
            <div>
                <div class="stat-value">{{ $stats['reservation'] }}</div>
                <div class="stat-label">حجوزات مسبقة</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="{{ $stats['overdue'] > 0 ? 'border-color:rgba(239,68,68,.25);background:rgba(239,68,68,.03);' : '' }}">
            <div class="stat-icon" style="background:rgba(239,68,68,.1);">
                <i class="bi bi-exclamation-triangle" style="color:#ef4444;"></i>
            </div>
            <div>
                <div class="stat-value" style="{{ $stats['overdue'] > 0 ? 'color:#ef4444;' : '' }}">
                    {{ $stats['overdue'] }}
                </div>
                <div class="stat-label">متأخرة عن الخروج</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Filter bar ───────────────────────────────────────────────────────────── --}}
<div class="filter-bar mb-4">
    <div class="row align-items-end g-3">

        <div class="col-md-4">
            <label class="form-label text-sm fw-600" style="color:#475569;">الموقف</label>
            <select id="lotFilter" class="form-select form-select-sm" style="border-color:#e2e8f0;">
                <option value="">جميع المواقف</option>
                @foreach($parkingLots as $lot)
                <option value="{{ $lot->id }}" {{ request('parking_lot_id') == $lot->id ? 'selected' : '' }}>
                    {{ $lot->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label text-sm fw-600" style="color:#475569;">بحث</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text" style="background:#fff;border-color:#e2e8f0;">
                    <i class="bi bi-search" style="color:#94a3b8;"></i>
                </span>
                <input type="text" id="searchInput"
                       class="form-control" style="border-color:#e2e8f0;"
                       placeholder="رقم اللوحة أو السائق..."
                       value="{{ $search ?? '' }}">
            </div>
        </div>

        <div class="col-md-auto d-flex gap-2">
            <button onclick="applyFilters()"
                    class="btn btn-sm fw-600"
                    style="background:#6366f1;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.45rem 1rem;">
                <i class="bi bi-funnel me-1"></i>تطبيق
            </button>
            @if(request('parking_lot_id') || request('search'))
            <a href="{{ route('admin.bookings.active') }}"
               class="btn btn-sm fw-600"
               style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.45rem 1rem;">
                <i class="bi bi-x me-1"></i>مسح
            </a>
            @endif
        </div>

    </div>
</div>

{{-- ── Bookings table ───────────────────────────────────────────────────────── --}}
<div class="card">

    <div class="card-header d-flex align-items-center justify-content-between py-2">
        <span class="fw-700 text-sm" style="color:#0f172a;">
            <i class="bi bi-list-ul me-1" style="color:#6366f1;"></i>
            قائمة الحجوزات
        </span>
        <span class="badge badge-soft-primary text-xs">
            {{ $activeBookings->total() }} سجل
        </span>
    </div>

    <div class="table-responsive">
        <table class="app-table w-100">
            <thead>
                <tr>
                    <th>السيارة</th>
                    <th>السائق</th>
                    <th>الموقف</th>
                    <th>وقت الدخول</th>
                    <th>المدة / الوضع</th>
                    <th class="text-center">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeBookings as $booking)
                @php
                    $elapsedMin  = $booking->start_time->diffInMinutes(now());
                    $remainMins  = now()->diffInMinutes($booking->end_time, false);
                    $isOverdue   = $remainMins < 0;
                    $isWarning   = !$isOverdue && $remainMins < 15;
                    $totalMins   = max(1, $booking->start_time->diffInMinutes($booking->end_time));
                    $pctUsed     = min(100, round($elapsedMin / $totalMins * 100));
                    $barColor    = $isOverdue ? '#ef4444' : ($isWarning ? '#f59e0b' : '#10b981');
                    $rowClass    = $isOverdue ? 'is-overdue' : ($isWarning ? 'is-warning' : 'is-ok');
                    $source      = $booking->source ?? 'walk_in';
                @endphp
                <tr class="booking-row {{ $rowClass }}" id="row-{{ $booking->id }}">

                    {{-- Plate + source --}}
                    <td>
                        <span class="plate-tag">
                            {{ $booking->vehicle_plate ?? '—' }}
                        </span>
                        <div>
                            @if($source === 'walk_in')
                            <span class="src-badge" style="background:rgba(16,185,129,.1);color:#059669;">
                                <i class="bi bi-box-arrow-in-right"></i>مباشر
                            </span>
                            @else
                            <span class="src-badge" style="background:rgba(14,165,233,.1);color:#0284c7;">
                                <i class="bi bi-calendar-check"></i>حجز
                            </span>
                            @endif
                        </div>
                    </td>

                    {{-- Driver --}}
                    <td>
                        <div class="fw-600 text-sm" style="color:#0f172a;">
                            {{ $booking->customer_name ?? '—' }}
                        </div>
                        @if($booking->phone)
                        <div class="text-xs" style="color:#94a3b8;direction:ltr;text-align:right;">
                            {{ $booking->phone }}
                        </div>
                        @endif
                    </td>

                    {{-- Lot --}}
                    <td>
                        <span class="badge badge-soft-info fw-600 text-xs">
                            {{ $booking->parkingLot->name }}
                        </span>
                    </td>

                    {{-- Entry time --}}
                    <td>
                        <div class="fw-600 text-sm" style="color:#0f172a;">
                            {{ $booking->start_time->format('H:i') }}
                        </div>
                        <div class="text-xs" style="color:#94a3b8;">
                            {{ $booking->start_time->format('d/m/Y') }}
                        </div>
                    </td>

                    {{-- Duration / status --}}
                    <td>
                        @if($isOverdue)
                        <span class="time-pill" style="background:rgba(239,68,68,.1);color:#dc2626;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            تجاوز {{ floor(abs($remainMins)/60) > 0 ? floor(abs($remainMins)/60).'س ' : '' }}{{ abs($remainMins)%60 }}د
                        </span>
                        @elseif($isWarning)
                        <span class="time-pill" style="background:rgba(245,158,11,.1);color:#d97706;">
                            <i class="bi bi-hourglass-split"></i>
                            متبقي {{ $remainMins }}د
                        </span>
                        @else
                        <span class="time-pill" style="background:rgba(16,185,129,.1);color:#059669;">
                            <i class="bi bi-clock"></i>
                            {{ floor($elapsedMin/60) }}س {{ $elapsedMin%60 }}د
                        </span>
                        @endif
                        <div class="dur-bar-wrap">
                            <div class="dur-bar-fill" style="width:{{ $pctUsed }}%;background:{{ $barColor }};"></div>
                        </div>
                    </td>

                    {{-- Action --}}
                    <td class="text-center">
                        <button class="btn btn-sm fw-600"
                                style="background:rgba(239,68,68,.1);color:#dc2626;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.35rem .9rem;"
                                onclick="askComplete({{ $booking->id }}, '{{ addslashes($booking->vehicle_plate ?? '—') }}', '{{ addslashes($booking->parkingLot->name) }}')">
                            <i class="bi bi-stop-circle me-1"></i>إنهاء
                        </button>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="bi bi-check-circle d-block mb-3" style="font-size:3rem;color:#10b981;opacity:.4;"></i>
                        <p class="fw-700 mb-1" style="color:#475569;font-size:1rem;">لا توجد حجوزات نشطة</p>
                        <p class="text-sm mb-0" style="color:#94a3b8;">جميع المواقف خالية حالياً</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($activeBookings->hasPages())
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="text-xs" style="color:#64748b;">
            عرض {{ $activeBookings->firstItem() }}–{{ $activeBookings->lastItem() }}
            من {{ $activeBookings->total() }}
        </span>
        {{ $activeBookings->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif

</div>

{{-- ── End booking modal (multi-step) ──────────────────────────────────────── --}}
<div class="modal fade" id="endBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;overflow:hidden;">

            {{-- Header --}}
            <div class="modal-header border-0 text-white"
                 style="background:linear-gradient(135deg,#1e293b,#334155);padding:1.1rem 1.4rem;">
                <div>
                    <div class="fw-800" style="font-size:.95rem;">
                        <i class="bi bi-stop-circle me-2"></i>إنهاء الحجز
                    </div>
                    <div class="text-xs mt-1" style="color:rgba(255,255,255,.6);">
                        <span id="endBkPlate" style="font-family:monospace;letter-spacing:.05em;"></span>
                        &nbsp;·&nbsp;
                        <span id="endBkLot"></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">

                {{-- ── STEP 1 : choose action ─────────────────────────────── --}}
                <div id="endStep1" class="p-4">
                    <p class="text-sm text-center mb-3 fw-600" style="color:#475569;">اختر طريقة الإنهاء:</p>
                    <div class="row g-3">

                        <div class="col-6">
                            <button class="w-100 h-100 border-0 rounded-3 p-3 text-center"
                                    style="background:#f0fdf4;cursor:pointer;transition:box-shadow .15s;"
                                    onmouseover="this.style.boxShadow='0 0 0 2px #10b981'"
                                    onmouseout="this.style.boxShadow=''"
                                    onclick="endChoose('payment')">
                                <div style="font-size:2rem;margin-bottom:.5rem;">💳</div>
                                <div class="fw-800 text-sm" style="color:#065f46;font-family:'Cairo',sans-serif;">إتمام الدفع</div>
                                <div class="text-xs mt-1" style="color:#6b7280;">احتساب الرسوم وتسجيل الدفع</div>
                            </button>
                        </div>

                        <div class="col-6">
                            <button class="w-100 h-100 border-0 rounded-3 p-3 text-center"
                                    style="background:#fff7ed;cursor:pointer;transition:box-shadow .15s;"
                                    onmouseover="this.style.boxShadow='0 0 0 2px #f59e0b'"
                                    onmouseout="this.style.boxShadow=''"
                                    onclick="endChoose('force')">
                                <div style="font-size:2rem;margin-bottom:.5rem;">⚠️</div>
                                <div class="fw-800 text-sm" style="color:#92400e;font-family:'Cairo',sans-serif;">إغلاق طارئ</div>
                                <div class="text-xs mt-1" style="color:#6b7280;">السيارة غادرت بدون دفع</div>
                            </button>
                        </div>

                    </div>
                </div>

                {{-- ── STEP 2A : receipt + payment ────────────────────────── --}}
                <div id="endStep2Pay" style="display:none;" class="p-4">

                    <button class="btn btn-sm mb-3 fw-600" onclick="endBack()"
                            style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
                        <i class="bi bi-arrow-right me-1"></i>رجوع
                    </button>

                    {{-- Times --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 rounded-3 text-center" style="background:#f0fdf4;">
                                <div class="text-xs fw-600 mb-1" style="color:#64748b;">وقت الدخول</div>
                                <div class="fw-700 small" id="payRcptEntry" style="color:#059669;">—</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3 text-center" style="background:#fef2f2;">
                                <div class="text-xs fw-600 mb-1" style="color:#64748b;">وقت الخروج</div>
                                <div class="fw-700 small" id="payRcptExit" style="color:#dc2626;">—</div>
                            </div>
                        </div>
                    </div>

                    {{-- Fee breakdown --}}
                    <div class="mb-3">
                        <div class="fw-bold small mb-2" style="color:#0f172a;font-family:'Cairo',sans-serif;">تفصيل الأجرة</div>
                        <div id="payRcptBreakdown" style="background:#f8fafc;border-radius:10px;padding:10px 14px;">
                            <div class="text-center py-2" style="color:#94a3b8;">
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:.625rem 0;border-top:2px solid #e2e8f0;font-weight:800;font-size:1.05rem;color:#0f172a;margin-top:.25rem;">
                            <span style="font-family:'Cairo',sans-serif;">الإجمالي</span>
                            <span id="payRcptTotal" style="color:#27ae60;">—</span>
                        </div>
                    </div>

                    {{-- Payment method --}}
                    <div class="mb-1">
                        <div class="fw-bold small mb-2" style="color:#0f172a;font-family:'Cairo',sans-serif;">طريقة الدفع</div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label style="cursor:pointer;display:block;">
                                    <input type="radio" name="adminPayType" value="cash" class="d-none" checked onchange="adminSelectPayment('cash')">
                                    <div id="adminPayOptCash"
                                         class="text-center p-3 rounded-3"
                                         style="border:2px solid #10b981;background:#f0fdf4;">
                                        <i class="bi bi-cash-coin d-block mb-1" style="font-size:1.4rem;color:#10b981;"></i>
                                        <span class="text-xs fw-600" style="color:#065f46;">نقداً</span>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <label style="cursor:pointer;display:block;">
                                    <input type="radio" name="adminPayType" value="upload" class="d-none" onchange="adminSelectPayment('upload')">
                                    <div id="adminPayOptUpload"
                                         class="text-center p-3 rounded-3"
                                         style="border:2px solid #e2e8f0;background:#fff;">
                                        <i class="bi bi-cloud-upload d-block mb-1" style="font-size:1.4rem;color:#3b82f6;"></i>
                                        <span class="text-xs fw-600" style="color:#1e40af;">إيصال إلكتروني</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div id="adminUploadArea" class="mt-2 p-3 border rounded-3 bg-light" style="display:none;">
                            <input type="file" id="adminPaymentProof" class="form-control form-control-sm" accept="image/*,.pdf">
                            <div class="text-xs mt-1 text-muted">JPG / PNG / PDF — حد أقصى 4MB</div>
                        </div>
                    </div>

                </div>

                {{-- ── STEP 2B : force close ───────────────────────────────── --}}
                <div id="endStep2Force" style="display:none;" class="p-4">

                    <button class="btn btn-sm mb-3 fw-600" onclick="endBack()"
                            style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
                        <i class="bi bi-arrow-right me-1"></i>رجوع
                    </button>

                    <div class="text-center py-2 mb-3">
                        <div style="font-size:2.8rem;margin-bottom:.75rem;">⚠️</div>
                        <p class="fw-700 mb-1" style="color:#0f172a;font-family:'Cairo',sans-serif;">إغلاق طارئ بدون دفع</p>
                        <p class="text-sm mb-0" style="color:#64748b;">سيتم إنهاء الحجز فوراً دون تسجيل أي رسوم.<br>استخدم هذا الخيار فقط إذا غادرت السيارة بدون دفع.</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-sm fw-600 mb-1" style="color:#475569;font-family:'Cairo',sans-serif;">
                            سبب الإغلاق <span style="color:#94a3b8;font-weight:400;">(اختياري)</span>
                        </label>
                        <textarea id="endForceNotes" rows="2"
                                  class="form-control"
                                  style="border-color:#e2e8f0;border-radius:.625rem;font-family:'Cairo',sans-serif;resize:none;"
                                  placeholder="مثال: السيارة غادرت دون إذن، خلل في النظام..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light fw-600 px-4"
                                style="font-family:'Cairo',sans-serif;border-radius:.5rem;"
                                data-bs-dismiss="modal">إلغاء</button>
                        <button type="button" id="endConfirmForceBtn"
                                class="btn btn-warning fw-bold flex-grow-1 text-dark"
                                style="font-family:'Cairo',sans-serif;border-radius:.5rem;">
                            <i class="bi bi-x-circle me-1"></i>تأكيد الإغلاق الطارئ
                        </button>
                    </div>

                </div>

            </div>

            {{-- Payment step footer (inside modal-content, outside modal-body) --}}
            <div id="endPayFooter" class="modal-footer border-0 px-4 pb-4 pt-2 gap-2" style="display:none;">
                <button type="button" class="btn btn-light fw-600 px-4"
                        style="font-family:'Cairo',sans-serif;border-radius:.5rem;"
                        data-bs-dismiss="modal">إلغاء</button>
                <button type="button" id="endConfirmPayBtn"
                        class="btn btn-success fw-bold flex-grow-1"
                        style="font-family:'Cairo',sans-serif;border-radius:.5rem;">
                    <i class="bi bi-check2-circle me-1"></i>تأكيد الدفع والخروج
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ── Toast ────────────────────────────────────────────────────────────────── --}}
<div id="toastWrap" style="position:fixed;bottom:1.5rem;inset-inline-start:50%;transform:translateX(-50%);z-index:9999;pointer-events:none;"></div>

@push('scripts')
<script>
// ── Filters ───────────────────────────────────────────────────────────────────
function applyFilters() {
    const url    = new URL(window.location);
    const lot    = document.getElementById('lotFilter').value;
    const search = document.getElementById('searchInput').value.trim();
    lot    ? url.searchParams.set('parking_lot_id', lot) : url.searchParams.delete('parking_lot_id');
    search ? url.searchParams.set('search', search)      : url.searchParams.delete('search');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
document.getElementById('lotFilter').addEventListener('change', applyFilters);
document.getElementById('searchInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') applyFilters();
});

// ── Toast ─────────────────────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
    const color = type === 'success' ? '#10b981' : '#ef4444';
    const icon  = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
    const el = document.createElement('div');
    el.style.cssText = `background:#0f172a;color:#f8fafc;padding:.65rem 1.25rem;border-radius:.625rem;font-size:.85rem;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,.25);display:flex;align-items:center;gap:.6rem;pointer-events:auto;margin-top:.5rem;`;
    el.innerHTML = `<i class="bi ${icon}" style="color:${color};font-size:1rem;flex-shrink:0;"></i>${msg}`;
    document.getElementById('toastWrap').appendChild(el);
    setTimeout(() => { el.style.transition='opacity .4s'; el.style.opacity='0'; setTimeout(()=>el.remove(),400); }, 3500);
}

// ── End booking modal ─────────────────────────────────────────────────────────
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let endBookingId   = null;
let endChoiceType  = null;
let adminPayMethod = 'cash';
let endModal       = null;

function getEndModal() {
    if (!endModal) endModal = new bootstrap.Modal(document.getElementById('endBookingModal'));
    return endModal;
}

function askComplete(id, plate, lot) {
    endBookingId  = id;
    endChoiceType = null;
    adminPayMethod = 'cash';
    document.getElementById('endBkPlate').textContent = plate;
    document.getElementById('endBkLot').textContent   = lot;
    showEndStep('step1');
    getEndModal().show();
}

function showEndStep(step) {
    document.getElementById('endStep1').style.display       = step === 'step1'     ? '' : 'none';
    document.getElementById('endStep2Pay').style.display    = step === 'step2Pay'  ? '' : 'none';
    document.getElementById('endStep2Force').style.display  = step === 'step2Force'? '' : 'none';
    document.getElementById('endPayFooter').style.display   = step === 'step2Pay'  ? 'flex' : 'none';
}

function endBack() { showEndStep('step1'); }

async function endChoose(type) {
    endChoiceType = type;

    if (type === 'payment') {
        showEndStep('step2Pay');
        document.querySelector('input[name="adminPayType"][value="cash"]').checked = true;
        adminSelectPayment('cash');

        // Reset pay button state
        const payBtn = document.getElementById('endConfirmPayBtn');
        payBtn.disabled = false;
        payBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>تأكيد الدفع والخروج';

        document.getElementById('payRcptBreakdown').innerHTML =
            '<div class="text-center py-2" style="color:#94a3b8;"><span class="spinner-border spinner-border-sm"></span></div>';
        document.getElementById('payRcptEntry').textContent = '—';
        document.getElementById('payRcptExit').textContent  = '—';
        document.getElementById('payRcptTotal').textContent = '—';

        try {
            const res  = await fetch(`/admin/bookings/${endBookingId}/checkout-preview`);
            const data = await res.json();
            if (!data.success) { showToast(data.message || 'حدث خطأ', 'error'); endBack(); return; }
            const d = data.data;
            document.getElementById('payRcptEntry').textContent = d.entry_time;
            document.getElementById('payRcptExit').textContent  = d.exit_time;
            document.getElementById('payRcptTotal').textContent = Number(d.total_fee).toLocaleString('ar-SA') + ' ل.س';
            const rows = d.fee_details.map(r => `
                <div style="display:flex;justify-content:space-between;padding:.3rem 0;border-bottom:1px dashed #f1f5f9;font-size:.82rem;">
                    <span>${r.day} <small style="color:#94a3b8;">${r.date}</small></span>
                    <span style="color:#64748b;">${r.hours}س × ${Number(r.rate).toLocaleString('ar-SA')}</span>
                    <span class="fw-600" style="color:#0f172a;">${Number(r.subtotal).toLocaleString('ar-SA')} ل.س</span>
                </div>`).join('');
            document.getElementById('payRcptBreakdown').innerHTML =
                rows || '<p class="text-xs text-center" style="color:#94a3b8;">لا تفاصيل</p>';
        } catch {
            showToast('تعذّر تحميل بيانات الفاتورة', 'error');
            endBack();
        }

    } else {
        showEndStep('step2Force');
        document.getElementById('endForceNotes').value = '';
    }
}

function adminSelectPayment(method) {
    adminPayMethod = method;
    const cashOpt    = document.getElementById('adminPayOptCash');
    const uploadOpt  = document.getElementById('adminPayOptUpload');
    const uploadArea = document.getElementById('adminUploadArea');
    if (method === 'cash') {
        cashOpt.style.cssText   = 'border:2px solid #10b981;background:#f0fdf4;border-radius:.75rem;text-align:center;padding:.75rem;';
        uploadOpt.style.cssText = 'border:2px solid #e2e8f0;background:#fff;border-radius:.75rem;text-align:center;padding:.75rem;';
        uploadArea.style.display = 'none';
    } else {
        cashOpt.style.cssText   = 'border:2px solid #e2e8f0;background:#fff;border-radius:.75rem;text-align:center;padding:.75rem;';
        uploadOpt.style.cssText = 'border:2px solid #3b82f6;background:#eff6ff;border-radius:.75rem;text-align:center;padding:.75rem;';
        uploadArea.style.display = 'block';
    }
}

// Payment confirm
document.getElementById('endConfirmPayBtn').addEventListener('click', async () => {
    if (!endBookingId) return;
    const btn = document.getElementById('endConfirmPayBtn');

    if (adminPayMethod === 'upload' && !document.getElementById('adminPaymentProof').files.length) {
        showToast('يرجى رفع إيصال الدفع', 'error');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري المعالجة...';

    const fd = new FormData();
    fd.append('type', 'payment');
    fd.append('payment_method', adminPayMethod);
    if (adminPayMethod === 'upload') {
        fd.append('payment_proof', document.getElementById('adminPaymentProof').files[0]);
    }

    try {
        const res  = await fetch(`/admin/bookings/${endBookingId}/complete`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body: fd
        });
        const data = await res.json();
        if (data.success) {
            getEndModal().hide();
            removeRow(endBookingId);
            showToast('تم تسجيل الدفع والخروج بنجاح');
        } else {
            showToast(data.message || 'حدث خطأ', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>تأكيد الدفع والخروج';
        }
    } catch {
        showToast('تعذّر الاتصال بالخادم', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>تأكيد الدفع والخروج';
    }
});

// Force-close confirm
document.getElementById('endConfirmForceBtn').addEventListener('click', async () => {
    if (!endBookingId) return;
    const btn = document.getElementById('endConfirmForceBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري الإغلاق...';

    const fd = new FormData();
    fd.append('type', 'force');
    const notes = document.getElementById('endForceNotes').value.trim();
    if (notes) fd.append('notes', notes);

    try {
        const res  = await fetch(`/admin/bookings/${endBookingId}/complete`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body: fd
        });
        const data = await res.json();
        if (data.success) {
            getEndModal().hide();
            removeRow(endBookingId);
            showToast('تم الإغلاق الطارئ للحجز');
        } else {
            showToast(data.message || 'حدث خطأ', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-x-circle me-1"></i>تأكيد الإغلاق الطارئ';
        }
    } catch {
        showToast('تعذّر الاتصال بالخادم', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-x-circle me-1"></i>تأكيد الإغلاق الطارئ';
    }
});

function removeRow(id) {
    const row = document.getElementById('row-' + id);
    if (row) {
        row.style.transition = 'opacity .4s, transform .4s';
        row.style.opacity    = '0';
        row.style.transform  = 'translateX(20px)';
        setTimeout(() => row.remove(), 420);
    }
}

// ── Auto-refresh (pauses while modal is open) ─────────────────────────────────
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
    badge.textContent = `تحديث بعد ${t}ث`;
    if (t <= 0) location.reload();
}, 1000);
</script>
@endpush

@endsection
