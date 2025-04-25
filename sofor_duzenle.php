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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = $_POST['ad'] ?? '';
    $soyad = $_POST['soyad'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $ehliyet_no = $_POST['ehliyet_no'] ?? '';
    $durum = $_POST['durum'] ?? '';
    
    if (empty($ad) || empty($soyad)) {
        $hata = "Ad ve soyad alanları zorunludur!";
    } else {
        try {
            $stmt = $db->prepare("UPDATE soforler SET ad = ?, soyad = ?, telefon = ?, ehliyet_no = ?, durum = ? WHERE sofor_id = ?");
            $stmt->execute([$ad, $soyad, $telefon, $ehliyet_no, $durum, $sofor_id]);
            
            $mesaj = "Şoför başarıyla güncellendi!";
            
            // Güncel bilgileri getir
            $stmt = $db->prepare("SELECT * FROM soforler WHERE sofor_id = ?");
            $stmt->execute([$sofor_id]);
            $sofor = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>Şoför Düzenle - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Şoför Düzenle</h1>
            <div>
                <a href="soforler.php" class="btn btn-secondary">Şoförler</a>
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
                        <label for="ad" class="form-label">Ad</label>
                        <input type="text" class="form-control" id="ad" name="ad" value="<?= $sofor['ad'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="soyad" class="form-label">Soyad</label>
                        <input type="text" class="form-control" id="soyad" name="soyad" value="<?= $sofor['soyad'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefon" class="form-label">Telefon</label>
                        <input type="tel" class="form-control" id="telefon" name="telefon" value="<?= $sofor['telefon'] ?>" placeholder="05XX XXX XX XX">
                    </div>
                    
                    <div class="mb-3">
                        <label for="ehliyet_no" class="form-label">Ehliyet No</label>
                        <input type="text" class="form-control" id="ehliyet_no" name="ehliyet_no" value="<?= $sofor['ehliyet_no'] ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="durum" class="form-label">Durum</label>
                        <select class="form-select" id="durum" name="durum" required>
                            <option value="Aktif" <?= $sofor['durum'] == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="İzinli" <?= $sofor['durum'] == 'İzinli' ? 'selected' : '' ?>>İzinli</option>
                            <option value="Yolda" <?= $sofor['durum'] == 'Yolda' ? 'selected' : '' ?>>Yolda</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
