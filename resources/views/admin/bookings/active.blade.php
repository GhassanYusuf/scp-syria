@extends('layouts.admin')
@section('title', 'الحجوزات النشطة — دمشق باركينغ')
@section('page-title', 'الحجوزات النشطة')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-800 mb-1" style="font-size:1.15rem;color:#0f172a;">الحجوزات النشطة</h2>
        <p class="text-sm mb-0" style="color:#64748b;">
            السيارات المسجّلة حالياً · يتجدد كل 30 ثانية
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge badge-soft-success fw-600" style="font-size:.82rem;padding:.4em .9em;">
            <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle;"></i>
            {{ $activeBookings->total() }} نشط
        </span>
        <span class="badge badge-soft-secondary text-xs" id="refresh-badge">تحديث بعد 30ث</span>
    </div>
</div>

{{-- ── Filter ────────────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-body p-3">
        <div class="row align-items-end g-3">
            <div class="col-md-5">
                <label class="form-label">فلترة حسب الموقف</label>
                <select id="lotFilter" class="form-select form-select-sm">
                    <option value="">جميع المواقف</option>
                    @foreach($parkingLots as $lot)
                    <option value="{{ $lot->id }}" {{ request('parking_lot_id') == $lot->id ? 'selected' : '' }}>
                        {{ $lot->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @if(request('parking_lot_id'))
            <div class="col-auto">
                <a href="{{ route('admin.bookings.active') }}"
                   class="btn btn-sm fw-600"
                   style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
                    <i class="bi bi-x me-1"></i>إلغاء الفلتر
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Table ────────────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="table-responsive">
        <table class="app-table w-100">
            <thead>
                <tr>
                    <th>رقم اللوحة</th>
                    <th>السائق</th>
                    <th>الهاتف</th>
                    <th>الموقف</th>
                    <th>وقت الدخول</th>
                    <th>وقت الخروج</th>
                    <th>المدة</th>
                    <th class="text-center">إنهاء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeBookings as $booking)
                <tr id="row-{{ $booking->id }}">
                    <td>
                        <span class="fw-700" style="font-family:monospace;font-size:.95rem;color:#0f172a;">
                            {{ $booking->vehicle_plate ?? $booking->customer_name ?? '--' }}
                        </span>
                    </td>
                    <td class="text-sm">{{ $booking->user_name ?? $booking->customer_name ?? '--' }}</td>
                    <td class="text-sm" style="direction:ltr;text-align:right;">
                        {{ $booking->user_phone ?? $booking->phone ?? '--' }}
                    </td>
                    <td>
                        <span class="badge badge-soft-info text-xs fw-600">
                            {{ $booking->parkingLot->name }}
                        </span>
                    </td>
                    <td class="text-xs" style="color:#64748b;">{{ $booking->start_time->format('Y/m/d H:i') }}</td>
                    <td class="text-xs" style="color:#64748b;">{{ $booking->end_time->format('Y/m/d H:i') }}</td>
                    <td>
                        <span class="badge badge-soft-warning text-xs fw-600">
                            {{ $booking->start_time->diffForHumans(now(), true) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm fw-600"
                                style="background:rgba(239,68,68,.1);color:#dc2626;border:none;border-radius:.375rem;font-family:'Cairo',sans-serif;padding:.3rem .75rem;"
                                onclick="completeBooking({{ $booking->id }}, this)">
                            <i class="bi bi-stop-circle me-1"></i>إنهاء
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-check-circle d-block mb-3" style="font-size:2.5rem;color:#10b981;opacity:.5;"></i>
                        <p class="fw-600 mb-0" style="color:#475569;">لا توجد حجوزات نشطة</p>
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

@push('scripts')
<script>
    document.getElementById('lotFilter').addEventListener('change', function () {
        const url = new URL(window.location);
        this.value ? url.searchParams.set('parking_lot_id', this.value)
                   : url.searchParams.delete('parking_lot_id');
        window.location.href = url.toString();
    });

    async function completeBooking(id, btn) {
        if (!confirm('إنهاء هذا الحجز؟')) return;
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const res  = await fetch(`/admin/bookings/${id}/complete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });
            const data = await res.json();

            if (data.success) {
                const row = document.getElementById('row-' + id);
                row.style.transition = 'opacity .4s';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 400);
            } else {
                alert(data.message || 'خطأ');
                btn.innerHTML = orig;
                btn.disabled  = false;
            }
        } catch {
            alert('خطأ في الاتصال');
            btn.innerHTML = orig;
            btn.disabled  = false;
        }
    }

    // Countdown
    let t = 30;
    const badge = document.getElementById('refresh-badge');
    setInterval(() => {
        t--;
        badge.textContent = `تحديث بعد ${t}ث`;
        if (t <= 0) location.reload();
    }, 1000);
</script>
@endpush

@endsection
