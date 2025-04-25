<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: iller.php');
    exit;
}

$il_id = $_GET['id'];
$mesaj = '';
$hata = '';

// İl bilgilerini getir
$stmt = $db->prepare("SELECT * FROM iller WHERE il_id = ?");
$stmt->execute([$il_id]);
$il = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$il) {
    header('Location: iller.php');
    exit;
}

// İlin kullanımda olup olmadığını kontrol et
$stmt = $db->prepare("SELECT COUNT(*) FROM tur_duraklar WHERE il_id = ?");
$stmt->execute([$il_id]);
$kullanim_sayisi = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($kullanim_sayisi > 0) {
        $hata = "Bu il bir veya daha fazla turda kullanıldığı için silinemez!";
    } else {
        try {
            $stmt = $db->prepare("DELETE FROM iller WHERE il_id = ?");
            $stmt->execute([$il_id]);
            
            $mesaj = "İl başarıyla silindi!";
            
            // 2 saniye bekleyip iller sayfasına yönlendir
            header("refresh:2;url=iller.php");
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
    <title>İl Sil - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>İl Sil</h1>
            <div>
                <a href="iller.php" class="btn btn-secondary">İller</a>
                <a href="index.php" class="btn btn-primary">Ana Sayfa</a>
            </div>
        </div>
        
        <?php if ($mesaj): ?>
            <div class="alert alert-success">
                <p><?= $mesaj ?></p>
                <p>İller sayfasına yönlendiriliyorsunuz...</p>
            </div>
        <?php elseif ($hata): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">İli silmek istediğinize emin misiniz?</h5>
                    <p><strong>İl Adı:</strong> <?= $il['il_adi'] ?></p>
                    <p><strong>Merkeze Uzaklık:</strong> <?= $il['merkeze_uzaklik'] ?> km</p>
                    
                    <?php if ($kullanim_sayisi > 0): ?>
                        <div class="alert alert-warning">
                            <strong>Uyarı!</strong> Bu il <?= $kullanim_sayisi ?> turda kullanılıyor. Silmek için önce bu turları güncellemeniz gerekiyor.
                        </div>
                    <?php else: ?>
                        <form method="post">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-danger">Evet, Sil</button>
                                <a href="iller.php" class="btn btn-secondary">İptal</a>
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