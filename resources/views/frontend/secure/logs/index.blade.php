<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uygulama Logları</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre {
            white-space: pre-wrap; /* Uzun satırları otomatik sar */
            word-break: break-all; /* Kelime içinde kırma */
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            max-height: 400px; /* Belirli bir yükseklik sınırı */
            overflow-y: auto; /* Dikey kaydırma çubuğu */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Uygulama Logları</h1>

        <form action="" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="logDate" class="form-label">Tarihe Göre Filtrele:</label>
                    <input type="date" id="logDate" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-4">
                    <label for="keyword" class="form-label">Anahtar Kelime Ara:</label>
                    <input type="text" id="keyword" name="keyword" class="form-control" value="{{ request('keyword') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrele</button>
                    <a href="/logs" class="btn btn-secondary">Temizle</a>
                </div>
            </div>
        </form>

        @foreach ($logs as $date => $logContent)
            @php
                $displayLog = false;
                $filteredContent = [];

                // Tarih filtresi
                if (request('date') && request('date') != $date) {
                    continue; // Tarih uyuşmuyorsa bu logu atla
                }

                // Anahtar kelime filtresi
                if (request('keyword')) {
                    foreach ($logContent as $line) {
                        if (stripos($line, request('keyword')) !== false) {
                            $filteredContent[] = $line;
                            $displayLog = true;
                        }
                    }
                } else {
                    $filteredContent = $logContent;
                    $displayLog = true;
                }
            @endphp

            @if ($displayLog && !empty($filteredContent))
                <div class="card mb-4">
                    <div class="card-header">
                        Loglar - {{ $date }}
                    </div>
                    <div class="card-body">
                        <pre>{{ implode("\n", $filteredContent) }}</pre>
                    </div>
                </div>
            @elseif ($displayLog && empty($filteredContent) && request('keyword'))
                <div class="card mb-4">
                    <div class="card-header">
                        Loglar - {{ $date }}
                    </div>
                    <div class="card-body">
                        <p>Bu tarihte ve anahtar kelimeyle eşleşen log bulunamadı.</p>
                    </div>
                </div>
            @endif
        @endforeach

        @if (empty($logs) || (request('date') && !array_key_exists(request('date'), $logs)))
            <p>Gösterilecek log bulunamadı.</p>
        @endif
    </div>
</body>
</html>