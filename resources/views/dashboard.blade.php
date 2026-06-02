<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - مجمع نبض الطبي</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header h1 { font-size: 32px; margin-bottom: 10px; }
        .header p { opacity: 0.9; font-size: 16px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); cursor: pointer; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
        .stat-card h3 { color: #667eea; font-size: 36px; margin-bottom: 10px; }
        .stat-card p { color: #666; font-size: 14px; }
        .stat-card .icon { font-size: 48px; opacity: 0.2; float: left; }
        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .chart-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .chart-card h3 { color: #333; margin-bottom: 20px; font-size: 18px; }
        .chart-container { position: relative; height: 300px; }
        .tables-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; }
        .table-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .table-card h3 { color: #333; margin-bottom: 20px; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 12px; text-align: right; color: #666; font-weight: 600; border-bottom: 2px solid #e9ecef; }
        td { padding: 12px; border-bottom: 1px solid #e9ecef; color: #333; }
        tr:hover { background: #f8f9fa; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .loading { text-align: center; padding: 40px; color: #666; }
        .btn { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; transition: background 0.3s; }
        .btn:hover { background: #5568d3; }
        .back-btn { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <button class="btn back-btn" onclick="window.history.back()">← رجوع</button>
        
        <div class="header">
            <h1>لوحة التحكم</h1>
            <p>نظرة شاملة على أداء المجمع الطبي</p>
        </div>

        <div class="stats-grid" id="statsGrid">
            <div class="loading">جاري تحميل البيانات...</div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <h3>الزيارات خلال 6 أشهر</h3>
                
            </div>
            <div class="chart-card">
                <h3>الإيرادات الشهرية</h3>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="tables-grid">
            <div class="table-card">
                <h3>أكثر الأطباء نشاطاً</h3>
                <table id="doctorsTable">
                    <thead>
                        <tr>
                            <th>الطبيب</th>
                            <th>العيادة</th>
                            <th>عدد الزيارات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="3" class="loading">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="table-card">
                <h3>المواعيد القادمة (24 ساعة)</h3>
                <table id="appointmentsTable">
                    <thead>
                        <tr>
                            <th>المريض</th>
                            <th>الطبيب</th>
                            <th>الوقت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="3" class="loading">جاري التحميل...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        async function loadDashboard() {
            try {
                const [statsRes, revenueRes, doctorsRes, appointmentsRes, chartsRes] = await Promise.all([
                    fetch('/api/dashboard/stats', { headers: { 'Authorization': `Bearer ${token}` } }),
                    fetch('/api/dashboard/revenue', { headers: { 'Authorization': `Bearer ${token}` } }),
                    fetch('/api/dashboard/top-doctors', { headers: { 'Authorization': `Bearer ${token}` } }),
                    fetch('/api/dashboard/appointments', { headers: { 'Authorization': `Bearer ${token}` } }),
                    fetch('/api/dashboard/charts', { headers: { 'Authorization': `Bearer ${token}` } })
                ]);

                const stats = await statsRes.json();
                const revenue = await revenueRes.json();
                const doctors = await doctorsRes.json();
                const appointments = await appointmentsRes.json();
                const charts = await chartsRes.json();

                renderStats(stats, revenue);
                renderDoctors(doctors.doctors || []);
                renderAppointments(appointments.appointments || []);
                renderCharts(charts);
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        function renderStats(stats, revenue) {
            document.getElementById('statsGrid').innerHTML = `
                <div class="stat-card" onclick="window.location.href='/patients?date=today'">
                    <div class="icon">👥</div>
                    <h3>${stats.patients_today}</h3>
                    <p>مرضى اليوم</p>
                </div>
                <div class="stat-card" onclick="window.location.href='/patients?date=today'">
                    <div class="icon">🏥</div>
                    <h3>${stats.examinations_today}</h3>
                    <p>معاينات اليوم</p>
                </div>
                <div class="stat-card" onclick="window.location.href='/patients?date=today'">
                    <div class="icon">🔄</div>
                    <h3>${stats.reviews_today}</h3>
                    <p>مراجعات اليوم</p>
                </div>
                <div class="stat-card" onclick="window.location.href='/finance'">
                    <div class="icon">💰</div>
                    <h3>${stats.revenue_today} ل.س</h3>
                    <p>إيراد اليوم</p>
                </div>
                <div class="stat-card" onclick="window.location.href='/finance'">
                    <div class="icon">📊</div>
                    <h3>${revenue.monthly_total} ل.س</h3>
                    <p>إيراد الشهر</p>
                </div>
                <div class="stat-card" onclick="window.location.href='/finance'">
                    <div class="icon">🏢</div>
                    <h3>${revenue.complex_share} ل.س</h3>
                    <p>حصة المجمع</p>
                </div>
            `;
        }

        function renderDoctors(doctors) {
            const tbody = document.querySelector('#doctorsTable tbody');
            if (doctors.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="loading">لا توجد بيانات</td></tr>';
                return;
            }
            tbody.innerHTML = doctors.map(doc => `
                <tr onclick="window.location.href='/patients?doctor_id=${doc.id}'" style="cursor: pointer;">
                    <td>${doc.full_name}</td>
                    <td>${doc.clinic || '-'}</td>
                    <td><span class="badge badge-info">${doc.visits_count}</span></td>
                </tr>
            `).join('');
        }

        function renderAppointments(appointments) {
            const tbody = document.querySelector('#appointmentsTable tbody');
            if (appointments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="loading">لا توجد مواعيد</td></tr>';
                return;
            }
            tbody.innerHTML = appointments.map(apt => `
                <tr onclick="window.location.href='/appointments'" style="cursor: pointer;">
                    <td>${apt.patient_name}</td>
                    <td>${apt.doctor_name}</td>
                    <td><span class="badge badge-success">${new Date(apt.appointment_date).toLocaleString('ar-SA')}</span></td>
                </tr>
            `).join('');
        }

        function renderCharts(charts) {
            const visitsCtx = document.getElementById('visitsChart').getContext('2d');
            new Chart(visitsCtx, {
                type: 'bar',
                data: {
                    labels: charts.visits_by_month.map(v => v.month),
                    datasets: [
                        { label: 'معاينات', data: charts.visits_by_month.map(v => v.examinations), backgroundColor: '#667eea' },
                        { label: 'مراجعات', data: charts.visits_by_month.map(v => v.reviews), backgroundColor: '#764ba2' }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: charts.revenue_by_month.map(r => r.month),
                    datasets: [{
                        label: 'الإيرادات',
                        data: charts.revenue_by_month.map(r => r.revenue),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        loadDashboard();
    </script>
</body>
</html>
