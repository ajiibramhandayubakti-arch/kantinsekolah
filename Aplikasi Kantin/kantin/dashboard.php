<?php
session_start();
include '../config/koneksi.php';

// proteksi akses penjual
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'penjual') {
    echo "<script>
    alert('Akses Ditolak! Khusus untuk Penjual Kantin!');
    window.location.href='../login.php';
    </script>";
    exit;
}

// ambil ID kantin milik penjual yang sedang login
$id_user = $_SESSION['id_user'];

// query mengambil data kantin berdasarkan id_user login
$q_kantin = mysqli_query($conn, "SELECT * FROM kantin WHERE id_user = '$id_user'");
$data_kantin = mysqli_fetch_assoc($q_kantin);

// jika data kantin ditemukan
$id_kantin = isset($data_kantin['id_kantin']) ? $data_kantin['id_kantin']: 0;
$nama_kantin = isset($data_kantin['nama_kantin']) ? $data_kantin['nama_kantin'] : 'Stand Kantin';

// 1. hitung pesanan perlu diproses hari ini
$q_proses = mysqli_query($conn, "SELECT COUNT(DISTINCT o.id_order) AS total FROM orders o JOIN transaksi t ON o.id_order = t.id_order WHERE t.id_kantin = '$id_kantin' AND o.status_pesanan = 'diproses' AND DATE(o.tanggal_pesan) = CURDATE()");
$d_proses = mysqli_fetch_assoc($q_proses);
$total_diproses = $d_proses['total'];

// 2. hitung pesanan selesai hari ini
$q_selesai = mysqli_query($conn, "SELECT COUNT(DISTINCT o.id_order) AS total FROM orders o JOIN transaksi t ON o.id_order = t.id_order WHERE t.id_kantin = '$id_kantin' AND o.status_pesanan = 'selesai' AND DATE(o.tanggal_pesan) = CURDATE()");
$d_selesai = mysqli_fetch_assoc($q_selesai);
$total_selesai = $d_selesai['total'];

// 3. hitung total pendapatan hari ini
$q_pendapatan = mysqli_query($conn, "SELECT SUM(t.subtotal) AS total_uang FROM orders o JOIN transaksi t ON o.id_order = t.id_order WHERE t.id_kantin = '$id_kantin' AND o.status_pesanan = 'selesai' AND DATE(o.tanggal_pesan) = CURDATE()");
$d_pendapatan = mysqli_fetch_assoc($q_pendapatan);
$total_pendapatan = $d_pendapatan['total_uang'] ? $d_pendapatan['total_uang'] : 0;

// 4. ambil 5 pesanan masuk terbaru (perlu diproses)
$q_pesanan_baru = mysqli_query($conn, "SELECT DISTINCT o.* FROM orders o JOIN transaksi t ON o.id_order = t.id_order WHERE t.id_kantin = '$id_kantin' AND o.status_pesanan = 'diproses' ORDER BY o.id_order DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual - <?php echo htmlspecialchars($nama_kantin); ?></title>
    <link rel="stylesheet" href="../assets/css/kantin-style.css">
    
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="halaman-judul"> Dashboard - <?php echo htmlspecialchars($nama_kantin); ?></h2>
        <p class="halaman-subjudul">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama_user']); ?></strong>!</p>

        <div class="kotak">
            <h3> Ringkasan Hari Ini (<?php echo date('d-m-Y'); ?>)</h3>

            <div class="stat-grid">
                <div class="stat-card kuning">
                    <div class="stat-label"> Perlu Diproses</div>
                    <div class="stat-angka"><?php echo $total_diproses; ?> Pesanan</div>
                </div>
                <div class="stat-card hijau">
                    <div class="stat-label"> Pesanan Selesai</div>
                    <div class="stat-angka"><?php echo $total_selesai; ?> Pesanan</div>
                </div>
                <div class="stat-card biru">
                    <div class="stat-label"> Pendapatan Hari Ini</div>
                    <div class="stat-angka">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
                </div>
            </div>
        </div>

        <div class="kotak">
            <h3>🚨 Pesanan Terbaru yang Perlu Diproses</h3>

            <div class="tabel-geser">
                <table>
                    <thead>
                        <tr>
                            <th>No Antrean</th>
                            <th>ID Order</th>
                            <th>Waktu Pesan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($q_pesanan_baru) > 0): ?>
                            <?php while ($p = mysqli_fetch_assoc($q_pesanan_baru)): ?>
                                <tr>
                                    <td><span class="nomor-antrean">#<?php echo $p['nomor_antrean']; ?></span></td>
                                    <td>#<?php echo $p['id_order']; ?></td>
                                    <td><?php echo $p['tanggal_pesan']; ?></td>
                                    <td><span class="badge <?php echo strtolower($p['status_pesanan']); ?>"><?php echo strtoupper($p['status_pesanan']); ?></span></td>
                                    <td><a href="pesanan_masuk.php" class="btn btn-kecil"> Lihat & Siapkan</a></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" align="center">Belum ada pesanan baru yang perlu diproses saat ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <br>
            <a href="pesanan_masuk.php"><strong>Lihat Semua Pesanan Masuk →</strong></a>
        </div>

    </div>
</body>
</html>
