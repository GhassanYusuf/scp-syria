@extends('layouts.admin')
@section('title', 'جميع الحجوزات — دمشق باركينغ')
@section('page-title', 'جميع الحجوزات')

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-800 mb-1" style="font-size:1.15rem;color:#0f172a;">جميع الحجوزات</h2>
        <p class="text-sm mb-0" style="color:#64748b;">سجل كامل بجميع الحجوزات في النظام</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.bookings.active') }}"
           class="btn btn-sm fw-600"
           style="background:rgba(16,185,129,.1);color:#059669;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
            <i class="bi bi-activity me-1"></i>النشطة فقط
        </a>
        <button class="btn btn-sm fw-600" onclick="exportCSV()"
                style="background:rgba(16,185,129,.1);color:#059669;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;">
            <i class="bi bi-download me-1"></i>CSV
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="fw-700 text-sm">قائمة الحجوزات</span>
        <div class="input-group" style="max-width:240px;">
            <input type="text" id="searchInput" class="form-control form-control-sm"
                   style="border-color:#e2e8f0;border-inline-end:none;" placeholder="بحث...">
            <button class="btn btn-sm" style="background:#6366f1;color:#fff;border:none;" onclick="doSearch()">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="app-table w-100">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الموقف</th>
                    <th>العميل</th>
                    <th>اللوحة</th>
                    <th>الهاتف</th>
                    <th>البداية</th>
                    <th>النهاية</th>
                    <th class="text-center">الحالة</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr>
                    <td class="text-xs" style="color:#94a3b8;">{{ $booking->id }}</td>
                    <td><span class="fw-600">{{ $booking->parkingLot->name ?? '--' }}</span></td>
                    <td class="text-sm">{{ $booking->customer_name ?? $booking->user_name ?? '--' }}</td>
                    <td>
                        <span style="font-family:monospace;font-weight:700;color:#0f172a;">
                            {{ $booking->vehicle_plate ?? '--' }}
                        </span>
                    </td>
                    <td class="text-sm" style="direction:ltr;text-align:right;">
                        {{ $booking->phone ?? $booking->user_phone ?? '--' }}
                    </td>
                    <td class="text-xs" style="color:#64748b;">{{ $booking->start_time->format('Y/m/d H:i') }}</td>
                    <td class="text-xs" style="color:#64748b;">{{ $booking->end_time->format('Y/m/d H:i') }}</td>
                    <td class="text-center">
                        @if($booking->status === 'active')
                            <span class="badge badge-soft-success text-xs">نشط</span>
                        @elseif($booking->status === 'completed')
                            <span class="badge badge-soft-secondary text-xs">مكتمل</span>
                        @else
                            <span class="badge badge-soft-warning text-xs">{{ $booking->status }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem;color:#cbd5e1;"></i>
                        <span class="text-sm" style="color:#94a3b8;">لا توجد حجوزات</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($bookings) && $bookings->hasPages())
    <div class="card-header">
        {{ $bookings->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@push('scripts')
<script>
    function exportCSV() {
        window.location.href = '{{ route("admin.bookings.export", ["format" => "csv"]) }}';
    }
    function doSearch() {
        window.location.href = `/admin/bookings?search=${encodeURIComponent(document.getElementById('searchInput').value)}`;
    }
    document.getElementById('searchInput').addEventListener('keypress', e => {
        if (e.key === 'Enter') doSearch();
    });
</script>
@endpush

@endsection
