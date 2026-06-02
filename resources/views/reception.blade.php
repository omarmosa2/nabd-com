<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الاستقبال - مجمع نبض الطبي</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card h2 { color: #333; margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { color: #333; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        .form-group input, .form-group select, .form-group textarea { padding: 12px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; transition: border-color 0.3s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #667eea; }
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; transition: all 0.3s; }
        .btn:hover { background: #5568d3; transform: translateY(-2px); }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        .procedures-list { margin-top: 20px; }
        .procedure-item { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; align-items: center; }
        .procedure-item input { padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        .totals-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin-top: 20px; }
        .totals-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; }
        .total-item { text-align: center; }
        .total-item .label { font-size: 12px; opacity: 0.9; margin-bottom: 5px; }
        .total-item .value { font-size: 24px; font-weight: bold; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .back-btn { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <button class="btn btn-secondary back-btn" onclick="window.history.back()">← رجوع</button>
        
        <div class="header">
            <h1>الاستقبال</h1>
            <p>تسجيل المرضى وإنشاء الزيارات</p>
        </div>

        <div id="alertContainer"></div>

        <div class="card">
            <h2>بيانات المريض</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>رقم الملف</label>
                    <input type="text" id="fileNumber" placeholder="اتركه فارغاً لإنشاء مريض جديد">
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
            <button class="btn" onclick="searchPatient()">بحث عن المريض</button>
        </div>

        <div class="card">
            <h2>بيانات الزيارة</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>العيادة *</label>
                    <select id="clinicId" onchange="loadDoctors()" required>
                        <option value="">اختر العيادة...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>الطبيب *</label>
                    <select id="doctorId" onchange="updateExaminationFee()" required>
                        <option value="">اختر الطبيب...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>تاريخ الزيارة *</label>
                    <input type="date" id="visitDate" required>
                </div>
                <div class="form-group">
                    <label>نوع الزيارة *</label>
                    <select id="visitType" required>
                        <option value="examination">معاينة</option>
                        <option value="review">مراجعة</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>رسوم المعاينة</label>
                    <input type="number" id="examinationFee" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>خصم المجمع</label>
                    <input type="number" id="complexDiscount" step="0.01" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>خصم الطبيب</label>
                    <input type="number" id="doctorDiscount" step="0.01" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>المبلغ المستلم</label>
                    <input type="number" id="amountReceived" step="0.01" min="0" value="0">
                </div>
            </div>
            <div class="form-group">
                <label>ملاحظات</label>
                <textarea id="notes" rows="3"></textarea>
            </div>
        </div>

        <div class="card">
            <h2>العمليات</h2>
            <button class="btn" onclick="addProcedure()">+ إضافة عملية</button>
            <div class="procedures-list" id="proceduresList"></div>
        </div>

        <div class="card">
            <h2>الملخص المالي</h2>
            <button class="btn btn-secondary" onclick="calculatePreview()">حساب المعاينة</button>
            <div class="totals-box" id="totalsBox" style="display: none;">
                <div class="totals-grid">
                    <div class="total-item">
                        <div class="label">إجمالي الرسوم</div>
                        <div class="value" id="totalFees">0</div>
                    </div>
                    <div class="total-item">
                        <div class="label">حصة الطبيب</div>
                        <div class="value" id="doctorShare">0</div>
                    </div>
                    <div class="total-item">
                        <div class="label">حصة المجمع</div>
                        <div class="value" id="centerShare">0</div>
                    </div>
                </div>
            </div>
            <div class="btn-group">
                <button class="btn btn-success" onclick="saveVisit()">حفظ الزيارة</button>
                <button class="btn btn-secondary" onclick="resetForm()">إعادة تعيين</button>
            </div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        let patientId = null;
        let procedures = [];

        document.getElementById('visitDate').valueAsDate = new Date();

        async function loadClinics() {
            try {
                const res = await fetch('/api/clinics', { headers: { 'Authorization': `Bearer ${token}` } });
                const data = await res.json();
                const select = document.getElementById('clinicId');
                select.innerHTML = '<option value="">اختر العيادة...</option>' + 
                    data.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            } catch (error) {
                showAlert('خطأ في تحميل العيادات', 'error');
            }
        }

        async function loadDoctors() {
            const clinicId = document.getElementById('clinicId').value;
            if (!clinicId) return;

            try {
                const res = await fetch(`/api/reception/doctors?clinic_id=${clinicId}`, { 
                    headers: { 'Authorization': `Bearer ${token}` } 
                });
                const data = await res.json();
                const select = document.getElementById('doctorId');
                select.innerHTML = '<option value="">اختر الطبيب...</option>' + 
                    data.doctors.map(d => `<option value="${d.id}" data-fee="${d.examination_fee}">${d.full_name}</option>`).join('');
            } catch (error) {
                showAlert('خطأ في تحميل الأطباء', 'error');
            }
        }

        function updateExaminationFee() {
            const select = document.getElementById('doctorId');
            const option = select.options[select.selectedIndex];
            if (option && option.dataset.fee) {
                document.getElementById('examinationFee').value = option.dataset.fee;
            }
        }

        async function searchPatient() {
            const fileNumber = document.getElementById('fileNumber').value.trim();
            if (!fileNumber) {
                showAlert('الرجاء إدخال رقم الملف', 'error');
                return;
            }

            try {
                const res = await fetch(`/api/reception/patients/by-file/${fileNumber}`, { 
                    headers: { 'Authorization': `Bearer ${token}` } 
                });
                
                if (res.ok) {
                    const data = await res.json();
                    patientId = data.patient.id;
                    document.getElementById('fullName').value = data.patient.full_name;
                    document.getElementById('age').value = data.patient.age;
                    document.getElementById('gender').value = data.patient.gender;
                    document.getElementById('residence').value = data.patient.residence || '';
                    document.getElementById('phone').value = data.patient.phone;
                    showAlert('تم العثور على المريض', 'success');
                } else {
                    patientId = null;
                    showAlert('لم يتم العثور على المريض - سيتم إنشاء مريض جديد', 'info');
                }
            } catch (error) {
                showAlert('خطأ في البحث', 'error');
            }
        }

        function addProcedure() {
            const index = procedures.length;
            procedures.push({ name: '', center_fee: 0, doctor_fee: 0 });
            
            const div = document.createElement('div');
            div.className = 'procedure-item';
            div.id = `procedure-${index}`;
            div.innerHTML = `
                <input type="text" placeholder="اسم العملية" onchange="updateProcedure(${index}, 'name', this.value)">
                <input type="number" placeholder="رسوم المجمع" step="0.01" min="0" onchange="updateProcedure(${index}, 'center_fee', this.value)">
                <input type="number" placeholder="رسوم الطبيب" step="0.01" min="0" onchange="updateProcedure(${index}, 'doctor_fee', this.value)">
                <button class="btn btn-danger" onclick="removeProcedure(${index})">حذف</button>
            `;
            document.getElementById('proceduresList').appendChild(div);
        }

        function updateProcedure(index, field, value) {
            procedures[index][field] = field === 'name' ? value : parseFloat(value) || 0;
        }

        function removeProcedure(index) {
            procedures.splice(index, 1);
            document.getElementById(`procedure-${index}`).remove();
        }

        async function calculatePreview() {
            if (!patientId && !document.getElementById('fullName').value) {
                showAlert('الرجاء إدخال بيانات المريض', 'error');
                return;
            }

            const data = {
                patient_id: patientId || 1,
                doctor_id: parseInt(document.getElementById('doctorId').value),
                visit_type: document.getElementById('visitType').value,
                examination_fee: parseFloat(document.getElementById('examinationFee').value) || 0,
                complex_discount: parseFloat(document.getElementById('complexDiscount').value) || 0,
                doctor_discount: parseFloat(document.getElementById('doctorDiscount').value) || 0,
                procedures: procedures.filter(p => p.name)
            };

            try {
                const res = await fetch('/api/reception/visits/calc-preview', {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await res.json();
                
                document.getElementById('totalsBox').style.display = 'block';
                document.getElementById('totalFees').textContent = result.totals.total_fees + ' ل.س';
                document.getElementById('doctorShare').textContent = result.totals.doctor_share + ' ل.س';
                document.getElementById('centerShare').textContent = result.totals.center_share + ' ل.س';

                if (result.is_free_review) {
                    showAlert('هذه مراجعة مجانية', 'info');
                }
            } catch (error) {
                showAlert('خطأ في الحساب', 'error');
            }
        }

        async function saveVisit() {
            const patientData = {
                file_number: document.getElementById('fileNumber').value || null,
                full_name: document.getElementById('fullName').value,
                age: parseInt(document.getElementById('age').value),
                gender: document.getElementById('gender').value,
                residence: document.getElementById('residence').value || null,
                phone: document.getElementById('phone').value
            };

            if (!patientData.full_name || !patientData.age || !patientData.gender || !patientData.phone) {
                showAlert('الرجاء إكمال بيانات المريض', 'error');
                return;
            }

            try {
                const patientRes = await fetch('/api/reception/patients/upsert', {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(patientData)
                });

                const patientResult = await patientRes.json();
                const finalPatientId = patientResult.patient.id;

                const visitData = {
                    patient_id: finalPatientId,
                    doctor_id: parseInt(document.getElementById('doctorId').value),
                    clinic_id: parseInt(document.getElementById('clinicId').value),
                    visit_date: document.getElementById('visitDate').value,
                    visit_type: document.getElementById('visitType').value,
                    examination_fee: parseFloat(document.getElementById('examinationFee').value) || 0,
                    amount_received: parseFloat(document.getElementById('amountReceived').value) || 0,
                    complex_discount: parseFloat(document.getElementById('complexDiscount').value) || 0,
                    doctor_discount: parseFloat(document.getElementById('doctorDiscount').value) || 0,
                    notes: document.getElementById('notes').value || null,
                    procedures: procedures.filter(p => p.name)
                };

                const visitRes = await fetch('/api/reception/visits', {
                    method: 'POST',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(visitData)
                });

                const visitResult = await visitRes.json();
                showAlert('تم حفظ الزيارة بنجاح', 'success');
                
                setTimeout(() => {
                    window.location.href = `/patients/${finalPatientId}`;
                }, 1500);
            } catch (error) {
                showAlert('خطأ في حفظ الزيارة', 'error');
            }
        }

        function resetForm() {
            patientId = null;
            procedures = [];
            document.getElementById('fileNumber').value = '';
            document.getElementById('fullName').value = '';
            document.getElementById('age').value = '';
            document.getElementById('gender').value = '';
            document.getElementById('residence').value = '';
            document.getElementById('phone').value = '';
            document.getElementById('clinicId').value = '';
            document.getElementById('doctorId').innerHTML = '<option value="">اختر الطبيب...</option>';
            document.getElementById('examinationFee').value = '';
            document.getElementById('complexDiscount').value = '0';
            document.getElementById('doctorDiscount').value = '0';
            document.getElementById('amountReceived').value = '0';
            document.getElementById('notes').value = '';
            document.getElementById('proceduresList').innerHTML = '';
            document.getElementById('totalsBox').style.display = 'none';
        }

        function showAlert(message, type) {
            const container = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            container.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
        }

        loadClinics();
    </script>
</body>
</html>
