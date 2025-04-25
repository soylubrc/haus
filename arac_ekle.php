<?php
require_once 'config.php';

$mesaj = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plaka = $_POST['plaka'] ?? '';
    $model = $_POST['model'] ?? '';
    $kapasite = $_POST['kapasite'] ?? '';
    
    if (empty($plaka) || empty($model)) {
        $hata = "Plaka ve model alanları zorunludur!";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO araclar (plaka, model, kapasite, durum) VALUES (?, ?, ?, 'Aktif')");
            $stmt->execute([$plaka, $model, $kapasite]);
            
            $mesaj = "Araç başarıyla eklendi!";
            
            // Formu temizle
            $plaka = $model = $kapasite = '';
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
    <title>Araç Ekle - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Yeni Araç Ekle</h1>
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
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="plaka" class="form-label">Plaka</label>
                        <input type="text" class="form-control" id="plaka" name="plaka" value="<?= $plaka ?? '' ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="model" class="form-label">Model</label>
                        <input type="text" class="form-control" id="model" name="model" value="<?= $model ?? '' ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="kapasite" class="form-label">Kapasite (kg)</label>
                        <input type="number" class="form-control" id="kapasite" name="kapasite" value="<?= $kapasite ?? '' ?>" step="0.1" min="0">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Araç Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>