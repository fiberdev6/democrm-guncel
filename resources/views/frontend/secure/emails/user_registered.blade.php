<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesabınız Oluşturuldu</title>
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
        .header p {
            margin: 10px 0 0 0;
            font-size: 18px;
            opacity: 0.9;
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
        .package-info {
            background: #e8f4fd;
            border: 1px solid #b8daff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .package-info h4 {
            color: #3e546a;
            margin-top: 0;
            font-size: 18px;
        }
        .package-info h5 {
            color: #3e546a;
            margin-top: 15px;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 10px 0;
        }
        .feature-list li {
            padding: 8px 0;
            border-bottom: 1px solid #d1ecf1;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list strong {
            color: #3e546a;
        }
        .credentials-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .credentials-box h4 {
            color: #856404;
            margin-top: 0;
        }
        .credentials-box p {
            margin: 10px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #f27c22 0%, #f58733 100%);
            color: white!important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: 600;
        }
        a{color: #ffffff!important;}
        .button:hover {
            background: linear-gradient(135deg, #e06b11 0%, #e47622 100%);
        }
        .trial-badge {
            display: inline-block;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .info-highlight {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .contact-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 20px 15px;
            }
            .header {
                padding: 20px 15px;
            }
            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Hesabınız Hazır!</h1>
            <p>{{ $mailData['tenant']->firma_adi }}</p>
        </div>
        
        <div class="content">
            <div class="welcome-box">
                <h2>Hoş Geldiniz!</h2>
                <p>Hesabınız başarıyla oluşturuldu. Sisteme giriş yaparak hizmetlerimizden faydalanmaya başlayabilirsiniz.</p>
            </div>

            @if(isset($mailData['firma_kodu']) && $mailData['firma_kodu'])
            <div class="firma-kodu-box">
                <div class="firma-kodu-label">Firma Kodunuz</div>
                <div class="firma-kodu-value">{{ $mailData['firma_kodu'] }}</div>
            </div>
            <div class="warning-box">
                <strong>⚠️ ÖNEMLİ UYARI</strong>
                <p>Bu firma kodunu mutlaka güvenli bir yere kaydedin! Sisteme giriş yaparken bu kodu kullanacaksınız.</p>
            </div>
             @endif

             <div class="credentials-box">
                <h4>🔐 Giriş Bilgileriniz</h4>
                
                <div class="credential-item">
                    <strong>Kullanıcı Adı</strong>
                    <div class="credential-value">{{ $mailData['username'] ?? 'Belirsiz' }}</div>
                </div>

                @if(isset($mailData['firma_kodu']) && $mailData['firma_kodu'])
                <div class="credential-item">
                    <strong>Firma Kodu</strong>
                    <div class="credential-value">{{ $mailData['firma_kodu'] }}</div>
                </div>
                @endif

                <div class="credential-item">
                    <strong>Şifre</strong>
                    <div class="credential-value">Kayıt sırasında belirlediğiniz şifre</div>
                </div>

                <p style="font-size: 13px; color: #6c757d; margin-top: 15px; font-style: italic;">
                    💡 Güvenliğiniz için şifrenizi kimseyle paylaşmayın.
                </p>
            </div>

            <div class="package-info">
                <h4> Paket Bilgileriniz</h4>
                
                @if($mailData['isTrialActive'])
                    <div style="margin-bottom: 15px;">
                        <strong>Paket Türü:</strong> 
                        <span class="trial-badge">Deneme Süresi (Ücretsiz)</span>
                    </div>
                    
                    <div class="info-highlight">
                        <p style="margin: 5px 0;"><strong>⏰ Kalan Süre:</strong> {{ $mailData['trialDaysRemaining'] }} gün</p>
                        <p style="margin: 5px 0;"><strong>📅 Bitiş Tarihi:</strong> {{ $mailData['tenant']->trial_ends_at->format('d.m.Y') }}</p>
                    </div>
                    
                    <h5>✨ Deneme Süresi Özellikleri:</h5>
                    <ul class="feature-list">
                        <li>
                            <strong>👥 Personel Sayısı:</strong> 
                            @if($mailData['tenant']->personelSayisi == -1)
                                <span style="color: #28a745; font-weight: 600;">Sınırsız ∞</span>
                            @elseif($mailData['tenant']->personelSayisi)
                                {{ $mailData['tenant']->personelSayisi }}
                            @else
                                Belirsiz
                            @endif
                        </li>
                        <li>
                            <strong>🏢 Bayi Sayısı:</strong> 
                            @if($mailData['tenant']->bayiSayisi == -1)
                                <span style="color: #28a745; font-weight: 600;">Sınırsız ∞</span>
                            @elseif($mailData['tenant']->bayiSayisi)
                                {{ $mailData['tenant']->bayiSayisi }}
                            @else
                                Belirsiz
                            @endif
                        </li>
                        <li>
                            <strong>📦 Stok Sayısı:</strong> 
                            @if($mailData['tenant']->stokSayisi == -1)
                                <span style="color: #28a745; font-weight: 600;">Sınırsız ∞</span>
                            @elseif($mailData['tenant']->stokSayisi)
                                {{ $mailData['tenant']->stokSayisi }}
                            @else
                                Belirsiz
                            @endif
                        </li>
                        <li>
                            <strong>🔄 Konsinye Sayısı:</strong> 
                            @if($mailData['tenant']->konsinyeSayisi == -1)
                                <span style="color: #28a745; font-weight: 600;">Sınırsız ∞</span>
                            @elseif($mailData['tenant']->konsinyeSayisi)
                                {{ $mailData['tenant']->konsinyeSayisi }}
                            @else
                                Belirsiz
                            @endif
                        </li>
                    </ul>
                @else
                    <p><strong>Paket Türü:</strong> Aktif Abonelik</p>
                    <p>Abonelik detaylarınızı sistem üzerinden görüntüleyebilirsiniz.</p>
                @endif
            </div>
            
            
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('giris') }}" class="button" style="">
                     Sisteme Giriş Yap
                </a>
            </div>

            <div class="contact-info">
                <p><strong> Sorularınız mı var?</strong></p>
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