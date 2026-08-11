<?php
// navbar.php
// File ini isinya cuma bagian navbar, dipanggil pakai include di tiap halaman siswa.
// Tujuannya biar nggak perlu tulis ulang kode navbar di 4 file berbeda.
// ui.js juga di-load di sini, jadi otomatis aktif di semua halaman siswa
// (toast notifikasi, modal konfirmasi custom, animasi sukses checkout).

// deteksi halaman mana yang lagi aktif, buat kasih warna beda di menu-nya
$halaman_sekarang = basename($_SERVER['PHP_SELF']);

// hitung jumlah item di keranjang (dicek dulu biar tidak error kalau belum ada)
$jumlah_keranjang = isset($_SESSION['keranjang']) ? array_sum($_SESSION['keranjang']) : 0;
?>
<script src="../assets/js/ui.js" defer></script>

<nav class="navbar">

    <div class="navbar-brand">
        <img src="../assets/img/logo-sekolah.png" alt="Logo Sekolah">
        <span>E-Kantin</span>
    </div>

    <div class="navbar-menu">
        <a href="dashboard.php" class="<?php echo ($halaman_sekarang == 'dashboard.php') ? 'aktif' : ''; ?>">
            🍔 Menu
        </a>
        <a href="keranjang.php" class="<?php echo ($halaman_sekarang == 'keranjang.php') ? 'aktif' : ''; ?>">
            🛒 Keranjang
            <?php if ($jumlah_keranjang > 0): ?>
                <span class="navbar-badge"><?php echo $jumlah_keranjang; ?></span>
            <?php endif; ?>
        </a>
        <a href="riwayat.php" class="<?php echo ($halaman_sekarang == 'riwayat.php') ? 'aktif' : ''; ?>">
            📋 Riwayat
        </a>
    </div>

    <div class="navbar-user">
        <span class="navbar-nama">👋 <?php echo htmlspecialchars($_SESSION['nama_user']); ?></span>
        <a href="../login.php"
           class="navbar-logout"
           data-konfirmasi="Yakin ingin logout dari akun ini?"
           data-judul-konfirmasi="Logout"
           data-bahaya="1">
            Logout
        </a>
    </div>

</nav>