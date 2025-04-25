<?php
require_once 'config.php';

$mesaj = '';
$hata = '';

// Tur ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: turlar.php');
    exit;
}

$tur_id = $_GET['id'];

// Tur bilgilerini getir
$stmt = $db->prepare("
    SELECT t.*, s.ad AS sofor_adi, s.soyad AS sofor_soyadi, a.plaka 
    FROM turlar t
    LEFT JOIN soforler s ON t.sofor_id = s.sofor_id
    LEFT JOIN araclar a ON t.arac_id = a.arac_id
    WHERE t.tur_id = ?
");
$stmt->execute([$tur_id]);
$tur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tur) {
    header('Location: turlar.php');
    exit;
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paket_durumu = $_POST['paket_durumu'];
    
    try {
        $stmt = $db->prepare("UPDATE turlar SET paket_durumu = ? WHERE tur_id = ?");
        $stmt->execute([$paket_durumu, $tur_id]);
        
        $mesaj = "Tur paketi durumu başarıyla güncellendi!";
        header("refresh:2;url=turlar.php");
    } catch (Exception $e) {
        $hata = "Hata oluştu: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tur Paketi Durumu - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Tur Paketi Durumu Güncelle</h1>
            <div>
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
            <div class="card-header">
                <h5>Tur Bilgileri</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Şoför:</strong> <?= htmlspecialchars($tur['sofor_adi'] . ' ' . $tur['sofor_soyadi']) ?></p>
                        <p><strong>Araç Plakası:</strong> <?= htmlspecialchars($tur['plaka']) ?></p>
                        <p><strong>Çıkış Tarihi:</strong> <?= htmlspecialchars($tur['cikis_tarihi']) ?> <?= htmlspecialchars($tur['cikis_saati']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Durum:</strong> <?= htmlspecialchars($tur['durum']) ?></p>
                        <p><strong>Mevcut Paket Durumu:</strong> <?= htmlspecialchars($tur['paket_durumu']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Paket Durumunu Güncelle</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="paket_durumu" class="form-label">Paket Durumu</label>
                        <select class="form-select" id="paket_durumu" name="paket_durumu" required>
                            <option value="Hazırlanıyor" <?= $tur['paket_durumu'] == 'Hazırlanıyor' ? 'selected' : '' ?>>Hazırlanıyor</option>
                            <option value="Hazır" <?= $tur['paket_durumu'] == 'Hazır' ? 'selected' : '' ?>>Hazır</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>