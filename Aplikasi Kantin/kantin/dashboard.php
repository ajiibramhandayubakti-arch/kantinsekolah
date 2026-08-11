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
$q_proses = mysqli_query($conn, "SELECT COUNT(DISTINCT o.id_orders) AS total FROM orders o JOIN transaksi t ON o.id_orders = t.id_orders WHERE t.id_kantin = '$id_kantin' AND o.status_pesanan = 'diproses' AND DATE(o.tanggal_pesan) = CURDATE()");
$d_proses = mysqli_fetch_assoc($q_proses);
$total_diproses =  $d_proses['total'];

// 2. hitung pesanan selesai hari ini
$q_selesai = mysqli_query($conn, "SELECT COUNT(DISTINCT o.id_orders) AS total FROM orders o JOIN transaksi t ON o.id_orders = t.id_orders WHERE t.id_kantin = '$id_kantin' AND o.status_pesanan = 'selesai' AND DATE(o.tanggal_pesan) = CURDATE()");
$d_selesai = mysqli_fetch_assoc($q_selesai);
$total_selesai = $d_selesai['total'];

// 3. hitung total pendapatan hari ini
$q_pendapatan = mysqli_query($conn, "SELECT SUM(t.subtotal) AS total_uang FROM orders o JOIN transaksi t ON o.id_orders = t.id_orders WHERE t.id_kantin = '$id_kantin' AND o.status_pesanan = 'selesai' AND DATE(o.tanggal_pesan) = CURDATE()");
$d_pendapatan = mysqli_fetch_assoc($q_pendapatan);
$total_pendapatan = $d_pendapatan['total_uang'] ? $d_pendapatan['total_uang'] : 0;

// 4. ambil 5 pesanan masuk terbaru (perlu diproses)
$q_pesanan_baru = mysqli_query($conn, "SELECT DISTINCT o.* FROM orders o JOIN transaksi t ON o.id_orders = t.id_orders WHERE t.id_kantin = '$id_kantin' AND o.status_pesanan = 'diproses' ORDER BY o.id_orders DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual - <?php echo htmlspecialchars ($nama_kantin); ?></title>
</head>
<body>
    <h2>🏪 Dashboard Penjual - <?php echo htmlspecialchars($nama_kantin); ?></h2>
    <p>Selamat Datang, <strong><?php echo htmlspecialchars($_SESSION['nama_user']); ?></strong>!</p>

    <!-- nav utama penjual -->
     <p>
        <a href="dashboard.php">📌 Dashboard</a> |
        <a href="kelola_menu.php">🍔 Kelola Menu</a> |
        <a href="pesanan_masuk.php">🛎️ Pesanan Masuk</a> |
        <a href="laporan.php">📊 Laporan Penjualan</a>
        <a href="../login.php" onclick="return confirm('Yakin Ingin Keluar?')">Logout</a>
     </p>
     <hr>

     <!-- kartu statistik hari ini -->
      <h3>📊 Ringkasan Hari ini (<?php echo date('d-m-Y'); ?>)</h3>
      <table border="1" cellpadding="15" cellspacing="0" style="text-align: center;">
        <tr>
            <td style="background-color: #fff3cd;">
                ⏳<strong>Perlu Diproses</strong>
                <h2 style="margin: 5px 0; color: #856404"><?php echo $total_diproses; ?> Pesanan</h2>
            </td>
            <td style="background-color: #d4edda;">
                ✅ <strong>Pesanan Selesai</strong><br>
                <h2 style="margin: 5px 0; color: #155724;"><?php echo $total_selesai; ?> Pesanan</h2>
            </td>

            <td style="background-color: #d1ecf1;">
                💰<strong>Pendapatan Hari Ini</strong>
                <h2 style="margin: 5px 0; color: #0c5460;">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h2>
            </td>
        </tr>
      </table>
      <br><hr>

      <!-- tabel pesanan masuk terbaru -->
       <h3>🚨 Pesanan Terbaru yang Perlu Diproses</h3>
       <table>
            <thead>
                <tr style="background-color: #f2f2f2;">
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
                        <tr align="center">
                            <td><strong style="font-size: 18px; color: green;">#<?php echo $p['nomor_antrean']; ?></strong></td>
                            <td>#<?php echo $p['id_orders']; ?></td>
                            <td><?php echo $p['tanggal_pesan']; ?></td>
                            <td><span style="color: orange; font-weight: bold;"><?php echo strtoupper($p['status_pesanan']); ?></span></td>
                            <td>
                                <a href="pesanan_masuk.php?id=<?php echo $p['id_orders'];  ?>">🔍 Lihat & Siapkan</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" align="center">Belum Ada Pesanan Baru yang Perlu Diproses Saat Ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
       </table>
       <br>
       <a href="pesanan_masuk.php"><strong>Lihat Semua Pesanan Masuk →</strong></a>
</body>
</html>