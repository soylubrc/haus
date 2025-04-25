<?php
require_once 'config.php';

// İlleri getir
$stmt = $db->query("SELECT * FROM iller ORDER BY il_adi");
$iller = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İller - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>İller</h1>
            <div>
                <a href="il_ekle.php" class="btn btn-primary">Yeni İl Ekle</a>
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
                                <th>İl Adı</th>
                                <th>Merkeze Uzaklık</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($iller) > 0): ?>
                                <?php foreach ($iller as $il): ?>
                                    <tr>
                                        <td><?= $il['il_id'] ?></td>
                                        <td><?= $il['il_adi'] ?></td>
                                        <td><?= $il['merkeze_uzaklik'] ?> km</td>
                                        <td>
                                            <a href="il_duzenle.php?id=<?= $il['il_id'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                            <a href="il_sil.php?id=<?= $il['il_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu ili silmek istediğinize emin misiniz?')">Sil</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Kayıtlı il bulunamadı.</td>
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