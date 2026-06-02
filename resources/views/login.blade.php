<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - مجمع نبض الطبي</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .logo p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .error-message {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            display: none;
        }
        .success-message {
            background: #efe;
            color: #3c3;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            display: none;
        }
        .info-box {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            font-size: 12px;
            color: #666;
        }
        .info-box h4 {
            margin-bottom: 8px;
            color: #333;
        }
        .info-box p {
            margin: 3px 0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>مجمع نبض الطبي</h1>
            <p>نظام إدارة المجمع</p>
        </div>

        <div class="error-message" id="errorMessage"></div>
        <div class="success-message" id="successMessage"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" required placeholder="admin@nabd.com">
            </div>

            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn-login">تسجيل الدخول</button>
        </form>

        <div class="info-box">
            <h4>حسابات تجريبية:</h4>
            <p><strong>مدير:</strong> admin@nabd.com / password</p>
            <p><strong>طبيب:</strong> doctor1@nabd.com / password</p>
            <p><strong>استقبال:</strong> reception1@nabd.com / password</p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');

            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';

            try {
                const response = await fetch('/api/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (response.ok) {
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    successMessage.textContent = 'تم تسجيل الدخول بنجاح!';
                    successMessage.style.display = 'block';
                    
                    setTimeout(() => {
                        const role = data.user.role;
                        if (role === 'admin') {
                            window.location.href = '/dashboard';
                        } else if (role === 'reception') {
                            window.location.href = '/reception';
                        } else if (role === 'doctor') {
                            window.location.href = '/patients';
                        } else {
                            window.location.href = '/dashboard';
                        }
                    }, 1000);
                } else {
                    errorMessage.textContent = data.message || 'بيانات الدخول غير صحيحة';
                    errorMessage.style.display = 'block';
                }
            } catch (error) {
                errorMessage.textContent = 'حدث خطأ في الاتصال';
                errorMessage.style.display = 'block';
            }
        });

                const data = await response.json();

                if (response.ok) {
                    localStorage.setItem('auth_token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    successMessage.textContent = 'تم تسجيل الدخول بنجاح!';
                    successMessage.style.display = 'block';
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1000);
                } else {
                    errorMessage.textContent = data.message || 'بيانات الدخول غير صحيحة';
                    errorMessage.style.display = 'block';
                }
            } catch (error) {
                errorMessage.textContent = 'حدث خطأ في الاتصال';
                errorMessage.style.display = 'block';
            }
        });
    </script>
</body>
</html>
