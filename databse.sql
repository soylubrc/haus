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