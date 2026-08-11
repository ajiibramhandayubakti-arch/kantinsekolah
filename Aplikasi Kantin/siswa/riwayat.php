<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

$query_order = mysqli_query($conn, "SELECT * FROM orders WHERE id_users = '$id_user' ORDER BY id_orders DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
</head>
<body>
    <h2>📋 Riwayat Pesanan Saya</h2>
    <a href="dashboard.php">← Kembali ke Dashboard Menu</a>
    <br><br>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <td>No</td>
                <td>ID Order</td>
                <td>Tanggal Pesan</td>
                <td>Nomor Antrean</td>
                <td>Total Bayar</td>
                <td>Status Pesanan</td>
                <td>Detail Pesanan</td>
            </tr>
        </thead>

        <tbody>
            <?php
            $no = 1;
            if (mysqli_num_rows($query_order) > 0) {
                while ($order = mysqli_fetch_assoc($query_order)) {
            ?>

                <tr>
                    <td><?php echo $no++; ?></td>
                    <td>#<?php echo $order['id_order']; ?></td>
                    <td><?php echo $order['tanggal_pesan']; ?></td>
                    <td><strong style="font-size: 18px; color: green;">#<?php echo $order['nomor_antrean']; ?></strong></td>
                    <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                    <td><strong><?php echo strtoupper($order['status_pesanan']); ?></strong></td>
                    <td>
                        <ul>
                            <?php 
                            $id_o = $order['id_order'];
                            $q_detail = mysqli_query($conn, "SELECT od.*, m.nama_menu, k.nama_kantin FROM transaksi od JOIN menu m ON od.id_menu = m.id_menu JOIN kantin k ON od.id_kantin = k.id_kantin WHERE od.id_order = '$id_o'");
                            while ($d = mysqli_fetch_assoc($q_detail)) {
                                echo "<li>[" . $d['nama_kantin'] . "] " . $d['nama_menu'] . " x" . $d['jumlah'] . "</li>";
                            }
                            ?>
                        </ul>
                    </td>
                </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='7' align='center'>Belum Ada Riwayat Pesanan.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>