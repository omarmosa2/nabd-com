// SPA Router for Nabdh Medical System
class NabdhSPA {
    constructor() {
        this.token = localStorage.getItem('token');
        this.user = JSON.parse(localStorage.getItem('user') || 'null');
        this.currentPage = null;
        this.appointmentsCurrentPage = 1;
        this.appointmentsView = 'table';
        this.calendarDate = new Date();
        this.calendarData = {};
        this.availabilityCheckTimer = null;
        this.appointmentsPollTimer = null;
        this.clinicsCurrentPage = 1;
        this.clinicsPollTimer = null;
        this.doctorsCurrentPage = 1;
        this.doctorsPollTimer = null;
        this.currentDoctorId = null;
        this.currentDoctorTab = 'overview';
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
                { path: '/doctors', icon: '👨‍⚕️', label: 'الأطباء' },
                { path: '/clinics', icon: '🏥', label: 'العيادات' },
                { path: '/appointments', icon: '📅', label: 'المواعيد' },
                { path: '/finance', icon: '🧾', label: 'الفواتير' },
                { path: '/settings', icon: '⚙️', label: 'الإعدادات' }
            ],
            reception: [
                { path: '/reception', icon: '🏥', label: 'الرئيسية' },
                { path: '/patients', icon: '👥', label: 'المرضى' },
                { path: '/doctors', icon: '👨‍⚕️', label: 'الأطباء' },
                { path: '/clinics', icon: '🏥', label: 'العيادات' },
                { path: '/appointments', icon: '📅', label: 'المواعيد' }
            ],
            doctor: [
                { path: '/doctors', icon: '👨‍⚕️', label: 'ملفي' },
                { path: '/patients', icon: '👥', label: 'المرضى' },
                { path: '/clinics', icon: '🏥', label: 'العيادة' },
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
            '/doctors': { page: 'doctors', title: 'إدارة الأطباء', roles: ['admin', 'reception', 'doctor'] },
            '/clinics': { page: 'clinics', title: 'إدارة العيادات', roles: ['admin', 'reception', 'doctor'] },
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
        if (pageName !== 'doctors') this.stopDoctorsPolling();
        if (pageName !== 'clinics') this.stopClinicsPolling();
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
            case 'clinics':
                await this.loadClinicsPage();
                break;
            case 'doctors':
                await this.loadDoctorsPage();
                break;
        }
    }

    async loadDashboard() {
        try {
            const requests = [
                this.apiCall('/api/dashboard/stats'),
                this.apiCall('/api/dashboard/charts'),
                this.apiCall('/api/dashboard/appointments'),
            ];
            if (this.user.role === 'admin' || this.user.role === 'reception') {
                requests.push(this.apiCall('/api/dashboard/clinics'));
                requests.push(this.apiCall('/api/dashboard/doctors'));
            }

            const responses = await Promise.all(requests);
            const stats = await responses[0].json();
            const charts = await responses[1].json();
            const aptsData = await responses[2].json();
            const clinicsData = responses[3] ? await responses[3].json() : null;
            const doctorsData = responses[4] ? await responses[4].json() : null;

            document.getElementById('dashboardStats').innerHTML = `
                <div class="stat-card" onclick="spa.navigate('/patients')">
                    <h3>${stats.patients_today || 0}</h3>
                    <p>مرضى اليوم</p>
                </div>
                <div class="stat-card" onclick="spa.navigate('/appointments')">
                    <h3>${stats.appointments_today || 0}</h3>
                    <p>مواعيد اليوم</p>
                </div>
                <div class="stat-card" onclick="spa.navigate('/appointments')">
                    <h3>${stats.appointments_upcoming || 0}</h3>
                    <p>مواعيد قادمة (24 ساعة)</p>
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

            const clinicsCard = document.getElementById('topClinicsCard');
            const clinicsContainer = document.getElementById('topClinicsContainer');
            if (clinicsCard && clinicsContainer && clinicsData && clinicsData.top_clinics && clinicsData.top_clinics.length > 0) {
                clinicsCard.style.display = '';
                clinicsContainer.innerHTML = clinicsData.top_clinics.map((c, idx) => {
                    const medal = idx === 0 ? '🥇' : idx === 1 ? '🥈' : '🥉';
                    const revenue = (c.monthly_revenue || 0).toLocaleString('en-US');
                    return `
                        <div class="top-clinic-item" onclick="spa.navigate('/clinics')">
                            <div class="top-clinic-rank">${medal}</div>
                            <div class="top-clinic-info">
                                <div class="top-clinic-name">${this.escapeHtml(c.name)}</div>
                                <div class="top-clinic-stats">
                                    👨‍⚕️ ${c.doctors_count} طبيب ·
                                    👥 ${c.patients_count} مريض ·
                                    📅 ${c.appointments_count} موعد ·
                                    💰 ${revenue} ر.س
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else if (clinicsCard) {
                clinicsCard.style.display = 'none';
            }

            const doctorsStatsGrid = document.getElementById('doctorsStatsGridDashboard');
            const topDoctorsCard = document.getElementById('topDoctorsCard');
            const topDoctorsContainer = document.getElementById('topDoctorsContainer');
            if (doctorsStatsGrid && doctorsData) {
                doctorsStatsGrid.style.display = '';
                const revenue = (doctorsData.doctors_revenue || 0).toLocaleString('en-US');
                doctorsStatsGrid.innerHTML = `
                    <div class="stat-card" onclick="spa.navigate('/doctors')">
                        <h3>${doctorsData.total_doctors || 0}</h3>
                        <p>إجمالي الأطباء</p>
                    </div>
                    <div class="stat-card" onclick="spa.navigate('/doctors')">
                        <h3>${doctorsData.active_doctors || 0}</h3>
                        <p>طبيب نشط</p>
                    </div>
                    <div class="stat-card" onclick="spa.navigate('/doctors')">
                        <h3>${revenue} ر.س</h3>
                        <p>إيرادات الأطباء (الشهر)</p>
                    </div>
                `;
            } else if (doctorsStatsGrid) {
                doctorsStatsGrid.style.display = 'none';
            }
            if (topDoctorsCard && topDoctorsContainer && doctorsData && doctorsData.top_by_visits && doctorsData.top_by_visits.length > 0) {
                topDoctorsCard.style.display = '';
                topDoctorsContainer.innerHTML = doctorsData.top_by_visits.slice(0, 5).map((d, idx) => {
                    const medal = idx === 0 ? '🥇' : idx === 1 ? '🥈' : idx === 2 ? '🥉' : '·';
                    const initials = (d.full_name || '?').split(' ').map(s => s[0]).slice(0, 2).join('');
                    const revenue = (d.monthly_revenue || 0).toLocaleString('en-US');
                    return `
                        <div class="top-doctor-item" onclick="spa.openDoctorDrawer(${d.id})">
                            <div class="top-clinic-rank">${medal}</div>
                            <div class="top-doctor-avatar">${this.escapeHtml(initials)}</div>
                            <div class="top-doctor-info">
                                <div class="top-doctor-name">د. ${this.escapeHtml(d.full_name)}</div>
                                <div class="top-doctor-meta">
                                    ${d.specialization ? this.escapeHtml(d.specialization) + ' · ' : ''}
                                    ${d.clinic_name ? this.escapeHtml(d.clinic_name) + ' · ' : ''}
                                    👥 ${d.patients_count || 0} · 🏥 ${d.visits_count || 0} · 💰 ${revenue} ر.س
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else if (topDoctorsCard) {
                topDoctorsCard.style.display = 'none';
            }

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
            const [clinicsRes, aptsRes] = await Promise.all([
                this.apiCall('/api/clinics/active'),
                this.apiCall('/api/appointments?date=' + this.formatDateStr(new Date()) + '&per_page=50'),
            ]);
            const [clinicsData, aptsData] = await Promise.all([clinicsRes.json(), aptsRes.json()]);

            const select = document.getElementById('receptionClinic');
            if (select) {
                select.innerHTML = '<option value="">اختر العيادة...</option>' +
                    (clinicsData.data || []).map(c => `<option value="${c.id}">${this.escapeHtml(c.name)}</option>`).join('');
            }

            this.renderReceptionTodayAppointments(aptsData.data || []);
        } catch (error) {
            console.error('Error loading reception:', error);
        }
    }

    renderReceptionTodayAppointments(appointments) {
        const container = document.getElementById('receptionTodayAppointments');
        if (!container) return;

        if (appointments.length === 0) {
            container.innerHTML = '<div class="empty-state" style="padding: 30px;"><div class="icon">📅</div><h3>لا توجد مواعيد اليوم</h3></div>';
            return;
        }

        appointments.sort((a, b) => new Date(a.appointment_date) - new Date(b.appointment_date));
        container.innerHTML = appointments.map(a => {
            const t = new Date(a.appointment_date);
            const hh = String(t.getHours()).padStart(2, '0');
            const mm = String(t.getMinutes()).padStart(2, '0');
            const canConvert = (a.status === 'scheduled' || a.status === 'completed') && !a.visit_id;
            return `
                <div class="day-appointment-card">
                    <div class="day-appointment-time">⏰ ${hh}:${mm}</div>
                    <div class="day-appointment-info">
                        <strong>${this.escapeHtml(a.patient?.full_name || '')}</strong>
                        <small>${this.escapeHtml(a.patient?.file_number || '')} • ${this.escapeHtml(a.doctor?.full_name || '')} • ${this.escapeHtml(a.clinic?.name || '')}</small>
                    </div>
                    <span class="apt-status-pill apt-status-${a.status}">${a.status_label || a.status}</span>
                    <div style="display: flex; gap: 6px;">
                        ${canConvert ? `<button class="icon-btn" title="تحويل إلى زيارة" onclick="spa.convertAppointmentToVisit(${a.id})">🩺</button>` : ''}
                        <button class="icon-btn" title="عرض" onclick="spa.openAppointmentDetails(${a.id})">👁</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Patients Module - Enhanced
    async loadPatientsFilters() {
        try {
            const [clinicsRes, doctorsRes] = await Promise.all([
                this.apiCall('/api/clinics/active'),
                this.apiCall('/api/doctors/active')
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
        const upcomingAppointments = data.upcoming_appointments || [];

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
        `;

        if (upcomingAppointments.length > 0) {
            html += '<h3 style="margin: 20px 0 12px; color: #333;">📅 المواعيد القادمة</h3>';
            html += upcomingAppointments.map(a => {
                const aptDate = new Date(a.appointment_date);
                return `
                    <div class="visit-card" style="border-right-color: #2563eb; cursor: pointer;" onclick="spa.closeViewModal(); spa.openAppointmentDetails(${a.id})">
                        <h4>${aptDate.toLocaleString('ar-SA')}</h4>
                        <p><strong>الطبيب:</strong> ${this.escapeHtml(a.doctor?.full_name || '')}</p>
                        <p><strong>العيادة:</strong> ${this.escapeHtml(a.clinic?.name || '')}</p>
                        <p><span class="apt-status-pill apt-status-${a.status}">${a.status_label || a.status}</span></p>
                        ${a.notes ? `<p style="margin-top: 8px; color: #4b5563;"><em>${this.escapeHtml(a.notes)}</em></p>` : ''}
                    </div>
                `;
            }).join('');
        }

        html += '<h3 style="margin: 20px 0 12px; color: #333;">سجل الزيارات</h3>';

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
        await this.loadAppointmentsFiltersData();
        if (this.appointmentsView === 'calendar') {
            await this.loadAppointmentsCalendar();
        } else {
            await this.loadAppointmentsTable();
        }
        this.startAppointmentsPolling();
    }

    // ===== Appointments Module =====
    async loadAppointmentsFiltersData() {
        try {
            const [clinicsRes, doctorsRes, statusesRes] = await Promise.all([
                this.apiCall('/api/clinics/active'),
                this.apiCall('/api/doctors/active'),
                this.apiCall('/api/appointments/statuses'),
            ]);
            const [clinicsData, doctorsData, statusesData] = await Promise.all([
                clinicsRes.json(),
                doctorsRes.json(),
                statusesRes.json(),
            ]);

            const fillSelect = (id, items, valueKey = 'id', labelKey = 'name', prefix = '') => {
                const el = document.getElementById(id);
                if (!el) return;
                const currentValue = el.value;
                el.innerHTML = `<option value="">${prefix}</option>` +
                    items.map(it => `<option value="${it[valueKey]}">${it[labelKey]}</option>`).join('');
                if (currentValue) el.value = currentValue;
            };

            fillSelect('appointmentsClinicFilter', clinicsData.data || [], 'id', 'name', 'جميع العيادات');
            fillSelect('appointmentsDoctorFilter', doctorsData.data || [], 'id', 'full_name', 'جميع الأطباء');
            fillSelect('appointmentsStatusFilter', (statusesData.data || []).map(s => ({
                value: s.value, label: s.label
            })), 'value', 'label', 'جميع الحالات');
        } catch (error) {
            console.error('Error loading appointment filters data:', error);
        }
    }

    async loadAppointmentsTable(page = 1) {
        this.appointmentsCurrentPage = page;
        const search = document.getElementById('appointmentsSearch')?.value || '';
        const clinicId = document.getElementById('appointmentsClinicFilter')?.value || '';
        const doctorId = document.getElementById('appointmentsDoctorFilter')?.value || '';
        const status = document.getElementById('appointmentsStatusFilter')?.value || '';
        const dateFrom = document.getElementById('appointmentsDateFrom')?.value || '';
        const dateTo = document.getElementById('appointmentsDateTo')?.value || '';

        let url = `/api/appointments?page=${page}&per_page=20`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (clinicId) url += `&clinic_id=${clinicId}`;
        if (doctorId) url += `&doctor_id=${doctorId}`;
        if (status) url += `&status=${status}`;
        if (dateFrom) url += `&date_from=${dateFrom}`;
        if (dateTo) url += `&date_to=${dateTo}`;

        const tbody = document.getElementById('appointmentsTable');
        if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="loading">جاري التحميل...</td></tr>';

        try {
            const res = await this.apiCall(url);
            const data = await res.json();
            this.renderAppointmentsTable(data);
        } catch (error) {
            console.error('Error loading appointments:', error);
            if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="loading">خطأ في التحميل</td></tr>';
        }
    }

    renderAppointmentsTable(data) {
        const appointments = data.data || [];
        const tbody = document.getElementById('appointmentsTable');
        if (!tbody) return;

        if (appointments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="empty-state"><div class="icon">📅</div><h3>لا توجد مواعيد</h3><p>لم يتم العثور على مواعيد مطابقة</p></td></tr>';
            const pag = document.getElementById('appointmentsPagination');
            if (pag) pag.innerHTML = '';
            return;
        }

        tbody.innerHTML = appointments.map((a, index) => {
            const serial = ((data.current_page || 1) - 1) * (data.per_page || 20) + index + 1;
            const aptDate = a.appointment_date ? new Date(a.appointment_date) : null;
            const formatted = aptDate ? aptDate.toLocaleString('ar-SA', {
                year: 'numeric', month: '2-digit', day: '2-digit',
                hour: '2-digit', minute: '2-digit',
            }) : '-';

            const canEdit = a.status !== 'converted_to_visit' && a.status !== 'cancelled';
            const canCancel = a.status === 'scheduled';
            const canConvert = (a.status === 'scheduled' || a.status === 'completed')
                && !a.visit_id
                && (this.user.role === 'admin' || this.user.role === 'reception');

            return `
                <tr data-appointment-id="${a.id}">
                    <td class="serial-cell">${serial}</td>
                    <td>
                        <div style="display:flex; flex-direction:column;">
                            <strong>${a.patient?.full_name || '-'}</strong>
                            <small style="color:#6b7280;">${a.patient?.file_number || ''}</small>
                        </div>
                    </td>
                    <td>${a.doctor?.full_name || '-'}</td>
                    <td>${a.clinic?.name || '-'}</td>
                    <td><span style="font-weight:600;">${formatted}</span></td>
                    <td>${a.duration_minutes || 30} د</td>
                    <td><span class="apt-status-pill apt-status-${a.status}">${a.status_label || a.status}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-btn" title="عرض" onclick="event.stopPropagation(); spa.openAppointmentDetails(${a.id})">👁</button>
                            <button class="icon-btn" title="تعديل" ${canEdit ? '' : 'disabled'} onclick="event.stopPropagation(); spa.openAppointmentModal(${a.id})">✎</button>
                            ${canConvert ? `<button class="icon-btn" title="تحويل لزيارة" onclick="event.stopPropagation(); spa.convertAppointmentToVisit(${a.id})">🩺</button>` : ''}
                            ${canCancel ? `<button class="icon-btn danger" title="إلغاء" onclick="event.stopPropagation(); spa.cancelAppointment(${a.id})">✕</button>` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        this.renderAppointmentsPagination(data);
    }

    renderAppointmentsPagination(data) {
        const pag = document.getElementById('appointmentsPagination');
        if (!pag) return;
        const { current_page, last_page } = data;
        let html = '';
        html += `<button ${current_page === 1 ? 'disabled' : ''} onclick="spa.loadAppointmentsTable(${current_page - 1})">السابق</button>`;
        for (let i = 1; i <= last_page; i++) {
            if (i === 1 || i === last_page || (i >= current_page - 2 && i <= current_page + 2)) {
                html += `<button class="${i === current_page ? 'active' : ''}" onclick="spa.loadAppointmentsTable(${i})">${i}</button>`;
            } else if (i === current_page - 3 || i === current_page + 3) {
                html += '<button disabled>...</button>';
            }
        }
        html += `<button ${current_page === last_page ? 'disabled' : ''} onclick="spa.loadAppointmentsTable(${current_page + 1})">التالي</button>`;
        pag.innerHTML = html;
    }

    switchAppointmentsView(view) {
        this.appointmentsView = view;
        const tableView = document.getElementById('appointmentsTableView');
        const calView = document.getElementById('appointmentsCalendarView');
        const tableBtn = document.getElementById('aptViewTable');
        const calBtn = document.getElementById('aptViewCalendar');

        if (view === 'table') {
            tableView.style.display = '';
            calView.style.display = 'none';
            tableBtn.classList.add('active');
            calBtn.classList.remove('active');
            this.loadAppointmentsTable(this.appointmentsCurrentPage);
        } else {
            tableView.style.display = 'none';
            calView.style.display = '';
            tableBtn.classList.remove('active');
            calBtn.classList.add('active');
            this.loadAppointmentsCalendar();
        }
    }

    async loadAppointmentsCalendar() {
        const year = this.calendarDate.getFullYear();
        const month = this.calendarDate.getMonth() + 1;

        const clinicId = document.getElementById('appointmentsClinicFilter')?.value || '';
        const doctorId = document.getElementById('appointmentsDoctorFilter')?.value || '';

        let url = `/api/appointments/calendar?year=${year}&month=${month}`;
        if (clinicId) url += `&clinic_id=${clinicId}`;
        if (doctorId) url += `&doctor_id=${doctorId}`;

        try {
            const res = await this.apiCall(url);
            const data = await res.json();
            this.calendarData = {};
            (data.days || []).forEach(d => { this.calendarData[d.date] = d; });
            this.renderCalendar(year, month);
        } catch (error) {
            console.error('Error loading calendar:', error);
        }
    }

    renderCalendar(year, month) {
        const titleEl = document.getElementById('calendarTitle');
        const gridEl = document.getElementById('appointmentsCalendarGrid');
        if (!titleEl || !gridEl) return;

        const monthNames = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
        titleEl.textContent = `${monthNames[month - 1]} ${year}`;

        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const startWeekday = firstDay.getDay();
        const totalDays = lastDay.getDate();
        const todayStr = new Date().toISOString().slice(0, 10);

        let html = '';
        for (let i = 0; i < startWeekday; i++) {
            const prevDate = new Date(year, month - 1, -startWeekday + i + 1);
            const dateStr = this.formatDateStr(prevDate);
            html += `<div class="calendar-day outside-month" data-date="${dateStr}">
                <div class="calendar-day-number">${prevDate.getDate()}</div>
            </div>`;
        }
        for (let day = 1; day <= totalDays; day++) {
            const date = new Date(year, month - 1, day);
            const dateStr = this.formatDateStr(date);
            const data = this.calendarData[dateStr];
            const count = data?.count || 0;
            const preview = (data?.appointments || []).slice(0, 3).map(a => {
                const t = new Date(a.appointment_date);
                const hh = String(t.getHours()).padStart(2, '0');
                const mm = String(t.getMinutes()).padStart(2, '0');
                return `<span class="preview-item">⏰ ${hh}:${mm} • ${this.escapeHtml(a.patient?.full_name || '')}</span>`;
            }).join('');

            const isToday = dateStr === todayStr;
            html += `
                <div class="calendar-day ${isToday ? 'today' : ''} ${count > 0 ? 'has-appointments' : ''}"
                     data-date="${dateStr}"
                     onclick="spa.openDayAppointments('${dateStr}')">
                    <div class="calendar-day-number">${day}</div>
                    ${count > 0 ? `<span class="calendar-day-count">${count}</span>` : ''}
                    <div class="calendar-day-preview">${preview}</div>
                </div>
            `;
        }
        const remaining = (7 - ((startWeekday + totalDays) % 7)) % 7;
        for (let i = 1; i <= remaining; i++) {
            const nextDate = new Date(year, month, i);
            const dateStr = this.formatDateStr(nextDate);
            html += `<div class="calendar-day outside-month" data-date="${dateStr}">
                <div class="calendar-day-number">${nextDate.getDate()}</div>
            </div>`;
        }
        gridEl.innerHTML = html;
    }

    changeCalendarMonth(delta) {
        this.calendarDate.setMonth(this.calendarDate.getMonth() + delta);
        this.loadAppointmentsCalendar();
    }

    goToTodayCalendar() {
        this.calendarDate = new Date();
        this.loadAppointmentsCalendar();
    }

    async openDayAppointments(dateStr) {
        try {
            const res = await this.apiCall(`/api/appointments?date=${dateStr}&per_page=100`);
            const data = await res.json();
            const list = data.data || [];

            const modal = document.getElementById('appointmentDayModal');
            const title = document.getElementById('appointmentDayTitle');
            const content = document.getElementById('appointmentDayContent');

            const d = new Date(dateStr);
            const formatted = d.toLocaleDateString('ar-SA', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            title.textContent = `مواعيد ${formatted}`;

            if (list.length === 0) {
                content.innerHTML = '<div class="empty-state"><div class="icon">📅</div><h3>لا توجد مواعيد</h3></div>';
            } else {
                content.innerHTML = list.map(a => {
                    const t = new Date(a.appointment_date);
                    const hh = String(t.getHours()).padStart(2, '0');
                    const mm = String(t.getMinutes()).padStart(2, '0');
                    return `
                        <div class="day-appointment-card">
                            <div class="day-appointment-time">⏰ ${hh}:${mm}</div>
                            <div class="day-appointment-info">
                                <strong>${this.escapeHtml(a.patient?.full_name || '')}</strong>
                                <small>${this.escapeHtml(a.doctor?.full_name || '')} • ${this.escapeHtml(a.clinic?.name || '')}</small>
                            </div>
                            <span class="apt-status-pill apt-status-${a.status}">${a.status_label || a.status}</span>
                            <button class="icon-btn" title="عرض" onclick="spa.closeAppointmentDayModal(); spa.openAppointmentDetails(${a.id})">👁</button>
                        </div>
                    `;
                }).join('');
            }
            modal.classList.add('open');
        } catch (error) {
            console.error('Error loading day appointments:', error);
        }
    }

    closeAppointmentDayModal() {
        document.getElementById('appointmentDayModal')?.classList.remove('open');
    }

    toggleAppointmentsFilters() {
        document.getElementById('appointmentsFiltersPanel')?.classList.toggle('open');
    }

    debounceAppointmentsSearch() {
        clearTimeout(this.appointmentsSearchTimeout);
        this.appointmentsSearchTimeout = setTimeout(() => this.onAppointmentsFilterChange(), 300);
    }

    onAppointmentsFilterChange() {
        if (this.appointmentsView === 'calendar') {
            this.loadAppointmentsCalendar();
        } else {
            this.loadAppointmentsTable(1);
        }
    }

    resetAppointmentsFilters() {
        const ids = ['appointmentsSearch', 'appointmentsClinicFilter', 'appointmentsDoctorFilter',
                     'appointmentsStatusFilter', 'appointmentsDateFrom', 'appointmentsDateTo'];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        this.onAppointmentsFilterChange();
    }

    async openAppointmentModal(appointmentId = null) {
        const modal = document.getElementById('appointmentModal');
        const titleEl = document.getElementById('appointmentModalTitle');
        const form = document.getElementById('appointmentForm');
        form.reset();
        document.getElementById('appointmentId').value = '';
        this.hideAvailabilityHint();

        if (appointmentId) {
            titleEl.textContent = 'تعديل موعد';
            try {
                const res = await this.apiCall(`/api/appointments/${appointmentId}`);
                const data = await res.json();
                const a = data.data || data;
                document.getElementById('appointmentId').value = a.id;
                document.getElementById('appointmentPatientId').value = a.patient.id;
                document.getElementById('appointmentClinicId').value = a.clinic.id;
                await this.loadDoctorsForAppointmentModal(a.doctor.id);
                document.getElementById('appointmentDoctorId').value = a.doctor.id;
                document.getElementById('appointmentDate').value = a.appointment_date.replace(' ', 'T').slice(0, 16);
                document.getElementById('appointmentDuration').value = a.duration_minutes || 30;
                document.getElementById('appointmentStatus').value = a.status;
                document.getElementById('appointmentNotes').value = a.notes || '';
            } catch (error) {
                this.showAlert('pageAlert', 'خطأ في تحميل الموعد', 'error');
                return;
            }
        } else {
            titleEl.textContent = 'حجز موعد جديد';
            document.getElementById('appointmentStatus').value = 'scheduled';
            await Promise.all([
                this.loadPatientsForAppointmentModal(),
                this.loadClinicsForAppointmentModal(),
            ]);
        }
        modal.classList.add('open');
        this.checkAppointmentAvailability();
    }

    closeAppointmentModal() {
        document.getElementById('appointmentModal')?.classList.remove('open');
        this.hideAvailabilityHint();
    }

    setAppointmentDuration(minutes) {
        const input = document.getElementById('appointmentDuration');
        if (!input) return;
        input.value = minutes;
        const wrap = input.closest('.apt-duration-wrap');
        if (wrap) {
            wrap.querySelectorAll('.apt-duration-presets button').forEach(btn => {
                btn.classList.toggle('active', parseInt(btn.textContent, 10) === minutes);
            });
        }
        this.checkAppointmentAvailability();
    }

    async loadPatientsForAppointmentModal() {
        try {
            const res = await this.apiCall('/api/patients?per_page=200');
            const data = await res.json();
            const sel = document.getElementById('appointmentPatientId');
            if (!sel) return;
            sel.innerHTML = '<option value="">اختر المريض...</option>' +
                (data.data || []).map(p => `<option value="${p.id}">${this.escapeHtml(p.full_name)} - ${p.file_number}</option>`).join('');
        } catch (error) {
            console.error('Error loading patients:', error);
        }
    }

    async loadClinicsForAppointmentModal() {
        try {
            const res = await this.apiCall('/api/clinics/active');
            const data = await res.json();
            const sel = document.getElementById('appointmentClinicId');
            if (!sel) return;
            const clinics = data.data || [];
            if (clinics.length === 0) {
                sel.innerHTML = '<option value="">— لا توجد عيادات نشطة —</option>';
            } else {
                sel.innerHTML = '<option value="">— اختر العيادة —</option>' +
                    clinics.map(c => `<option value="${c.id}">${this.escapeHtml(c.name)}</option>`).join('');
            }
        } catch (error) {
            console.error('Error loading clinics:', error);
        }
    }

    async loadDoctorsForAppointmentModal(preselectId = null) {
        const clinicId = document.getElementById('appointmentClinicId')?.value;
        const sel = document.getElementById('appointmentDoctorId');
        if (!sel) return;
        if (!clinicId) {
            sel.innerHTML = '<option value="">اختر الطبيب...</option>';
            return;
        }
        try {
            const res = await this.apiCall(`/api/reception/doctors?clinic_id=${clinicId}`);
            const data = await res.json();
            sel.innerHTML = '<option value="">اختر الطبيب...</option>' +
                (data.doctors || []).map(d => `<option value="${d.id}">${this.escapeHtml(d.full_name)}</option>`).join('');
            if (preselectId) sel.value = preselectId;
        } catch (error) {
            console.error('Error loading doctors:', error);
        }
    }

    checkAppointmentAvailability() {
        clearTimeout(this.availabilityCheckTimer);
        const doctorId = document.getElementById('appointmentDoctorId')?.value;
        const dateVal = document.getElementById('appointmentDate')?.value;
        const duration = parseInt(document.getElementById('appointmentDuration')?.value || '30');
        const ignoreId = document.getElementById('appointmentId')?.value;

        if (!doctorId || !dateVal) {
            this.hideAvailabilityHint();
            return;
        }

        this.showAvailabilityHint('جاري التحقق من التوفر...', 'checking');
        this.availabilityCheckTimer = setTimeout(async () => {
            let url = `/api/appointments/check-availability?doctor_id=${doctorId}&appointment_date=${encodeURIComponent(dateVal)}&duration_minutes=${duration}`;
            if (ignoreId) url += `&ignore_id=${ignoreId}`;
            try {
                const res = await this.apiCall(url);
                const data = await res.json();
                if (data.available) {
                    this.showAvailabilityHint('✓ الموعد متاح', 'ok');
                } else {
                    const list = (data.conflicts || []).map(c =>
                        `${c.appointment_date} - ${this.escapeHtml(c.patient_name || '')}`
                    ).join(' • ');
                    this.showAvailabilityHint(`⚠ تعارض: الطبيب لديه موعد آخر في نفس الفترة (${list})`, 'conflict');
                }
            } catch (error) {
                this.hideAvailabilityHint();
            }
        }, 400);
    }

    showAvailabilityHint(text, type) {
        const el = document.getElementById('appointmentAvailabilityHint');
        if (!el) return;
        el.className = `availability-hint ${type}`;
        el.textContent = text;
        el.style.display = 'block';
        const btn = document.getElementById('appointmentSubmitBtn');
        if (btn) btn.disabled = (type === 'conflict');
    }

    hideAvailabilityHint() {
        const el = document.getElementById('appointmentAvailabilityHint');
        if (el) el.style.display = 'none';
        const btn = document.getElementById('appointmentSubmitBtn');
        if (btn) btn.disabled = false;
    }

    async saveAppointment(event) {
        event.preventDefault();
        const id = document.getElementById('appointmentId').value;
        const payload = {
            patient_id: parseInt(document.getElementById('appointmentPatientId').value),
            clinic_id: parseInt(document.getElementById('appointmentClinicId').value),
            doctor_id: parseInt(document.getElementById('appointmentDoctorId').value),
            appointment_date: document.getElementById('appointmentDate').value,
            duration_minutes: parseInt(document.getElementById('appointmentDuration').value || 30),
            status: document.getElementById('appointmentStatus').value,
            notes: document.getElementById('appointmentNotes').value || null,
        };

        const url = id ? `/api/appointments/${id}` : '/api/appointments';
        const method = id ? 'PUT' : 'POST';
        try {
            const res = await this.apiCall(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            if (res.ok) {
                this.showAlert('pageAlert', id ? 'تم تحديث الموعد بنجاح' : 'تم حجز الموعد بنجاح', 'success');
                this.closeAppointmentModal();
                this.onAppointmentsFilterChange();
            } else {
                const err = await res.json();
                const msg = err.message ||
                    (err.errors && Object.values(err.errors).flat().join(' / ')) ||
                    'حدث خطأ';
                this.showAlert('pageAlert', msg, 'error');
            }
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        }
    }

    async openAppointmentDetails(id) {
        const modal = document.getElementById('appointmentDetailsModal');
        const content = document.getElementById('appointmentDetailsContent');
        const actions = document.getElementById('appointmentDetailsActions');
        content.innerHTML = '<div class="loading">جاري التحميل...</div>';
        actions.innerHTML = '';
        modal.classList.add('open');

        try {
            const res = await this.apiCall(`/api/appointments/${id}`);
            const data = await res.json();
            const a = data.data || data;

            document.getElementById('appointmentDetailsTitle').textContent =
                `${a.patient?.full_name || ''} - ${a.doctor?.full_name || ''}`;
            const date = a.appointment_date ? new Date(a.appointment_date) : null;
            const dateStr = date ? date.toLocaleString('ar-SA') : '-';
            document.getElementById('appointmentDetailsSubtitle').textContent = dateStr;

            content.innerHTML = `
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">المريض</div>
                        <div class="value">${this.escapeHtml(a.patient?.full_name || '')}</div>
                        <small style="color:#6b7280;">${a.patient?.file_number || ''} • ${a.patient?.phone || ''}</small>
                    </div>
                    <div class="info-item">
                        <div class="label">الطبيب</div>
                        <div class="value">${this.escapeHtml(a.doctor?.full_name || '')}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">العيادة</div>
                        <div class="value">${this.escapeHtml(a.clinic?.name || '')}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">المدة</div>
                        <div class="value">${a.duration_minutes || 30} دقيقة</div>
                    </div>
                    <div class="info-item">
                        <div class="label">الحالة</div>
                        <div class="value"><span class="apt-status-pill apt-status-${a.status}">${a.status_label || a.status}</span></div>
                    </div>
                    <div class="info-item">
                        <div class="label">الزيارة المرتبطة</div>
                        <div class="value">${a.visit_id ? '#' + a.visit_id : 'لا توجد'}</div>
                    </div>
                </div>
                ${a.notes ? `<div class="card" style="margin-top: 16px; padding: 14px;">
                    <strong>ملاحظات:</strong>
                    <p style="margin-top: 6px; color: #4b5563;">${this.escapeHtml(a.notes)}</p>
                </div>` : ''}
                ${a.cancel_reason ? `<div class="card" style="margin-top: 12px; padding: 14px; background: #fee2e2;">
                    <strong>سبب الإلغاء:</strong>
                    <p style="margin-top: 6px; color: #991b1b;">${this.escapeHtml(a.cancel_reason)}</p>
                </div>` : ''}
            `;

            const buttons = [];
            if (a.can_be_cancelled && (this.user.role === 'admin' || this.user.role === 'reception')) {
                buttons.push(`<button class="btn btn-danger" onclick="spa.cancelAppointment(${a.id})">إلغاء الموعد</button>`);
            }
            if (a.status === 'scheduled' && (this.user.role === 'admin' || this.user.role === 'reception')) {
                buttons.push(`<button class="btn" onclick="spa.markAppointmentCompleted(${a.id})">تعليم كمكتمل</button>`);
            }
            if (a.can_be_converted && (this.user.role === 'admin' || this.user.role === 'reception')) {
                buttons.push(`<button class="btn btn-success" onclick="spa.convertAppointmentToVisit(${a.id})">🩺 تحويل إلى زيارة</button>`);
            }
            if (a.status !== 'converted_to_visit' && a.status !== 'cancelled'
                && (this.user.role === 'admin' || this.user.role === 'reception')) {
                buttons.push(`<button class="btn btn-secondary" onclick="spa.closeAppointmentDetails(); spa.openAppointmentModal(${a.id})">تعديل</button>`);
            }
            actions.innerHTML = buttons.join('');
        } catch (error) {
            content.innerHTML = '<div class="loading">خطأ في التحميل</div>';
        }
    }

    closeAppointmentDetails() {
        document.getElementById('appointmentDetailsModal')?.classList.remove('open');
    }

    async cancelAppointment(id) {
        const confirmed = await this.showConfirm(
            'إلغاء الموعد',
            'هل أنت متأكد من إلغاء هذا الموعد؟',
            '⚠️',
        );
        if (!confirmed) return;
        const reason = prompt('سبب الإلغاء (اختياري):') || null;
        try {
            const res = await this.apiCall(`/api/appointments/${id}/cancel`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ reason }),
            });
            if (res.ok) {
                this.showAlert('pageAlert', 'تم إلغاء الموعد', 'success');
                this.closeAppointmentDetails();
                this.onAppointmentsFilterChange();
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'خطأ في الإلغاء', 'error');
            }
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        }
    }

    async markAppointmentCompleted(id) {
        try {
            const res = await this.apiCall(`/api/appointments/${id}/mark-completed`, { method: 'POST' });
            if (res.ok) {
                this.showAlert('pageAlert', 'تم تعليم الموعد كمكتمل', 'success');
                this.closeAppointmentDetails();
                this.onAppointmentsFilterChange();
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'خطأ', 'error');
            }
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        }
    }

    async convertAppointmentToVisit(id) {
        const confirmed = await this.showConfirm(
            'تحويل الموعد إلى زيارة',
            'سيتم إنشاء زيارة مالية من هذا الموعد. هل تريد المتابعة؟',
            '🩺',
        );
        if (!confirmed) return;
        try {
            const res = await this.apiCall(`/api/appointments/${id}/convert-to-visit`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({}),
            });
            if (res.ok) {
                this.showAlert('pageAlert', 'تم تحويل الموعد إلى زيارة بنجاح', 'success');
                this.closeAppointmentDetails();
                this.closeAppointmentDayModal();
                this.onAppointmentsFilterChange();
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'خطأ في التحويل', 'error');
            }
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        }
    }

    startAppointmentsPolling() {
        this.stopAppointmentsPolling();
        this.appointmentsPollTimer = setInterval(() => {
            if (this.currentPage === 'appointments') {
                this.onAppointmentsFilterChange();
            } else {
                this.stopAppointmentsPolling();
            }
        }, 30000);
    }

    stopAppointmentsPolling() {
        if (this.appointmentsPollTimer) {
            clearInterval(this.appointmentsPollTimer);
            this.appointmentsPollTimer = null;
        }
    }

    formatDateStr(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    escapeHtml(s) {
        if (s === null || s === undefined) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
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

    // ===== Clinics Module =====
    async loadClinicsPage() {
        await this.loadClinicsFiltersData();
        await this.loadClinicsStats();
        await this.loadClinicsTable(1);
        this.startClinicsPolling();
    }

    async loadClinicsFiltersData() {
        try {
            const res = await this.apiCall('/api/clinics/statuses');
            const data = await res.json();
            const sel = document.getElementById('clinicsStatusFilter');
            if (sel) {
                const current = sel.value;
                sel.innerHTML = '<option value="">جميع الحالات</option>' +
                    (data.data || []).map(s => `<option value="${s.value}">${this.escapeHtml(s.label)}</option>`).join('');
                if (current) sel.value = current;
            }
        } catch (error) {
            console.error('Error loading clinic statuses:', error);
        }
    }

    async loadClinicsStats() {
        const container = document.getElementById('clinicsStatsGrid');
        if (!container) return;
        container.innerHTML = '<div class="loading">جاري التحميل...</div>';
        try {
            const res = await this.apiCall('/api/dashboard/clinics');
            const data = await res.json();
            container.innerHTML = `
                <div class="clinic-stat-card">
                    <div class="clinic-stat-icon primary">🏥</div>
                    <div class="clinic-stat-meta">
                        <div class="clinic-stat-value">${data.total_clinics || 0}</div>
                        <div class="clinic-stat-label">إجمالي العيادات</div>
                    </div>
                </div>
                <div class="clinic-stat-card">
                    <div class="clinic-stat-icon success">🟢</div>
                    <div class="clinic-stat-meta">
                        <div class="clinic-stat-value">${data.active_clinics || 0}</div>
                        <div class="clinic-stat-label">عيادات نشطة</div>
                    </div>
                </div>
                <div class="clinic-stat-card">
                    <div class="clinic-stat-icon warning">⏸</div>
                    <div class="clinic-stat-meta">
                        <div class="clinic-stat-value">${(data.inactive_clinics || 0) + (data.archived_clinics || 0)}</div>
                        <div class="clinic-stat-label">غير نشطة / مؤرشفة</div>
                    </div>
                </div>
                <div class="clinic-stat-card">
                    <div class="clinic-stat-icon purple">🩺</div>
                    <div class="clinic-stat-meta">
                        <div class="clinic-stat-value">${data.doctors_in_clinics || 0}</div>
                        <div class="clinic-stat-label">أطباء موزعون</div>
                    </div>
                </div>
                <div class="clinic-stat-card">
                    <div class="clinic-stat-icon danger">💰</div>
                    <div class="clinic-stat-meta">
                        <div class="clinic-stat-value">${(data.total_clinic_revenue || 0).toLocaleString('ar-SA')} <small style="font-size:12px; font-weight:500;">ر.س</small></div>
                        <div class="clinic-stat-label">إجمالي إيراد الشهر</div>
                    </div>
                </div>
            `;
        } catch (error) {
            console.error('Error loading clinic stats:', error);
            container.innerHTML = '<div class="loading">خطأ في التحميل</div>';
        }
    }

    async loadClinicsTable(page = 1) {
        this.clinicsCurrentPage = page;
        const search = document.getElementById('clinicsSearch')?.value || '';
        const status = document.getElementById('clinicsStatusFilter')?.value || '';
        const minDoctors = document.getElementById('clinicsMinDoctors')?.value || '';
        const activeOnly = document.getElementById('clinicsActiveOnly')?.checked || false;

        let url = `/api/clinics?page=${page}&per_page=20`;
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (status) url += `&status=${status}`;
        if (minDoctors) url += `&min_doctors=${minDoctors}`;
        if (activeOnly) url += `&active_only=1`;

        const tbody = document.getElementById('clinicsTable');
        if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="loading">جاري التحميل...</td></tr>';

        try {
            const res = await this.apiCall(url);
            const data = await res.json();
            this.renderClinicsTable(data);
        } catch (error) {
            console.error('Error loading clinics:', error);
            if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="loading">خطأ في التحميل</td></tr>';
        }
    }

    renderClinicsTable(data) {
        const clinics = data.data || [];
        const tbody = document.getElementById('clinicsTable');
        if (!tbody) return;
        if (clinics.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="empty-state"><div class="icon">🏥</div><h3>لا توجد عيادات</h3><p>لم يتم العثور على عيادات مطابقة للبحث</p></td></tr>';
            const pag = document.getElementById('clinicsPagination');
            if (pag) pag.innerHTML = '';
            return;
        }

        const isAdmin = this.user.role === 'admin';

        tbody.innerHTML = clinics.map((c, index) => {
            const serial = ((data.current_page || 1) - 1) * (data.per_page || 20) + index + 1;
            const isArchived = c.is_archived;
            const rowClass = isArchived ? 'clinic-row-archived' : '';

            return `
                <tr class="${rowClass}" data-clinic-id="${c.id}">
                    <td class="serial-cell">${serial}</td>
                    <td>
                        <div class="clinic-name-cell">
                            <div class="clinic-icon">🏥</div>
                            <div>
                                <strong>${this.escapeHtml(c.name)}</strong>
                                ${c.description ? `<small>${this.escapeHtml(c.description.length > 60 ? c.description.slice(0, 60) + '…' : c.description)}</small>` : ''}
                            </div>
                        </div>
                    </td>
                    <td>${c.location ? this.escapeHtml(c.location) : '<span style="color:#9ca3af;">—</span>'}</td>
                    <td>${c.phone ? `<a href="tel:${c.phone}" style="color:var(--primary); text-decoration:none;">${this.escapeHtml(c.phone)}</a>` : '<span style="color:#9ca3af;">—</span>'}</td>
                    <td><strong>${c.doctors_count || 0}</strong></td>
                    <td>${c.visits_count || 0}</td>
                    <td>${c.appointments_count || 0}</td>
                    <td><strong style="color: #0f9f6e;">${(c.monthly_revenue || 0).toLocaleString('ar-SA')}</strong> <small style="color:#9ca3af;">ر.س</small></td>
                    <td><span class="clinic-status-pill clinic-status-${c.status}">${this.escapeHtml(c.status_label || c.status)}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-btn" title="عرض التفاصيل" onclick="event.stopPropagation(); spa.openClinicDrawer(${c.id})">👁</button>
                            ${isAdmin ? `
                                <button class="icon-btn" title="تعديل" ${isArchived ? 'disabled' : ''} onclick="event.stopPropagation(); spa.openClinicModal(${c.id})">✎</button>
                                <button class="icon-btn" title="أرشفة" ${isArchived ? 'disabled' : ''} onclick="event.stopPropagation(); spa.archiveClinic(${c.id}, '${this.escapeHtml(c.name).replace(/'/g, "\\'")}')">📦</button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        this.renderClinicsPagination(data);
    }

    renderClinicsPagination(data) {
        const pag = document.getElementById('clinicsPagination');
        if (!pag) return;
        const { current_page, last_page } = data;
        let html = '';
        html += `<button ${current_page === 1 ? 'disabled' : ''} onclick="spa.loadClinicsTable(${current_page - 1})">السابق</button>`;
        for (let i = 1; i <= last_page; i++) {
            if (i === 1 || i === last_page || (i >= current_page - 2 && i <= current_page + 2)) {
                html += `<button class="${i === current_page ? 'active' : ''}" onclick="spa.loadClinicsTable(${i})">${i}</button>`;
            } else if (i === current_page - 3 || i === current_page + 3) {
                html += '<button disabled>...</button>';
            }
        }
        html += `<button ${current_page === last_page ? 'disabled' : ''} onclick="spa.loadClinicsTable(${current_page + 1})">التالي</button>`;
        pag.innerHTML = html;
    }

    toggleClinicsFilters() {
        document.getElementById('clinicsFiltersPanel')?.classList.toggle('open');
    }

    debounceClinicsSearch() {
        clearTimeout(this.clinicsSearchTimeout);
        this.clinicsSearchTimeout = setTimeout(() => this.loadClinicsTable(1), 300);
    }

    resetClinicsFilters() {
        const ids = ['clinicsSearch', 'clinicsStatusFilter', 'clinicsMinDoctors'];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        const activeOnly = document.getElementById('clinicsActiveOnly');
        if (activeOnly) activeOnly.checked = false;
        this.loadClinicsTable(1);
    }

    async openClinicModal(clinicId = null) {
        if (this.user.role !== 'admin') {
            this.showAlert('pageAlert', 'فقط المدير يمكنه إضافة/تعديل العيادات', 'error');
            return;
        }
        const modal = document.getElementById('clinicModal');
        const titleEl = document.getElementById('clinicModalTitle');
        const form = document.getElementById('clinicForm');
        form.reset();
        document.getElementById('clinicId').value = '';
        document.getElementById('clinicStatusGroup').style.display = 'none';

        if (clinicId) {
            titleEl.textContent = 'تعديل العيادة';
            try {
                const res = await this.apiCall(`/api/clinics/${clinicId}`);
                const data = await res.json();
                const c = data.data || data;
                document.getElementById('clinicId').value = c.id;
                document.getElementById('clinicName').value = c.name || '';
                document.getElementById('clinicPhone').value = c.phone || '';
                document.getElementById('clinicLocation').value = c.location || '';
                document.getElementById('clinicDescription').value = c.description || '';
                if (c.status !== 'archived') {
                    document.getElementById('clinicStatusGroup').style.display = '';
                    document.getElementById('clinicStatus').value = c.status;
                }
            } catch (error) {
                this.showAlert('pageAlert', 'خطأ في تحميل بيانات العيادة', 'error');
                return;
            }
        } else {
            titleEl.textContent = 'إضافة عيادة جديدة';
        }
        modal.classList.add('open');
    }

    closeClinicModal() {
        document.getElementById('clinicModal')?.classList.remove('open');
    }

    async saveClinic(event) {
        event.preventDefault();
        const id = document.getElementById('clinicId').value;
        const payload = {
            name: document.getElementById('clinicName').value.trim(),
            phone: document.getElementById('clinicPhone').value.trim() || null,
            location: document.getElementById('clinicLocation').value.trim() || null,
            description: document.getElementById('clinicDescription').value.trim() || null,
        };
        if (!id) {
            payload.status = 'active';
        } else {
            const statusEl = document.getElementById('clinicStatus');
            if (statusEl && statusEl.offsetParent !== null) {
                payload.status = statusEl.value;
            }
        }

        const url = id ? `/api/clinics/${id}` : '/api/clinics';
        const method = id ? 'PUT' : 'POST';
        try {
            const res = await this.apiCall(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            if (res.ok) {
                this.showAlert('pageAlert', id ? 'تم تحديث العيادة بنجاح' : 'تم إضافة العيادة بنجاح', 'success');
                this.closeClinicModal();
                this.loadClinicsPage();
            } else {
                const err = await res.json();
                const msg = err.message ||
                    (err.errors && Object.values(err.errors).flat().join(' / ')) ||
                    'حدث خطأ';
                this.showAlert('pageAlert', msg, 'error');
            }
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        }
    }

    async archiveClinic(id, name) {
        const confirmed = await this.showConfirm(
            'أرشفة العيادة',
            `هل تريد أرشفة "${name}"؟\nلن يتم حذف البيانات التاريخية، لكن العيادة لن تستقبل مواعيد أو زيارات جديدة.`,
            '📦',
        );
        if (!confirmed) return;
        try {
            const res = await this.apiCall(`/api/clinics/${id}/archive`, { method: 'POST' });
            if (res.ok) {
                this.showAlert('pageAlert', 'تم أرشفة العيادة', 'success');
                this.loadClinicsPage();
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'خطأ في الأرشفة (قد يكون هناك أطباء نشطون)', 'error');
            }
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        }
    }

    async activateClinic(id, name) {
        const confirmed = await this.showConfirm(
            'تنشيط العيادة',
            `هل تريد إعادة تنشيط "${name}"؟`,
            '🟢',
        );
        if (!confirmed) return;
        try {
            const res = await this.apiCall(`/api/clinics/${id}/activate`, { method: 'POST' });
            if (res.ok) {
                this.showAlert('pageAlert', 'تم تنشيط العيادة', 'success');
                this.loadClinicsPage();
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'خطأ', 'error');
            }
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        }
    }

    async openClinicDrawer(id) {
        const drawer = document.getElementById('clinicDrawer');
        const content = document.getElementById('clinicDrawerContent');
        document.getElementById('clinicDrawerTitle').textContent = 'تفاصيل العيادة';
        document.getElementById('clinicDrawerSubtitle').textContent = '—';
        content.innerHTML = '<div class="loading">جاري التحميل...</div>';
        drawer.classList.add('open');

        try {
            const [clinicRes, reportRes, allDoctorsRes] = await Promise.all([
                this.apiCall(`/api/clinics/${id}`),
                this.apiCall(`/api/clinics/${id}/detailed-report?limit=5`),
                this.user.role === 'admin' ? this.apiCall('/api/doctors/active') : Promise.resolve(null),
            ]);
            const clinic = (await clinicRes.json()).data || {};
            const report = await reportRes.json();
            const allDoctors = allDoctorsRes ? (await allDoctorsRes.json()).data || [] : [];

            document.getElementById('clinicDrawerTitle').textContent = clinic.name || 'تفاصيل العيادة';
            const statusLabel = clinic.status_label || clinic.status;
            document.getElementById('clinicDrawerSubtitle').innerHTML =
                `<span class="clinic-status-pill clinic-status-${clinic.status}" style="font-size:11px;">${this.escapeHtml(statusLabel)}</span>` +
                (clinic.location ? ` • ${this.escapeHtml(clinic.location)}` : '');

            const isAdmin = this.user.role === 'admin';

            content.innerHTML = `
                <div class="drawer-stats-grid">
                    <div class="drawer-stat">
                        <div class="label">الأطباء</div>
                        <div class="value">${report.statistics.doctors_count}</div>
                    </div>
                    <div class="drawer-stat green">
                        <div class="label">المرضى</div>
                        <div class="value">${report.statistics.patients_count}</div>
                    </div>
                    <div class="drawer-stat purple">
                        <div class="label">إجمالي الزيارات</div>
                        <div class="value">${report.statistics.visits_count}</div>
                    </div>
                    <div class="drawer-stat amber">
                        <div class="label">المواعيد</div>
                        <div class="value">${report.statistics.appointments_count}</div>
                    </div>
                </div>

                <section class="drawer-section">
                    <div class="drawer-section-title">📋 معلومات العيادة</div>
                    <div class="clinic-info-row">
                        <div class="info-label">الاسم</div>
                        <div class="info-value">${this.escapeHtml(clinic.name || '')}</div>
                    </div>
                    <div class="clinic-info-row">
                        <div class="info-label">الوصف</div>
                        <div class="info-value">${clinic.description ? this.escapeHtml(clinic.description) : '<span style="color:#9ca3af;">لا يوجد</span>'}</div>
                    </div>
                    <div class="clinic-info-row">
                        <div class="info-label">الموقع</div>
                        <div class="info-value">${clinic.location ? this.escapeHtml(clinic.location) : '<span style="color:#9ca3af;">—</span>'}</div>
                    </div>
                    <div class="clinic-info-row">
                        <div class="info-label">الهاتف</div>
                        <div class="info-value">${clinic.phone ? `<a href="tel:${clinic.phone}" style="color:var(--primary); text-decoration:none;">${this.escapeHtml(clinic.phone)}</a>` : '<span style="color:#9ca3af;">—</span>'}</div>
                    </div>
                    <div class="clinic-info-row">
                        <div class="info-label">الحالة</div>
                        <div class="info-value"><span class="clinic-status-pill clinic-status-${clinic.status}">${this.escapeHtml(clinic.status_label || '')}</span></div>
                    </div>
                </section>

                <section class="drawer-section">
                    <div class="drawer-section-title">🩺 الأطباء (${report.doctors.length})</div>
                    ${report.doctors.length === 0
                        ? '<p style="color:#9ca3af; text-align:center; padding:12px;">لا يوجد أطباء في هذه العيادة</p>'
                        : report.doctors.map(d => `
                            <div class="drawer-doctor-item">
                                <div class="doctor-info">
                                    <strong>${this.escapeHtml(d.full_name)}</strong>
                                    <small>${this.escapeHtml(d.email || '')}</small>
                                </div>
                                <div class="doctor-stat">
                                    <strong>${d.visits_count || 0}</strong>
                                    <span>زيارة</span>
                                </div>
                                ${isAdmin ? `<button class="icon-btn danger" title="إزالة من العيادة" onclick="spa.unassignClinicDoctor(${id}, ${d.id}, '${this.escapeHtml(d.full_name).replace(/'/g, "\\'")}')">✕</button>` : ''}
                            </div>
                        `).join('')
                    }
                    ${isAdmin ? `
                        <div class="assign-doctor-form">
                            <select id="clinicAssignDoctorSelect">
                                <option value="">— اختر طبيب لتعيينه —</option>
                                ${allDoctors.filter(d => !d.clinic_id).map(d => `<option value="${d.id}">${this.escapeHtml(d.full_name)}</option>`).join('')}
                            </select>
                            <button type="button" class="btn btn-success btn-sm" onclick="spa.assignClinicDoctor(${id})">تعيين</button>
                        </div>
                    ` : ''}
                </section>

                <section class="drawer-section">
                    <div class="drawer-section-title">💰 الإيرادات</div>
                    <div class="drawer-stats-grid">
                        <div class="drawer-stat green">
                            <div class="label">إيراد اليوم</div>
                            <div class="value">${(report.statistics.revenue_today || 0).toLocaleString('ar-SA')} <small style="font-size:11px; font-weight:500;">ر.س</small></div>
                        </div>
                        <div class="drawer-stat purple">
                            <div class="label">إيراد الشهر</div>
                            <div class="value">${(report.statistics.monthly_revenue || 0).toLocaleString('ar-SA')} <small style="font-size:11px; font-weight:500;">ر.س</small></div>
                        </div>
                        <div class="drawer-stat amber">
                            <div class="label">إيراد السنة</div>
                            <div class="value">${(report.statistics.yearly_revenue || 0).toLocaleString('ar-SA')} <small style="font-size:11px; font-weight:500;">ر.س</small></div>
                        </div>
                        <div class="drawer-stat">
                            <div class="label">حصة المجمع (الشهر)</div>
                            <div class="value">${(report.statistics.center_monthly_share || 0).toLocaleString('ar-SA')} <small style="font-size:11px; font-weight:500;">ر.س</small></div>
                        </div>
                    </div>
                    <div class="clinic-info-row" style="margin-top:12px;">
                        <div class="info-label">زيارات الشهر</div>
                        <div class="info-value">
                            <strong>${report.statistics.visits_this_month || 0}</strong>
                            <small style="color:#9ca3af;"> (${report.statistics.examinations_this_month || 0} معاينة، ${report.statistics.reviews_this_month || 0} مراجعة)</small>
                        </div>
                    </div>
                </section>

                ${report.recent_visits.length > 0 ? `
                    <section class="drawer-section">
                        <div class="drawer-section-title">🕐 آخر الزيارات</div>
                        ${report.recent_visits.map(v => `
                            <div class="drawer-doctor-item">
                                <div class="doctor-info">
                                    <strong>${this.escapeHtml(v.patient_name)}</strong>
                                    <small>${this.escapeHtml(v.doctor_name)} • ${v.visit_date}</small>
                                </div>
                                <div class="doctor-stat">
                                    <strong>${(v.amount_received || 0).toLocaleString('ar-SA')} ر.س</strong>
                                    <span>${v.visit_type === 'examination' ? 'معاينة' : 'مراجعة'}</span>
                                </div>
                            </div>
                        `).join('')}
                    </section>
                ` : ''}

                ${report.recent_appointments.length > 0 ? `
                    <section class="drawer-section">
                        <div class="drawer-section-title">📅 آخر المواعيد</div>
                        ${report.recent_appointments.map(a => `
                            <div class="drawer-doctor-item">
                                <div class="doctor-info">
                                    <strong>${this.escapeHtml(a.patient_name)}</strong>
                                    <small>${this.escapeHtml(a.doctor_name)}</small>
                                </div>
                                <div class="doctor-stat">
                                    <span class="clinic-status-pill clinic-status-${a.status}" style="font-size:10px;">${this.escapeHtml(a.status)}</span>
                                    <small>${new Date(a.appointment_date).toLocaleDateString('ar-SA')}</small>
                                </div>
                            </div>
                        `).join('')}
                    </section>
                ` : ''}

                ${report.recent_patients.length > 0 ? `
                    <section class="drawer-section">
                        <div class="drawer-section-title">👥 آخر المرضى</div>
                        ${report.recent_patients.map(p => `
                            <div class="drawer-doctor-item">
                                <div class="doctor-info">
                                    <strong>${this.escapeHtml(p.full_name)}</strong>
                                    <small>${this.escapeHtml(p.file_number)} • ${this.escapeHtml(p.phone || '')}</small>
                                </div>
                            </div>
                        `).join('')}
                    </section>
                ` : ''}

                <div class="drawer-actions">
                    <button class="btn btn-secondary" onclick="spa.openClinicModal(${id}); spa.closeClinicDrawer();">تعديل</button>
                    <a class="btn" href="/appointments?clinic_id=${id}" onclick="event.preventDefault(); spa.closeClinicDrawer(); spa.navigate('/appointments');">عرض المواعيد</a>
                    ${isAdmin && !clinic.is_archived ? `<button class="btn btn-danger" onclick="spa.archiveClinic(${id}, '${this.escapeHtml(clinic.name).replace(/'/g, "\\'")}')">أرشفة</button>` : ''}
                    ${isAdmin && clinic.is_archived ? `<button class="btn btn-success" onclick="spa.activateClinic(${id}, '${this.escapeHtml(clinic.name).replace(/'/g, "\\'")}')">إعادة تنشيط</button>` : ''}
                </div>
            `;
        } catch (error) {
            content.innerHTML = '<div class="loading">خطأ في التحميل</div>';
        }
    }

    closeClinicDrawer() {
        document.getElementById('clinicDrawer')?.classList.remove('open');
    }

    async assignClinicDoctor(clinicId) {
        const select = document.getElementById('clinicAssignDoctorSelect');
        const doctorId = select?.value;
        if (!doctorId) return;
        try {
            const res = await this.apiCall(`/api/clinics/${clinicId}/assign-doctor`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ doctor_id: parseInt(doctorId) }),
            });
            if (res.ok) {
                this.showAlert('pageAlert', 'تم تعيين الطبيب بنجاح', 'success');
                this.openClinicDrawer(clinicId);
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'خطأ في التعيين', 'error');
            }
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        }
    }

    async unassignClinicDoctor(clinicId, doctorId, doctorName) {
        const confirmed = await this.showConfirm(
            'إزالة الطبيب',
            `هل تريد إزالة "${doctorName}" من هذه العيادة؟`,
            '⚠️',
        );
        if (!confirmed) return;
        try {
            const res = await this.apiCall(`/api/clinics/${clinicId}/doctors/${doctorId}`, { method: 'DELETE' });
            if (res.ok) {
                this.showAlert('pageAlert', 'تم إزالة الطبيب', 'success');
                this.openClinicDrawer(clinicId);
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'خطأ', 'error');
            }
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        }
    }

    startClinicsPolling() {
        this.stopClinicsPolling();
        this.clinicsPollTimer = setInterval(() => {
            if (this.currentPage === 'clinics') {
                this.loadClinicsStats();
                this.loadClinicsTable(this.clinicsCurrentPage || 1);
            } else {
                this.stopClinicsPolling();
            }
        }, 30000);
    }

    stopClinicsPolling() {
        if (this.clinicsPollTimer) {
            clearInterval(this.clinicsPollTimer);
            this.clinicsPollTimer = null;
        }
    }

    // ===== Doctors Module =====
    async loadDoctorsPage() {
        const addBtn = document.getElementById('addDoctorBtn');
        if (addBtn) addBtn.style.display = '';
        if (this.user.role === 'doctor') {
            try {
                const res = await this.apiCall('/api/user');
                const me = await res.json();
                if (me && me.id) {
                    this.openDoctorDrawer(me.id);
                    return;
                }
            } catch (e) { console.error('Cannot load own profile', e); }
        }
        await this.loadDoctorsFiltersData();
        await this.loadDoctorsStats();
        await this.loadDoctorsTable(1);
        this.startDoctorsPolling();
    }

    async loadDoctorsFiltersData() {
        try {
            const [clinicsRes, specsRes] = await Promise.all([
                this.apiCall('/api/clinics/active'),
                this.apiCall('/api/doctors/specializations'),
            ]);
            const clinicsData = await clinicsRes.json();
            const specsData = await specsRes.json();
            const sel = document.getElementById('doctorsClinicFilter');
            if (sel) {
                const current = sel.value;
                sel.innerHTML = '<option value="">جميع العيادات</option>' +
                    (clinicsData.data || []).map(c => `<option value="${c.id}">${this.escapeHtml(c.name)}</option>`).join('');
                if (current) sel.value = current;
            }
            const specSel = document.getElementById('doctorsSpecializationFilter');
            if (specSel) {
                const current = specSel.value;
                specSel.innerHTML = '<option value="">جميع التخصصات</option>' +
                    (specsData.data || []).map(s => `<option value="${this.escapeHtml(s)}">${this.escapeHtml(s)}</option>`).join('');
                if (current) specSel.value = current;
            }
            const datalist = document.getElementById('doctorSpecializationsList');
            if (datalist) {
                datalist.innerHTML = (specsData.data || []).map(s => `<option value="${this.escapeHtml(s)}">`).join('');
            }
        } catch (error) {
            console.error('Error loading doctor filters data:', error);
        }
    }

    async loadDoctorsStats() {
        const container = document.getElementById('doctorsStatsGrid');
        if (!container) return;
        if (this.user.role === 'doctor') {
            container.innerHTML = '';
            return;
        }
        container.innerHTML = '<div class="loading">جاري التحميل...</div>';
        try {
            const res = await this.apiCall('/api/dashboard/doctors');
            const data = await res.json();
            const revenue = (data.doctors_revenue || 0).toLocaleString('en-US');
            container.innerHTML = `
                <div class="doctor-stat-card">
                    <div class="doctor-stat-icon">👨‍⚕️</div>
                    <div class="doctor-stat-info">
                        <div class="doctor-stat-value">${data.total_doctors || 0}</div>
                        <div class="doctor-stat-label">إجمالي الأطباء</div>
                    </div>
                </div>
                <div class="doctor-stat-card">
                    <div class="doctor-stat-icon green">✅</div>
                    <div class="doctor-stat-info">
                        <div class="doctor-stat-value">${data.active_doctors || 0}</div>
                        <div class="doctor-stat-label">أطباء نشطون</div>
                    </div>
                </div>
                <div class="doctor-stat-card">
                    <div class="doctor-stat-icon blue">👥</div>
                    <div class="doctor-stat-info">
                        <div class="doctor-stat-value">${data.total_patients || 0}</div>
                        <div class="doctor-stat-label">إجمالي المرضى</div>
                    </div>
                </div>
                <div class="doctor-stat-card">
                    <div class="doctor-stat-icon orange">📅</div>
                    <div class="doctor-stat-info">
                        <div class="doctor-stat-value">${data.visits_this_month || 0}</div>
                        <div class="doctor-stat-label">زيارات هذا الشهر</div>
                    </div>
                </div>
                <div class="doctor-stat-card">
                    <div class="doctor-stat-icon green">💰</div>
                    <div class="doctor-stat-info">
                        <div class="doctor-stat-value">${revenue} ر.س</div>
                        <div class="doctor-stat-label">إيرادات الأطباء (الشهر)</div>
                    </div>
                </div>
                <div class="doctor-stat-card">
                    <div class="doctor-stat-icon red">📌</div>
                    <div class="doctor-stat-info">
                        <div class="doctor-stat-value">${data.appointments_today || 0}</div>
                        <div class="doctor-stat-label">مواعيد اليوم</div>
                    </div>
                </div>
            `;
        } catch (error) {
            container.innerHTML = '<div class="loading">تعذر تحميل الإحصائيات</div>';
            console.error('Error loading doctors stats:', error);
        }
    }

    async loadDoctorsTable(page = 1) {
        this.doctorsCurrentPage = page;
        const tbody = document.getElementById('doctorsTable');
        if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="loading">جاري التحميل...</td></tr>';

        const params = new URLSearchParams();
        params.set('page', page);
        params.set('per_page', 20);
        const search = document.getElementById('doctorsSearch')?.value;
        if (search) params.set('search', search);
        const clinicId = document.getElementById('doctorsClinicFilter')?.value;
        if (clinicId) params.set('clinic_id', clinicId);
        const spec = document.getElementById('doctorsSpecializationFilter')?.value;
        if (spec) params.set('specialization', spec);
        const active = document.getElementById('doctorsActiveFilter')?.value;
        if (active === 'archived') params.set('archived_only', 1);
        else if (active) params.set('active', active);

        try {
            const res = await this.apiCall(`/api/doctors?${params.toString()}`);
            const data = await res.json();
            this.renderDoctorsTable(data.data || []);
            this.renderDoctorsPagination(data);
        } catch (error) {
            if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="loading">تعذر التحميل</td></tr>';
            console.error('Error loading doctors table:', error);
        }
    }

    renderDoctorsTable(doctors) {
        const tbody = document.getElementById('doctorsTable');
        if (!tbody) return;
        if (!doctors.length) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 40px; color: #6b7280;">لا يوجد أطباء</td></tr>';
            return;
        }
        const start = ((this.doctorsCurrentPage - 1) * 20) + 1;
        tbody.innerHTML = doctors.map((d, idx) => {
            const initials = (d.full_name || '?').split(' ').map(s => s[0]).slice(0, 2).join('');
            const status = d.is_archived ? 'archived' : (d.is_active ? 'active' : 'inactive');
            const statusLabel = d.is_archived ? 'مؤرشف' : (d.is_active ? 'نشط' : 'غير نشط');
            const rowClass = d.is_archived ? 'doctor-row-archived' : '';
            return `
                <tr class="${rowClass}">
                    <td>${start + idx}</td>
                    <td>
                        <div class="doctor-name-cell">
                            <div class="doctor-avatar">${this.escapeHtml(initials)}</div>
                            <div class="doctor-meta">
                                <div class="doctor-full-name">${this.escapeHtml(d.full_name)}</div>
                                <div class="doctor-email">${this.escapeHtml(d.email || '')}</div>
                            </div>
                        </div>
                    </td>
                    <td>${d.clinic ? this.escapeHtml(d.clinic.name) : '—'}</td>
                    <td>${d.specialization ? this.escapeHtml(d.specialization) : '—'}</td>
                    <td>${(d.examination_fee || 0).toLocaleString('en-US')} ر.س</td>
                    <td><span class="badge badge-info">${d.patients_count || 0}</span></td>
                    <td><span class="badge badge-info">${d.visits_count || 0}</span></td>
                    <td>${(d.monthly_revenue || 0).toLocaleString('en-US')} ر.س</td>
                    <td><span class="doctor-status-pill ${status}"><span class="dot"></span>${statusLabel}</span></td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn-icon" title="عرض" onclick="spa.openDoctorDrawer(${d.id})">👁️</button>
                            ${this.user.role === 'admin' ? `
                                <button class="btn-icon" title="تعديل" onclick="spa.openDoctorModal(${d.id})">✏️</button>
                                ${d.is_active
                                    ? `<button class="btn-icon" title="إلغاء التفعيل" onclick="spa.deactivateDoctor(${d.id})">⏸️</button>`
                                    : `<button class="btn-icon" title="تفعيل" onclick="spa.activateDoctor(${d.id})">▶️</button>`}
                                <button class="btn-icon" title="أرشفة" onclick="spa.archiveDoctor(${d.id})">📦</button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    renderDoctorsPagination(data) {
        const c = document.getElementById('doctorsPagination');
        if (!c) return;
        if (!data.last_page || data.last_page <= 1) { c.innerHTML = ''; return; }
        const current = data.current_page;
        const last = data.last_page;
        let html = `<button ${current === 1 ? 'disabled' : ''} onclick="spa.loadDoctorsTable(${current - 1})">السابق</button>`;
        const startPage = Math.max(1, current - 2);
        const endPage = Math.min(last, current + 2);
        if (startPage > 1) html += `<button onclick="spa.loadDoctorsTable(1)">1</button>`;
        if (startPage > 2) html += `<span>…</span>`;
        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="${i === current ? 'active' : ''}" onclick="spa.loadDoctorsTable(${i})">${i}</button>`;
        }
        if (endPage < last - 1) html += `<span>…</span>`;
        if (endPage < last) html += `<button onclick="spa.loadDoctorsTable(${last})">${last}</button>`;
        html += `<button ${current === last ? 'disabled' : ''} onclick="spa.loadDoctorsTable(${current + 1})">التالي</button>`;
        c.innerHTML = html;
    }

    toggleDoctorsFilters() {
        const p = document.getElementById('doctorsFiltersPanel');
        if (p) p.classList.toggle('open');
    }

    debounceDoctorsSearch() {
        clearTimeout(this.doctorsSearchTimeout);
        this.doctorsSearchTimeout = setTimeout(() => this.loadDoctorsTable(1), 300);
    }

    resetDoctorsFilters() {
        const s = document.getElementById('doctorsSearch');
        if (s) s.value = '';
        const cf = document.getElementById('doctorsClinicFilter');
        if (cf) cf.value = '';
        const sf = document.getElementById('doctorsSpecializationFilter');
        if (sf) sf.value = '';
        const af = document.getElementById('doctorsActiveFilter');
        if (af) af.value = '';
        this.loadDoctorsTable(1);
    }

    async openDoctorModal(doctorId = null) {
        if (this.user.role !== 'admin') {
            this.showAlert('pageAlert', 'غير مصرح لك بإضافة أطباء', 'error');
            return;
        }
        const modal = document.getElementById('doctorModal');
        const title = document.getElementById('doctorModalTitle');
        const form = document.getElementById('doctorForm');
        if (form) form.reset();
        document.getElementById('doctorId').value = '';
        document.getElementById('doctorStatusSection').style.display = 'none';
        document.getElementById('doctorPasswordGroup').style.display = '';
        document.getElementById('doctorPasswordHint').textContent = 'اتركها فارغة للإبقاء على كلمة المرور الحالية';
        const pwInput = document.getElementById('doctorPassword');
        if (pwInput) pwInput.required = true;

        await this.loadDoctorsFiltersData();

        if (doctorId) {
            title.textContent = 'تعديل بيانات الطبيب';
            try {
                const res = await this.apiCall(`/api/doctors/${doctorId}`);
                const data = await res.json();
                const d = data.data || data;
                document.getElementById('doctorId').value = d.id;
                document.getElementById('doctorFullName').value = d.full_name || '';
                document.getElementById('doctorEmail').value = d.email || '';
                document.getElementById('doctorPhone').value = d.phone || '';
                document.getElementById('doctorClinicId').value = d.clinic_id || '';
                document.getElementById('doctorSpecialization').value = d.specialization || '';
                document.getElementById('doctorExaminationFee').value = d.examination_fee || 0;
                document.getElementById('doctorPercentageType').value = d.percentage_type || 'fixed';
                document.getElementById('doctorPercentageValue').value = d.percentage_value || 0;
                document.getElementById('doctorNotes').value = d.notes || '';
                document.getElementById('doctorIsActive').checked = !!d.is_active;
                document.getElementById('doctorStatusSection').style.display = '';
                if (pwInput) pwInput.required = false;
                document.getElementById('doctorPasswordHint').textContent = 'اتركها فارغة للإبقاء على كلمة المرور الحالية';
            } catch (e) {
                this.showAlert('pageAlert', 'تعذر تحميل بيانات الطبيب', 'error');
                return;
            }
        } else {
            title.textContent = 'إضافة طبيب جديد';
        }
        if (modal) modal.classList.add('active');
    }

    closeDoctorModal() {
        const modal = document.getElementById('doctorModal');
        if (modal) modal.classList.remove('active');
    }

    async saveDoctor(event) {
        event.preventDefault();
        if (this.user.role !== 'admin') {
            this.showAlert('pageAlert', 'غير مصرح', 'error');
            return;
        }
        const id = document.getElementById('doctorId').value;
        const payload = {
            full_name: document.getElementById('doctorFullName').value.trim(),
            email: document.getElementById('doctorEmail').value.trim(),
            phone: document.getElementById('doctorPhone').value.trim() || null,
            clinic_id: parseInt(document.getElementById('doctorClinicId').value) || null,
            specialization: document.getElementById('doctorSpecialization').value.trim() || null,
            examination_fee: parseFloat(document.getElementById('doctorExaminationFee').value) || 0,
            percentage_type: document.getElementById('doctorPercentageType').value,
            percentage_value: parseFloat(document.getElementById('doctorPercentageValue').value) || 0,
            notes: document.getElementById('doctorNotes').value.trim() || null,
        };
        const password = document.getElementById('doctorPassword').value;
        if (password) payload.password = password;
        if (id) payload.is_active = document.getElementById('doctorIsActive').checked;

        if (!payload.clinic_id) {
            this.showAlert('pageAlert', 'العيادة مطلوبة', 'error');
            return;
        }

        const btn = document.getElementById('doctorSubmitBtn');
        btn.disabled = true; btn.textContent = 'جاري الحفظ...';
        try {
            const res = await this.apiCall(id ? `/api/doctors/${id}` : '/api/doctors', {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            if (res.ok) {
                this.showAlert('pageAlert', id ? 'تم تحديث الطبيب بنجاح' : 'تم إضافة الطبيب بنجاح', 'success');
                this.closeDoctorModal();
                this.loadDoctorsTable(this.doctorsCurrentPage || 1);
                this.loadDoctorsStats();
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'حدث خطأ', 'error');
            }
        } catch (error) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        } finally {
            btn.disabled = false; btn.innerHTML = '<span aria-hidden="true">✓</span><span>حفظ الطبيب</span>';
        }
    }

    async activateDoctor(doctorId) {
        if (!confirm('هل تريد تفعيل هذا الطبيب؟')) return;
        try {
            const res = await this.apiCall(`/api/doctors/${doctorId}/activate`, { method: 'POST' });
            if (res.ok) {
                this.showAlert('pageAlert', 'تم تفعيل الطبيب', 'success');
                this.loadDoctorsTable(this.doctorsCurrentPage || 1);
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'خطأ', 'error');
            }
        } catch (e) { this.showAlert('pageAlert', 'خطأ في الاتصال', 'error'); }
    }

    async deactivateDoctor(doctorId) {
        if (!confirm('هل تريد إلغاء تفعيل هذا الطبيب؟')) return;
        try {
            const res = await this.apiCall(`/api/doctors/${doctorId}/deactivate`, { method: 'POST' });
            if (res.ok) {
                this.showAlert('pageAlert', 'تم إلغاء التفعيل', 'success');
                this.loadDoctorsTable(this.doctorsCurrentPage || 1);
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'خطأ', 'error');
            }
        } catch (e) { this.showAlert('pageAlert', 'خطأ في الاتصال', 'error'); }
    }

    async archiveDoctor(doctorId) {
        if (!confirm('هل تريد أرشفة هذا الطبيب؟\nلن يستطيع استقبال مواعيد أو زيارات جديدة، مع الإبقاء على بياناته التاريخية.')) return;
        try {
            const res = await this.apiCall(`/api/doctors/${doctorId}/archive`, { method: 'POST' });
            if (res.ok) {
                this.showAlert('pageAlert', 'تم أرشفة الطبيب', 'success');
                this.loadDoctorsTable(this.doctorsCurrentPage || 1);
                this.loadDoctorsStats();
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'خطأ', 'error');
            }
        } catch (e) { this.showAlert('pageAlert', 'خطأ في الاتصال', 'error'); }
    }

    async openDoctorDrawer(doctorId) {
        this.currentDoctorId = doctorId;
        this.currentDoctorTab = 'overview';
        const drawer = document.getElementById('doctorDrawer');
        const content = document.getElementById('doctorDrawerContent');
        const title = document.getElementById('doctorDrawerTitle');
        const subtitle = document.getElementById('doctorDrawerSubtitle');
        if (drawer) drawer.classList.add('active');
        if (content) content.innerHTML = '<div class="loading">جاري التحميل...</div>';
        if (title) title.textContent = 'تفاصيل الطبيب';
        if (subtitle) subtitle.textContent = '—';

        try {
            const [infoRes, statsRes, financeRes, scheduleRes, patientsRes, deductionsRes] = await Promise.all([
                this.apiCall(`/api/doctors/${doctorId}`),
                this.apiCall(`/api/doctors/${doctorId}/statistics`),
                this.apiCall(`/api/doctors/${doctorId}/finance`),
                this.apiCall(`/api/doctors/${doctorId}/schedule?range=week`),
                this.apiCall(`/api/doctors/${doctorId}/patients?limit=20`),
                this.user.role === 'admin' ? this.apiCall(`/api/doctors/${doctorId}/deductions`) : Promise.resolve(null),
            ]);
            const info = (await infoRes.json()).data || {};
            const stats = (await statsRes.json()).data || {};
            const finance = (await financeRes.json()).data || {};
            const schedule = (await scheduleRes.json()).data || {};
            const patients = (await patientsRes.json()).data || [];
            const deductions = deductionsRes ? (await deductionsRes.json()).data || [] : [];

            if (title) title.textContent = `د. ${info.full_name || ''}`;
            if (subtitle) subtitle.textContent = `${info.specialization || 'طبيب'} · ${info.clinic?.name || 'بدون عيادة'}`;

            if (content) content.innerHTML = this.renderDoctorDrawerContent(info, stats, finance, schedule, patients, deductions);
        } catch (error) {
            if (content) content.innerHTML = '<div class="loading">تعذر التحميل</div>';
            console.error('Error loading doctor drawer:', error);
        }
    }

    renderDoctorDrawerContent(info, stats, finance, schedule, patients, deductions) {
        const fmt = (n) => (parseFloat(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const isAdmin = this.user.role === 'admin';
        return `
            <div class="doctor-tab-bar">
                <button class="doctor-tab active" data-tab="overview" onclick="spa.switchDoctorTab('overview')">نظرة عامة</button>
                <button class="doctor-tab" data-tab="finance" onclick="spa.switchDoctorTab('finance')">المالية</button>
                <button class="doctor-tab" data-tab="schedule" onclick="spa.switchDoctorTab('schedule')">الجدول</button>
                <button class="doctor-tab" data-tab="patients" onclick="spa.switchDoctorTab('patients')">المرضى</button>
                ${isAdmin ? `<button class="doctor-tab" data-tab="deductions" onclick="spa.switchDoctorTab('deductions')">الخصومات</button>` : ''}
            </div>

            <div class="doctor-tab-content active" data-tab="overview">
                <div class="card">
                    <h3>📋 المعلومات الأساسية</h3>
                    <div class="doctor-info-row"><span class="info-label">الاسم</span><span class="info-value">د. ${this.escapeHtml(info.full_name || '')}</span></div>
                    <div class="doctor-info-row"><span class="info-label">البريد</span><span class="info-value">${this.escapeHtml(info.email || '—')}</span></div>
                    <div class="doctor-info-row"><span class="info-label">الجوال</span><span class="info-value">${this.escapeHtml(info.phone || '—')}</span></div>
                    <div class="doctor-info-row"><span class="info-label">التخصص</span><span class="info-value">${this.escapeHtml(info.specialization || '—')}</span></div>
                    <div class="doctor-info-row"><span class="info-label">العيادة</span><span class="info-value">${info.clinic ? this.escapeHtml(info.clinic.name) : '—'}</span></div>
                    <div class="doctor-info-row"><span class="info-label">سعر الكشف</span><span class="info-value">${fmt(info.examination_fee)} ر.س</span></div>
                    <div class="doctor-info-row"><span class="info-label">الحالة</span><span class="info-value">${info.is_archived ? 'مؤرشف' : (info.is_active ? '✅ نشط' : '⏸️ غير نشط')}</span></div>
                </div>

                <div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-top: 12px;">
                    <div class="doctor-stat-card">
                        <div class="doctor-stat-icon green">💰</div>
                        <div class="doctor-stat-info">
                            <div class="doctor-stat-value">${fmt(info.today_revenue)}</div>
                            <div class="doctor-stat-label">إيراد اليوم (ر.س)</div>
                        </div>
                    </div>
                    <div class="doctor-stat-card">
                        <div class="doctor-stat-icon blue">📅</div>
                        <div class="doctor-stat-info">
                            <div class="doctor-stat-value">${fmt(info.monthly_revenue)}</div>
                            <div class="doctor-stat-label">إيراد الشهر (ر.س)</div>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-top: 12px;">
                    <h3>👥 المرضى</h3>
                    <div class="doctor-info-row"><span class="info-label">إجمالي المرضى</span><span class="info-value">${stats.patients?.total || 0}</span></div>
                    <div class="doctor-info-row"><span class="info-label">جدد هذا الشهر</span><span class="info-value">${stats.patients?.new_this_month || 0}</span></div>
                    <div class="doctor-info-row"><span class="info-label">مرضى عائدون</span><span class="info-value">${stats.patients?.returning || 0}</span></div>
                </div>

                <div class="card" style="margin-top: 12px;">
                    <h3>🏥 الزيارات</h3>
                    <div class="doctor-info-row"><span class="info-label">إجمالي</span><span class="info-value">${stats.visits?.total || 0}</span></div>
                    <div class="doctor-info-row"><span class="info-label">معاينات</span><span class="info-value">${stats.visits?.examinations || 0}</span></div>
                    <div class="doctor-info-row"><span class="info-label">مراجعات</span><span class="info-value">${stats.visits?.reviews || 0}</span></div>
                    <div class="doctor-info-row"><span class="info-label">مراجعات مجانية</span><span class="info-value">${stats.visits?.free_reviews || 0}</span></div>
                </div>

                <div class="card" style="margin-top: 12px;">
                    <h3>🦷 الإجراءات</h3>
                    <div class="doctor-info-row"><span class="info-label">عدد الإجراءات</span><span class="info-value">${stats.procedures?.count || 0}</span></div>
                    <div class="doctor-info-row"><span class="info-label">إيرادات الإجراءات</span><span class="info-value">${fmt(stats.procedures?.revenue)} ر.س</span></div>
                </div>

                <div class="card" style="margin-top: 12px;">
                    <h3>📅 المواعيد</h3>
                    <div class="doctor-info-row"><span class="info-label">القادمة</span><span class="info-value">${stats.appointments?.upcoming || 0}</span></div>
                    <div class="doctor-info-row"><span class="info-label">اليوم</span><span class="info-value">${stats.appointments?.today || 0}</span></div>
                    <div class="doctor-info-row"><span class="info-label">فائتة</span><span class="info-value">${stats.appointments?.missed || 0}</span></div>
                </div>
            </div>

            <div class="doctor-tab-content" data-tab="finance">
                <div class="card">
                    <h3>💰 الملخص المالي (${finance.period?.from} → ${finance.period?.to})</h3>
                    <div class="doctor-finance-card">
                        <div class="doctor-finance-row">
                            <span class="label">إيرادات المعاينات</span>
                            <span class="value positive">${fmt(finance.examination_revenue)} ر.س</span>
                        </div>
                        <div class="doctor-finance-row">
                            <span class="label">إيرادات المراجعات</span>
                            <span class="value positive">${fmt(finance.review_revenue)} ر.س</span>
                        </div>
                        <div class="doctor-finance-row">
                            <span class="label">إيرادات الإجراءات</span>
                            <span class="value positive">${fmt(finance.procedure_revenue)} ر.س</span>
                        </div>
                        <div class="doctor-finance-row">
                            <span class="label">إجمالي الإيرادات</span>
                            <span class="value">${fmt(finance.gross_revenue)} ر.س</span>
                        </div>
                        <div class="doctor-finance-row">
                            <span class="label">إجمالي الخصومات والسلف</span>
                            <span class="value negative">-${fmt(finance.deductions_total)} ر.س</span>
                        </div>
                        <div class="doctor-finance-row">
                            <span class="label">إجمالي المكافآت</span>
                            <span class="value positive">+${fmt(finance.bonuses_total)} ر.س</span>
                        </div>
                        <div class="doctor-finance-row total">
                            <span class="label">صافي الأرباح</span>
                            <span class="value">${fmt(finance.net_earnings)} ر.س</span>
                        </div>
                    </div>
                    ${isAdmin ? `<button class="btn btn-success" style="width:100%; margin-top:8px;" onclick="spa.openDeductionModal(${info.id})">➕ إضافة خصم / سلفة / مكافأة</button>` : ''}
                </div>
            </div>

            <div class="doctor-tab-content" data-tab="schedule">
                <div class="card">
                    <h3>📅 جدول الأسبوع (${schedule.period?.from} → ${schedule.period?.to})</h3>
                    <div class="stats-grid" style="grid-template-columns: 1fr 1fr 1fr; margin-top:8px;">
                        <div class="doctor-stat-card">
                            <div class="doctor-stat-info">
                                <div class="doctor-stat-value">${schedule.total_slots || 0}</div>
                                <div class="doctor-stat-label">إجمالي المواعيد</div>
                            </div>
                        </div>
                        <div class="doctor-stat-card">
                            <div class="doctor-stat-info">
                                <div class="doctor-stat-value">${schedule.upcoming_appointments || 0}</div>
                                <div class="doctor-stat-label">قادمة</div>
                            </div>
                        </div>
                        <div class="doctor-stat-card">
                            <div class="doctor-stat-info">
                                <div class="doctor-stat-value">${schedule.missed_appointments || 0}</div>
                                <div class="doctor-stat-label">فائتة</div>
                            </div>
                        </div>
                    </div>
                    ${schedule.appointments && schedule.appointments.length > 0 ? `
                        <div style="margin-top: 12px;">
                            ${schedule.appointments.map(a => `
                                <div class="deduction-item" style="border-right-color: #667eea;">
                                    <div>
                                        <div style="font-weight: 600;">${this.escapeHtml(a.patient_name || '')}</div>
                                        <div style="font-size: 12px; color: #6b7280;">📁 ${a.patient_file_number || ''}</div>
                                    </div>
                                    <div style="margin-right: auto; text-align: left;">
                                        <div style="font-weight: 600;">${a.date} ${a.time}</div>
                                        <div style="font-size: 12px; color: #6b7280;">${this.escapeHtml(a.clinic_name || '')}</div>
                                    </div>
                                    <span class="type-badge">${a.status_label || a.status}</span>
                                </div>
                            `).join('')}
                        </div>
                    ` : '<p style="text-align: center; color: #6b7280; padding: 20px;">لا توجد مواعيد هذا الأسبوع</p>'}
                </div>
            </div>

            <div class="doctor-tab-content" data-tab="patients">
                <div class="card">
                    <h3>👥 مرضى الطبيب</h3>
                    ${patients && patients.length > 0 ? `
                        <div class="patients-table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>رقم الملف</th>
                                        <th>الاسم</th>
                                        <th>الجوال</th>
                                        <th>عدد الزيارات</th>
                                        <th>آخر زيارة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${patients.map(p => `
                                        <tr>
                                            <td><span class="badge badge-info">${this.escapeHtml(p.file_number || '')}</span></td>
                                            <td>${this.escapeHtml(p.full_name || '')}</td>
                                            <td>${this.escapeHtml(p.phone || '—')}</td>
                                            <td><span class="badge badge-success">${p.visits_count || 0}</span></td>
                                            <td>${p.last_visit || '—'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    ` : '<p style="text-align: center; color: #6b7280; padding: 20px;">لا يوجد مرضى</p>'}
                </div>
            </div>

            ${isAdmin ? `
            <div class="doctor-tab-content" data-tab="deductions">
                <div class="card">
                    <h3>💸 الخصومات والسلف والمكافآت</h3>
                    ${deductions && deductions.length > 0 ? `
                        ${deductions.map(d => `
                            <div class="deduction-item ${d.type}">
                                <div class="amount ${d.type === 'bonus' ? 'positive' : 'negative'}">${d.type === 'bonus' ? '+' : '-'}${fmt(d.amount)} ر.س</div>
                                <div class="reason">${this.escapeHtml(d.reason || '')}</div>
                                <span class="type-badge">${d.type_label || d.type}</span>
                                <span style="font-size: 12px; color: #6b7280;">${d.deduction_date || ''}</span>
                            </div>
                        `).join('')}
                    ` : '<p style="text-align: center; color: #6b7280; padding: 20px;">لا توجد حركات</p>'}
                </div>
            </div>
            ` : ''}
        `;
    }

    switchDoctorTab(tabName) {
        this.currentDoctorTab = tabName;
        document.querySelectorAll('#doctorDrawerContent .doctor-tab').forEach(t => {
            t.classList.toggle('active', t.dataset.tab === tabName);
        });
        document.querySelectorAll('#doctorDrawerContent .doctor-tab-content').forEach(c => {
            c.classList.toggle('active', c.dataset.tab === tabName);
        });
    }

    closeDoctorDrawer() {
        const drawer = document.getElementById('doctorDrawer');
        if (drawer) drawer.classList.remove('active');
    }

    openDeductionModal(doctorId = null) {
        const modal = document.getElementById('deductionModal');
        const form = document.getElementById('deductionForm');
        if (form) form.reset();
        document.getElementById('deductionDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('deductionDoctorId').value = doctorId || this.currentDoctorId || '';
        if (modal) modal.classList.add('active');
    }

    closeDeductionModal() {
        const modal = document.getElementById('deductionModal');
        if (modal) modal.classList.remove('active');
    }

    async saveDeduction(event) {
        event.preventDefault();
        const doctorId = this.currentDoctorId || document.getElementById('deductionDoctorId')?.value;
        if (!doctorId) {
            this.showAlert('pageAlert', 'لم يتم تحديد الطبيب', 'error');
            return;
        }
        const payload = {
            amount: parseFloat(document.getElementById('deductionAmount').value),
            reason: document.getElementById('deductionReason').value.trim(),
            type: document.getElementById('deductionType').value,
            deduction_date: document.getElementById('deductionDate').value || null,
        };
        const btn = document.getElementById('deductionSubmitBtn');
        btn.disabled = true; btn.textContent = 'جاري الحفظ...';
        try {
            const res = await this.apiCall(`/api/doctors/${doctorId}/deductions`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            if (res.ok) {
                this.showAlert('pageAlert', 'تم إضافة الحركة بنجاح', 'success');
                this.closeDeductionModal();
                if (this.currentDoctorId) this.openDoctorDrawer(this.currentDoctorId);
            } else {
                const err = await res.json();
                this.showAlert('pageAlert', err.message || 'خطأ', 'error');
            }
        } catch (e) {
            this.showAlert('pageAlert', 'خطأ في الاتصال', 'error');
        } finally {
            btn.disabled = false; btn.innerHTML = '<span aria-hidden="true">✓</span><span>حفظ الحركة</span>';
        }
    }

    startDoctorsPolling() {
        this.stopDoctorsPolling();
        this.doctorsPollTimer = setInterval(() => {
            if (this.currentPage === 'doctors') {
                this.loadDoctorsStats();
                this.loadDoctorsTable(this.doctorsCurrentPage || 1);
            } else {
                this.stopDoctorsPolling();
            }
        }, 30000);
    }

    stopDoctorsPolling() {
        if (this.doctorsPollTimer) {
            clearInterval(this.doctorsPollTimer);
            this.doctorsPollTimer = null;
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
