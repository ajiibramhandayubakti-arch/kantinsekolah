<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_order = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ambil data order, sekaligus pastikan order ini benar milik siswa yang sedang login
$q_order = mysqli_query($conn, "SELECT * FROM orders WHERE id_order = '$id_order' AND id_user = '$id_user'");

if (mysqli_num_rows($q_order) == 0) {
    header("Location: riwayat.php?notif=error&pesan=" . urlencode('Struk tidak ditemukan'));
    exit;
}

$order = mysqli_fetch_assoc($q_order);

// bukti pembayaran cuma boleh dilihat kalau pesanannya sudah selesai
if ($order['status_pesanan'] != 'selesai') {
    header("Location: riwayat.php?notif=error&pesan=" . urlencode('Bukti pembayaran hanya tersedia untuk pesanan yang sudah selesai'));
    exit;
}

// ambil detail item yang dibeli di order ini
$q_detail = mysqli_query($conn, "SELECT od.*, m.nama_menu, k.nama_kantin FROM transaksi od JOIN menu m ON od.id_menu = m.id_menu JOIN kantin k ON od.id_kantin = k.id_kantin WHERE od.id_order = '$id_order'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran - Order #<?php echo $order['id_order']; ?></title>
    <link rel="stylesheet" href="../assets/css/siswa-style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="halaman-judul">🧾 Bukti Pembayaran</h2>
        <p class="halaman-subjudul">Simpan atau cetak halaman ini sebagai bukti kamu sudah bayar.</p>

        <div class="kotak struk-area">
            <div class="struk">

                <div class="struk-kop">
                    <img src="../assets/img/logo-sekolah.png" alt="Logo Sekolah">
                    <h3>E-KANTIN SEKOLAH</h3>
                    <p>Bukti Pembayaran Sah</p>
                </div>

                <div class="struk-garis"></div>

                <table class="struk-info">
                    <tr><td>No. Order</td><td>: #<?php echo $order['id_order']; ?></td></tr>
                    <tr><td>ID Pembayaran</td><td>: <?php echo htmlspecialchars($order['nomor_antrean']); ?></td></tr>
                    <tr><td>Tanggal</td><td>: <?php echo $order['tanggal_pesan']; ?></td></tr>
                    <tr><td>Atas Nama</td><td>: <?php echo htmlspecialchars($_SESSION['nama_user']); ?></td></tr>
                </table>

                <div class="struk-garis"></div>

                <table class="struk-item">
                    <?php while ($d = mysqli_fetch_assoc($q_detail)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['nama_kantin']); ?> - <?php echo htmlspecialchars($d['nama_menu']); ?> x<?php echo $d['jumlah']; ?></td>
                            <td>Rp <?php echo number_format($d['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>

                <div class="struk-garis"></div>

                <table class="struk-total">
                    <tr><td>TOTAL BAYAR</td><td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td></tr>
                </table>

                <div class="struk-status"> LUNAS &middot; PESANAN SELESAI</div>

                <p class="struk-footer">Terima kasih sudah jajan di kantin sekolah </p>
            </div>

            <div class="struk-aksi">
                <button onclick="window.print()" class="btn btn-hijau"> Cetak / Simpan PDF</button>
                <a href="riwayat.php" class="btn">← Kembali</a>
            </div>
        </div>

    </div>

</body>
</html>