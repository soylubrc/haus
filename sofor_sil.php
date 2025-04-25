<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: soforler.php');
    exit;
}

$sofor_id = $_GET['id'];
$mesaj = '';
$hata = '';

// Şoför bilgilerini getir
$stmt = $db->prepare("SELECT * FROM soforler WHERE sofor_id = ?");
$stmt->execute([$sofor_id]);
$sofor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sofor) {
    header('Location: soforler.php');
    exit;
}

// Şoförün kullanımda olup olmadığını kontrol et
$stmt = $db->prepare("SELECT COUNT(*) FROM turlar WHERE sofor_id = ?");
$stmt->execute([$sofor_id]);
$kullanim_sayisi = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($kullanim_sayisi > 0) {
        $hata = "Bu şoför bir veya daha fazla turda kullanıldığı için silinemez!";
    } else {
        try {
            $stmt = $db->prepare("DELETE FROM soforler WHERE sofor_id = ?");
            $stmt->execute([$sofor_id]);
            
            $mesaj = "Şoför başarıyla silindi!";
            
            // 2 saniye bekleyip şoförler sayfasına yönlendir
            header("refresh:2;url=soforler.php");
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
    <title>Şoför Sil - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Şoför Sil</h1>
            <div>
                <a href="soforler.php" class="btn btn-secondary">Şoförler</a>
                <a href="index.php" class="btn btn-primary">Ana Sayfa</a>
            </div>
        </div>
        
        <?php if ($mesaj): ?>
            <div class="alert alert-success">
                <p><?= $mesaj ?></p>
                <p>Şoförler sayfasına yönlendiriliyorsunuz...</p>
            </div>
        <?php elseif ($hata): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Şoförü silmek istediğinize emin misiniz?</h5>
                    <p><strong>Ad Soyad:</strong> <?= $sofor['ad'] . ' ' . $sofor['soyad'] ?></p>
                    <p><strong>Telefon:</strong> <?= $sofor['telefon'] ?></p>
                    <p><strong>Ehliyet No:</strong> <?= $sofor['ehliyet_no'] ?></p>
                    <p><strong>Durum:</strong> <?= $sofor['durum'] ?></p>
                    
                    <?php if ($kullanim_sayisi > 0): ?>
                        <div class="alert alert-warning">
                            <strong>Uyarı!</strong> Bu şoför <?= $kullanim_sayisi ?> turda kullanılıyor. Silmek için önce bu turları güncellemeniz gerekiyor.
                        </div>
                    <?php else: ?>
                        <form method="post">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger">Evet, Sil</button>
                                <a href="soforler.php" class="btn btn-secondary">İptal</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>