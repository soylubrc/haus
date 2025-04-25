<?php
require_once 'config.php';

// Turları getir
$stmt = $db->query("SELECT t.*, 
                    CONCAT(s.ad, ' ', s.soyad) as sofor_adi, 
                    a.plaka 
                    FROM turlar t
                    LEFT JOIN soforler s ON t.sofor_id = s.sofor_id
                    LEFT JOIN araclar a ON t.arac_id = a.arac_id
                    ORDER BY t.cikis_tarihi DESC, t.cikis_saati DESC");
$turlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turlar - Haus des Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Turlar</h1>
            <div>
                <a href="tur_ekle.php" class="btn btn-primary">Yeni Tur Ekle</a>
                <a href="index.php" class="btn btn-secondary">Ana Sayfa</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Şoför</th>
                                <th>Araç</th>
                                <th>Çıkış Tarihi</th>
                                <th>Çıkış Saati</th>
                                <th>Tahmini Dönüş</th>
                                <th>Mesafe</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($turlar) > 0): ?>
                                <?php foreach ($turlar as $tur): ?>
                                    <tr>
                                        <td><?= $tur['tur_id'] ?></td>
                                        <td><?= $tur['sofor_adi'] ?></td>
                                        <td><?= $tur['plaka'] ?></td>
                                        <td><?= formatTarih($tur['cikis_tarihi']) ?></td>
                                        <td><?= formatSaat($tur['cikis_saati']) ?></td>
                                        <td>
                                            <?= formatTarih($tur['tahmini_donus_tarihi']) ?> 
                                            <?= formatSaat($tur['tahmini_donus_saati']) ?>
                                        </td>
                                        <td><?= $tur['toplam_mesafe'] ?> km</td>
                                        <td>
                                            <?php
                                            $durum_class = '';
                                            switch ($tur['durum']) {
                                                case 'Planlandı':
                                                    $durum_class = 'bg-info';
                                                    break;
                                                case 'Yolda':
                                                    $durum_class = 'bg-warning';
                                                    break;
                                                case 'Tamamlandı':
                                                    $durum_class = 'bg-success';
                                                    break;
                                                case 'İptal Edildi':
                                                    $durum_class = 'bg-danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?= $durum_class ?>"><?= $tur['durum'] ?></span>
                                        </td>
                                        <td>
                                            <a href="tur_detay.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-info">Detay</a>
                                            <a href="tur_duzenle.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                            <?php if ($tur['durum'] == 'Planlandı'): ?>
                                                <a href="tur_baslat.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-success">Başlat</a>
                                            <?php elseif ($tur['durum'] == 'Yolda'): ?>
                                                <a href="tur_tamamla.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-primary">Tamamla</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">Kayıtlı tur bulunamadı.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>