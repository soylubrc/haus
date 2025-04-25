<?php
require_once 'config.php';

// Özet bilgileri getir
$stmt = $db->query("SELECT COUNT(*) AS toplam FROM turlar");
$toplam_tur = $stmt->fetch(PDO::FETCH_ASSOC)['toplam'];

$stmt = $db->query("SELECT COUNT(*) AS toplam FROM turlar WHERE durum = 'Yolda'");
$yoldaki_tur = $stmt->fetch(PDO::FETCH_ASSOC)['toplam'];

$stmt = $db->query("SELECT COUNT(*) AS toplam FROM soforler");
$toplam_sofor = $stmt->fetch(PDO::FETCH_ASSOC)['toplam'];

$stmt = $db->query("SELECT COUNT(*) AS toplam FROM araclar");
$toplam_arac = $stmt->fetch(PDO::FETCH_ASSOC)['toplam'];

$stmt = $db->query("SELECT COUNT(*) AS toplam FROM turlar WHERE paket_durumu = 'Hazırlanıyor'");
$hazirlanmakta_olan_paket = $stmt->fetch(PDO::FETCH_ASSOC)['toplam'];

// Yaklaşan turları getir
$stmt = $db->prepare("
    SELECT 
        t.*, 
        s.ad AS sofor_adi, 
        s.soyad AS sofor_soyadi, 
        a.plaka
    FROM 
        turlar t
    LEFT JOIN 
        soforler s ON t.sofor_id = s.sofor_id
    LEFT JOIN 
        araclar a ON t.arac_id = a.arac_id
    WHERE 
        t.durum = 'Planlandı' AND
        t.cikis_tarihi >= CURDATE()
    ORDER BY 
        t.cikis_tarihi ASC, 
        t.cikis_saati ASC
    LIMIT 5
");
$stmt->execute();
$yaklasan_turlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ana Sayfa - Haus des Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Haus des Logistics Yönetim Sistemi</h1>
        
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Tur</h5>
                        <p class="card-text display-4"><?= $toplam_tur ?></p>
                        <a href="turlar.php" class="btn btn-light">Turları Görüntüle</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Yoldaki Tur</h5>
                        <p class="card-text display-4"><?= $yoldaki_tur ?></p>
                        <a href="turlar.php?durum=Yolda" class="btn btn-light">Yoldaki Turlar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Şoför</h5>
                        <p class="card-text display-4"><?= $toplam_sofor ?></p>
                        <a href="soforler.php" class="btn btn-light">Şoförleri Görüntüle</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">Hazırlanmakta Olan Paket</h5>
                        <p class="card-text display-4"><?= $hazirlanmakta_olan_paket ?></p>
                        <a href="turlar.php?paket_durumu=Hazırlanıyor" class="btn btn-light">Paketleri Görüntüle</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Hızlı Erişim</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="tur_ekle.php" class="btn btn-success">Yeni Tur Ekle</a>
                            <a href="sofor_ekle.php" class="btn btn-primary">Yeni Şoför Ekle</a>
                            <a href="arac_ekle.php" class="btn btn-secondary">Yeni Araç Ekle</a>
                            <a href="raporlar.php" class="btn btn-info">Raporlar</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Yaklaşan Turlar</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($yaklasan_turlar) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Çıkış Tarihi</th>
                                            <th>Şoför</th>
                                            <th>Araç</th>
                                            <th>Paket Durumu</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($yaklasan_turlar as $tur): ?>
                                            <tr>
                                                <td><?= date('d.m.Y H:i', strtotime($tur['cikis_tarihi'] . ' ' . $tur['cikis_saati'])) ?></td>
                                                <td><?= htmlspecialchars($tur['sofor_adi'] . ' ' . $tur['sofor_soyadi']) ?></td>
                                                <td><?= htmlspecialchars($tur['plaka']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $tur['paket_durumu'] === 'Hazır' ? 'success' : 'warning' ?>">
                                                        <?= $tur['paket_durumu'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="tur_detay.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-info">Detay</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center">Yaklaşan tur bulunmuyor.</p>
                        <?php endif; ?>
                        <a href="turlar.php" class="btn btn-primary">Tüm Turları Görüntüle</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>