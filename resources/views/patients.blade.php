<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المرضى - مجمع نبض الطبي</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
            background: #f3f5f9;
            color: #1f2937;
            min-height: 100vh;
        }

        .page { max-width: 1500px; margin: 0 auto; padding: 24px; }

        /* ===== Page Header ===== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .title-section { text-align: right; }
        .title-section h1 { font-size: 28px; font-weight: 700; color: #111827; }
        .title-section p { font-size: 14px; color: #6b7280; margin-top: 6px; }

        .actions { display: flex; gap: 12px; align-items: center; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-primary {
            background: #1d8eff;
            color: white;
            border-color: #1d8eff;
        }
        .btn-primary:hover { background: #0f7ce0; border-color: #0f7ce0; }

        .btn-outline {
            background: white;
            color: #1f2937;
            border-color: #e5e7eb;
        }
        .btn-outline:hover { background: #f9fafb; }

        .btn svg { width: 16px; height: 16px; }

        /* ===== Filter Card ===== */
        .filter-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            margin-bottom: 20px;
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-btn:hover { background: #f9fafb; border-color: #d1d5db; }
        .filter-btn svg { width: 16px; height: 16px; }

        .search-input {
            flex: 1;
            min-width: 220px;
            padding: 11px 16px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            color: #1f2937;
            text-align: right;
            direction: rtl;
        }
        .search-input::placeholder { color: #9ca3af; }
        .search-input:focus {
            outline: none;
            border-color: #1d8eff;
            background: white;
        }
        .search-input.large { flex: 2; }

        /* ===== Table Card ===== */
        .table-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .table-wrapper { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            background: #f8fafc;
            padding: 14px 16px;
            text-align: right;
            color: #6b7280;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
            border-bottom: 1px solid #e5e7eb;
        }
        thead th .sort-icon {
            display: inline-block;
            margin-right: 6px;
            color: #9ca3af;
            font-size: 11px;
        }

        tbody td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
            color: #1f2937;
            vertical-align: middle;
        }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #fafbfc; }

        .col-num { color: #9ca3af; font-weight: 500; width: 50px; }

        /* Patient cell with avatar */
        .patient-cell { display: flex; align-items: center; gap: 12px; min-width: 200px; }
        .avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #1d8eff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }
        .patient-name { font-weight: 500; color: #111827; }

        /* Gender badge */
        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-male { background: #dbeafe; color: #1d4ed8; }
        .badge-female { background: #f3e8ff; color: #6b21a8; }

        /* Age cell */
        .age-cell { color: #4b5563; }
        .age-cell .unit { color: #9ca3af; font-size: 13px; margin-right: 4px; }

        /* Phone cell */
        .phone-cell { color: #4b5563; }
        .phone-cell.empty { color: #9ca3af; }

        /* Toggle switch */
        .toggle {
            position: relative;
            display: inline-block;
            width: 36px;
            height: 20px;
        }
        .toggle input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #e5e7eb;
            border-radius: 999px;
            transition: 0.2s;
        }
        .toggle-slider::before {
            content: "";
            position: absolute;
            height: 16px; width: 16px;
            right: 2px; top: 2px;
            background: white;
            border-radius: 50%;
            transition: 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .toggle input:checked + .toggle-slider { background: #1d8eff; }
        .toggle input:checked + .toggle-slider::before { transform: translateX(-16px); }

        /* Date cell */
        .date-cell { color: #4b5563; font-variant-numeric: tabular-nums; }

        /* Actions */
        .actions-cell { white-space: nowrap; }
        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s;
        }
        .action-btn:hover { background: #f3f4f6; }
        .action-btn svg { width: 16px; height: 16px; }
        .action-view { color: #1d8eff; }
        .action-edit { color: #1d8eff; }
        .action-delete { color: #ef4444; }
        .action-more { color: #6b7280; }

        /* ===== Stats Cards ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .stat-icon svg { width: 24px; height: 24px; }
        .stat-icon.blue { background: #eff6ff; color: #1d8eff; }
        .stat-icon.green { background: #ecfdf5; color: #10b981; }
        .stat-icon.orange { background: #fffbeb; color: #f59e0b; }
        .stat-icon.purple { background: #f5f3ff; color: #8b5cf6; }

        .stat-content { display: flex; flex-direction: column; }
        .stat-number { font-size: 24px; font-weight: 700; color: #111827; line-height: 1.2; }
        .stat-label { font-size: 13px; color: #6b7280; margin-top: 4px; }

        /* ===== Pagination ===== */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-top: 1px solid #f1f5f9;
            flex-wrap: wrap;
            gap: 16px;
        }
        .page-info { color: #6b7280; font-size: 13px; }
        .page-controls { display: flex; align-items: center; gap: 12px; color: #6b7280; font-size: 13px; }
        .page-size-select {
            padding: 6px 28px 6px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            background: white;
            color: #1f2937;
            cursor: pointer;
        }

        .page-nav { display: flex; align-items: center; gap: 8px; }
        .page-text { color: #6b7280; font-size: 13px; margin-left: 8px; }
        .page-btn {
            width: 32px; height: 32px;
            border: 1px solid #e5e7eb;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            transition: all 0.15s;
        }
        .page-btn:hover:not(:disabled) { background: #f9fafb; border-color: #d1d5db; }
        .page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .page-btn svg { width: 14px; height: 14px; }

        /* ===== Loading & Empty ===== */
        .loading, .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        .empty-state .icon { font-size: 48px; margin-bottom: 12px; opacity: 0.5; }

        /* ===== Modal (kept for future integration) ===== */
        .modal {
            display: none;
            position: fixed; inset: 0;
            background: rgba(15, 23, 42, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.open { display: flex; }
        .modal-content {
            background: white;
            padding: 28px;
            border-radius: 16px;
            max-width: 600px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-header h2 { font-size: 20px; font-weight: 700; }
        .modal-close {
            background: none; border: none; font-size: 24px;
            cursor: pointer; color: #9ca3af;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .form-group { display: flex; flex-direction: column; }
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-group input, .form-group select {
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none; border-color: #1d8eff;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .page-header { flex-direction: column-reverse; }
            .actions { width: 100%; }
            .actions .btn { flex: 1; justify-content: center; }
            .filter-card { flex-direction: column; align-items: stretch; }
            .search-input { min-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="page">

        {{-- ===== Page Header ===== --}}
        <div class="page-header">
            <div class="actions">
                <button class="btn btn-primary" onclick="openAddModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    إضافة مريض جديد
                </button>
                <button class="btn btn-outline" onclick="exportPatients()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    تصدير
                </button>
            </div>
            <div class="title-section">
                <h1>إدارة المرضى</h1>
                <p>إدارة معلومات المرضى وسجلاتهم الطبية</p>
            </div>
        </div>

        {{-- ===== Stats Cards ===== --}}
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="stat-content">
                    <span class="stat-number" id="statTotal">-</span>
                    <span class="stat-label">إجمالي المرضى</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div class="stat-content">
                    <span class="stat-number" id="statActive">-</span>
                    <span class="stat-label">مرضى نشطون</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="stat-content">
                    <span class="stat-number" id="statToday">-</span>
                    <span class="stat-label">مرضى اليوم</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div class="stat-content">
                    <span class="stat-number" id="statVisits">-</span>
                    <span class="stat-label">إجمالي الزيارات</span>
                </div>
            </div>
        </div>

        {{-- ===== Filter Bar ===== --}}
        <div class="filter-card">
            <button class="filter-btn" onclick="openFilters()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                تصفية
            </button>
            <input type="text" id="searchByNumber" class="search-input" placeholder="البحث برقم المريض..." oninput="debounceSearch()">
            <input type="text" id="searchAll" class="search-input large" placeholder="البحث في جميع بيانات المريض..." oninput="debounceSearch()">
        </div>

        {{-- ===== Table ===== --}}
        <div class="table-card">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th class="col-num">#</th>
                            <th><span class="sort-icon">↕</span>الاسم الكامل للمريض</th>
                            <th><span class="sort-icon">↕</span>رقم المريض</th>
                            <th><span class="sort-icon">↕</span>الجنس</th>
                            <th><span class="sort-icon">↕</span>العمر</th>
                            <th><span class="sort-icon">↕</span>رقم الهاتف</th>
                            <th><span class="sort-icon">↕</span>حالة المريض</th>
                            <th><span class="sort-icon">↕</span>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="patientsTable">
                        <tr><td colspan="9" class="loading">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <div class="page-info" id="pageInfo">عرض 0 إلى 0 من 0 مريض</div>
                <div class="page-controls">
                    <span>عدد الصفوف لكل صفحة</span>
                    <select class="page-size-select" id="pageSize" onchange="loadPatients(1)">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="page-nav">
                    <span class="page-text" id="pageText">صفحة 1 من 1</span>
                    <button class="page-btn" id="firstBtn" onclick="goToPage(1)" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
                    </button>
                    <button class="page-btn" id="prevBtn" onclick="goToPage(currentPage - 1)" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <button class="page-btn" id="nextBtn" onclick="goToPage(currentPage + 1)" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                    <button class="page-btn" id="lastBtn" onclick="goToPage(lastPage)" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Add/Edit Modal (skeleton - integrate with your API) ===== --}}
    <div class="modal" id="patientModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">إضافة مريض جديد</h2>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <form id="patientForm" onsubmit="savePatient(event)">
                <input type="hidden" id="patientId">
                <div class="form-grid">
                    <div class="form-group">
                        <label>رقم الملف</label>
                        <input type="text" id="fileNumber" readonly>
                    </div>
                    <div class="form-group">
                        <label>الاسم الكامل *</label>
                        <input type="text" id="fullName" required>
                    </div>
                    <div class="form-group">
                        <label>العمر *</label>
                        <input type="number" id="age" min="0" max="150" required>
                    </div>
                    <div class="form-group">
                        <label>الجنس *</label>
                        <select id="gender" required>
                            <option value="">اختر...</option>
                            <option value="male">ذكر</option>
                            <option value="female">أنثى</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>رقم الهاتف *</label>
                        <input type="tel" id="phone" required>
                    </div>
                    <div class="form-group">
                        <label>مكان الإقامة</label>
                        <input type="text" id="residence">
                    </div>
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal()">إلغاء</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        let currentPage = 1;
        let lastPage = 1;
        let searchTimeout = null;

        async function loadPatients(page = 1) {
            currentPage = page;
            const searchAll = document.getElementById('searchAll').value;
            const searchByNumber = document.getElementById('searchByNumber').value;
            const perPage = document.getElementById('pageSize').value;

            let url = `/api/patients?page=${page}&per_page=${perPage}`;
            if (searchAll) url += `&search=${encodeURIComponent(searchAll)}`;
            if (searchByNumber) url += `&file_number=${encodeURIComponent(searchByNumber)}`;

            try {
                const res = await apiCall(url);
                const data = await res.json();
                renderPatients(data);
            } catch (error) {
                document.getElementById('patientsTable').innerHTML =
                    '<tr><td colspan="9" class="loading">خطأ في التحميل</td></tr>';
            }
        }

        function renderPatients(data) {
            const patients = data.data || [];
            const tbody = document.getElementById('patientsTable');

            if (patients.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="empty-state"><div class="icon">👥</div><h3>لا توجد نتائج</h3></td></tr>';
                updatePagination(0, 0, 0, 1);
                return;
            }

            const startIndex = (data.current_page - 1) * data.per_page + 1;

            tbody.innerHTML = patients.map((p, idx) => {
                const initial = (p.full_name || '?').trim().charAt(0);
                const createdAt = p.created_at
                    ? new Date(p.created_at).toLocaleDateString('en-GB')
                    : '-';
                const isActive = p.is_active !== false;
                const phone = p.phone && p.phone.trim() ? p.phone : null;

                return `
                    <tr data-patient-id="${p.id}">
                        <td class="col-num">${startIndex + idx}</td>
                        <td>
                            <div class="patient-cell">
                                <div class="avatar">${initial}</div>
                                <span class="patient-name">${p.full_name}</span>
                            </div>
                        </td>
                        <td>${p.file_number ? '#' + p.file_number : '-'}</td>
                        <td><span class="badge ${p.gender === 'male' ? 'badge-male' : 'badge-female'}">${p.gender === 'male' ? 'ذكر' : 'أنثى'}</span></td>
                        <td class="age-cell">${p.age}<span class="unit">سنة</span></td>
                        <td class="phone-cell ${phone ? '' : 'empty'}">${phone || 'غير محدد'}</td>
                        <td>
                            <label class="toggle">
                                <input type="checkbox" ${isActive ? 'checked' : ''} onchange="togglePatient(${p.id}, this.checked)">
                                <span class="toggle-slider"></span>
                            </label>
                        </td>
                        <td class="date-cell">${createdAt}</td>
                        <td class="actions-cell">
                            <button class="action-btn action-delete" title="حذف" onclick="deletePatient(${p.id}, '${p.full_name.replace(/'/g, "\\'")}')">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                            </button>
                            <button class="action-btn action-edit" title="تعديل" onclick="openEditModal(${p.id})">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button class="action-btn action-view" title="عرض" onclick="viewPatient(${p.id})">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="action-btn action-more" title="المزيد">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            updatePagination(data.total, data.from, data.to, data.current_page, data.last_page);
        }

        function updatePagination(total, from, to, page, totalPages) {
            lastPage = totalPages || 1;
            document.getElementById('pageInfo').textContent = `عرض ${from || 0} إلى ${to || 0} من ${total || 0} مريض`;
            document.getElementById('pageText').textContent = `صفحة ${page} من ${lastPage}`;
            document.getElementById('firstBtn').disabled = page <= 1;
            document.getElementById('prevBtn').disabled = page <= 1;
            document.getElementById('nextBtn').disabled = page >= lastPage;
            document.getElementById('lastBtn').disabled = page >= lastPage;
        }

        function goToPage(page) {
            if (page < 1 || page > lastPage) return;
            loadPatients(page);
        }

        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadPatients(1), 300);
        }

        async function loadStats() {
            try {
                const res = await apiCall('/api/patients/stats');
                const data = await res.json();
                document.getElementById('statTotal').textContent = data.total_patients ?? '-';
                document.getElementById('statActive').textContent = data.active_patients ?? '-';
                document.getElementById('statToday').textContent = data.today_patients ?? '-';
                document.getElementById('statVisits').textContent = data.total_visits ?? '-';
            } catch (error) {
                // stats silently fail
            }
        }

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'إضافة مريض جديد';
            document.getElementById('patientForm').reset();
            document.getElementById('patientId').value = '';
            document.getElementById('patientModal').classList.add('open');
        }

        function openEditModal(id) {
            // TODO: fetch patient data and populate form
            document.getElementById('modalTitle').textContent = 'تعديل بيانات المريض';
            document.getElementById('patientId').value = id;
            document.getElementById('patientModal').classList.add('open');
        }

        function closeModal() {
            document.getElementById('patientModal').classList.remove('open');
        }

        function viewPatient(id) {
            // TODO: navigate to patient details or open drawer
            window.location.href = `/patients/${id}`;
        }

        function togglePatient(id, active) {
            // TODO: PATCH /api/patients/{id} with is_active
        }

        function deletePatient(id, name) {
            if (!confirm(`هل أنت متأكد من حذف "${name}"؟`)) return;
            // TODO: DELETE /api/patients/{id}
            loadPatients(currentPage);
        }

        function exportPatients() {
            // TODO: GET /api/patients/export and download as CSV/Excel
        }

        function openFilters() {
            // TODO: open advanced filters drawer/modal
        }

        async function savePatient(e) {
            e.preventDefault();
            // TODO: integrate POST/PUT /api/patients
        }

        async function apiCall(url, options = {}) {
            const mergedOptions = {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    ...(options.headers || {})
                },
                ...options
            };
            const response = await fetch(url, mergedOptions);
            if (response.status === 401) {
                localStorage.removeItem('token');
                window.location.href = '/login';
            }
            return response;
        }

        loadPatients();
        loadStats();
    </script>
</body>
</html>
