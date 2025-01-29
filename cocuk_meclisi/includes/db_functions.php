
<?php
// Çocuk Meclisi ile ilgili veritabanı fonksiyonları

// cocuk_meclisi/includes/db_functions.php

function getCouncilSettings() {
    $db = Database::getInstance();
    $result = $db->query("SELECT baslik, deger FROM cocuk_meclisi_ayarlar")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Varsayılan değerler
    $default_settings = [
        'hero_text' => 'Hayallerimizi birlikte gerçekleştiriyoruz!',
        'dreams_text' => 'Her çocuğun sesi duyulsun, fikirleri değer görsün istiyoruz!',
        'values_text' => 'Sevgi, saygı ve eşitlik bizim en önemli değerlerimiz!',
        'goals_text' => 'Daha güzel bir gelecek için fikirlerimizi paylaşıyoruz!'
    ];
    
    return array_merge($default_settings, $result);
}

function getActiveEvents($limit = null) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM cocuk_meclisi_etkinlikler WHERE durum = 'aktif' ORDER BY tarih DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    return $db->query($sql)->fetchAll();
}

function getCouncilNews($limit = null) {
    $db = Database::getInstance();
    $sql = "SELECT * FROM cocuk_meclisi_haberler WHERE durum = 'aktif' ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    return $db->query($sql)->fetchAll();
}

function getActiveMembers() {
    $db = Database::getInstance();
    return $db->query("SELECT * FROM cocuk_meclisi_uyeler WHERE durum = 'aktif' ORDER BY id ASC")->fetchAll();
}

function getChildRights() {
    $db = Database::getInstance();
    return $db->query("SELECT * FROM cocuk_meclisi_haklar ORDER BY sira ASC")->fetchAll();
}

function submitApplication($data) {
    $db = Database::getInstance();
    return $db->query(
        "INSERT INTO cocuk_meclisi_basvurular (ad_soyad, dogum_tarihi, okul, sinif, veli_ad_soyad, veli_telefon, veli_email, adres, katilim_nedeni) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [
            $data['ad_soyad'],
            $data['dogum_tarihi'],
            $data['okul'],
            $data['sinif'],
            $data['veli_ad_soyad'],
            $data['veli_telefon'],
            $data['veli_email'],
            $data['adres'],
            $data['katilim_nedeni']
        ]
    );
}

function submitContactForm($data) {
    $db = Database::getInstance();
    return $db->query(
        "INSERT INTO cocuk_meclisi_iletisim (ad_soyad, email, telefon, konu, mesaj) 
         VALUES (?, ?, ?, ?, ?)",
        [
            $data['ad_soyad'],
            $data['email'],
            $data['telefon'],
            $data['konu'],
            $data['mesaj']
        ]
    );
}