<?php
$host = "localhost";
$username = "idjva4aabd_depo";
$password = "88H5aDcNhSxFXSkEYr48";
$database = "idjva4aabd_depo";

try {
    $db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Veritabanı bağlantı hatası: " . $e->getMessage();
    die();
}

// Tarih formatı fonksiyonu
function formatTarih($tarih) {
    if (!$tarih) return "";
    return date("d.m.Y", strtotime($tarih));
}

// Saat formatı fonksiyonu
function formatSaat($saat) {
    if (!$saat) return "";
    return date("H:i", strtotime($saat));
}
?>