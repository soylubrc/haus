<?php
require_once 'config.php';

$mesaj = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tip_adi = $_POST['tip_adi'] ?? '';
    $aciklama = $_POST['aciklama'] ?? '';
    
    if (empty($tip_adi)) {
        $hata = "Yük tipi adı zorunludur!";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO yuk_tipleri (tip_adi, aciklama) VALUES (?, ?)");
            $stmt->execute([$tip_adi, $aciklama]);
            
            $mesaj = "Yük tipi başarıyla eklendi!";
            
            // Formu temizle
            $tip_adi = '';
            $aciklama = '';
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
    <title>Yük Tipi Ekle - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Yük Tipi Ekle</h1>
            <div>
                <a href="yuk_tipleri.php" class="btn btn-secondary">Yük Tipleri</a>
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
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="tip_adi" class="form-label">Yük Tipi Adı</label>
                        <input type="text" class="form-control" id="tip_adi" name="tip_adi" value="<?= $tip_adi ?? '' ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="aciklama" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?= $aciklama ?? '' ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>