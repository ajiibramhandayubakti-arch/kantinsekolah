<?php
session_start();
include '../config/koneksi.php';


// proteksi akses siswa
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'siswa') {
    echo "<script>
    alert('Akses Ditolak! Khusus untuk Siswa!');
    window.location.href='login.php';
    </script>";
    exit;
}

// inisialisasi keranjang belanja
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = array();
}

// tambah keranjang 
if (isset($_POST['tambah_keranjang'])) {
    $id_menu = (int)$_POST['id_menu'];
    $jumlah = (int)$_POST['jumlah'];

    if (isset($_SESSION['keranjang'][$id_menu])) {
        $_SESSION['keranjang'][$id_menu] += $jumlah;
    } else {
        $_SESSION['keranjang'][$id_menu] = $jumlah;
    }

    echo "<script>
    alert('Menu Berhasil Ditambahkan ke Keranjang!');
    window.location.href='dashboard.php';
    </script>";
    exit;
}

// filter & search menu
$id_kantin_pilih = isset($_GET['kantin']) ? (int)$_GET['kantin'] : 0;
$keyword = isset($_GET['cari']) ? mysqli_real_escape_string($conn, $_GET['cari']) : '';

// query mengambil daftar kantin
$query_kantin = mysqli_query($conn, "SELECT * FROM kantin ORDER BY nama_kantin ASC");

// query mengambil menu berdasarkan filter/pencarian
$sql_menu = "SELECT menu.*, kantin.nama_kantin FROM menu JOIN kantin ON menu.id_kantin = kantin.id_kantin WHERE menu.status = 'tersedia' AND menu.stok > 0";

if ($id_kantin_pilih > 0) {
    $sql_menu .= " AND menu.id_kantin = '$id_kantin_pilih'";
}

if (!empty($keyword)) {
    $sql_menu .= " AND menu.nama_menu LIKE '%$keyword%'";
}

$sql_menu .= " ORDER BY menu.id_menu DESC";
$query_menu = mysqli_query($conn, $sql_menu);

// hitung item keranjang
$total_item_keranjang = array_sum($_SESSION['keranjang']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Siswa</title>
</head>
<body>
    <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama_user']); ?>! 👋</h2>
    <p>
        <a href="keranjang.php">🛒 Keranjang Belanja (<strong><?php echo $total_item_keranjang; ?></strong>)</a> |
        <a href="riwayat.php">📋 Riwayat Pesanan</a> |
        <a href="../login.php" onclick="return confirm('Yakin Ingin Logout?')">Logout</a>
    </p>
    <hr>

    <!-- form pencarian menu -->
     <h3>🔍 Cari Makanan / Minuman</h3>
     <form action="" method="GET">
        <?php if ($id_kantin_pilih > 0): ?>
            <input type="hidden" name="kantin" value="<?php echo $id_kantin_pilih; ?>">
        <?php endif; ?>
        <input type="text" name="cari" placeholder="Ketik Nama Makanan/Minuman..." value="<?php echo htmlspecialchars($keyword); ?>">
        <button type="submit">Cari</button>
        <?php if (!empty($keyword)): ?>
            <a href="dashboard.php<?php echo ($id_kantin_pilih > 0) ? '?kantin='.$id_kantin_pilih : ''; ?>">Riset Pencarian</a>
        <?php endif; ?>
     </form>
    <br>

    <!-- pilih stand kantin -->
     <h3>🏪 Pilih Stand Kantin</h3>
     <a href="dashboard.php"><strong>[ Semua Kantin ]</strong></a> &nbsp;
     <?php while ($k = mysqli_fetch_assoc($query_kantin)): ?>
        <a href="dashboard.php?kantin=<?php echo $k['id_kantin']; ?>">
            [ <?php echo htmlspecialchars($k['nama_kantin']); ?> ]
        </a> &nbsp;
     <?php endwhile; ?>
     <br><br>
     <hr>

     <!-- daftar menu -->
      <h3>🍔 Daftar Menu Makanan & Minuman</h3>
      <table border='1' cellpadding='10' cellspacing='0'>
        <thead>
            <tr>
                <th>No</th>
                <th>Stand Kantin</th>
                <th>Nama Menu</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Pesan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if(mysqli_num_rows($query_menu) > 0) {
                while ($menu = mysqli_fetch_assoc($query_menu)) {
            ?>

                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><strong><?php echo htmlspecialchars($menu['nama_kantin']); ?></strong></td>
                    <td><?php echo htmlspecialchars($menu['nama_menu']); ?></td>
                    <td>Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></td>
                    <td><?php echo $menu['stok']; ?></td>
                    <td>
                        <form action="" method="POST" style="margin: 0;">
                            <input type="hidden" name="id_menu" value="<?php echo $menu['id_menu']; ?>">
                            <input type="number" name="jumlah" value="1" min="1" max="<?php echo $menu['stok']; ?>" style="width: 50px;"required>
                            <button type="submit" name="tambah_keranjang">+  Ke Keranjang</button>
                        </form>
                    </td>
                </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='6' align='center'>Menu Tidak Ditemukan Atau Stok Habis.</td></tr>";
            }
            ?>
        </tbody>
      </table>
</body>
</html>