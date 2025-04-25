<?php
require_once 'config.php';
require_once 'functions.php';

// Yetki kontrolü
if (!oturumAcikMi() || !yetkiKontrol('soforler')) {
    header("Location: login.php");
    exit;
}

// Ceza silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $ceza_id = $_GET['sil'];
    
    // Belge dosyasını kontrol et ve sil
    $stmt = $db->prepare("SELECT belge FROM sofor_cezalar WHERE ceza_id = ?");
    $stmt->execute([$ceza_id]);
    $belge = $stmt->fetchColumn();
    
    if ($belge && file_exists("uploads/cezalar/" . $belge)) {
        unlink("uploads/cezalar/" . $belge);
    }
    
    // Cezayı veritabanından sil
    $stmt = $db->prepare("DELETE FROM sofor_cezalar WHERE ceza_id = ?");
    $stmt->execute([$ceza_id]);
    
    $_SESSION['mesaj'] = "Ceza/masraf kaydı başarıyla silindi.";
    $_SESSION['mesaj_tur'] = "success";
    header("Location: sofor_cezalar.php");
    exit;
}

// Ceza ekleme/güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ceza_id = isset($_POST['ceza_id']) ? $_POST['ceza_id'] : null;
    $sofor_id = $_POST['sofor_id'];
    $tur_id = !empty($_POST['tur_id']) ? $_POST['tur_id'] : null;
    $tarih = $_POST['tarih'];
    $ceza_turu = $_POST['ceza_turu'];
    $aciklama = $_POST['aciklama'];
    $tutar = $_POST['tutar'];
    $odendi_mi = isset($_POST['odendi_mi']) ? 1 : 0;
    $odeme_tarihi = !empty($_POST['odeme_tarihi']) ? $_POST['odeme_tarihi'] : null;
    
    // Belge yükleme işlemi
    $belge = null;
    if (isset($_FILES['belge']) && $_FILES['belge']['error'] === 0) {
        $dosya_adi = $_FILES['belge']['name'];
        $dosya_tmp = $_FILES['belge']['tmp_name'];
        $dosya_boyut = $_FILES['belge']['size'];
        $dosya_uzanti = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));
        
        // İzin verilen dosya uzantıları
        $izin_verilen = array('pdf', 'jpg', 'jpeg', 'png');
        
        if (in_array($dosya_uzanti, $izin_verilen) && $dosya_boyut <= 5000000) { // 5MB limit
            // Uploads klasörü yoksa oluştur
            if (!file_exists('uploads/cezalar')) {
                mkdir('uploads/cezalar', 0777, true);
            }
            
            $yeni_dosya_adi = uniqid() . '.' . $dosya_uzanti;
            $hedef = 'uploads/cezalar/' . $yeni_dosya_adi;
            
            if (move_uploaded_file($dosya_tmp, $hedef)) {
                $belge = $yeni_dosya_adi;
                
                // Eğer güncelleme ise ve yeni belge yüklendiyse eski belgeyi sil
                if ($ceza_id) {
                    $stmt = $db->prepare("SELECT belge FROM sofor_cezalar WHERE ceza_id = ?");
                    $stmt->execute([$ceza_id]);
                    $eski_belge = $stmt->fetchColumn();
                    
                    if ($eski_belge && file_exists("uploads/cezalar/" . $eski_belge)) {
                        unlink("uploads/cezalar/" . $eski_belge);
                    }
                }
            }
        } else {
            $_SESSION['mesaj'] = "Geçersiz dosya formatı veya boyutu. İzin verilen formatlar: PDF, JPG, JPEG, PNG. Maksimum boyut: 5MB.";
            $_SESSION['mesaj_tur'] = "danger";
            header("Location: sofor_cezalar.php");
            exit;
        }
    } elseif ($ceza_id) {
        // Güncelleme durumunda belge yüklenmemişse mevcut belgeyi koru
        $stmt = $db->prepare("SELECT belge FROM sofor_cezalar WHERE ceza_id = ?");
        $stmt->execute([$ceza_id]);
        $belge = $stmt->fetchColumn();
    }
    
    // Veritabanına kaydet
    if ($ceza_id) {
        // Güncelleme
        $stmt = $db->prepare("UPDATE sofor_cezalar SET 
                              sofor_id = ?, tur_id = ?, tarih = ?, ceza_turu = ?, 
                              aciklama = ?, tutar = ?, odendi_mi = ?, odeme_tarihi = ?, 
                              belge = ? WHERE ceza_id = ?");
        $stmt->execute([$sofor_id, $tur_id, $tarih, $ceza_turu, $aciklama, $tutar, $odendi_mi, $odeme_tarihi, $belge, $ceza_id]);
        
        $_SESSION['mesaj'] = "Ceza/masraf kaydı başarıyla güncellendi.";
    } else {
        // Yeni kayıt
        $stmt = $db->prepare("INSERT INTO sofor_cezalar 
                              (sofor_id, tur_id, tarih, ceza_turu, aciklama, tutar, odendi_mi, odeme_tarihi, belge) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$sofor_id, $tur_id, $tarih, $ceza_turu, $aciklama, $tutar, $odendi_mi, $odeme_tarihi, $belge]);
        
        $_SESSION['mesaj'] = "Ceza/masraf kaydı başarıyla eklendi.";
    }
    
    $_SESSION['mesaj_tur'] = "success";
    header("Location: sofor_cezalar.php");
    exit;
}

// Düzenleme için ceza bilgilerini getir
$duzenle = null;
if (isset($_GET['duzenle']) && is_numeric($_GET['duzenle'])) {
    $ceza_id = $_GET['duzenle'];
    $stmt = $db->prepare("SELECT * FROM sofor_cezalar WHERE ceza_id = ?");
    $stmt->execute([$ceza_id]);
    $duzenle = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Şoförleri getir
$stmt = $db->query("SELECT sofor_id, CONCAT(ad, ' ', soyad) as tam_ad FROM soforler ORDER BY ad, soyad");
$soforler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Turları getir
$stmt = $db->query("SELECT tur_id, CONCAT('Tur #', tur_id, ' - ', cikis_tarihi) as tur_bilgisi FROM turlar ORDER BY cikis_tarihi DESC LIMIT 100");
$turlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cezaları listele
$filtre_sofor = isset($_GET['filtre_sofor']) ? $_GET['filtre_sofor'] : '';
$filtre_ceza_turu = isset($_GET['filtre_ceza_turu']) ? $_GET['filtre_ceza_turu'] : '';
$filtre_odeme_durumu = isset($_GET['filtre_odeme_durumu']) ? $_GET['filtre_odeme_durumu'] : '';

$sql = "SELECT sc.*, 
         CONCAT(s.ad, ' ', s.soyad) as sofor_adi,
         CONCAT('Tur #', t.tur_id, ' - ', t.cikis_tarihi) as tur_bilgisi
         FROM sofor_cezalar sc
         LEFT JOIN soforler s ON sc.sofor_id = s.sofor_id
         LEFT JOIN turlar t ON sc.tur_id = t.tur_id
         WHERE 1=1";

$params = [];

if (!empty($filtre_sofor)) {
    $sql .= " AND sc.sofor_id = ?";
    $params[] = $filtre_sofor;
}

if (!empty($filtre_ceza_turu)) {
    $sql .= " AND sc.ceza_turu = ?";
    $params[] = $filtre_ceza_turu;
}

if ($filtre_odeme_durumu !== '') {
    $sql .= " AND sc.odendi_mi = ?";
    $params[] = $filtre_odeme_durumu;
}

$sql .= " ORDER BY sc.tarih DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$cezalar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sayfa başlığı
$sayfa_baslik = "Şoför Cezaları ve Masrafları";
require_once 'header.php';
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Şoför Cezaları ve Masrafları</h1>
    
    <?php if (isset($_SESSION['mesaj'])): ?>
        <div class="alert alert-<?= $_SESSION['mesaj_tur'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['mesaj'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?= $duzenle ? 'Ceza/Masraf Düzenle' : 'Yeni Ceza/Masraf Ekle' ?>
                    </h6>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <?php if ($duzenle): ?>
                            <input type="hidden" name="ceza_id" value="<?= $duzenle['ceza_id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="sofor_id" class="form-label">Şoför</label>
                            <select class="form-select" id="sofor_id" name="sofor_id" required>
                                <option value="">Şoför Seçin</option>
                                <?php foreach ($soforler as $sofor): ?>
                                    <option value="<?= $sofor['sofor_id'] ?>" <?= ($duzenle && $duzenle['sofor_id'] == $sofor['sofor_id']) ? 'selected' : '' ?>>
                                        <?= $sofor['tam_ad'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tur_id" class="form-label">İlgili Tur (Opsiyonel)</label>
                            <select class="form-select" id="tur_id" name="tur_id">
                                <option value="">Tur Seçin</option>
                                <?php foreach ($turlar as $tur): ?>
                                    <option value="<?= $tur['tur_id'] ?>" <?= ($duzenle && $duzenle['tur_id'] == $tur['tur_id']) ? 'selected' : '' ?>>
                                        <?= $tur['tur_bilgisi'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tarih" class="form-label">Tarih</label>
                            <input type="date" class="form-control" id="tarih" name="tarih" value="<?= $duzenle ? $duzenle['tarih'] : date('Y

sofor_cezalar.php
devam edebilir misin? kaldığın yerden


Context
— none fetched
Tabii, kaldığım yerden devam ediyorum:

                        <div class="mb-3">
                            <label for="tarih" class="form-label">Tarih</label>
                            <input type="date" class="form-control" id="tarih" name="tarih" value="<?= $duzenle ? $duzenle['tarih'] : date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ceza_turu" class="form-label">Ceza/Masraf Türü</label>
                            <select class="form-select" id="ceza_turu" name="ceza_turu" required>
                                <option value="">Tür Seçin</option>
                                <option value="Trafik Cezası" <?= ($duzenle && $duzenle['ceza_turu'] == 'Trafik Cezası') ? 'selected' : '' ?>>Trafik Cezası</option>
                                <option value="Trafik Kazası" <?= ($duzenle && $duzenle['ceza_turu'] == 'Trafik Kazası') ? 'selected' : '' ?>>Trafik Kazası</option>
                                <option value="Hasar" <?= ($duzenle && $duzenle['ceza_turu'] == 'Hasar') ? 'selected' : '' ?>>Hasar</option>
                                <option value="Diğer" <?= ($duzenle && $duzenle['ceza_turu'] == 'Diğer') ? 'selected' : '' ?>>Diğer</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="aciklama" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="aciklama" name="aciklama" rows="3" required><?= $duzenle ? $duzenle['aciklama'] : '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tutar" class="form-label">Tutar (TL)</label>
                            <input type="number" step="0.01" class="form-control" id="tutar" name="tutar" value="<?= $duzenle ? $duzenle['tutar'] : '' ?>" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="odendi_mi" name="odendi_mi" <?= ($duzenle && $duzenle['odendi_mi']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="odendi_mi">Ödendi</label>
                        </div>
                        
                        <div class="mb-3" id="odeme_tarihi_div" style="display: <?= ($duzenle && $duzenle['odendi_mi']) ? 'block' : 'none' ?>;">
                            <label for="odeme_tarihi" class="form-label">Ödeme Tarihi</label>
                            <input type="date" class="form-control" id="odeme_tarihi" name="odeme_tarihi" value="<?= $duzenle && $duzenle['odeme_tarihi'] ? $duzenle['odeme_tarihi'] : date('Y-m-d') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="belge" class="form-label">Belge (PDF, JPG, PNG - Max 5MB)</label>
                            <input type="file" class="form-control" id="belge" name="belge">
                            <?php if ($duzenle && $duzenle['belge']): ?>
                                <div class="mt-2">
                                    <a href="uploads/cezalar/<?= $duzenle['belge'] ?>" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-file"></i> Mevcut Belgeyi Görüntüle
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?= $duzenle ? 'Güncelle' : 'Kaydet' ?>
                            </button>
                            <?php if ($duzenle): ?>
                                <a href="sofor_cezalar.php" class="btn btn-secondary">İptal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ceza ve Masraf Listesi</h6>
                </div>
                <div class="card-body">
                    <!-- Filtreler -->
                    <form method="get" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="filtre_sofor" class="form-label">Şoför</label>
                                <select class="form-select" id="filtre_sofor" name="filtre_sofor">
                                    <option value="">Tümü</option>
                                    <?php foreach ($soforler as $sofor): ?>
                                        <option value="<?= $sofor['sofor_id'] ?>" <?= $filtre_sofor == $sofor['sofor_id'] ? 'selected' : '' ?>>
                                            <?= $sofor['tam_ad'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtre_ceza_turu" class="form-label">Ceza/Masraf Türü</label>
                                <select class="form-select" id="filtre_ceza_turu" name="filtre_ceza_turu">
                                    <option value="">Tümü</option>
                                    <option value="Trafik Cezası" <?= $filtre_ceza_turu == 'Trafik Cezası' ? 'selected' : '' ?>>Trafik Cezası</option>
                                    <option value="Trafik Kazası" <?= $filtre_ceza_turu == 'Trafik Kazası' ? 'selected' : '' ?>>Trafik Kazası</option>
                                    <option value="Hasar" <?= $filtre_ceza_turu == 'Hasar' ? 'selected' : '' ?>>Hasar</option>
                                    <option value="Diğer" <?= $filtre_ceza_turu == 'Diğer' ? 'selected' : '' ?>>Diğer</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtre_odeme_durumu" class="form-label">Ödeme Durumu</label>
                                <select class="form-select" id="filtre_odeme_durumu" name="filtre_odeme_durumu">
                                    <option value="">Tümü</option>
                                    <option value="1" <?= $filtre_odeme_durumu === '1' ? 'selected' : '' ?>>Ödendi</option>
                                    <option value="0" <?= $filtre_odeme_durumu === '0' ? 'selected' : '' ?>>Ödenmedi</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="d-grid gap-2 w-100">
                                    <button type="submit" class="btn btn-primary">Filtrele</button>
                                    <a href="sofor_cezalar.php" class="btn btn-secondary">Sıfırla</a>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Ceza Listesi -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="cezalarTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Şoför</th>
                                    <th>Tarih</th>
                                    <th>Tür</th>
                                    <th>Açıklama</th>
                                    <th>Tutar</th>
                                    <th>Ödeme Durumu</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($cezalar) > 0): ?>
                                    <?php foreach ($cezalar as $ceza): ?>
                                        <tr>
                                            <td><?= $ceza['ceza_id'] ?></td>
                                            <td><?= $ceza['sofor_adi'] ?></td>
                                            <td><?= date('d.m.Y', strtotime($ceza['tarih'])) ?></td>
                                            <td>
                                                <span class="badge <?= 
                                                    $ceza['ceza_turu'] == 'Trafik Cezası' ? 'bg-warning' : 
                                                    ($ceza['ceza_turu'] == 'Trafik Kazası' ? 'bg-danger' : 
                                                    ($ceza['ceza_turu'] == 'Hasar' ? 'bg-info' : 'bg-secondary')) 
                                                ?>">
                                                    <?= $ceza['ceza_turu'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= mb_strlen($ceza['aciklama']) > 50 ? mb_substr($ceza['aciklama'], 0, 50) . '...' : $ceza['aciklama'] ?>
                                                <?php if ($ceza['tur_id']): ?>
                                                    <br><small class="text-muted"><?= $ceza['tur_bilgisi'] ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end"><?= number_format($ceza['tutar'], 2, ',', '.') ?> ₺</td>
                                            <td>
                                                <?php if ($ceza['odendi_mi']): ?>
                                                    <span class="badge bg-success">Ödendi</span>
                                                    <br><small><?= date('d.m.Y', strtotime($ceza['odeme_tarihi'])) ?></small>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Ödenmedi</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if ($ceza['belge']): ?>
                                                        <a href="uploads/cezalar/<?= $ceza['belge'] ?>" target="_blank" class="btn btn-sm btn-info" title="Belgeyi Görüntüle">
                                                            <i class="fas fa-file"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="sofor_cezalar.php?duzenle=<?= $ceza['ceza_id'] ?>" class="btn btn-sm btn-primary" title="Düzenle">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" onclick="cezaSil(<?= $ceza['ceza_id'] ?>)" class="btn btn-sm btn-danger" title="Sil">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Kayıt bulunamadı.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Silme Onay Modalı -->
<div class="modal fade" id="silModal" tabindex="-1" aria-labelledby="silModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="silModalLabel">Silme Onayı</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bu ceza/masraf kaydını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <a href="#" id="silOnayBtn" class="btn btn-danger">Sil</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Ödendi checkbox'ı değiştiğinde ödeme tarihi alanını göster/gizle
    document.getElementById('odendi_mi').addEventListener('change', function() {
        document.getElementById('odeme_tarihi_div').style.display = this.checked ? 'block' : 'none';
    });
    
    // Silme onay modalı
    function cezaSil(id) {
        document.getElementById('silOnayBtn').href = 'sofor_cezalar.php?sil=' + id;
        var silModal = new bootstrap.Modal(document.getElementById('silModal'));
        silModal.show();
    }
    
    // DataTables
    $(document).ready(function() {
        $('#cezalarTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Turkish.json"
            },
            "order": [[2, 'desc']], // Tarihe göre sırala
            "pageLength": 25
        });
    });
</script>

<?php require_once 'footer.php'; ?>