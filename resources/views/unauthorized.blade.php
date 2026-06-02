<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>غير مصرح - مجمع نبض الطبي</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 60px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        h1 {
            color: #dc3545;
            font-size: 32px;
            margin-bottom: 15px;
        }
        p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn {
            padding: 15px 40px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>غير مصرح بالدخول</h1>
        <p>
            عذراً، ليس لديك صلاحية للوصول إلى هذه الصفحة.<br>
            يرجى التواصل مع مدير النظام إذا كنت تعتقد أن هذا خطأ.
        </p>
        <a href="/dashboard" class="btn">العودة للرئيسية</a>
    </div>
</body>
</html>
