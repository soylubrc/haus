<?php
require_once 'config.php';

// Filtreleme için parametreler
$durum = isset($_GET['durum']) ? $_GET['durum'] : '';
$sofor_id = isset($_GET['sofor_id']) ? $_GET['sofor_id'] : '';
$arac_id = isset($_GET['arac_id']) ? $_GET['arac_id'] : '';
$baslangic_tarihi = isset($_GET['baslangic_tarihi']) ? $_GET['baslangic_tarihi'] : '';
$bitis_tarihi = isset($_GET['bitis_tarihi']) ? $_GET['bitis_tarihi'] : '';
$paket_durumu = isset($_GET['paket_durumu']) ? $_GET['paket_durumu'] : '';

// Şoförleri getir
$stmt = $db->query("SELECT sofor_id, CONCAT(ad, ' ', soyad) AS tam_ad FROM soforler ORDER BY ad, soyad");
$soforler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Araçları getir
$stmt = $db->query("SELECT arac_id, plaka FROM araclar ORDER BY plaka");
$araclar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Turları getir
$query = "
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
        1=1
";

$params = [];

if (!empty($durum)) {
    $query .= " AND t.durum = ?";
    $params[] = $durum;
}

if (!empty($sofor_id)) {
    $query .= " AND t.sofor_id = ?";
    $params[] = $sofor_id;
}

if (!empty($arac_id)) {
    $query .= " AND t.arac_id = ?";
    $params[] = $arac_id;
}

if (!empty($baslangic_tarihi)) {
    $query .= " AND t.cikis_tarihi >= ?";
    $params[] = $baslangic_tarihi;
}

if (!empty($bitis_tarihi)) {
    $query .= " AND t.cikis_tarihi <= ?";
    $params[] = $bitis_tarihi;
}

if (!empty($paket_durumu)) {
    $query .= " AND t.paket_durumu = ?";
    $params[] = $paket_durumu;
}

$query .= " ORDER BY t.cikis_tarihi DESC, t.cikis_saati DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$turlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turlar - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Turlar</h1>
            <div>
                <a href="tur_ekle.php" class="btn btn-success">Yeni Tur Ekle</a>
                <a href="index.php" class="btn btn-primary">Ana Sayfa</a>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Filtrele</h5>
            </div>
            <div class="card-body">
                <form method="get" action="">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="durum" class="form-label">Durum</label>
                            <select class="form-select" id="durum" name="durum">
                                <option value="">Tümü</option>
                                <option value="Planlandı" <?= $durum === 'Planlandı' ? 'selected' : '' ?>>Planlandı</option>
                                <option value="Yolda" <?= $durum === 'Yolda' ? 'selected' : '' ?>>Yolda</option>
                                <option value="Tamamlandı" <?= $durum === 'Tamamlandı' ? 'selected' : '' ?>>Tamamlandı</option>
                                <option value="İptal Edildi" <?= $durum === 'İptal Edildi' ? 'selected' : '' ?>>İptal Edildi</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="paket_durumu" class="form-label">Paket Durumu</label>
                            <select class="form-select" id="paket_durumu" name="paket_durumu">
                                <option value="">Tümü</option>
                                <option value="Hazırlanıyor" <?= $paket_durumu === 'Hazırlanıyor' ? 'selected' : '' ?>>Hazırlanıyor</option>
                                <option value="Hazır" <?= $paket_durumu === 'Hazır' ? 'selected' : '' ?>>Hazır</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sofor_id" class="form-label">Şoför</label>
                            <select class="form-select" id="sofor_id" name="sofor_id">
                                <option value="">Tümü</option>
                                <?php foreach ($soforler as $sofor): ?>
                                    <option value="<?= $sofor['sofor_id'] ?>" <?= $sofor_id == $sofor['sofor_id'] ? 'selected' : '' ?>><?= htmlspecialchars($sofor['tam_ad']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="arac_id" class="form-label">Araç</label>
                            <select class="form-select" id="arac_id" name="arac_id">
                                <option value="">Tümü</option>
                                <?php foreach ($araclar as $arac): ?>
                                    <option value="<?= $arac['arac_id'] ?>" <?= $arac_id == $arac['arac_id'] ? 'selected' : '' ?>><?= htmlspecialchars($arac['plaka']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="baslangic_tarihi" class="form-label">Başlangıç Tarihi</label>
                            <input type="date" class="form-control" id="baslangic_tarihi" name="baslangic_tarihi" value="<?= $baslangic_tarihi ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="bitis_tarihi" class="form-label">Bitiş Tarihi</label>
                            <input type="date" class="form-control" id="bitis_tarihi" name="bitis_tarihi" value="<?= $bitis_tarihi ?>">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filtrele</button>
                        <a href="turlar.php" class="btn btn-secondary">Sıfırla</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Tur Listesi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>                                <th>ID</th>
                                <th>Çıkış Tarihi</th>
                                <th>Şoför</th>
                                <th>Araç</th>
                                <th>Durum</th>
                                <th>Paket Durumu</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($turlar) > 0): ?>
                                <?php foreach ($turlar as $tur): ?>
                                    <tr>
                                        <td><?= $tur['tur_id'] ?></td>
                                        <td><?= date('d.m.Y H:i', strtotime($tur['cikis_tarihi'] . ' ' . $tur['cikis_saati'])) ?></td>
                                        <td><?= htmlspecialchars($tur['sofor_adi'] . ' ' . $tur['sofor_soyadi']) ?></td>
                                        <td><?= htmlspecialchars($tur['plaka']) ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                switch ($tur['durum']) {
                                                    case 'Planlandı': echo 'secondary'; break;
                                                    case 'Yolda': echo 'primary'; break;
                                                    case 'Tamamlandı': echo 'success'; break;
                                                    case 'İptal Edildi': echo 'danger'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                                <?= $tur['durum'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $tur['paket_durumu'] === 'Hazır' ? 'success' : 'warning' ?>">
                                                <?= $tur['paket_durumu'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="tur_detay.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-info">Detay</a>
                                                <a href="tur_duzenle.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-primary">Düzenle</a>
                                                <a href="tur_paket_durum.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-warning">Paket Durumu</a>
                                                <a href="tur_sil.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-danger">Sil</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Kayıtlı tur bulunamadı.</td>
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