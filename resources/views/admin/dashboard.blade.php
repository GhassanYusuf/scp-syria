@extends('layouts.admin')
@section('title', 'لوحة التحكم — دمشق باركينغ')
@section('page-title', 'لوحة التحكم')

@section('content')

{{-- ── Stats Row ──────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    @php
    $stats = [
        ['id' => 'total-parking-lots', 'label' => 'إجمالي المواقف',    'icon' => 'bi-buildings',         'color' => '#6366f1', 'bg' => 'rgba(99,102,241,.1)'],
        ['id' => 'total-bookings',     'label' => 'إجمالي الحجوزات',   'icon' => 'bi-calendar3',          'color' => '#0ea5e9', 'bg' => 'rgba(14,165,233,.1)'],
        ['id' => 'active-bookings',    'label' => 'الحجوزات النشطة',   'icon' => 'bi-clock-history',      'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.1)'],
        ['id' => 'occupancy-rate',     'label' => 'معدل الإشغال',       'icon' => 'bi-graph-up-arrow',     'color' => '#ef4444', 'bg' => 'rgba(239,68,68,.1)'],
        ['id' => 'estimated-revenue',  'label' => 'الإيرادات المتوقعة', 'icon' => 'bi-cash-coin',          'color' => '#10b981', 'bg' => 'rgba(16,185,129,.1)'],
        ['id' => 'available-spots',    'label' => 'الأماكن المتاحة',    'icon' => 'bi-check2-square',      'color' => '#8b5cf6', 'bg' => 'rgba(139,92,246,.1)'],
    ];
    @endphp

    @foreach($stats as $s)
    <div class="col-xl-2 col-lg-4 col-sm-6">
        <div class="card stat-card h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:42px;height:42px;background:{{ $s['bg'] }};">
                        <i class="bi {{ $s['icon'] }}" style="font-size:1.2rem;color:{{ $s['color'] }};"></i>
                    </div>
                </div>
                <div class="fw-800 lh-sm mb-1" id="{{ $s['id'] }}"
                     style="font-size:1.6rem;color:{{ $s['color'] }};">--</div>
                <div class="text-xs" style="color:#64748b;">{{ $s['label'] }}</div>
            </div>
        </div>
    </div>
    @endforeach

</div>

{{-- ── Quick Actions ───────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <h5 class="fw-700 mb-3" style="color:#0f172a;font-size:.95rem;">
            <i class="bi bi-lightning-charge-fill me-2" style="color:#f59e0b;"></i>
            إجراءات سريعة
        </h5>
        <div class="row g-3">
            <div class="col-lg-3 col-sm-6">
                <a href="{{ route('admin.parking-lots.index') }}" class="quick-card card h-100 p-4">
                    <div class="quick-icon" style="background:rgba(99,102,241,.1);">
                        <i class="bi bi-buildings" style="color:#6366f1;"></i>
                    </div>
                    <h6 style="color:#0f172a;">إدارة المواقف</h6>
                    <small>إضافة · تعديل · تفعيل</small>
                </a>
            </div>
            <div class="col-lg-3 col-sm-6">
                <a href="{{ route('admin.bookings.active') }}" class="quick-card card h-100 p-4">
                    <div class="quick-icon" style="background:rgba(16,185,129,.1);">
                        <i class="bi bi-calendar-check" style="color:#10b981;"></i>
                    </div>
                    <h6 style="color:#0f172a;">الحجوزات النشطة</h6>
                    <small>إنهاء · فلترة · متابعة</small>
                </a>
            </div>
            <div class="col-lg-3 col-sm-6">
                <a href="{{ route('operator.dashboard') }}" class="quick-card card h-100 p-4">
                    <div class="quick-icon" style="background:rgba(245,158,11,.1);">
                        <i class="bi bi-person-badge" style="color:#f59e0b;"></i>
                    </div>
                    <h6 style="color:#0f172a;">لوحة المشغّل</h6>
                    <small>دخول وخروج فوري</small>
                </a>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="quick-card card h-100 p-4" onclick="alert('قريباً...')">
                    <div class="quick-icon" style="background:rgba(100,116,139,.1);">
                        <i class="bi bi-gear" style="color:#64748b;"></i>
                    </div>
                    <h6 style="color:#0f172a;">الإعدادات</h6>
                    <small>تخصيص · تقارير</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Charts ──────────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-700" style="font-size:.9rem;">
                    <i class="bi bi-bar-chart-line me-2" style="color:#6366f1;"></i>
                    الحجوزات اليومية — آخر 7 أيام
                </span>
            </div>
            <div class="card-body p-3">
                <canvas id="dailyChart" height="140"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-700" style="font-size:.9rem;">
                    <i class="bi bi-trophy me-2" style="color:#f59e0b;"></i>
                    أفضل 5 مواقف
                </span>
            </div>
            <div class="card-body p-3 d-flex align-items-center justify-content-center">
                <canvas id="topChart" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── Recent Bookings ─────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="fw-700" style="font-size:.9rem;">
            <i class="bi bi-clock-history me-2" style="color:#0ea5e9;"></i>
            آخر الحجوزات
        </span>
        <span class="badge badge-soft-secondary" id="last-updated">--</span>
    </div>
    <div class="table-responsive">
        <table class="app-table w-100">
            <thead>
                <tr>
                    <th>الموقف</th>
                    <th>العميل</th>
                    <th>الهاتف</th>
                    <th>الحالة</th>
                    <th>وقت البدء</th>
                    <th>وقت الانتهاء</th>
                </tr>
            </thead>
            <tbody id="bookings-body">
                <tr>
                    <td colspan="6" class="text-center py-4" style="color:#94a3b8;">
                        <div class="spinner-border spinner-border-sm me-2"></div>
                        جاري التحميل...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
let dailyChart, topChart;

async function loadStats() {
    try {
        const { success, data } = await fetch('/admin/stats').then(r => r.json());
        if (!success) return;
        document.getElementById('total-parking-lots').textContent  = data.total_parking_lots  ?? 0;
        document.getElementById('total-bookings').textContent      = data.total_bookings      ?? 0;
        document.getElementById('active-bookings').textContent     = data.active_bookings     ?? 0;
        document.getElementById('occupancy-rate').textContent      = (data.occupancy_rate ?? 0) + '%';
        document.getElementById('estimated-revenue').textContent   = (data.estimated_revenue ?? 0).toLocaleString('ar-SA') + ' ر.س';
        document.getElementById('available-spots').textContent     = data.available_spots     ?? 0;
    } catch {}
}

async function loadCharts() {
    try {
        const { success, data } = await fetch('/admin/charts').then(r => r.json());
        if (!success) return;

        const labels = Array.from({length: 7}, (_, i) => {
            const d = new Date();
            d.setDate(d.getDate() - (6 - i));
            return d.toLocaleDateString('ar-SA', {weekday: 'short'});
        });

        if (dailyChart) dailyChart.destroy();
        dailyChart = new Chart(document.getElementById('dailyChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'حجوزات',
                    data: data.daily_bookings ?? [],
                    backgroundColor: 'rgba(99,102,241,.2)',
                    borderColor: '#6366f1',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });

        if (topChart) topChart.destroy();
        const lots = data.top_parking_lots ?? [];
        topChart = new Chart(document.getElementById('topChart'), {
            type: 'doughnut',
            data: {
                labels: lots.map(l => l.name),
                datasets: [{
                    data: lots.map(l => l.value),
                    backgroundColor: ['#6366f1','#10b981','#f59e0b','#ef4444','#0ea5e9'],
                    borderWidth: 3,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12, font: { family: 'Cairo', size: 12 } } }
                },
                cutout: '65%',
            }
        });
    } catch {}
}

async function loadBookings() {
    const tbody = document.getElementById('bookings-body');
    try {
        const { success, data } = await fetch('/api/v1/bookings?per_page=8').then(r => r.json());
        if (!success || !data?.data?.length) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4" style="color:#94a3b8;">لا توجد حجوزات</td></tr>`;
            return;
        }
        tbody.innerHTML = data.data.map(b => {
            const badge = b.status === 'active'
                ? '<span class="badge badge-soft-success">نشط</span>'
                : '<span class="badge badge-soft-secondary">مكتمل</span>';
            return `<tr>
                <td><span class="fw-600">${b.parking_lot?.name ?? '--'}</span></td>
                <td>${b.customer_name ?? '--'}</td>
                <td style="direction:ltr;text-align:right;">${b.phone ?? '--'}</td>
                <td>${badge}</td>
                <td class="text-sm" style="color:#64748b;">${new Date(b.start_time).toLocaleString('ar-SA')}</td>
                <td class="text-sm" style="color:#64748b;">${new Date(b.end_time).toLocaleString('ar-SA')}</td>
            </tr>`;
        }).join('');
    } catch {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-3 text-sm" style="color:#94a3b8;">تعذّر تحميل البيانات</td></tr>`;
    }
    document.getElementById('last-updated').textContent = new Date().toLocaleTimeString('ar-SA');
}

loadStats();
loadCharts();
loadBookings();
setInterval(() => { loadStats(); loadCharts(); loadBookings(); }, 30000);
</script>
@endpush

@endsection
