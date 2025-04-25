<?php
require_once 'config.php';

// Araçları getir
$stmt = $db->query("SELECT * FROM araclar ORDER BY plaka");
$araclar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Araçlar - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Araçlar</h1>
            <div>
                <a href="arac_ekle.php" class="btn btn-primary">Yeni Araç Ekle</a>
                <a href="index.php" class="btn btn-secondary">Ana Sayfa</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Plaka</th>
                                <th>Model</th>
                                <th>Kapasite</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($araclar) > 0): ?>
                                <?php foreach ($araclar as $arac): ?>
                                    <tr>
                                        <td><?= $arac['arac_id'] ?></td>
                                        <td><?= $arac['plaka'] ?></td>
                                        <td><?= $arac['model'] ?></td>
                                        <td><?= $arac['kapasite'] ?> kg</td>
                                        <td>
                                            <span class="badge <?= $arac['durum'] == 'Aktif' ? 'bg-success' : 'bg-warning' ?>">
                                                <?= $arac['durum'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="arac_duzenle.php?id=<?= $arac['arac_id'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                            <a href="arac_sil.php?id=<?= $arac['arac_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu aracı silmek istediğinize emin misiniz?')">Sil</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Kayıtlı araç bulunamadı.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>