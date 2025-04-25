<?php
require_once 'config.php';

// Şoförleri getir
$stmt = $db->query("SELECT * FROM soforler ORDER BY ad, soyad");
$soforler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şoförler - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Şoförler</h1>
            <div>
                <a href="sofor_ekle.php" class="btn btn-primary">Yeni Şoför Ekle</a>
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
                                <th>Ad Soyad</th>
                                <th>Telefon</th>
                                <th>Ehliyet No</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($soforler) > 0): ?>
                                <?php foreach ($soforler as $sofor): ?>
                                    <tr>
                                        <td><?= $sofor['sofor_id'] ?></td>
                                        <td><?= $sofor['ad'] . ' ' . $sofor['soyad'] ?></td>
                                        <td><?= $sofor['telefon'] ?></td>
                                        <td><?= $sofor['ehliyet_no'] ?></td>
                                        <td>
                                            <span class="badge <?= $sofor['durum'] == 'Aktif' ? 'bg-success' : 'bg-warning' ?>">
                                                <?= $sofor['durum'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="sofor_duzenle.php?id=<?= $sofor['sofor_id'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                            <a href="sofor_sil.php?id=<?= $sofor['sofor_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu şoförü silmek istediğinize emin misiniz?')">Sil</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Kayıtlı şoför bulunamadı.</td>
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