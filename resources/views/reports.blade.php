<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقارير - مجمع نبض الطبي</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; background: white; padding: 15px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .tab { padding: 12px 30px; background: #f8f9fa; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; color: #666; transition: all 0.3s; }
        .tab.active { background: #667eea; color: white; }
        .tab:hover:not(.active) { background: #e9ecef; }
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card h2 { color: #333; margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .filters { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { color: #333; font-weight: 600; margin-bottom: 5px; font-size: 12px; }
        .filter-group input, .filter-group select { padding: 10px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; }
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s; }
        .btn:hover { background: #5568d3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-group { display: flex; gap: 10px; margin-bottom: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); text-align: center; }
        .stat-card h3 { color: #667eea; font-size: 32px; margin-bottom: 10px; }
        .stat-card p { color: #666; font-size: 14px; }
        .chart-container { position: relative; height: 400px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px; text-align: right; color: #666; font-weight: 600; border-bottom: 2px solid #e9ecef; }
        td { padding: 15px; border-bottom: 1px solid #e9ecef; color: #333; }
        tr:hover { background: #f8f9fa; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .loading { text-align: center; padding: 40px; color: #666; }
        .report-section { display: none; }
        .report-section.active { display: block; }
        .back-btn { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <button class="btn btn-secondary back-btn" onclick="window.history.back()">← رجوع</button>
        
        <div class="header">
            <h1>التقارير</h1>
            <p>تحليلات وتقارير شاملة</p>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('daily')">تقرير يومي</button>
            <button class="tab" onclick="switchTab('monthly')">تقرير شهري</button>
            <button class="tab" onclick="switchTab('patients')">تقرير المرضى</button>
            <button class="tab" onclick="switchTab('visits')">تقرير الزيارات</button>
            <button class="tab" onclick="switchTab('finance')">تقرير مالي</button>
        </div>

        <div id="daily" class="report-section active">
            <div class="card">
                <h2>التقرير اليومي</h2>
                <div class="filters">
                    <div class="filter-group">
                        <label>التاريخ</label>
                        <input type="date" id="dailyDate" value="">
                    </div>
                    <button class="btn" onclick="loadDailyReport()">عرض التقرير</button>
                </div>
                <div id="dailyContent">
                    <div class="loading">اختر تاريخاً واضغط عرض التقرير</div>
                </div>
            </div>
        </div>

        <div id="monthly" class="report-section">
            <div class="card">
                <h2>التقرير الشهري</h2>
                <div class="filters">
                    <div class="filter-group">
                        <label>الشهر</label>
                        <input type="month" id="monthlyDate" value="">
                    </div>
                    <button class="btn" onclick="loadMonthlyReport()">عرض التقرير</button>
                </div>
                <div id="monthlyContent">
                    <div class="loading">اختر شهراً واضغط عرض التقرير</div>
                </div>
            </div>
        </div>

        <div id="patients" class="report-section">
            <div class="card">
                <h2>تقرير المرضى</h2>
                <div class="btn-group">
                    <button class="btn btn-success" onclick="exportReport('patients', 'excel')">تصدير Excel</button>
                    <button class="btn" onclick="exportReport('patients', 'pdf')">تصدير PDF</button>
                </div>
                <div id="patientsContent">
                    <div class="loading">جاري التحميل...</div>
                </div>
            </div>
        </div>

        <div id="visits" class="report-section">
            <div class="card">
                <h2>تقرير الزيارات</h2>
                <div class="btn-group">
                    <button class="btn btn-success" onclick="exportReport('visits', 'excel')">تصدير Excel</button>
                    <button class="btn" onclick="exportReport('visits', 'pdf')">تصدير PDF</button>
                </div>
                <div id="visitsContent">
                    <div class="loading">جاري التحميل...</div>
                </div>
            </div>
        </div>

        <div id="finance" class="report-section">
            <div class="card">
                <h2>التقرير المالي</h2>
                <div class="btn-group">
                    <button class="btn btn-success" onclick="exportReport('finance', 'excel')">تصدير Excel</button>
                    <button class="btn" onclick="exportReport('finance', 'pdf')">تصدير PDF</button>
                </div>
                <div id="financeContent">
                    <div class="loading">جاري التحميل...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        const today = new Date();
        document.getElementById('dailyDate').value = today.toISOString().split('T')[0];
        document.getElementById('monthlyDate').value = today.toISOString().slice(0, 7);

        function switchTab(tabName) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.report-section').forEach(s => s.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tabName).classList.add('active');

            if (tabName === 'patients') loadPatientsReport();
            if (tabName === 'visits') loadVisitsReport();
            if (tabName === 'finance') loadFinanceReport();
        }

        async function loadDailyReport() {
            const date = document.getElementById('dailyDate').value;
            if (!date) return;

            document.getElementById('dailyContent').innerHTML = '<div class="loading">جاري التحميل...</div>';

            try {
                const res = await fetch(`/api/reports/daily?date=${date}`, { 
                    headers: { 'Authorization': `Bearer ${token}` } 
                });
                const data = await res.json();
                renderDailyReport(data);
            } catch (error) {
                document.getElementById('dailyContent').innerHTML = '<div class="loading">خطأ في التحميل</div>';
            }
        }

        function renderDailyReport(data) {
            let html = `
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>${data.total_visits}</h3>
                        <p>إجمالي الزيارات</p>
                    </div>
                    <div class="stat-card">
                        <h3>${data.total_revenue} ل.س</h3>
                        <p>إجمالي الإيراد</p>
                    </div>
                    <div class="stat-card">
                        <h3>${data.examinations}</h3>
                        <p>معاينات</p>
                    </div>
                    <div class="stat-card">
                        <h3>${data.reviews}</h3>
                        <p>مراجعات</p>
                    </div>
                </div>
            `;

            if (data.doctor_breakdown && data.doctor_breakdown.length > 0) {
                html += '<h3 style="margin-bottom: 15px;">تفصيل حسب الطبيب</h3>';
                html += '<table><thead><tr><th>الطبيب</th><th>عدد الزيارات</th><th>الإيراد</th></tr></thead><tbody>';
                html += data.doctor_breakdown.map(d => `
                    <tr>
                        <td>${d.doctor_name}</td>
                        <td><span class="badge badge-info">${d.visits_count}</span></td>
                        <td>${d.revenue} ل.س</td>
                    </tr>
                `).join('');
                html += '</tbody></table>';
            }

            document.getElementById('dailyContent').innerHTML = html;
        }

        async function loadMonthlyReport() {
            const month = document.getElementById('monthlyDate').value;
            if (!month) return;

            document.getElementById('monthlyContent').innerHTML = '<div class="loading">جاري التحميل...</div>';

            try {
                const res = await fetch(`/api/reports/monthly?month=${month}`, { 
                    headers: { 'Authorization': `Bearer ${token}` } 
                });
                const data = await res.json();
                renderMonthlyReport(data);
            } catch (error) {
                document.getElementById('monthlyContent').innerHTML = '<div class="loading">خطأ في التحميل</div>';
            }
        }

        function renderMonthlyReport(data) {
            let html = `
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>${data.total_visits}</h3>
                        <p>إجمالي الزيارات</p>
                    </div>
                    <div class="stat-card">
                        <h3>${data.total_revenue} ل.س</h3>
                        <p>إجمالي الإيراد</p>
                    </div>
                    <div class="stat-card">
                        <h3>${data.examinations}</h3>
                        <p>معاينات</p>
                    </div>
                    <div class="stat-card">
                        <h3>${data.reviews}</h3>
                        <p>مراجعات</p>
                    </div>
                </div>
            `;

            if (data.daily_breakdown && data.daily_breakdown.length > 0) {
                html += '<h3 style="margin-bottom: 15px;">التفصيل اليومي</h3>';
                html += '<div class="chart-container"><canvas id="dailyChart"></canvas></div>';
                
                setTimeout(() => {
                    const ctx = document.getElementById('dailyChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.daily_breakdown.map(d => d.date),
                            datasets: [
                                { label: 'عدد الزيارات', data: data.daily_breakdown.map(d => d.visits_count), backgroundColor: '#667eea' },
                                { label: 'الإيراد', data: data.daily_breakdown.map(d => d.revenue), backgroundColor: '#764ba2' }
                            ]
                        },
                        options: { responsive: true, maintainAspectRatio: false }
                    });
                }, 100);
            }

            if (data.doctor_breakdown && data.doctor_breakdown.length > 0) {
                html += '<h3 style="margin-top: 30px; margin-bottom: 15px;">تفصيل حسب الطبيب</h3>';
                html += '<table><thead><tr><th>الطبيب</th><th>عدد الزيارات</th><th>الإيراد</th></tr></thead><tbody>';
                html += data.doctor_breakdown.map(d => `
                    <tr>
                        <td>${d.doctor_name}</td>
                        <td><span class="badge badge-info">${d.visits_count}</span></td>
                        <td>${d.revenue} ل.س</td>
                    </tr>
                `).join('');
                html += '</tbody></table>';
            }

            document.getElementById('monthlyContent').innerHTML = html;
        }

        async function loadPatientsReport() {
            try {
                const res = await fetch('/api/reports/patients', { headers: { 'Authorization': `Bearer ${token}` } });
                const data = await res.json();
                renderPatientsReport(data);
            } catch (error) {
                document.getElementById('patientsContent').innerHTML = '<div class="loading">خطأ في التحميل</div>';
            }
        }

        function renderPatientsReport(data) {
            let html = `
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>${data.total_patients}</h3>
                        <p>إجمالي المرضى</p>
                    </div>
                    <div class="stat-card">
                        <h3>${data.new_this_month}</h3>
                        <p>مرضى جدد هذا الشهر</p>
                    </div>
                </div>
            `;

            if (data.top_patients && data.top_patients.length > 0) {
                html += '<h3 style="margin-bottom: 15px;">أكثر المرضى زيارة</h3>';
                html += '<table><thead><tr><th>رقم الملف</th><th>الاسم</th><th>الهاتف</th><th>عدد الزيارات</th></tr></thead><tbody>';
                html += data.top_patients.map(p => `
                    <tr>
                        <td><span class="badge badge-info">${p.file_number}</span></td>
                        <td>${p.full_name}</td>
                        <td>${p.phone}</td>
                        <td><span class="badge badge-success">${p.visits_count}</span></td>
                    </tr>
                `).join('');
                html += '</tbody></table>';
            }

            document.getElementById('patientsContent').innerHTML = html;
        }

        async function loadVisitsReport() {
            try {
                const res = await fetch('/api/reports/visits', { headers: { 'Authorization': `Bearer ${token}` } });
                const data = await res.json();
                renderVisitsReport(data);
            } catch (error) {
                document.getElementById('visitsContent').innerHTML = '<div class="loading">خطأ في التحميل</div>';
            }
        }

        function renderVisitsReport(data) {
            let html = `
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>${data.total_visits}</h3>
                        <p>إجمالي الزيارات</p>
                    </div>
                    <div class="stat-card">
                        <h3>${data.this_month}</h3>
                        <p>زيارات هذا الشهر</p>
                    </div>
                    <div class="stat-card">
                        <h3>${data.free_reviews}</h3>
                        <p>مراجعات مجانية</p>
                    </div>
                </div>
            `;

            document.getElementById('visitsContent').innerHTML = html;
        }

        async function loadFinanceReport() {
            try {
                const res = await fetch('/api/reports/finance', { headers: { 'Authorization': `Bearer ${token}` } });
                const data = await res.json();
                renderFinanceReport(data);
            } catch (error) {
                document.getElementById('financeContent').innerHTML = '<div class="loading">خطأ في التحميل</div>';
            }
        }

        function renderFinanceReport(data) {
            const summary = data.summary;
            let html = `
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>${summary.total_examination_fees} ل.س</h3>
                        <p>رسوم الكشف</p>
                    </div>
                    <div class="stat-card">
                        <h3>${summary.total_amount_received} ل.س</h3>
                        <p>المستلم</p>
                    </div>
                    <div class="stat-card">
                        <h3>${summary.total_center_share} ل.س</h3>
                        <p>حصة المجمع</p>
                    </div>
                    <div class="stat-card">
                        <h3>${summary.net_doctor_payable} ل.س</h3>
                        <p>صافي الأطباء</p>
                    </div>
                </div>
            `;

            document.getElementById('financeContent').innerHTML = html;
        }

        function exportReport(type, format) {
            alert(`تصدير ${type} بصيغة ${format.toUpperCase()} - قيد التطوير`);
        }

        loadDailyReport();
    </script>
</body>
</html>
