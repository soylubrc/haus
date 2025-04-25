<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['tur_id']) || !is_numeric($_GET['tur_id'])) {
    header('Location: turlar.php');
    exit;
}

$durak_id = $_GET['id'];
$tur_id = $_GET['tur_id'];
$mesaj = '';
$hata = '';

// Durak bilgilerini kontrol et
$stmt = $db->prepare("SELECT * FROM tur_duraklar WHERE durak_id = ? AND tur_id = ?");
$stmt->execute([$durak_id, $tur_id]);
$durak = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$durak) {
    header('Location: tur_detay.php?id=' . $tur_id);
    exit;
}

try {
    $db->beginTransaction();
    
    // Durak durumunu güncelle
    $stmt = $db->prepare("UPDATE tur_duraklar SET teslim_durumu = 'Teslim Edildi' WHERE durak_id = ?");
    $stmt->execute([$durak_id]);
    
    $db->commit();
    $mesaj = "Durak teslim edildi olarak işaretlendi!";
    
    // 2 saniye bekleyip tur detay sayfasına yönlendir
    header("refresh:2;url=tur_detay.php?id=" . $tur_id);
} catch (Exception $e) {
    $db->rollBack();
    $hata = "Hata oluştu: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Durak Teslim - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Durak Teslim</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($mesaj): ?>
                            <div class="alert alert-success">
                                <h5>İşlem Başarılı!</h5>
                                <p><?= $mesaj ?></p>
                                <p>Tur detay sayfasına yönlendiriliyorsunuz...</p>
                            </div>
                        <?php elseif ($hata): ?>
                            <div class="alert alert-danger">
                                <h5>Hata!</h5>
                                <p><?= $hata ?></p>
                                <div class="mt-3">
                                    <a href="tur_detay.php?id=<?= $tur_id ?>" class="btn btn-primary">Tur Detayına Dön</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

