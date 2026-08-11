<?php
session_start();
include '../config/koneksi.php';

// proteksi akses penjual
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'penjual') {
    echo "<script>
    alert('Akses Ditolak!');
    window.locatin.href='../login.php';
    </script>";
    exit;
}

$id_user = $_SESSION['id_user'];

// ambil data kantin penjual
$q_kantin = mysqli_query($conn, "SELECT * FROM kantin WHERE id_user = '$id_user'");
$data_kantin = mysqli_fetch_assoc($q_kantin);
$id_kantin = isset($data_kantin['id_kantin']) ? $data_kantin['id_kantin'] : 0;

// proses ubah status pesanan
if (isset($_POST['update_status'])) {
    $id_orders = mysqli_real_escape_string($conn, $_POST['id_orders']);
    $status_baru = mysqli_real_escape_string($conn, $_POST['status_pesanan']);

    $q_update = mysqli_query($conn, "UPDATE orders SET status_pesanan = '$status_baru' WHERE id_orders = '$id_orders'");

    if ($q_update) {
        echo "<script>
        alert('Status Pesanan Berhasil Diperbarui');
        window.location.href='pesanan_masuk.php';
        </script>";
    } else {
        echo "<script>
        alert('Gagal Memperbarui Status Pesanan!');
        </script>";
    }
}

// ambil daftar order unik yang membeli di kantin ini
$q_orders = mysqli_query($conn, "SELECT DISTINCT o.*, u.nama_user AS nama_siswa FROM orders o JOIN transaksi t ON o.id_orders = t.id_orders JOIN users u ON o.id_users = u.id_users WHERE t.id_kantin = '$id_kantin' ORDER BY o.id_orders DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h2>🛎️ Kelola Pesanan Masuk</h2>

    <!-- Navigasi Utama -->
    <p>
        <a href="dashboard.php">📌 Dashboard</a> |
        <a href="kelola_menu.php">🍔 Kelola Menu</a> |
        <a href="pesanan_masuk.php">🛎️ Pesanan Masuk</a> |
        <a href="laporan.php">📊 Laporan Penjualan</a> |
        <a href="../logout.php" onclick="return confirm('Yakin Ingin Keluar?')">Logout</a>
    </p>
    <hr>

    <h3>📋 Daftar Pesanan Pembeli</h3>

    <?php if (mysqli_num_rows($q_orders) > 0): ?>
        <?php while ($ord = mysqli_fetch_assoc($q_orders)): ?>
            <?php 
                $id_orders = $ord['id_orders'];
                
                // Ambil detail makanan/minuman yang dibeli khusus dari kantin ini saja
                $q_detail = mysqli_query($conn, "SELECT t.*, m.nama_menu 
                                                FROM transaksi t 
                                                JOIN menu m ON t.id_menu = m.id_menu 
                                                WHERE t.id_orders = '$id_orders' 
                                                AND t.id_kantin = '$id_kantin'");
                
                // Hitung total belanjaan khusus di kantin ini
                $total_belanja_kantin = 0;
            ?>

            <table border="1" cellpadding="8" cellspacing="0" width="100%" style="margin-bottom: 20px;">
                <tr style="background-color: #e9ecef;">
                    <td colspan="4">
                        <strong>No Antrean: <span style="font-size: 20px; color: green;">#<?php echo $ord['nomor_antrean']; ?></span></strong> | 
                        ID Order: <strong>#<?php echo $ord['id_orders']; ?></strong> | 
                        Pemesan: <strong><?php echo htmlspecialchars($ord['nama_siswa']); ?></strong> | 
                        Waktu: <?php echo $ord['tanggal_pesan']; ?>
                    </td>
                </tr>
                <tr style="background-color: #f8f9fa; font-weight: bold;">
                    <td>Nama Menu</td>
                    <td>Harga Satuan</td>
                    <td>Jumlah</td>
                    <td>Subtotal</td>
                </tr>

                <?php while ($det = mysqli_fetch_assoc($q_detail)): ?>
                    <?php $total_belanja_kantin += $det['subtotal']; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($det['nama_menu']); ?></td>
                        <td>Rp <?php echo number_format($det['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $det['jumlah']; ?> Porsi</td>
                        <td>Rp <?php echo number_format($det['subtotal'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>

                <tr style="background-color: #fafafa;">
                    <td colspan="3" align="right"><strong>Total Kantin Ini:</strong></td>
                    <td><strong style="color: blue;">Rp <?php echo number_format($total_belanja_kantin, 0, ',', '.'); ?></strong></td>
                </tr>

                <!-- Form Ubah Status Pesanan -->
                <tr>
                    <td colspan="4" align="right">
                        <form action="" method="POST" style="margin: 0;">
                            <input type="hidden" name="id_orders" value="<?php echo $ord['id_orders']; ?>">
                            <label for=""><strong>Status Pesanan Saat Ini:</strong> </label>
                            
                            <select name="status_pesanan" style="padding: 5px;">
                                <option value="diproses" <?php echo ($ord['status_pesanan'] == 'diproses') ? 'selected' : ''; ?>>⏳ Diproses (Sedang Dimasak)</option>
                                <option value="siap diambil" <?php echo ($ord['status_pesanan'] == 'siap diambil') ? 'selected' : ''; ?>>🔔 Siap Diambil</option>
                                <option value="selesai" <?php echo ($ord['status_pesanan'] == 'selesai') ? 'selected' : ''; ?>>✅ Selesai (Sudah Diambil)</option>
                            </select>

                            <button type="submit" name="update_status" style="padding: 5px 10px; cursor: pointer;">Update Status</button>
                        </form>
                    </td>
                </tr>
            </table>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Belum ada pesanan masuk untuk stand kantin Anda saat ini.</p>
    <?php endif; ?>
</body>
</html>