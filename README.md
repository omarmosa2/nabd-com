# نظام إدارة مجمع نبض الطبي - Nabdh Medical System

## 🏥 نظرة عامة

نظام إدارة طبي متكامل مبني بـ Laravel Backend و SPA Frontend (Vanilla JavaScript)

## 🚀 المميزات الرئيسية

### Backend (Laravel)
- ✅ **44 API Endpoint** كامل
- ✅ **Role-based Access Control** (Admin, Reception, Doctor)
- ✅ **Sanctum Authentication**
- ✅ **Service Layer Architecture**
- ✅ **Policy-based Authorization**
- ✅ **Real-time Events** (VisitCreated, AppointmentCreated)
- ✅ **Financial Calculations Engine**
- ✅ **Daily/Monthly Reports**

### Frontend (SPA)
- ✅ **Single Page Application** بدون frameworks
- ✅ **Client-side Routing** مع History API
- ✅ **Role-based Navigation** ديناميكي
- ✅ **Protected Routes** مع Guards
- ✅ **Real-time UI Updates**
- ✅ **Responsive Design** مع RTL Support
- ✅ **Chart.js Integration** للرسوم البيانية

## 📦 التثبيت

```bash
# تثبيت الـ dependencies
composer install

# إعداد قاعدة البيانات
php artisan migrate:fresh --seed

# تشغيل الخادم
php artisan serve
```

## 🔐 حسابات تجريبية

| الدور | البريد الإلكتروني | كلمة المرور | الصفحة الأولى |
|------|------------------|-------------|---------------|
| Admin | admin@nabd.com | password | /dashboard |
| Reception | reception1@nabd.com | password | /reception |
| Doctor | doctor1@nabd.com | password | /patients |

## 🗺️ خريطة المسارات (Routing Map)

### Public Routes
- `/` → يعيد التوجيه حسب الدور
- `/login` → صفحة تسجيل الدخول

### Protected Routes (داخل AppLayout)
- `/dashboard` → لوحة التحكم (Admin فقط)
- `/reception` → الاستقبال (Admin + Reception)
- `/patients` → المرضى (الجميع)
- `/finance` → المالية (Admin فقط)
- `/reports` → التقارير (Admin فقط)
- `/appointments` → المواعيد (الجميع)
- `/settings` → الإعدادات (Admin فقط)
- `/unauthorized` → صفحة غير مصرح

## 🧭 Role-based Navigation

### Admin
```
Dashboard → Reception → Patients → Finance → Reports → Appointments → Settings
```

### Reception
```
Reception → Patients → Appointments
```

### Doctor
```
Patients → Appointments
```

## 📡 API Endpoints

### Authentication
- `POST /api/login` - تسجيل الدخول
- `POST /api/logout` - تسجيل الخروج
- `GET /api/user` - معلومات المستخدم الحالي

### Dashboard
- `GET /api/dashboard/stats` - إحصائيات اليوم
- `GET /api/dashboard/revenue` - الإيرادات الشهرية
- `GET /api/dashboard/charts` - بيانات الرسوم البيانية
- `GET /api/dashboard/appointments` - المواعيد القادمة
- `GET /api/dashboard/top-doctors` - أكثر الأطباء نشاطاً
- `GET /api/dashboard/top-clinics` - أكثر العيادات نشاطاً

### Reception
- `POST /api/reception/patients/upsert` - إنشاء/تحديث مريض
- `GET /api/reception/patients/by-file/{fileNumber}` - البحث برقم الملف
- `GET /api/reception/doctors?clinic_id=` - الأطباء حسب العيادة
- `POST /api/reception/visits` - إنشاء زيارة
- `POST /api/reception/visits/calc-preview` - معاينة الحسابات

### Patients
- `GET /api/patients` - قائمة المرضى (مع بحث وفلترة)
- `GET /api/patients/{id}` - تفاصيل المريض
- `PUT /api/patients/{id}` - تحديث مريض
- `DELETE /api/patients/{id}` - حذف مريض

### Visits
- `GET /api/visits` - قائمة الزيارات
- `POST /api/visits` - إنشاء زيارة
- `GET /api/visits/{id}` - تفاصيل الزيارة
- `PUT /api/visits/{id}` - تحديث زيارة
- `DELETE /api/visits/{id}` - حذف زيارة

### Finance
- `GET /api/finance/summary` - ملخص مالي
- `GET /api/finance/doctors` - ملخص الأطباء
- `GET /api/finance/doctor/{id}/details` - تفاصيل طبيب
- `POST /api/finance/deductions` - إضافة خصم

### Reports
- `GET /api/reports/patients` - تقرير المرضى
- `GET /api/reports/visits` - تقرير الزيارات
- `GET /api/reports/finance` - تقرير مالي
- `GET /api/reports/daily?date=` - تقرير يومي
- `GET /api/reports/monthly?month=` - تقرير شهري

### Appointments
- `GET /api/appointments` - قائمة المواعيد
- `POST /api/appointments` - إنشاء موعد
- `GET /api/appointments/{id}` - تفاصيل موعد
- `PUT /api/appointments/{id}` - تحديث موعد
- `DELETE /api/appointments/{id}` - حذف موعد

### Settings
- `GET /api/settings` - جلب الإعدادات
- `PUT /api/settings` - تحديث الإعدادات

### Reference Data
- `GET /api/clinics` - قائمة العيادات
- `GET /api/users?role=` - قائمة المستخدمين

## 🏗️ البنية المعمارية

### Backend Architecture
```
app/
├── Http/
│   ├── Controllers/     # 11 Controllers
│   ├── Middleware/      # RoleMiddleware
│   ├── Requests/        # Form Requests
│   └── Resources/       # API Resources
├── Models/              # 7 Models
├── Policies/            # 2 Policies
├── Services/            # 6 Services
├── Events/              # 2 Events
└── Enums/               # 3 Enums
```

### Frontend Architecture
```
resources/views/
└── app.blade.php        # SPA Shell

public/
└── spa-router.js        # Client-side Router
```

## 🔄 Data Flow

```
User Action (UI)
    ↓
API Call (Fetch)
    ↓
Laravel Controller
    ↓
Service Layer (Business Logic)
    ↓
Database (MySQL)
    ↓
Response (JSON)
    ↓
UI Update (DOM Manipulation)
```

## 🎯 Key Features

### 1. Smart Role Redirect
بعد تسجيل الدخول، يتم توجيه المستخدم تلقائياً:
- Admin → `/dashboard`
- Reception → `/reception`
- Doctor → `/patients`

### 2. Dynamic Sidebar
السايدبار يتغير حسب دور المستخدم:
- Admin: 7 عناصر
- Reception: 3 عناصر
- Doctor: 2 عناصر

### 3. Financial Engine
حسابات تلقائية لكل زيارة:
- رسوم المعاينة
- العمليات الطبية
- خصومات المجمع والطبيب
- حصة المجمع والطبيب
- المراجعات المجانية

### 4. Real-time Updates
Events تُطلق عند:
- إنشاء زيارة جديدة
- إنشاء موعد جديد
- تحديث البيانات

### 5. Patient Lifecycle
```
تسجيل مريض → إنشاء زيارة → إضافة عمليات → حساب مالي → حفظ
```

## 🔒 الأمان

- **Sanctum Token Authentication**
- **Role-based Middleware**
- **Policy Authorization**
- **Form Request Validation**
- **SQL Injection Protection**
- **XSS Protection**

## 📊 الإحصائيات

- **44 API Endpoints**
- **11 Controllers**
- **7 Models**
- **6 Services**
- **8 Pages**
- **3 User Roles**
- **20+ Database Tables**

## 🚀 التطوير المستقبلي

- [ ] Export to Excel/PDF
- [ ] Real-time WebSocket Integration
- [ ] Advanced Analytics Dashboard
- [ ] Patient Medical History
- [ ] Prescription Management
- [ ] Insurance Integration
- [ ] Multi-language Support
- [ ] Mobile App (React Native)

## 📝 ملاحظات

- النظام يستخدم MySQL كقاعدة بيانات
- الملفات في `public/` متاحة مباشرة
- الـ SPA يعمل بدون أي framework خارجي
- Chart.js للرسوم البيانية
- دعم كامل للغة العربية (RTL)

## 🎉 جاهز للاستخدام!

النظام جاهز تماماً للاختبار والاستخدام. قم بتشغيل `php artisan serve` وافتح `http://localhost:8000`
