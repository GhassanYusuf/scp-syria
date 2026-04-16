@extends('layouts.user')
@section('title', 'حجوزاتي — دمشق باركينغ')

@section('content')

{{-- Page header --}}
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('parking.index') }}"
       class="btn btn-sm"
       style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
        <i class="bi bi-arrow-right me-1"></i>العودة
    </a>
    <div>
        <h1 class="fw-800 mb-0" style="font-size:1.25rem;color:#0f172a;">حجوزاتي</h1>
        <p class="text-sm mb-0" style="color:#64748b;">سجل حجوزاتك في مواقف السيارات</p>
    </div>
</div>

{{-- ── Debt banner ─────────────────────────────────────────────────────────── --}}
@if($pendingDebt > 0)
<div class="mb-4 p-3 rounded-3 d-flex align-items-center gap-3 flex-wrap"
     style="background:linear-gradient(135deg,#fff7ed,#fef3c7);border:1.5px solid #fbbf24;">
    <div style="width:42px;height:42px;background:#f59e0b;border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <i class="bi bi-exclamation-triangle-fill" style="color:#fff;font-size:1.1rem;"></i>
    </div>
    <div class="flex-grow-1">
        <div class="fw-700" style="color:#92400e;font-size:.9rem;">رصيد مستحق غير مدفوع</div>
        <div class="text-sm" style="color:#b45309;">
            لديك رسوم إلغاء متراكمة بقيمة
            <strong>{{ number_format($pendingDebt) }} ل.س</strong>
            — ستُضاف تلقائياً إلى حجزك القادم.
        </div>
    </div>
    <div class="fw-800" style="color:#92400e;font-size:1.25rem;white-space:nowrap;">
        {{ number_format($pendingDebt) }} ل.س
    </div>
</div>
@endif

{{-- ── Stats row ──────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @php
        $statCards = [
            ['label' => 'إجمالي الحجوزات', 'value' => $stats['total'],     'icon' => 'bi-calendar3',      'color' => '#6366f1', 'bg' => 'rgba(99,102,241,.1)'],
            ['label' => 'نشطة',            'value' => $stats['active'],    'icon' => 'bi-clock-history',  'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.1)'],
            ['label' => 'مكتملة',          'value' => $stats['completed'], 'icon' => 'bi-check-circle',   'color' => '#10b981', 'bg' => 'rgba(16,185,129,.1)'],
            ['label' => 'ملغاة',           'value' => $stats['cancelled'], 'icon' => 'bi-x-circle',       'color' => '#ef4444', 'bg' => 'rgba(239,68,68,.1)'],
        ];
    @endphp

    @foreach($statCards as $s)
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:44px;height:44px;background:{{ $s['bg'] }};">
                    <i class="bi {{ $s['icon'] }}" style="font-size:1.2rem;color:{{ $s['color'] }};"></i>
                </div>
                <div>
                    <div class="fw-800" style="font-size:1.4rem;color:{{ $s['color'] }};line-height:1;">{{ $s['value'] }}</div>
                    <div class="text-xs" style="color:#64748b;">{{ $s['label'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Bookings table ──────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="fw-700" style="font-size:.9rem;">
            <i class="bi bi-list-check me-2" style="color:#6366f1;"></i>
            قائمة الحجوزات
        </span>
        <span class="badge badge-soft-secondary text-xs">{{ $stats['total'] }} حجز</span>
    </div>

    @if($bookings->isEmpty())
    <div class="text-center py-5" style="color:#94a3b8;">
        <i class="bi bi-calendar-x d-block mb-2" style="font-size:2.5rem;opacity:.35;"></i>
        <p class="fw-600 mb-1" style="color:#64748b;">لا توجد حجوزات بعد</p>
        <p class="text-sm mb-3" style="color:#94a3b8;">ابدأ بالبحث عن موقف وحجز مكانك</p>
        <a href="{{ route('parking.index') }}"
           class="btn btn-sm fw-600"
           style="background:#6366f1;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
            <i class="bi bi-search me-1"></i>تصفح المواقف
        </a>
    </div>
    @else
    <div class="table-responsive">
        <table class="app-table w-100">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الموقف</th>
                    <th>بدء</th>
                    <th>انتهاء</th>
                    <th>الرسوم</th>
                    <th>الحالة</th>
                    <th class="text-center">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $booking)
                @php
                    $canCancel  = $booking->status === 'active' && $booking->source === 'reservation';
                    $hasDebt    = $booking->status === 'cancelled' && $booking->total_fee > 0 && is_null($booking->paid_at);
                    $isFree     = $booking->status === 'cancelled' && ($booking->total_fee == 0 || is_null($booking->total_fee));
                    $isPaid     = $booking->status === 'completed' || ($booking->status === 'cancelled' && !is_null($booking->paid_at) && $booking->total_fee > 0);
                @endphp
                <tr id="bk-row-{{ $booking->id }}">
                    <td class="text-sm" style="color:#94a3b8;">{{ $booking->id }}</td>

                    <td>
                        <span class="fw-600" style="color:#0f172a;">{{ $booking->parkingLot?->name ?? '—' }}</span>
                        <div class="text-xs" style="color:#94a3b8;">{{ $booking->parkingLot?->address }}</div>
                    </td>

                    <td class="text-sm" style="color:#475569;">
                        {{ $booking->start_time->format('Y/m/d') }}
                        <div class="text-xs" style="color:#94a3b8;">{{ $booking->start_time->format('H:i') }}</div>
                    </td>

                    <td class="text-sm" style="color:#475569;">
                        {{ $booking->end_time->format('Y/m/d') }}
                        <div class="text-xs" style="color:#94a3b8;">{{ $booking->end_time->format('H:i') }}</div>
                    </td>

                    {{-- Fee column --}}
                    <td>
                        @if($hasDebt)
                            <span class="fw-700" style="color:#ef4444;">{{ number_format($booking->total_fee) }} ل.س</span>
                            <div class="text-xs fw-600" style="color:#ef4444;">مستحق</div>
                        @elseif($booking->total_fee > 0 && !is_null($booking->paid_at))
                            <span class="fw-600 text-sm" style="color:#10b981;">{{ number_format($booking->total_fee) }} ل.س</span>
                            <div class="text-xs" style="color:#10b981;">مدفوع</div>
                        @elseif($booking->total_fee > 0)
                            <span class="fw-600 text-sm" style="color:#475569;">{{ number_format($booking->total_fee) }} ل.س</span>
                        @elseif($isFree && $booking->status === 'cancelled')
                            <span class="text-xs" style="color:#94a3b8;">مجاني</span>
                        @else
                            <span class="text-xs" style="color:#94a3b8;">—</span>
                        @endif
                    </td>

                    {{-- Status column --}}
                    <td>
                        @if($booking->status === 'active')
                            @if($booking->source === 'reservation')
                                <span class="badge badge-soft-primary">حجز مسبق</span>
                            @else
                                <span class="badge badge-soft-warning">داخل الموقف</span>
                            @endif
                        @elseif($booking->status === 'completed')
                            <span class="badge badge-soft-success">مكتمل</span>
                        @else
                            @if($hasDebt)
                                <span class="badge" style="background:rgba(239,68,68,.1);color:#dc2626;">ملغي — رسوم معلقة</span>
                            @else
                                <span class="badge badge-soft-danger">ملغي</span>
                            @endif
                        @endif
                    </td>

                    {{-- Action column --}}
                    <td class="text-center">
                        @if($canCancel)
                            <button class="btn btn-sm fw-600"
                                    style="background:rgba(239,68,68,.1);color:#dc2626;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.3rem .75rem;"
                                    onclick="openCancelModal({{ $booking->id }}, '{{ addslashes($booking->parkingLot?->name ?? '') }}', '{{ $booking->start_time->toISOString() }}')">
                                <i class="bi bi-x-circle me-1"></i>إلغاء
                            </button>
                        @else
                            <span class="text-xs" style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>


{{-- ══════════════════════════════════════════════════════════════════════════
     CANCEL RESERVATION MODAL
══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:1rem;overflow:hidden;">

            <div class="modal-header border-0 text-white"
                 style="background:linear-gradient(135deg,#1e293b,#334155);padding:1.1rem 1.4rem;">
                <div>
                    <div class="fw-800" style="font-size:.95rem;">
                        <i class="bi bi-x-circle me-2"></i>إلغاء الحجز
                    </div>
                    <div class="text-xs mt-1" style="color:rgba(255,255,255,.6);" id="cancelBkLot"></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">

                {{-- Loading --}}
                <div id="cancelStep0" class="text-center p-5">
                    <span class="spinner-border" style="color:#6366f1;"></span>
                </div>

                {{-- Free cancellation --}}
                <div id="cancelStepFree" style="display:none;" class="p-4 text-center">
                    <div style="font-size:3rem;margin-bottom:.75rem;">✅</div>
                    <p class="fw-700 mb-1" style="color:#0f172a;font-family:'Cairo',sans-serif;">إلغاء مجاني</p>
                    <p class="text-sm" style="color:#64748b;">لم يبدأ وقت الحجز بعد — يمكنك الإلغاء مجاناً.</p>
                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn btn-light fw-600 flex-fill"
                                style="font-family:'Cairo',sans-serif;border-radius:.5rem;"
                                data-bs-dismiss="modal">تراجع</button>
                        <button type="button" id="cancelFreeConfirmBtn"
                                class="btn btn-danger fw-bold flex-fill"
                                style="font-family:'Cairo',sans-serif;border-radius:.5rem;">
                            <i class="bi bi-x-circle me-1"></i>تأكيد الإلغاء
                        </button>
                    </div>
                </div>

                {{-- Paid cancellation (after start time) --}}
                <div id="cancelStepPaid" style="display:none;" class="p-4">

                    <div class="p-3 rounded-3 mb-3" style="background:#fef2f2;border:1px solid #fecaca;">
                        <div class="fw-700 text-sm mb-1" style="color:#991b1b;font-family:'Cairo',sans-serif;">
                            <i class="bi bi-exclamation-triangle me-1"></i>بدأ وقت حجزك
                        </div>
                        <div class="text-xs" style="color:#b91c1c;">
                            الإلغاء بعد وقت البدء يستوجب دفع رسوم المدة المنقضية.
                        </div>
                    </div>

                    {{-- Fee breakdown --}}
                    <div class="mb-3">
                        <div class="fw-700 small mb-2" style="color:#0f172a;font-family:'Cairo',sans-serif;">تفصيل الرسوم المستحقة</div>
                        <div id="cancelFeeBreakdown"
                             style="background:#f8fafc;border-radius:.625rem;padding:.75rem 1rem;font-size:.82rem;">
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-top:2px solid #e2e8f0;font-weight:800;font-size:1rem;color:#0f172a;margin-top:.25rem;">
                            <span style="font-family:'Cairo',sans-serif;">المستحق عليك</span>
                            <span id="cancelFeeTotal" style="color:#ef4444;">—</span>
                        </div>
                    </div>

                    {{-- Payment method (shown only for pay-now) --}}
                    <div id="cancelPayMethodWrap" style="display:none;" class="mb-3">
                        <div class="fw-700 small mb-2" style="color:#0f172a;font-family:'Cairo',sans-serif;">طريقة الدفع</div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label style="cursor:pointer;display:block;">
                                    <input type="radio" name="cancelPayType" value="cash" class="d-none" checked
                                           onchange="cancelSelectPayment('cash')">
                                    <div id="cancelPayOptCash"
                                         class="text-center p-3 rounded-3"
                                         style="border:2px solid #10b981;background:#f0fdf4;">
                                        <i class="bi bi-cash-coin d-block mb-1" style="font-size:1.3rem;color:#10b981;"></i>
                                        <span class="text-xs fw-600" style="color:#065f46;">نقداً</span>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <label style="cursor:pointer;display:block;">
                                    <input type="radio" name="cancelPayType" value="upload" class="d-none"
                                           onchange="cancelSelectPayment('upload')">
                                    <div id="cancelPayOptUpload"
                                         class="text-center p-3 rounded-3"
                                         style="border:2px solid #e2e8f0;background:#fff;">
                                        <i class="bi bi-cloud-upload d-block mb-1" style="font-size:1.3rem;color:#3b82f6;"></i>
                                        <span class="text-xs fw-600" style="color:#1e40af;">إيصال إلكتروني</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div id="cancelUploadArea" class="mt-2 p-3 border rounded-3 bg-light" style="display:none;">
                            <input type="file" id="cancelPayProof" class="form-control form-control-sm" accept="image/*,.pdf">
                            <div class="text-xs mt-1 text-muted">JPG / PNG / PDF — حد أقصى 4MB</div>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div id="cancelPaidActions" class="d-flex gap-2">
                        <button type="button" class="btn btn-light fw-600"
                                style="font-family:'Cairo',sans-serif;border-radius:.5rem;padding:.5rem 1rem;"
                                data-bs-dismiss="modal">تراجع</button>
                        <button type="button" id="cancelPayNowBtn"
                                class="btn btn-success fw-bold flex-grow-1"
                                style="font-family:'Cairo',sans-serif;border-radius:.5rem;">
                            <i class="bi bi-credit-card me-1"></i>دفع الآن وإلغاء
                        </button>
                        <button type="button" id="cancelPayLaterBtn"
                                class="btn btn-outline-warning fw-bold flex-grow-1"
                                style="font-family:'Cairo',sans-serif;border-radius:.5rem;color:#b45309;">
                            <i class="bi bi-clock me-1"></i>سأدفع لاحقاً
                        </button>
                    </div>

                    {{-- After pay-now chosen: back + confirm --}}
                    <div id="cancelPayNowActions" style="display:none;" class="d-flex gap-2">
                        <button type="button" class="btn btn-light fw-600"
                                style="font-family:'Cairo',sans-serif;border-radius:.5rem;"
                                onclick="cancelBackToActions()">
                            <i class="bi bi-arrow-right me-1"></i>رجوع
                        </button>
                        <button type="button" id="cancelConfirmPayBtn"
                                class="btn btn-success fw-bold flex-grow-1"
                                style="font-family:'Cairo',sans-serif;border-radius:.5rem;">
                            <i class="bi bi-check2-circle me-1"></i>تأكيد الدفع والإلغاء
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

{{-- Toast container --}}
<div id="dashToastWrap"
     style="position:fixed;bottom:1.5rem;inset-inline-end:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:.5rem;"></div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// ── Toast ─────────────────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
    const colors = { success:'#10b981', danger:'#ef4444', warning:'#f59e0b' };
    const t = document.createElement('div');
    t.style.cssText = `background:${colors[type]||colors.success};color:#fff;padding:.7rem 1.2rem;
        border-radius:.625rem;font-family:'Cairo',sans-serif;font-size:.875rem;font-weight:600;
        box-shadow:0 8px 24px rgba(0,0,0,.18);opacity:0;transition:opacity .25s;max-width:320px;`;
    t.textContent = msg;
    document.getElementById('dashToastWrap').appendChild(t);
    requestAnimationFrame(() => t.style.opacity = '1');
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 4000);
}

// ── Cancel modal state ────────────────────────────────────────────────────
let cancelBkId    = null;
let cancelPayMode = 'cash';
let cancelModal   = null;

function getCancelModal() {
    if (!cancelModal) cancelModal = new bootstrap.Modal(document.getElementById('cancelBookingModal'));
    return cancelModal;
}

function showCancelStep(step) {
    ['cancelStep0','cancelStepFree','cancelStepPaid'].forEach(id => {
        document.getElementById(id).style.display = 'none';
    });
    document.getElementById(step).style.display = '';
}

async function openCancelModal(id, lotName, startIso) {
    cancelBkId    = id;
    cancelPayMode = 'cash';
    document.getElementById('cancelBkLot').textContent = lotName;
    showCancelStep('cancelStep0');
    getCancelModal().show();

    try {
        const res  = await fetch(`/reservations/${id}/cancel-preview`);
        const data = await res.json();
        if (!data.success) { showToast(data.message || 'حدث خطأ', 'danger'); getCancelModal().hide(); return; }
        const d = data.data;

        if (d.is_free) {
            showCancelStep('cancelStepFree');
        } else {
            // Build breakdown
            const rows = d.fee_details.map(r => `
                <div style="display:flex;justify-content:space-between;padding:.25rem 0;border-bottom:1px dashed #f1f5f9;">
                    <span>${r.day} <small style="color:#94a3b8;">${r.date}</small></span>
                    <span style="color:#64748b;">${r.hours}س × ${Number(r.rate).toLocaleString('ar-SA')}</span>
                    <span class="fw-600" style="color:#0f172a;">${Number(r.subtotal).toLocaleString('ar-SA')} ل.س</span>
                </div>`).join('');
            document.getElementById('cancelFeeBreakdown').innerHTML =
                rows || '<span style="color:#94a3b8;font-size:.8rem;">— مدة قصيرة جداً —</span>';
            document.getElementById('cancelFeeTotal').textContent =
                Number(d.fee).toLocaleString('ar-SA') + ' ل.س';

            // Reset UI
            document.getElementById('cancelPayMethodWrap').style.display = 'none';
            document.getElementById('cancelPaidActions').style.display   = 'flex';
            document.getElementById('cancelPayNowActions').style.display = 'none';
            cancelSelectPayment('cash');
            document.querySelector('input[name="cancelPayType"][value="cash"]').checked = true;

            showCancelStep('cancelStepPaid');
        }
    } catch {
        showToast('تعذّر تحميل بيانات الإلغاء', 'danger');
        getCancelModal().hide();
    }
}

// Free cancel confirm
document.getElementById('cancelFreeConfirmBtn').addEventListener('click', async () => {
    const btn = document.getElementById('cancelFreeConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
    try {
        const fd = new FormData();
        fd.append('type', 'free');
        const res  = await fetch(`/reservations/${cancelBkId}/cancel`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: fd
        });
        const data = await res.json();
        getCancelModal().hide();
        if (data.success) {
            showToast(data.message, 'success');
            const row = document.getElementById('bk-row-' + cancelBkId);
            if (row) { row.style.opacity = '0'; row.style.transition = 'opacity .4s'; setTimeout(() => location.reload(), 600); }
        } else { showToast(data.message || 'حدث خطأ', 'danger'); }
    } catch {
        showToast('خطأ في الاتصال', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-x-circle me-1"></i>تأكيد الإلغاء';
    }
});

// Pay-now button (show payment method)
document.getElementById('cancelPayNowBtn').addEventListener('click', () => {
    document.getElementById('cancelPaidActions').style.display   = 'none';
    document.getElementById('cancelPayNowActions').style.display = 'flex';
    document.getElementById('cancelPayMethodWrap').style.display = 'block';
});

function cancelBackToActions() {
    document.getElementById('cancelPayNowActions').style.display = 'none';
    document.getElementById('cancelPaidActions').style.display   = 'flex';
    document.getElementById('cancelPayMethodWrap').style.display = 'none';
}

// Pay later
document.getElementById('cancelPayLaterBtn').addEventListener('click', async () => {
    const btn = document.getElementById('cancelPayLaterBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
    try {
        const fd = new FormData();
        fd.append('type', 'pay_later');
        const res  = await fetch(`/reservations/${cancelBkId}/cancel`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: fd
        });
        const data = await res.json();
        getCancelModal().hide();
        if (data.success) { showToast(data.message, 'warning'); setTimeout(() => location.reload(), 1200); }
        else               { showToast(data.message || 'حدث خطأ', 'danger'); }
    } catch {
        showToast('خطأ في الاتصال', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-clock me-1"></i>سأدفع لاحقاً';
    }
});

// Confirm pay now
document.getElementById('cancelConfirmPayBtn').addEventListener('click', async () => {
    if (cancelPayMode === 'upload' && !document.getElementById('cancelPayProof').files.length) {
        showToast('يرجى رفع إيصال الدفع', 'danger'); return;
    }
    const btn = document.getElementById('cancelConfirmPayBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري الدفع...';
    try {
        const fd = new FormData();
        fd.append('type', 'pay_now');
        fd.append('payment_method', cancelPayMode);
        if (cancelPayMode === 'upload') fd.append('payment_proof', document.getElementById('cancelPayProof').files[0]);
        const res  = await fetch(`/reservations/${cancelBkId}/cancel`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: fd
        });
        const data = await res.json();
        getCancelModal().hide();
        if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 800); }
        else               { showToast(data.message || 'حدث خطأ', 'danger'); }
    } catch {
        showToast('خطأ في الاتصال', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>تأكيد الدفع والإلغاء';
    }
});

function cancelSelectPayment(method) {
    cancelPayMode = method;
    const cashOpt   = document.getElementById('cancelPayOptCash');
    const uploadOpt = document.getElementById('cancelPayOptUpload');
    const area      = document.getElementById('cancelUploadArea');
    if (method === 'cash') {
        cashOpt.style.cssText   = 'border:2px solid #10b981;background:#f0fdf4;border-radius:.75rem;text-align:center;padding:.75rem;';
        uploadOpt.style.cssText = 'border:2px solid #e2e8f0;background:#fff;border-radius:.75rem;text-align:center;padding:.75rem;';
        area.style.display = 'none';
    } else {
        cashOpt.style.cssText   = 'border:2px solid #e2e8f0;background:#fff;border-radius:.75rem;text-align:center;padding:.75rem;';
        uploadOpt.style.cssText = 'border:2px solid #3b82f6;background:#eff6ff;border-radius:.75rem;text-align:center;padding:.75rem;';
        area.style.display = 'block';
    }
}
</script>
@endpush

@endsection
