<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مجمع نبض الطبي</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
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

        /* Medical UI refresh */
        :root {
            --page-bg: #F5F7FA;
            --sidebar-bg: #1E2A5E;
            --sidebar-bg-soft: #27376f;
            --primary: #2563eb;
            --primary-soft: #EFF6FF;
            --success: #0f9f6e;
            --success-dark: #0b7f59;
            --text-main: #111827;
            --text-muted: #6b7280;
            --line: #e5e7eb;
            --card-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        }

        body {
            font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
            background: var(--page-bg);
            color: var(--text-main);
            font-size: 14px;
        }

        #loginPage {
            background: var(--page-bg);
        }

        .login-container {
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }

        .login-container h1,
        .topbar h1,
        .card h2,
        .modal-header h2,
        .drawer-header h2 {
            letter-spacing: 0;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            background: var(--sidebar-bg);
            box-shadow: -8px 0 30px rgba(30, 42, 94, 0.16);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 26px 22px;
            text-align: right;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(255,255,255,0.12);
            color: #ffffff;
            font-size: 22px;
            flex-shrink: 0;
        }

        .sidebar-header h2 {
            font-size: 18px;
            line-height: 1.3;
        }

        .sidebar-header p {
            color: rgba(255,255,255,0.72);
            opacity: 1;
        }

        .sidebar-menu {
            flex: 1;
            padding: 18px 12px;
        }

        .sidebar-menu li {
            margin: 4px 0;
        }

        .sidebar-menu a {
            min-height: 44px;
            border-radius: 8px;
            border-right: 0;
            color: rgba(255,255,255,0.82);
            font-weight: 600;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.12);
            color: #ffffff;
            border-right-color: transparent;
        }

        .sidebar-footer {
            padding: 16px 14px 22px;
            border-top: 1px solid rgba(255,255,255,0.12);
        }

        .btn-logout {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 8px;
            color: #ffffff;
            font-family: inherit;
        }

        .btn-logout:hover {
            background: rgba(255,255,255,0.2);
        }

        .main-content {
            background: var(--page-bg);
        }

        .topbar {
            padding: 18px 34px;
            border-bottom: 1px solid var(--line);
            box-shadow: none;
        }

        .topbar h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-main);
        }

        .user-info {
            gap: 12px;
        }

        .navbar-icon {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #ffffff;
            color: var(--text-muted);
            font-size: 18px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 800;
        }

        .user-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .user-meta strong {
            font-size: 14px;
            color: var(--text-main);
        }

        .user-meta small {
            font-size: 12px;
            color: var(--text-muted);
        }

        .page-content {
            padding: 28px 34px;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--card-shadow);
        }

        .card h2 {
            font-size: 18px;
            color: var(--text-main);
            border-bottom-color: var(--primary-soft);
        }

        .btn {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 8px;
            font-family: inherit;
            transition: background 0.2s, border-color 0.2s, color 0.2s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: none;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.12);
        }

        .btn-success {
            background: var(--success);
        }

        .btn-success:hover {
            background: var(--success-dark);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid var(--line);
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            box-shadow: none;
        }

        .patients-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .patients-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .patients-search {
            min-width: 280px;
            flex: 1;
            max-width: 460px;
        }

        .patients-search input {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #ffffff;
            padding: 10px 14px;
            color: var(--text-main);
            font-family: inherit;
            font-size: 14px;
        }

        .patients-search input:focus,
        .filter-group input:focus,
        .filter-group select:focus,
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus,
        .form-group-inline input:focus,
        .form-group-inline select:focus,
        .form-group-inline textarea:focus {
            outline: none;
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .filters.compact-filters {
            display: none;
            padding: 16px;
            margin: 0 0 18px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f9fafb;
        }

        .filters.compact-filters.open {
            display: flex;
        }

        .patients-table-card {
            padding: 0;
            overflow: hidden;
        }

        .patients-table-wrap {
            overflow-x: auto;
        }

        table {
            min-width: 860px;
        }

        th {
            background: #f8fafc;
            color: #374151;
            font-size: 14px;
            font-weight: 700;
            border-bottom: 1px solid var(--line);
        }

        td {
            font-size: 14px;
            color: #1f2937;
            border-bottom: 1px solid #eef2f7;
        }

        tbody tr {
            cursor: default;
        }

        tr:hover {
            background: #f8fbff;
        }

        .serial-cell {
            color: var(--text-muted);
            font-weight: 700;
            width: 58px;
        }

        .patient-number {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 8px;
            background: var(--primary-soft);
            color: #1d4ed8;
            font-weight: 700;
        }

        .status-badge,
        .gender-badge,
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 26px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .gender-male {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .gender-female {
            background: #fce7f3;
            color: #be185d;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-new {
            background: #fef9c3;
            color: #854d0e;
        }

        .row-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .icon-btn {
            width: 34px;
            height: 34px;
            min-height: 34px;
            padding: 0;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #ffffff;
            color: #374151;
            font-size: 15px;
        }

        .icon-btn:hover {
            background: var(--primary-soft);
            color: var(--primary);
            box-shadow: none;
        }

        .icon-btn.danger:hover {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .pagination {
            padding: 18px;
            border-top: 1px solid var(--line);
            margin-top: 0;
        }

        .pagination button {
            border-radius: 8px;
            font-family: inherit;
        }

        .pagination button.active {
            background: var(--primary);
        }

        .modal-content,
        .confirm-content {
            border-radius: 8px;
            box-shadow: var(--card-shadow);
        }

        .drawer {
            width: min(620px, 92vw);
        }

        .drawer-header {
            background: var(--sidebar-bg);
        }

        .visit-card {
            background: #f8fafc;
            border-right-color: var(--primary);
        }

        @media (max-width: 900px) {
            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-right: 220px;
            }

            .topbar,
            .page-content {
                padding-left: 18px;
                padding-right: 18px;
            }
        }

        @media (max-width: 720px) {
            #appLayout.active {
                display: block;
            }

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                min-height: 0;
            }

            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding: 12px;
            }

            .sidebar-menu li {
                flex: 0 0 auto;
            }

            .sidebar-footer {
                display: none;
            }

            .main-content {
                margin-right: 0;
            }

            .topbar {
                align-items: flex-start;
                gap: 14px;
                flex-direction: column;
            }

            .patients-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .patients-search {
                max-width: none;
                min-width: 0;
            }
        }
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
                <span class="brand-icon" aria-hidden="true">🏥</span>
                <div>
                    <h2>مجمع نبض الطبي</h2>
                    <p>نظام إدارة المجمع</p>
                </div>
            </div>
            <ul class="sidebar-menu" id="sidebarMenu"></ul>
            <div class="sidebar-footer">
                <button class="btn-logout" onclick="logout()">
                    <span aria-hidden="true">↩</span>
                    <span>تسجيل الخروج</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <h1 id="pageTitle">لوحة التحكم</h1>
                <div class="user-info">
                    <button class="navbar-icon" title="الإشعارات" aria-label="الإشعارات">🔔</button>
                    <span class="user-avatar" id="userAvatar" aria-hidden="true">م</span>
                    <span class="user-meta">
                        <strong id="userName">المستخدم</strong>
                        <small id="userRole">مستخدم النظام</small>
                    </span>
                </div>
            </div>

            <!-- Page Content -->
            <div class="page-content">
                <div id="pageAlert"></div>
                <!-- Dashboard Page -->
                <div id="dashboardPage" class="page">
                    <div class="stats-grid" id="dashboardStats"></div>
                    <div class="card">
                        <h2>الزيارات خلال 6 أشهر</h2>
                        <div class="chart-container">
                            <canvas id="visitsChart"></canvas>
                        </div>
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
                    <div class="patients-toolbar">
                        <div class="patients-actions">
                            <button class="btn btn-success" onclick="spa.openAddPatientModal()">
                                <span aria-hidden="true">➕</span>
                                <span>إضافة مريض جديد</span>
                            </button>
                            <button class="btn btn-secondary" onclick="spa.togglePatientsFilters()">
                                <span aria-hidden="true">🔍</span>
                                <span>تصفية</span>
                            </button>
                        </div>
                        <div class="patients-search">
                            <input type="text" id="patientsSearch" placeholder="البحث برقم المريض..." onkeyup="spa.debouncePatientsSearch()">
                        </div>
                    </div>

                    <div class="filters compact-filters" id="patientsFiltersPanel">
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
                        <button class="btn btn-secondary" onclick="spa.resetPatientsFilters()">إعادة تعيين</button>
                    </div>

                    <div class="card patients-table-card">
                        <div class="patients-table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>رقم المريض</th>
                                        <th>الجنس</th>
                                        <th>العمر</th>
                                        <th>رقم الهاتف</th>
                                        <th>حالة المريض</th>
                                        <th>تاريخ الإضافة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="patientsTable">
                                    <tr><td colspan="8" class="loading">جاري التحميل...</td></tr>
                                </tbody>
                            </table>
                        </div>
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
