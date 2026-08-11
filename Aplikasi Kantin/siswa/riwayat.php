<?php
/* =========================================================
   1. SETUP & PROTEKSI AKSES
   ========================================================= */
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];


/* =========================================================
   2. AMBIL FILTER STATUS DARI URL
   ========================================================= */
$status_dipilih = isset($_GET['status']) ? $_GET['status'] : 'semua';
$status_valid   = array('semua', 'diproses', 'selesai', 'dibatalkan');

if (!in_array($status_dipilih, $status_valid)) {
    $status_dipilih = 'semua';
}


/* =========================================================
   3. QUERY DATA PESANAN (SESUAI FILTER STATUS)
   ========================================================= */
$sql_order = "SELECT * FROM orders WHERE id_user = '$id_user'";

if ($status_dipilih !== 'semua') {
    $sql_order .= " AND status_pesanan = '$status_dipilih'";
}

$sql_order .= " ORDER BY id_order DESC";
$query_order = mysqli_query($conn, $sql_order);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
    <link rel="stylesheet" href="../assets/css/siswa-style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="halaman-judul">📋 Riwayat Pesanan Saya</h2>
        <p class="halaman-subjudul">Pesanan yang sudah selesai bisa dicetak jadi bukti pembayaran.</p>

        <!-- ============ TAB FILTER STATUS ============ -->
        <div class="filter-tab-bar">
            <a href="?status=semua" class="filter-tab <?php echo ($status_dipilih == 'semua') ? 'aktif' : ''; ?>">Semua</a>
            <a href="?status=diproses" class="filter-tab <?php echo ($status_dipilih == 'diproses') ? 'aktif' : ''; ?>">Diproses</a>
            <a href="?status=selesai" class="filter-tab <?php echo ($status_dipilih == 'selesai') ? 'aktif' : ''; ?>">Selesai</a>
            <a href="?status=dibatalkan" class="filter-tab <?php echo ($status_dipilih == 'dibatalkan') ? 'aktif' : ''; ?>">Dibatalkan</a>
        </div>

        <div class="kotak">
            <div class="tabel-geser">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Order</th>
                            <th>Tanggal Pesan</th>
                            <th>ID Pembayaran</th>
                            <th>Total Bayar</th>
                            <th>Status Pesanan</th>
                            <th>Detail Pesanan</th>
                            <th>Aksi</th>
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
                                <td><span class="nomor-antrean"><?php echo htmlspecialchars($order['nomor_antrean']); ?></span></td>
                                <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge <?php echo strtolower($order['status_pesanan']); ?>">
                                        <?php echo strtoupper($order['status_pesanan']); ?>
                                    </span>
                                </td>
                                <td>
                                    <ul class="detail-pesanan">
                                        <?php 
                                        $id_o = $order['id_order'];
                                        $q_detail = mysqli_query($conn, "SELECT od.*, m.nama_menu, m.foto_menu, k.nama_kantin FROM transaksi od JOIN menu m ON od.id_menu = m.id_menu JOIN kantin k ON od.id_kantin = k.id_kantin WHERE od.id_order = '$id_o'");
                                        while ($d = mysqli_fetch_assoc($q_detail)) {
                                            echo "<li>";
                                            if (!empty($d['foto_menu'])) {
                                                echo "<img src='../uploads/menu/" . htmlspecialchars($d['foto_menu']) . "' alt='' class='menu-thumb-kecil'>";
                                            } else {
                                                echo "<div class='menu-thumb-kosong-kecil'>🍽️</div>";
                                            }
                                            echo "<span>[" . $d['nama_kantin'] . "] " . $d['nama_menu'] . " x" . $d['jumlah'] . "</span>";
                                            echo "</li>";
                                        }
                                        ?>
                                    </ul>
                                </td>
                                <td>
                                    <?php if ($order['status_pesanan'] == 'selesai'): ?>
                                        <a href="struk.php?id=<?php echo $order['id_order']; ?>" class="btn btn-hijau">Bukti Bayar</a>
                                    <?php else: ?>
                                        <span class="halaman-subjudul" style="margin:0;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php
                            }
                        } else {
                            $pesan_kosong = ($status_dipilih === 'semua')
                                ? 'Belum Ada Riwayat Pesanan.'
                                : 'Tidak Ada Pesanan dengan Status "' . ucfirst($status_dipilih) . '".';
                            echo "<tr><td colspan='8' align='center'>$pesan_kosong</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
