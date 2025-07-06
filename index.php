<?php
/**
 * @package     File Upload
 * @author      Joko Supriyanto <joko@sibermu.ac.id>
 * @copyright   Copyright (C) Juni 2025 Biro Sistem Informasi SiberMu. All rights reserved.
 * @license     GNU
 */
// Mulai session di baris paling atas
session_start();
// Atur zona waktu ke Waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');

$upload_dir = 'uploads/';

// --- BARU: LOGIKA UNTUK MENGAMBIL BERITA DARI SITEMAP XML ---
$news_items = [];
try {
    // Ambil konten XML dari URL
    $xml_string = @file_get_contents('https://sibermu.ac.id/post-sitemap.xml');

    if ($xml_string !== false) {
        // Load XML string, libxml_nocompact untuk performa lebih baik
       $xml = simplexml_load_string($xml_string, "SimpleXMLElement", LIBXML_NOCDATA);
        
        // Daftarkan namespace 'image' agar bisa diakses
        $xml->registerXPathNamespace('image', 'http://www.google.com/schemas/sitemap-image/1.1');
        
        $count = 0;
        foreach ($xml->url as $url_node) {
            if ($count >= 4) {
                break; // Ambil 3 berita saja
            }

            // Ambil gambar pertama
            // Menggunakan XPath untuk langsung mengambil URL gambar pertama dari tag <image:loc>
            $image_nodes = $url_node->xpath('image:image/image:loc');
            $image_url = !empty($image_nodes) ? (string)$image_nodes[0] : 'assets/default_news.png'; // Gunakan gambar default jika tidak ada

            // Ambil data lain
            $link = (string)$url_node->loc;
            $lastmod = (string)$url_node->lastmod;
            
            // Format tanggal menjadi lebih mudah dibaca
            $date_formatted = date('d F Y', strtotime($lastmod));

            // Ekstrak dan format judul dari URL
            $path = parse_url($link, PHP_URL_PATH);
            $slug = basename($path);
            $title = ucwords(str_replace('-', ' ', $slug));

            // Simpan ke dalam array
            $news_items[] = [
                'title' => $title,
                'link' => $link,
                'image' => $image_url,
                'date' => $date_formatted
            ];
            
            $count++;
        }
    }
} catch (Exception $e) {
    // Jika terjadi error saat mengambil atau parsing XML, biarkan $news_items kosong
    // Sebaiknya log error ini untuk keperluan debugging
    error_log('Gagal mengambil berita: ' . $e->getMessage());
}

// Proses HANYA jika ada kiriman form (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['fileToUpload'])) {
        if ($_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
            $file_name = basename($_FILES['fileToUpload']['name']);
            $target_path = $upload_dir . $file_name;
            if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_path)) {
                $_SESSION['message'] = "<strong>Diterima!</strong> File <strong>" . htmlspecialchars($file_name, ENT_QUOTES, 'UTF-8') . "</strong> berhasil diunggah.";
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = "<strong>Ditolak!</strong> Terjadi kesalahan saat menyimpan file.";
                $_SESSION['message_type'] = 'danger';
            }
        } else {
            $_SESSION['message_type'] = 'danger';
            switch ($_FILES['fileToUpload']['error']) {
                case UPLOAD_ERR_INI_SIZE: $_SESSION['message'] = "<strong>Ditolak!</strong> Ukuran file melebihi batas maksimal server."; break;
                case UPLOAD_ERR_NO_FILE: $_SESSION['message'] = "<strong>Gagal!</strong> Tidak ada file yang dipilih untuk diunggah."; break;
                default: $_SESSION['message'] = "<strong>Ditolak!</strong> Terjadi kesalahan yang tidak diketahui."; break;
            }
        }
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Helper functions (tidak ada perubahan)
function getFileIconClass($filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch ($extension) {
        case 'pdf': return 'bi-file-earmark-pdf-fill text-danger';
        case 'jpg': case 'jpeg': case 'png': case 'gif': return 'bi-file-earmark-image text-info';
        case 'doc': case 'docx': return 'bi-file-earmark-word-fill text-primary';
        case 'xls': case 'xlsx': return 'bi-file-earmark-excel-fill text-success';
        case 'zip': case 'rar': return 'bi-file-earmark-zip-fill text-secondary';
        default: return 'bi-file-earmark-text';
    }
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unggah File Lembar Observasi PeerTeaching</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="icon" type="image/png" href="assets/favicon.png">    
    <style>
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        body {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #fff;
            min-height: 100vh;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        .form-label {
            color: #eee;
        }

        .btn-upload {
            background: linear-gradient(45deg, #8a23d5, #e73c7e);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-upload:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(231, 60, 126, 0.7);
        }

        .list-group-item {
            background-color: rgba(255, 255, 255, 0.15);
            border: none;
            color: #fff;
            transition: background-color 0.3s ease;
        }

        .list-group-item:hover {
            background-color: rgba(255, 255, 255, 0.25);
        }

        .file-info h6 {
            color: #fff;
        }

        .file-info small {
            color: #ddd;
        }

        .badge {
            background-color: rgba(0, 0, 0, 0.2) !important;
        }
        
        .footer-text {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .number-box {
            background-color: rgba(0, 0, 0, 0.2);
            color: #fff;
            font-weight: bold;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem; /* 6px */
            margin-right: 1rem; /* 16px */
        }
        

        
        footer {
            background-color: rgba(0, 0, 0, 0.5); /* Warna hitam semi-transparan */
            padding: 1rem; /* Memberi ruang di dalam blok */
            border-radius: 0.75rem; /* Membuat sudut melengkung agar serasi */
            backdrop-filter: blur(5px); /* Efek blur tambahan */
        }
        


    </style>
     <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <div class="container my-5">
        <div class="main-container p-4 p-md-5">
            <h2 class="text-center mb-4 fw-bold">
                <i class="bi bi-stars"></i> Unggah File Lembar Observasi PeerTeaching
            </h2>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type'] === 'success' ? 'info' : 'danger'; ?> d-flex align-items-center" role="alert">
                    <i class="bi <?php echo $_SESSION['message_type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
                    <div><?php echo $_SESSION['message']; ?></div>
                </div>
                <?php
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="border-bottom border-light border-opacity-25 pb-4 mb-4">
                <div class="mb-3">
                    <label for="fileToUpload" class="form-label">Pilih file observasi:</label>
                    <input class="form-control" type="file" name="fileToUpload" id="fileToUpload" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-upload btn-primary fw-semibold py-2">
                        <i class="bi bi-upload"></i> Unggah Sekarang
                    </button>
                </div>
            </form>

<h4 class="fw-bold"><i class="bi bi-card-list"></i> Daftar File Terunggah</h4>

<div class="list-group">
    <?php
    if (is_dir($upload_dir)) {
        $all_files = array_diff(scandir($upload_dir), array('.', '..'));
        
        $sorted_files = [];
        if (!empty($all_files)) {
            // 1. Kumpulkan file beserta waktu modifikasinya
            foreach ($all_files as $file) {
                $filepath = $upload_dir . $file;
                // Simpan nama file dan timestamp-nya
                $sorted_files[$file] = filemtime($filepath);
            }
            
            // 2. Urutkan array berdasarkan timestamp (dari lama ke baru)
            asort($sorted_files);
        }

        if (count($sorted_files) > 0) {
            $counter = 0;
            // 3. Tampilkan file berdasarkan urutan yang sudah benar
            foreach ($sorted_files as $file => $timestamp) {
                $filepath = $upload_dir . $file;
                $upload_time = date('d-m-Y, H:i', $timestamp); // Gunakan timestamp yang sudah ada
                $filesize = filesize($filepath);
                $formatted_size = formatBytes($filesize);
                $icon_class = getFileIconClass($file);
                
                echo '<div class="list-group-item mb-2 rounded-3">';
                echo '  <div class="d-flex align-items-center w-100">';
                echo '    <span class="number-box">' . $counter . '</span>';
                echo '    <i class="bi ' . $icon_class . ' h2 me-3 mb-0"></i>';
                echo '    <div class="file-info">';
                echo '      <h6 class="mb-0">' . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . '</h6>';
                echo '      <small>Diunggah: ' . $upload_time . '</small>';
                echo '    </div>';
                echo '    <span class="badge rounded-pill align-self-center p-2 ms-auto">' . $formatted_size . '</span>';
                echo '  </div>';
                echo '</div>';

                $counter++;
            }
        } else {
            echo '<div class="list-group-item text-center p-3 rounded-3">Belum ada file yang diunggah.</div>';
        }
    } else {
        echo '<div class="list-group-item text-danger text-center p-3 rounded-3">Error: Direktori `uploads` tidak ditemukan.</div>';
    }
    ?>
</div>

<?php if (!empty($news_items)): ?>
<section class="news-section-container">
    <div class="container">
        <h2 class="news-section-title">Berita Terbaru dari SiberMu</h2>
        
        <div class="news-grid">
            <?php foreach ($news_items as $item): ?>
                <a href="<?= htmlspecialchars($item['link']) ?>" target="_blank" rel="noopener noreferrer" class="news-card">
                    <div class="news-card-image-wrapper">
                        <img src="image_proxy.php?url=<?= urlencode($item['image']) ?>" alt="Gambar Berita: <?= htmlspecialchars($item['title']) ?>" class="news-card-image">
                    </div>
                    <div class="news-card-content">
                        <p class="news-card-date"><?= htmlspecialchars($item['date']) ?></p>
                        <h3 class="news-card-title"><?= htmlspecialchars($item['title']) ?></h3>
                        
                        <div class="news-card-footer">
                            <span class="news-card-readmore">
                                Baca Selengkapnya &rarr;
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>    

        </div>
<footer class="text-center mt-4">
    <small class="footer-text">
        Aplikasi di buat menggunakan AI Gemini 2.5 Pro | LPD <a href="https://sibermu.ac.id" target="_blank" rel="noopener noreferrer" class="footer-link">SiberMu</a> | <a href="https://jokovlog.my.id" target="_blank" rel="noopener noreferrer" class="footer-link">jokovlog.my.id</a>
    </small>
</footer>
    </div>

</body>
</html>
