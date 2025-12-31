<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erişim Engellendi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #252b3b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
        }
        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .error-code {
            font-size: 72px;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 10px;
        }
        .error-title {
            font-size: 24px;
            color: #495057;
            margin-bottom: 15px;
        }
        .error-message {
            color: #6c757d;
            margin-bottom: 30px;
        }
        .btn-back {
            background: #252b3b;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            text-decoration: none;
            transition: transform 0.3s;
        }
        .btn-back:hover {
            transform: scale(1.05);
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-lock"></i>
        </div>
        <div class="error-code">403</div>
        <div class="error-title">Erişim Engellendi</div>
        <p class="error-message">
            Bu sayfaya erişim yetkiniz bulunmamaktadır.<br>
            Yetkili olduğunuzu düşünüyorsanız yöneticinizle iletişime geçin.
        </p>
        <a href="{{ url()->previous() }}" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i>Geri Dön
        </a>
        @if(auth()->check())
            <a href="{{ route('secure.home', auth()->user()->tenant_id) }}" class="btn btn-outline-secondary ms-2" style="border-radius: 25px; padding: 8px 30px;">
                <i class="fas fa-home me-2"></i>Ana Sayfa
            </a>
        @endif
    </div>
</body>
</html>