<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات - مجمع نبض الطبي</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 20px; }
        .card h2 { color: #333; margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { color: #333; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        .form-group input, .form-group select, .form-group textarea { padding: 12px; border: 2px solid #e1e1e1; border-radius: 8px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #667eea; }
        .btn { padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; transition: all 0.3s; }
        .btn:hover { background: #5568d3; transform: translateY(-2px); }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .loading { text-align: center; padding: 40px; color: #666; }
        .back-btn { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <button class="btn btn-secondary back-btn" onclick="window.history.back()">← رجوع</button>
        
        <div class="header">
            <h1>الإعدادات</h1>
            <p>إدارة إعدادات النظام</p>
        </div>

        <div id="alertContainer"></div>

        <div class="card">
            <h2>إعدادات المجمع</h2>
            <form id="settingsForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label>اسم المجمع *</label>
                        <input type="text" id="complexName" required>
                    </div>
                    <div class="form-group">
                        <label>رسوم المعاينة الافتراضية *</label>
                        <input type="number" id="defaultExaminationFee" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>العملة *</label>
                        <input type="text" id="currency" required>
                    </div>
                    <div class="form-group">
                        <label>رمز العملة *</label>
                        <input type="text" id="currencySymbol" required>
                    </div>
                    <div class="form-group">
                        <label>رأس الفاتورة</label>
                        <input type="text" id="invoiceHeader">
                    </div>
                    <div class="form-group">
                        <label>تذييل الفاتورة</label>
                        <input type="text" id="invoiceFooter">
                    </div>
                </div>
                <button type="submit" class="btn btn-success">حفظ الإعدادات</button>
            </form>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        if (!token) window.location.href = '/login';

        async function loadSettings() {
            try {
                const res = await fetch('/api/settings', { headers: { 'Authorization': `Bearer ${token}` } });
                const data = await res.json();
                
                document.getElementById('complexName').value = data.complex_name || '';
                document.getElementById('defaultExaminationFee').value = data.default_examination_fee || 0;
                document.getElementById('currency').value = data.currency || 'SAR';
                document.getElementById('currencySymbol').value = data.currency_symbol || 'ل.س';
                document.getElementById('invoiceHeader').value = data.invoice_header || '';
                document.getElementById('invoiceFooter').value = data.invoice_footer || '';
            } catch (error) {
                showAlert('خطأ في تحميل الإعدادات', 'error');
            }
        }

        document.getElementById('settingsForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = {
                complex_name: document.getElementById('complexName').value,
                default_examination_fee: parseFloat(document.getElementById('defaultExaminationFee').value),
                currency: document.getElementById('currency').value,
                currency_symbol: document.getElementById('currencySymbol').value,
                invoice_header: document.getElementById('invoiceHeader').value,
                invoice_footer: document.getElementById('invoiceFooter').value
            };

            try {
                const res = await fetch('/api/settings', {
                    method: 'PUT',
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (res.ok) {
                    showAlert('تم حفظ الإعدادات بنجاح', 'success');
                } else {
                    showAlert('خطأ في حفظ الإعدادات', 'error');
                }
            } catch (error) {
                showAlert('خطأ في الاتصال', 'error');
            }
        });

        function showAlert(message, type) {
            const container = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            container.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
        }

        loadSettings();
    </script>
</body>
</html>
