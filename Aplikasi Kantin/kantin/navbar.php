<?php
// dipanggil pakai include di setiap halaman penjual, biar tidak nulis ulang menu navigasi
$halaman_sekarang = basename($_SERVER['PHP_SELF']);

// splash screen hanya ditampilkan sekali, tepat setelah login berhasil.
$tampilkan_splash = !empty($_SESSION['baru_login']);
unset($_SESSION['baru_login']);
?>

<!-- ============ SPLASH SCREEN (INTRO) ============ -->
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
        <p class="splash-tagline">Kelola pesanan kantinmu, lebih cepat.</p>

        <div class="splash-progress-wrap">
            <div class="splash-progress-bar" id="splashProgressBar"></div>
        </div>
    </div>
</div>
<script>
    (function () {
        var splash   = document.getElementById('splashScreen');
        var progress = document.getElementById('splashProgressBar');
        var DURASI   = 1500;

        document.body.classList.add('splash-aktif');

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
        <span>E-Kantin · Penjual</span>
    </div>

    <div class="navbar-menu">
        <a href="dashboard.php" class="<?php echo ($halaman_sekarang == 'dashboard.php') ? 'aktif' : ''; ?>"> Dashboard</a>
        <a href="kelola_menu.php" class="<?php echo ($halaman_sekarang == 'kelola_menu.php') ? 'aktif' : ''; ?>"> Kelola Menu</a>
        <a href="pesanan_masuk.php" class="<?php echo ($halaman_sekarang == 'pesanan_masuk.php') ? 'aktif' : ''; ?>"> Pesanan Masuk</a>
        <a href="laporan.php" class="<?php echo ($halaman_sekarang == 'laporan.php') ? 'aktif' : ''; ?>"> Laporan</a>
    </div>

    <div class="navbar-user">
        <span class="navbar-nama"> <?php echo htmlspecialchars($_SESSION['nama_user']); ?></span>
        <a href="../login.php" class="navbar-logout" onclick="return confirm('Yakin Ingin Keluar?')">Logout</a>
    </div>

</nav>