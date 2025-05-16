<?php
require_once 'config.php';

// Bugün ve yarın için planlanan turları çek
$stmt = $db->query("SELECT t.*, CONCAT(s.ad, ' ', s.soyad) as sofor_adi, a.plaka, t.paket_durumu, t.guncelleme_zamani 
                    FROM turlar t
                    LEFT JOIN soforler s ON t.sofor_id = s.sofor_id
                    LEFT JOIN araclar a ON t.arac_id = a.arac_id
                    WHERE t.durum IN ('Planlandı', 'Yolda')
                      AND (t.cikis_tarihi = CURDATE() OR t.cikis_tarihi = DATE_ADD(CURDATE(), INTERVAL 1 DAY))
                    ORDER BY t.cikis_tarihi, t.cikis_saati");
$turlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Şu anki tarih ve saat
$simdikiZaman = date('d.m.Y H:i:s');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canlı Tur Takip - Haus des Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="60"> <!-- Otomatik yenileme -->
</head>
<body>
    <div class="container-fluid">
        <header class="main-header d-flex justify-content-between align-items-center py-3 px-4 bg-primary text-white">
            <div class="d-flex align-items-center">
                <img src="img/hausdeslogistics.png" alt="Haus des Logistics Logo" class="company-logo me-3" width="60">
                <h1 class="mb-0">Haus des Logistics - Canlı Tur Takip Ekranı</h1>
            </div>
            <div class="text-end">
                <p class="mb-0"><i class="far fa-clock"></i> Güncellenme: <?= $simdikiZaman ?></p>
                <small>Sayfa her dakika otomatik yenilenir</small>
            </div>
        </header>

        <div class="row mt-3">
            <!-- Bugünkü Turlar -->
            <div class="col-md-6">
                <h3 class="text-center bg-info text-white py-2">Bugünkü Turlar</h3>
                <table class="table table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th>Şoför</th>
                            <th>Araç</th>
                            <th>Durum</th>
                            <th>Paket</th>
                            <th>Çıkış</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($turlar as $tur): ?>
                            <?php if (date('Y-m-d') == $tur['cikis_tarihi']): ?>
                            <tr>
                                <td><?= $tur['sofor_adi'] ?? 'Atanmadı' ?></td>
                                <td><?= $tur['plaka'] ?? 'Bilinmiyor' ?></td>
                                <td><span class="badge <?= $tur['durum'] == 'Yolda' ? 'bg-success' : 'bg-warning' ?>">
                                    <?= $tur['durum'] ?></span></td>
                                <td><span class="badge <?= $tur['paket_durumu'] == 'Hazır' ? 'bg-info' : 'bg-danger' ?>">
                                    <?= $tur['paket_durumu'] ?></span></td>
                                <td><?= date('d.m.Y H:i', strtotime($tur['cikis_tarihi'].' '.$tur['cikis_saati'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Yarınki Turlar -->
            <div class="col-md-6">
                <h3 class="text-center bg-warning text-white py-2">Yarınki Turlar</h3>
                <table class="table table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th>Şoför</th>
                            <th>Araç</th>
                            <th>Durum</th>
                            <th>Paket</th>
                            <th>Çıkış</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($turlar as $tur): ?>
                            <?php if (date('Y-m-d', strtotime('+1 day')) == $tur['cikis_tarihi']): ?>
                            <tr>
                                <td><?= $tur['sofor_adi'] ?? 'Atanmadı' ?></td>
                                <td><?= $tur['plaka'] ?? 'Bilinmiyor' ?></td>
                                <td><span class="badge <?= $tur['durum'] == 'Yolda' ? 'bg-success' : 'bg-warning' ?>">
                                    <?= $tur['durum'] ?></span></td>
                                <td><span class="badge <?= $tur['paket_durumu'] == 'Hazır' ? 'bg-info' : 'bg-danger' ?>">
                                    <?= $tur['paket_durumu'] ?></span></td>
                                <td><?= date('d.m.Y H:i', strtotime($tur['cikis_tarihi'].' '.$tur['cikis_saati'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <footer class="text-center py-3">
            <p>&copy; <?= date('Y') ?> Haus des Logistics - Canlı Tur Takip Sistemi</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
