<?php
/* =========================================================
   1. SETUP & PROTEKSI AKSES
   ========================================================= */
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'siswa') {
    echo "<script>
    alert('Akses Ditolak! Khusus untuk Siswa!');
    window.location.href='login.php';
    </script>";
    exit;
}

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = array();
}


/* =========================================================
   2. AKSI: TAMBAH ITEM KE KERANJANG (POST)
   ========================================================= */
if (isset($_POST['tambah_keranjang'])) {
    $id_menu = (int)$_POST['id_menu'];
    $jumlah  = (int)$_POST['jumlah'];

    if (isset($_SESSION['keranjang'][$id_menu])) {
        $_SESSION['keranjang'][$id_menu] += $jumlah;
    } else {
        $_SESSION['keranjang'][$id_menu] = $jumlah;
    }

    // kembali ke halaman dengan filter yang tadi sedang aktif (bukan reset ke dashboard polos),
    // lalu tampilkan toast sukses lewat ui.js
    $kembali_ke = !empty($_POST['redirect_url']) ? $_POST['redirect_url'] : 'dashboard.php';
    $pemisah    = (strpos($kembali_ke, '?') !== false) ? '&' : '?';
    $pesan      = urlencode('Menu berhasil ditambahkan ke keranjang!');

    header("Location: {$kembali_ke}{$pemisah}notif=sukses&pesan={$pesan}");
    exit;
}


/* =========================================================
   3. AMBIL PARAMETER FILTER DARI URL
   (kantin yang dipilih, kata kunci pencarian, urutan tampilan)
   ========================================================= */
$id_kantin_pilih = isset($_GET['kantin']) ? (int)$_GET['kantin'] : 0;
$keyword         = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';
$urutan          = isset($_GET['urutan']) ? $_GET['urutan'] : 'terbaru';

/**
 * Helper: menyusun ulang link filter tanpa menghilangkan filter lain yang
 * sedang aktif. Misalnya waktu ganti urutan, filter kantin & pencarian
 * yang sudah dipilih user tetap ikut terbawa di URL.
 */
function buatLinkFilter($override = array()) {
    $params = array_merge($_GET, $override);

    // buang parameter yang kosong/nol biar URL tetap bersih
    foreach ($params as $key => $val) {
        if ($val === '' || $val === 0 || $val === '0') {
            unset($params[$key]);
        }
    }

    return 'dashboard.php' . (empty($params) ? '' : '?' . http_build_query($params));
}


/* =========================================================
   4. QUERY DATA: DAFTAR KANTIN & DAFTAR MENU (SESUAI FILTER)
   ========================================================= */
$query_kantin = mysqli_query($conn, "SELECT * FROM kantin ORDER BY nama_kantin ASC");

$sql_menu = "SELECT menu.*, kantin.nama_kantin
             FROM menu
             JOIN kantin ON menu.id_kantin = kantin.id_kantin
             WHERE menu.status = 'tersedia' AND menu.stok > 0";

if ($id_kantin_pilih > 0) {
    $sql_menu .= " AND menu.id_kantin = '$id_kantin_pilih'";
}

if (!empty($keyword)) {
    $sql_menu .= " AND menu.nama_menu LIKE '%$keyword%'";
}

// urutan tampilan menu: terbaru (default) / termurah / termahal
switch ($urutan) {
    case 'termurah':
        $sql_menu .= " ORDER BY menu.harga ASC";
        break;
    case 'termahal':
        $sql_menu .= " ORDER BY menu.harga DESC";
        break;
    default:
        $urutan   = 'terbaru';
        $sql_menu .= " ORDER BY menu.id_menu DESC";
}

$query_menu = mysqli_query($conn, $sql_menu);

$total_item_keranjang = array_sum($_SESSION['keranjang']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Siswa</title>
    <link rel="stylesheet" href="../assets/css/siswa-style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="halaman-judul">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_user']); ?>! </h2>
        <p class="halaman-subjudul">Yuk pilih menu favoritmu hari ini.</p>

        <!-- ============ FORM PENCARIAN + URUTAN ============ -->
        <div class="kotak">
            <h3> Cari Makanan / Minuman</h3>

            <form action="" method="GET" class="form-cari">
                <?php if ($id_kantin_pilih > 0): ?>
                    <input type="hidden" name="kantin" value="<?php echo $id_kantin_pilih; ?>">
                <?php endif; ?>
                <?php if ($urutan !== 'terbaru'): ?>
                    <input type="hidden" name="urutan" value="<?php echo htmlspecialchars($urutan); ?>">
                <?php endif; ?>

                <input type="text" name="cari" placeholder="Ketik Nama Makanan/Minuman..." value="<?php echo htmlspecialchars($keyword); ?>">
                <button type="submit">Cari</button>

                <?php if (!empty($keyword)): ?>
                    <a href="<?php echo buatLinkFilter(['cari' => '']); ?>">Reset Pencarian</a>
                <?php endif; ?>
            </form>

            <div class="urutan-bar">
                <span class="urutan-label">Urutkan:</span>
                <a href="<?php echo buatLinkFilter(['urutan' => 'terbaru']); ?>" class="filter-tab <?php echo ($urutan == 'terbaru') ? 'aktif' : ''; ?>">Terbaru</a>
                <a href="<?php echo buatLinkFilter(['urutan' => 'termurah']); ?>" class="filter-tab <?php echo ($urutan == 'termurah') ? 'aktif' : ''; ?>">Termurah</a>
                <a href="<?php echo buatLinkFilter(['urutan' => 'termahal']); ?>" class="filter-tab <?php echo ($urutan == 'termahal') ? 'aktif' : ''; ?>">Termahal</a>
            </div>
        </div>

        <!-- ============ FILTER STAND KANTIN ============ -->
        <div class="kotak">
            <h3> Pilih Stand Kantin</h3>

            <a href="<?php echo buatLinkFilter(['kantin' => 0]); ?>" class="kantin-chip <?php echo ($id_kantin_pilih == 0) ? 'aktif' : ''; ?>">
                <strong>Semua Kantin</strong>
            </a>

            <?php while ($k = mysqli_fetch_assoc($query_kantin)): ?>
                <a href="<?php echo buatLinkFilter(['kantin' => $k['id_kantin']]); ?>" class="kantin-chip <?php echo ($id_kantin_pilih == $k['id_kantin']) ? 'aktif' : ''; ?>">
                    <?php echo htmlspecialchars($k['nama_kantin']); ?>
                </a>
            <?php endwhile; ?>
        </div>

        <!-- ============ DAFTAR MENU (HASIL FILTER) ============ -->
        <div class="kotak">
            <h3> Daftar Menu Makanan & Minuman</h3>

            <?php if (mysqli_num_rows($query_menu) > 0): ?>
                <div class="menu-grid">
                    <?php while ($menu = mysqli_fetch_assoc($query_menu)): ?>

                        <div class="menu-card">

                            <div class="menu-img">
                                <?php if (!empty($menu['foto_menu'])): ?>
                                    <img src="../uploads/menu/<?php echo htmlspecialchars($menu['foto_menu']); ?>" alt="<?php echo htmlspecialchars($menu['nama_menu']); ?>">
                                <?php else: ?>
                                    <div class="menu-img-kosong">🍽️</div>
                                <?php endif; ?>
                            </div>

                            <div class="menu-info">
                                <span class="menu-kantin"><?php echo htmlspecialchars($menu['nama_kantin']); ?></span>
                                <h4><?php echo htmlspecialchars($menu['nama_menu']); ?></h4>
                                <p class="menu-harga">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></p>
                                <p class="menu-stok">Stok: <?php echo $menu['stok']; ?></p>

                                <form action="" method="POST">
                                    <input type="hidden" name="id_menu" value="<?php echo $menu['id_menu']; ?>">
                                    <input type="hidden" name="redirect_url" value="dashboard.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>">

                                    <div class="qty-control">
                                        <button type="button" onclick="kurangJumlah(<?php echo $menu['id_menu']; ?>)">-</button>
                                        <input type="number" id="jumlah_<?php echo $menu['id_menu']; ?>" name="jumlah" value="1" min="1" max="<?php echo $menu['stok']; ?>" required>
                                        <button type="button" onclick="tambahJumlah(<?php echo $menu['id_menu']; ?>, <?php echo $menu['stok']; ?>)">+</button>
                                    </div>

                                    <button type="submit" name="tambah_keranjang" class="btn btn-hijau">Ke Keranjang</button>
                                </form>
                            </div>
                        </div>

                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Menu tidak ditemukan atau stok habis.</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- fungsi tombol - dan + ditulis langsung di sini (inline),
         supaya tidak gagal kalau file JS eksternal salah path atau belum ke-upload -->
    <script>
        function tambahJumlah(idMenu, stokMaksimal) {
            var input = document.getElementById("jumlah_" + idMenu);
            var nilaiSekarang = parseInt(input.value);

            if (nilaiSekarang < stokMaksimal) {
                input.value = nilaiSekarang + 1;
            } else {
                goyangkanElemen(input);
                showToast('Jumlah sudah mencapai batas stok (' + stokMaksimal + ')', 'error');
            }
        }

        function kurangJumlah(idMenu) {
            var input = document.getElementById("jumlah_" + idMenu);
            var nilaiSekarang = parseInt(input.value);

            if (nilaiSekarang > 1) {
                input.value = nilaiSekarang - 1;
            } else {
                goyangkanElemen(input);
            }
        }
    </script>
</body>
</html>
