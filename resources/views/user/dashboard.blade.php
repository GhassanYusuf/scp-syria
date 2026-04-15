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
                    <th>تاريخ البدء</th>
                    <th>تاريخ الانتهاء</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $booking)
                <tr>
                    <td class="text-sm" style="color:#94a3b8;">{{ $booking->id }}</td>
                    <td>
                        <span class="fw-600" style="color:#0f172a;">
                            {{ $booking->parkingLot?->name ?? '—' }}
                        </span>
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
                    <td>
                        @if($booking->status === 'active')
                            <span class="badge badge-soft-warning">نشط</span>
                        @elseif($booking->status === 'completed')
                            <span class="badge badge-soft-success">مكتمل</span>
                        @else
                            <span class="badge badge-soft-danger">ملغي</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
