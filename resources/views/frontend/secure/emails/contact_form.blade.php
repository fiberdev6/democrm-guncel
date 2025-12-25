<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İletişim Formu Mesajı</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #3e546a 0%, #49657B 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
        }
        .info-row {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eeeeee;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .label {
            font-weight: 600;
            color: #3e546a;
            margin-bottom: 5px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .value {
            color: #333;
            font-size: 16px;
        }
        .message-box {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3e546a;
            margin-top: 10px;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .badge {
            display: inline-block;
            background-color: #3e546a;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Yeni İletişim Formu Mesajı</h1>
        </div>
        
        <div class="content">
            <div class="badge">{{ now()->format('d.m.Y H:i') }}</div>
            
            <div class="info-row">
                <div class="label">👤 Ad Soyad</div>
                <div class="value">{{ $data['name'] }}</div>
            </div>
            
            <div class="info-row">
                <div class="label">✉️ E-posta</div>
                <div class="value">
                    <a href="mailto:{{ $data['email'] }}" style="color: #3e546a; text-decoration: none;">
                        {{ $data['email'] }}
                    </a>
                </div>
            </div>
            
            @if(!empty($data['phone']))
            <div class="info-row">
                <div class="label">📱 Telefon</div>
                <div class="value">
                    <a href="tel:{{ $data['phone'] }}" style="color: #3e546a; text-decoration: none;">
                        {{ $data['phone'] }}
                    </a>
                </div>
            </div>
            @endif
            
            <div class="info-row">
                <div class="label">💬 Mesaj</div>
                <div class="message-box">
                    {{ $data['message'] }}
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Bu mesaj <strong>{{ config('app.name') }}</strong> iletişim formu üzerinden gönderilmiştir.</p>
            <p style="margin-top: 10px; color: #999;">
                Mesaja direkt olarak yanıt vermek için yukarıdaki e-posta adresine tıklayabilirsiniz.
            </p>
        </div>
    </div>
</body>
</html>