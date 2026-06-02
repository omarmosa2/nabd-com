<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المرضى - مجمع نبض الطبي</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { opacity: 0.9; font-size: 16px; }
        
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card h2 { color: #333; margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        
        .filters { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; flex: 1; min-width: 200px; }
        .filter-group label { color: #333; font-weight: 600; margin-bottom: 5px; font-size: 12px; }
        .filter-group input, .filter-group select { padding: 10px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; }
        .filter-group input:focus, .filter-group select:focus { outline: none; border-color: #667eea; }
        
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s; }
        .btn:hover { background: #5568d3; transform: translateY(-2px); }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-sm { padding: 8px 16px; font-size: 12px; white-space: nowrap; }
        .btn-eye { background: #17a2b8; padding: 8px 12px; min-width: 45px; }
        .btn-eye:hover { background: #138496; }
        
        .btn-group { display: flex; gap: 10px; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px; text-align: right; color: #666; font-weight: 600; border-bottom: 2px solid #e9ecef; }
        td { padding: 15px; border-bottom: 1px solid #e9ecef; color: #333; }
        td:last-child { white-space: nowrap; }
        tr:hover { background: #f8f9fa; cursor: pointer; }
        
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-warning { background: #fff3cd; color: #856404; }
        
        .loading { text-align: center; padding: 40px; color: #666; }
        
        .pagination { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
        .pagination button { padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 5px; cursor: pointer; }
        .pagination button.active { background: #667eea; color: white; }
        .pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
        
        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.open { display: flex; }
        .modal-content { background: white; padding: 30px; border-radius: 15px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h2 { color: #333; font-size: 24px; }
        .modal-close { background: none; border: none; font-size: 30px; cursor: pointer; color: #666; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { color: #333; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        .form-group input, .form-group select, .form-group textarea { padding: 12px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #667eea; }
        .form-group input:read-only { background: #f8f9fa; }
        
        /* Drawer */
        .drawer { position: fixed; top: 0; left: -600px; width: 600px; height: 100vh; background: white; box-shadow: 5px 0 30px rgba(0,0,0,0.2); transition: left 0.3s; z-index: 1000; overflow-y: auto; }
        .drawer.open { left: 0; }
        .drawer-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; position: sticky; top: 0; z-index: 10; }
        .drawer-header h2 { font-size: 24px; margin-bottom: 10px; }
        .drawer-close { position: absolute; top: 20px; left: 20px; background: rgba(255,255,255,0.2); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; font-size: 20px; }
        .drawer-content { padding: 30px; }
        
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 30px; }
        .info-item { background: #f8f9fa; padding: 15px; border-radius: 8px; }
        .info-item .label { color: #666; font-size: 12px; margin-bottom: 5px; }
        .info-item .value { color: #333; font-size: 16px; font-weight: 600; }
        
        .visit-card { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 15px; border-right: 4px solid #667eea; }
        .visit-card h4 { color: #333; margin-bottom: 10px; }
        .visit-card p { color: #666; font-size: 14px; margin-bottom: 5px; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #666; }
        .empty-state .icon { font-size: 64px; margin-bottom: 20px; opacity: 0.3; }
        .empty-state h3 { font-size: 20px; margin-bottom: 10px; }

        /* Confirmation Dialog */
        .confirm-dialog { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; align-items: center; justify-content: center; }
        .confirm-dialog.open { display: flex; }
        .confirm-content { background: white; padding: 40px; border-radius: 15px; max-width: 450px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); animation: slideUp 0.3s ease; }
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .confirm-icon { font-size: 60px; text-align: center; margin-bottom: 20px; }
        .confirm-title { color: #333; font-size: 22px; font-weight: 600; text-align: center; margin-bottom: 15px; }
        .confirm-message { color: #666; font-size: 16px; text-align: center; margin-bottom: 30px; line-height: 1.6; }
        .confirm-buttons { display: flex; gap: 15px; justify-content: center; }
        .confirm-buttons button { padding: 12px 30px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-confirm-yes { background: #dc3545; color: white; }
        .btn-confirm-yes:hover { background: #c82333; transform: translateY(-2px); }
        .btn-confirm-no { background: #6c757d; color: white; }
        .btn-confirm-no:hover { background: #5a6268; transform: translateY(-2px); }

        /* View Details Modal */
        .view-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001; align-items: center; justify-content: center; }
        .view-modal.open { display: flex; }
        .view-modal-content { background: white; padding: 30px; border-radius: 15px; max-width: 700px; width: 90%; max-height: 85vh; overflow-y: auto; }
        .view-modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; }
        .view-modal-header h2 { color: #333; font-size: 24px; }
        .view-modal-header p { color: #666; font-size: 14px; margin-top: 5px; }
        .view-modal-close { background: none; border: none; font-size: 28px; cursor: pointer; color: #999; transition: color 0.2s; }
        .view-modal-close:hover { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>إدارة المرضى</h1>
            <p>عرض وإدارة جميع المرضى وسجلاتهم الطبية</p>
        </div>

        <div id="alertContainer"></div>

        <div class="card">
            <h2>البحث والفلاتر</h2>
            <div class="filters">
                <div class="filter-group">
                    <label>بحث</label>
                    <input type="text" id="searchInput" placeholder="ابحث بالاسم أو رقم الملف أو الهاتف..." onkeyup="debounceSearch()">
                </div>
                <div class="filter-group">
                    <label>العيادة</label>
                    <select id="clinicFilter" onchange="loadPatients()">
                        <option value="">جميع العيادات</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>الطبيب</label>
                    <select id="doctorFilter" onchange="loadPatients()">
                        <option value="">جميع الأطباء</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>الجنس</label>
                    <select id="genderFilter" onchange="loadPatients()">
                        <option value="">الكل</option>
                        <option value="male">ذكر</option>
                        <option value="female">أنثى</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>من تاريخ</label>
                    <input type="date" id="dateFromFilter" onchange="loadPatients()">
                </div>
                <div class="filter-group">
                    <label>إلى تاريخ</label>
                    <input type="date" id="dateToFilter" onchange="loadPatients()">
                </div>
            </div>
            <button class="btn btn-secondary" onclick="resetFilters()">إعادة تعيين الفلاتر</button>
        </div>

        <div class="card">
            <div class="btn-group">
                <button class="btn btn-success" onclick="openAddModal()">+ إضافة مريض جديد</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>رقم الملف</th>
                        <th>الاسم</th>
                        <th>العمر</th>
                        <th>الجنس</th>
                        <th>الهاتف</th>
                        <th>آخر زيارة</th>
                        <th>عدد الزيارات</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody id="patientsTable">
                    <tr><td colspan="8" class="loading">جاري التحميل...</td></tr>
                </tbody>
            </table>
            <div class="pagination" id="pagination"></div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal" id="patientModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">إضافة مريض جديد</h2>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <form id="patientForm">
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
                        <label>مكان الإقامة</label>
                        <input type="text" id="residence">
                    </div>
                    <div class="form-group">
                        <label>رقم الهاتف *</label>
                        <input type="tel" id="phone" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">حفظ</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">إلغاء</button>
            </form>
        </div>
    </div>

    <!-- Patient Drawer -->
    <div class="drawer" id="patientDrawer">
        <div class="drawer-header">
            <button class="drawer-close" onclick="closeDrawer()">×</button>
            <h2 id="drawerTitle">تفاصيل المريض</h2>
            <p id="drawerSubtitle">معلومات شاملة</p>
        </div>
        <div class="drawer-content" id="drawerContent">
            <div class="loading">جاري التحميل...</div>
        </div>
    </div>

    <!-- View Patient Modal -->
    <div class="view-modal" id="viewPatientModal">
        <div class="view-modal-content">
            <div class="view-modal-header">
                <div>
                    <h2 id="viewModalTitle">تفاصيل المريض</h2>
                    <p id="viewModalSubtitle"></p>
                </div>
                <button class="view-modal-close" onclick="closeViewModal()">×</button>
            </div>
            <div id="viewModalContent">
                <div class="loading">جاري التحميل...</div>
            </div>
        </div>
    </div>

    <!-- Confirmation Dialog -->
    <div class="confirm-dialog" id="confirmDialog">
        <div class="confirm-content">
            <div class="confirm-icon" id="confirmIcon">⚠️</div>
            <h2 class="confirm-title" id="confirmTitle">تأكيد الحذف</h2>
            <p class="confirm-message" id="confirmMessage">هل أنت متأكد من هذه العملية؟</p>
            <div class="confirm-buttons">
                <button class="btn-confirm-yes" id="confirmYesBtn">حذف</button>
                <button class="btn-confirm-no" id="confirmNoBtn">إلغاء</button>
            </div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        let currentPage = 1;
        let searchTimeout = null;
        let clinics = [];
        let doctors = [];

        async function init() {
            await loadClinics();
            await loadDoctors();
            await loadPatients();
        }

        async function loadClinics() {
            try {
                const res = await apiCall('/api/clinics');
                const data = await res.json();
                clinics = data.data || [];
                
                const select = document.getElementById('clinicFilter');
                select.innerHTML = '<option value="">جميع العيادات</option>' + 
                    clinics.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            } catch (error) {
                console.error('Error loading clinics:', error);
            }
        }

        async function loadDoctors() {
            try {
                const res = await apiCall('/api/users?role=doctor');
                const data = await res.json();
                doctors = data.data || [];
                
                const select = document.getElementById('doctorFilter');
                select.innerHTML = '<option value="">جميع الأطباء</option>' + 
                    doctors.map(d => `<option value="${d.id}">${d.full_name}</option>`).join('');
            } catch (error) {
                console.error('Error loading doctors:', error);
            }
        }

        async function loadPatients(page = 1) {
            currentPage = page;
            
            const search = document.getElementById('searchInput').value;
            const clinicId = document.getElementById('clinicFilter').value;
            const doctorId = document.getElementById('doctorFilter').value;
            const gender = document.getElementById('genderFilter').value;
            const dateFrom = document.getElementById('dateFromFilter').value;
            const dateTo = document.getElementById('dateToFilter').value;

            let url = `/api/patients?page=${page}&per_page=15`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (clinicId) url += `&clinic_id=${clinicId}`;
            if (doctorId) url += `&doctor_id=${doctorId}`;
            if (gender) url += `&gender=${gender}`;
            if (dateFrom) url += `&date_from=${dateFrom}`;
            if (dateTo) url += `&date_to=${dateTo}`;

            try {
                const res = await apiCall(url);
                const data = await res.json();
                renderPatients(data);
            } catch (error) {
                document.getElementById('patientsTable').innerHTML = '<tr><td colspan="8" class="loading">خطأ في التحميل</td></tr>';
            }
        }

        function renderPatients(data) {
            const patients = data.data || [];
            const tbody = document.getElementById('patientsTable');
            
            if (patients.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="empty-state"><div class="icon">👥</div><h3>لا توجد نتائج</h3><p>لم يتم العثور على مرضى مطابقين للبحث</p></td></tr>';
                document.getElementById('pagination').innerHTML = '';
                return;
            }

            tbody.innerHTML = patients.map(p => {
                const lastVisit = p.visits && p.visits.length > 0 ? p.visits[0] : null;
                const lastVisitDate = lastVisit ? new Date(lastVisit.visit_date).toLocaleDateString('ar-SA') : '-';

                return `
                    <tr data-patient-id="${p.id}" onclick="openPatientDrawer(${p.id})">
                        <td><span class="badge badge-info">${p.file_number}</span></td>
                        <td>${p.full_name}</td>
                        <td>${p.age}</td>
                        <td>${p.gender === 'male' ? 'ذكر' : 'أنثى'}</td>
                        <td>${p.phone}</td>
                        <td>${lastVisitDate}</td>
                        <td><span class="badge badge-success">${p.visits_count || 0}</span></td>
                        <td>
                            <button class="btn btn-sm btn-eye" title="عرض التفاصيل" onclick="event.stopPropagation(); openViewModal(${p.id})">👁️</button>
                            <button class="btn btn-sm" onclick="event.stopPropagation(); openEditModal(${p.id})">تعديل</button>
                            ${p.visits_count === 0 ? `<button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deletePatient(${p.id}, '${p.full_name}')">حذف</button>` : ''}
                        </td>
                    </tr>
                `;
            }).join('');

            renderPagination(data);
        }

        function renderPagination(data) {
            const pagination = document.getElementById('pagination');
            const { current_page, last_page } = data;
            
            let html = '';
            
            html += `<button ${current_page === 1 ? 'disabled' : ''} onclick="loadPatients(${current_page - 1})">السابق</button>`;
            
            for (let i = 1; i <= last_page; i++) {
                if (i === 1 || i === last_page || (i >= current_page - 2 && i <= current_page + 2)) {
                    html += `<button class="${i === current_page ? 'active' : ''}" onclick="loadPatients(${i})">${i}</button>`;
                } else if (i === current_page - 3 || i === current_page + 3) {
                    html += '<button disabled>...</button>';
                }
            }
            
            html += `<button ${current_page === last_page ? 'disabled' : ''} onclick="loadPatients(${current_page + 1})">التالي</button>`;
            
            pagination.innerHTML = html;
        }

        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadPatients(1), 300);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('clinicFilter').value = '';
            document.getElementById('doctorFilter').value = '';
            document.getElementById('genderFilter').value = '';
            document.getElementById('dateFromFilter').value = '';
            document.getElementById('dateToFilter').value = '';
            loadPatients(1);
        }

        async function openAddModal() {
            document.getElementById('modalTitle').textContent = 'إضافة مريض جديد';
            document.getElementById('patientForm').reset();
            document.getElementById('patientId').value = '';
            
            try {
                const res = await apiCall('/api/patients/next-file-number');
                const data = await res.json();
                document.getElementById('fileNumber').value = data.file_number;
            } catch (error) {
                showAlert('خطأ في توليد رقم الملف', 'error');
            }
            
            document.getElementById('patientModal').classList.add('open');
        }

        async function openEditModal(patientId) {
            try {
                const res = await apiCall(`/api/patients/${patientId}`);
                const data = await res.json();
                const patient = data.patient;
                
                document.getElementById('modalTitle').textContent = 'تعديل بيانات المريض';
                document.getElementById('patientId').value = patient.id;
                document.getElementById('fileNumber').value = patient.file_number;
                document.getElementById('fullName').value = patient.full_name;
                document.getElementById('age').value = patient.age;
                document.getElementById('gender').value = patient.gender;
                document.getElementById('residence').value = patient.residence || '';
                document.getElementById('phone').value = patient.phone;
                
                document.getElementById('patientModal').classList.add('open');
            } catch (error) {
                showAlert('خطأ في تحميل بيانات المريض', 'error');
            }
        }

        function closeModal() {
            document.getElementById('patientModal').classList.remove('open');
        }

        document.getElementById('patientForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const patientId = document.getElementById('patientId').value;
            const data = {
                full_name: document.getElementById('fullName').value,
                age: parseInt(document.getElementById('age').value),
                gender: document.getElementById('gender').value,
                residence: document.getElementById('residence').value || null,
                phone: document.getElementById('phone').value
            };

            try {
                const url = patientId ? `/api/patients/${patientId}` : '/api/patients';
                const method = patientId ? 'PUT' : 'POST';
                
                const res = await apiCall(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    showAlert(patientId ? 'تم تحديث المريض بنجاح' : 'تم إضافة المريض بنجاح', 'success');
                    closeModal();
                    loadPatients(currentPage);
                } else {
                    const error = await res.json();
                    showAlert(error.message || 'خطأ في الحفظ', 'error');
                }
            } catch (error) {
                showAlert('خطأ في الاتصال', 'error');
            }
        });

        async function deletePatient(patientId, patientName) {
            const confirmed = await showConfirm(
                'حذف المريض',
                `هل أنت متأكد من حذف المريض "${patientName}"؟`,
                '🗑️'
            );

            if (!confirmed) return;

            try {
                const res = await apiCall(`/api/patients/${patientId}`, { method: 'DELETE' });

                if (res.ok) {
                    showAlert('تم حذف المريض بنجاح ✓', 'success');
                    const row = document.querySelector(`tr[data-patient-id="${patientId}"]`);
                    if (row) {
                        row.style.opacity = '0.5';
                        setTimeout(() => row.remove(), 300);
                    } else {
                        loadPatients(currentPage);
                    }
                } else {
                    const error = await res.json();
                    showAlert(error.message || 'خطأ في حذف المريض', 'error');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showAlert('خطأ في الحذف - تحقق من الاتصال', 'error');
            }
        }

        async function openPatientDrawer(patientId) {
            document.getElementById('patientDrawer').classList.add('open');
            document.getElementById('drawerContent').innerHTML = '<div class="loading">جاري التحميل...</div>';

            try {
                const res = await apiCall(`/api/patients/${patientId}`);
                const data = await res.json();
                renderPatientDetails(data);
            } catch (error) {
                document.getElementById('drawerContent').innerHTML = '<div class="loading">خطأ في التحميل</div>';
            }
        }

        function renderPatientDetails(data) {
            const patient = data.patient;
            const visits = data.visits || [];

            document.getElementById('drawerTitle').textContent = patient.full_name;
            document.getElementById('drawerSubtitle').textContent = `رقم الملف: ${patient.file_number}`;

            let html = `
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">العمر</div>
                        <div class="value">${patient.age} سنة</div>
                    </div>
                    <div class="info-item">
                        <div class="label">الجنس</div>
                        <div class="value">${patient.gender === 'male' ? 'ذكر' : 'أنثى'}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">الهاتف</div>
                        <div class="value">${patient.phone}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">الإقامة</div>
                        <div class="value">${patient.residence || '-'}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">إجمالي الزيارات</div>
                        <div class="value">${data.total_visits}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">إجمالي المصروف</div>
                        <div class="value">${data.total_spent} ر.س</div>
                    </div>
                </div>
                <h3 style="margin-bottom: 15px; color: #333;">سجل الزيارات</h3>
            `;

            if (visits.length === 0) {
                html += '<div class="empty-state"><div class="icon">📋</div><h3>لا توجد زيارات</h3><p>لم يقم المريض بأي زيارة بعد</p></div>';
            } else {
                html += visits.map(v => `
                    <div class="visit-card">
                        <h4>${v.visit_type === 'examination' ? 'معاينة' : 'مراجعة'} - ${new Date(v.visit_date).toLocaleDateString('ar-SA')}</h4>
                        <p><strong>الطبيب:</strong> ${v.doctor.full_name}</p>
                        <p><strong>العيادة:</strong> ${v.clinic.name}</p>
                        <p><strong>إجمالي الرسوم:</strong> ${v.totals.total_fees} ر.س</p>
                        <p><strong>حصة الطبيب:</strong> ${v.totals.doctor_share} ر.س</p>
                        <p><strong>حصة المجمع:</strong> ${v.totals.center_share} ر.س</p>
                        ${v.is_free_review ? '<p><span class="badge badge-warning">مراجعة مجانية</span></p>' : ''}
                        ${v.procedures.length > 0 ? `
                            <p style="margin-top: 10px;"><strong>العمليات:</strong></p>
                            <ul style="margin-right: 20px; margin-top: 5px;">
                                ${v.procedures.map(p => `<li>${p.name} - مجمع: ${p.center_fee} ر.س، طبيب: ${p.doctor_fee} ر.س</li>`).join('')}
                            </ul>
                        ` : ''}
                        ${v.notes ? `<p style="margin-top: 10px;"><strong>ملاحظات:</strong> ${v.notes}</p>` : ''}
                    </div>
                `).join('');
            }

            document.getElementById('drawerContent').innerHTML = html;
        }

        function closeDrawer() {
            document.getElementById('patientDrawer').classList.remove('open');
        }

        async function openViewModal(patientId) {
            document.getElementById('viewPatientModal').classList.add('open');
            document.getElementById('viewModalContent').innerHTML = '<div class="loading">جاري التحميل...</div>';

            try {
                const res = await apiCall(`/api/patients/${patientId}`);
                const data = await res.json();
                renderViewModalContent(data);
            } catch (error) {
                document.getElementById('viewModalContent').innerHTML = '<div class="loading">خطأ في التحميل</div>';
            }
        }

        function renderViewModalContent(data) {
            const patient = data.patient;
            const visits = data.visits || [];

            document.getElementById('viewModalTitle').textContent = patient.full_name;
            document.getElementById('viewModalSubtitle').textContent = `رقم الملف: ${patient.file_number}`;

            let html = `
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">العمر</div>
                        <div class="value">${patient.age} سنة</div>
                    </div>
                    <div class="info-item">
                        <div class="label">الجنس</div>
                        <div class="value">${patient.gender === 'male' ? 'ذكر' : 'أنثى'}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">الهاتف</div>
                        <div class="value">${patient.phone}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">الإقامة</div>
                        <div class="value">${patient.residence || '-'}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">إجمالي الزيارات</div>
                        <div class="value">${data.total_visits}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">إجمالي المصروف</div>
                        <div class="value">${data.total_spent} ر.س</div>
                    </div>
                </div>
                <h3 style="margin-bottom: 15px; color: #333;">سجل الزيارات</h3>
            `;

            if (visits.length === 0) {
                html += '<div class="empty-state"><div class="icon">📋</div><h3>لا توجد زيارات</h3><p>لم يقم المريض بأي زيارة بعد</p></div>';
            } else {
                html += visits.map(v => `
                    <div class="visit-card">
                        <h4>${v.visit_type === 'examination' ? 'معاينة' : 'مراجعة'} - ${new Date(v.visit_date).toLocaleDateString('ar-SA')}</h4>
                        <p><strong>الطبيب:</strong> ${v.doctor.full_name}</p>
                        <p><strong>العيادة:</strong> ${v.clinic.name}</p>
                        <p><strong>إجمالي الرسوم:</strong> ${v.totals.total_fees} ر.س</p>
                        <p><strong>حصة الطبيب:</strong> ${v.totals.doctor_share} ر.س</p>
                        <p><strong>حصة المجمع:</strong> ${v.totals.center_share} ر.س</p>
                        ${v.is_free_review ? '<p><span class="badge badge-warning">مراجعة مجانية</span></p>' : ''}
                        ${v.procedures.length > 0 ? `
                            <p style="margin-top: 10px;"><strong>العمليات:</strong></p>
                            <ul style="margin-right: 20px; margin-top: 5px;">
                                ${v.procedures.map(p => `<li>${p.name} - مجمع: ${p.center_fee} ر.س، طبيب: ${p.doctor_fee} ر.س</li>`).join('')}
                            </ul>
                        ` : ''}
                        ${v.notes ? `<p style="margin-top: 10px;"><strong>ملاحظات:</strong> ${v.notes}</p>` : ''}
                    </div>
                `).join('');
            }

            document.getElementById('viewModalContent').innerHTML = html;
        }

        function closeViewModal() {
            document.getElementById('viewPatientModal').classList.remove('open');
        }

        async function apiCall(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            };

            const mergedOptions = { ...defaultOptions, ...options };
            if (options.headers) {
                mergedOptions.headers = { ...defaultOptions.headers, ...options.headers };
            }

            const response = await fetch(url, mergedOptions);
            
            if (response.status === 401) {
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                window.location.href = '/login';
                throw new Error('Unauthorized');
            }

            return response;
        }

        function showAlert(message, type) {
            const container = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            container.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
        }

        function showConfirm(title, message, icon = '⚠️') {
            return new Promise((resolve) => {
                const dialog = document.getElementById('confirmDialog');
                if (!dialog) {
                    resolve(confirm(message));
                    return;
                }

                document.getElementById('confirmTitle').textContent = title;
                document.getElementById('confirmMessage').textContent = message;
                document.getElementById('confirmIcon').textContent = icon;

                const yesBtn = document.getElementById('confirmYesBtn');
                const noBtn = document.getElementById('confirmNoBtn');

                const cleanup = () => {
                    dialog.classList.remove('open');
                    yesBtn.removeEventListener('click', handleYes);
                    noBtn.removeEventListener('click', handleNo);
                };

                const handleYes = () => {
                    cleanup();
                    resolve(true);
                };

                const handleNo = () => {
                    cleanup();
                    resolve(false);
                };

                yesBtn.addEventListener('click', handleYes);
                noBtn.addEventListener('click', handleNo);
                dialog.classList.add('open');
            });
        }

        init();
    </script>
</body>
</html>
