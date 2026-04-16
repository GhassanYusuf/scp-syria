@extends('layouts.admin')
@section('title', 'إدارة المشغّلين — دمشق باركينغ')
@section('page-title', 'إدارة المشغّلين')

@section('styles')
<style>
    .op-table th { font-size:.8rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; }
    .op-table td { vertical-align:middle; font-size:.875rem; }
    .op-avatar {
        width:38px; height:38px; border-radius:50%;
        background:rgba(99,102,241,.12); color:#6366f1;
        display:flex; align-items:center; justify-content:center;
        font-weight:800; font-size:.9rem; flex-shrink:0;
    }
    .lot-pill {
        display:inline-flex; align-items:center; gap:.35rem;
        padding:.25em .75em; border-radius:20px; font-size:.75rem; font-weight:700;
        background:rgba(16,185,129,.1); color:#059669;
    }
    .no-lot-pill {
        display:inline-flex; align-items:center; gap:.35rem;
        padding:.25em .75em; border-radius:20px; font-size:.75rem; font-weight:600;
        background:#f1f5f9; color:#94a3b8;
    }
</style>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <p class="mb-0" style="color:#64748b;font-size:.875rem;">إنشاء حسابات المشغّلين وتخصيص مواقف لهم</p>
    </div>
    <button id="btnOpenCreate" class="btn btn-primary fw-700" style="font-family:'Cairo',sans-serif;border-radius:.625rem;">
        <i class="bi bi-person-plus me-1"></i>إضافة مشغّل
    </button>
</div>

{{-- Operators table --}}
<div class="card border-0 shadow-sm" style="border-radius:1rem;overflow:hidden;">
    <div class="table-responsive">
        <table class="table op-table mb-0">
            <thead style="background:#f8fafc;">
                <tr>
                    <th class="px-4 py-3">المشغّل</th>
                    <th class="py-3">البريد الإلكتروني</th>
                    <th class="py-3">الهاتف</th>
                    <th class="py-3">الموقف المخصص</th>
                    <th class="py-3 text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($operators as $op)
                <tr>
                    <td class="px-4 py-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="op-avatar">{{ mb_substr($op->name, 0, 1) }}</div>
                            <span class="fw-600" style="color:#0f172a;">{{ $op->name }}</span>
                        </div>
                    </td>
                    <td class="py-3" style="color:#475569;">{{ $op->email }}</td>
                    <td class="py-3" style="color:#475569;">{{ $op->phone ?? '—' }}</td>
                    <td class="py-3">
                        @if($op->assignedLot)
                            <span class="lot-pill">
                                <i class="bi bi-buildings"></i>
                                {{ $op->assignedLot->name }}
                            </span>
                        @else
                            <span class="no-lot-pill">
                                <i class="bi bi-dash-circle"></i>
                                غير مخصص
                            </span>
                        @endif
                    </td>
                    <td class="py-3 text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <button class="btn btn-sm fw-600 btn-edit-op"
                                    style="background:#f1f5f9;color:#475569;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
                                    data-id="{{ $op->id }}"
                                    data-name="{{ $op->name }}"
                                    data-email="{{ $op->email }}"
                                    data-phone="{{ $op->phone ?? '' }}"
                                    data-lot="{{ $op->parking_lot_id ?? '' }}">
                                <i class="bi bi-pencil me-1"></i>تعديل
                            </button>
                            <button class="btn btn-sm fw-600 btn-delete-op"
                                    style="background:rgba(239,68,68,.1);color:#ef4444;border:none;border-radius:.5rem;font-family:'Cairo',sans-serif;"
                                    data-id="{{ $op->id }}"
                                    data-name="{{ $op->name }}">
                                <i class="bi bi-trash me-1"></i>حذف
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5" style="color:#94a3b8;">
                        <i class="bi bi-people d-block mb-2" style="font-size:2.5rem;opacity:.3;"></i>
                        <span>لا يوجد مشغّلون بعد. أضف أول مشغّل الآن.</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ══ CREATE MODAL ══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800" style="color:#0f172a;">إضافة مشغّل جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <div id="createError" class="alert alert-danger d-none border-0 rounded-3 py-2 mb-3" role="alert"></div>

                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:.875rem;color:#374151;">الاسم الكامل</label>
                    <input type="text" id="createName" class="form-control" placeholder="مثال: أحمد محمد">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:.875rem;color:#374151;">البريد الإلكتروني</label>
                    <input type="email" id="createEmail" class="form-control" placeholder="operator@example.com" dir="ltr">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:.875rem;color:#374151;">رقم الهاتف <span style="color:#94a3b8;font-weight:400;">(اختياري)</span></label>
                    <input type="text" id="createPhone" class="form-control" placeholder="09xxxxxxxx">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:.875rem;color:#374151;">كلمة المرور</label>
                    <input type="password" id="createPassword" class="form-control" placeholder="8 أحرف على الأقل" dir="ltr">
                </div>
                <div class="mb-1">
                    <label class="form-label fw-600" style="font-size:.875rem;color:#374151;">الموقف المخصص <span style="color:#94a3b8;font-weight:400;">(اختياري)</span></label>
                    <select id="createLot" class="form-select">
                        <option value="">— بدون تخصيص —</option>
                        @foreach($lots as $lot)
                        <option value="{{ $lot->id }}">{{ $lot->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 pt-1">
                <button type="button" class="btn fw-600"
                        style="background:#f1f5f9;color:#475569;border:none;border-radius:.625rem;font-family:'Cairo',sans-serif;"
                        data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary fw-700" id="createBtn"
                        style="border-radius:.625rem;font-family:'Cairo',sans-serif;">
                    <span id="createSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                    إنشاء الحساب
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ EDIT MODAL ════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800" style="color:#0f172a;">تعديل بيانات المشغّل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <div id="editError" class="alert alert-danger d-none border-0 rounded-3 py-2 mb-3" role="alert"></div>
                <input type="hidden" id="editId">

                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:.875rem;color:#374151;">الاسم الكامل</label>
                    <input type="text" id="editName" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:.875rem;color:#374151;">البريد الإلكتروني</label>
                    <input type="email" id="editEmail" class="form-control" dir="ltr">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:.875rem;color:#374151;">رقم الهاتف <span style="color:#94a3b8;font-weight:400;">(اختياري)</span></label>
                    <input type="text" id="editPhone" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600" style="font-size:.875rem;color:#374151;">كلمة مرور جديدة <span style="color:#94a3b8;font-weight:400;">(اتركها فارغة للإبقاء على الحالية)</span></label>
                    <input type="password" id="editPassword" class="form-control" placeholder="8 أحرف على الأقل" dir="ltr">
                </div>
                <div class="mb-1">
                    <label class="form-label fw-600" style="font-size:.875rem;color:#374151;">الموقف المخصص</label>
                    <select id="editLot" class="form-select">
                        <option value="">— بدون تخصيص —</option>
                        @foreach($lots as $lot)
                        <option value="{{ $lot->id }}">{{ $lot->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 pt-1">
                <button type="button" class="btn fw-600"
                        style="background:#f1f5f9;color:#475569;border:none;border-radius:.625rem;font-family:'Cairo',sans-serif;"
                        data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary fw-700" id="editBtn"
                        style="border-radius:.625rem;font-family:'Cairo',sans-serif;">
                    <span id="editSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                    حفظ التغييرات
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══ DELETE MODAL ══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0" style="border-radius:1rem;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800" style="color:#0f172a;">حذف المشغّل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-3">
                <div style="width:56px;height:56px;background:rgba(239,68,68,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                    <i class="bi bi-person-x-fill" style="font-size:1.6rem;color:#ef4444;"></i>
                </div>
                <p class="mb-1" style="color:#374151;font-size:.9rem;">هل تريد حذف حساب</p>
                <p class="fw-800 mb-0" style="color:#0f172a;" id="deleteOpName">—</p>
                <p class="mt-2 mb-0" style="color:#94a3b8;font-size:.8rem;">لا يمكن التراجع عن هذا الإجراء.</p>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                <button type="button" class="btn fw-600"
                        style="background:#f1f5f9;color:#475569;border:none;border-radius:.625rem;font-family:'Cairo',sans-serif;"
                        data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn fw-700" id="deleteBtn"
                        style="background:#ef4444;color:#fff;border:none;border-radius:.625rem;font-family:'Cairo',sans-serif;">
                    <span id="deleteSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                    حذف
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="opToast" class="toast align-items-center border-0 position-fixed bottom-0 end-0 m-3"
     style="z-index:9999;min-width:260px;" role="alert">
    <div class="d-flex">
        <div class="toast-body fw-600" id="opToastMsg"></div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    // ── Toast ─────────────────────────────────────────────────────────────────
    function showToast(msg, success) {
        const el = document.getElementById('opToast');
        document.getElementById('opToastMsg').textContent = msg;
        el.classList.remove('text-bg-success', 'text-bg-danger');
        el.classList.add(success !== false ? 'text-bg-success' : 'text-bg-danger');
        bootstrap.Toast.getOrCreateInstance(el, { delay: 3500 }).show();
    }

    // ── Modals (lazy) ─────────────────────────────────────────────────────────
    function modal(id) { return bootstrap.Modal.getOrCreateInstance(document.getElementById(id)); }

    // ── Open create ───────────────────────────────────────────────────────────
    document.getElementById('btnOpenCreate').addEventListener('click', function () {
        ['createName','createEmail','createPhone','createPassword'].forEach(id => document.getElementById(id).value = '');
        document.getElementById('createLot').value = '';
        document.getElementById('createError').classList.add('d-none');
        modal('createModal').show();
    });

    // ── Open edit ─────────────────────────────────────────────────────────────
    document.querySelectorAll('.btn-edit-op').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('editId').value       = this.dataset.id;
            document.getElementById('editName').value     = this.dataset.name;
            document.getElementById('editEmail').value    = this.dataset.email;
            document.getElementById('editPhone').value    = this.dataset.phone || '';
            document.getElementById('editPassword').value = '';
            document.getElementById('editLot').value      = this.dataset.lot || '';
            document.getElementById('editError').classList.add('d-none');
            modal('editModal').show();
        });
    });

    // ── Open delete ───────────────────────────────────────────────────────────
    let pendingDeleteId = null;
    document.querySelectorAll('.btn-delete-op').forEach(btn => {
        btn.addEventListener('click', function () {
            pendingDeleteId = this.dataset.id;
            document.getElementById('deleteOpName').textContent = this.dataset.name;
            modal('deleteModal').show();
        });
    });

    // ── Submit create ─────────────────────────────────────────────────────────
    document.getElementById('createBtn').addEventListener('click', async function () {
        const btn     = this;
        const spinner = document.getElementById('createSpinner');
        const errEl   = document.getElementById('createError');
        btn.disabled  = true;
        spinner.classList.remove('d-none');
        errEl.classList.add('d-none');
        try {
            const res  = await fetch('{{ route("admin.operators.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    name:           document.getElementById('createName').value.trim(),
                    email:          document.getElementById('createEmail').value.trim(),
                    phone:          document.getElementById('createPhone').value.trim() || null,
                    password:       document.getElementById('createPassword').value,
                    parking_lot_id: document.getElementById('createLot').value || null,
                }),
            });
            const json = await res.json();
            if (json.success) {
                modal('createModal').hide();
                showToast(json.message);
                setTimeout(() => location.reload(), 800);
            } else {
                errEl.textContent = json.message || 'حدث خطأ.';
                errEl.classList.remove('d-none');
            }
        } catch { errEl.textContent = 'تعذّر الاتصال بالخادم.'; errEl.classList.remove('d-none'); }
        finally  { btn.disabled = false; spinner.classList.add('d-none'); }
    });

    // ── Submit edit ───────────────────────────────────────────────────────────
    document.getElementById('editBtn').addEventListener('click', async function () {
        const id      = document.getElementById('editId').value;
        const btn     = this;
        const spinner = document.getElementById('editSpinner');
        const errEl   = document.getElementById('editError');
        btn.disabled  = true;
        spinner.classList.remove('d-none');
        errEl.classList.add('d-none');
        try {
            const res  = await fetch(`/admin/operators/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({
                    name:           document.getElementById('editName').value.trim(),
                    email:          document.getElementById('editEmail').value.trim(),
                    phone:          document.getElementById('editPhone').value.trim() || null,
                    password:       document.getElementById('editPassword').value || null,
                    parking_lot_id: document.getElementById('editLot').value || null,
                }),
            });
            const json = await res.json();
            if (json.success) {
                modal('editModal').hide();
                showToast(json.message);
                setTimeout(() => location.reload(), 800);
            } else {
                errEl.textContent = json.message || 'حدث خطأ.';
                errEl.classList.remove('d-none');
            }
        } catch { errEl.textContent = 'تعذّر الاتصال بالخادم.'; errEl.classList.remove('d-none'); }
        finally  { btn.disabled = false; spinner.classList.add('d-none'); }
    });

    // ── Submit delete ─────────────────────────────────────────────────────────
    document.getElementById('deleteBtn').addEventListener('click', async function () {
        if (!pendingDeleteId) return;
        const btn     = this;
        const spinner = document.getElementById('deleteSpinner');
        btn.disabled  = true;
        spinner.classList.remove('d-none');
        try {
            const res  = await fetch(`/admin/operators/${pendingDeleteId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf },
            });
            const json = await res.json();
            modal('deleteModal').hide();
            showToast(json.message, json.success);
            if (json.success) setTimeout(() => location.reload(), 800);
        } catch { showToast('تعذّر الاتصال بالخادم.', false); }
        finally  { btn.disabled = false; spinner.classList.add('d-none'); }
    });

}); // DOMContentLoaded
</script>
@endpush
