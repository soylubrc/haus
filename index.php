<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haus des Logistics Lojistik Yönetim Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Haus des Logistics Yönetim Sistemi</h1>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Tur Yönetimi</h5>
                    </div>
                    <div class="card-body">
                        <p>Araç turlarını planlayın, düzenleyin ve takip edin.</p>
                        <a href="tur_ekle.php" class="btn btn-primary">Yeni Tur Ekle</a>
                        <a href="turlar.php" class="btn btn-outline-primary mt-2">Turları Listele</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">Şoför Yönetimi</h5>
                    </div>
                    <div class="card-body">
                        <p>Şoför bilgilerini ekleyin ve düzenleyin.</p>
                        <a href="sofor_ekle.php" class="btn btn-success">Yeni Şoför Ekle</a>
                        <a href="soforler.php" class="btn btn-outline-success mt-2">Şoförleri Listele</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">Araç Yönetimi</h5>
                    </div>
                    <div class="card-body">
                        <p>Araç bilgilerini ekleyin ve düzenleyin.</p>
                        <a href="arac_ekle.php" class="btn btn-info">Yeni Araç Ekle</a>
                        <a href="araclar.php" class="btn btn-outline-info mt-2">Araçları Listele</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="card-title mb-0">İl Yönetimi</h5>
                    </div>
                    <div class="card-body">
                        <p>İl bilgilerini ve mesafeleri yönetin.</p>
                        <a href="il_ekle.php" class="btn btn-warning">Yeni İl Ekle</a>
                        <a href="iller.php" class="btn btn-outline-warning mt-2">İlleri Listele</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">Yük Tipleri</h5>
                    </div>
                    <div class="card-body">
                        <p>Yük tiplerini ekleyin ve düzenleyin.</p>
                        <a href="yuk_tipi_ekle.php" class="btn btn-danger">Yeni Yük Tipi Ekle</a>
                        <a href="yuk_tipleri.php" class="btn btn-outline-danger mt-2">Yük Tiplerini Listele</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">Anlık Tur Ekranı</h5>
                    </div>
                    <div class="card-body">
                        <p>Depo çalışanları için anlık tur bilgilerini görüntüleyin.</p>
                        <a href="canli_ekran.php" class="btn btn-secondary">Canlı Ekranı Aç</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>