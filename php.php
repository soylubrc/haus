1. Veritaban� Yap�s�
�ncelikle veritaban� �emas�n� olu�tural�m:

CREATE DATABASE lojistik_firma;
USE lojistik_firma;

CREATE TABLE iller (
    il_id INT AUTO_INCREMENT PRIMARY KEY,
    il_adi VARCHAR(50) NOT NULL,
    merkeze_uzaklik INT NOT NULL COMMENT 'Kilometre cinsinden'
);

CREATE TABLE soforler (
    sofor_id INT AUTO_INCREMENT PRIMARY KEY,
    ad VARCHAR(50) NOT NULL,
    soyad VARCHAR(50) NOT NULL,
    telefon VARCHAR(15),
    ehliyet_no VARCHAR(20),
    durum ENUM('Aktif', '�zinli', 'Yolda') DEFAULT 'Aktif'
);

CREATE TABLE araclar (
    arac_id INT AUTO_INCREMENT PRIMARY KEY,
    plaka VARCHAR(15) NOT NULL,
    model VARCHAR(50),
    kapasite FLOAT NOT NULL COMMENT 'kg cinsinden',
    durum ENUM('Aktif', 'Bak�mda', 'Yolda') DEFAULT 'Aktif'
);

CREATE TABLE yuk_tipleri (
    yuk_tip_id INT AUTO_INCREMENT PRIMARY KEY,
    tip_adi VARCHAR(50) NOT NULL,
    aciklama TEXT
);

CREATE TABLE turlar (
    tur_id INT AUTO_INCREMENT PRIMARY KEY,
    sofor_id INT,
    arac_id INT,
    cikis_tarihi DATE NOT NULL,
    cikis_saati TIME NOT NULL,
    tahmini_donus_tarihi DATE,
    tahmini_donus_saati TIME,
    gercek_donus_tarihi DATE,
    gercek_donus_saati TIME,
    toplam_mesafe INT COMMENT 'Kilometre cinsinden',
    durum ENUM('Planland�', 'Yolda', 'Tamamland�', '�ptal Edildi') DEFAULT 'Planland�',
    olusturma_zamani TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sofor_id) REFERENCES soforler(sofor_id),
    FOREIGN KEY (arac_id) REFERENCES araclar(arac_id)
);

CREATE TABLE tur_duraklar (
    durak_id INT AUTO_INCREMENT PRIMARY KEY,
    tur_id INT,
    il_id INT,
    sira INT NOT NULL COMMENT 'Durak s�ras�',
    yuk_tip_id INT,
    yuk_miktari FLOAT COMMENT 'kg cinsinden',
    teslim_durumu ENUM('Bekleniyor', 'Teslim Edildi', '�ptal') DEFAULT 'Bekleniyor',
    FOREIGN KEY (tur_id) REFERENCES turlar(tur_id) ON DELETE CASCADE,
    FOREIGN KEY (il_id) REFERENCES iller(il_id),
    FOREIGN KEY (yuk_tip_id) REFERENCES yuk_tipleri(yuk_tip_id)
);

-- Temel y�k tiplerini ekleyelim
INSERT INTO yuk_tipleri (tip_adi, aciklama) VALUES 
('��ecek', 'Su, me�rubat, meyve suyu vb.'),
('Normal Y�k', 'Standart kargo ve y�kler');

-- �rnek iller ekleyelim
INSERT INTO iller (il_adi, merkeze_uzaklik) VALUES 
('�stanbul', 0),
('Ankara', 450),
('�zmir', 480),
('Bursa', 240),
('Antalya', 700),
('Adana', 940);

database.sql
2. Veritaban� Ba�lant� Dosyas�
<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "lojistik_firma";

try {
    $db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Veritaban� ba�lant� hatas�: " . $e->getMessage();
    die();
}

// Tarih format� fonksiyonu
function formatTarih($tarih) {
    if (!$tarih) return "";
    return date("d.m.Y", strtotime($tarih));
}

// Saat format� fonksiyonu
function formatSaat($saat) {
    if (!$saat) return "";
    return date("H:i", strtotime($saat));
}
?>

config.php
3. Ana Sayfa
<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lojistik Firma Y�netim Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Lojistik Firma Y�netim Sistemi</h1>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Tur Y�netimi</h5>
                    </div>
                    <div class="card-body">
                        <p>Ara� turlar�n� planlay�n, d�zenleyin ve takip edin.</p>
                        <a href="tur_ekle.php" class="btn btn-primary">Yeni Tur Ekle</a>
                        <a href="turlar.php" class="btn btn-outline-primary mt-2">Turlar� Listele</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">�of�r Y�netimi</h5>
                    </div>
                    <div class="card-body">
                        <p>�of�r bilgilerini ekleyin ve d�zenleyin.</p>
                        <a href="sofor_ekle.php" class="btn btn-success">Yeni �of�r Ekle</a>
                        <a href="soforler.php" class="btn btn-outline-success mt-2">�of�rleri Listele</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">Ara� Y�netimi</h5>
                    </div>
                    <div class="card-body">
                        <p>Ara� bilgilerini ekleyin ve d�zenleyin.</p>
                        <a href="arac_ekle.php" class="btn btn-info">Yeni Ara� Ekle</a>
                        <a href="araclar.php" class="btn btn-outline-info mt-2">Ara�lar� Listele</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="card-title mb-0">�l Y�netimi</h5>
                    </div>
                    <div class="card-body">
                        <p>�l bilgilerini ve mesafeleri y�netin.</p>
                        <a href="il_ekle.php" class="btn btn-warning">Yeni �l Ekle</a>
                        <a href="iller.php" class="btn btn-outline-warning mt-2">�lleri Listele</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">Y�k Tipleri</h5>
                    </div>
                    <div class="card-body">
                        <p>Y�k tiplerini ekleyin ve d�zenleyin.</p>
                        <a href="yuk_tipi_ekle.php" class="btn btn-danger">Yeni Y�k Tipi Ekle</a>
                        <a href="yuk_tipleri.php" class="btn btn-outline-danger mt-2">Y�k Tiplerini Listele</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">Anl�k Tur Ekran�</h5>
                    </div>
                    <div class="card-body">
                        <p>Depo �al��anlar� i�in anl�k tur bilgilerini g�r�nt�leyin.</p>
                        <a href="canli_ekran.php" class="btn btn-secondary">Canl� Ekran� A�</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

index.php
4. Tur Ekleme Sayfas�
<?php
require_once 'config.php';

$mesaj = '';
$hata = '';

// �of�rleri getir
$stmt = $db->query("SELECT * FROM soforler WHERE durum = 'Aktif' ORDER BY ad, soyad");
$soforler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ara�lar� getir
$stmt = $db->query("SELECT * FROM araclar WHERE durum = 'Aktif' ORDER BY plaka");
$araclar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// �lleri getir
$stmt = $db->query("SELECT * FROM iller ORDER BY il_adi");
$iller = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Y�k tiplerini getir
$stmt = $db->query("SELECT * FROM yuk_tipleri ORDER BY tip_adi");
$yuk_tipleri = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        // Tur bilgilerini ekle
        $stmt = $db->prepare("INSERT INTO turlar (sofor_id, arac_id, cikis_tarihi, cikis_saati, 
                             tahmini_donus_tarihi, tahmini_donus_saati, toplam_mesafe, durum) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, 'Planland�')");
        
        $stmt->execute([
            $_POST['sofor_id'],
            $_POST['arac_id'],
            $_POST['cikis_tarihi'],
            $_POST['cikis_saati'],
            $_POST['tahmini_donus_tarihi'],
            $_POST['tahmini_donus_saati'],
            $_POST['toplam_mesafe']
        ]);
        
        $tur_id = $db->lastInsertId();
        
        // Duraklar� ekle
        $durak_sayisi = count($_POST['il_id']);
        
        for ($i = 0; $i < $durak_sayisi; $i++) {
            if (!empty($_POST['il_id'][$i])) {
                $stmt = $db->prepare("INSERT INTO tur_duraklar (tur_id, il_id, sira, yuk_tip_id, yuk_miktari) 
                                     VALUES (?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $tur_id,
                    $_POST['il_id'][$i],
                    $i + 1,
                    $_POST['yuk_tip_id'][$i],
                    $_POST['yuk_miktari'][$i]
                ]);
            }
        }
        
        // �of�r ve ara� durumunu g�ncelle
        $db->prepare("UPDATE soforler SET durum = 'Yolda' WHERE sofor_id = ?")->execute([$_POST['sofor_id']]);
        $db->prepare("UPDATE araclar SET durum = 'Yolda' WHERE arac_id = ?")->execute([$_POST['arac_id']]);
        
        $db->commit();
        $mesaj = "Tur ba�ar�yla olu�turuldu!";
    } catch (Exception $e) {
        $db->rollBack();
        $hata = "Hata olu�tu: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Tur Ekle - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Yeni Tur Ekle</h1>
            <a href="index.php" class="btn btn-secondary">Ana Sayfa</a>
        </div>
        
        <?php if ($mesaj): ?>
            <div class="alert alert-success"><?= $mesaj ?></div>
        <?php endif; ?>
        
        <?php if ($hata): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
        <?php endif; ?>
        
        <form method="post" id="turForm">
            <div class="row">
                <div class="col-md-6">

Context
� none fetched
Tabii, tur_ekle.php sayfas�n�n devam�n� tamamlayal�m:

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Tur Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="sofor_id" class="form-label">�of�r</label>
                                <select name="sofor_id" id="sofor_id" class="form-select" required>
                                    <option value="">�of�r Se�in</option>
                                    <?php foreach ($soforler as $sofor): ?>
                                        <option value="<?= $sofor['sofor_id'] ?>">
                                            <?= $sofor['ad'] . ' ' . $sofor['soyad'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="arac_id" class="form-label">Ara�</label>
                                <select name="arac_id" id="arac_id" class="form-select" required>
                                    <option value="">Ara� Se�in</option>
                                    <?php foreach ($araclar as $arac): ?>
                                        <option value="<?= $arac['arac_id'] ?>">
                                            <?= $arac['plaka'] . ' - ' . $arac['model'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cikis_tarihi" class="form-label">��k�� Tarihi</label>
                                        <input type="date" name="cikis_tarihi" id="cikis_tarihi" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cikis_saati" class="form-label">��k�� Saati</label>
                                        <input type="time" name="cikis_saati" id="cikis_saati" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tahmini_donus_tarihi" class="form-label">Tahmini D�n�� Tarihi</label>
                                        <input type="date" name="tahmini_donus_tarihi" id="tahmini_donus_tarihi" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tahmini_donus_saati" class="form-label">Tahmini D�n�� Saati</label>
                                        <input type="time" name="tahmini_donus_saati" id="tahmini_donus_saati" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="toplam_mesafe" class="form-label">Toplam Mesafe (km)</label>
                                <input type="number" name="toplam_mesafe" id="toplam_mesafe" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Duraklar</h5>
                            <button type="button" class="btn btn-light btn-sm" id="durakEkle">+ Durak Ekle</button>
                        </div>
                        <div class="card-body">
                            <div id="duraklar">
                                <div class="durak-item mb-3 p-3 border rounded">
                                    <h6 class="durak-sira mb-2">Durak #1</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">�l</label>
                                                <select name="il_id[]" class="form-select il-select" required>
                                                    <option value="">�l Se�in</option>
                                                    <?php foreach ($iller as $il): ?>
                                                        <option value="<?= $il['il_id'] ?>" data-mesafe="<?= $il['merkeze_uzaklik'] ?>">
                                                            <?= $il['il_adi'] ?> (<?= $il['merkeze_uzaklik'] ?> km)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <label class="form-label">Y�k Tipi</label>
                                                <select name="yuk_tip_id[]" class="form-select" required>
                                                    <option value="">Y�k Tipi Se�in</option>
                                                    <?php foreach ($yuk_tipleri as $yuk_tipi): ?>
                                                        <option value="<?= $yuk_tipi['yuk_tip_id'] ?>">
                                                            <?= $yuk_tipi['tip_adi'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Y�k Miktar� (kg)</label>
                                        <input type="number" name="yuk_miktari[]" class="form-control" step="0.1" min="0" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-primary btn-lg">Turu Kaydet</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let durakSayisi = 1;
            const durakEkleBtn = document.getElementById('durakEkle');
            const duraklar = document.getElementById('duraklar');
            
            // Durak ekleme
            durakEkleBtn.addEventListener('click', function() {
                durakSayisi++;
                
                const yeniDurak = document.createElement('div');
                yeniDurak.className = 'durak-item mb-3 p-3 border rounded';
                
                const durakHeader = document.createElement('div');
                durakHeader.className = 'd-flex justify-content-between align-items-center mb-2';
                
                const durakSira = document.createElement('h6');
                durakSira.className = 'durak-sira mb-0';
                durakSira.textContent = `Durak #${durakSayisi}`;
                
                const silBtn = document.createElement('button');
                silBtn.type = 'button';
                silBtn.className = 'btn btn-danger btn-sm durak-sil';
                silBtn.textContent = 'Sil';
                silBtn.addEventListener('click', function() {
                    yeniDurak.remove();
                    hesaplaToplam();
                    yenidenNumaralandir();
                });
                
                durakHeader.appendChild(durakSira);
                durakHeader.appendChild(silBtn);
                
                yeniDurak.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">�l</label>
                                <select name="il_id[]" class="form-select il-select" required>
                                    <option value="">�l Se�in</option>
                                    <?php foreach ($iller as $il): ?>
                                        <option value="<?= $il['il_id'] ?>" data-mesafe="<?= $il['merkeze_uzaklik'] ?>">
                                            <?= $il['il_adi'] ?> (<?= $il['merkeze_uzaklik'] ?> km)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Y�k Tipi</label>
                                <select name="yuk_tip_id[]" class="form-select" required>
                                    <option value="">Y�k Tipi Se�in</option>
                                    <?php foreach ($yuk_tipleri as $yuk_tipi): ?>
                                        <option value="<?= $yuk_tipi['yuk_tip_id'] ?>">
                                            <?= $yuk_tipi['tip_adi'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Y�k Miktar� (kg)</label>
                        <input type="number" name="yuk_miktari[]" class="form-control" step="0.1" min="0" required>
                    </div>
                `;
                
                yeniDurak.insertBefore(durakHeader, yeniDurak.firstChild);
                duraklar.appendChild(yeniDurak);
                
                // Yeni eklenen durak i�in il de�i�ikli�i dinleyicisi ekle
                const yeniIlSelect = yeniDurak.querySelector('.il-select');
                yeniIlSelect.addEventListener('change', hesaplaToplam);
            });
            
            // �l de�i�ikliklerini dinle ve toplam mesafeyi hesapla
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('il-select')) {
                    hesaplaToplam();
                }
            });
            
            // �lk durak i�in il de�i�ikli�i dinleyicisi ekle
            const ilkIlSelect = document.querySelector('.il-select');
            ilkIlSelect.addEventListener('change', hesaplaToplam);
            
            // Toplam mesafeyi hesapla
            function hesaplaToplam() {
                const ilSelects = document.querySelectorAll('.il-select');
                let toplamMesafe = 0;
                
                ilSelects.forEach(select => {
                    const selectedOption = select.options[select.selectedIndex];
                    if (selectedOption && selectedOption.value) {
                        const mesafe = parseInt(selectedOption.dataset.mesafe);
                        if (!isNaN(mesafe)) {
                            toplamMesafe += mesafe * 2; // Gidi�-d�n�� mesafesi
                        }
                    }
                });
                
                document.getElementById('toplam_mesafe').value = toplamMesafe;
            }
            
            // Duraklar� yeniden numaraland�r
            function yenidenNumaralandir() {
                const durakSiralar = document.querySelectorAll('.durak-sira');
                durakSiralar.forEach((sira, index) => {
                    sira.textContent = `Durak #${index + 1}`;
                });
                durakSayisi = durakSiralar.length;
            }
        });
    </script>
</body>
</html>

tur_ekle.php
5. Turlar� Listeleme Sayfas�
<?php
require_once 'config.php';

// Turlar� getir
$stmt = $db->query("SELECT t.*, 
                    CONCAT(s.ad, ' ', s.soyad) as sofor_adi, 
                    a.plaka 
                    FROM turlar t
                    LEFT JOIN soforler s ON t.sofor_id = s.sofor_id
                    LEFT JOIN araclar a ON t.arac_id = a.arac_id
                    ORDER BY t.cikis_tarihi DESC, t.cikis_saati DESC");
$turlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turlar - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Turlar</h1>
            <div>
                <a href="tur_ekle.php" class="btn btn-primary">Yeni Tur Ekle</a>
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
                                <th>�of�r</th>
                                <th>Ara�</th>
                                <th>��k�� Tarihi</th>
                                <th>��k�� Saati</th>
                                <th>Tahmini D�n��</th>
                                <th>Toplam Mesafe</th>
                                <th>Durum</th>
                                <th>��lemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($turlar) > 0): ?>
                                <?php foreach ($turlar as $

turlar.php
turlar.php sayfas�na kald���n yerden devam eder misin?


Context
� none fetched
Tabii, turlar.php sayfas�n�n devam�n� tamamlayal�m:

                                <?php foreach ($turlar as $tur): ?>
                                    <tr>
                                        <td><?= $tur['tur_id'] ?></td>
                                        <td><?= $tur['sofor_adi'] ?></td>
                                        <td><?= $tur['plaka'] ?></td>
                                        <td><?= formatTarih($tur['cikis_tarihi']) ?></td>
                                        <td><?= formatSaat($tur['cikis_saati']) ?></td>
                                        <td>
                                            <?= formatTarih($tur['tahmini_donus_tarihi']) ?> 
                                            <?= formatSaat($tur['tahmini_donus_saati']) ?>
                                        </td>
                                        <td><?= $tur['toplam_mesafe'] ?> km</td>
                                        <td>
                                            <?php
                                            $durum_class = '';
                                            switch ($tur['durum']) {
                                                case 'Planland�':
                                                    $durum_class = 'bg-info';
                                                    break;
                                                case 'Yolda':
                                                    $durum_class = 'bg-warning';
                                                    break;
                                                case 'Tamamland�':
                                                    $durum_class = 'bg-success';
                                                    break;
                                                case '�ptal Edildi':
                                                    $durum_class = 'bg-danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?= $durum_class ?>"><?= $tur['durum'] ?></span>
                                        </td>
                                        <td>
                                            <a href="tur_detay.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-info">Detay</a>
                                            <a href="tur_duzenle.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-warning">D�zenle</a>
                                            <?php if ($tur['durum'] == 'Planland�'): ?>
                                                <a href="tur_baslat.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-success">Ba�lat</a>
                                            <?php elseif ($tur['durum'] == 'Yolda'): ?>
                                                <a href="tur_tamamla.php?id=<?= $tur['tur_id'] ?>" class="btn btn-sm btn-primary">Tamamla</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">Kay�tl� tur bulunamad�.</td>
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

turlar.php
6. Tur Detay Sayfas�
<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: turlar.php');
    exit;
}

$tur_id = $_GET['id'];

// Tur bilgilerini getir
$stmt = $db->prepare("SELECT t.*, 
                      CONCAT(s.ad, ' ', s.soyad) as sofor_adi, 
                      s.telefon as sofor_telefon,
                      a.plaka, a.model as arac_model
                      FROM turlar t
                      LEFT JOIN soforler s ON t.sofor_id = s.sofor_id
                      LEFT JOIN araclar a ON t.arac_id = a.arac_id
                      WHERE t.tur_id = ?");
$stmt->execute([$tur_id]);
$tur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tur) {
    header('Location: turlar.php');
    exit;
}

// Tur duraklar�n� getir
$stmt = $db->prepare("SELECT td.*, i.il_adi, yt.tip_adi as yuk_tipi
                      FROM tur_duraklar td
                      LEFT JOIN iller i ON td.il_id = i.il_id
                      LEFT JOIN yuk_tipleri yt ON td.yuk_tip_id = yt.yuk_tip_id
                      WHERE td.tur_id = ?
                      ORDER BY td.sira");
$stmt->execute([$tur_id]);
$duraklar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tur Detay� - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Tur Detay� #<?= $tur['tur_id'] ?></h1>
            <div>
                <a href="turlar.php" class="btn btn-secondary">Turlar</a>
                <a href="index.php" class="btn btn-primary">Ana Sayfa</a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tur Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%">Durum</th>
                                <td>
                                    <?php
                                    $durum_class = '';
                                    switch ($tur['durum']) {
                                        case 'Planland�':
                                            $durum_class = 'bg-info';
                                            break;
                                        case 'Yolda':
                                            $durum_class = 'bg-warning';
                                            break;
                                        case 'Tamamland�':
                                            $durum_class = 'bg-success';
                                            break;
                                        case '�ptal Edildi':
                                            $durum_class = 'bg-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?= $durum_class ?>"><?= $tur['durum'] ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th>�of�r</th>
                                <td><?= $tur['sofor_adi'] ?> (Tel: <?= $tur['sofor_telefon'] ?>)</td>
                            </tr>
                            <tr>
                                <th>Ara�</th>
                                <td><?= $tur['plaka'] ?> - <?= $tur['arac_model'] ?></td>
                            </tr>
                            <tr>
                                <th>��k�� Tarihi/Saati</th>
                                <td><?= formatTarih($tur['cikis_tarihi']) ?> <?= formatSaat($tur['cikis_saati']) ?></td>
                            </tr>
                            <tr>
                                <th>Tahmini D�n��</th>
                                <td><?= formatTarih($tur['tahmini_donus_tarihi']) ?> <?= formatSaat($tur['tahmini_donus_saati']) ?></td>
                            </tr>
                            <?php if ($tur['gercek_donus_tarihi']): ?>
                            <tr>
                                <th>Ger�ek D�n��</th>
                                <td><?= formatTarih($tur['gercek_donus_tarihi']) ?> <?= formatSaat($tur['gercek_donus_saati']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Toplam Mesafe</th>
                                <td><?= $tur['toplam_mesafe'] ?> km</td>
                            </tr>
                            <tr>
                                <th>Olu�turulma Zaman�</th>
                                <td><?= date("d.m.Y H:i", strtotime($tur['olusturma_zamani'])) ?></td>
                            </tr>
                        </table>
                        
                        <div class="mt-3">
                            <?php if ($tur['durum'] == 'Planland�'): ?>
                                <a href="tur_baslat.php?id=<?= $tur['tur_id'] ?>" class="btn btn-success">Turu Ba�lat</a>
                            <?php elseif ($tur['durum'] == 'Yolda'): ?>
                                <a href="tur_tamamla.php?id=<?= $tur['tur_id'] ?>" class="btn btn-primary">Turu Tamamla</a>
                            <?php endif; ?>
                            
                            <?php if ($tur['durum'] != 'Tamamland�' && $tur['durum'] != '�ptal Edildi'): ?>
                                <a href="tur_duzenle.php?id=<?= $tur['tur_id'] ?>" class="btn btn-warning">D�zenle</a>
                                <a href="tur_iptal.php?id=<?= $tur['tur_id'] ?>" class="btn btn-danger" onclick="return confirm('Bu turu iptal etmek istedi�inize emin misiniz?')">�ptal Et</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Duraklar</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($duraklar) > 0): ?>
                            <div class="list-group">
                                <?php foreach ($duraklar as $durak): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-1">Durak #<?= $durak['sira'] ?>: <?= $durak['il_adi'] ?></h6>
                                            <?php if ($tur['durum'] == 'Yolda'): ?>
                                                <?php if ($durak['teslim_durumu'] == 'Bekleniyor'): ?>
                                                    <a href="durak_teslim.php?id=<?= $durak['durak_id'] ?>&tur_id=<?= $tur_id ?>" class="btn btn-sm btn-success">Teslim Edildi</a>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Teslim Edildi</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge <?= $durak['teslim_durumu'] == 'Teslim Edildi' ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $durak['teslim_durumu'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mb-1">Y�k Tipi: <?= $durak['yuk_tipi'] ?></p>
                                        <p class="mb-0">Y�k Miktar�: <?= $durak['yuk_miktari'] ?> kg</p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center">Bu tur i�in durak bilgisi bulunamad�.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

tur_detay.php
7. Tur Ba�latma Sayfas�
<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: turlar.php');
    exit;
}

$tur_id = $_GET['id'];
$mesaj = '';
$hata = '';

// Tur bilgilerini kontrol et
$stmt = $db->prepare("SELECT * FROM turlar WHERE tur_id = ?");
$stmt->execute([$tur_id]);
$tur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tur) {
    header('Location: turlar.php');
    exit;
}

if ($tur['durum'] != 'Planland�') {
    $hata = "Bu tur zaten ba�lat�lm�� veya iptal edilmi� durumda!";
} else {
    try {
        $db->beginTransaction();
        
        // Tur durumunu g�ncelle
        $stmt = $db->prepare("UPDATE turlar SET durum = 'Yolda' WHERE tur_id = ?");
        $stmt->execute([$tur_id]);
        
        $db->commit();
        $mesaj = "Tur ba�ar�yla ba�lat�ld�!";
        
        // 2 saniye bekleyip tur detay sayfas�na y�nlendir
        header("refresh:2;url=tur_detay.php?id=" . $tur_id);
    } catch (Exception $e) {
        $db->rollBack();
        $hata = "Hata olu�tu: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
 <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tur Ba�lat - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Tur Ba�latma</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($mesaj): ?>
                            <div class="alert alert-success">
                                <h5>��lem Ba�ar�l�!</h5>
                                <p><?= $mesaj ?></p>
                                <p>Tur detay sayfas�na y�nlendiriliyorsunuz...</p>
                            </div>
                        <?php elseif ($hata): ?>
                            <div class="alert alert-danger">
                                <h5>Hata!</h5>
                                <p><?= $hata ?></p>
                                <div class="mt-3">
                                    <a href="tur_detay.php?id=<?= $tur_id ?>" class="btn btn-primary">Tur Detay�na D�n</a>
                                    <a href="turlar.php" class="btn btn-secondary">T�m Turlar</a>
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

tur_baslat.php
8. Tur Tamamlama Sayfas�
<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: turlar.php');
    exit;
}

$tur_id = $_GET['id'];
$mesaj = '';
$hata = '';

// Tur bilgilerini kontrol et
$stmt = $db->prepare("SELECT t.*, s.sofor_id, a.arac_id 
                      FROM turlar t 
                      LEFT JOIN soforler s ON t.sofor_id = s.sofor_id 
                      LEFT JOIN araclar a ON t.arac_id = a.arac_id 
                      WHERE t.tur_id = ?");
$stmt->execute([$tur_id]);
$tur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tur) {
    header('Location: turlar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gercek_donus_tarihi = $_POST['gercek_donus_tarihi'];
    $gercek_donus_saati = $_POST['gercek_donus_saati'];
    
    if (empty($gercek_donus_tarihi) || empty($gercek_donus_saati)) {
        $hata = "Ger�ek d�n�� tarihi ve saati gereklidir!";
    } else {
        try {
            $db->beginTransaction();
            
            // Tur durumunu g�ncelle
            $stmt = $db->prepare("UPDATE turlar SET 
                                 durum = 'Tamamland�', 
                                 gercek_donus_tarihi = ?, 
                                 gercek_donus_saati = ? 
                                 WHERE tur_id = ?");
            $stmt->execute([$gercek_donus_tarihi, $gercek_donus_saati, $tur_id]);
            
            // �of�r ve ara� durumunu g�ncelle
            $db->prepare("UPDATE soforler SET durum = 'Aktif' WHERE sofor_id = ?")->execute([$tur['sofor_id']]);
            $db->prepare("UPDATE araclar SET durum = 'Aktif' WHERE arac_id = ?")->execute([$tur['arac_id']]);
            
            // T�m duraklar� teslim edildi olarak i�aretle
            $db->prepare("UPDATE tur_duraklar SET teslim_durumu = 'Teslim Edildi' WHERE tur_id = ? AND teslim_durumu = 'Bekleniyor'")->execute([$tur_id]);
            
            $db->commit();
            $mesaj = "Tur ba�ar�yla tamamland�!";
            
            // 2 saniye bekleyip tur detay sayfas�na y�nlendir
            header("refresh:2;url=tur_detay.php?id=" . $tur_id);
        } catch (Exception $e) {
            $db->rollBack();
            $hata = "Hata olu�tu: " . $e->getMessage();
        }
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
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Tur Tamamlama</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($mesaj): ?>
                            <div class="alert alert-success">
                                <h5>��lem Ba�ar�l�!</h5>
                                <p><?= $mesaj ?></p>
                                <p>Tur detay sayfas�na y�nlendiriliyorsunuz...</p>
                            </div>
                        <?php elseif ($hata): ?>
                            <div class="alert alert-danger">
                                <h5>Hata!</h5>
                                <p><?= $hata ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($tur['durum'] != 'Tamamland�' && $tur['durum'] != '�ptal Edildi' && !$mesaj): ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="gercek_donus_tarihi" class="form-label">Ger�ek D�n�� Tarihi</label>
                                    <input type="date" name="gercek_donus_tarihi" id="gercek_donus_tarihi" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="gercek_donus_saati" class="form-label">Ger�ek D�n�� Saati</label>
                                    <input type="time" name="gercek_donus_saati" id="gercek_donus_saati" class="form-control" value="<?= date('H:i') ?>" required>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success">Turu Tamamla</button>
                                    <a href="tur_detay.php?id=<?= $tur_id ?>" class="btn btn-secondary">�ptal</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

tur_tamamla.php
9. Durak Teslim Sayfas�
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
    
    // Durak durumunu g�ncelle
    $stmt = $db->prepare("UPDATE tur_duraklar SET teslim_durumu = 'Teslim Edildi' WHERE durak_id = ?");
    $stmt->execute([$durak_id]);
    
    $db->commit();
    $mesaj = "Durak teslim edildi olarak i�aretlendi!";
    
    // 2 saniye bekleyip tur detay sayfas�na y�nlendir
    header("refresh:2;url=tur_detay.php?id=" . $tur_id);
} catch (Exception $e) {
    $db->rollBack();
    $hata = "Hata olu�tu: " . $e->getMessage();
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
                                <h5>��lem Ba�ar�l�!</h5>
                                <p><?= $mesaj ?></p>
                                <p>Tur detay sayfas�na y�nlendiriliyorsunuz...</p>
                            </div>
                        <?php elseif ($hata): ?>
                            <div class="alert alert-danger">
                                <h5>Hata!</h5>
                                <p><?= $hata ?></p>
                                <div class="mt-3">
                                    <a href="tur_detay.php?id=<?= $tur_id ?>" class="btn btn-primary">Tur Detay�na D�n</a>
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

durak_teslim.php
10. Canl� Ekran (Depo �al��anlar� ��in)
<?php
require_once 'config.php';

// Bug�n ve yar�n i�in planlanan turlar� getir
$stmt = $db->query("SELECT t.*, 
                    CONCAT(s.ad, ' ', s.soyad) as sofor_adi, 
                    a.plaka 
                    FROM turlar t
                    LEFT JOIN soforler s ON t.sofor_id = s.sofor_id
                    LEFT JOIN araclar a ON t.arac_id = a.arac_id
                    WHERE t.durum IN ('Planland�', 'Yolda') 
                    AND (t.cikis_tarihi = CURDATE() OR t.cikis_tarihi = DATE_ADD(CURDATE(), INTERVAL 1 DAY))
                    ORDER BY t.cikis_tarihi, t.cikis_saati");
$turlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Duraklar� getir
$duraklar = [];
if (count($turlar) > 0) {
    $tur_idler = array_column($turlar, 'tur_id');
    $tur_id_str = implode(',', $tur_idler);
    
    $stmt = $db->query("SELECT td.*, t.tur_id, i.il_adi, yt.tip_adi as yuk_tipi
                        FROM tur_duraklar td
                        JOIN turlar t ON td.tur_id = t.tur_id
                        LEFT JOIN iller i ON td.il_id = i.il_id
                        LEFT JOIN yuk_tipleri yt ON td.yuk_tip_id = yt.yuk_tip_id
                        WHERE td.tur_id IN ($tur_id_str)
                        ORDER BY td.tur_id, td.sira");
    
    $tum_duraklar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($tum_duraklar as $durak) {
        $duraklar[$durak['tur_id']][] = $durak;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canl� Tur Ekran� - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .tur-

canli_ekran.php
canli_ekran.php sayfas�nda 
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .tur-
burada kald�. Kald���m�z yerden devam eder misin?


Context
� none fetched
Tabii, canli_ekran.php sayfas�n�n devam�n� tamamlayal�m:

        .tur-card {
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .tur-header {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .durak-item {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .durak-item:last-child {
            border-bottom: none;
        }
        .durak-item.teslim-edildi {
            background-color: #d1e7dd;
        }
        .durak-item.bekleniyor {
            background-color: #fff3cd;
        }
        .saat {
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .tarih {
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 30px;
        }
        .baslik {
            text-align: center;
            margin-bottom: 30px;
            color: #0d6efd;
        }
        .yuk-tipi-icecek {
            color: #0d6efd;
            font-weight: bold;
        }
        .yuk-tipi-normal {
            color: #198754;
            font-weight: bold;
        }
        .refresh-info {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }
    </style>
    <meta http-equiv="refresh" content="60">
</head>
<body>
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="baslik">LOJ�ST�K F�RMA TUR EKRANI</h1>
                <div class="saat" id="saat"></div>
                <div class="tarih" id="tarih"></div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">BUG�NK� TURLAR</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        $bugun_var = false;
                        foreach ($turlar as $tur): 
                            if (date('Y-m-d', strtotime($tur['cikis_tarihi'])) == date('Y-m-d')):
                                $bugun_var = true;
                        ?>
                            <div class="card tur-card">
                                <div class="card-header <?= $tur['durum'] == 'Yolda' ? 'bg-warning' : 'bg-info' ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="tur-header">
                                            <?= formatSaat($tur['cikis_saati']) ?> - <?= $tur['plaka'] ?>
                                        </span>
                                        <span class="badge <?= $tur['durum'] == 'Yolda' ? 'bg-danger' : 'bg-primary' ?>">
                                            <?= $tur['durum'] ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="p-3">
                                        <p class="mb-1"><strong>�of�r:</strong> <?= $tur['sofor_adi'] ?></p>
                                        <p class="mb-0"><strong>Toplam Mesafe:</strong> <?= $tur['toplam_mesafe'] ?> km</p>
                                    </div>
                                    
                                    <?php if (isset($duraklar[$tur['tur_id']])): ?>
                                        <div class="duraklar">
                                            <?php foreach ($duraklar[$tur['tur_id']] as $durak): ?>
                                                <div class="durak-item <?= $durak['teslim_durumu'] == 'Teslim Edildi' ? 'teslim-edildi' : 'bekleniyor' ?>">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span><strong><?= $durak['sira'] ?>.</strong> <?= $durak['il_adi'] ?></span>
                                                        <span class="badge <?= $durak['teslim_durumu'] == 'Teslim Edildi' ? 'bg-success' : 'bg-warning' ?>">
                                                            <?= $durak['teslim_durumu'] ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="<?= $durak['yuk_tipi'] == '��ecek' ? 'yuk-tipi-icecek' : 'yuk-tipi-normal' ?>">
                                                            <?= $durak['yuk_tipi'] ?>
                                                        </span>
                                                        - <?= $durak['yuk_miktari'] ?> kg
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-3 text-center text-muted">
                                            Durak bilgisi bulunamad�.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        
                        if (!$bugun_var):
                        ?>
                            <div class="alert alert-info text-center">
                                Bug�n i�in planlanm�� tur bulunmamaktad�r.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0">YARININ TURLARI</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        $yarin_var = false;
                        foreach ($turlar as $tur): 
                            if (date('Y-m-d', strtotime($tur['cikis_tarihi'])) == date('Y-m-d', strtotime('+1 day'))):
                                $yarin_var = true;
                        ?>
                            <div class="card tur-card">
                                <div class="card-header bg-info">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="tur-header">
                                            <?= formatSaat($tur['cikis_saati']) ?> - <?= $tur['plaka'] ?>
                                        </span>
                                        <span class="badge bg-primary">
                                            <?= $tur['durum'] ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="p-3">
                                        <p class="mb-1"><strong>�of�r:</strong> <?= $tur['sofor_adi'] ?></p>
                                        <p class="mb-0"><strong>Toplam Mesafe:</strong> <?= $tur['toplam_mesafe'] ?> km</p>
                                    </div>
                                    
                                    <?php if (isset($duraklar[$tur['tur_id']])): ?>
                                        <div class="duraklar">
                                            <?php foreach ($duraklar[$tur['tur_id']] as $durak): ?>
                                                <div class="durak-item bekleniyor">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span><strong><?= $durak['sira'] ?>.</strong> <?= $durak['il_adi'] ?></span>
                                                        <span class="badge bg-secondary">
                                                            Planland�
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="<?= $durak['yuk_tipi'] == '��ecek' ? 'yuk-tipi-icecek' : 'yuk-tipi-normal' ?>">
                                                            <?= $durak['yuk_tipi'] ?>
                                                        </span>
                                                        - <?= $durak['yuk_miktari'] ?> kg
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="p-3 text-center text-muted">
                                            Durak bilgisi bulunamad�.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php 
                            endif;
                        endforeach; 
                        
                        if (!$yarin_var):
                        ?>
                            <div class="alert alert-info text-center">
                                Yar�n i�in planlanm�� tur bulunmamaktad�r.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="refresh-info">
            <p>Bu sayfa her 60 saniyede bir otomatik olarak yenilenir. Son g�ncelleme: <span id="son-guncelleme"></span></p>
            <a href="index.php" class="btn btn-primary">Y�netim Paneline D�n</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Saat ve tarih g�ncelleme
        function guncelleZaman() {
            const simdi = new Date();
            
            // Saat
            const saat = simdi.getHours().toString().padStart(2, '0');
            const dakika = simdi.getMinutes().toString().padStart(2, '0');
            const saniye = simdi.getSeconds().toString().padStart(2, '0');
            document.getElementById('saat').textContent = `${saat}:${dakika}:${saniye}`;
            
            // Tarih
            const gun = simdi.getDate().toString().padStart(2, '0');
            const ay = (simdi.getMonth() + 1).toString().padStart(2, '0');
            const yil = simdi.getFullYear();
            
            const gunler = ["Pazar", "Pazartesi", "Sal�", "�ar�amba", "Per�embe", "Cuma", "Cumartesi"];
            const gunAdi = gunler[simdi.getDay()];
            
            document.getElementById('tarih').textContent = `${gun}.${ay}.${yil} ${gunAdi}`;
            
            // Son g�ncelleme
            document.getElementById('son-guncelleme').textContent = `${saat}:${dakika}:${saniye}`;
        }
        
        // Sayfa y�klendi�inde ve her saniye �al��t�r
        guncelleZaman();
        setInterval(guncelleZaman, 1000);
    </script>
</body>
</html>

canli_ekran.php
11. �of�r Ekleme Sayfas�
<?php
require_once 'config.php';

$mesaj = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad = $_POST['ad'] ?? '';
    $soyad = $_POST['soyad'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $ehliyet_no = $_POST['ehliyet_no'] ?? '';
    
    if (empty($ad) || empty($soyad)) {
        $hata = "Ad ve soyad alanlar� zorunludur!";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO soforler (ad, soyad, telefon, ehliyet_no, durum) VALUES (?, ?, ?, ?, 'Aktif')");
            $stmt->execute([$ad, $soyad, $telefon, $ehliyet_no]);
            
            $mesaj = "�of�r ba�ar�yla eklendi!";
            
            // Formu temizle
            $ad = $soyad = $telefon = $ehliyet_no = '';
        } catch (Exception $e) {
            $hata = "Hata olu�tu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>�of�r Ekle - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Yeni �of�r Ekle</h1>
            <div>
                <a href="soforler.php" class="btn btn-secondary">�of�rler</a>
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
                        <input type="text" class="form-control" id="ad" name="ad" value="<?= $ad ?? '' ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="soyad" class="form-label">Soyad</label>
                        <input type="text" class="form-control" id="soyad"

sofor_ekle.php
sofor_ekle.php sayfas� 
<div class="mb-3">
                        <label for="soyad" class="form-label">Soyad</label>
                        <input type="text" class="form-control" id="soyad"
bu a�amada kald�. Tamamlayabilir miyiz?


Context
� none fetched
Tabii, sofor_ekle.php sayfas�n�n devam�n� tamamlayal�m:

                        <input type="text" class="form-control" id="soyad" name="soyad" value="<?= $soyad ?? '' ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefon" class="form-label">Telefon</label>
                        <input type="tel" class="form-control" id="telefon" name="telefon" value="<?= $telefon ?? '' ?>" placeholder="05XX XXX XX XX">
                    </div>
                    
                    <div class="mb-3">
                        <label for="ehliyet_no" class="form-label">Ehliyet No</label>
                        <input type="text" class="form-control" id="ehliyet_no" name="ehliyet_no" value="<?= $ehliyet_no ?? '' ?>">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">�of�r Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

sofor_ekle.php
12. �of�rleri Listeleme Sayfas�
<?php
require_once 'config.php';

// �of�rleri getir
$stmt = $db->query("SELECT * FROM soforler ORDER BY ad, soyad");
$soforler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>�of�rler - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>�of�rler</h1>
            <div>
                <a href="sofor_ekle.php" class="btn btn-primary">Yeni �of�r Ekle</a>
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
                                <th>��lemler</th>
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
                                            <a href="sofor_duzenle.php?id=<?= $sofor['sofor_id'] ?>" class="btn btn-sm btn-warning">D�zenle</a>
                                            <a href="sofor_sil.php?id=<?= $sofor['sofor_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu �of�r� silmek istedi�inize emin misiniz?')">Sil</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Kay�tl� �of�r bulunamad�.</td>
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

soforler.php
13. Ara� Ekleme Sayfas�
<?php
require_once 'config.php';

$mesaj = '';
$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plaka = $_POST['plaka'] ?? '';
    $model = $_POST['model'] ?? '';
    $kapasite = $_POST['kapasite'] ?? '';
    
    if (empty($plaka) || empty($model)) {
        $hata = "Plaka ve model alanlar� zorunludur!";
    } else {
        try {
            $stmt = $db->prepare("INSERT INTO araclar (plaka, model, kapasite, durum) VALUES (?, ?, ?, 'Aktif')");
            $stmt->execute([$plaka, $model, $kapasite]);
            
            $mesaj = "Ara� ba�ar�yla eklendi!";
            
            // Formu temizle
            $plaka = $model = $kapasite = '';
        } catch (Exception $e) {
            $hata = "Hata olu�tu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ara� Ekle - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Yeni Ara� Ekle</h1>
            <div>
                <a href="araclar.php" class="btn btn-secondary">Ara�lar</a>
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
                        <button type="submit" class="btn btn-primary">Ara� Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

arac_ekle.php
14. Ara�lar� Listeleme Sayfas�
<?php
require_once 'config.php';

// Ara�lar� getir
$stmt = $db->query("SELECT * FROM araclar ORDER BY plaka");
$araclar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ara�lar - Lojistik Firma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Ara�lar</h1>
            <div>
                <a href="arac_ekle.php" class="btn btn-primary">Yeni Ara� Ekle</a>
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
                                <th>��lemler</th>
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
                                            <a href="arac_duzenle.php?id=<?= $arac['arac_id'] ?>" class="btn btn-sm btn-warning">D�zenle</a>
                                            <a href="arac_sil.php?id=<?= $arac['arac_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu arac� silmek istedi�inize emin misiniz?')">Sil</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Kay�tl� ara� bulunamad�.</td>
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

araclar.php
Bu �ekilde lojistik firma y�netim sisteminin temel sayfalar�n� tamamlam�� olduk. Sistem �u temel �zelliklere sahip:

Tur planlama ve y�netimi
�of�r y�netimi
Ara� y�netimi
Durak ve teslimat takibi
Canl� ekran ile g�ncel tur durumlar�n� izleme

E�er ileride:

Sistemde yeni �zellikler eklemek
Mevcut �zellikleri geli�tirmek
Raporlama eklemek
Kullan�c� yetkilendirme sistemi eklemek
Mobil uyumlu aray�z� geli�tirme