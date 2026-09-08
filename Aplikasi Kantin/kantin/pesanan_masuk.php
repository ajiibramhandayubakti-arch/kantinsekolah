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

// proses ubah status pesanan
if (isset($_POST['update_status'])) {
    $id_order = mysqli_real_escape_string($conn, $_POST['id_order']);
    $status_baru = mysqli_real_escape_string($conn, $_POST['status_pesanan']);

    $q_update = mysqli_query($conn, "UPDATE orders SET status_pesanan = '$status_baru' WHERE id_order = '$id_order'");

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
$q_orders = mysqli_query($conn, "SELECT DISTINCT o.*, u.nama_user AS nama_siswa FROM orders o JOIN transaksi t ON o.id_order = t.id_order JOIN users u ON o.id_user = u.id_user WHERE t.id_kantin = '$id_kantin' ORDER BY o.id_order DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Masuk - Kantin</title>
    <link rel="stylesheet" href="../assets/css/kantin-style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="halaman-judul"> Kelola Pesanan Masuk</h2>
        <p class="halaman-subjudul">Ubah status pesanan sesuai progres di dapur/stand Anda.</p>

        <?php if (mysqli_num_rows($q_orders) > 0): ?>
            <?php while ($ord = mysqli_fetch_assoc($q_orders)): ?>
                <?php
                    $id_order = $ord['id_order'];

                    // ambil detail makanan/minuman yang dibeli khusus dari kantin ini saja
                    $q_detail = mysqli_query($conn, "SELECT t.*, m.nama_menu, m.harga
                                                    FROM transaksi t
                                                    JOIN menu m ON t.id_menu = m.id_menu
                                                    WHERE t.id_order = '$id_order' AND t.id_kantin = '$id_kantin'");

                    $total_belanja_kantin = 0;
                ?>

                <div class="kotak">

                    <div class="pesanan-kop">
                        <span>No Antrean <span class="nomor-antrean">#<?php echo $ord['nomor_antrean']; ?></span></span>
                        <span>ID Order: <strong>#<?php echo $ord['id_order']; ?></strong></span>
                        <span>Pemesan: <strong><?php echo htmlspecialchars($ord['nama_siswa']); ?></strong></span>
                        <span>Waktu: <?php echo $ord['tanggal_pesan']; ?></span>
                        <span class="badge <?php echo strtolower($ord['status_pesanan']); ?>"><?php echo strtoupper($ord['status_pesanan']); ?></span>
                    </div>

                    <div class="tabel-geser">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Menu</th>
                                    <th>Harga Satuan</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($det = mysqli_fetch_assoc($q_detail)): ?>
                                    <?php $total_belanja_kantin += $det['subtotal']; ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($det['nama_menu']); ?></td>
                                        <td>Rp <?php echo number_format($det['harga'], 0, ',', '.'); ?></td>
                                        <td><?php echo $det['jumlah']; ?> Porsi</td>
                                        <td>Rp <?php echo number_format($det['subtotal'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="pesanan-total">
                        <span>Total Kantin Ini:</span>
                        <span>Rp <?php echo number_format($total_belanja_kantin, 0, ',', '.'); ?></span>
                    </div>

                    <form action="" method="POST" class="pesanan-aksi">
                        <input type="hidden" name="id_order" value="<?php echo $ord['id_order']; ?>">
                        <label for="status_<?php echo $ord['id_order']; ?>">Status Pesanan:</label>

                        <select id="status_<?php echo $ord['id_order']; ?>" name="status_pesanan">
                            <option value="diproses" <?php echo ($ord['status_pesanan'] == 'diproses') ? 'selected' : ''; ?>> Diproses (Sedang Dimasak)</option>
                            <option value="selesai" <?php echo ($ord['status_pesanan'] == 'selesai') ? 'selected' : ''; ?>> Selesai (Sudah Diambil)</option>
                            <option value="dibatalkan" <?php echo ($ord['status_pesanan'] == 'dibatalkan') ? 'selected' : ''; ?>> Dibatalkan</option>
                        </select>

                        <button type="submit" name="update_status" class="btn btn-hijau btn-kecil">Update Status</button>
                    </form>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="kotak">
                <p>Belum ada pesanan masuk untuk stand kantin Anda saat ini.</p>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
