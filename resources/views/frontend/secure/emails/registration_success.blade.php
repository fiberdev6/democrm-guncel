<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Başarılı</title>
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
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #3e546a 0%, #2c3e50 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome-box {
            background: #f8f9fa;
            border-left: 4px solid #3e546a;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .welcome-box h2 {
            color: #3e546a;
            margin-top: 0;
        }
        .info-box {
            background: #e8f4fd;
            border: 1px solid #b8daff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #f27c22 0%, #f58733 100%);
            color: white!important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        a{color: #ffffff!important;}
        .contact-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Kayıt Başarılı!</h1>
        </div>
        
        <div class="content">
            <div class="welcome-box">
                <h2>Hoş Geldiniz {{ $tenantName }}!</h2>
                <p>{{ $companyName }} firması olarak Serbis ailesine katıldığınız için teşekkür ederiz.</p>
            </div>

            <div class="info-box">
                <p><strong>Demo talebiniz başarıyla alınmıştır.</strong></p>
                <p>Hesap bilgileriniz ve detaylı bilgiler en kısa süre içerisinde <strong>{{ $tenantEmail }}</strong> adresinize gönderilecektir.</p>
            </div>

            <p>14 gün boyunca sistemimizi ücretsiz olarak deneyebilir, tüm özelliklerimizi keşfedebilirsiniz.</p>

            <div style="text-align: center;">
                <a href="{{ route('giris') }}" class="button">Giriş Yap</a>
            </div>

            <div class="contact-info">
                <p><strong>Sorularınız mı var?</strong></p>
                <p>Destek ekibimiz size yardımcı olmak için burada!</p>
                <p>📧 E-posta: serbiscrmyazilimi@gmail.com</p>
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Serbis - Teknik Servis Yönetim Sistemi</p>
            <p>Bu e-posta otomatik olarak gönderilmiştir.</p>
        </div>
    </div>
</body>
</html>