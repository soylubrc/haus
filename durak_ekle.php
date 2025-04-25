<?php
require_once 'config.php';

if (!isset($_GET['tur_id']) || !is_numeric($_GET['tur_id'])) {
    header('Location: turlar.php');
    exit;
}

$tur_id = $_GET['tur_id'];
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

// İlleri getir
$stmt = $db->query("SELECT * FROM iller ORDER BY il_adi");
$iller = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Yük tiplerini getir
$stmt = $db->query("SELECT * FROM yuk_tipleri ORDER BY tip_adi");
$yuk_tipleri = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mevcut durakların maksimum sırasını bul
$stmt = $db->prepare("SELECT MAX(sira) FROM tur_duraklar WHERE tur_id = ?");
$stmt->execute([$tur_id]);
$max_sira = $stmt->fetchColumn();
$yeni_sira = $max_sira ? $max_sira + 1 : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $il_id = $_POST['il_id'] ?? null;
    $adres = $_POST['adres'] ?? '';
    $yuk_tip_id = $_POST['yuk_tip_id'] ?? null;
    $yuk_miktari = $_POST['yuk_miktari'] ?? 0;
    $sira = $_POST['sira'] ?? $yeni_sira;
    $teslim_durumu = $_POST['teslim_durumu'] ?? 'Bekleniyor';
    
    if (empty($il_id) || empty($adres) || empty($yuk_tip_id) || !is_numeric($yuk_miktari)) {
        $hata = "İl, adres, yük tipi ve yük miktarı alanları zorunludur!";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO tur_duraklar (tur_id, il_id, adres, yuk_tip_id, yuk_miktari, sira, teslim_durumu) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tur_id, $il_id, $adres, $yuk_tip_id, $yuk_miktari, $sira, $teslim_durumu]);
            
            $mesaj = "Durak başarıyla eklendi!";
            
            // Tur düzenleme sayfasına yönlendir
            header("refresh:2;url=tur_duzenle.php?id=" . $tur_id);
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
    <title>Durak Ekle - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Durak Ekle</h1>
            <div>
                <a href="tur_duzenle.php?id=<?= $tur_id ?>" class="btn btn-secondary">Tur Düzenle</a>
                <a href="turlar.php" class="btn btn-primary">Turlar</a>
            </div>
        </div>
        
        <?php if ($mesaj): ?>
            <div class="alert alert-success">
                <p><?= $mesaj ?></p>
                <p>Tur düzenleme sayfasına yönlendiriliyorsunuz...</p>
            </div>
        <?php endif; ?>
        
        <?php if ($hata): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="il_id" class="form-label">İl</label>
                        <select class="form-select" id="il_id" name="il_id" required>
                            <option value="">İl Seçin</option>
                            <?php foreach ($iller as $il): ?>
                                <option value="<?= $il['il_id'] ?>"><?= $il['il_adi'] ?> (<?= $il['merkeze_uzaklik'] ?> km)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adres" class="form-label">Adres</label>
                        <textarea class="form-control" id="adres" name="adres" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="yuk_tip_id" class="form-label">Yük Tipi</label>
                        <select class="form-select" id="yuk_tip_id" name="yuk_tip_id" required>
                            <option value="">Yük Tipi Seçin</option>
                            <?php foreach ($yuk_tipleri as $yuk_tipi): ?>
                                <option value="<?= $yuk_tipi['yuk_tip_id'] ?>"><?= $yuk_tipi['tip_adi'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="yuk_miktari" class="form-label">Yük Miktarı (kg)</label>
                        <input type="number" class="form-control" id="yuk_miktari" name="yuk_miktari" step="0.1" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sira" class="form-label">Sıra</label>
                        <input type="number" class="form-control" id="sira" name="sira" value="<?= $yeni_sira ?>" min="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="teslim_durumu" class="form-label">Teslim Durumu</label>
                        <select class="form-select" id="teslim_durumu" name="teslim_durumu" required>
                            <option value="Bekleniyor">Bekleniyor</option>
                            <option value="Teslim Edildi">Teslim Edildi</option>
                            <option value="İptal Edildi">İptal Edildi</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Durak Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

