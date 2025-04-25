        $mesaj = "Tur başarıyla tamamlandı!";
        
        // 2 saniye bekleyip turlar sayfasına yönlendir
        header("refresh:2;url=turlar.php");
    } catch (Exception $e) {
        $hata = "Hata oluştu: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tur Tamamla - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Tur Tamamla</h1>
            <div>
                <a href="turlar.php" class="btn btn-secondary">Turlar</a>
                <a href="index.php" class="btn btn-primary">Ana Sayfa</a>
            </div>
        </div>
        
        <?php if ($mesaj): ?>
            <div class="alert alert-success">
                <p><?= $mesaj ?></p>
                <p>Turlar sayfasına yönlendiriliyorsunuz...</p>
            </div>
        <?php elseif ($hata): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Turu tamamlamak istediğinize emin misiniz?</h5>
                    <p><strong>Tur ID:</strong> <?= $tur['tur_id'] ?></p>
                    <p><strong>Şoför:</strong> <?= $tur['sofor_adi'] ?: 'Belirtilmemiş' ?></p>
                    <p><strong>Araç:</strong> <?= $tur['plaka'] ?: 'Belirtilmemiş' ?></p>
                    <p><strong>Çıkış Tarihi:</strong> <?= formatTarih($tur['cikis_tarihi']) ?></p>
                    <p><strong>Çıkış Saati:</strong> <?= formatSaat($tur['cikis_saati']) ?></p>
                    
                    <div class="alert alert-info">
                        <strong>Not:</strong> Turu tamamladığınızda, şoför ve araç durumu "Aktif" olarak güncellenecek ve tüm duraklar "Teslim Edildi" olarak işaretlenecektir.
                    </div>
                    
                    <form method="post">
                        <div class="mb-3">
                            <label for="gercek_donus_tarihi" class="form-label">Gerçek Dönüş Tarihi</label>
                            <input type="date" class="form-control" id="gercek_donus_tarihi" name="gercek_donus_tarihi" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="gercek_donus_saati" class="form-label">Gerçek Dönüş Saati</label>
                            <input type="time" class="form-control" id="gercek_donus_saati" name="gercek_donus_saati" value="<?= date('H:i') ?>" required>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Evet, Tamamla</button>
                            <a href="turlar.php" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
