<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kayıt Başarılı</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="{{asset('frontend/custom.css')}}" rel="stylesheet">
  <style>
    body {
      background-color: #e9e9e9; /* Daha sade gri */
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-family: "Segoe UI", sans-serif;
    }

    .logo-container {
      text-align: center;
      margin-bottom: 30px;
    }

    .logo-container img {
      width: 160px;
      height: auto;
    }

    .success-card {
      background: #fff;
      border-radius: 1.5rem;
      padding: 3rem 2rem;
      text-align: center;
      max-width: 420px;
      width: 100%;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      animation: fadeIn 0.6s ease;
    }

    .success-icon {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: #4caf50;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      animation: pop 0.5s ease;
    }

    .success-icon svg {
      width: 40px;
      height: 40px;
      color: white;
    }

    h2 {
      color: #333;
      font-weight: 600;
    }

    p {
      color: #666;
      margin-bottom: 1.5rem;
    }

    .btn-gradient {
      background: linear-gradient(135deg, #f27c22, #f5b642);
      border: none;
      color: #fff;
      padding: 0.7rem 2rem;
      border-radius: 2rem;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-gradient:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pop {
      0% { transform: scale(0.5); opacity: 0; }
      100% { transform: scale(1); opacity: 1; }
    }
  </style>
</head>
<body>
  
  <!-- Logo üstte ortalı -->
  <div class="logo-container">
    <img src="{{ asset('frontend/img/serbis-logo.png') }}" alt="Firma Logosu">
  </div>

  <!-- Başarı Kartı -->
  <div class="success-card">
    <div class="success-icon">
      <!-- ✅ Onay İkonu -->
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
      </svg>
    </div>
    <h2>Kayıt Başarılı</h2>
    <h4>Demo talebiniz alınmıştır.</h4>
    <p>Hoş geldiniz! Serbis demo hesap bilgilerinizi en kısa süre içerisinde e-posta adresinize göndereceğiz.</p>
    <a href="{{ url('/kullanici-girisi') }}" class="btn btn-gradient">Giriş Yap</a>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
