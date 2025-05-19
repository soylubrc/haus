<?php
require_once 'config.php';

// İlleri getir
$iller_query = $db->query("SELECT * FROM iller ORDER BY il_adi ASC");
$iller = $iller_query->fetchAll(PDO::FETCH_ASSOC);

// Araçları getir
$araclar_query = $db->query("SELECT * FROM araclar WHERE durum = 'Aktif' ORDER BY plaka ASC");
$araclar = $araclar_query->fetchAll(PDO::FETCH_ASSOC);

// Şoförleri getir
$soforler_query = $db->query("SELECT * FROM soforler WHERE durum = 'Aktif' ORDER BY ad, soyad ASC");
$soforler = $soforler_query->fetchAll(PDO::FETCH_ASSOC);

// Personeli getir
$personel_query = $db->query("SELECT * FROM personel WHERE aktif = 1 ORDER BY ad, soyad ASC");
$personel = $personel_query->fetchAll(PDO::FETCH_ASSOC);

// Yük tiplerini getir
$yuk_tipleri_query = $db->query("SELECT * FROM yuk_tipleri ORDER BY tip_adi ASC");
$yuk_tipleri = $yuk_tipleri_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tur Oluşturma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <style>
        .drag-container {
            min-height: 150px;
            border: 2px dashed #ccc;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .drag-item {
            padding: 8px 12px;
            margin: 5px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: move;
            display: inline-block;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .drag-item:hover {
            background-color: #f0f0f0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .drag-item.selected {
            background-color: #e7f3ff;
            border-color: #007bff;
        }
        .durak-item {
            background-color: #e7f7e7;
            border-left: 4px solid #28a745;
        }
        .arac-item {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .sofor-item {
            background-color: #cce5ff;
            border-left: 4px solid #007bff;
        }
        .personel-item {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .selected-container {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .select2-container {
            width: 100% !important;
        }
        .durak-sira {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #28a745;
        }
        .durak-sira .handle {
            cursor: move;
            margin-right: 10px;
            color: #6c757d;
        }
        .durak-sira .remove-durak {
            margin-left: auto;
            color: #dc3545;
            cursor: pointer;
        }
        .section-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 15px;
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <h2 class="text-center mb-4">Yeni Tur Oluştur</h2>
        
        <form id="turForm" action="tur_kaydet.php" method="post">
            <div class="row">
                <!-- Sol Taraf - Seçim Alanları -->
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">İller</h5>
                        </div>
                        <div class="card-body">
                            <select id="ilSecimi" class="form-control select2">
                                <option value="">İl Seçiniz</option>
                                <?php foreach ($iller as $il): ?>
                                <option value="<?= $il['il_id'] ?>" data-uzaklik="<?= $il['merkeze_uzaklik'] ?>"><?= $il['il_adi'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" id="ilEkleBtn" class="btn btn-success mt-2">Durak Olarak Ekle</button>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Araçlar</h5>
                        </div>
                        <div class="card-body">
                            <div class="drag-container" id="araclarContainer">
                                <?php foreach ($araclar as $arac): ?>
                                <div class="drag-item arac-item" data-id="<?= $arac['arac_id'] ?>" data-type="arac">
                                    <?= $arac['plaka'] ?> - <?= $arac['kapasite'] ?> kg)
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Şoförler</h5>
                        </div>
                        <div class="card-body">
                            <div class="drag-container" id="soforlerContainer">
                                <?php foreach ($soforler as $sofor): ?>
                                <div class="drag-item sofor-item" data-id="<?= $sofor['sofor_id'] ?>" data-type="sofor">
                                    <?= $sofor['ad'] ?> <?= $sofor['soyad'] ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Personel</h5>
                        </div>
                        <div class="card-body">
                            <div class="drag-container" id="personelContainer">
                                <?php foreach ($personel as $p): ?>
                                <div class="drag-item personel-item" data-id="<?= $p['id'] ?>" data-type="personel">
                                    <?= $p['ad'] ?> <?= $p['soyad'] ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sağ Taraf - Seçilen Öğeler ve Form -->
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Tur Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="cikisTarihi" class="form-label">Çıkış Tarihi</label>
                                    <input type="date" class="form-control" id="cikisTarihi" name="cikisTarihi" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="cikisSaati" class="form-label">Çıkış Saati</label>
                                    <input type="time" class="form-control" id="cikisSaati" name="cikisSaati" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tahminiDonusTarihi" class="form-label">Tahmini Dönüş Tarihi</label>
                                    <input type="date" class="form-control" id="tahminiDonusTarihi" name="tahminiDonusTarihi">
                                </div>
                                <div class="col-md-6">
                                    <label for="tahminiDonusSaati" class="form-label">Tahmini Dönüş Saati</label>
                                    <input type="time" class="form-control" id="tahminiDonusSaati" name="tahminiDonusSaati">
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="nakitTahsilat" name="nakitTahsilat">
                                <label class="form-check-label" for="nakitTahsilat">
                                    Nakit Tahsilat Yapılacak
                                </label>
                            </div>
                            
                            <div id="nakitTutarDiv" class="mb-3" style="display: none;">
                                <label for="nakitTutar" class="form-label">Nakit Tahsilat Tutarı (TL)</label>
                                <input type="number" step="0.01" class="form-control" id="nakitTutar" name="nakitTutar">
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Duraklar</h5>
                        </div>
                        <div class="card-body">
                            <div id="durakListesi" class="mb-3">
                                <!-- Duraklar buraya eklenecek -->
                                <div class="alert alert-info">Henüz durak eklenmedi. Lütfen sol taraftan il seçerek durak ekleyin.</div>
                            </div>
                            <div class="alert alert-warning">
                                <strong>Not:</strong> Durakları sürükleyerek sıralayabilirsiniz.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">Seçilen Araç</h5>
                                </div>
                                <div class="card-body selected-container" id="seciliArac">
                                    <div class="alert alert-info">Henüz araç seçilmedi. Lütfen sol taraftan bir araç sürükleyin.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Seçilen Şoför</h5>
                                </div>
                                  <div class="card-body selected-container" id="seciliSofor">
                                    <div class="alert alert-info">Henüz şoför seçilmedi. Lütfen sol taraftan bir şoför sürükleyin.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0">Tur Hazırlayan</h5>
                                </div>
                                <div class="card-body selected-container" id="seciliHazirlayan">
                                    <div class="alert alert-info">Henüz tur hazırlayan seçilmedi. Lütfen sol taraftan bir personel sürükleyin.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0">Depo Sorumlusu</h5>
                                </div>
                                <div class="card-body selected-container" id="seciliDepoSorumlusu">
                                    <div class="alert alert-info">Henüz depo sorumlusu seçilmedi. Lütfen sol taraftan bir personel sürükleyin.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Tur Özeti</h5>
                        </div>
                        <div class="card-body">
                            <div id="turOzeti">
                                <div class="alert alert-info">Tur bilgilerini tamamladıktan sonra özet burada görüntülenecektir.</div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="button" id="ozetGosterBtn" class="btn btn-primary">Özeti Göster</button>
                                <button type="submit" id="turKaydetBtn" class="btn btn-success">Turu Kaydet</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gizli input alanları -->
            <input type="hidden" id="seciliAracId" name="aracId">
            <input type="hidden" id="seciliSoforId" name="soforId">
            <input type="hidden" id="seciliHazirlayanId" name="hazirlayanId">
            <input type="hidden" id="seciliDepoSorumlusuId" name="depoSorumlusuId">
            <input type="hidden" id="duraklar" name="duraklar">
            <input type="hidden" id="toplamMesafe" name="toplamMesafe">
        </form>
        
        <!-- Durak Detay Modalı -->
        <div class="modal fade" id="durakDetayModal" tabindex="-1" aria-labelledby="durakDetayModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="durakDetayModalLabel">Durak Detayları</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="durakIndex">
                        <div class="mb-3">
                            <label for="yukTipi" class="form-label">Yük Tipi</label>
                            <select class="form-control" id="yukTipi">
                                <option value="">Yük Tipi Seçiniz</option>
                                <?php foreach ($yuk_tipleri as $yuk_tipi): ?>
                                <option value="<?= $yuk_tipi['yuk_tip_id'] ?>"><?= $yuk_tipi['tip_adi'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="yukMiktari" class="form-label">Yük Miktarı (Ton)</label>
                            <input type="number" step="0.01" class="form-control" id="yukMiktari">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="button" class="btn btn-primary" id="durakDetayKaydet">Kaydet</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            // Select2 başlat
            $('.select2').select2();
            
            // Duraklar dizisi
            let duraklar = [];
            
            // Nakit tahsilat checkbox kontrolü
            $('#nakitTahsilat').change(function() {
                if($(this).is(':checked')) {
                    $('#nakitTutarDiv').show();
                } else {
                    $('#nakitTutarDiv').hide();
                    $('#nakitTutar').val('');
                }
            });
            
            // İl ekle butonu
            $('#ilEkleBtn').click(function() {
                const ilId = $('#ilSecimi').val();
                const ilAdi = $('#ilSecimi option:selected').text();
                const uzaklik = $('#ilSecimi option:selected').data('uzaklik');
                
                if (ilId) {
                    // Durak nesnesini oluştur
                    const durak = {
                        il_id: ilId,
                        il_adi: ilAdi,
                        uzaklik: uzaklik,
                        sira: duraklar.length + 1,
                        yuk_tip_id: '',
                        yuk_miktari: ''
                    };
                    
                    // Duraklar dizisine ekle
                    duraklar.push(durak);
                    
                    // Durakları yeniden çiz
                    durakListesiniGuncelle();
                    
                    // Select2'yi temizle
                    $('#ilSecimi').val(null).trigger('change');
                }
            });
            
            // Durak listesini güncelleme fonksiyonu
            function durakListesiniGuncelle() {
                if (duraklar.length === 0) {
                    $('#durakListesi').html('<div class="alert alert-info">Henüz durak eklenmedi. Lütfen sol taraftan il seçerek durak ekleyin.</div>');
                    return;
                }
                
                let html = '';
                duraklar.forEach((durak, index) => {
                    html += `
                    <div class="durak-sira" data-index="${index}">
                        <span class="handle"><i class="fas fa-grip-lines"></i> ≡</span>
                        <span class="ms-2">${durak.sira}. ${durak.il_adi}</span>
                        <span class="ms-3 badge bg-secondary">${durak.uzaklik} km</span>
                        <span class="ms-3">
                            ${durak.yuk_tip_id ? `<span class="badge bg-success">Yük: ${durak.yuk_miktari} kg</span>` : '<span class="badge bg-warning">Yük detayı yok</span>'}
                        </span>
                        <button type="button" class="btn btn-sm btn-info ms-2 durak-detay-btn" data-index="${index}">Detay</button>
                        <span class="remove-durak" data-index="${index}"><i class="fas fa-times"></i> ✕</span>
                    </div>
                    `;
                });
                
                $('#durakListesi').html(html);
                
                // Durakları sürüklenebilir yap
                $('#durakListesi').sortable({
                    handle: '.handle',
                    update: function(event, ui) {
                        // Sıralamayı güncelle
                        const yeniDuraklar = [];
                        $('#durakListesi .durak-sira').each(function(index) {
                            const eskiIndex = $(this).data('index');
                            const durak = {...duraklar[eskiIndex]};
                            durak.sira = index + 1;
                            yeniDuraklar.push(durak);
                        });
                        duraklar = yeniDuraklar;
                        durakListesiniGuncelle();
                    }
                });
                
                // Durak silme butonları
                $('.remove-durak').click(function() {
                    const index = $(this).data('index');
                    duraklar.splice(index, 1);
                    // Sıra numaralarını güncelle
                    duraklar.forEach((durak, i) => {
                        durak.sira = i + 1;
                    });
                    durakListesiniGuncelle();
                });
                
                // Durak detay butonları
                $('.durak-detay-btn').click(function() {
                    const index = $(this).data('index');
                    $('#durakIndex').val(index);
                    $('#yukTipi').val(duraklar[index].yuk_tip_id || '');
                    $('#yukMiktari').val(duraklar[index].yuk_miktari || '');
                    $('#durakDetayModal').modal('show');
                });
                
                // Toplam mesafeyi hesapla
                hesaplaToplamMesafe();
            }
            
            // Durak detaylarını kaydet
            $('#durakDetayKaydet').click(function() {
                const index = $('#durakIndex').val();
                const yukTipi = $('#yukTipi').val();
                const yukMiktari = $('#yukMiktari').val();
                
                duraklar[index].yuk_tip_id = yukTipi;
                duraklar[index].yuk_miktari = yukMiktari;
                
                durakListesiniGuncelle();
                $('#durakDetayModal').modal('hide');
            });
            
            // Toplam mesafeyi hesaplama
            function hesaplaToplamMesafe() {
                let toplamMesafe = 0;
                duraklar.forEach(durak => {
                    toplamMesafe += parseInt(durak.uzaklik);
                });
                $('#toplamMesafe').val(toplamMesafe);
                return toplamMesafe;
            }
            
            // Sürükle bırak işlemleri
            $('.drag-item').draggable({
                helper: 'clone',
                revert: 'invalid',
                cursor: 'move'
            });
            
            // Araç hedef alanı
            $('#seciliArac').droppable({
                accept: '.arac-item',
                drop: function(event, ui) {
                    const aracId = $(ui.draggable).data('id');
                    const aracText = $(ui.draggable).text();
                    
                    $('#seciliArac').html(`
                        <div class="selected-item">
                            <strong>Araç:</strong> ${aracText}
                            <button type="button" class="btn btn-sm btn-danger float-end" id="aracKaldir">Kaldır</button>
                        </div>
                    `);
                    
                    $('#seciliAracId').val(aracId);
                    
                    // Kaldır butonu
                    $('#aracKaldir').click(function() {
                        $('#seciliArac').html('<div class="alert alert-info">Henüz araç seçilmedi. Lütfen sol taraftan bir araç sürükleyin.</div>');
                        $('#seciliAracId').val('');
                    });
                }
            });
            
            // Şoför hedef alanı
            $('#seciliSofor').droppable({
                accept: '.sofor-item',
                drop: function(event, ui) {
                    const soforId = $(ui.draggable).data('id');
                    const soforText = $(ui.draggable).text();
                    
                    $('#seciliSofor').html(`
                        <div class="selected-item">
                            <strong>Şoför:</strong> ${soforText}
                            <button type="button" class="btn btn-sm btn-danger float-end" id="soforKaldir">Kaldır</button>
                        </div>
                    `);
                    
                    $('#seciliSoforId').val(soforId);
                    
                    // Kaldır butonu
                    $('#soforKaldir').click(function() {
                        $('#seciliSofor').html('<div class="alert alert-info">Henüz şoför seçilmedi. Lütfen sol taraftan bir şoför sürükleyin.</div>');
                        $('#seciliSoforId').val('');
                    });
                }
            });
            
            // Tur hazırlayan hedef alanı
            $('#seciliHazirlayan').droppable({
                accept: '.personel-item',
                drop: function(event, ui) {
                    const personelId = $(ui.draggable).data('id');
                    const personelText = $(ui.draggable).text();
                    
                    $('#seciliHazirlayan').html(`
                        <div class="selected-item">
                            <strong>Hazırlayan:</strong> ${personelText}
                            <button type="button" class="btn btn-sm btn-danger float-end" id="hazirlayanKaldir">Kaldır</button>
                        </div>
                    `);
                    
                    $('#seciliHazirlayanId').val(personelId);
                    
                    // Kaldır butonu
                      $('#hazirlayanKaldir').click(function() {
                        $('#seciliHazirlayan').html('<div class="alert alert-info">Henüz tur hazırlayan seçilmedi. Lütfen sol taraftan bir personel sürükleyin.</div>');
                        $('#seciliHazirlayanId').val('');
                    });
                }
            });
            
            // Depo sorumlusu hedef alanı
            $('#seciliDepoSorumlusu').droppable({
                accept: '.personel-item',
                drop: function(event, ui) {
                    const personelId = $(ui.draggable).data('id');
                    const personelText = $(ui.draggable).text();
                    
                    $('#seciliDepoSorumlusu').html(`
                        <div class="selected-item">
                            <strong>Depo Sorumlusu:</strong> ${personelText}
                            <button type="button" class="btn btn-sm btn-danger float-end" id="depoSorumlusuKaldir">Kaldır</button>
                        </div>
                    `);
                    
                    $('#seciliDepoSorumlusuId').val(personelId);
                    
                    // Kaldır butonu
                    $('#depoSorumlusuKaldir').click(function() {
                        $('#seciliDepoSorumlusu').html('<div class="alert alert-info">Henüz depo sorumlusu seçilmedi. Lütfen sol taraftan bir personel sürükleyin.</div>');
                        $('#seciliDepoSorumlusuId').val('');
                    });
                }
            });
            
            // Özet göster butonu
            $('#ozetGosterBtn').click(function() {
                // Gerekli alanların kontrolü
                if (!$('#seciliAracId').val()) {
                    alert('Lütfen bir araç seçiniz!');
                    return;
                }
                
                if (!$('#seciliSoforId').val()) {
                    alert('Lütfen bir şoför seçiniz!');
                    return;
                }
                
                if (!$('#seciliHazirlayanId').val()) {
                    alert('Lütfen bir tur hazırlayan personel seçiniz!');
                    return;
                }
                
                if (!$('#seciliDepoSorumlusuId').val()) {
                    alert('Lütfen bir depo sorumlusu personel seçiniz!');
                    return;
                }
                
                if (duraklar.length === 0) {
                    alert('Lütfen en az bir durak ekleyiniz!');
                    return;
                }
                
                if (!$('#cikisTarihi').val() || !$('#cikisSaati').val()) {
                    alert('Lütfen çıkış tarih ve saatini belirtiniz!');
                    return;
                }
                
                // Durakları JSON olarak hidden input'a ekle
                $('#duraklar').val(JSON.stringify(duraklar));
                
                // Özet oluştur
                const aracText = $('#seciliArac .selected-item').text().replace('Kaldır', '').trim();
                const soforText = $('#seciliSofor .selected-item').text().replace('Kaldır', '').trim();
                const hazirlayanText = $('#seciliHazirlayan .selected-item').text().replace('Kaldır', '').trim();
                const depoSorumlusuText = $('#seciliDepoSorumlusu .selected-item').text().replace('Kaldır', '').trim();
                
                let durakListesi = '';
                duraklar.forEach(durak => {
                    durakListesi += `<li>${durak.sira}. ${durak.il_adi} (${durak.uzaklik} km)</li>`;
                });
                
                const toplamMesafe = hesaplaToplamMesafe();
                const nakitTahsilat = $('#nakitTahsilat').is(':checked') ? 'Evet - ' + $('#nakitTutar').val() + ' TL' : 'Hayır';
                
                const ozet = `
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tur Özeti</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Araç:</strong> ${aracText}</p>
                                <p><strong>Şoför:</strong> ${soforText}</p>
                                <p><strong>Tur Hazırlayan:</strong> ${hazirlayanText}</p>
                                <p><strong>Depo Sorumlusu:</strong> ${depoSorumlusuText}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Çıkış:</strong> ${$('#cikisTarihi').val()} - ${$('#cikisSaati').val()}</p>
                                <p><strong>Tahmini Dönüş:</strong> ${$('#tahminiDonusTarihi').val() || 'Belirtilmedi'} - ${$('#tahminiDonusSaati').val() || 'Belirtilmedi'}</p>
                                <p><strong>Toplam Mesafe:</strong> ${toplamMesafe} km</p>
                                <p><strong>Nakit Tahsilat:</strong> ${nakitTahsilat}</p>
                            </div>
                        </div>
                        <h6>Duraklar:</h6>
                        <ol>${durakListesi}</ol>
                    </div>
                </div>
                `;
                
                $('#turOzeti').html(ozet);
            });
            
            // Form gönderilmeden önce kontrol
            $('#turForm').submit(function(e) {
                // Özet göster butonuna tıklanmış gibi kontrolleri yap
                $('#ozetGosterBtn').click();
                
                // Eğer nakitTahsilat seçili ve tutar girilmemişse uyarı ver
                if ($('#nakitTahsilat').is(':checked') && !$('#nakitTutar').val()) {
                    alert('Lütfen nakit tahsilat tutarını giriniz!');
                    e.preventDefault();
                    return false;
                }
                
                // Durakları JSON olarak hidden input'a ekle (tekrar)
                $('#duraklar').val(JSON.stringify(duraklar));
                
                return true;
            });
        });
    </script>
</body>
</html>