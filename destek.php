<?php
session_start();
require_once 'config.php';

// Basit admin girişi (gerçek uygulamada daha güvenli bir yöntem kullanılmalıdır)
$admin_username = "admin";
$admin_password = "admin123";

$login_error = '';

// Giriş kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = false;
}

// Giriş formu gönderildi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = 'Geçersiz kullanıcı adı veya şifre!';
    }
}

// Çıkış yapma
if (isset($_GET['logout'])) {
    $_SESSION['admin_logged_in'] = false;
    header("Location: index.php");
    exit;
}

// Şikayet durumunu güncelleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_sikayet']) && $_SESSION['admin_logged_in']) {
    $sikayet_id = $_POST['sikayet_id'];
    $durum = $_POST['durum'];
    $yanit = trim($_POST['yanit']);
    
    try {
        $stmt = $db->prepare("UPDATE sikayetler SET durum = :durum, yanit = :yanit WHERE id = :id");
        $stmt->bindParam(':durum', $durum);
        $stmt->bindParam(':yanit', $yanit);
        $stmt->bindParam(':id', $sikayet_id);
        $stmt->execute();
        
        $success_message = "Şikayet başarıyla güncellendi.";
    } catch(PDOException $e) {
        $error_message = "Güncelleme sırasında bir hata oluştu: " . $e->getMessage();
    }
}

// Geri dönüşüm güncelleme işlemi
if (isset($_POST['update_geridonusum'])) {
    $takip_kodu = $_POST['takip_kodu'];
    $durum = $_POST['durum'];
    $yanit = $_POST['yanit'];
    
    try {
        $stmt = $db->prepare("UPDATE geri_donusum SET durum = :durum, yanit = :yanit WHERE takip_kodu = :takip_kodu");
        $stmt->bindParam(':durum', $durum);
        $stmt->bindParam(':yanit', $yanit);
        $stmt->bindParam(':takip_kodu', $takip_kodu);
        $stmt->execute();
        
        // Başarılı mesajı
        $success_message = "Geri dönüşüm kaydı başarıyla güncellendi.";
        
        // Sayfayı yenile
        echo "<script>window.location.href = '?success=" . urlencode($success_message) . "#geridonusum';</script>";
        exit;
    } catch (PDOException $e) {
        $error_message = "Güncelleme sırasında bir hata oluştu: " . $e->getMessage();
    }
}

// Şikayetleri ve geri dönüşüm bildirimlerini listele
$sikayetler = [];
$geri_donusumler = [];

if ($_SESSION['admin_logged_in']) {
    try {
        $stmt = $db->query("SELECT * FROM sikayetler ORDER BY olusturma_tarihi DESC");
        $sikayetler = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $db->query("SELECT * FROM geri_donusum ORDER BY kayit_tarihi DESC");
        $geri_donusumler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error_message = "Veriler listelenirken bir hata oluştu: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        /* Geri dönüşüm yönetim paneli stilleri */
#geridonusum_table th, #geridonusum_table td {
    vertical-align: middle;
}

.modal-header.bg-success {
    color: white;
}

.modal-header .btn-close-white {
    filter: brightness(0) invert(1);
}

/* Filtreleme alanları */
.filter-section {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

/* Tablo hover efekti */
#geridonusum_table tbody tr:hover {
    background-color: rgba(40, 167, 69, 0.1);
}

/* Durum badge'leri */
.badge {
    font-size: 0.85rem;
    padding: 0.35em 0.65em;
}

/* Ürün resmi */
.product-image-small {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
}

/* Detay modal içeriği */
.modal-body .card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1rem;
}

.modal-body .card-header {
    padding: 0.5rem 1rem;
    font-weight: 500;
}
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Yönetim Paneli</h1>
        
        <?php if (!$_SESSION['admin_logged_in']): ?>
            <!-- Giriş Formu -->
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Yönetici Girişi</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($login_error)): ?>
                                <div class="alert alert-danger"><?php echo $login_error; ?></div>
                            <?php endif; ?>
                            
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Kullanıcı Adı</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Şifre</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary">Giriş Yap</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Admin Paneli -->
            <div class="mb-3">
                <a href="?logout=1" class="btn btn-danger">Çıkış Yap</a>
                <a href="../index.php" class="btn btn-secondary ms-2" target="_blank">Ana Sayfayı Görüntüle</a>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <ul class="nav nav-tabs mb-4" id="adminTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="sikayetler-tab" data-bs-toggle="tab" data-bs-target="#sikayetler" type="button" role="tab" aria-controls="sikayetler" aria-selected="true">Şikayetler</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="geridonusum-tab" data-bs-toggle="tab" data-bs-target="#geridonusum" type="button" role="tab" aria-controls="geridonusum" aria-selected="false">Geri Dönüşüm Bildirimleri</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="urunler-tab" data-bs-toggle="tab" data-bs-target="#urunler" type="button" role="tab" aria-controls="urunler" aria-selected="false">Geri Dönüşüm Ürünleri</button>
                </li>            
                </ul>
            
            <div class="tab-content" id="adminTabContent">
                <!-- Şikayetler Tab -->
                <div class="tab-pane fade show active" id="sikayetler" role="tabpanel" aria-labelledby="sikayetler-tab">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Şikayetler</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($sikayetler)): ?>
                                <div class="alert alert-info">Henüz hiç şikayet bulunmuyor.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Takip Kodu</th>
                                                <th>Ad Soyad</th>
                                                <th>Konu</th>
                                                <th>Durum</th>
                                                <th>Tarih</th>
                                                <th>İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($sikayetler as $sikayet): ?>
                                                <tr>
                                                    <td><?php echo $sikayet['id']; ?></td>
                                                      <td><?php echo htmlspecialchars($sikayet['takip_kodu']); ?></td>
                                                    <td><?php echo htmlspecialchars($sikayet['ad_soyad']); ?></td>
                                                    <td><?php echo htmlspecialchars($sikayet['konu']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $durum_class = '';
                                                        switch($sikayet['durum']) {
                                                            case 'Beklemede':
                                                                $durum_class = 'bg-warning text-dark';
                                                                break;
                                                            case 'İnceleniyor':
                                                                $durum_class = 'bg-info text-white';
                                                                break;
                                                            case 'Çözüldü':
                                                                $durum_class = 'bg-success text-white';
                                                                break;
                                                            case 'Reddedildi':
                                                                $durum_class = 'bg-danger text-white';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $durum_class; ?>"><?php echo htmlspecialchars($sikayet['durum']); ?></span>
                                                    </td>
                                                    <td><?php echo date('d.m.Y H:i', strtotime($sikayet['olusturma_tarihi'])); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sikayetModal<?php echo $sikayet['id']; ?>">
                                                            Detay/Güncelle
                                                        </button>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Şikayet Detay Modal -->
                                                <div class="modal fade" id="sikayetModal<?php echo $sikayet['id']; ?>" tabindex="-1" aria-labelledby="sikayetModalLabel<?php echo $sikayet['id']; ?>" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="sikayetModalLabel<?php echo $sikayet['id']; ?>">Şikayet Detayı</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <form method="post" action="">
                                                                    <input type="hidden" name="sikayet_id" value="<?php echo $sikayet['id']; ?>">
                                                                    
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-6">
                                                                            <p><strong>Takip Kodu:</strong> <?php echo htmlspecialchars($sikayet['takip_kodu']); ?></p>
                                                                            <p><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($sikayet['ad_soyad']); ?></p>
                                                                            <p><strong>E-posta:</strong> <?php echo htmlspecialchars($sikayet['email']); ?></p>
                                                                            <p><strong>Telefon:</strong> <?php echo htmlspecialchars($sikayet['telefon']); ?></p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <p><strong>Konu:</strong> <?php echo htmlspecialchars($sikayet['konu']); ?></p>
                                                                            <p><strong>Oluşturma Tarihi:</strong> <?php echo date('d.m.Y H:i', strtotime($sikayet['kayit_tarihi'])); ?></p>
                                                                            <p><strong>Son Güncelleme:</strong> <?php echo date('d.m.Y H:i', strtotime($sikayet['guncelleme_tarihi'])); ?></p>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label"><strong>Şikayet Mesajı:</strong></label>
                                                                        <div class="border p-2 bg-light"><?php echo nl2br(htmlspecialchars($sikayet['mesaj'])); ?></div>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="durum<?php echo $sikayet['id']; ?>" class="form-label"><strong>Durum:</strong></label>
                                                                        <select class="form-select" id="durum<?php echo $sikayet['id']; ?>" name="durum">
                                                                            <option value="Beklemede" <?php echo ($sikayet['durum'] == 'Beklemede') ? 'selected' : ''; ?>>Beklemede</option>
                                                                            <option value="İnceleniyor" <?php echo ($sikayet['durum'] == 'İnceleniyor') ? 'selected' : ''; ?>>İnceleniyor</option>
                                                                            <option value="Çözüldü" <?php echo ($sikayet['durum'] == 'Çözüldü') ? 'selected' : ''; ?>>Çözüldü</option>
                                                                            <option value="Reddedildi" <?php echo ($sikayet['durum'] == 'Reddedildi') ? 'selected' : ''; ?>>Reddedildi</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="yanit<?php echo $sikayet['id']; ?>" class="form-label"><strong>Yanıt:</strong></label>
                                                                        <textarea class="form-control" id="yanit<?php echo $sikayet['id']; ?>" name="yanit" rows="4"><?php echo htmlspecialchars($sikayet['yanit']); ?></textarea>
                                                                    </div>
                                                                    
                                                                    <button type="submit" name="update_sikayet" class="btn btn-primary">Güncelle</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Geri Dönüşüm Tab -->

<div class="tab-pane fade" id="geridonusum" role="tabpanel" aria-labelledby="geridonusum-tab">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-recycle me-2"></i>Geri Dönüşüm Bildirimleri</h5>
        </div>
        <div class="card-body">
            <!-- Filtreleme Seçenekleri -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <select id="il_filter" class="form-select">
                        <option value="">Tüm İller</option>
                        <?php
                        $il_stmt = $db->query("SELECT DISTINCT i.il_id, i.il_adi FROM geri_donusum g 
                                              JOIN iller i ON g.il_id = i.il_id 
                                              ORDER BY i.il_adi");
                        $iller = $il_stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($iller as $il) {
                            echo '<option value="'.$il['il_id'].'">'.$il['il_adi'].'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="durum_filter" class="form-select">
                        <option value="">Tüm Durumlar</option>
                        <option value="Beklemede">Beklemede</option>
                        <option value="İnceleniyor">İnceleniyor</option>
                        <option value="Onaylandı">Onaylandı</option>
                        <option value="Tamamlandı">Tamamlandı</option>
                        <option value="Reddedildi">Reddedildi</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" id="takip_kodu_filter" class="form-control" placeholder="Takip Kodu Ara...">
                        <button class="btn btn-outline-secondary" type="button" id="search_btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <button id="reset_filters" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-sync-alt me-1"></i>Sıfırla
                    </button>
                </div>
            </div>

            <?php
            // Geri dönüşüm kayıtlarını gruplandırarak al
            $stmt = $db->query("
                SELECT 
                    g.takip_kodu,
                    i.il_adi,
                    g.tarih,
                    g.aciklama,
                    g.kayit_tarihi,
                    g.durum,
                    g.yanit,
                    COUNT(g.id) AS urun_sayisi,
                    SUM(g.toplam_fiyat) AS toplam_tutar
                FROM 
                    geri_donusum g
                JOIN 
                    iller i ON g.il_id = i.il_id
                GROUP BY 
                    g.takip_kodu
                ORDER BY 
                    g.kayit_tarihi DESC
            ");
            $geri_donusumler = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (empty($geri_donusumler)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Henüz hiç geri dönüşüm bildirimi bulunmuyor.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="geridonusum_table">
                        <thead class="table-success">
                            <tr>
                                <th>Takip Kodu</th>
                                <th>İl</th>
                                <th>Tarih</th>
                                <th>Ürün Sayısı</th>
                                <th>Toplam Tutar</th>
                                <th>Durum</th>
                                <th>Kayıt Tarihi</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($geri_donusumler as $geri_donusum): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($geri_donusum['takip_kodu']); ?></td>
                                    <td><?php echo htmlspecialchars($geri_donusum['il_adi']); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($geri_donusum['tarih'])); ?></td>
                                    <td><?php echo $geri_donusum['urun_sayisi']; ?></td>
                                    <td><?php echo number_format($geri_donusum['toplam_tutar'], 2, ',', '.'); ?> €</td>
                                    <td>
                                        <?php
                                        $durum_class = '';
                                        switch($geri_donusum['durum']) {
                                            case 'Beklemede':
                                                $durum_class = 'bg-warning text-dark';
                                                break;
                                            case 'İnceleniyor':
                                                $durum_class = 'bg-info text-white';
                                                break;
                                            case 'Onaylandı':
                                                $durum_class = 'bg-primary text-white';
                                                break;
                                            case 'Tamamlandı':
                                                $durum_class = 'bg-success text-white';
                                                break;
                                            case 'Reddedildi':
                                                $durum_class = 'bg-danger text-white';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $durum_class; ?>"><?php echo htmlspecialchars($geri_donusum['durum']); ?></span>
                                    </td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($geri_donusum['kayit_tarihi'])); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#geridonusumModal<?php echo $geri_donusum['takip_kodu']; ?>">
                                            <i class="fas fa-eye me-1"></i>Detay
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Geri Dönüşüm Detay Modal -->
                                <div class="modal fade" id="geridonusumModal<?php echo $geri_donusum['takip_kodu']; ?>" tabindex="-1" aria-labelledby="geridonusumModalLabel<?php echo $geri_donusum['takip_kodu']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title" id="geridonusumModalLabel<?php echo $geri_donusum['takip_kodu']; ?>">
                                                    <i class="fas fa-recycle me-2"></i>Geri Dönüşüm Bildirimi Detayı
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="post" action="">
                                                    <input type="hidden" name="takip_kodu" value="<?php echo $geri_donusum['takip_kodu']; ?>">
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header bg-light">
                                                                    <strong>Genel Bilgiler</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                    <p><strong>Takip Kodu:</strong> <?php echo htmlspecialchars($geri_donusum['takip_kodu']); ?></p>
                                                                    <p><strong>İl:</strong> <?php echo htmlspecialchars($geri_donusum['il_adi']); ?></p>
                                                                    <p><strong>Tarih:</strong> <?php echo date('d.m.Y', strtotime($geri_donusum['tarih'])); ?></p>
                                                                    <p><strong>Toplam Tutar:</strong> <?php echo number_format($geri_donusum['toplam_tutar'], 2, ',', '.'); ?> €</p>
                                                                    <p><strong>Kayıt Tarihi:</strong> <?php echo date('d.m.Y H:i', strtotime($geri_donusum['kayit_tarihi'])); ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card">
                                                                <div class="card-header bg-light">
                                                                    <strong>Durum Bilgileri</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                    <div class="mb-3">
                                                                        <label for="durum<?php echo $geri_donusum['takip_kodu']; ?>" class="form-label"><strong>Durum:</strong></label>
                                                                        <select class="form-select" id="durum<?php echo $geri_donusum['takip_kodu']; ?>" name="durum">
                                                                            <option value="Beklemede" <?php echo ($geri_donusum['durum'] == 'Beklemede') ? 'selected' : ''; ?>>Beklemede</option>
                                                                            <option value="İnceleniyor" <?php echo ($geri_donusum['durum'] == 'İnceleniyor') ? 'selected' : ''; ?>>İnceleniyor</option>
                                                                            <option value="Onaylandı" <?php echo ($geri_donusum['durum'] == 'Onaylandı') ? 'selected' : ''; ?>>Onaylandı</option>
                                                                            <option value="Tamamlandı" <?php echo ($geri_donusum['durum'] == 'Tamamlandı') ? 'selected' : ''; ?>>Tamamlandı</option>
                                                                            <option value="Reddedildi" <?php echo ($geri_donusum['durum'] == 'Reddedildi') ? 'selected' : ''; ?>>Reddedildi</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label for="yanit<?php echo $geri_donusum['takip_kodu']; ?>" class="form-label"><strong>Yanıt/Not:</strong></label>
                                                                        <textarea class="form-control" id="yanit<?php echo $geri_donusum['takip_kodu']; ?>" name="yanit" rows="4"><?php echo htmlspecialchars($geri_donusum['yanit']); ?></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($geri_donusum['aciklama'])): ?>
                                                    <div class="mb-3">
                                                        <label class="form-label"><strong>Açıklama:</strong></label>
                                                        <div class="border p-2 bg-light"><?php echo nl2br(htmlspecialchars($geri_donusum['aciklama'])); ?></div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Ürün Listesi -->
                                                    <div class="mb-3">
                                                        <label class="form-label"><strong>Ürün Listesi:</strong></label>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Ürün</th>
                                                                        <th>Birim Fiyat</th>
                                                                        <th>Adet</th>
                                                                        <th>Toplam</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $urun_stmt = $db->prepare("
                                                                        SELECT 
                                                                            g.*, 
                                                                            u.urun_adi, 
                                                                            u.birim_fiyat,
                                                                            u.urun_resmi
                                                                        FROM 
                                                                            geri_donusum g
                                                                        JOIN 
                                                                            geri_donusum_urunleri u ON g.urun_id = u.id
                                                                        WHERE 
                                                                            g.takip_kodu = :takip_kodu
                                                                    ");
                                                                    $urun_stmt->bindParam(':takip_kodu', $geri_donusum['takip_kodu']);
                                                                    $urun_stmt->execute();
                                                                    $urunler = $urun_stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                    foreach ($urunler as $urun):
                                                                    ?>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <?php if (!empty($urun['urun_resmi'])): ?>
                                                                                <img src="../uploads/<?php echo htmlspecialchars($urun['urun_resmi']); ?>" 
                                                                                     alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>" 
                                                                                     class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                                                <?php endif; ?>
                                                                                <?php echo htmlspecialchars($urun['urun_adi']); ?>
                                                                            </div>
                                                                        </td>
                                                                        <td><?php echo number_format($urun['birim_fiyat'], 2, ',', '.'); ?> €</td>
                                                                        <td><?php echo $urun['adet']; ?></td>
                                                                        <td><?php echo number_format($urun['toplam_fiyat'], 2, ',', '.'); ?> €</td>
                                                                    </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                                <tfoot class="table-light">
                                                                    <tr>
                                                                        <td colspan="3" class="text-end"><strong>Genel Toplam:</strong></td>
                                                                        <td><strong><?php echo number_format($geri_donusum['toplam_tutar'], 2, ',', '.'); ?> €</strong></td>
                                                                    </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-between">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            <i class="fas fa-times me-1"></i>Kapat
                                                        </button>
                                                        <button type="submit" name="update_geridonusum" class="btn btn-success">
                                                            <i class="fas fa-save me-1"></i>Güncelle
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

                <!-- Tab içeriği olarak eklenecek -->
<div class="tab-pane fade" id="urunler" role="tabpanel" aria-labelledby="urunler-tab">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Geri Dönüşüm Ürünleri</h5>
            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#yeniUrunModal">
                <i class="bi bi-plus-circle"></i> Yeni Ürün Ekle
            </button>
        </div>
        <div class="card-body">
            <?php
            // Ürünleri getir
            try {
                $stmt = $db->query("SELECT * FROM geri_donusum_urunleri ORDER BY urun_adi");
                $urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                echo '<div class="alert alert-danger">Ürünler listelenirken bir hata oluştu: ' . $e->getMessage() . '</div>';
                $urunler = [];
            }
            ?>
            
            <?php if (empty($urunler)): ?>
                <div class="alert alert-info">Henüz hiç geri dönüşüm ürünü eklenmemiş.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Resim</th>
                                <th>Ürün Adı</th>
                                <th>Birim Fiyat</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($urunler as $urun): ?>
                                <tr>
                                    <td><?php echo $urun['id']; ?></td>
                                    <td>
                                        <img src="../uploads/<?php echo htmlspecialchars($urun['urun_resmi']); ?>" 
                                             alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>" 
                                             style="max-width: 100px; max-height: 100px;">
                                    </td>
                                    <td><?php echo htmlspecialchars($urun['urun_adi']); ?></td>
                                    <td><?php echo number_format($urun['birim_fiyat'], 2, ',', '.'); ?> €</td>
                                    <td>
                                        <?php if ($urun['aktif']): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Pasif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#duzenleUrunModal<?php echo $urun['id']; ?>">
                                            Düzenle
                                        </button>
                                        <button type="button" class="btn btn-sm <?php echo $urun['aktif'] ? 'btn-warning' : 'btn-success'; ?>" 
                                                onclick="durumDegistir(<?php echo $urun['id']; ?>, <?php echo $urun['aktif'] ? 0 : 1; ?>)">
                                            <?php echo $urun['aktif'] ? 'Pasif Yap' : 'Aktif Yap'; ?>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Ürün Düzenleme Modal -->
                                <div class="modal fade" id="duzenleUrunModal<?php echo $urun['id']; ?>" tabindex="-1" aria-labelledby="duzenleUrunModalLabel<?php echo $urun['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="duzenleUrunModalLabel<?php echo $urun['id']; ?>">Ürün Düzenle</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form method="post" action="urun_guncelle.php" enctype="multipart/form-data">
                                                    <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label for="urun_adi<?php echo $urun['id']; ?>" class="form-label">Ürün Adı</label>
                                                        <input type="text" class="form-control" id="urun_adi<?php echo $urun['id']; ?>" name="urun_adi" value="<?php echo htmlspecialchars($urun['urun_adi']); ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="birim_fiyat<?php echo $urun['id']; ?>" class="form-label">Birim Fiyat (€)</label>
                                                        <input type="number" step="0.01" class="form-control" id="birim_fiyat<?php echo $urun['id']; ?>" name="birim_fiyat" value="<?php echo $urun['birim_fiyat']; ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="urun_resmi<?php echo $urun['id']; ?>" class="form-label">Ürün Resmi</label>
                                                                 <input type="file" class="form-control" id="urun_resmi<?php echo $urun['id']; ?>" name="urun_resmi">
                                                        <div class="form-text">Yeni bir resim yüklemezseniz mevcut resim korunacaktır.</div>
                                                        <div class="mt-2">
                                                            <label>Mevcut Resim:</label>
                                                            <img src="../uploads/<?php echo htmlspecialchars($urun['urun_resmi']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>" 
                                                                 class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="aktif<?php echo $urun['id']; ?>" name="aktif" value="1" <?php echo $urun['aktif'] ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="aktif<?php echo $urun['id']; ?>">
                                                                Aktif
                                                            </label>
                                                        </div>
                                                    </div>
                                                    
                                                    <button type="submit" class="btn btn-primary">Güncelle</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Yeni Ürün Ekleme Modal -->
<div class="modal fade" id="yeniUrunModal" tabindex="-1" aria-labelledby="yeniUrunModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="yeniUrunModalLabel">Yeni Geri Dönüşüm Ürünü Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="urun_ekle.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="yeni_urun_adi" class="form-label">Ürün Adı</label>
                        <input type="text" class="form-control" id="yeni_urun_adi" name="urun_adi" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="yeni_birim_fiyat" class="form-label">Birim Fiyat (€)</label>
                        <input type="number" step="0.01" class="form-control" id="yeni_birim_fiyat" name="birim_fiyat" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="yeni_urun_resmi" class="form-label">Ürün Resmi</label>
                        <input type="file" class="form-control" id="yeni_urun_resmi" name="urun_resmi" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="yeni_aktif" name="aktif" value="1" checked>
                            <label class="form-check-label" for="yeni_aktif">
                                Aktif
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Ürün Ekle</button>
                </form>
            </div>
        </div>
    </div>
</div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Geri dönüşüm filtreleme
document.addEventListener('DOMContentLoaded', function() {
    const ilFilter = document.getElementById('il_filter');
    const durumFilter = document.getElementById('durum_filter');
    const takipKoduFilter = document.getElementById('takip_kodu_filter');
    const searchBtn = document.getElementById('search_btn');
    const resetBtn = document.getElementById('reset_filters');
    const table = document.getElementById('geridonusum_table');
    
    if (!table) return;
    
    const rows = table.querySelectorAll('tbody tr');
    
    // Filtreleme fonksiyonu
    function filterTable() {
        const ilValue = ilFilter.value;
        const durumValue = durumFilter.value;
        const takipKoduValue = takipKoduFilter.value.toLowerCase();
        
        rows.forEach(row => {
            const il = row.cells[1].textContent;
            const durum = row.cells[5].textContent.trim();
            const takipKodu = row.cells[0].textContent.toLowerCase();
            
            const ilMatch = !ilValue || il === ilFilter.options[ilFilter.selectedIndex].text;
            const durumMatch = !durumValue || durum === durumValue;
            const takipKoduMatch = !takipKoduValue || takipKodu.includes(takipKoduValue);
            
            if (ilMatch && durumMatch && takipKoduMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Filtreleri sıfırlama
    function resetFilters() {
        ilFilter.value = '';
        durumFilter.value = '';
        takipKoduFilter.value = '';
        
        rows.forEach(row => {
            row.style.display = '';
        });
    }
    
    // Event listeners
    if (ilFilter) ilFilter.addEventListener('change', filterTable);
    if (durumFilter) durumFilter.addEventListener('change', filterTable);
    if (searchBtn) searchBtn.addEventListener('click', filterTable);
    if (resetBtn) resetBtn.addEventListener('click', resetFilters);
    
    // Enter tuşu ile arama
    if (takipKoduFilter) {
        takipKoduFilter.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                filterTable();
            }
        });
    }
});
</script>
 <script>
    // Ürün durumunu değiştirme fonksiyonu
function durumDegistir(urunId, durum) {
    if (confirm('Ürün durumunu değiştirmek istediğinize emin misiniz?')) {
        const formData = new FormData();
        formData.append('urun_id', urunId);
        formData.append('durum', durum);
        
        fetch('urun_durum_degistir.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Ürün durumu başarıyla güncellendi.');
                // Sayfayı yenile veya DOM'u güncelle
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            console.error('İstek hatası:', error);
            alert('İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.');
        });
    }
}

// Durum değiştirme butonları için event listener eklemek için örnek kod
document.addEventListener('DOMContentLoaded', function() {
    // Aktif/Pasif butonları için event listener'lar
    const durumButonlari = document.querySelectorAll('.durum-btn');
    
    durumButonlari.forEach(btn => {
        btn.addEventListener('click', function() {
            const urunId = this.getAttribute('data-id');
            const yeniDurum = this.getAttribute('data-durum');
            durumDegistir(urunId, yeniDurum);
        });
    });
});
</script>
</body>
</html>