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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $il_adi = $_POST['il_adi'] ?? '';
    $merkeze_uzaklik = $_POST['merkeze_uzaklik'] ?? '';
    
    if (empty($il_adi) || !is_numeric($merkeze_uzaklik)) {
        $hata = "İl adı ve merkeze uzaklık (km) alanları zorunludur!";
    } else {
        try {
            $stmt = $db->prepare("UPDATE iller SET il_adi = ?, merkeze_uzaklik = ? WHERE il_id = ?");
            $stmt->execute([$il_adi, $merkeze_uzaklik, $il_id]);
            
            $mesaj = "İl başarıyla güncellendi!";
            
            // Güncel bilgileri getir
            $stmt = $db->prepare("SELECT * FROM iller WHERE il_id = ?");
            $stmt->execute([$il_id]);
            $il = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <title>İl Düzenle - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>İl Düzenle</h1>
            <div>
                <a href="iller.php" class="btn btn-secondary">İller</a>
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
                        <label for="il_adi" class="form-label">İl Adı</label>
                        <input type="text" class="form-control" id="il_adi" name="il_adi" value="<?= $il['il_adi'] ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="merkeze_uzaklik" class="form-label">Merkeze Uzaklık (km)</label>
                        <input type="number" class="form-control" id="merkeze_uzaklik" name="merkeze_uzaklik" value="<?= $il['merkeze_uzaklik'] ?>" required min="0">
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