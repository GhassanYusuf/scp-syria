@extends('layouts.admin')
@section('title', 'إدارة المواقف — دمشق باركينغ')
@section('page-title', 'إدارة المواقف')

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #lotsMap        { height: 380px; border-radius: .5rem; z-index: 0; }
    #modalMap       { height: 280px; border-radius: .5rem; z-index: 0; }
    .map-hint       { font-size: .78rem; color: #64748b; margin-top: .35rem; }
    /* keep Leaflet tiles crisp inside RTL layout */
    .leaflet-container { direction: ltr; }
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

{{-- ── Overview Map ─────────────────────────────────────────────────────────── --}}
<div class="card mb-4">
    <div class="card-header">
        <span class="fw-700 text-sm">
            <i class="bi bi-map me-1" style="color:#6366f1;"></i>
            خريطة المواقف
        </span>
    </div>
    <div class="p-2">
        <div id="lotsMap"></div>
    </div>
</div>

{{-- ── Table Card ───────────────────────────────────────────────────────────── --}}
<div class="card">

    {{-- Toolbar --}}
    <div class="card-header d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <span class="fw-700 text-sm">
            <i class="bi bi-list-ul me-1" style="color:#6366f1;"></i>
            قائمة المواقف
        </span>
        <div class="input-group" style="max-width:260px;">
            <input type="text" id="searchInput"
                   class="form-control form-control-sm"
                   style="border-color:#e2e8f0;border-inline-end:none;"
                   placeholder="بحث..." value="{{ request('search') }}">
            <button class="btn btn-sm" style="background:#6366f1;color:#fff;border:none;" onclick="doSearch()">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="app-table w-100">
            <thead>
                <tr>
                    <th>الموقف</th>
                    <th>العنوان</th>
                    <th class="text-center">السعة</th>
                    <th class="text-center">السعر / ساعة</th>
                    <th class="text-center">ساعات العمل</th>
                    <th class="text-center">الحالة</th>
                    <th class="text-center">نشط حالياً</th>
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parkingLots as $lot)
                <tr>
                    <td>
                        <div class="fw-600" style="color:#0f172a;">{{ $lot->name }}</div>
                        <div class="text-xs" style="color:#94a3b8;direction:ltr;text-align:right;">
                            {{ number_format($lot->latitude, 5) }}, {{ number_format($lot->longitude, 5) }}
                        </div>
                    </td>
                    <td>
                        <span class="text-sm" style="color:#475569;" title="{{ $lot->address }}">
                            {{ Str::limit($lot->address, 40) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-soft-info fw-600">{{ $lot->total_capacity }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-soft-success fw-600">
                            {{ number_format($lot->price_per_hour, 0) }} ر.س
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-soft-secondary text-xs">{{ $lot->working_hours }}</span>
                    </td>
                    <td class="text-center">
                        @if($lot->is_active)
                            <span class="badge badge-soft-success">
                                <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle;"></i>نشط
                            </span>
                        @else
                            <span class="badge badge-soft-danger">
                                <i class="bi bi-circle-fill me-1" style="font-size:.45rem;vertical-align:middle;"></i>معطل
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-soft-warning fw-600">{{ $lot->active_bookings_count ?? 0 }}</span>
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">
                            <button class="btn btn-sm"
                                    style="background:#f1f5f9;color:#475569;border:none;border-radius:.375rem;width:30px;height:30px;padding:0;"
                                    data-bs-toggle="modal" data-bs-target="#lotModal"
                                    onclick="editLot({{ $lot->id }})" title="تعديل">
                                <i class="bi bi-pencil" style="font-size:.8rem;"></i>
                            </button>
                            <button class="btn btn-sm"
                                    style="background:{{ $lot->is_active ? 'rgba(239,68,68,.1)' : 'rgba(16,185,129,.1)' }};color:{{ $lot->is_active ? '#dc2626' : '#059669' }};border:none;border-radius:.375rem;width:30px;height:30px;padding:0;"
                                    onclick="toggleStatus({{ $lot->id }})"
                                    title="{{ $lot->is_active ? 'تعطيل' : 'تفعيل' }}">
                                <i class="bi {{ $lot->is_active ? 'bi-slash-circle' : 'bi-check-circle' }}" style="font-size:.8rem;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-buildings d-block mb-3" style="font-size:2.5rem;color:#cbd5e1;"></i>
                        <p class="fw-600 mb-1" style="color:#475569;">لا توجد مواقف بعد</p>
                        <p class="text-sm mb-3" style="color:#94a3b8;">ابدأ بإضافة أول موقف سيارات</p>
                        <button class="btn btn-sm fw-600"
                                style="background:#6366f1;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
                                data-bs-toggle="modal" data-bs-target="#lotModal">
                            <i class="bi bi-plus-lg me-1"></i>إضافة موقف
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($parkingLots->hasPages())
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="text-xs" style="color:#64748b;">
            عرض {{ $parkingLots->firstItem() ?? 0 }}–{{ $parkingLots->lastItem() ?? 0 }}
            من {{ $parkingLots->total() }}
        </span>
        {{ $parkingLots->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif

</div>

{{-- ── Add / Edit Modal ────────────────────────────────────────────────────── --}}
<div class="modal fade" id="lotModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header" style="border-bottom:1px solid #f1f5f9;">
                <h5 class="modal-title fw-700" id="modalLabel" style="font-size:1rem;color:#0f172a;">
                    إضافة موقف جديد
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="lotForm">
                @csrf
                <input type="hidden" id="lotId">

                <div class="modal-body p-4">
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
                    <button type="submit" id="submitBtn"
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

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Parking lots data from server ─────────────────────────────────────────
const lotsData = @json($parkingLots->items());

// Damascus city centre fallback
const DAMASCUS = [33.5138, 36.2765];

// ── Overview map ──────────────────────────────────────────────────────────
const lotsMap = L.map('lotsMap').setView(DAMASCUS, 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
}).addTo(lotsMap);

const activeIcon = L.icon({
    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34]
});

lotsData.forEach(lot => {
    const lat = parseFloat(lot.latitude);
    const lng = parseFloat(lot.longitude);
    if (isNaN(lat) || isNaN(lng)) return;

    const statusBadge = lot.is_active
        ? '<span style="color:#059669;font-weight:700;">● نشط</span>'
        : '<span style="color:#dc2626;font-weight:700;">● معطل</span>';

    L.marker([lat, lng], { icon: activeIcon })
        .addTo(lotsMap)
        .bindPopup(`
            <div style="font-family:'Cairo',sans-serif;min-width:160px;direction:rtl;">
                <div style="font-weight:700;font-size:.92rem;margin-bottom:4px;">${lot.name}</div>
                <div style="font-size:.8rem;color:#475569;margin-bottom:4px;">${lot.address ?? ''}</div>
                <div style="font-size:.8rem;margin-bottom:6px;">${statusBadge}</div>
                <div style="font-size:.78rem;color:#64748b;">
                    السعة: ${lot.total_capacity} | ${lot.price_per_hour} ر.س/س
                </div>
            </div>
        `, { maxWidth: 220 });
});

// Fit map to markers if any exist
const validLots = lotsData.filter(l => !isNaN(parseFloat(l.latitude)) && !isNaN(parseFloat(l.longitude)));
if (validLots.length > 0) {
    const bounds = L.latLngBounds(validLots.map(l => [parseFloat(l.latitude), parseFloat(l.longitude)]));
    lotsMap.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
}

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
['f_lat', 'f_lng'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
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
const modal   = new bootstrap.Modal(lotModalEl, { backdrop: 'static' });

document.getElementById('addLotBtn').onclick = () => {
    editingId = null;
    document.getElementById('lotForm').reset();
    document.getElementById('modalLabel').textContent = 'إضافة موقف جديد';
    document.getElementById('submitText').textContent  = 'حفظ الموقف';
    modal.show();
};

async function editLot(id) {
    editingId = id;
    setBtnLoading(true);
    try {
        const { data } = await fetch(`/admin/parking-lots/${id}`).then(r => r.json());
        document.getElementById('modalLabel').textContent  = 'تعديل: ' + data.name;
        document.getElementById('submitText').textContent  = 'تحديث الموقف';
        document.getElementById('f_name').value     = data.name;
        document.getElementById('f_capacity').value = data.total_capacity;
        document.getElementById('f_price').value    = data.price_per_hour;
        document.getElementById('f_hours').value    = data.working_hours;
        document.getElementById('f_lat').value      = data.latitude;
        document.getElementById('f_lng').value      = data.longitude;
        document.getElementById('f_address').value  = data.address;
        modal.show();
    } catch { alert('خطأ في تحميل البيانات'); }
    finally { setBtnLoading(false); }
}

document.getElementById('lotForm').onsubmit = async (e) => {
    e.preventDefault();
    setBtnLoading(true);
    try {
        const res    = await fetch(editingId ? `/admin/parking-lots/${editingId}` : '/admin/parking-lots', {
            method: editingId ? 'PUT' : 'POST',
            body: new FormData(e.target)
        });
        const result = await res.json();
        result.success ? location.reload() : alert(result.message || 'خطأ في العملية');
    } catch { alert('خطأ في الاتصال'); }
    finally { setBtnLoading(false); }
};

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
</script>
@endpush

@endsection
