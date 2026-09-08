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
// splash screen hanya ditampilkan sekali, tepat setelah login berhasil.
// begitu dibaca di sini, langsung di-unset supaya tidak tampil lagi
// walau halaman di-refresh atau dibuka ulang.
$tampilkan_splash = !empty($_SESSION['baru_login']);
unset($_SESSION['baru_login']);
?>
<script src="../assets/js/ui.js" defer></script>

<!-- ============ SPLASH SCREEN (INTRO) ============ -->
<!-- Hanya tampil sekali, tepat setelah login berhasil (ditandai lewat session PHP).
     Refresh halaman setelahnya TIDAK akan memicu splash lagi. -->
<?php if ($tampilkan_splash): ?>
<div class="splash-screen" id="splashScreen">
    <div class="splash-blob splash-blob-1"></div>
    <div class="splash-blob splash-blob-2"></div>

    <div class="splash-isi">
        <div class="splash-logo-wrap">
            <span class="splash-logo-ring"></span>
            <span class="splash-logo-ring jeda"></span>
            <img src="../assets/img/logo-sekolah.png" alt="Logo E-Kantin" class="splash-logo">
        </div>
        <h1 class="splash-judul">E-Kantin</h1>
        <p class="splash-tagline">Pesan makanan, tanpa antre lama.</p>

        <div class="splash-progress-wrap">
            <div class="splash-progress-bar" id="splashProgressBar"></div>
        </div>
    </div>
</div>
<script>
    (function () {
        var splash   = document.getElementById('splashScreen');
        var progress = document.getElementById('splashProgressBar');
        var DURASI   = 1500; // total durasi splash sebelum fade-out, dalam ms

        document.body.classList.add('splash-aktif');

        // kasih sedikit jeda supaya transisi width dari 0% ke 100% tertangkap browser (bukan langsung loncat)
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                progress.style.transitionDuration = DURASI + 'ms';
                progress.style.width = '100%';
            });
        });

        window.addEventListener('load', function () {
            setTimeout(function () {
                splash.classList.add('splash-selesai');
                document.body.classList.remove('splash-aktif');

                setTimeout(function () {
                    splash.style.display = 'none';
                }, 550);
            }, DURASI);
        });
    })();
</script>
<?php endif; ?>

<nav class="navbar">

    <div class="navbar-brand">
        <img src="../assets/img/logo-sekolah.png" alt="Logo Sekolah">
        <span>E-Kantin</span>
    </div>

    <div class="navbar-menu">
        <a href="dashboard.php" class="<?php echo ($halaman_sekarang == 'dashboard.php') ? 'aktif' : ''; ?>">
             Menu
        </a>
        <a href="keranjang.php" class="<?php echo ($halaman_sekarang == 'keranjang.php') ? 'aktif' : ''; ?>">
             Keranjang
            <?php if ($jumlah_keranjang > 0): ?>
                <span class="navbar-badge"><?php echo $jumlah_keranjang; ?></span>
            <?php endif; ?>
        </a>
        <a href="riwayat.php" class="<?php echo ($halaman_sekarang == 'riwayat.php') ? 'aktif' : ''; ?>">
             Riwayat
        </a>
    </div>

    <div class="navbar-user">
        <span class="navbar-nama"> <?php echo htmlspecialchars($_SESSION['nama_user']); ?></span>
        <a href="../login.php"
           class="navbar-logout"
           data-konfirmasi="Yakin ingin logout dari akun ini?"
           data-judul-konfirmasi="Logout"
           data-bahaya="1">
            Logout
        </a>
    </div>

</nav>
