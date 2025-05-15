<?php
require_once 'config.php';

// Bugün ve yarın için planlanan turları getir
$stmt = $db->query("SELECT t.*,
                     CONCAT(s.ad, ' ', s.soyad) as sofor_adi,
                     a.plaka
                     FROM turlar t
                    LEFT JOIN soforler s ON t.sofor_id = s.sofor_id
                    LEFT JOIN araclar a ON t.arac_id = a.arac_id
                    WHERE t.durum IN ('Planlandı', 'Yolda')
                      AND (t.cikis_tarihi = CURDATE() OR t.cikis_tarihi = DATE_ADD(CURDATE(), INTERVAL 1 DAY))
                    ORDER BY t.cikis_tarihi, t.cikis_saati");
$turlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Durakları getir
$duraklar = [];
if (count($turlar) > 0) {
    $tur_idler = array_column($turlar, 'tur_id');
    $tur_id_str = implode(',', $tur_idler);
    
    // SQL sorgusunda tüm gerekli alanların seçildiğinden emin olun
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

// Yardımcı fonksiyon - Eğer tanımlanmamışsa
if (!function_exists('formatSaat')) {
    function formatSaat($saat) {
        return date('H:i', strtotime($saat));
    }
}

function kisaltIlAdi($ilAdi, $uzunluk = 8) {
    if (strlen($ilAdi) > $uzunluk) {
        return substr($ilAdi, 0, $uzunluk) . '...';
    }
    return $ilAdi;
}

// Şu anki tarih ve saat
$simdikiZaman = date('d.m.Y H:i:s');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canlı Tur Ekranı - Haus des Logistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="refresh" content="60"> <!-- Her 60 saniyede bir sayfayı yenile -->
</head>
<body>
    <div class="container-fluid">
        <header class="main-header">
            <div class="logo-container">
                <img src="img/hausdeslogistics.png" alt="Haus des Logistics Logo" class="company-logo">
                <h1>Haus des Logistics</h1>
            </div>
            <div class="date-time">
                <i class="far fa-clock"></i> <?= $simdikiZaman ?>
                <div class="refresh-info">
                    <small>Sayfa her dakika otomatik yenilenir</small>
                    <button onclick="window.location.reload()" class="btn btn-sm btn-refresh">
                        <i class="fas fa-sync-alt"></i> Yenile
                    </button>
                </div>
            </div>
        </header>
        <div class="dashboard-title">
            <h2><i class="fas fa-truck"></i> Canlı Tur Takip Ekranı</h2>
        </div>
        <div class="tours-container">
            <?php if (empty($turlar)): ?>
                <div class="no-tours">
                    <i class="fas fa-route fa-3x"></i>
                    <p>Bugün ve yarın için planlanmış tur bulunmamaktadır.</p>
                </div>
            <?php else: ?>
                <div class="row tours-grid">
                    <?php foreach ($turlar as $tur): ?>
                    
                        <?php 
                            // Durum sınıflarını belirle
                            $statusClass = '';
                            $statusBadgeClass = '';
                            $statusIcon = '';
                            $statusText = $tur['durum'] ?? 'Bilinmiyor';
                            
                            switch ($tur['durum']) {
                                case 'Planlandı':
                                    $statusClass = 'status-planned';
                                    $statusBadgeClass = 'badge-planned';
                                    $statusIcon = 'fa-calendar-check';
                                    break;
                                case 'Yolda':
                                    $statusClass = 'status-on-road';
                                    $statusBadgeClass = 'badge-on-road';
                                    $statusIcon = 'fa-truck-moving';
                                    break;
                                default:
                                    $statusClass = 'status-unknown';
                                    $statusBadgeClass = 'badge-unknown';
                                    $statusIcon = 'fa-question-circle';
                            }
                            
                            // Paket durumu sınıfları
                            $paketClass = '';
                            if (isset($tur['paket_durumu'])) {
                                switch ($tur['paket_durumu']) {
                                    case 'Hazır':
                                        $paketClass = 'paket-hazir';
                                        break;
                                    case 'Hazırlanıyor':
                                        $paketClass = 'paket-hazirlaniyor';
                                        break;
                                    default:
                                        $paketClass = 'paket-beklemede';
                                }
                            }
                            
                            
                            // Tur tamamlanmış mı?
                            $turTamamlanmis = isset($tur['durum']) && $tur['durum'] === 'Tamamlandı';
                        ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="tour-card <?= $statusClass ?> <?= $turTamamlanmis ? 'tour-completed' : '' ?>">
                                <div class="tour-status-indicator"></div>
                                
                                <?php if (isset($tur['paket_durumu']) && !empty($tur['paket_durumu'])): ?>
                                    <div class="paket-bildirim <?= $paketClass ?>">
                                        <i class="fas fa-box"></i> <?= $tur['paket_durumu'] ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($turTamamlanmis): ?>
                                    <div class="tour-completed-badge">
                                        <i class="fas fa-check"></i> TAMAMLANDI
                                    </div>
                                <?php endif; ?>
                                
                                <div class="tour-header">
                                    <div class="tour-route">
                                        <?php 
                                        // İlk ve son durak bilgilerini al
                                        $ilkDurak = isset($duraklar[$tur['tur_id']]) ? $duraklar[$tur['tur_id']][0] : null;
                                        $sonDurak = isset($duraklar[$tur['tur_id']]) ? end($duraklar[$tur['tur_id']]) : null;
                                        ?>
                                        
                                        <?php if ($ilkDurak && $sonDurak): ?>
                                            <span class="tour-city" title="<?= $ilkDurak['il_adi'] ?? 'Bilinmeyen' ?>">
                                                <?= kisaltIlAdi($ilkDurak['il_adi'] ?? 'Bilinmeyen') ?>
                                            </span>
                                            <span class="tour-arrow">
                                                <i class="fas fa-long-arrow-alt-right"></i>
                                            </span>
                                            <span class="tour-city" title="<?= $sonDurak['il_adi'] ?? 'Bilinmeyen' ?>">
                                                <?= kisaltIlAdi($sonDurak['il_adi'] ?? 'Bilinmeyen') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="tour-city">Rota Bilgisi Yok</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="tour-status <?= $statusBadgeClass ?>">
                                        <i class="fas <?= $statusIcon ?>"></i>
                                        <?= $statusText ?>
                                    </div>
                                </div>
                                
                                <div class="tour-body">
                                    <div class="tour-info-row">
                                        <div class="tour-info-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="tour-info-content">
                                            <span class="tour-info-label">Çıkış:</span>
                                            <?= date('d.m.Y', strtotime($tur['cikis_tarihi'])) ?> <?= formatSaat($tur['cikis_saati']) ?>
                                        </div>
                                    </div>
                                    
                                    <div class="tour-info-row">
                                        <div class="tour-info-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="tour-info-content">
                                            <span class="tour-info-label">Şoför:</span>
                                            <?= $tur['sofor_adi'] ?? 'Atanmadı' ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (isset($tur['plaka']) && !empty($tur['plaka'])): ?>
                                    <div class="tour-info-row">
                                        <div class="tour-info-icon">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                        <div class="tour-info-content">
                                            <span class="tour-info-label">Araç:</span>
                                            <?= $tur['plaka'] ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <!--<?php if (isset($duraklar[$tur['tur_id']])): ?>
                                    <div class="tour-info-row">
                                        <div class="tour-info-icon">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="tour-info-content">
                                            <span class="tour-info-label">Durak Sayısı:</span>
                                            <?= count($duraklar[$tur['tur_id']]) ?>
                                            <?php if (isset($tur['nakit_tahsilat']) && $tur['nakit_tahsilat'] > 0): ?>
                                                <span class="badge bg-warning text-dark ms-2">BAR</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>-->
                                </div>
                                
                                <div class="tour-footer">
                                    <span>
                                        <i class="fas fa-hashtag"></i>
                                       <a href="tur_takip.php?kod=<?= $tur['takip_kodu'] ?>" class="badge bg-info text-dark text-decoration-none"> Tur #<?= $tur['tur_id'] ?> Detay</a>
                                    </span>
                                    
                                    <?php if (isset($tur['guncelleme_zamani'])): ?>
                                    <span title="Son Güncelleme">
                                        <i class="fas fa-history"></i>
                                        <?= date('H:i', strtotime($tur['guncelleme_zamani'])) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <footer class="main-footer">
            <p>&copy; <?= date('Y') ?> Haus des Logistics - Canlı Tur Takip Sistemi</p>
        </footer>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sayfa yüklendiğinde çalışacak JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Kalan süreyi gösteren sayaç
            let countdownElement = document.createElement('span');
            countdownElement.className = 'countdown';
            document.querySelector('.refresh-info').appendChild(countdownElement);
            
            let countdown = 60; // 60 saniye
            
            function updateCountdown() {
                countdownElement.textContent = `(${countdown}s)`;
                countdown--;
                
                if (countdown < 0) {
                    countdown = 60;
                }
            }
            
            // Her saniye sayacı güncelle
            setInterval(updateCountdown, 1000);
            updateCountdown(); // İlk çağrı
        });
    </script>
</body>
