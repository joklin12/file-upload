<?php
/**
 * @package     ShortLink Sibermu
 * @author      Joko Supriyanto <joko@sibermu.ac.id>
 * @copyright   Copyright (C) Juni 2025 Biro Sistem Informasi SiberMu. All rights reserved.
 * @license     Commercial License
 */
 
//Fungsi: menarik rss berita dari web SiberMu.ac.id, menarik gambar langsung tidak bisa karena ada proteksi HotLink dari CMS WP
// Ambil URL gambar target dari parameter 'url'
$imageUrl = $_GET['url'] ?? '';

// --- Pemeriksaan Keamanan Sederhana ---
// Pastikan URL tidak kosong, valid, dan hanya dari domain yang diizinkan.
if (empty($imageUrl) || !filter_var($imageUrl, FILTER_VALIDATE_URL) || !str_starts_with($imageUrl, 'https://sibermu.ac.id/')) {
    // Jika tidak valid, kirim error "Bad Request". Ini mencegah script Anda disalahgunakan.
    http_response_code(400);
    // Anda bisa juga menampilkan gambar placeholder untuk error
    // header('Content-Type: image/png');
    // readfile('assets/image_error.png');
    exit('Invalid or disallowed image URL.');
}

// Ambil konten gambar dari URL target
// Tanda '@' digunakan untuk menekan warning jika file_get_contents gagal
$imageData = @file_get_contents($imageUrl);

// Jika gagal mengambil gambar, kirim error "Not Found"
if ($imageData === false) {
    http_response_code(404);
    // Anda bisa juga menampilkan gambar placeholder untuk "tidak ditemukan"
    // header('Content-Type: image/png');
    // readfile('assets/image_not_found.png');
    exit('Image could not be fetched.');
}

// Tentukan tipe konten (Content-Type) berdasarkan ekstensi file gambar
$extension = strtolower(pathinfo($imageUrl, PATHINFO_EXTENSION));
$contentType = 'image/jpeg'; // Default
if ($extension === 'png') {
    $contentType = 'image/png';
} elseif ($extension === 'gif') {
    $contentType = 'image/gif';
} elseif ($extension === 'webp') {
    $contentType = 'image/webp';
}

// Kirim header tipe konten yang benar ke browser
header('Content-Type: ' . $contentType);
// Tampilkan data gambar
echo $imageData;