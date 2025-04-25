<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: turlar.php');
    exit;
}

$tur_id = $_GET['id'];

// Tur bilgilerini getir
$stmt = $db->prepare("SELECT t.*, 
                      CONCAT(s.ad, ' ', s.soyad) as sofor_adi, 
                      s.telefon as sofor_telefon,
                      a.plaka, a.model as arac_model
                      FROM turlar t
                      LEFT JOIN soforler s ON t.sofor_id = s.sofor_id
                      LEFT JOIN araclar a ON t.arac_id = a.arac_id
                      WHERE t.tur_id = ?");
$stmt->execute([$tur_id]);
$tur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tur) {
    header('Location: turlar.php');
    exit;
}

// Tur duraklarını getir
$stmt = $db->prepare("SELECT td.*, i.il_adi, yt.tip_adi as yuk_tipi
                      FROM tur_duraklar td
                      LEFT JOIN iller i ON td.il_id = i.il_id
                      LEFT JOIN yuk_tipleri yt ON td.yuk_tip_id = yt.yuk_tip_id
                      WHERE td.tur_id = ?
                      ORDER BY td.sira");
$stmt->execute([$tur_id]);
$duraklar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tur Detayı - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Tur Detayı #<?= $tur['tur_id'] ?></h1>
            <div>
                <a href="turlar.php" class="btn btn-secondary">Turlar</a>
                <a href="index.php" class="btn btn-primary">Ana Sayfa</a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tur Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%">Durum</th>
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
                            </tr>
                            <tr>
                                <th>Şoför</th>
                                <td><?= $tur['sofor_adi'] ?> (Tel: <?= $tur['sofor_telefon'] ?>)</td>
                            </tr>
                            <tr>
                                <th>Araç</th>
                                <td><?= $tur['plaka'] ?> - <?= $tur['arac_model'] ?></td>
                            </tr>
                            <tr>
                                <th>Çıkış Tarihi/Saati</th>
                                <td><?= formatTarih($tur['cikis_tarihi']) ?> <?= formatSaat($tur['cikis_saati']) ?></td>
                            </tr>
                            <tr>
                                <th>Tahmini Dönüş</th>
                                <td><?= formatTarih($tur['tahmini_donus_tarihi']) ?> <?= formatSaat($tur['tahmini_donus_saati']) ?></td>
                            </tr>
                            <?php if ($tur['gercek_donus_tarihi']): ?>
                            <tr>
                                <th>Gerçek Dönüş</th>
                                <td><?= formatTarih($tur['gercek_donus_tarihi']) ?> <?= formatSaat($tur['gercek_donus_saati']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Toplam Mesafe</th>
                                <td><?= $tur['toplam_mesafe'] ?> km</td>
                            </tr>
                            <tr>
                                <th>Oluşturulma Zamanı</th>
                                <td><?= date("d.m.Y H:i", strtotime($tur['olusturma_zamani'])) ?></td>
                            </tr>
                        </table>
                        
                        <div class="mt-3">
                            <?php if ($tur['durum'] == 'Planlandı'): ?>
                                <a href="tur_baslat.php?id=<?= $tur['tur_id'] ?>" class="btn btn-success">Turu Başlat</a>
                            <?php elseif ($tur['durum'] == 'Yolda'): ?>
                                <a href="tur_tamamla.php?id=<?= $tur['tur_id'] ?>" class="btn btn-primary">Turu Tamamla</a>
                            <?php endif; ?>
                            
                            <?php if ($tur['durum'] != 'Tamamlandı' && $tur['durum'] != 'İptal Edildi'): ?>
                                <a href="tur_duzenle.php?id=<?= $tur['tur_id'] ?>" class="btn btn-warning">Düzenle</a>
                                <a href="tur_iptal.php?id=<?= $tur['tur_id'] ?>" class="btn btn-danger" onclick="return confirm('Bu turu iptal etmek istediğinize emin misiniz?')">İptal Et</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Duraklar</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($duraklar) > 0): ?>
                            <div class="list-group">
                                <?php foreach ($duraklar as $durak): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-1">Durak #<?= $durak['sira'] ?>: <?= $durak['il_adi'] ?></h6>
                                            <?php if ($tur['durum'] == 'Yolda'): ?>
                                                <?php if ($durak['teslim_durumu'] == 'Bekleniyor'): ?>
                                                    <a href="durak_teslim.php?id=<?= $durak['durak_id'] ?>&tur_id=<?= $tur_id ?>" class="btn btn-sm btn-success">Teslim Edildi</a>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Teslim Edildi</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge <?= $durak['teslim_durumu'] == 'Teslim Edildi' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $durak['teslim_durumu'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mb-1">Yük Tipi: <?= $durak['yuk_tipi'] ?></p>
                                        <p class="mb-0">Yük Miktarı: <?= $durak['yuk_miktari'] ?> kg</p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center">Bu tur için durak bilgisi bulunamadı.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>