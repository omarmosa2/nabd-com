// SPA Router for Nabdh Medical System
class NabdhSPA {
    constructor() {
        this.token = localStorage.getItem('token');
        this.user = JSON.parse(localStorage.getItem('user') || 'null');
        this.currentPage = null;
        this.init();
    }

    init() {
        if (!this.token || !this.user) {
            this.showLogin();
        } else {
            this.showApp();
            this.setupRouter();
            this.handleInitialRoute();
        }
    }

    // Authentication
    showLogin() {
        document.getElementById('loginPage').classList.add('active');
        document.getElementById('appLayout').classList.remove('active');
        this.setupLoginForm();
    }

    showApp() {
        document.getElementById('loginPage').classList.remove('active');
        document.getElementById('appLayout').classList.add('active');
        this.renderSidebar();
        this.updateUserInfo();
    }

    setupLoginForm() {
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (response.ok) {
                    this.token = data.token;
                    this.user = data.user;
                    localStorage.setItem('token', this.token);
                    localStorage.setItem('user', JSON.stringify(this.user));
                    this.showApp();
                    this.setupRouter();
                    this.handleInitialRoute();
                } else {
                    this.showAlert('loginAlert', data.message || 'بيانات الدخول غير صحيحة', 'error');
                }
            } catch (error) {
                this.showAlert('loginAlert', 'حدث خطأ في الاتصال', 'error');
            }
        });
    }

    logout() {
        fetch('/api/logout', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Accept': 'application/json'
            }
        }).finally(() => {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            this.token = null;
            this.user = null;
            this.showLogin();
        });
    }

    // Sidebar
    renderSidebar() {
        const menu = document.getElementById('sidebarMenu');
        const role = this.user.role;

        const menuItems = {
            admin: [
                { path: '/dashboard', icon: '🏥', label: 'الرئيسية' },
                { path: '/patients', icon: '👥', label: 'المرضى' },
                { path: '/appointments', icon: '📅', label: 'المواعيد' },
                { path: '/finance', icon: '🧾', label: 'الفواتير' },
                { path: '/settings', icon: '⚙️', label: 'الإعدادات' }
            ],
            reception: [
                { path: '/reception', icon: '🏥', label: 'الرئيسية' },
                { path: '/patients', icon: '👥', label: 'المرضى' },
                { path: '/appointments', icon: '📅', label: 'المواعيد' }
            ],
            doctor: [
                { path: '/patients', icon: '👥', label: 'المرضى' },
                { path: '/appointments', icon: '📅', label: 'المواعيد' }
            ]
        };

        const items = menuItems[role] || [];
        menu.innerHTML = items.map(item => `
            <li>
                <a href="${item.path}" onclick="event.preventDefault(); spa.navigate('${item.path}')">
                    <span class="icon">${item.icon}</span>
                    <span>${item.label}</span>
                </a>
            </li>
        `).join('');
    }

    updateUserInfo() {
        const name = this.user.full_name || 'المستخدم';
        const roleLabels = {
            admin: 'مدير النظام',
            reception: 'الاستقبال',
            doctor: 'طبيب'
        };

        document.getElementById('userName').textContent = name;

        const roleEl = document.getElementById('userRole');
        if (roleEl) {
            roleEl.textContent = roleLabels[this.user.role] || 'مستخدم النظام';
        }

        const avatar = document.getElementById('userAvatar');
        if (avatar) {
            avatar.textContent = name.trim().charAt(0) || 'م';
        }
    }

    // Router
    setupRouter() {
        window.addEventListener('popstate', () => {
            this.handleRoute(window.location.pathname);
        });
    }

    handleInitialRoute() {
        const path = window.location.pathname;
        if (path === '/' || path === '/login') {
            this.roleRedirect();
        } else {
            this.handleRoute(path);
        }
    }

    roleRedirect() {
        const role = this.user.role;
        if (role === 'admin') {
            this.navigate('/dashboard');
        } else if (role === 'reception') {
            this.navigate('/reception');
        } else if (role === 'doctor') {
            this.navigate('/patients');
        } else {
            this.navigate('/unauthorized');
        }
    }

    navigate(path) {
        history.pushState(null, '', path);
        this.handleRoute(path);
    }

    handleRoute(path) {
        const route = this.getRoute(path);
        
        if (!this.checkRoleAccess(route)) {
            this.showPage('unauthorized');
            return;
        }

        this.showPage(route.page);
        this.updatePageTitle(route.title);
        this.loadPageData(route.page);
        this.updateSidebarActive(path);
    }

    getRoute(path) {
        const routes = {
            '/dashboard': { page: 'dashboard', title: 'الرئيسية', roles: ['admin'] },
            '/reception': { page: 'reception', title: 'الرئيسية', roles: ['admin', 'reception'] },
            '/patients': { page: 'patients', title: 'إدارة المرضى', roles: ['admin', 'reception', 'doctor'] },
            '/finance': { page: 'finance', title: 'الفواتير', roles: ['admin'] },
            '/reports': { page: 'reports', title: 'التقارير', roles: ['admin'] },
            '/appointments': { page: 'appointments', title: 'المواعيد', roles: ['admin', 'reception', 'doctor'] },
            '/settings': { page: 'settings', title: 'الإعدادات', roles: ['admin'] },
            '/unauthorized': { page: 'unauthorized', title: 'غير مصرح', roles: ['admin', 'reception', 'doctor'] }
        };

        return routes[path] || routes['/unauthorized'];
    }

    checkRoleAccess(route) {
        return route.roles.includes(this.user.role);
    }

    showPage(pageName) {
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        const page = document.getElementById(`${pageName}Page`);
        if (page) {
            page.classList.add('active');
            this.currentPage = pageName;
        }
    }

    updatePageTitle(title) {
        document.getElementById('pageTitle').textContent = title;
    }

    updateSidebarActive(path) {
        document.querySelectorAll('.sidebar-menu a').forEach(a => {
            a.classList.remove('active');
            if (a.getAttribute('href') === path) {
                a.classList.add('active');
            }
        });
    }

    // Page Data Loaders
    async loadPageData(page) {
        switch(page) {
            case 'dashboard':
                await this.loadDashboard();
                break;
            case 'reception':
                await this.loadReception();
                break;
            case 'patients':
                await this.loadPatientsFilters();
                await this.loadPatients();
                break;
            case 'finance':
                await this.loadFinance();
                break;
            case 'appointments':
                await this.loadAppointments();
                break;
        }
    }

    async loadDashboard() {
        try {
            const [statsRes, chartsRes] = await Promise.all([
                this.apiCall('/api/dashboard/stats'),
                this.apiCall('/api/dashboard/charts')
            ]);

            const stats = await statsRes.json();
            const charts = await chartsRes.json();

            document.getElementById('dashboardStats').innerHTML = `
                <div class="stat-card" onclick="spa.navigate('/patients')">
                    <h3>${stats.patients_today || 0}</h3>
                    <p>مرضى اليوم</p>
                </div>
                <div class="stat-card" onclick="spa.navigate('/reception')">
                    <h3>${stats.examinations_today || 0}</h3>
                    <p>معاينات اليوم</p>
                </div>
                <div class="stat-card" onclick="spa.navigate('/reception')">
                    <h3>${stats.reviews_today || 0}</h3>
                    <p>مراجعات اليوم</p>
                </div>
                <div class="stat-card" onclick="spa.navigate('/finance')">
                    <h3>${stats.revenue_today || 0} ر.س</h3>
                    <p>إيراد اليوم</p>
                </div>
            `;

            if (charts.visits_by_month && charts.visits_by_month.length > 0) {
                const ctx = document.getElementById('visitsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: charts.visits_by_month.map(v => v.month),
                        datasets: [
                            {
                                label: 'معاينات',
                                data: charts.visits_by_month.map(v => v.examinations),
                                backgroundColor: '#667eea'
                            },
                            {
                                label: 'مراجعات',
                                data: charts.visits_by_month.map(v => v.reviews),
                                backgroundColor: '#764ba2'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        } catch (error) {
            console.error('Error loading dashboard:', error);
        }
    }

    async loadReception() {
        try {
            const res = await this.apiCall('/api/clinics');
            const data = await res.json();
            
            const select = document.getElementById('receptionClinic');
            select.innerHTML = '<option value="">اختر العيادة...</option>' + 
                data.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        } catch (error) {
            console.error('Error loading clinics:', error);
        }
    }

    // Patients Module - Enhanced
    async loadPatientsFilters() {
        try {
            const [clinicsRes, doctorsRes] = await Promise.all([
                this.apiCall('/api/clinics'),
                this.apiCall('/api/users?role=doctor')
            ]);

            const clinicsData = await clinicsRes.json();
            const doctorsData = await doctorsRes.json();

            const clinicsSelect = document.getElementById('patientsClinicFilter');
            if (clinicsSelect) {
                clinicsSelect.innerHTML = '<option value="">جميع العيادات</option>' + 
                    (clinicsData.data || []).map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            }

            const doctorsSelect = document.getElementById('patientsDoctorFilter');
            if (doctorsSelect) {
                doctorsSelect.innerHTML = '<option value="">جميع الأطباء</option>' + 
                    (doctorsData.data || []).map(d => `<option value="${d.id}">${d.full_name}</option>`).join('');
            }
        } catch (error) {
            console.error('Error loading patients filters:', error);
        }
    }

    async loadPatients(page = 1) {
        this.patientsCurrentPage = page;
        
        const search = document.getElementById('patientsSearch')?.value || '';
        const clinicId = document.getElementById('patientsClinicFilter')?.value || '';
        const doctorId = document.getElementById('patientsDoctorFilter')?.value || '';
        const gender = document.getElementById('patientsGenderFilter')?.value || '';
        const dateFrom = document.getElementById('patientsDateFrom')?.value || '';
        const dateTo = document.getElementById('patientsDateTo')?.value || '';

        let url = `/api/patients?page=${page}&per_page=15`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (clinicId) url += `&clinic_id=${clinicId}`;
        if (doctorId) url += `&doctor_id=${doctorId}`;
        if (gender) url += `&gender=${gender}`;
        if (dateFrom) url += `&date_from=${dateFrom}`;
        if (dateTo) url += `&date_to=${dateTo}`;

        try {
            const res = await this.apiCall(url);
            const data = await res.json();
            this.renderPatientsTable(data);
        } catch (error) {
            console.error('Error loading patients:', error);
            document.getElementById('patientsTable').innerHTML = '<tr><td colspan="8" class="loading">خطأ في التحميل</td></tr>';
        }
    }

    renderPatientsTable(data) {
        const patients = data.data || [];
        const tbody = document.getElementById('patientsTable');
        
        if (patients.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="empty-state"><div class="icon">👥</div><h3>لا توجد نتائج</h3><p>لم يتم العثور على مرضى مطابقين للبحث</p></td></tr>';
            document.getElementById('patientsPagination').innerHTML = '';
            return;
        }

        tbody.innerHTML = patients.map((p, index) => {
            const serial = ((data.current_page || 1) - 1) * (data.per_page || patients.length) + index + 1;
            const createdDate = p.created_at ? new Date(p.created_at).toLocaleDateString('ar-SA') : '-';
            const isActive = (p.visits_count || 0) > 0;
            const statusLabel = isActive ? 'نشط' : 'جديد';
            const statusClass = isActive ? 'status-active' : 'status-new';
            const genderClass = p.gender === 'male' ? 'gender-male' : 'gender-female';
            const genderLabel = p.gender === 'male' ? 'ذكر' : 'أنثى';
            const encodedName = encodeURIComponent(p.full_name || '').replace(/'/g, '%27');

            return `
                <tr data-patient-id="${p.id}">
                    <td class="serial-cell">${serial}</td>
                    <td><span class="patient-number">${p.file_number}</span></td>
                    <td><span class="gender-badge ${genderClass}">${genderLabel}</span></td>
                    <td>${p.age} سنة</td>
                    <td>${p.phone || '-'}</td>
                    <td><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                    <td>${createdDate}</td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-btn" title="عرض التفاصيل" aria-label="عرض التفاصيل" onclick="event.stopPropagation(); spa.openViewModal(${p.id})">👁</button>
                            <button class="icon-btn" title="تعديل" aria-label="تعديل" onclick="event.stopPropagation(); spa.openEditPatientModal(${p.id})">✎</button>
                            <button class="icon-btn danger" title="${isActive ? 'لا يمكن حذف مريض لديه زيارات' : 'حذف'}" aria-label="حذف" ${isActive ? 'disabled' : ''} onclick="event.stopPropagation(); spa.deletePatient(${p.id}, decodeURIComponent('${encodedName}'))">🗑</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        this.renderPatientsPagination(data);
    }

    renderPatientsPagination(data) {
        const pagination = document.getElementById('patientsPagination');
        const { current_page, last_page } = data;
        
        let html = '';
        
        html += `<button ${current_page === 1 ? 'disabled' : ''} onclick="spa.loadPatients(${current_page - 1})">السابق</button>`;
        
        for (let i = 1; i <= last_page; i++) {
            if (i === 1 || i === last_page || (i >= current_page - 2 && i <= current_page + 2)) {
                html += `<button class="${i === current_page ? 'active' : ''}" onclick="spa.loadPatients(${i})">${i}</button>`;
            } else if (i === current_page - 3 || i === current_page + 3) {
                html += '<button disabled>...</button>';
            }
        }
        
        html += `<button ${current_page === last_page ? 'disabled' : ''} onclick="spa.loadPatients(${current_page + 1})">التالي</button>`;
        
        pagination.innerHTML = html;
    }

    togglePatientsFilters() {
        const panel = document.getElementById('patientsFiltersPanel');
        if (panel) {
            panel.classList.toggle('open');
        }
    }

    debouncePatientsSearch() {
        clearTimeout(this.patientsSearchTimeout);
        this.patientsSearchTimeout = setTimeout(() => this.loadPatients(1), 300);
    }

    resetPatientsFilters() {
        document.getElementById('patientsSearch').value = '';
        document.getElementById('patientsClinicFilter').value = '';
        document.getElementById('patientsDoctorFilter').value = '';
        document.getElementById('patientsGenderFilter').value = '';
        document.getElementById('patientsDateFrom').value = '';
        document.getElementById('patientsDateTo').value = '';
        this.loadPatients(1);
    }

    async openAddPatientModal() {
        document.getElementById('patientsModalTitle').textContent = 'إضافة مريض جديد';
        document.getElementById('patientsForm').reset();
        document.getElementById('patientId').value = '';
        
        try {
            const res = await this.apiCall('/api/patients/next-file-number');
            const data = await res.json();
            document.getElementById('patientFileNumber').value = data.file_number;
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في توليد رقم الملف', 'error');
        }
        
        document.getElementById('patientsModal').classList.add('open');
    }

    async openEditPatientModal(patientId) {
        try {
            const res = await this.apiCall(`/api/patients/${patientId}`);
            const data = await res.json();
            const patient = data.patient;
            
            document.getElementById('patientsModalTitle').textContent = 'تعديل بيانات المريض';
            document.getElementById('patientId').value = patient.id;
            document.getElementById('patientFileNumber').value = patient.file_number;
            document.getElementById('patientFullName').value = patient.full_name;
            document.getElementById('patientAge').value = patient.age;
            document.getElementById('patientGender').value = patient.gender;
            document.getElementById('patientResidence').value = patient.residence || '';
            document.getElementById('patientPhone').value = patient.phone;
            
            document.getElementById('patientsModal').classList.add('open');
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في تحميل بيانات المريض', 'error');
        }
    }

    closePatientsModal() {
        document.getElementById('patientsModal').classList.remove('open');
    }

    async savePatient(e) {
        e.preventDefault();
        
        const patientId = document.getElementById('patientId').value;
        const data = {
            full_name: document.getElementById('patientFullName').value,
            age: parseInt(document.getElementById('patientAge').value),
            gender: document.getElementById('patientGender').value,
            residence: document.getElementById('patientResidence').value || null,
            phone: document.getElementById('patientPhone').value
        };

        try {
            const url = patientId ? `/api/patients/${patientId}` : '/api/patients';
            const method = patientId ? 'PUT' : 'POST';
            
            const res = await this.apiCall(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (res.ok) {
                this.showAlert('pageAlert', patientId ? 'تم تحديث المريض بنجاح' : 'تم إضافة المريض بنجاح', 'success');
                this.closePatientsModal();
                this.loadPatients(this.patientsCurrentPage || 1);
            } else {
                const error = await res.json();
                this.showAlert('pageAlert', error.message || 'خطأ في الحفظ', 'error');
            }
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        }
    }

    async deletePatient(patientId, patientName) {
        const confirmed = await this.showConfirm(
            'حذف المريض',
            `هل أنت متأكد من حذف المريض "${patientName}"؟`,
            '🗑️'
        );

        if (!confirmed) return;

        try {
            const res = await this.apiCall(`/api/patients/${patientId}`, { method: 'DELETE' });

            if (res.ok) {
                this.showAlert('pageAlert', 'تم حذف المريض بنجاح ✓', 'success');
                const table = document.getElementById('patientsTable');
                const row = table.querySelector(`tr[data-patient-id="${patientId}"]`);
                if (row) {
                    row.style.opacity = '0.5';
                    setTimeout(() => row.remove(), 300);
                } else {
                    this.loadPatients(this.patientsCurrentPage || 1);
                }
            } else {
                const error = await res.json();
                this.showAlert('pageAlert', error.message || 'خطأ في حذف المريض', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showAlert('pageAlert', 'خطأ في الحذف - تحقق من الاتصال', 'error');
        }
    }

    async openPatientDrawer(patientId) {
        document.getElementById('patientsDrawer').classList.add('open');
        document.getElementById('patientsDrawerContent').innerHTML = '<div class="loading">جاري التحميل...</div>';

        try {
            const res = await this.apiCall(`/api/patients/${patientId}`);
            const data = await res.json();
            this.renderPatientDetails(data);
        } catch (error) {
            document.getElementById('patientsDrawerContent').innerHTML = '<div class="loading">خطأ في التحميل</div>';
        }
    }

    renderPatientDetails(data) {
        const patient = data.patient;
        const visits = data.visits || [];

        document.getElementById('patientsDrawerTitle').textContent = patient.full_name;
        document.getElementById('patientsDrawerSubtitle').textContent = `رقم الملف: ${patient.file_number}`;

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

        document.getElementById('patientsDrawerContent').innerHTML = html;
    }

    closePatientsDrawer() {
        document.getElementById('patientsDrawer').classList.remove('open');
    }

    async openViewModal(patientId) {
        const modal = document.getElementById('patientsViewModal');
        if (!modal) {
            console.error('View modal not found');
            return;
        }
        modal.classList.add('open');
        document.getElementById('patientsViewModalContent').innerHTML = '<div class="loading">جاري التحميل...</div>';

        try {
            const res = await this.apiCall(`/api/patients/${patientId}`);
            const data = await res.json();
            this.renderViewModalContent(data);
        } catch (error) {
            document.getElementById('patientsViewModalContent').innerHTML = '<div class="loading">خطأ في التحميل</div>';
        }
    }

    renderViewModalContent(data) {
        const patient = data.patient;
        const visits = data.visits || [];

        document.getElementById('patientsViewModalTitle').textContent = patient.full_name;
        document.getElementById('patientsViewModalSubtitle').textContent = `رقم الملف: ${patient.file_number}`;

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

        document.getElementById('patientsViewModalContent').innerHTML = html;
    }

    closeViewModal() {
        const modal = document.getElementById('patientsViewModal');
        if (modal) {
            modal.classList.remove('open');
        }
    }

    async loadFinance() {
        try {
            const [summaryRes, doctorsRes] = await Promise.all([
                this.apiCall('/api/finance/summary'),
                this.apiCall('/api/finance/doctors')
            ]);

            const summary = await summaryRes.json();
            const doctors = await doctorsRes.json();

            document.getElementById('financeStats').innerHTML = `
                <div class="stat-card">
                    <h3>${summary.total_examination_fees || 0} ر.س</h3>
                    <p>رسوم الكشف</p>
                </div>
                <div class="stat-card">
                    <h3>${summary.total_amount_received || 0} ر.س</h3>
                    <p>المستلم</p>
                </div>
                <div class="stat-card">
                    <h3>${summary.total_center_share || 0} ر.س</h3>
                    <p>حصة المجمع</p>
                </div>
                <div class="stat-card">
                    <h3>${summary.net_doctor_payable || 0} ر.س</h3>
                    <p>صافي الأطباء</p>
                </div>
            `;

            const tbody = document.getElementById('financeTable');
            tbody.innerHTML = (doctors.data || []).map(d => `
                <tr>
                    <td>${d.doctor_name}</td>
                    <td><span class="badge badge-info">${d.total_visits}</span></td>
                    <td>${d.doctor_share} ر.س</td>
                    <td>${d.deductions} ر.س</td>
                    <td><strong>${d.net_payable} ر.س</strong></td>
                </tr>
            `).join('');
        } catch (error) {
            console.error('Error loading finance:', error);
        }
    }

    async loadAppointments() {
        try {
            const res = await this.apiCall('/api/appointments?per_page=50');
            const data = await res.json();
            
            const tbody = document.getElementById('appointmentsTable');
            tbody.innerHTML = (data.data || []).map(a => `
                <tr>
                    <td>${a.patient.full_name}</td>
                    <td>${a.doctor.full_name}</td>
                    <td><span class="badge badge-success">${new Date(a.appointment_date).toLocaleString('ar-SA')}</span></td>
                    <td>${a.notes || '-'}</td>
                </tr>
            `).join('');
        } catch (error) {
            console.error('Error loading appointments:', error);
        }
    }

    // Reception Functions
    async searchPatient() {
        const fileNumber = document.getElementById('receptionFileNumber').value.trim();
        if (!fileNumber) {
            alert('الرجاء إدخال رقم الملف');
            return;
        }

        try {
            const res = await this.apiCall(`/api/reception/patients/by-file/${fileNumber}`);
            if (res.ok) {
                const data = await res.json();
                document.getElementById('receptionFullName').value = data.patient.full_name;
                document.getElementById('receptionAge').value = data.patient.age;
                document.getElementById('receptionGender').value = data.patient.gender;
                document.getElementById('receptionPhone').value = data.patient.phone;
                alert('تم العثور على المريض');
            } else {
                alert('لم يتم العثور على المريض - سيتم إنشاء مريض جديد');
            }
        } catch (error) {
            alert('خطأ في البحث');
        }
    }

    async loadDoctors() {
        const clinicId = document.getElementById('receptionClinic').value;
        if (!clinicId) return;

        try {
            const res = await this.apiCall(`/api/reception/doctors?clinic_id=${clinicId}`);
            const data = await res.json();
            
            const select = document.getElementById('receptionDoctor');
            select.innerHTML = '<option value="">اختر الطبيب...</option>' + 
                data.doctors.map(d => `<option value="${d.id}" data-fee="${d.examination_fee}">${d.full_name}</option>`).join('');
            
            select.onchange = function() {
                const option = this.options[this.selectedIndex];
                if (option.dataset.fee) {
                    document.getElementById('receptionFee').value = option.dataset.fee;
                }
            };
        } catch (error) {
            console.error('Error loading doctors:', error);
        }
    }

    async createVisit() {
        const patientData = {
            file_number: document.getElementById('receptionFileNumber').value || null,
            full_name: document.getElementById('receptionFullName').value,
            age: parseInt(document.getElementById('receptionAge').value),
            gender: document.getElementById('receptionGender').value,
            phone: document.getElementById('receptionPhone').value
        };

        try {
            // Upsert patient
            const patientRes = await fetch('/api/reception/patients/upsert', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(patientData)
            });

            const patientResult = await patientRes.json();
            const patientId = patientResult.patient.id;

            // Create visit
            const visitData = {
                patient_id: patientId,
                doctor_id: parseInt(document.getElementById('receptionDoctor').value),
                clinic_id: parseInt(document.getElementById('receptionClinic').value),
                visit_date: new Date().toISOString().split('T')[0],
                visit_type: document.getElementById('receptionVisitType').value,
                examination_fee: parseFloat(document.getElementById('receptionFee').value) || 0,
                amount_received: parseFloat(document.getElementById('receptionFee').value) || 0,
                complex_discount: 0,
                doctor_discount: 0
            };

            const visitRes = await fetch('/api/reception/visits', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(visitData)
            });

            if (visitRes.ok) {
                alert('تم حفظ الزيارة بنجاح');
                // Clear form
                document.getElementById('receptionFileNumber').value = '';
                document.getElementById('receptionFullName').value = '';
                document.getElementById('receptionAge').value = '';
                document.getElementById('receptionGender').value = '';
                document.getElementById('receptionPhone').value = '';
                
                // Refresh dashboard if admin
                if (this.user.role === 'admin') {
                    this.loadDashboard();
                }
            } else {
                alert('خطأ في حفظ الزيارة');
            }
        } catch (error) {
            alert('خطأ في الاتصال');
        }
    }

    // Patients Search
    async searchPatients() {
        const search = document.getElementById('patientsSearch').value;
        try {
            const res = await this.apiCall(`/api/patients?search=${encodeURIComponent(search)}&per_page=50`);
            const data = await res.json();
            
            const tbody = document.getElementById('patientsTable');
            tbody.innerHTML = (data.data || []).map(p => `
                <tr>
                    <td><span class="badge badge-info">${p.file_number}</span></td>
                    <td>${p.full_name}</td>
                    <td>${p.age}</td>
                    <td>${p.gender === 'male' ? 'ذكر' : 'أنثى'}</td>
                    <td>${p.phone}</td>
                    <td><span class="badge badge-success">${p.visits_count || 0}</span></td>
                </tr>
            `).join('');
        } catch (error) {
            console.error('Error searching patients:', error);
        }
    }

    // API Helper
    async apiCall(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Accept': 'application/json'
            }
        };

        const mergedOptions = { ...defaultOptions, ...options };
        if (options.headers) {
            mergedOptions.headers = { ...defaultOptions.headers, ...options.headers };
        }

        const response = await fetch(url, mergedOptions);
        
        if (response.status === 401) {
            this.logout();
            throw new Error('Unauthorized');
        }

        return response;
    }

    // Alert Helper
    showAlert(elementId, message, type) {
        const container = document.getElementById(elementId);
        if (!container) return;
        container.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        setTimeout(() => container.innerHTML = '', 5000);
    }

    // Confirmation Dialog Helper
    showConfirm(title, message, icon = '⚠️') {
        return new Promise((resolve) => {
            const dialog = document.getElementById('confirmDialog');
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
}

// Initialize SPA
const spa = new NabdhSPA();

// Global functions for onclick handlers
function logout() {
    spa.logout();
}

function searchPatient() {
    spa.searchPatient();
}

function loadDoctors() {
    spa.loadDoctors();
}

function createVisit() {
    spa.createVisit();
}

function searchPatients() {
    spa.searchPatients();
}
