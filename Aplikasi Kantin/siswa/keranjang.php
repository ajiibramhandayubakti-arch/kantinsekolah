<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'siswa') {
    header("Locatin: login.php");
    exit;
}

// hapus item keranjang
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    unset($_SESSION['keranjang'][$id_hapus]);
    echo "<script>
    alert('Item Dihapus dari Keranjang');
    window.location.href='keranjang.php';
    </script>";
    exit;
}

// kosongkan keranjang
if (isset($_GET['kosongkan'])) {
    $_SESSION['keranjang'] = array();
    header("Location: keranjang.php");
    exit;
}

// proses checkout & pendaftaran
if (isset($_POST['checkout'])) {
    if (empty($_SESSION['keranjang'])) {
        echo "<script>
        alert('Keranjang Anda Kosong!');
        window.location.href='dashboard.php';
        </script>";
        exit;
    }

    $id_user = $_SESSION['id_user'];
    $metode_bayar = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);
    $total_harga = (int)$_POST['total_harga'];
    $nomor_antrean = rand(100, 999); 

    // 1. simpan transaksi ke tabel 'orders'
    $query_order = "INSERT INTO orders (id_user, total_harga, nomor_antrean, status_pesanan) VALUES ('$id_user', '$total_harga', '$nomor_antrean', 'diproses')";

    if (mysqli_query($conn, $query_order)) {
        $id_order = mysqli_insert_id($conn);

        // 2. simpan detail item ke 'transaksi' & kurangi stok menu
        foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
            $q_menu = mysqli_query($conn, "SELECT id_kantin, harga, stok FROM menu WHERE id = '$id_menu'");
            $d_menu = mysqli_fetch_assoc($q_menu);

            $id_kantin = $d_menu['id_kantin'];
            $subtotal = $d_menu['harga'] * $jumlah;

            // simpann detail
            mysqli_query($conn, "INSERT INTO transaksi (id_order, id_kantin, id_menu, jumlah, subtotal) VALUES ('$id_order', '$id_menu', '$jumlah', '$subtotal')");

            // kurangi stok menu 
            $stok_baru = $d_menu['stok'] - $jumlah;
            $status_baru = ($stok_baru <= 0) ? 'habis' : 'tersedia';
            mysqli_query($conn, "UPDATE menu SET stok = '$stok_baru', status = '$status_baru' WHERE id = '$id_menu'");
        }

        // kosongkan keranjang belanja
        $_SESSION['keranjang'] = array();
        echo "<script>
        alert('Pesanan Berhasil Dibuat! Nomor Antrean Anda: #$nomor_antrean');
        window.location.href=''riwayat.php;
        </script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
</head>
<body>
    <h2>🛒 Keranjang Belanja</h2>
    <a href="dashboard.php">← Kembali ke Dashboard Menu</a>
    <br><br>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Stand Kantin</th>
                <th>Nama Menu</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $total_bayar = 0;

            if (!empty($_SESSION['keranjang'])) {
                foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
                    $query = mysqli_query($conn, "SELECT menu.*, kantin.nama_kantin FROM menu JOIN kantin ON menu.id_kantin = kantin.id_kantin WHERE menu.id_menu = '$id_menu'");

                    $data = mysqli_fetch_assoc($query);
                    $subtotal = $data['harga'] * $jumlah;
                    $total_bayar += $subtotal;
            ?>

                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($data['nama_kantin']); ?></td>
                    <td><?php echo htmlspecialchars($data['nama_menu']); ?></td>
                    <td>Rp <?php echo number_format($data['harga'], 0, ',', '.'); ?></td>
                    <td><?php echo $jumlah; ?></td>
                    <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                    <td><a href="keranjang.php?hapus=<?php echo $id_menu; ?>">Hapus</a></td>
                </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='7' align='center'>Keranjang Belanja Masih Kosong.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php if (!empty($_SESSION['keranjang'])): ?>
        <hr>
        <h3>Total Pembayaran: Rp <?php echo number_format($total_bayar, 0, ',', '.'); ?></h3>

        <form action="" method="POST">
            <input type="hidden" name="total_harga" value="<?php echo  $total_bayar; ?>">
            <input type="hidden" name="metode_pembayaran" value="QRIS">

            <!-- tampilan pembayaran qris -->
             <div style="border: 1px solid #ccc; padding: 15px; width: 300px; text-align: center; borde-raduis: 8px;">
                <h4 style="margin: 0 0 10px 0;">Pembayaran Via QRIS</h4>
                <p style="font-size: 12px color: #555;">Support: DANA, GoPay, OVO, ShoppePay, LinkAja, dan Mobile Banking</p>

                <!-- ganti src dengan link/path gambar QRIS kantin
                 <img src="" alt=""> -->

                 <p style="font-size: 13px; margin-top: 10px;">
                    Silahkan Scan QRIS Diatas Sesuai Nominal <strong>Rp <?php echo number_format($total_bayar, 0, ",", "."); ?>Lalu Tekan  Tombol  Dibawah.</strong>
                 </p>
             </div>
             <br>

             <button type="submit" name="checkout" onclick="return confirm('Apakah Anda Sudah Melakukan Pembayaran?')">
                ✅ Konfirmasi & Pesan Sekarang
             </button>

             <a href="keranjang.php?kosongkan=1" onclick="return confirm('Kosongkan Keranjang?')">Kosongkan Keranjang</a>
        </form>
    <?php endif; ?>
</body>
</html>