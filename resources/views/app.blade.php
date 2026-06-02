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

        /* Appointments Module */
        .view-toggle {
            display: inline-flex;
            background: #f3f4f6;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 3px;
            gap: 2px;
        }
        .view-toggle-btn {
            min-height: 34px;
            padding: 6px 14px;
            border: none;
            background: transparent;
            border-radius: 6px;
            cursor: pointer;
            font-family: inherit;
            font-size: 13px;
            color: #374151;
            font-weight: 600;
        }
        .view-toggle-btn.active {
            background: #ffffff;
            color: var(--primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .apt-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
        }
        .apt-status-pill::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255,255,255,0.9);
        }
        .apt-status-scheduled { background: #2563eb; }
        .apt-status-completed { background: #0f9f6e; }
        .apt-status-cancelled { background: #dc2626; }
        .apt-status-missed { background: #d97706; }
        .apt-status-converted_to_visit { background: #7c3aed; }

        .availability-hint {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 600;
        }
        .availability-hint.ok {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .availability-hint.conflict {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .availability-hint.checking {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .calendar-toolbar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 16px;
            border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
        }
        .calendar-toolbar h3 {
            margin: 0;
            color: var(--text-main);
            font-size: 18px;
            min-width: 180px;
            text-align: center;
        }
        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
        }
        .calendar-weekdays > div {
            padding: 12px;
            text-align: center;
            font-weight: 700;
            color: #374151;
            font-size: 13px;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            grid-auto-rows: minmax(110px, auto);
            gap: 1px;
            background: var(--line);
        }
        .calendar-day {
            background: #ffffff;
            padding: 8px;
            min-height: 110px;
            cursor: pointer;
            position: relative;
            transition: background 0.15s;
            display: flex;
            flex-direction: column;
        }
        .calendar-day:hover { background: #f8fbff; }
        .calendar-day.outside-month { background: #f9fafb; color: #9ca3af; }
        .calendar-day.today { background: #eff6ff; }
        .calendar-day.today .day-number { color: var(--primary); font-weight: 800; }
        .calendar-day-number {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
        }
        .calendar-day-count {
            display: inline-block;
            background: var(--primary);
            color: white;
            font-size: 11px;
            font-weight: 700;
            border-radius: 999px;
            padding: 2px 8px;
            align-self: flex-start;
        }
        .calendar-day.has-appointments .calendar-day-count { background: var(--success); }
        .calendar-day-preview {
            font-size: 11px;
            color: #4b5563;
            margin-top: 6px;
            line-height: 1.5;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        .calendar-day-preview .preview-item {
            display: block;
            padding: 2px 0;
            border-bottom: 1px dashed #e5e7eb;
        }

        .day-appointment-card {
            background: #f8fafc;
            border-right: 4px solid var(--primary);
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .day-appointment-time {
            font-weight: 800;
            color: var(--text-main);
            min-width: 80px;
        }
        .day-appointment-info { flex: 1; }
        .day-appointment-info strong { color: var(--text-main); display: block; }
        .day-appointment-info small { color: var(--text-muted); }

        .modal-actions {
            border-top: 1px solid var(--line);
            padding-top: 16px;
        }
        .modal-actions .btn { min-height: 36px; padding: 8px 16px; font-size: 13px; }

        /* Appointment Form Modal */
        .modal-content-wide {
            max-width: 820px;
            width: 95%;
            padding: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }

        .modal-header-gradient {
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            color: #ffffff;
            padding: 22px 28px;
            border-bottom: none;
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 0;
        }
        .modal-header-gradient h2 {
            color: #ffffff;
            font-size: 20px;
            margin: 0;
            font-weight: 700;
        }
        .modal-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255,255,255,0.18);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .modal-subtitle {
            color: rgba(255,255,255,0.85);
            font-size: 13px;
            margin-top: 2px;
        }
        .modal-header-gradient .modal-close {
            position: absolute;
            top: 18px;
            left: 18px;
            color: #ffffff;
            background: rgba(255,255,255,0.18);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            font-size: 22px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .modal-header-gradient .modal-close:hover {
            background: rgba(255,255,255,0.32);
            color: #ffffff;
        }
        .modal-content-wide > .modal-header-gradient { position: relative; }

        .apt-form {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }

        .apt-form-body {
            padding: 24px 28px;
            overflow-y: auto;
            flex: 1;
        }

        .apt-section {
            margin-bottom: 22px;
        }
        .apt-section:last-of-type {
            margin-bottom: 8px;
        }

        .apt-section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed var(--line);
        }
        .apt-section-icon {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: var(--primary-soft);
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .apt-section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
        }

        .apt-row {
            display: grid;
            gap: 16px;
        }
        .apt-row-2 { grid-template-columns: 1fr 1fr; }

        .apt-form .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .apt-form .form-group label {
            color: var(--text-main);
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .apt-form .form-group .req {
            color: #dc2626;
            font-weight: 700;
        }
        .apt-form .form-group input,
        .apt-form .form-group select,
        .apt-form .form-group textarea {
            padding: 10px 12px;
            border: 1.5px solid var(--line);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: #ffffff;
            color: var(--text-main);
            transition: border-color 0.15s, box-shadow 0.15s;
            min-height: 42px;
        }
        .apt-form .form-group textarea {
            min-height: 64px;
            resize: vertical;
            line-height: 1.5;
        }
        .apt-form .form-group input:focus,
        .apt-form .form-group select:focus,
        .apt-form .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .apt-form .form-group select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%236b7280' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: left 12px center;
            padding-left: 36px;
        }
        .apt-hint {
            color: #9ca3af;
            font-size: 12px;
        }

        .apt-duration-wrap {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .apt-duration-presets {
            display: flex;
            gap: 6px;
        }
        .apt-duration-presets button {
            flex: 1;
            min-height: 32px;
            padding: 0;
            background: #f3f4f6;
            border: 1px solid var(--line);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #4b5563;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.15s;
        }
        .apt-duration-presets button:hover {
            background: var(--primary-soft);
            border-color: var(--primary);
            color: var(--primary);
        }
        .apt-duration-presets button.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #ffffff;
        }

        .apt-form-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 28px;
            background: #f9fafb;
            border-top: 1px solid var(--line);
        }
        .apt-form-footer .btn { min-height: 42px; padding: 10px 22px; font-size: 14px; }
        .btn-ghost {
            background: transparent;
            color: #4b5563;
            border: 1px solid var(--line);
        }
        .btn-ghost:hover {
            background: #f3f4f6;
            color: #111827;
            box-shadow: none;
        }
        .btn-with-icon {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-with-icon[disabled] { opacity: 0.55; cursor: not-allowed; }

        @media (max-width: 640px) {
            .apt-row-2 { grid-template-columns: 1fr; }
            .apt-form-body { padding: 18px 18px; }
            .apt-form-footer { padding: 14px 18px; flex-direction: column-reverse; }
            .apt-form-footer .btn { width: 100%; }
            .modal-header-gradient { padding: 18px 20px; }
        }

        /* Clinics Module */
        .clinic-stat-card {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: var(--card-shadow);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .clinic-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }
        .clinic-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            flex-shrink: 0;
        }
        .clinic-stat-icon.primary { background: #dbeafe; color: #1d4ed8; }
        .clinic-stat-icon.success { background: #dcfce7; color: #166534; }
        .clinic-stat-icon.warning { background: #fef3c7; color: #92400e; }
        .clinic-stat-icon.danger { background: #fee2e2; color: #991b1b; }
        .clinic-stat-icon.purple { background: #ede9fe; color: #6d28d9; }
        .clinic-stat-meta { flex: 1; }
        .clinic-stat-value {
            font-size: 24px;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1;
        }
        .clinic-stat-label {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
            font-weight: 500;
        }

        .clinic-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
        }
        .clinic-status-pill::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255,255,255,0.95);
        }
        .clinic-status-active { background: #0f9f6e; }
        .clinic-status-inactive { background: #d97706; }
        .clinic-status-archived { background: #6b7280; }

        .clinic-row-archived { opacity: 0.6; background: #f9fafb !important; }
        .clinic-row-archived td { color: #6b7280; }

        .top-clinic-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f5f7ff 0%, #fafbff 100%);
            margin-bottom: 10px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e5e7eb;
        }
        .top-clinic-item:hover { transform: translateX(-4px); box-shadow: 0 4px 12px rgba(102,126,234,0.15); }
        .top-clinic-rank { font-size: 28px; min-width: 40px; text-align: center; }
        .top-clinic-info { flex: 1; }
        .top-clinic-name { font-weight: 700; color: var(--text-primary); font-size: 16px; margin-bottom: 4px; }
        .top-clinic-stats { font-size: 13px; color: var(--text-muted); }

        /* ===== Doctors Module ===== */
        .doctor-stat-card {
            background: linear-gradient(135deg, #fafbff 0%, #f5f7ff 100%);
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .doctor-stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(102,126,234,0.12); }
        .doctor-stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .doctor-stat-icon.green { background: linear-gradient(135deg, #11998e, #38ef7d); }
        .doctor-stat-icon.orange { background: linear-gradient(135deg, #f2994a, #f2c94c); }
        .doctor-stat-icon.red { background: linear-gradient(135deg, #eb3349, #f45c43); }
        .doctor-stat-icon.blue { background: linear-gradient(135deg, #2193b0, #6dd5ed); }
        .doctor-stat-info { flex: 1; }
        .doctor-stat-value { font-size: 22px; font-weight: 700; color: var(--text-primary); line-height: 1; }
        .doctor-stat-label { font-size: 13px; color: var(--text-muted); margin-top: 4px; }

        .doctor-row-archived { opacity: 0.6; background: #f9fafb !important; }
        .doctor-row-archived td { color: #6b7280; }

        .doctor-name-cell { display: flex; align-items: center; gap: 10px; min-width: 200px; }
        .doctor-name-cell .doctor-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }
        .doctor-name-cell .doctor-meta { line-height: 1.3; }
        .doctor-name-cell .doctor-full-name { font-weight: 600; color: var(--text-primary); }
        .doctor-name-cell .doctor-email { font-size: 12px; color: var(--text-muted); }

        .doctor-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .doctor-status-pill.active { background: #d1fae5; color: #065f46; }
        .doctor-status-pill.inactive { background: #fef3c7; color: #92400e; }
        .doctor-status-pill.archived { background: #e5e7eb; color: #6b7280; }
        .doctor-status-pill .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .doctor-info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .doctor-info-row:last-child { border-bottom: none; }
        .doctor-info-row .info-label { color: var(--text-muted); font-size: 13px; min-width: 130px; }
        .doctor-info-row .info-value { font-weight: 600; color: var(--text-primary); flex: 1; }

        .doctor-finance-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #f5f3ff 100%);
            border: 1px solid #e0e7ff;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
        }
        .doctor-finance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed #e5e7eb;
        }
        .doctor-finance-row:last-child { border-bottom: none; }
        .doctor-finance-row.total {
            border-top: 2px solid #4f46e5;
            margin-top: 8px;
            padding-top: 12px;
            font-weight: 700;
            font-size: 16px;
        }
        .doctor-finance-row .label { color: var(--text-muted); }
        .doctor-finance-row .value { font-weight: 700; }
        .doctor-finance-row .value.positive { color: #059669; }
        .doctor-finance-row .value.negative { color: #dc2626; }

        .deduction-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #f9fafb;
            margin-bottom: 6px;
            border-right: 3px solid #6b7280;
        }
        .deduction-item.deduction, .deduction-item.advance, .deduction-item.other { border-right-color: #dc2626; }
        .deduction-item.bonus { border-right-color: #059669; }
        .deduction-item .amount { font-weight: 700; min-width: 90px; }
        .deduction-item .amount.negative { color: #dc2626; }
        .deduction-item .amount.positive { color: #059669; }
        .deduction-item .reason { flex: 1; color: var(--text-muted); font-size: 13px; }
        .deduction-item .type-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            background: #e5e7eb;
        }

        .doctor-tab-bar {
            display: flex;
            gap: 4px;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 16px;
            overflow-x: auto;
        }
        .doctor-tab {
            padding: 10px 18px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .doctor-tab:hover { color: var(--primary); }
        .doctor-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .doctor-tab-content { display: none; }
        .doctor-tab-content.active { display: block; }

        .top-doctor-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f5f7ff 0%, #fafbff 100%);
            margin-bottom: 10px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e5e7eb;
        }
        .top-doctor-item:hover { transform: translateX(-4px); box-shadow: 0 4px 12px rgba(102,126,234,0.15); }
        .top-doctor-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
        }
        .top-doctor-info { flex: 1; }
        .top-doctor-name { font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .top-doctor-meta { font-size: 13px; color: var(--text-muted); }

        .clinic-name-cell { display: flex; align-items: center; gap: 10px; min-width: 180px; }
        .clinic-name-cell .clinic-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--primary-soft);
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .clinic-name-cell strong { display: block; color: var(--text-main); }
        .clinic-name-cell small { color: var(--text-muted); font-size: 12px; }

        .clinic-info-row {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px dashed var(--line);
        }
        .clinic-info-row:last-child { border-bottom: none; }
        .clinic-info-row .info-label {
            color: var(--text-muted);
            font-size: 12px;
            min-width: 110px;
            font-weight: 500;
        }
        .clinic-info-row .info-value {
            color: var(--text-main);
            font-size: 14px;
            font-weight: 500;
            flex: 1;
        }

        .drawer-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .drawer-stat {
            background: #f8fafc;
            padding: 12px 14px;
            border-radius: 8px;
            border-right: 3px solid var(--primary);
        }
        .drawer-stat .label {
            font-size: 11px;
            color: var(--text-muted);
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .drawer-stat .value {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-main);
        }
        .drawer-stat.green { border-right-color: var(--success); }
        .drawer-stat.purple { border-right-color: #7c3aed; }
        .drawer-stat.amber { border-right-color: #d97706; }

        .drawer-section {
            margin-bottom: 24px;
        }
        .drawer-section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--line);
        }

        .drawer-doctor-item {
            background: #f8fafc;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .drawer-doctor-item .doctor-info strong { display: block; color: var(--text-main); font-size: 14px; }
        .drawer-doctor-item .doctor-info small { color: var(--text-muted); font-size: 12px; }
        .drawer-doctor-item .doctor-stat {
            text-align: left;
            color: var(--text-muted);
            font-size: 12px;
        }
        .drawer-doctor-item .doctor-stat strong {
            display: block;
            color: var(--text-main);
            font-size: 16px;
        }

        .drawer-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--line);
        }
        .drawer-actions .btn { min-height: 36px; padding: 8px 14px; font-size: 13px; }

        .assign-doctor-form {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 8px;
            background: #eff6ff;
            padding: 12px;
            border-radius: 8px;
        }
        .assign-doctor-form select { flex: 1; min-height: 36px; }

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
                    <div class="stats-grid" id="doctorsStatsGridDashboard" style="display:none"></div>
                    <div class="card" id="topDoctorsCard" style="display:none">
                        <h2>👨‍⚕️ أفضل الأطباء</h2>
                        <div class="chart-container" id="topDoctorsContainer"></div>
                    </div>
                    <div class="card" id="topClinicsCard" style="display:none">
                        <h2>🏥 أفضل العيادات</h2>
                        <div class="chart-container" id="topClinicsContainer"></div>
                    </div>
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
                        <h2>📅 مواعيد اليوم</h2>
                        <p style="color: #6b7280; margin-bottom: 12px; font-size: 13px;">يمكنك تحويل موعد إلى زيارة بضغطة واحدة</p>
                        <div id="receptionTodayAppointments" class="loading">جاري التحميل...</div>
                    </div>
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
                    <div class="patients-toolbar">
                        <div class="patients-actions">
                            <button class="btn btn-success" onclick="spa.openAppointmentModal()">
                                <span aria-hidden="true">➕</span>
                                <span>حجز موعد جديد</span>
                            </button>
                            <button class="btn btn-secondary" onclick="spa.toggleAppointmentsFilters()">
                                <span aria-hidden="true">🔍</span>
                                <span>تصفية</span>
                            </button>
                            <div class="view-toggle" role="tablist">
                                <button id="aptViewTable" class="view-toggle-btn active" onclick="spa.switchAppointmentsView('table')">📋 جدول</button>
                                <button id="aptViewCalendar" class="view-toggle-btn" onclick="spa.switchAppointmentsView('calendar')">🗓 تقويم</button>
                            </div>
                        </div>
                        <div class="patients-search">
                            <input type="text" id="appointmentsSearch" placeholder="البحث باسم المريض أو رقم الملف..." onkeyup="spa.debounceAppointmentsSearch()">
                        </div>
                    </div>

                    <div class="filters compact-filters" id="appointmentsFiltersPanel">
                        <div class="filter-group">
                            <label>العيادة</label>
                            <select id="appointmentsClinicFilter" onchange="spa.onAppointmentsFilterChange()">
                                <option value="">جميع العيادات</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>الطبيب</label>
                            <select id="appointmentsDoctorFilter" onchange="spa.onAppointmentsFilterChange()">
                                <option value="">جميع الأطباء</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>الحالة</label>
                            <select id="appointmentsStatusFilter" onchange="spa.onAppointmentsFilterChange()">
                                <option value="">جميع الحالات</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>من تاريخ</label>
                            <input type="date" id="appointmentsDateFrom" onchange="spa.onAppointmentsFilterChange()">
                        </div>
                        <div class="filter-group">
                            <label>إلى تاريخ</label>
                            <input type="date" id="appointmentsDateTo" onchange="spa.onAppointmentsFilterChange()">
                        </div>
                        <button class="btn btn-secondary" onclick="spa.resetAppointmentsFilters()">إعادة تعيين</button>
                    </div>

                    <div id="appointmentsTableView" class="card patients-table-card">
                        <div class="patients-table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>المريض</th>
                                        <th>الطبيب</th>
                                        <th>العيادة</th>
                                        <th>التاريخ والوقت</th>
                                        <th>المدة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="appointmentsTable">
                                    <tr><td colspan="8" class="loading">جاري التحميل...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination" id="appointmentsPagination"></div>
                    </div>

                    <div id="appointmentsCalendarView" class="card patients-table-card" style="display: none;">
                        <div class="calendar-toolbar">
                            <button class="btn btn-secondary btn-sm" onclick="spa.changeCalendarMonth(-1)">‹ السابق</button>
                            <h3 id="calendarTitle">—</h3>
                            <button class="btn btn-secondary btn-sm" onclick="spa.changeCalendarMonth(1)">التالي ›</button>
                            <button class="btn btn-sm" onclick="spa.goToTodayCalendar()">اليوم</button>
                        </div>
                        <div class="calendar-weekdays">
                            <div>الأحد</div><div>الإثنين</div><div>الثلاثاء</div><div>الأربعاء</div><div>الخميس</div><div>الجمعة</div><div>السبت</div>
                        </div>
                        <div id="appointmentsCalendarGrid" class="calendar-grid"></div>
                    </div>
                </div>

                <!-- Appointment Form Modal -->
                <div class="modal" id="appointmentModal">
                    <div class="modal-content modal-content-wide">
                        <div class="modal-header modal-header-gradient">
                            <div class="modal-header-icon">📅</div>
                            <div>
                                <h2 id="appointmentModalTitle">حجز موعد جديد</h2>
                                <p class="modal-subtitle">املأ البيانات أدناه لإضافة موعد جديد</p>
                            </div>
                            <button class="modal-close" onclick="spa.closeAppointmentModal()" aria-label="إغلاق">×</button>
                        </div>
                        <form id="appointmentForm" onsubmit="spa.saveAppointment(event)" class="apt-form">
                            <input type="hidden" id="appointmentId">

                            <div class="apt-form-body">
                                <section class="apt-section">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">👤</span>
                                        <span class="apt-section-title">المريض</span>
                                    </div>
                                    <div class="form-group">
                                        <label for="appointmentPatientId">اختر المريض <span class="req">*</span></label>
                                        <select id="appointmentPatientId" required>
                                            <option value="">— ابحث أو اختر المريض —</option>
                                        </select>
                                        <small class="apt-hint">يكتب اسم المريض أو جزء من رقم الملف للبحث</small>
                                    </div>
                                </section>

                                <section class="apt-section">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">⏰</span>
                                        <span class="apt-section-title">تفاصيل الموعد</span>
                                    </div>
                                    <div class="apt-row apt-row-2">
                                        <div class="form-group">
                                            <label for="appointmentDate">التاريخ والوقت <span class="req">*</span></label>
                                            <input type="datetime-local" id="appointmentDate" onchange="spa.checkAppointmentAvailability()" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="appointmentDuration">المدة <small style="color:#9ca3af; font-weight:500;">(دقيقة)</small></label>
                                            <div class="apt-duration-wrap">
                                                <input type="number" id="appointmentDuration" min="5" max="480" step="5" value="30" onchange="spa.checkAppointmentAvailability()">
                                                <div class="apt-duration-presets" role="group" aria-label="مدة سريعة">
                                                    <button type="button" onclick="spa.setAppointmentDuration(15)">15</button>
                                                    <button type="button" onclick="spa.setAppointmentDuration(30)" class="active">30</button>
                                                    <button type="button" onclick="spa.setAppointmentDuration(45)">45</button>
                                                    <button type="button" onclick="spa.setAppointmentDuration(60)">60</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="apt-section">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">🩺</span>
                                        <span class="apt-section-title">العيادة والطبيب</span>
                                    </div>
                                    <div class="apt-row apt-row-2">
                                        <div class="form-group">
                                            <label for="appointmentClinicId">العيادة <span class="req">*</span></label>
                                            <select id="appointmentClinicId" onchange="spa.loadDoctorsForAppointmentModal()" required>
                                                <option value="">— اختر العيادة —</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="appointmentDoctorId">الطبيب <span class="req">*</span></label>
                                            <select id="appointmentDoctorId" onchange="spa.checkAppointmentAvailability()" required>
                                                <option value="">— اختر الطبيب —</option>
                                            </select>
                                        </div>
                                    </div>
                                </section>

                                <section class="apt-section">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">📝</span>
                                        <span class="apt-section-title">تفاصيل إضافية</span>
                                    </div>
                                    <div class="apt-row apt-row-2">
                                        <div class="form-group">
                                            <label for="appointmentStatus">الحالة</label>
                                            <select id="appointmentStatus">
                                                <option value="scheduled">🟢 مجدول</option>
                                                <option value="completed">✓ مكتمل</option>
                                                <option value="cancelled">✕ ملغي</option>
                                                <option value="missed">⚠ فائت</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="appointmentNotes">ملاحظات</label>
                                            <textarea id="appointmentNotes" rows="2" maxlength="2000" placeholder="ملاحظات اختيارية..."></textarea>
                                        </div>
                                    </div>
                                </section>

                                <div id="appointmentAvailabilityHint" class="availability-hint" style="display: none;"></div>
                            </div>

                            <div class="apt-form-footer">
                                <button type="button" class="btn btn-ghost" onclick="spa.closeAppointmentModal()">إلغاء</button>
                                <button type="submit" class="btn btn-success btn-with-icon" id="appointmentSubmitBtn">
                                    <span aria-hidden="true">✓</span>
                                    <span>حفظ الموعد</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Appointment Details Modal -->
                <div class="modal" id="appointmentDetailsModal">
                    <div class="modal-content" style="max-width: 640px;">
                        <div class="modal-header">
                            <div>
                                <h2 id="appointmentDetailsTitle">تفاصيل الموعد</h2>
                                <p id="appointmentDetailsSubtitle" style="color: #666; font-size: 14px; margin-top: 5px;"></p>
                            </div>
                            <button class="modal-close" onclick="spa.closeAppointmentDetails()">×</button>
                        </div>
                        <div id="appointmentDetailsContent">
                            <div class="loading">جاري التحميل...</div>
                        </div>
                        <div class="modal-actions" id="appointmentDetailsActions" style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;"></div>
                    </div>
                </div>

                <!-- Day Appointments Modal (for calendar) -->
                <div class="modal" id="appointmentDayModal">
                    <div class="modal-content" style="max-width: 720px;">
                        <div class="modal-header">
                            <h2 id="appointmentDayTitle">مواعيد اليوم</h2>
                            <button class="modal-close" onclick="spa.closeAppointmentDayModal()">×</button>
                        </div>
                        <div id="appointmentDayContent"></div>
                    </div>
                </div>

                <!-- Settings Page -->
                <div id="settingsPage" class="page">
                    <div class="card">
                        <h2>الإعدادات</h2>
                        <p>صفحة الإعدادات قيد التطوير...</p>
                    </div>
                </div>

                <!-- Clinics Page -->
                <div id="clinicsPage" class="page">
                    <div class="patients-toolbar">
                        <div class="patients-actions">
                            <button class="btn btn-success" onclick="spa.openClinicModal()">
                                <span aria-hidden="true">➕</span>
                                <span>إضافة عيادة جديدة</span>
                            </button>
                            <button class="btn btn-secondary" onclick="spa.toggleClinicsFilters()">
                                <span aria-hidden="true">🔍</span>
                                <span>تصفية</span>
                            </button>
                        </div>
                        <div class="patients-search">
                            <input type="text" id="clinicsSearch" placeholder="البحث باسم العيادة أو الموقع أو الهاتف..." onkeyup="spa.debounceClinicsSearch()">
                        </div>
                    </div>

                    <div class="filters compact-filters" id="clinicsFiltersPanel">
                        <div class="filter-group">
                            <label>الحالة</label>
                            <select id="clinicsStatusFilter" onchange="spa.loadClinicsTable(1)">
                                <option value="">جميع الحالات</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>الحد الأدنى لعدد الأطباء</label>
                            <input type="number" id="clinicsMinDoctors" min="0" placeholder="0" onchange="spa.loadClinicsTable(1)">
                        </div>
                        <div class="filter-group" style="flex: 0; min-width: auto; justify-content: flex-end;">
                            <label style="display: flex; gap: 8px; align-items: center; cursor: pointer; user-select: none;">
                                <input type="checkbox" id="clinicsActiveOnly" onchange="spa.loadClinicsTable(1)">
                                <span>العيادات النشطة فقط</span>
                            </label>
                        </div>
                        <button class="btn btn-secondary" onclick="spa.resetClinicsFilters()">إعادة تعيين</button>
                    </div>

                    <div class="stats-grid" id="clinicsStatsGrid">
                        <div class="loading">جاري تحميل الإحصائيات...</div>
                    </div>

                    <div class="card patients-table-card">
                        <div class="patients-table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم العيادة</th>
                                        <th>الموقع</th>
                                        <th>الهاتف</th>
                                        <th>الأطباء</th>
                                        <th>الزيارات</th>
                                        <th>المواعيد</th>
                                        <th>إيراد الشهر</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="clinicsTable">
                                    <tr><td colspan="10" class="loading">جاري التحميل...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination" id="clinicsPagination"></div>
                    </div>
                </div>

                <!-- Clinic Form Modal -->
                <div class="modal" id="clinicModal">
                    <div class="modal-content modal-content-wide">
                        <div class="modal-header modal-header-gradient">
                            <div class="modal-header-icon">🏥</div>
                            <div>
                                <h2 id="clinicModalTitle">إضافة عيادة جديدة</h2>
                                <p class="modal-subtitle">أدخل بيانات العيادة الأساسية</p>
                            </div>
                            <button class="modal-close" onclick="spa.closeClinicModal()" aria-label="إغلاق">×</button>
                        </div>
                        <form id="clinicForm" onsubmit="spa.saveClinic(event)" class="apt-form">
                            <input type="hidden" id="clinicId">
                            <div class="apt-form-body">
                                <section class="apt-section">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">🏥</span>
                                        <span class="apt-section-title">البيانات الأساسية</span>
                                    </div>
                                    <div class="form-group">
                                        <label for="clinicName">اسم العيادة <span class="req">*</span></label>
                                        <input type="text" id="clinicName" required maxlength="255" placeholder="مثال: عيادة الأسنان">
                                    </div>
                                </section>

                                <section class="apt-section">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">📍</span>
                                        <span class="apt-section-title">معلومات الاتصال</span>
                                    </div>
                                    <div class="apt-row apt-row-2">
                                        <div class="form-group">
                                            <label for="clinicPhone">رقم الهاتف</label>
                                            <input type="tel" id="clinicPhone" maxlength="30" placeholder="05xxxxxxxx">
                                        </div>
                                        <div class="form-group">
                                            <label for="clinicLocation">الموقع</label>
                                            <input type="text" id="clinicLocation" maxlength="255" placeholder="المدينة، الحي">
                                        </div>
                                    </div>
                                </section>

                                <section class="apt-section">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">📝</span>
                                        <span class="apt-section-title">تفاصيل إضافية</span>
                                    </div>
                                    <div class="form-group">
                                        <label for="clinicDescription">الوصف</label>
                                        <textarea id="clinicDescription" rows="3" maxlength="2000" placeholder="وصف مختصر عن العيادة والخدمات..."></textarea>
                                    </div>
                                    <div class="form-group" id="clinicStatusGroup" style="display: none;">
                                        <label for="clinicStatus">الحالة</label>
                                        <select id="clinicStatus">
                                            <option value="active">🟢 نشطة</option>
                                            <option value="inactive">🟡 غير نشطة</option>
                                        </select>
                                        <small class="apt-hint">للأرشفة استخدم زر الأرشفة في الجدول (لحماية البيانات التاريخية)</small>
                                    </div>
                                </section>
                            </div>
                            <div class="apt-form-footer">
                                <button type="button" class="btn btn-ghost" onclick="spa.closeClinicModal()">إلغاء</button>
                                <button type="submit" class="btn btn-success btn-with-icon" id="clinicSubmitBtn">
                                    <span aria-hidden="true">✓</span>
                                    <span>حفظ العيادة</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Clinic Details Drawer -->
                <div class="drawer" id="clinicDrawer">
                    <div class="drawer-header">
                        <button class="drawer-close" onclick="spa.closeClinicDrawer()">×</button>
                        <h2 id="clinicDrawerTitle">تفاصيل العيادة</h2>
                        <p id="clinicDrawerSubtitle">—</p>
                    </div>
                    <div class="drawer-content" id="clinicDrawerContent">
                        <div class="loading">جاري التحميل...</div>
                    </div>
                </div>

                <!-- Doctors Page -->
                <div id="doctorsPage" class="page">
                    <div class="patients-toolbar">
                        <div class="patients-actions">
                            <button class="btn btn-success" id="addDoctorBtn" onclick="spa.openDoctorModal()">
                                <span aria-hidden="true">➕</span>
                                <span>إضافة طبيب جديد</span>
                            </button>
                            <button class="btn btn-secondary" onclick="spa.toggleDoctorsFilters()">
                                <span aria-hidden="true">🔍</span>
                                <span>تصفية</span>
                            </button>
                        </div>
                        <div class="patients-search">
                            <input type="text" id="doctorsSearch" placeholder="البحث بالاسم، الإيميل، الجوال، التخصص..." onkeyup="spa.debounceDoctorsSearch()">
                        </div>
                    </div>

                    <div class="filters compact-filters" id="doctorsFiltersPanel">
                        <div class="filter-group">
                            <label>العيادة</label>
                            <select id="doctorsClinicFilter" onchange="spa.loadDoctorsTable(1)">
                                <option value="">جميع العيادات</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>التخصص</label>
                            <select id="doctorsSpecializationFilter" onchange="spa.loadDoctorsTable(1)">
                                <option value="">جميع التخصصات</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>الحالة</label>
                            <select id="doctorsActiveFilter" onchange="spa.loadDoctorsTable(1)">
                                <option value="">الكل</option>
                                <option value="active">نشط</option>
                                <option value="inactive">غير نشط</option>
                                <option value="archived">مؤرشف</option>
                            </select>
                        </div>
                        <button class="btn btn-secondary" onclick="spa.resetDoctorsFilters()">إعادة تعيين</button>
                    </div>

                    <div class="stats-grid" id="doctorsStatsGrid">
                        <div class="loading">جاري تحميل الإحصائيات...</div>
                    </div>

                    <div class="card patients-table-card">
                        <div class="patients-table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الطبيب</th>
                                        <th>العيادة</th>
                                        <th>التخصص</th>
                                        <th>سعر الكشف</th>
                                        <th>المرضى</th>
                                        <th>الزيارات</th>
                                        <th>إيراد الشهر</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody id="doctorsTable">
                                    <tr><td colspan="10" class="loading">جاري التحميل...</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination" id="doctorsPagination"></div>
                    </div>
                </div>

                <!-- Doctor Form Modal -->
                <div class="modal" id="doctorModal">
                    <div class="modal-content modal-content-wide">
                        <div class="modal-header modal-header-gradient">
                            <div class="modal-header-icon">👨‍⚕️</div>
                            <div>
                                <h2 id="doctorModalTitle">إضافة طبيب جديد</h2>
                                <p class="modal-subtitle">أدخل بيانات الطبيب الأساسية</p>
                            </div>
                            <button class="modal-close" onclick="spa.closeDoctorModal()" aria-label="إغلاق">×</button>
                        </div>
                        <form id="doctorForm" onsubmit="spa.saveDoctor(event)" class="apt-form">
                            <input type="hidden" id="doctorId">
                            <div class="apt-form-body">
                                <section class="apt-section">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">👤</span>
                                        <span class="apt-section-title">البيانات الشخصية</span>
                                    </div>
                                    <div class="apt-row apt-row-2">
                                        <div class="form-group">
                                            <label for="doctorFullName">الاسم الكامل <span class="req">*</span></label>
                                            <input type="text" id="doctorFullName" required maxlength="255" placeholder="د. محمد العلي">
                                        </div>
                                        <div class="form-group">
                                            <label for="doctorPhone">رقم الجوال</label>
                                            <input type="tel" id="doctorPhone" maxlength="32" placeholder="05xxxxxxxx">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="doctorEmail">البريد الإلكتروني <span class="req">*</span></label>
                                        <input type="email" id="doctorEmail" required maxlength="255" placeholder="doctor@example.com">
                                    </div>
                                    <div class="form-group" id="doctorPasswordGroup">
                                        <label for="doctorPassword">كلمة المرور <span class="req">*</span></label>
                                        <input type="password" id="doctorPassword" minlength="6" placeholder="6 أحرف على الأقل">
                                        <small class="apt-hint" id="doctorPasswordHint">اتركها فارغة للإبقاء على كلمة المرور الحالية</small>
                                    </div>
                                </section>

                                <section class="apt-section">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">🏥</span>
                                        <span class="apt-section-title">التخصص والعيادة</span>
                                    </div>
                                    <div class="apt-row apt-row-2">
                                        <div class="form-group">
                                            <label for="doctorClinicId">العيادة <span class="req">*</span></label>
                                            <select id="doctorClinicId" required>
                                                <option value="">— اختر العيادة —</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="doctorSpecialization">التخصص</label>
                                            <input type="text" id="doctorSpecialization" maxlength="255" list="doctorSpecializationsList" placeholder="مثال: طب الأسنان">
                                            <datalist id="doctorSpecializationsList"></datalist>
                                        </div>
                                    </div>
                                </section>

                                <section class="apt-section">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">💰</span>
                                        <span class="apt-section-title">الرسوم والنسب</span>
                                    </div>
                                    <div class="apt-row apt-row-2">
                                        <div class="form-group">
                                            <label for="doctorExaminationFee">سعر الكشف (ر.س) <span class="req">*</span></label>
                                            <input type="number" id="doctorExaminationFee" required min="0" step="0.01" placeholder="0.00">
                                        </div>
                                        <div class="form-group">
                                            <label for="doctorPercentageType">نوع النسبة</label>
                                            <select id="doctorPercentageType">
                                                <option value="fixed">مبلغ ثابت</option>
                                                <option value="percentage">نسبة مئوية</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="doctorPercentageValue">قيمة النسبة</label>
                                        <input type="number" id="doctorPercentageValue" min="0" max="100" step="0.01" placeholder="0.00">
                                        <small class="apt-hint">إذا كانت النسبة مئوية: من 0 إلى 100. إذا كانت ثابتة: اتركها 0.</small>
                                    </div>
                                </section>

                                <section class="apt-section" id="doctorStatusSection" style="display: none;">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">⚙️</span>
                                        <span class="apt-section-title">الحالة</span>
                                    </div>
                                    <div class="form-group">
                                        <label style="display: flex; gap: 8px; align-items: center; cursor: pointer;">
                                            <input type="checkbox" id="doctorIsActive" checked>
                                            <span>الطبيب مفعّل ويستقبل مرضى</span>
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <label for="doctorNotes">ملاحظات</label>
                                        <textarea id="doctorNotes" rows="2" maxlength="1000" placeholder="ملاحظات اختيارية..."></textarea>
                                    </div>
                                </section>
                            </div>
                            <div class="apt-form-footer">
                                <button type="button" class="btn btn-ghost" onclick="spa.closeDoctorModal()">إلغاء</button>
                                <button type="submit" class="btn btn-success btn-with-icon" id="doctorSubmitBtn">
                                    <span aria-hidden="true">✓</span>
                                    <span>حفظ الطبيب</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Doctor Details Drawer -->
                <div class="drawer" id="doctorDrawer">
                    <div class="drawer-header">
                        <button class="drawer-close" onclick="spa.closeDoctorDrawer()">×</button>
                        <h2 id="doctorDrawerTitle">تفاصيل الطبيب</h2>
                        <p id="doctorDrawerSubtitle">—</p>
                    </div>
                    <div class="drawer-content" id="doctorDrawerContent">
                        <div class="loading">جاري التحميل...</div>
                    </div>
                </div>

                <!-- Deduction Modal -->
                <div class="modal" id="deductionModal">
                    <div class="modal-content" style="max-width: 500px;">
                        <div class="modal-header modal-header-gradient">
                            <div class="modal-header-icon">💸</div>
                            <div>
                                <h2 id="deductionModalTitle">إضافة خصم / سلفة</h2>
                                <p class="modal-subtitle">سيتم تطبيقها على صافي أرباح الطبيب</p>
                            </div>
                            <button class="modal-close" onclick="spa.closeDeductionModal()" aria-label="إغلاق">×</button>
                        </div>
                        <form id="deductionForm" onsubmit="spa.saveDeduction(event)" class="apt-form">
                            <input type="hidden" id="deductionDoctorId">
                            <div class="apt-form-body">
                                <section class="apt-section">
                                    <div class="apt-section-header">
                                        <span class="apt-section-icon">💰</span>
                                        <span class="apt-section-title">تفاصيل الحركة المالية</span>
                                    </div>
                                    <div class="apt-row apt-row-2">
                                        <div class="form-group">
                                            <label for="deductionAmount">المبلغ (ر.س) <span class="req">*</span></label>
                                            <input type="number" id="deductionAmount" required min="0.01" step="0.01" placeholder="0.00">
                                        </div>
                                        <div class="form-group">
                                            <label for="deductionType">النوع <span class="req">*</span></label>
                                            <select id="deductionType" required>
                                                <option value="deduction">خصم</option>
                                                <option value="advance">سلفة</option>
                                                <option value="bonus">مكافأة</option>
                                                <option value="other">أخرى</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="deductionDate">التاريخ</label>
                                        <input type="date" id="deductionDate">
                                    </div>
                                    <div class="form-group">
                                        <label for="deductionReason">السبب / الوصف <span class="req">*</span></label>
                                        <textarea id="deductionReason" required rows="3" maxlength="500" placeholder="مثال: سلفة شهرية..."></textarea>
                                    </div>
                                </section>
                            </div>
                            <div class="apt-form-footer">
                                <button type="button" class="btn btn-ghost" onclick="spa.closeDeductionModal()">إلغاء</button>
                                <button type="submit" class="btn btn-success btn-with-icon" id="deductionSubmitBtn">
                                    <span aria-hidden="true">✓</span>
                                    <span>حفظ الحركة</span>
                                </button>
                            </div>
                        </form>
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
