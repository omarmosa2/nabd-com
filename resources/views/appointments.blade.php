<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المواعيد - مجمع نبض الطبي</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .filters { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { color: #333; font-weight: 600; margin-bottom: 5px; font-size: 12px; }
        .filter-group input, .filter-group select { padding: 10px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; }
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s; }
        .btn:hover { background: #5568d3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .card h2 { color: #333; margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px; text-align: right; color: #666; font-weight: 600; border-bottom: 2px solid #e9ecef; }
        td { padding: 15px; border-bottom: 1px solid #e9ecef; color: #333; }
        tr:hover { background: #f8f9fa; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .loading { text-align: center; padding: 40px; color: #666; }
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
        .back-btn { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <button class="btn btn-secondary back-btn" onclick="window.history.back()">← رجوع</button>
        
        <div class="header">
            <h1>المواعيد</h1>
            <p>إدارة مواعيد المرضى</p>
        </div>

        <div class="filters">
            <div class="filter-group">
                <label>العيادة</label>
                <select id="clinicFilter" onchange="loadAppointments()">
                    <option value="">جميع العيادات</option>
                </select>
            </div>
            <div class="filter-group">
                <label>الطبيب</label>
                <select id="doctorFilter" onchange="loadAppointments()">
                    <option value="">جميع الأطباء</option>
                </select>
            </div>
            <div class="filter-group">
                <label>التاريخ</label>
                <input type="date" id="dateFilter" onchange="loadAppointments()">
            </div>
            <button class="btn btn-success" onclick="openAddModal()">+ إضافة موعد</button>
        </div>

        <div class="card">
            <h2>قائمة المواعيد</h2>
            <table>
                <thead>
                    <tr>
                        <th>المريض</th>
                        <th>الطبيب</th>
                        <th>العيادة</th>
                        <th>التاريخ والوقت</th>
                        <th>ملاحظات</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody id="appointmentsTable">
                    <tr><td colspan="6" class="loading">جاري التحميل...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal" id="appointmentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">إضافة موعد جديد</h2>
                <button class="modal-close" onclick="closeModal()">×</button>
            </div>
            <form id="appointmentForm">
                <input type="hidden" id="appointmentId">
                <div class="form-grid">
                    <div class="form-group">
                        <label>المريض *</label>
                        <select id="patientId" required>
                            <option value="">اختر المريض...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>العيادة *</label>
                        <select id="clinicId" onchange="loadDoctorsForModal()" required>
                            <option value="">اختر العيادة...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>الطبيب *</label>
                        <select id="doctorId" required>
                            <option value="">اختر الطبيب...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>التاريخ والوقت *</label>
                        <input type="datetime-local" id="appointmentDate" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>ملاحظات</label>
                    <textarea id="notes" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-success">حفظ الموعد</button>
            </form>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        async function loadClinics() {
            try {
                const res = await fetch('/api/clinics', { headers: { 'Authorization': `Bearer ${token}` } });
                const data = await res.json();
                
                const filterSelect = document.getElementById('clinicFilter');
                const modalSelect = document.getElementById('clinicId');
                
                const options = '<option value="">جميع العيادات</option>' + 
                    data.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
                
                filterSelect.innerHTML = options;
                modalSelect.innerHTML = '<option value="">اختر العيادة...</option>' + 
                    data.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            } catch (error) {
                console.error('Error loading clinics:', error);
            }
        }

        async function loadDoctors() {
            try {
                const res = await fetch('/api/users?role=doctor', { headers: { 'Authorization': `Bearer ${token}` } });
                const data = await res.json();
                
                const filterSelect = document.getElementById('doctorFilter');
                filterSelect.innerHTML = '<option value="">جميع الأطباء</option>' + 
                    (data.data || []).map(d => `<option value="${d.id}">${d.full_name}</option>`).join('');
            } catch (error) {
                console.error('Error loading doctors:', error);
            }
        }

        async function loadDoctorsForModal() {
            const clinicId = document.getElementById('clinicId').value;
            if (!clinicId) return;

            try {
                const res = await fetch(`/api/reception/doctors?clinic_id=${clinicId}`, { 
                    headers: { 'Authorization': `Bearer ${token}` } 
                });
                const data = await res.json();
                
                const select = document.getElementById('doctorId');
                select.innerHTML = '<option value="">اختر الطبيب...</option>' + 
                    data.doctors.map(d => `<option value="${d.id}">${d.full_name}</option>`).join('');
            } catch (error) {
                console.error('Error loading doctors:', error);
            }
        }

        async function loadPatients() {
            try {
                const res = await fetch('/api/patients?per_page=100', { headers: { 'Authorization': `Bearer ${token}` } });
                const data = await res.json();
                
                const select = document.getElementById('patientId');
                select.innerHTML = '<option value="">اختر المريض...</option>' + 
                    (data.data || []).map(p => `<option value="${p.id}">${p.full_name} - ${p.file_number}</option>`).join('');
            } catch (error) {
                console.error('Error loading patients:', error);
            }
        }

        async function loadAppointments() {
            const clinicId = document.getElementById('clinicFilter').value;
            const doctorId = document.getElementById('doctorFilter').value;
            const date = document.getElementById('dateFilter').value;

            let url = '/api/appointments?per_page=50';
            if (clinicId) url += `&clinic_id=${clinicId}`;
            if (doctorId) url += `&doctor_id=${doctorId}`;
            if (date) url += `&date=${date}`;

            try {
                const res = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` } });
                const data = await res.json();
                renderAppointments(data.data || []);
            } catch (error) {
                document.getElementById('appointmentsTable').innerHTML = '<tr><td colspan="6" class="loading">خطأ في التحميل</td></tr>';
            }
        }

        function renderAppointments(appointments) {
            const tbody = document.getElementById('appointmentsTable');
            if (appointments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="loading">لا توجد مواعيد</td></tr>';
                return;
            }
            tbody.innerHTML = appointments.map(apt => `
                <tr>
                    <td>${apt.patient.full_name}</td>
                    <td>${apt.doctor.full_name}</td>
                    <td>${apt.clinic.name}</td>
                    <td><span class="badge badge-success">${new Date(apt.appointment_date).toLocaleString('ar-SA')}</span></td>
                    <td>${apt.notes || '-'}</td>
                    <td>
                        <button class="btn btn-danger" onclick="deleteAppointment(${apt.id})">حذف</button>
                    </td>
                </tr>
            `).join('');
        }

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'إضافة موعد جديد';
            document.getElementById('appointmentForm').reset();
            document.getElementById('appointmentId').value = '';
            document.getElementById('appointmentModal').classList.add('open');
        }

        function closeModal() {
            document.getElementById('appointmentModal').classList.remove('open');
        }

        document.getElementById('appointmentForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                patient_id: parseInt(document.getElementById('patientId').value),
                clinic_id: parseInt(document.getElementById('clinicId').value),
                doctor_id: parseInt(document.getElementById('doctorId').value),
                appointment_date: document.getElementById('appointmentDate').value,
                notes: document.getElementById('notes').value || null
            };

            try {
                const res = await fetch('/api/appointments', {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    alert('تم حفظ الموعد بنجاح');
                    closeModal();
                    loadAppointments();
                } else {
                    alert('خطأ في حفظ الموعد');
                }
            } catch (error) {
                alert('خطأ في الاتصال');
            }
        });

        async function deleteAppointment(id) {
            if (!confirm('هل أنت متأكد من حذف هذا الموعد؟')) return;

            try {
                const res = await fetch(`/api/appointments/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                if (res.ok) {
                    alert('تم حذف الموعد');
                    loadAppointments();
                } else {
                    alert('خطأ في الحذف');
                }
            } catch (error) {
                alert('خطأ في الاتصال');
            }
        }

        loadClinics();
        loadDoctors();
        loadPatients();
        loadAppointments();
    </script>
</body>
</html>
