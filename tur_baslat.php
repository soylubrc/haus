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
$stmt = $db->prepare("SELECT t.*, 
                      CONCAT(s.ad, ' ', s.soyad) as sofor_adi, 
                      a.plaka 
                      FROM turlar t
                      LEFT JOIN soforler s ON t.sofor_id = s.sofor_id
                      LEFT JOIN araclar a ON t.arac_id = a.arac_id
                      WHERE t.tur_id = ?");
$stmt->execute([$tur_id]);
$tur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tur || $tur['durum'] != 'Planlandı') {
    header('Location: turlar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Turu başlat
        $stmt = $db->prepare("UPDATE turlar SET durum = 'Yolda' WHERE tur_id = ?");
        $stmt->execute([$tur_id]);
        
        // Şoför durumunu güncelle
        if ($tur['sofor_id']) {
            $stmt = $db->prepare("UPDATE soforler SET durum = 'Yolda' WHERE sofor_id = ?");
            $stmt->execute([$tur['sofor_id']]);
        }
        
        // Araç durumunu güncelle
        if ($tur['arac_id']) {
            $stmt = $db->prepare("UPDATE araclar SET durum = 'Yolda' WHERE arac_id = ?");
            $stmt->execute([$tur['arac_id']]);
        }
        
        $mesaj = "Tur başarıyla başlatıldı!";
        
        // 2 saniye bekleyip turlar sayfasına yönlendir
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
    <title>Tur Başlat - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Tur Başlat</h1>
            <div>
                <a href="turlar.php" class="btn btn-secondary">Turlar</a>
                <a href="index.php" class="btn btn-primary">Ana Sayfa</a>
            </div>
        </div>
        
        <?php if ($mesaj): ?>
            <div class="alert alert-success">
                <p><?= $mesaj ?></p>
                <p>Turlar sayfasına yönlendiriliyorsunuz...</p>
            </div>
        <?php elseif ($hata): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Turu başlatmak istediğinize emin misiniz?</h5>
                    <p><strong>Tur ID:</strong> <?= $tur['tur_id'] ?></p>
                    <p><strong>Şoför:</strong> <?= $tur['sofor_adi'] ?: 'Belirtilmemiş' ?></p>
                    <p><strong>Araç:</strong> <?= $tur['plaka'] ?: 'Belirtilmemiş' ?></p>
                    <p><strong>Çıkış Tarihi:</strong> <?= formatTarih($tur['cikis_tarihi']) ?></p>
                    <p><strong>Çıkış Saati:</strong> <?= formatSaat($tur['cikis_saati']) ?></p>
                    
                    <div class="alert alert-info">
                        <strong>Not:</strong> Turu başlattığınızda, şoför ve araç durumu "Yolda" olarak güncellenecektir.
                    </div>
                    
                    <form method="post">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Evet, Başlat</button>
                            <a href="turlar.php" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>