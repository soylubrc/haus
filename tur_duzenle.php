<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: turlar.php');
    exit;
}

$tur_id = $_GET['id'];
$mesaj = '';
$hata = '';

// Tur bilgilerini getir
$stmt = $db->prepare("SELECT * FROM turlar WHERE tur_id = ?");
$stmt->execute([$tur_id]);
$tur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tur) {
    header('Location: turlar.php');
    exit;
}

// Şoförleri getir
$stmt = $db->query("SELECT * FROM soforler ORDER BY ad, soyad");
$soforler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Araçları getir
$stmt = $db->query("SELECT * FROM araclar ORDER BY plaka");
$araclar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Durakları getir
$stmt = $db->prepare("SELECT td.*, i.il_adi, yt.tip_adi 
                     FROM tur_duraklar td
                     LEFT JOIN iller i ON td.il_id = i.il_id
                     LEFT JOIN yuk_tipleri yt ON td.yuk_tip_id = yt.yuk_tip_id
                     WHERE td.tur_id = ?
                     ORDER BY td.sira");
$stmt->execute([$tur_id]);
$duraklar = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sofor_id = $_POST['sofor_id'] ?? null;
    $arac_id = $_POST['arac_id'] ?? null;
    $cikis_tarihi = $_POST['cikis_tarihi'] ?? '';
    $cikis_saati = $_POST['cikis_saati'] ?? '';
    $tahmini_donus_tarihi = $_POST['tahmini_donus_tarihi'] ?? null;
    $tahmini_donus_saati = $_POST['tahmini_donus_saati'] ?? null;
    $toplam_mesafe = $_POST['toplam_mesafe'] ?? null;
    $durum = $_POST['durum'] ?? 'Planlandı';
    
    if (empty($cikis_tarihi) || empty($cikis_saati)) {
        $hata = "Çıkış tarihi ve saati zorunludur!";
    } else {
        try {
            $stmt = $db->prepare("UPDATE turlar SET 
                                 sofor_id = ?, 
                                 arac_id = ?, 
                                 cikis_tarihi = ?, 
                                 cikis_saati = ?, 
                                 tahmini_donus_tarihi = ?, 
                                 tahmini_donus_saati = ?, 
                                 toplam_mesafe = ?, 
                                 durum = ? 
                                 WHERE tur_id = ?");
            $stmt->execute([
                $sofor_id, 
                $arac_id, 
                $cikis_tarihi, 
                $cikis_saati, 
                $tahmini_donus_tarihi, 
                $tahmini_donus_saati, 
                $toplam_mesafe, 
                $durum, 
                $tur_id
            ]);
            
            $mesaj = "Tur başarıyla güncellendi!";
            
            // Güncel bilgileri getir
            $stmt = $db->prepare("SELECT * FROM turlar WHERE tur_id = ?");
            $stmt->execute([$tur_id]);
            $tur = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $hata = "Hata oluştu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tur Düzenle - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Tur Düzenle</h1>
            <div>
                <a href="tur_detay.php?id=<?= $tur_id ?>" class="btn btn-info">Tur Detayı</a>
                <a href="turlar.php" class="btn btn-secondary">Turlar</a>
                <a href="index.php" class="btn btn-primary">Ana Sayfa</a>
            </div>
        </div>
        
        <?php if ($mesaj): ?>
            <div class="alert alert-success"><?= $mesaj ?></div>
        <?php endif; ?>
        
        <?php if ($hata): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sofor_id" class="form-label">Şoför</label>
                            <select class="form-select" id="sofor_id" name="sofor_id">
                                <option value="">Şoför Seçin</option>
                                <?php foreach ($soforler as $sofor): ?>
                                    <option value="<?= $sofor['sofor_id'] ?>" <?= $tur['sofor_id'] == $sofor['sofor_id'] ? 'selected' : '' ?>>
                                        <?= $sofor['ad'] . ' ' . $sofor['soyad'] ?> (<?= $sofor['durum'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="arac_id" class="form-label">Araç</label>
                            <select class="form-select" id="arac_id" name="arac_id">
                                <option value="">Araç Seçin</option>
                                <?php foreach ($araclar as $arac): ?>
                                    <option value="<?= $arac['arac_id'] ?>" <?= $tur['arac_id'] == $arac['arac_id'] ? 'selected' : '' ?>>
                                        <?= $arac['plaka'] ?> (<?= $arac['model'] ?>, <?= $arac['kapasite'] ?> kg, <?= $arac['durum'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="cikis_tarihi" class="form-label">Çıkış Tarihi</label>
                            <input type="date" class="form-control" id="cikis_tarihi" name="cikis_tarihi" value="<?= $tur['cikis_tarihi'] ?>" required>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="cikis_saati" class="form-label">Çıkış Saati</label>
                            <input type="time" class="form-control" id="cikis_saati" name="cikis_saati" value="<?= $tur['cikis_saati'] ?>" required>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="tahmini_donus_tarihi" class="form-label">Tahmini Dönüş Tarihi</label>
                            <input type="date" class="form-control" id="tahmini_donus_tarihi" name="tahmini_donus_tarihi" value="<?= $tur['tahmini_donus_tarihi'] ?>">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label for="tahmini_donus_saati" class="form-label">Tahmini Dönüş Saati</label>
                            <input type="time" class="form-control" id="tahmini_donus_saati" name="tahmini_donus_saati" value="<?= $tur['tahmini_donus_saati'] ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="toplam_mesafe" class="form-label">Toplam Mesafe (km)</label>
                            <input type="number" class="form-control" id="toplam_mesafe" name="toplam_mesafe" value="<?= $tur['toplam_mesafe'] ?>" min="0">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="durum" class="form-label">Durum</label>
                            <select class="form-select" id="durum" name="durum" required>
                                <option value="Planlandı" <?= $tur['durum'] == 'Planlandı' ? 'selected' : '' ?>>Planlandı</option>
                                <option value="Yolda" <?= $tur['durum'] == 'Yolda' ? 'selected' : '' ?>>Yolda</option>
 <option value="Tamamlandı" <?= $tur['durum'] == 'Tamamlandı' ? 'selected' : '' ?>>Tamamlandı</option>
                                <option value="İptal Edildi" <?= $tur['durum'] == 'İptal Edildi' ? 'selected' : '' ?>>İptal Edildi</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Duraklar</h5>
                <a href="durak_ekle.php?tur_id=<?= $tur_id ?>" class="btn btn-sm btn-primary">Yeni Durak Ekle</a>
            </div>
            <div class="card-body">
                <?php if (count($duraklar) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Sıra</th>
                                    <th>İl</th>
                                    <th>Yük Tipi</th>
                                    <th>Yük Miktarı</th>
                                    <th>Teslim Durumu</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($duraklar as $durak): ?>
                                    <tr>
                                        <td><?= $durak['sira'] ?></td>
                                        <td><?= $durak['il_adi'] ?></td>
                                        <td><?= $durak['tip_adi'] ?></td>
                                        <td><?= $durak['yuk_miktari'] ?> kg</td>
                                        <td>
                                            <?php
                                            $durum_class = '';
                                            switch ($durak['teslim_durumu']) {
                                                case 'Bekleniyor':
                                                    $durum_class = 'bg-warning';
                                                    break;
                                                case 'Teslim Edildi':
                                                    $durum_class = 'bg-success';
                                                    break;
                                                case 'İptal Edildi':
                                                    $durum_class = 'bg-danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?= $durum_class ?>"><?= $durak['teslim_durumu'] ?></span>
                                        </td>
                                        <td>
                                            <a href="durak_duzenle.php?id=<?= $durak['durak_id'] ?>&tur_id=<?= $tur_id ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                            <a href="durak_sil.php?id=<?= $durak['durak_id'] ?>&tur_id=<?= $tur_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu durağı silmek istediğinize emin misiniz?')">Sil</a>
                                            <?php if ($durak['teslim_durumu'] == 'Bekleniyor' && $tur['durum'] == 'Yolda'): ?>
                                                <a href="durak_teslim.php?id=<?= $durak['durak_id'] ?>&tur_id=<?= $tur_id ?>" class="btn btn-sm btn-success">Teslim Et</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Bu tura henüz durak eklenmemiş.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>