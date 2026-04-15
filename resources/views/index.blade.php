<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>مواقف السيارات في دمشق</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
    <style>
        /* Bootstrap resets max-width:100% on all <img> which breaks Leaflet tiles */
        .leaflet-container img { max-width: none !important; box-shadow: none !important; }
        .leaflet-container     { direction: ltr; }
    </style>
</head>
<body style="background:#f1f5f9;font-family:'Cairo',sans-serif;">

    {{-- ══ HEADER ══════════════════════════════════════════════════════════════ --}}
    <header class="public-header">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:40px;height:40px;background:#6366f1;border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-p-square-fill text-white" style="font-size:1.25rem;"></i>
                    </div>
                    <div>
                        <div class="fw-800" style="color:#f8fafc;font-size:1.05rem;line-height:1.2;">دمشق باركينغ</div>
                        <div style="color:#94a3b8;font-size:.72rem;">مواقف السيارات في دمشق</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @include('partials.user-dropdown')
                </div>
            </div>
        </div>
    </header>

    {{-- ══ HERO SEARCH ═════════════════════════════════════════════════════════ --}}
    <div class="mob-hero-compact" style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);padding:2.5rem 0 3rem;">
        <div class="container text-center">
            <h1 class="fw-800 mb-2 d-none d-md-block" style="color:#f8fafc;font-size:clamp(1.4rem,4vw,2rem);">
                ابحث عن موقف سيارات في دمشق
            </h1>
            <p class="mb-4 d-none d-md-block" style="color:#94a3b8;font-size:.9rem;">
                تصفّح المواقف المتاحة، تحقق من الأسعار، واحجز مكانك
            </p>

            <div class="mx-auto" style="max-width:520px;">
                <div class="d-flex gap-2" style="background:#1e293b;padding:.5rem;border-radius:.75rem;">
                    <input type="text" id="searchInput"
                           class="form-control"
                           style="background:transparent;border:none;color:#f8fafc;font-family:'Cairo',sans-serif;box-shadow:none;"
                           placeholder="اكتب اسم الموقف أو المنطقة...">
                    <button id="searchBtn"
                            class="btn fw-600 flex-shrink-0"
                            style="background:#6366f1;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.5rem 1.25rem;">
                        <i class="bi bi-search me-1"></i>بحث
                    </button>
                </div>
            </div>

            <div id="searchStatus" class="mt-3 d-none">
                <span class="badge" style="background:rgba(255,255,255,.1);color:#cbd5e1;padding:.45em .9em;font-size:.8rem;">
                    نتائج البحث عن "<span id="searchTerm"></span>"
                </span>
            </div>
        </div>
    </div>

    {{-- ══ MAIN CONTENT ════════════════════════════════════════════════════════ --}}
    <div class="container py-4 mob-nav-pad">
        <div class="row g-3">

            {{-- Parking List (RTL: renders on right side, but we put it second so it's on LEFT visually) --}}
            {{-- Actually in RTL, order-1 = appears first = right side --}}
            {{-- List should be on LEFT (order-2 = left in RTL), Map on RIGHT (order-1 = right in RTL) --}}

            {{-- MAP  --}}
            <div class="col-lg-8 order-lg-1 mob-section-map">
                <div class="card h-100" style="min-height:520px;">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-700 text-sm" style="color:#0f172a;">
                            <i class="bi bi-map me-1" style="color:#6366f1;"></i>
                            خريطة المواقف
                        </span>
                        <span class="badge badge-soft-primary text-xs" id="map-count">--</span>
                    </div>
                    <div class="card-body p-0" style="border-radius:0 0 .75rem .75rem;overflow:hidden;">
                        <div id="map" style="height:500px;"></div>
                    </div>
                </div>
            </div>

            {{-- LIST --}}
            <div class="col-lg-4 order-lg-2 mob-section-list">
                <div class="card mob-sticky-desktop" style="position:sticky;top:1rem;">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-700 text-sm" style="color:#0f172a;">
                            <i class="bi bi-car-front me-1" style="color:#6366f1;"></i>
                            المواقف المتاحة
                        </span>
                        <span class="badge badge-soft-success text-xs" id="list-count">{{ $lots->count() }} موقف</span>
                    </div>
                    <div style="max-height:500px;overflow-y:auto;scrollbar-width:thin;" id="parkingList">
                        @forelse($lots as $lot)
                            @php
                                $avail = $lot['avail'];
                                $total = $lot['total'];
                                if ($avail === 0) {
                                    $avCls  = 'avail-full';
                                    $avText = 'ممتلئ';
                                } elseif ($avail < $total * 0.2) {
                                    $avCls  = 'avail-limited';
                                    $avText = $avail . ' محدود';
                                } else {
                                    $avCls  = 'avail-open';
                                    $avText = $avail . ' متاح';
                                }
                            @endphp
                            <div class="parking-card p-3 border-bottom" id="card-{{ $lot['id'] }}"
                                 onclick="showModal(lots.find(x=>x.id==={{ $lot['id'] }})); highlightCard({{ $lot['id'] }});">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                    <span class="fw-700" style="color:#0f172a;font-size:.9rem;">{{ $lot['name'] }}</span>
                                    <span class="badge {{ $avCls }} flex-shrink-0" style="font-size:.72rem;">{{ $avText }}</span>
                                </div>
                                <p class="text-xs mb-2" style="color:#94a3b8;">{{ $lot['address'] }}</p>
                                <div class="d-flex gap-3 text-xs" style="color:#64748b;">
                                    <span><i class="bi bi-car-front me-1"></i>{{ $total }}</span>
                                    <span><i class="bi bi-currency-exchange me-1"></i>{{ number_format($lot['price']) }} ر.س</span>
                                    <span><i class="bi bi-clock me-1"></i>{{ $lot['hours'] }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5" style="color:#94a3b8;">
                                <i class="bi bi-p-square d-block mb-2" style="font-size:1.8rem;opacity:.4;"></i>
                                <span class="text-sm">لا توجد مواقف متاحة</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ DETAILS MODAL ═══════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom:1px solid #f1f5f9;">
                    <h5 class="modal-title fw-800" id="modalName" style="font-size:1rem;color:#0f172a;"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">

                    {{-- Availability Bar --}}
                    <div class="mb-3 p-3 rounded-3" style="background:#f8fafc;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-xs fw-600" style="color:#64748b;">الإشغال</span>
                            <span class="text-xs fw-700" id="modalPct" style="color:#0f172a;">--%</span>
                        </div>
                        <div class="progress" style="height:8px;border-radius:4px;background:#e2e8f0;">
                            <div class="progress-bar" id="modalBar" role="progressbar"
                                 style="border-radius:4px;transition:width .5s ease;">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <div class="text-center">
                                <div class="fw-800" id="modalAvailable" style="color:#10b981;font-size:1.3rem;line-height:1;">--</div>
                                <div class="text-xs" style="color:#64748b;">متاح</div>
                            </div>
                            <div class="text-center">
                                <div class="fw-800" id="modalTotal" style="color:#475569;font-size:1.3rem;line-height:1;">--</div>
                                <div class="text-xs" style="color:#64748b;">إجمالي</div>
                            </div>
                        </div>
                    </div>

                    {{-- Info Grid --}}
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="p-3 rounded-3" style="background:#f0fdf4;">
                                <div class="text-xs fw-600 mb-1" style="color:#64748b;">السعر / ساعة</div>
                                <div class="fw-800" id="modalPrice" style="color:#059669;font-size:1rem;"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3" style="background:#f0f9ff;">
                                <div class="text-xs fw-600 mb-1" style="color:#64748b;">ساعات العمل</div>
                                <div class="fw-700" id="modalHours" style="color:#0369a1;font-size:.9rem;"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 rounded-3" style="background:#fafafa;">
                                <div class="text-xs fw-600 mb-1" style="color:#64748b;">
                                    <i class="bi bi-geo-alt me-1"></i>العنوان
                                </div>
                                <div class="text-sm" id="modalAddress" style="color:#475569;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid #f1f5f9;gap:.5rem;">
                    <button type="button" class="btn btn-sm fw-600"
                            style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
                            data-bs-dismiss="modal">إغلاق</button>
                    <button type="button" id="reserveBtn"
                            class="btn btn-sm fw-700"
                            style="background:#10b981;color:#fff;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;padding:.4rem 1.25rem;">
                        <i class="bi bi-calendar-check me-1"></i>احجز الآن
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MOBILE BOTTOM NAV ══════════════════════════════════════════════════ --}}
    <nav class="mobile-bottom-nav" aria-label="التنقل الرئيسي">
        <a href="#" class="mob-nav-item active" data-tab="list"
           onclick="switchMobSection('list'); return false;">
            <i class="bi bi-list-ul"></i>
            <span>المواقف</span>
        </a>
        <a href="#" class="mob-nav-item" data-tab="map"
           onclick="switchMobSection('map'); return false;">
            <i class="bi bi-map-fill"></i>
            <span>الخريطة</span>
        </a>
        <a href="#" class="mob-nav-item" data-tab="search"
           onclick="switchMobSection('list'); window.scrollTo({top:0,behavior:'smooth'}); setTimeout(()=>document.getElementById('searchInput').focus(),280); return false;">
            <i class="bi bi-search"></i>
            <span>بحث</span>
        </a>
        @auth
        <a href="{{ route('user.dashboard') }}" class="mob-nav-item">
            <div style="width:26px;height:26px;background:#6366f1;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.7rem;color:#fff;margin-bottom:1px;">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </div>
            <span>حسابي</span>
        </a>
        @else
        <a href="{{ route('login') }}" class="mob-nav-item">
            <i class="bi bi-person-fill"></i>
            <span>الدخول</span>
        </a>
        @endauth
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // ── Parking Lots (from database) ──────────────────────────────────────
        const lots = {{ Js::from($lots) }};

        // ── Helpers ───────────────────────────────────────────────────────────
        function availability(l) {
            if (l.avail === 0)           return { cls: 'avail-full',    text: 'ممتلئ',                  pct: 100 };
            if (l.avail < l.total * .2)  return { cls: 'avail-limited', text: `${l.avail} محدود`,       pct: Math.round((l.total - l.avail) / l.total * 100) };
            return                              { cls: 'avail-open',    text: `${l.avail} متاح`,        pct: Math.round((l.total - l.avail) / l.total * 100) };
        }
        const fmtPrice = p => new Intl.NumberFormat('ar-SY').format(p) + ' ر.س';

        // ── Map (lazy-init on mobile) ─────────────────────────────────────────
        let map = null, mapReady = false;

        function initMap() {
            if (mapReady) { map?.invalidateSize(); return; }
            mapReady = true;

            map = L.map('map').setView([33.5138, 36.2765], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            lots.forEach(l => {
                const av  = availability(l);
                const col = l.avail === 0 ? '#ef4444' : (l.avail < l.total * .2 ? '#f59e0b' : '#10b981');
                const icon = L.divIcon({
                    html: `<div style="width:36px;height:36px;background:${col};border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 8px rgba(0,0,0,.25);"></div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 36],
                    className: ''
                });
                const m = L.marker([l.lat, l.lng], { icon }).addTo(map);
                m.bindPopup(`
                    <div style="font-family:'Cairo',sans-serif;min-width:170px;padding:4px;">
                        <strong style="font-size:.95rem;color:#0f172a;">${l.name}</strong>
                        <p style="margin:4px 0;font-size:.78rem;color:#64748b;">${l.address}</p>
                        <span style="background:${col}22;color:${col};padding:2px 8px;border-radius:99px;font-size:.78rem;font-weight:700;">${av.text}</span>
                        <hr style="margin:6px 0;border-color:#f1f5f9;">
                        <span style="font-size:.78rem;color:#64748b;">💰 ${fmtPrice(l.price)} / ساعة</span>
                    </div>
                `);
                m.on('click', () => showModal(l));
            });

            if (lots.length) {
                const group = L.featureGroup(lots.map(l => L.marker([l.lat, l.lng])));
                map.fitBounds(group.getBounds().pad(.1));
            }
            document.getElementById('map-count').textContent = lots.length + ' موقف';
        }

        // ── Mobile Section Switch ─────────────────────────────────────────────
        function switchMobSection(name) {
            const mapEl  = document.querySelector('.mob-section-map');
            const listEl = document.querySelector('.mob-section-list');
            if (name === 'map') {
                listEl?.classList.add('mob-hidden');
                mapEl?.classList.remove('mob-hidden');
                setTimeout(initMap, 150);
            } else {
                mapEl?.classList.add('mob-hidden');
                listEl?.classList.remove('mob-hidden');
            }
            document.querySelectorAll('.mob-nav-item[data-tab]').forEach(b => {
                b.classList.toggle('active', b.dataset.tab === name);
            });
        }

        // ── List ──────────────────────────────────────────────────────────────
        let activeId = null;

        function renderList(data) {
            const el = document.getElementById('parkingList');
            document.getElementById('list-count').textContent = data.length + ' موقف';

            if (!data.length) {
                el.innerHTML = `
                    <div class="text-center py-5" style="color:#94a3b8;">
                        <i class="bi bi-search d-block mb-2" style="font-size:1.8rem;opacity:.4;"></i>
                        <span class="text-sm">لا توجد نتائج</span>
                    </div>`;
                return;
            }

            el.innerHTML = data.map(l => {
                const av = availability(l);
                return `
                <div class="parking-card p-3 border-bottom" id="card-${l.id}"
                     onclick="showModal(lots.find(x=>x.id===${l.id})); highlightCard(${l.id});">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                        <span class="fw-700" style="color:#0f172a;font-size:.9rem;">${l.name}</span>
                        <span class="badge ${av.cls} flex-shrink-0" style="font-size:.72rem;">${av.text}</span>
                    </div>
                    <p class="text-xs mb-2" style="color:#94a3b8;">${l.address}</p>
                    <div class="d-flex gap-3 text-xs" style="color:#64748b;">
                        <span><i class="bi bi-car-front me-1"></i>${l.total}</span>
                        <span><i class="bi bi-currency-exchange me-1"></i>${fmtPrice(l.price)}</span>
                        <span><i class="bi bi-clock me-1"></i>${l.hours}</span>
                    </div>
                </div>`;
            }).join('');
        }

        function highlightCard(id) {
            if (activeId) document.getElementById('card-' + activeId)?.classList.remove('active-card');
            activeId = id;
            document.getElementById('card-' + id)?.classList.add('active-card');
        }

        // ── Modal ─────────────────────────────────────────────────────────────
        // Bootstrap is deferred (loaded as ES module), so we lazy-init the
        // modal on first use rather than at script parse time.
        let modal = null;
        function getModal() {
            if (!modal) modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            return modal;
        }
        let current = null;

        function showModal(l) {
            current = l;
            const av  = availability(l);
            const pct = Math.round((l.total - l.avail) / l.total * 100);
            const barCol = l.avail === 0 ? '#ef4444' : (l.avail < l.total * .2 ? '#f59e0b' : '#10b981');

            document.getElementById('modalName').textContent      = l.name;
            document.getElementById('modalAvailable').textContent = l.avail;
            document.getElementById('modalTotal').textContent     = l.total;
            document.getElementById('modalPrice').textContent     = fmtPrice(l.price);
            document.getElementById('modalHours').textContent     = l.hours;
            document.getElementById('modalAddress').textContent   = l.address;
            document.getElementById('modalPct').textContent       = pct + '%';
            document.getElementById('modalBar').style.cssText     = `width:${pct}%;background:${barCol};border-radius:4px;`;

            const btn = document.getElementById('reserveBtn');
            if (l.avail > 0) {
                btn.disabled          = false;
                btn.style.background  = '#10b981';
                btn.innerHTML         = '<i class="bi bi-calendar-check me-1"></i>احجز الآن';
            } else {
                btn.disabled          = true;
                btn.style.background  = '#94a3b8';
                btn.innerHTML         = '<i class="bi bi-x-circle me-1"></i>ممتلئ';
            }

            if (map) map.setView([l.lat, l.lng], 16);
            getModal().show();
        }

        // ── Reserve ───────────────────────────────────────────────────────────
        document.getElementById('reserveBtn').addEventListener('click', () => {
            if (!current || current.avail <= 0) return;
            alert(`تم حجز مكان في ${current.name}!\nالعنوان: ${current.address}\nالسعر: ${fmtPrice(current.price)} / ساعة`);
            current.avail--;
            getModal().hide();
            renderList(lots);
        });

        // ── Search ────────────────────────────────────────────────────────────
        function doSearch() {
            const term = document.getElementById('searchInput').value.trim().toLowerCase();
            const res  = term
                ? lots.filter(l => l.name.includes(term) || l.address.includes(term))
                : lots;
            renderList(res);
            const status = document.getElementById('searchStatus');
            document.getElementById('searchTerm').textContent = term;
            status.classList.toggle('d-none', !term);
        }

        document.getElementById('searchBtn').addEventListener('click', doSearch);
        document.getElementById('searchInput').addEventListener('keypress', e => {
            if (e.key === 'Enter') doSearch();
        });

        // ── Init ──────────────────────────────────────────────────────────────
        document.getElementById('map-count').textContent = lots.length + ' موقف';
        if (window.innerWidth >= 768) {
            initMap(); // Desktop: initialize immediately
        } else {
            // Mobile: show list first, lazy-init map when map tab is tapped
            document.querySelector('.mob-section-map')?.classList.add('mob-hidden');
        }
    </script>
</body>
</html>
