<?php
session_start();
include '../config/koneksi.php';

// proteksi akses khusus penjual
if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'penjual') {
    echo "<script>
    alert('Akses Ditolak');
    window.location.href='../login.php';
    </script>";
    exit;
}

$id_user = $_SESSION['id_user'];

// ambil data kantin penjual
$q_kantin = mysqli_query($conn, "SELECT * FROM kantin WHERE id_user = '$id_user'");
$data_kantin = mysqli_fetch_assoc($q_kantin);
$id_kantin = isset($data_kantin['id_kantin']) ? $data_kantin['id_kantin'] : 0;

// 1. proses tambah menu 
if (isset($_POST['tambah_menu'])) {
    $nama_menu = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $stok = mysqli_real_escape_string($conn, $_POST['stok']);
    $status_menu = mysqli_real_escape_string($conn, $_POST['status_menu']);

    // proses upload foto
    $filename = $_FILES['foto']['name'];
    if ($filename != "") {
        $rand = rand();
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $foto_baru = $rand . '_' . $filename;
        move_uploaded_file($_FILES['foto']['tmp_name'], '../assets/img/' . $foto_baru);
    } else {
        $foto_baru = 'default.jpg';
    }

    $q_insert = "INSERT INTO menu (id_kantin, nama_menu, harga, stok, status_menu, foto) VALUES ('$id_kantin', '$nama_menu', '$harga', '$stok', '$status_menu', '$foto_baru')";

    if (mysqli_query($conn, $q_insert)) {
        echo "<script>
        alert('Menu Berhasil Ditambahkan!');
        window.location.href='kelola_menu.php';
        </script>";
    } else {
        echo "<script>
        alert('Gagal Menambahkan Menu!');
        </script>";
    }

}

// 2. proses hapus menu
if (isset($_GET['hapus'])) {
    $id_menu = mysqli_real_escape_string($conn, $_GET['hapus']);

    $q_delete = "DELETE FROM menu WHERE id_menu = '$id_menu' AND id_kantin = '$id_kantin'";
    if (mysqli_query($conn, $q_delete)) {
        echo "<script>
        alert('Manu Berhasil Dihapus!');
        window.location.href='kelola_menu.php';
        </script>";
    }
}

// 3. proses ubah status stok
if (isset($_GET['toggle_status'])) {
    $id_menu = mysqli_real_escape_string($conn, $_GET['toggle_status']);
    $status_sekarang = $_GET['status'] == 'tersedia' ? 'habis' : 'tersedia';

    mysqli_query($conn, "UPDATE menu SET status_menu = '$status_menu' WHERE id_menu = '$id_menu' AND id_kantin = '$id_kantin'");
    echo "<script>
    window.location.href='kelola_menu.php';
    </script>";
}

// ambil daftar menu milik kantin
$q_menu = mysqli_query($conn, "SELECT * FROM menu WHERE id_kantin = '$id_kantin' ORDER BY id_menu DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Kantin</title>
</head>
<body>
    <h2>🍔 Kelola Menu Makanan & Minuman</h2>

    <!-- navigasi utama -->
     <p>
        <a href="dashboard.php">📌 Dashboard</a>
        <a href="kelola_menu.php">🍔 Kelola Menu</a>
        <a href="pesanan_masuk.php">🛎️ Pesanan Masuk</a>
        <a href="laporan.php">📊 Laporan Penjualan</a>
        <a href="../login.php" onclick="return confirm ('Yakin Ingin Keluar?')">Logout</a>
     </p>
     <hr>

    <!-- form tambah menu -->
      <h3>➕ Tambah Menu Baru</h3>
      <form action="" method="POST" enctype="multipart/form-data">
        <table cellpadding="5">
            <tr>
                <td>Nama Menu</td>
                <td>: <input type="text" name="nama_menu" required></td>
            </tr>
            <tr>
                <td>Harga (Rp)</td>
                <td>: <input type="number" name="harga" required></td>
            </tr>
            <tr>
                <td>Stok Awal</td>
                <td>: <input type="number" name="stok" value="50" required></td>
            </tr>
            <tr>
                <td>Status Stok</td>
                <td>: 
                    <select name="status_menu" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="habis">Habis</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Foto Menu</td>
                <td>: <input type="file" name="foto" accept="image/*"></td>
            </tr>
            <tr>
                <td></td>
                <td><button type="submit" name="tambah_menu">Simpan Menu Baru</button></td>
            </tr>
        </table>
      </form>

      <br><hr>

    <!-- tabel daftar menu -->
    <h3>📋 Daftar Menu Stand Anda</h3>
    <table border="1" cellpadding="8" cellspacing="0" widht="100%">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>No</th>
                <th>Foto</th>
                <th>Nama Menu</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Status</th>
                <th>Aksi / Ubah Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if (mysqli_num_rows($q_menu) > 0):
                while  ($m = mysqli_fetch_assoc($q_menu)):
            ?>
                <tr align="center">
                    <td><?php echo $no++; ?></td>
                    <td>
                        <img src="../assets/img<?php echo $m['foto']; ?>" width="60" height="60" alt="Foto Menu" style="object-fit:cover;">
                    </td>
                    <td align="left"><strong><?php echo htmlspecialchars($m['nama_menu']);?></strong></td>
                    <td>Rp <?php echo number_format($m['harga'], 0, ',', '.'); ?></td>
                    <td><?php echo $m['stok']; ?>Porsi</td>
                    <td>
                        <?php if ($m['status_menu'] == 'tersedia'): ?>
                            <span style="color: green; font-weight: bold;">TERSEDIA</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">HABIS</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- toggle status stok -->
                         <a href="kelola_menu.php?toggle_status=<?php echo $m['id_menu']; ?>&status=<?php echo $m['status_menu']; ?>">
                            [Ubah Jadi <?php echo $m['status_menu'] == 'tersedia' ? 'Habis' : 'Tersedia'; ?>]
                         </a> |

                         <!-- link hapus -->
                          <a href="kelola_menu.php?hapus=<?php echo $m['id_menu']; ?>"  onclick="return confirm('Yakin Ingin Menghapus Menghapus Menu Ini')">❌ Hapus</a>
                    </td>
                </tr>
            <?php
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="7" align="center">Belum Ada Menu yang Ditambahkan. Silahkan Tambah Menu Diatas</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>