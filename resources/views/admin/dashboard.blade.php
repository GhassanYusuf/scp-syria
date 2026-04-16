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
<div class="row g-3 mb-4" style="align-items:stretch;">
    <div class="col-lg-7 d-flex flex-column">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header d-flex align-items-center justify-content-between flex-shrink-0">
                <span class="fw-700" style="font-size:.9rem;">
                    <i class="bi bi-bar-chart-line me-2" style="color:#6366f1;"></i>
                    الحجوزات اليومية — آخر 7 أيام
                </span>
                <span class="text-xs" style="color:#94a3b8;">اضغط على أي عمود للتفاصيل</span>
            </div>
            <div class="card-body p-3 flex-grow-1 d-flex flex-column" style="min-height:0;">
                <div style="position:relative;flex:1;min-height:0;">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5 d-flex flex-column">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header d-flex align-items-center justify-content-between flex-shrink-0">
                <span class="fw-700" style="font-size:.9rem;">
                    <i class="bi bi-trophy me-2" style="color:#f59e0b;"></i>
                    أفضل 5 مواقف
                </span>
                <span class="text-xs" style="color:#94a3b8;">اضغط على أي شريحة للتفاصيل</span>
            </div>
            <div class="card-body p-3 d-flex align-items-center justify-content-center flex-grow-1">
                <canvas id="topChart" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── Chart Segment Modal ──────────────────────────────────────────────────── --}}
<div class="modal fade" id="segmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:640px;">
        <div class="modal-content" style="border:none;border-radius:1rem;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            {{-- Header --}}
            <div id="segmentModalTop" style="padding:1.25rem 1.5rem;background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                <div class="d-flex align-items-center gap-3">
                    <div id="segmentModalIcon" style="width:44px;height:44px;border-radius:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i id="segmentModalIconEl" style="font-size:1.3rem;"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div id="segmentModalTitle" class="fw-800" style="font-size:1rem;color:#0f172a;line-height:1.3;"></div>
                        <div id="segmentModalSub" class="text-xs mt-1" style="color:#64748b;"></div>
                    </div>
                    <button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="modal"></button>
                </div>
            </div>
            {{-- Summary stats --}}
            <div id="segmentModalStats" class="px-4 pt-3 pb-2 d-flex gap-3 flex-wrap"></div>
            {{-- Scrollable booking table --}}
            <div class="px-4 pb-3">
                <div id="segmentModalTableWrap" style="max-height:320px;overflow-y:auto;border-radius:.5rem;border:1px solid #f1f5f9;">
                    <table class="app-table w-100 mb-0" id="segmentModalTable">
                        <thead style="position:sticky;top:0;z-index:1;">
                            <tr>
                                <th>#</th>
                                <th>العميل</th>
                                <th>الهاتف</th>
                                <th>الحالة</th>
                                <th>البدء</th>
                                <th>الانتهاء</th>
                            </tr>
                        </thead>
                        <tbody id="segmentModalTbody">
                            <tr><td colspan="6" class="text-center py-4" style="color:#94a3b8;">
                                <div class="spinner-border spinner-border-sm me-2"></div>جاري التحميل...
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f1f5f9;padding:.75rem 1.25rem;">
                <button type="button" class="btn btn-sm fw-600 w-100"
                        style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
                        data-bs-dismiss="modal">إغلاق</button>
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
let chartData = {};
let segmentModal = null;

function getSegmentModal() {
    if (!segmentModal) segmentModal = new bootstrap.Modal(document.getElementById('segmentModal'));
    return segmentModal;
}

// ── Segment modal ─────────────────────────────────────────────────────────────
function openSegmentModal({ title, sub, icon, iconBg, iconColor, statChips }) {
    document.getElementById('segmentModalTitle').textContent  = title;
    document.getElementById('segmentModalSub').textContent    = sub;
    document.getElementById('segmentModalIconEl').className   = `bi ${icon}`;
    document.getElementById('segmentModalIconEl').style.color = iconColor;
    document.getElementById('segmentModalIcon').style.background = iconBg;

    // Stat chips row
    const chips = (statChips || []).map(c =>
        `<div class="px-3 py-2 rounded-3 text-center" style="background:${c.bg};flex:1;min-width:100px;">
            <div class="fw-800" style="font-size:1.1rem;color:${c.color};">${c.value}</div>
            <div class="text-xs" style="color:#64748b;">${c.label}</div>
        </div>`
    ).join('');
    document.getElementById('segmentModalStats').innerHTML = chips;

    // Reset table to loading
    document.getElementById('segmentModalTbody').innerHTML =
        `<tr><td colspan="6" class="text-center py-4" style="color:#94a3b8;">
            <div class="spinner-border spinner-border-sm me-2"></div>جاري التحميل...
        </td></tr>`;

    getSegmentModal().show();
}

function renderBookingTable(bookings) {
    const tbody = document.getElementById('segmentModalTbody');
    if (!bookings.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4" style="color:#94a3b8;">لا توجد حجوزات</td></tr>`;
        return;
    }
    const statusBadge = s => s === 'active'
        ? '<span class="badge badge-soft-warning">نشط</span>'
        : s === 'completed'
            ? '<span class="badge badge-soft-success">مكتمل</span>'
            : '<span class="badge badge-soft-danger">ملغي</span>';
    const fmt = iso => new Date(iso).toLocaleString('ar-SA', {month:'short', day:'numeric', hour:'2-digit', minute:'2-digit'});

    tbody.innerHTML = bookings.map((b, i) => `
        <tr>
            <td class="text-xs" style="color:#94a3b8;">${i + 1}</td>
            <td><span class="fw-600" style="font-size:.85rem;">${b.customer_name ?? '--'}</span></td>
            <td style="direction:ltr;text-align:right;font-size:.82rem;color:#475569;">${b.phone ?? '--'}</td>
            <td>${statusBadge(b.status)}</td>
            <td class="text-xs" style="color:#64748b;">${fmt(b.start_time)}</td>
            <td class="text-xs" style="color:#64748b;">${fmt(b.end_time)}</td>
        </tr>`).join('');
}

// ── Stats ─────────────────────────────────────────────────────────────────────
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

// ── Charts ────────────────────────────────────────────────────────────────────
async function loadCharts() {
    try {
        const { success, data } = await fetch('/admin/charts').then(r => r.json());
        if (!success) return;
        chartData = data;

        const dayLabels = Array.from({length: 7}, (_, i) => {
            const d = new Date();
            d.setDate(d.getDate() - (6 - i));
            return d.toLocaleDateString('ar-SA', {weekday: 'short', month: 'short', day: 'numeric'});
        });
        const dayLabelsShort = Array.from({length: 7}, (_, i) => {
            const d = new Date();
            d.setDate(d.getDate() - (6 - i));
            return d.toLocaleDateString('ar-SA', {weekday: 'short'});
        });

        // ── Bar chart ──────────────────────────────────────────────────────────
        if (dailyChart) dailyChart.destroy();
        dailyChart = new Chart(document.getElementById('dailyChart'), {
            type: 'bar',
            data: {
                labels: dayLabelsShort,
                datasets: [{
                    label: 'حجوزات',
                    data: data.daily_bookings ?? [],
                    backgroundColor: 'rgba(99,102,241,.2)',
                    borderColor: '#6366f1',
                    borderWidth: 2,
                    borderRadius: 6,
                    hoverBackgroundColor: 'rgba(99,102,241,.45)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: {
                        title: ctx => dayLabels[ctx[0].dataIndex],
                        label: ctx => ` ${ctx.parsed.y} حجز`,
                    }}
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                },
                onClick(e, elements) {
                    if (!elements.length) return;
                    const i     = elements[0].index;
                    const count = (data.daily_bookings ?? [])[i] ?? 0;
                    const label = dayLabels[i];
                    const date  = (data.daily_dates ?? [])[i];
                    const weekTotal = (data.daily_bookings ?? []).reduce((a, b) => a + b, 0);
                    const pct   = weekTotal ? Math.round(count / weekTotal * 100) : 0;

                    openSegmentModal({
                        title: label,
                        sub:   'قائمة الحجوزات المسجّلة في هذا اليوم',
                        icon:  'bi-calendar3',
                        iconBg:    'rgba(99,102,241,.12)',
                        iconColor: '#6366f1',
                        statChips: [
                            { label: 'الحجوزات', value: count, color: '#6366f1', bg: 'rgba(99,102,241,.08)' },
                            { label: 'من الأسبوع', value: pct + '%', color: '#0ea5e9', bg: 'rgba(14,165,233,.08)' },
                        ],
                    });

                    if (date) {
                        fetch(`/api/v1/bookings?date=${date}&per_page=100`)
                            .then(r => r.json())
                            .then(({ data: d }) => renderBookingTable(d?.data ?? []))
                            .catch(() => renderBookingTable([]));
                    }
                }
            }
        });

        // ── Doughnut chart ─────────────────────────────────────────────────────
        if (topChart) topChart.destroy();
        const lots   = data.top_parking_lots ?? [];
        const colors = ['#6366f1','#10b981','#f59e0b','#ef4444','#0ea5e9'];
        const total  = lots.reduce((s, l) => s + l.value, 0);

        topChart = new Chart(document.getElementById('topChart'), {
            type: 'doughnut',
            data: {
                labels: lots.map(l => l.name),
                datasets: [{
                    data: lots.map(l => l.value),
                    backgroundColor: colors,
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverBorderColor: '#fff',
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12, font: { family: 'Cairo', size: 12 } } },
                    tooltip: { callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed} حجز (${total ? Math.round(ctx.parsed/total*100) : 0}%)`,
                    }}
                },
                cutout: '65%',
                onClick(e, elements) {
                    if (!elements.length) return;
                    const i   = elements[0].index;
                    const lot = lots[i];
                    if (!lot) return;
                    const pct = total ? Math.round(lot.value / total * 100) : 0;

                    openSegmentModal({
                        title: lot.name,
                        sub:   'قائمة جميع حجوزات هذا الموقف',
                        icon:  'bi-buildings',
                        iconBg:    colors[i] + '22',
                        iconColor: colors[i],
                        statChips: [
                            { label: 'الحجوزات', value: lot.value, color: colors[i], bg: colors[i] + '14' },
                            { label: 'من الكل',  value: pct + '%', color: '#0ea5e9', bg: 'rgba(14,165,233,.08)' },
                            { label: 'الترتيب',  value: '#' + (i + 1), color: '#f59e0b', bg: 'rgba(245,158,11,.08)' },
                        ],
                    });

                    fetch(`/api/v1/bookings?parking_lot_id=${lot.id}&per_page=100`)
                        .then(r => r.json())
                        .then(({ data: d }) => renderBookingTable(d?.data ?? []))
                        .catch(() => renderBookingTable([]));
                }
            }
        });
    } catch {}
}

// ── Bookings ──────────────────────────────────────────────────────────────────
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
