<?php
session_start();
include '../config/koneksi.php';

// proteksi akses penjual
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'penjual') {
    echo "<script>
    alert('Akses Ditolak!');
    window.location.href='../login.php';
    </script>";
    exit;
}

$id_user = $_SESSION['id_user'];

// ambil data kantin penjual
$q_kantin = mysqli_query($conn, "SELECT * FROM kantin WHERE id_user = '$id_user'");
$data_kantin = mysqli_fetch_assoc($q_kantin);
$id_kantin = isset($data_kantin['id_kantin']) ? $data_kantin['id_kantin'] : 0;

// rentang tanggal laporan, default: awal bulan ini s/d hari ini
$tanggal_mulai   = isset($_GET['mulai']) ? mysqli_real_escape_string($conn, $_GET['mulai']) : date('Y-m-01');
$tanggal_selesai = isset($_GET['selesai']) ? mysqli_real_escape_string($conn, $_GET['selesai']) : date('Y-m-d');

// preset cepat, hanya untuk kasih tanda "aktif" di tombol filter
$preset_aktif = 'custom';
if ($tanggal_mulai == date('Y-m-d') && $tanggal_selesai == date('Y-m-d')) {
    $preset_aktif = 'hari_ini';
} elseif ($tanggal_mulai == date('Y-m-d', strtotime('monday this week')) && $tanggal_selesai == date('Y-m-d')) {
    $preset_aktif = 'minggu_ini';
} elseif ($tanggal_mulai == date('Y-m-01') && $tanggal_selesai == date('Y-m-d')) {
    $preset_aktif = 'bulan_ini';
}

// hanya pesanan berstatus selesai yang dihitung sebagai penjualan sah
$kondisi_dasar = "t.id_kantin = '$id_kantin' AND o.status_pesanan = 'selesai' AND DATE(o.tanggal_pesan) BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'";

// 1. ringkasan: total pendapatan & jumlah pesanan pada rentang tanggal
$q_ringkasan = mysqli_query($conn, "SELECT COUNT(DISTINCT o.id_order) AS total_pesanan, SUM(t.subtotal) AS total_pendapatan
                                    FROM orders o JOIN transaksi t ON o.id_order = t.id_order
                                    WHERE $kondisi_dasar");
$d_ringkasan     = mysqli_fetch_assoc($q_ringkasan);
$total_pesanan   = $d_ringkasan['total_pesanan'] ? $d_ringkasan['total_pesanan'] : 0;
$total_pendapatan = $d_ringkasan['total_pendapatan'] ? $d_ringkasan['total_pendapatan'] : 0;
$rata_rata       = $total_pesanan > 0 ? $total_pendapatan / $total_pesanan : 0;

// 2. produk terlaris (top 5 berdasarkan jumlah porsi terjual)
$q_terlaris = mysqli_query($conn, "SELECT m.nama_menu, SUM(t.jumlah) AS total_terjual, SUM(t.subtotal) AS total_omzet
                                    FROM transaksi t
                                    JOIN menu m ON t.id_menu = m.id_menu
                                    JOIN orders o ON t.id_order = o.id_order
                                    WHERE $kondisi_dasar
                                    GROUP BY t.id_menu
                                    ORDER BY total_terjual DESC
                                    LIMIT 5");

// 3. riwayat transaksi (per pesanan) pada rentang tanggal
$q_riwayat = mysqli_query($conn, "SELECT o.id_order, o.nomor_antrean, o.tanggal_pesan, u.nama_user, SUM(t.subtotal) AS total_kantin
                                FROM orders o
                                JOIN transaksi t ON o.id_order = t.id_order
                                JOIN users u ON o.id_user = u.id_user
                                WHERE $kondisi_dasar
                                GROUP BY o.id_order
                                ORDER BY o.tanggal_pesan DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Kantin</title>
    <link rel="stylesheet" href="../assets/css/kantin-style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="halaman-judul"> Laporan Penjualan</h2>
        <p class="halaman-subjudul">Pantau performa penjualan stand Anda per periode.</p>

        <!-- filter rentang tanggal -->
        <div class="kotak">
            <h3> Pilih Periode</h3>

            <form action="" method="GET" class="form-cari">
                <div class="form-group">
                    <label for="mulai">Dari Tanggal</label>
                    <input type="date" id="mulai" name="mulai" value="<?php echo htmlspecialchars($tanggal_mulai); ?>">
                </div>
                <div class="form-group">
                    <label for="selesai">Sampai Tanggal</label>
                    <input type="date" id="selesai" name="selesai" value="<?php echo htmlspecialchars($tanggal_selesai); ?>">
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-hijau">Tampilkan</button>
                </div>
            </form>

            <div class="filter-tab-bar">
                <a href="?mulai=<?php echo date('Y-m-d'); ?>&selesai=<?php echo date('Y-m-d'); ?>" class="filter-tab <?php echo ($preset_aktif == 'hari_ini') ? 'aktif' : ''; ?>">Hari Ini</a>
                <a href="?mulai=<?php echo date('Y-m-d', strtotime('monday this week')); ?>&selesai=<?php echo date('Y-m-d'); ?>" class="filter-tab <?php echo ($preset_aktif == 'minggu_ini') ? 'aktif' : ''; ?>">Minggu Ini</a>
                <a href="?mulai=<?php echo date('Y-m-01'); ?>&selesai=<?php echo date('Y-m-d'); ?>" class="filter-tab <?php echo ($preset_aktif == 'bulan_ini') ? 'aktif' : ''; ?>">Bulan Ini</a>
            </div>
        </div>

        <!-- ringkasan angka -->
        <div class="kotak">
            <h3> Ringkasan Periode <?php echo date('d M Y', strtotime($tanggal_mulai)) . ' – ' . date('d M Y', strtotime($tanggal_selesai)); ?></h3>

            <div class="stat-grid">
                <div class="stat-card hijau">
                    <div class="stat-label"> Total Pendapatan</div>
                    <div class="stat-angka">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
                </div>
                <div class="stat-card biru">
                    <div class="stat-label"> Jumlah Pesanan Selesai</div>
                    <div class="stat-angka"><?php echo $total_pesanan; ?> Pesanan</div>
                </div>
                <div class="stat-card kuning">
                    <div class="stat-label"> Rata-rata / Pesanan</div>
                    <div class="stat-angka">Rp <?php echo number_format($rata_rata, 0, ',', '.'); ?></div>
                </div>
            </div>
        </div>

        <!-- produk terlaris -->
        <div class="kotak">
            <h3> Produk Terlaris</h3>

            <?php if (mysqli_num_rows($q_terlaris) > 0): ?>
                <div class="produk-list">
                    <?php $peringkat = 1; while ($p = mysqli_fetch_assoc($q_terlaris)): ?>
                        <div class="produk-item">
                            <div class="produk-rank">#<?php echo $peringkat++; ?></div>
                            <div class="produk-info">
                                <div class="produk-nama"><?php echo htmlspecialchars($p['nama_menu']); ?></div>
                                <div class="produk-meta"><?php echo $p['total_terjual']; ?> porsi terjual</div>
                            </div>
                            <div class="produk-omzet">Rp <?php echo number_format($p['total_omzet'], 0, ',', '.'); ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Belum ada penjualan pada periode ini.</p>
            <?php endif; ?>
        </div>

        <!-- riwayat transaksi -->
        <div class="kotak">
            <h3> Riwayat Transaksi</h3>

            <div class="tabel-geser">
                <table>
                    <thead>
                        <tr>
                            <th>No Antrean</th>
                            <th>ID Order</th>
                            <th>Pemesan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($q_riwayat) > 0): ?>
                            <?php while ($r = mysqli_fetch_assoc($q_riwayat)): ?>
                                <tr>
                                    <td><span class="nomor-antrean">#<?php echo $r['nomor_antrean']; ?></span></td>
                                    <td>#<?php echo $r['id_order']; ?></td>
                                    <td><?php echo htmlspecialchars($r['nama_user']); ?></td>
                                    <td><?php echo $r['tanggal_pesan']; ?></td>
                                    <td>Rp <?php echo number_format($r['total_kantin'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" align="center">Tidak ada transaksi pada periode ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>