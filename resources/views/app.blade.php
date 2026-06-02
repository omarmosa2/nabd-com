<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مجمع نبض الطبي</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; }
        
        /* Login Page */
        #loginPage { display: none; min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); align-items: center; justify-content: center; }
        #loginPage.active { display: flex; }
        .login-container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); width: 100%; max-width: 400px; }
        .login-container h1 { color: #667eea; text-align: center; margin-bottom: 30px; font-size: 28px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; }
        .form-group input { width: 100%; padding: 12px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .btn-login { width: 100%; padding: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4); }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
        
        /* App Layout */
        #appLayout { display: none; }
        #appLayout.active { display: flex; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; position: fixed; right: 0; top: 0; height: 100vh; overflow-y: auto; box-shadow: -2px 0 10px rgba(0,0,0,0.1); z-index: 1000; }
        .sidebar-header { padding: 30px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .sidebar-header h2 { font-size: 20px; margin-bottom: 5px; }
        .sidebar-header p { font-size: 12px; opacity: 0.8; }
        .sidebar-menu { list-style: none; padding: 20px 0; }
        .sidebar-menu li { margin: 5px 0; }
        .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: white; text-decoration: none; transition: all 0.3s; border-right: 3px solid transparent; }
        .sidebar-menu a:hover { background: rgba(255,255,255,0.1); border-right-color: white; }
        .sidebar-menu a.active { background: rgba(255,255,255,0.2); border-right-color: white; }
        .sidebar-menu .icon { font-size: 20px; width: 24px; text-align: center; }
        
        /* Main Content */
        .main-content { flex: 1; margin-right: 260px; min-height: 100vh; }
        
        /* Topbar */
        .topbar { background: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100; }
        .topbar h1 { color: #333; font-size: 24px; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info span { color: #666; font-size: 14px; }
        .btn-logout { padding: 8px 20px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; }
        .btn-logout:hover { background: #5568d3; }
        
        /* Page Content */
        .page-content { padding: 30px; }
        .page { display: none; }
        .page.active { display: block; }
        
        /* Common Components */
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card h2 { color: #333; margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); cursor: pointer; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
        .stat-card h3 { color: #667eea; font-size: 32px; margin-bottom: 10px; }
        .stat-card p { color: #666; font-size: 14px; }
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; }
        .btn:hover { background: #5568d3; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .btn-secondary { background: #6c757d; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8f9fa; padding: 15px; text-align: right; color: #666; font-weight: 600; border-bottom: 2px solid #e9ecef; }
        td { padding: 15px; border-bottom: 1px solid #e9ecef; color: #333; }
        tr:hover { background: #f8f9fa; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .loading { text-align: center; padding: 40px; color: #666; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .form-group-inline { display: flex; flex-direction: column; }
        .form-group-inline label { color: #333; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        .form-group-inline input, .form-group-inline select, .form-group-inline textarea { padding: 12px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; }
        .form-group-inline input:focus, .form-group-inline select:focus, .form-group-inline textarea:focus { outline: none; border-color: #667eea; }
        
        /* Filters */
        .filters { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; flex: 1; min-width: 200px; }
        .filter-group label { color: #333; font-weight: 600; margin-bottom: 5px; font-size: 12px; }
        .filter-group input, .filter-group select { padding: 10px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; }
        .filter-group input:focus, .filter-group select:focus { outline: none; border-color: #667eea; }
        
        /* Pagination */
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
        
        .btn-sm { padding: 8px 16px; font-size: 12px; }
        .btn-group { display: flex; gap: 10px; margin-bottom: 20px; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: #666; }
        .empty-state .icon { font-size: 64px; margin-bottom: 20px; opacity: 0.3; }
        .empty-state h3 { font-size: 20px; margin-bottom: 10px; }
        
        .badge-warning { background: #fff3cd; color: #856404; }
        
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        
        .form-group input:read-only { background: #f8f9fa; }

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
    </style>
</head>
<body>
    <!-- Login Page -->
    <div id="loginPage">
        <div class="login-container">
            <h1>مجمع نبض الطبي</h1>
            <div id="loginAlert"></div>
            <form id="loginForm">
                <div class="form-group">
                    <label>البريد الإلكتروني</label>
                    <input type="email" id="loginEmail" required>
                </div>
                <div class="form-group">
                    <label>كلمة المرور</label>
                    <input type="password" id="loginPassword" required>
                </div>
                <button type="submit" class="btn-login">تسجيل الدخول</button>
            </form>
        </div>
    </div>

    <!-- App Layout -->
    <div id="appLayout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>مجمع نبض الطبي</h2>
                <p>نظام إدارة المجمع</p>
            </div>
            <ul class="sidebar-menu" id="sidebarMenu"></ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <h1 id="pageTitle">لوحة التحكم</h1>
                <div class="user-info">
                    <span id="userName">المستخدم</span>
                    <button class="btn-logout" onclick="logout()">تسجيل الخروج</button>
                </div>
            </div>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Dashboard Page -->
                <div id="dashboardPage" class="page">
                    <div class="stats-grid" id="dashboardStats"></div>
                    <div class="card">
                        <h2>الزيارات خلال 6 أشهر</h2>
                    </div>
                </div>

                <!-- Reception Page -->
                <div id="receptionPage" class="page">
                    <div class="card">
                        <h2>تسجيل زيارة جديدة</h2>
                        <div class="form-grid">
                            <div class="form-group-inline">
                                <label>رقم الملف</label>
                                <input type="text" id="receptionFileNumber" placeholder="اتركه فارغاً لمريض جديد">
                            </div>
                            <div class="form-group-inline">
                                <label>الاسم الكامل *</label>
                                <input type="text" id="receptionFullName" required>
                            </div>
                            <div class="form-group-inline">
                                <label>العمر *</label>
                                <input type="number" id="receptionAge" required>
                            </div>
                            <div class="form-group-inline">
                                <label>الجنس *</label>
                                <select id="receptionGender" required>
                                    <option value="">اختر...</option>
                                    <option value="male">ذكر</option>
                                    <option value="female">أنثى</option>
                                </select>
                            </div>
                            <div class="form-group-inline">
                                <label>الهاتف *</label>
                                <input type="tel" id="receptionPhone" required>
                            </div>
                        </div>
                        <button class="btn" onclick="searchPatient()">بحث عن المريض</button>
                    </div>
                    <div class="card">
                        <h2>بيانات الزيارة</h2>
                        <div class="form-grid">
                            <div class="form-group-inline">
                                <label>العيادة *</label>
                                <select id="receptionClinic" onchange="loadDoctors()"></select>
                            </div>
                            <div class="form-group-inline">
                                <label>الطبيب *</label>
                                <select id="receptionDoctor"></select>
                            </div>
                            <div class="form-group-inline">
                                <label>نوع الزيارة *</label>
                                <select id="receptionVisitType">
                                    <option value="examination">معاينة</option>
                                    <option value="review">مراجعة</option>
                                </select>
                            </div>
                            <div class="form-group-inline">
                                <label>رسوم المعاينة</label>
                                <input type="number" id="receptionFee" step="0.01">
                            </div>
                        </div>
                        <button class="btn btn-success" onclick="createVisit()">حفظ الزيارة</button>
                    </div>
                </div>

                <!-- Patients Page -->
                <div id="patientsPage" class="page">
                    <div class="card">
                        <h2>البحث والفلاتر</h2>
                        <div class="filters">
                            <div class="filter-group">
                                <label>بحث</label>
                                <input type="text" id="patientsSearch" placeholder="ابحث بالاسم أو رقم الملف أو الهاتف..." onkeyup="spa.debouncePatientsSearch()">
                            </div>
                            <div class="filter-group">
                                <label>العيادة</label>
                                <select id="patientsClinicFilter" onchange="spa.loadPatients()">
                                    <option value="">جميع العيادات</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>الطبيب</label>
                                <select id="patientsDoctorFilter" onchange="spa.loadPatients()">
                                    <option value="">جميع الأطباء</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>الجنس</label>
                                <select id="patientsGenderFilter" onchange="spa.loadPatients()">
                                    <option value="">الكل</option>
                                    <option value="male">ذكر</option>
                                    <option value="female">أنثى</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>من تاريخ</label>
                                <input type="date" id="patientsDateFrom" onchange="spa.loadPatients()">
                            </div>
                            <div class="filter-group">
                                <label>إلى تاريخ</label>
                                <input type="date" id="patientsDateTo" onchange="spa.loadPatients()">
                            </div>
                        </div>
                        <button class="btn btn-secondary" onclick="spa.resetPatientsFilters()">إعادة تعيين الفلاتر</button>
                    </div>

                    <div class="card">
                        <div class="btn-group">
                            <button class="btn btn-success" onclick="spa.openAddPatientModal()">+ إضافة مريض جديد</button>
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
                        <div class="pagination" id="patientsPagination"></div>
                    </div>
                </div>

                <!-- Patients Modal -->
                <div class="modal" id="patientsModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 id="patientsModalTitle">إضافة مريض جديد</h2>
                            <button class="modal-close" onclick="spa.closePatientsModal()">×</button>
                        </div>
                        <form id="patientsForm" onsubmit="spa.savePatient(event)">
                            <input type="hidden" id="patientId">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>رقم الملف</label>
                                    <input type="text" id="patientFileNumber" readonly>
                                </div>
                                <div class="form-group">
                                    <label>الاسم الكامل *</label>
                                    <input type="text" id="patientFullName" required>
                                </div>
                                <div class="form-group">
                                    <label>العمر *</label>
                                    <input type="number" id="patientAge" min="0" max="150" required>
                                </div>
                                <div class="form-group">
                                    <label>الجنس *</label>
                                    <select id="patientGender" required>
                                        <option value="">اختر...</option>
                                        <option value="male">ذكر</option>
                                        <option value="female">أنثى</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>مكان الإقامة</label>
                                    <input type="text" id="patientResidence">
                                </div>
                                <div class="form-group">
                                    <label>رقم الهاتف *</label>
                                    <input type="tel" id="patientPhone" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success">حفظ</button>
                            <button type="button" class="btn btn-secondary" onclick="spa.closePatientsModal()">إلغاء</button>
                        </form>
                    </div>
                </div>

                <!-- Patients Drawer -->
                <div class="drawer" id="patientsDrawer">
                    <div class="drawer-header">
                        <button class="drawer-close" onclick="spa.closePatientsDrawer()">×</button>
                        <h2 id="patientsDrawerTitle">تفاصيل المريض</h2>
                        <p id="patientsDrawerSubtitle">معلومات شاملة</p>
                    </div>
                    <div class="drawer-content" id="patientsDrawerContent">
                        <div class="loading">جاري التحميل...</div>
                    </div>
                </div>

                <!-- Patients View Modal -->
                <div class="modal" id="patientsViewModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h2 id="patientsViewModalTitle">تفاصيل المريض</h2>
                                <p id="patientsViewModalSubtitle" style="color: #666; font-size: 14px; margin-top: 5px;"></p>
                            </div>
                            <button class="modal-close" onclick="spa.closeViewModal()">×</button>
                        </div>
                        <div id="patientsViewModalContent">
                            <div class="loading">جاري التحميل...</div>
                        </div>
                    </div>
                </div>

                <!-- Finance Page -->
                <div id="financePage" class="page">
                    <div class="stats-grid" id="financeStats"></div>
                    <div class="card">
                        <h2>ملخص الأطباء</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>الطبيب</th>
                                    <th>الزيارات</th>
                                    <th>الحصة</th>
                                    <th>الخصومات</th>
                                    <th>الصافي</th>
                                </tr>
                            </thead>
                            <tbody id="financeTable"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Reports Page -->
                <div id="reportsPage" class="page">
                    <div class="card">
                        <h2>التقارير</h2>
                        <p>صفحة التقارير قيد التطوير...</p>
                    </div>
                </div>

                <!-- Appointments Page -->
                <div id="appointmentsPage" class="page">
                    <div class="card">
                        <h2>المواعيد</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>المريض</th>
                                    <th>الطبيب</th>
                                    <th>التاريخ</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody id="appointmentsTable"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Settings Page -->
                <div id="settingsPage" class="page">
                    <div class="card">
                        <h2>الإعدادات</h2>
                        <p>صفحة الإعدادات قيد التطوير...</p>
                    </div>
                </div>

                <!-- Unauthorized Page -->
                <div id="unauthorizedPage" class="page">
                    <div class="card" style="text-align: center; padding: 60px;">
                        <h1 style="font-size: 80px; margin-bottom: 20px;">🚫</h1>
                        <h2 style="color: #dc3545; margin-bottom: 20px;">غير مصرح بالدخول</h2>
                        <p style="color: #666; margin-bottom: 30px;">ليس لديك صلاحية للوصول إلى هذه الصفحة</p>
                        <button class="btn" onclick="navigate('/dashboard')">العودة للرئيسية</button>
                    </div>
                </div>
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

    <script src="spa-router.js"></script>
</body>
</html>
