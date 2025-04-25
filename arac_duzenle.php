<?php
require_once 'config.php';

$mesaj = '';
$hata = '';
$arac = null;

// Araç ID'sini al
$arac_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($arac_id <= 0) {
    header('Location: araclar.php');
    exit;
}

// Aracı veritabanından çek
try {
    $stmt = $db->prepare("SELECT * FROM araclar WHERE arac_id = ?");
    $stmt->execute([$arac_id]);
    $arac = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$arac) {
        header('Location: araclar.php');
        exit;
    }
} catch (Exception $e) {
    $hata = "Araç bilgileri alınırken hata oluştu: " . $e->getMessage();
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plaka = $_POST['plaka'] ?? '';
    $model = $_POST['model'] ?? '';
    $kapasite = $_POST['kapasite'] ?? '';
    $durum = $_POST['durum'] ?? 'Aktif';
    
    if (empty($plaka) || empty($model)) {
        $hata = "Plaka ve model alanları zorunludur!";
    } else {
        try {
            $stmt = $db->prepare("UPDATE araclar SET plaka = ?, model = ?, kapasite = ?, durum = ? WHERE arac_id = ?");
            $stmt->execute([$plaka, $model, $kapasite, $durum, $arac_id]);
            
            $mesaj = "Araç bilgileri başarıyla güncellendi!";
            
            // Güncel verileri tekrar çek
            $stmt = $db->prepare("SELECT * FROM araclar WHERE arac_id = ?");
            $stmt->execute([$arac_id]);
            $arac = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $hata = "Güncelleme sırasında hata oluştu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Araç Düzenle - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Araç Düzenle</h1>
            <div>
                <a href="araclar.php" class="btn btn-secondary">Araçlar</a>
                <a href="index.php" class="btn btn-primary">Ana Sayfa</a>
            </div>
        </div>
        
        <?php if ($mesaj): ?>
            <div class="alert alert-success"><?= $mesaj ?></div>
        <?php endif; ?>
        
        <?php if ($hata): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Araç #<?= $arac['arac_id'] ?> Bilgileri</h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="plaka" class="form-label">Plaka</label>
                        <input type="text" class="form-control" id="plaka" name="plaka" value="<?= htmlspecialchars($arac['plaka']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="model" class="form-label">Model</label>
                        <input type="text" class="form-control" id="model" name="model" value="<?= htmlspecialchars($arac['model']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="kapasite" class="form-label">Kapasite (kg)</label>
                        <input type="number" class="form-control" id="kapasite" name="kapasite" value="<?= htmlspecialchars($arac['kapasite']) ?>" step="0.1" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="durum" class="form-label">Durum</label>
                        <select class="form-select" id="durum" name="durum">
                            <option value="Aktif" <?= $arac['durum'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="Bakımda" <?= $arac['durum'] == 'Bakımda' ? 'selected' : '' ?>>Bakımda</option>
                            <option value="Pasif" <?= $arac['durum'] == 'Pasif' ? 'selected' : '' ?>>Pasif</option>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                        <a href="araclar.php" class="btn btn-secondary">İptal</a>
                    </div>
                </form>
            </div>
            <div class="card-footer text-muted">
                <small>Son güncelleme: <?= isset($arac['guncelleme_tarihi']) ? date('d.m.Y H:i', strtotime($arac['guncelleme_tarihi'])) : 'Belirtilmemiş' ?></small>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
