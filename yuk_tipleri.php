<?php
require_once 'config.php';

// Yük tiplerini getir
$stmt = $db->query("SELECT * FROM yuk_tipleri ORDER BY tip_adi");
$yuk_tipleri = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yük Tipleri - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Yük Tipleri</h1>
            <div>
                <a href="yuk_tipi_ekle.php" class="btn btn-success">Yeni Yük Tipi Ekle</a>
                <a href="index.php" class="btn btn-primary">Ana Sayfa</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <?php if (count($yuk_tipleri) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Yük Tipi Adı</th>
                                    <th>Açıklama</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($yuk_tipleri as $yuk_tipi): ?>
                                    <tr>
                                        <td><?= $yuk_tipi['yuk_tip_id'] ?></td>
                                        <td><?= $yuk_tipi['tip_adi'] ?></td>
                                        <td><?= $yuk_tipi['aciklama'] ?></td>
                                        <td>
                                            <a href="yuk_tipi_duzenle.php?id=<?= $yuk_tipi['yuk_tip_id'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                                            <a href="yuk_tipi_sil.php?id=<?= $yuk_tipi['yuk_tip_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu yük tipini silmek istediğinize emin misiniz?')">Sil</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">Henüz yük tipi eklenmemiş.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>