<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 0; size: 50mm 25mm; }

    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      display: flex;
      flex-direction: column;
      justify-content: space-between; /* Üst ve alt boşlukları eşitler */
      align-items: center;
      box-sizing: border-box;
      padding: 2mm; /* Sağ ve sol boşluklar için */
    }

    .barcode-area {
      width: 100%;
      flex-grow: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      display: block; /* Yeni eklenen */
      margin: 0 auto; /* Yeni eklenen */
    }

    .barcode-area img {
      width: 100%;
      max-height: 100%;
      object-fit: contain;
    }

    .text-area {
      font-size: 8pt; /* Daha küçük yazı boyutu */
      font-weight: bold;
      text-align: center;
      line-height: 1;
    }
  </style>
</head>
<body>
  <div class="barcode-area">
    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($stock->urunKodu, 'C128', 2, 82) }}" alt="Barkod">
  </div>
  <div class="text-area">
    {{ $stock->urunKodu }}
  </div>
</body>
</html>
