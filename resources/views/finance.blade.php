<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المالية - مجمع نبض الطبي</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .stat-card h3 { color: #667eea; font-size: 32px; margin-bottom: 10px; }
        .stat-card p { color: #666; font-size: 14px; }
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card h2 { color: #333; margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px; text-align: right; color: #666; font-weight: 600; border-bottom: 2px solid #e9ecef; }
        td { padding: 15px; border-bottom: 1px solid #e9ecef; color: #333; }
        tr:hover { background: #f8f9fa; cursor: pointer; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s; }
        .btn:hover { background: #5568d3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-group { display: flex; gap: 10px; margin-bottom: 20px; }
        .loading { text-align: center; padding: 40px; color: #666; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal.open { display: flex; }
        .modal-content { background: white; padding: 30px; border-radius: 15px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h2 { color: #333; font-size: 24px; }
        .modal-close { background: none; border: none; font-size: 30px; cursor: pointer; color: #666; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; color: #333; font-weight: 600; margin-bottom: 8px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #667eea; }
        .back-btn { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <button class="btn btn-secondary back-btn" onclick="window.history.back()">← رجوع</button>
        
        <div class="header">
            <h1>المالية</h1>
            <p>تحليل الأرباح والمستحقات</p>
        </div>

        <div class="stats-grid" id="statsGrid">
            <div class="loading">جاري التحميل...</div>
        </div>

        <div class="card">
            <h2>ملخص الأطباء المالي</h2>
            <div class="btn-group">
                <button class="btn btn-success" onclick="exportToExcel()">تصدير Excel</button>
                <button class="btn" onclick="exportToPDF()">تصدير PDF</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>الطبيب</th>
                        <th>العيادة</th>
                        <th>عدد الزيارات</th>
                        <th>إجمالي الحصة</th>
                        <th>الخصومات</th>
                        <th>الصافي</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody id="doctorsTable">
                    <tr><td colspan="7" class="loading">جاري التحميل...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal" id="deductionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>إضافة خصم</h2>
                <button class="modal-close" onclick="closeDeductionModal()">×</button>
            </div>
            <form id="deductionForm">
                <div class="form-group">
                    <label>الطبيب</label>
                    <input type="text" id="doctorName" readonly>
                    <input type="hidden" id="doctorId">
                </div>
                <div class="form-group">
                    <label>المبلغ *</label>
                    <input type="number" id="amount" step="0.01" min="0.01" required>
                </div>
                <div class="form-group">
                    <label>السبب *</label>
                    <textarea id="reason" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-danger">حفظ الخصم</button>
            </form>
        </div>
    </div>

    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>تفاصيل الطبيب</h2>
                <button class="modal-close" onclick="closeDetailsModal()">×</button>
            </div>
            <div id="detailsContent">
                <div class="loading">جاري التحميل...</div>
            </div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        let doctorsData = [];

        async function loadFinance() {
            try {
                const [summaryRes, doctorsRes] = await Promise.all([
                    fetch('/api/finance/summary', { headers: { 'Authorization': `Bearer ${token}` } }),
                    fetch('/api/finance/doctors', { headers: { 'Authorization': `Bearer ${token}` } })
                ]);

                const summary = await summaryRes.json();
                const doctors = await doctorsRes.json();

                renderStats(summary);
                renderDoctors(doctors.data || []);
            } catch (error) {
                console.error('Error loading finance:', error);
            }
        }

        function renderStats(summary) {
            document.getElementById('statsGrid').innerHTML = `
                <div class="stat-card">
                    <h3>${summary.total_examination_fees} ل.س</h3>
                    <p>إجمالي رسوم الكشف</p>
                </div>
                <div class="stat-card">
                    <h3>${summary.total_amount_received} ل.س</h3>
                    <p>المبلغ المستلم</p>
                </div>
                <div class="stat-card">
                    <h3>${summary.total_center_share} ل.س</h3>
                    <p>حصة المجمع</p>
                </div>
                <div class="stat-card">
                    <h3>${summary.total_doctor_share} ل.س</h3>
                    <p>إجمالي مستحقات الأطباء</p>
                </div>
                <div class="stat-card">
                    <h3>${summary.total_deductions} ل.س</h3>
                    <p>إجمالي الخصومات</p>
                </div>
                <div class="stat-card">
                    <h3>${summary.net_doctor_payable} ل.س</h3>
                    <p>صافي مستحقات الأطباء</p>
                </div>
            `;
        }

        function renderDoctors(doctors) {
            doctorsData = doctors;
            const tbody = document.getElementById('doctorsTable');
            if (doctors.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="loading">لا توجد بيانات</td></tr>';
                return;
            }
            tbody.innerHTML = doctors.map(doc => `
                <tr>
                    <td onclick="openDetailsModal(${doc.doctor_id})">${doc.doctor_name}</td>
                    <td>${doc.clinic || '-'}</td>
                    <td><span class="badge badge-success">${doc.total_visits}</span></td>
                    <td>${doc.doctor_share} ل.س</td>
                    <td><span class="badge badge-danger">${doc.deductions} ل.س</span></td>
                    <td><strong>${doc.net_payable} ل.س</strong></td>
                    <td>
                        <button class="btn btn-danger" onclick="openDeductionModal(${doc.doctor_id}, '${doc.doctor_name}')">خصم</button>
                    </td>
                </tr>
            `).join('');
        }

        function openDeductionModal(doctorId, doctorName) {
            document.getElementById('doctorId').value = doctorId;
            document.getElementById('doctorName').value = doctorName;
            document.getElementById('deductionModal').classList.add('open');
        }

        function closeDeductionModal() {
            document.getElementById('deductionModal').classList.remove('open');
            document.getElementById('deductionForm').reset();
        }

        document.getElementById('deductionForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = {
                doctor_id: parseInt(document.getElementById('doctorId').value),
                amount: parseFloat(document.getElementById('amount').value),
                reason: document.getElementById('reason').value
            };

            try {
                const res = await fetch('/api/finance/deductions', {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    alert('تم إضافة الخصم بنجاح');
                    closeDeductionModal();
                    loadFinance();
                } else {
                    alert('خطأ في إضافة الخصم');
                }
            } catch (error) {
                alert('خطأ في الاتصال');
            }
        });

        async function openDetailsModal(doctorId) {
            document.getElementById('detailsModal').classList.add('open');
            document.getElementById('detailsContent').innerHTML = '<div class="loading">جاري التحميل...</div>';

            try {
                const res = await fetch(`/api/finance/doctor/${doctorId}/details`, { 
                    headers: { 'Authorization': `Bearer ${token}` } 
                });
                const data = await res.json();
                renderDoctorDetails(data);
            } catch (error) {
                document.getElementById('detailsContent').innerHTML = '<div class="loading">خطأ في التحميل</div>';
            }
        }

        function renderDoctorDetails(data) {
            let html = `
                <div style="margin-bottom: 20px;">
                    <h3>${data.doctor.full_name}</h3>
                    <p style="color: #666;">${data.doctor.clinic || '-'}</p>
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #667eea;">${data.total_visits}</div>
                        <div style="color: #666; font-size: 12px;">عدد الزيارات</div>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #28a745;">${data.total_share} ل.س</div>
                        <div style="color: #666; font-size: 12px;">إجمالي الحصة</div>
                    </div>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #dc3545;">${data.total_deductions} ل.س</div>
                        <div style="color: #666; font-size: 12px;">الخصومات</div>
                    </div>
                </div>
                <h4 style="margin-bottom: 15px;">سجل الزيارات</h4>
            `;

            if (data.visits.length === 0) {
                html += '<div class="loading">لا توجد زيارات</div>';
            } else {
                html += '<table><thead><tr><th>التاريخ</th><th>المريض</th><th>النوع</th><th>الحصة</th></tr></thead><tbody>';
                html += data.visits.map(v => `
                    <tr>
                        <td>${v.date}</td>
                        <td>${v.patient}</td>
                        <td>${v.type === 'examination' ? 'معاينة' : 'مراجعة'}</td>
                        <td>${v.doctor_share} ل.س</td>
                    </tr>
                `).join('');
                html += '</tbody></table>';
            }

            if (data.deductions.length > 0) {
                html += '<h4 style="margin-top: 20px; margin-bottom: 15px;">سجل الخصومات</h4>';
                html += '<table><thead><tr><th>التاريخ</th><th>المبلغ</th><th>السبب</th></tr></thead><tbody>';
                html += data.deductions.map(d => `
                    <tr>
                        <td>${d.date}</td>
                        <td>${d.amount} ل.س</td>
                        <td>${d.reason}</td>
                    </tr>
                `).join('');
                html += '</tbody></table>';
            }

            document.getElementById('detailsContent').innerHTML = html;
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.remove('open');
        }

        function exportToExcel() {
            alert('تصدير Excel - قيد التطوير');
        }

        function exportToPDF() {
            alert('تصدير PDF - قيد التطوير');
        }

        loadFinance();
    </script>
</body>
</html>
