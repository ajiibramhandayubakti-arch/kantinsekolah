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
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // proses upload foto
    $filename = $_FILES['foto']['name'];
    if ($filename != "") {
        $rand = rand();
        $foto_baru = $rand . '_' . $filename;
        move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/menu/' . $foto_baru);
    } else {
        $foto_baru = '';
    }

    // nama kolom foto di tabel menu adalah foto_menu, bukan foto
    $q_insert = "INSERT INTO menu (id_kantin, nama_menu, harga, stok, status, foto_menu) VALUES ('$id_kantin', '$nama_menu', '$harga', '$stok', '$status', '$foto_baru')";

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
        alert('Menu Berhasil Dihapus!');
        window.location.href='kelola_menu.php';
        </script>";
    }
}

// 3. proses ubah status stok
if (isset($_GET['toggle_status'])) {
    $id_menu = mysqli_real_escape_string($conn, $_GET['toggle_status']);
    $status_sekarang = $_GET['status'] == 'tersedia' ? 'habis' : 'tersedia';

    mysqli_query($conn, "UPDATE menu SET status = '$status_sekarang' WHERE id_menu = '$id_menu' AND id_kantin = '$id_kantin'");
    echo "<script>
    window.location.href='kelola_menu.php';
    </script>";
}

// 4. proses edit/ubah menu
if (isset($_POST['edit_menu'])) {
    $id_menu   = (int)$_POST['id_menu'];
    $nama_menu = mysqli_real_escape_string($conn, $_POST['nama_menu']);
    $harga     = mysqli_real_escape_string($conn, $_POST['harga']);
    $stok      = mysqli_real_escape_string($conn, $_POST['stok']);
    $status    = mysqli_real_escape_string($conn, $_POST['status']);

    // foto bersifat opsional saat edit: hanya diganti kalau penjual upload file baru,
    // kalau tidak, foto lama tetap dipakai (kolomnya tidak disentuh)
    $filename = $_FILES['foto']['name'];
    if ($filename != "") {
        $rand      = rand();
        $foto_baru = $rand . '_' . $filename;
        move_uploaded_file($_FILES['foto']['tmp_name'], '../uploads/menu/' . $foto_baru);

        $q_update = "UPDATE menu SET nama_menu = '$nama_menu', harga = '$harga', stok = '$stok', status = '$status', foto_menu = '$foto_baru'
                     WHERE id_menu = '$id_menu' AND id_kantin = '$id_kantin'";
    } else {
        $q_update = "UPDATE menu SET nama_menu = '$nama_menu', harga = '$harga', stok = '$stok', status = '$status'
                     WHERE id_menu = '$id_menu' AND id_kantin = '$id_kantin'";
    }

    if (mysqli_query($conn, $q_update)) {
        echo "<script>
        alert('Menu Berhasil Diubah!');
        window.location.href='kelola_menu.php';
        </script>";
    } else {
        echo "<script>
        alert('Gagal Mengubah Menu!');
        </script>";
    }
    exit;
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
    <link rel="stylesheet" href="../assets/css/kantin-style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="halaman-judul"> Kelola Menu Makanan & Minuman</h2>
        <p class="halaman-subjudul">Tambah, ubah status stok, atau hapus menu di stand Anda.</p>

        <div class="kotak">
            <h3> Tambah Menu Baru</h3>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama_menu">Nama Menu</label>
                        <input type="text" id="nama_menu" name="nama_menu" required>
                    </div>

                    <div class="form-group">
                        <label for="harga">Harga (Rp)</label>
                        <input type="number" id="harga" name="harga" required>
                    </div>

                    <div class="form-group">
                        <label for="stok">Stok Awal</label>
                        <input type="number" id="stok" name="stok" value="50" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Status Stok</label>
                        <select id="status" name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="habis">Habis</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="foto">Foto Menu</label>
                        <input type="file" id="foto" name="foto" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" name="tambah_menu" class="btn btn-hijau">Simpan Menu Baru</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="kotak">
            <h3> Daftar Menu Stand Anda</h3>

            <div class="tabel-geser">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Nama Menu</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($q_menu) > 0):
                            while ($m = mysqli_fetch_assoc($q_menu)):
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <?php if (!empty($m['foto_menu'])): ?>
                                        <img src="../uploads/menu/<?php echo htmlspecialchars($m['foto_menu']); ?>" alt="Foto Menu" class="menu-thumb">
                                    <?php else: ?>
                                        <div class="menu-thumb-kosong">🍽️</div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($m['nama_menu']); ?></strong></td>
                                <td>Rp <?php echo number_format($m['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo $m['stok']; ?> Porsi</td>
                                <td><span class="badge <?php echo $m['status']; ?>"><?php echo strtoupper($m['status']); ?></span></td>
                                <td>
                                    <button type="button"
                                        class="btn btn-kecil"
                                        onclick='bukaEditModal(<?php echo htmlspecialchars(json_encode([
                                            "id"     => $m["id_menu"],
                                            "nama"   => $m["nama_menu"],
                                            "harga"  => $m["harga"],
                                            "stok"   => $m["stok"],
                                            "status" => $m["status"],
                                            "foto"   => $m["foto_menu"],
                                        ]), ENT_QUOTES, "UTF-8"); ?>)'>
                                        ✏️ Edit
                                    </button>
                                    <a href="kelola_menu.php?toggle_status=<?php echo $m['id_menu']; ?>&status=<?php echo $m['status']; ?>" class="btn btn-kecil">
                                        Ubah Jadi <?php echo $m['status'] == 'tersedia' ? 'Habis' : 'Tersedia'; ?>
                                    </a>
                                    <a href="kelola_menu.php?hapus=<?php echo $m['id_menu']; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus menu ini?')">❌ Hapus</a>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr><td colspan="7" align="center">Belum ada menu yang ditambahkan. Silakan tambah menu di atas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ============ MODAL EDIT MENU ============ -->
    <div class="modal-overlay" id="modalEdit" onclick="tutupEditModalKlikLuar(event)">
        <div class="modal-box">
            <button type="button" class="modal-close" onclick="tutupEditModal()">&times;</button>
            <h3>Edit Menu</h3>
            <p class="halaman-subjudul" style="margin-bottom: 16px;">Ubah detail menu, lalu simpan perubahan.</p>

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_menu" id="edit_id_menu">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_nama_menu">Nama Menu</label>
                        <input type="text" id="edit_nama_menu" name="nama_menu" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_harga">Harga (Rp)</label>
                        <input type="number" id="edit_harga" name="harga" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_stok">Stok</label>
                        <input type="number" id="edit_stok" name="stok" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_status">Status Stok</label>
                        <select id="edit_status" name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="habis">Habis</option>
                        </select>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="edit_foto">Ganti Foto (opsional)</label>
                        <input type="file" id="edit_foto" name="foto" accept="image/*">
                        <small id="edit_foto_info" style="display: block; margin-top: 4px; color: var(--abu-teks); font-size: 12px;"></small>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <button type="submit" name="edit_menu" class="btn btn-hijau" style="width: 100%;">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaEditModal(data) {
            document.getElementById('edit_id_menu').value = data.id;
            document.getElementById('edit_nama_menu').value = data.nama;
            document.getElementById('edit_harga').value = data.harga;
            document.getElementById('edit_stok').value = data.stok;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_foto').value = ''; // reset input file tiap buka modal
            document.getElementById('edit_foto_info').textContent = data.foto
                ? 'Foto saat ini: ' + data.foto + ' (biarkan kosong kalau tidak ingin ganti foto)'
                : 'Belum ada foto (opsional, boleh diisi sekarang)';

            document.getElementById('modalEdit').classList.add('aktif');
        }

        function tutupEditModal() {
            document.getElementById('modalEdit').classList.remove('aktif');
        }

        // tutup modal kalau klik di area gelap luar kotak, bukan di dalam kotak modal-nya
        function tutupEditModalKlikLuar(event) {
            if (event.target.id === 'modalEdit') {
                tutupEditModal();
            }
        }
    </script>
</body>
</html>
