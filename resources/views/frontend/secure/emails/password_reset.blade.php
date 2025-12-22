<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırlama Talebi</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .info-box { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #ffc107; }
        .reset-button { 
            color: white; 
            padding: 12px 24px; 
            text-decoration: none; 
            border-radius: 5px; 
            display: inline-block; 
            margin: 20px 0; 
        }
        .warning { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Şifre Sıfırlama Talebi</h2>
        </div>
        
        <div class="content">
            <h3>Merhaba {{ $userName }},</h3>
            <p>Hesabınız için bir şifre sıfırlama talebi aldık. Eğer bu talebi siz yapmadıysanız, bu e-postayı dikkate almayınız.</p>
            
            <div class="info-box">
                <p><strong>Şifrenizi sıfırlamak için aşağıdaki butona tıklayınız:</strong></p>
                <div style="text-align: center;">
                    <a href="{{ $resetUrl }}" class="reset-button">Şifremi Sıfırla</a>
                </div>
            </div>
            
            <p class="warning">⚠️ Bu bağlantı {{ $expiresAt }} tarihine kadar geçerlidir.</p>
            
            <p><small>Eğer buton çalışmıyorsa, aşağıdaki bağlantıyı tarayıcınıza kopyalayıp yapıştırabilirsiniz:</small></p>
            <p style="word-break: break-all; color: #666; font-size: 0.9rem;">{{ $resetUrl }}</p>
            
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
            
            <p><small>Bu e-posta otomatik olarak gönderilmiştir. Herhangi bir sorunuz varsa destek ekibimizle iletişime geçebilirsiniz.</small></p>
        </div>
    </div>
</body>
</html>