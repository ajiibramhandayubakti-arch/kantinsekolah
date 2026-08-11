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


/* =========================================================
   2. AKSI: UBAH ISI KERANJANG (GET: tambah/kurang/hapus/kosongkan)
   ========================================================= */

// tambah jumlah salah satu item di keranjang (tidak boleh melebihi stok)
if (isset($_GET['tambah_qty'])) {
    $id_menu = (int)$_GET['tambah_qty'];

    $q_stok = mysqli_query($conn, "SELECT stok FROM menu WHERE id_menu = '$id_menu'");
    $d_stok = mysqli_fetch_assoc($q_stok);

    if ($d_stok && isset($_SESSION['keranjang'][$id_menu]) && $_SESSION['keranjang'][$id_menu] < $d_stok['stok']) {
        $_SESSION['keranjang'][$id_menu]++;
    }
    header("Location: keranjang.php");
    exit;
}

// kurangi jumlah salah satu item di keranjang (kalau sampai 0, otomatis hilang dari keranjang)
if (isset($_GET['kurang_qty'])) {
    $id_menu = (int)$_GET['kurang_qty'];

    if (isset($_SESSION['keranjang'][$id_menu])) {
        $_SESSION['keranjang'][$id_menu]--;

        if ($_SESSION['keranjang'][$id_menu] <= 0) {
            unset($_SESSION['keranjang'][$id_menu]);
        }
    }
    header("Location: keranjang.php");
    exit;
}

// hapus item keranjang
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    unset($_SESSION['keranjang'][$id_hapus]);
    header("Location: keranjang.php?notif=sukses&pesan=" . urlencode('Item dihapus dari keranjang'));
    exit;
}

// kosongkan keranjang
if (isset($_GET['kosongkan'])) {
    $_SESSION['keranjang'] = array();
    header("Location: keranjang.php?notif=sukses&pesan=" . urlencode('Keranjang berhasil dikosongkan'));
    exit;
}

/* =========================================================
   3. AKSI: PROSES CHECKOUT (POST)
   Hanya memproses item yang dicentang siswa di form keranjang
   ========================================================= */
if (isset($_POST['checkout'])) {

    if (empty($_SESSION['keranjang'])) {
        header("Location: dashboard.php?notif=error&pesan=" . urlencode('Keranjang Anda masih kosong'));
        exit;
    }

    // ambil id item yang dicentang dari form, lalu bersihkan jadi array integer
    $item_dipilih_mentah = isset($_POST['item_dipilih']) ? $_POST['item_dipilih'] : array();
    $item_dipilih = array();
    foreach ($item_dipilih_mentah as $id) {
        $id = (int)$id;
        // pastikan id yang dikirim memang ada di keranjang session (jangan percaya input client mentah-mentah)
        if ($id > 0 && isset($_SESSION['keranjang'][$id])) {
            $item_dipilih[] = $id;
        }
    }

    if (empty($item_dipilih)) {
        header("Location: keranjang.php?notif=error&pesan=" . urlencode('Pilih minimal 1 item untuk dibayar'));
        exit;
    }

    $id_user = $_SESSION['id_user'];
    $metode_bayar = isset($_POST['metode_pembayaran']) ? mysqli_real_escape_string($conn, $_POST['metode_pembayaran']) : 'QRIS';

    // total dihitung ULANG di server (bukan percaya nilai dari client) supaya tidak bisa dimanipulasi
    $total_harga = 0;
    $data_menu_dipilih = array(); // simpan biar tidak query 2x

    foreach ($item_dipilih as $id_menu) {
        $jumlah = (int)$_SESSION['keranjang'][$id_menu];
        $q_menu = mysqli_query($conn, "SELECT id_kantin, harga, stok FROM menu WHERE id_menu = '$id_menu'");
        $d_menu = mysqli_fetch_assoc($q_menu);

        if (!$d_menu) continue;

        $subtotal = $d_menu['harga'] * $jumlah;
        $total_harga += $subtotal;

        $data_menu_dipilih[$id_menu] = array(
            'jumlah'    => $jumlah,
            'id_kantin' => $d_menu['id_kantin'],
            'subtotal'  => $subtotal,
            'stok'      => $d_menu['stok'],
        );
    }

    // 1. simpan transaksi ke tabel 'orders' dulu (nomor_antrean diisi sementara, diupdate setelah dapat id_order)
    $query_order = "INSERT INTO orders (id_user, total_harga, nomor_antrean, status_pesanan) VALUES ('$id_user', '$total_harga', '', 'diproses')";

    if (mysqli_query($conn, $query_order)) {
        $id_order = mysqli_insert_id($conn);

        // bikin ID Pembayaran yang rapi & berurutan (bukan angka acak lagi), format: PAY-YYMMDD-0001
        $id_pembayaran = 'PAY-' . date('ymd') . '-' . str_pad($id_order, 4, '0', STR_PAD_LEFT);
        mysqli_query($conn, "UPDATE orders SET nomor_antrean = '$id_pembayaran' WHERE id_order = '$id_order'");

        // 2. simpan detail item terpilih ke 'transaksi' & kurangi stok menu
        foreach ($data_menu_dipilih as $id_menu => $d) {
            mysqli_query($conn, "INSERT INTO transaksi (id_order, id_kantin, id_menu, jumlah, subtotal) VALUES ('$id_order', '{$d['id_kantin']}', '$id_menu', '{$d['jumlah']}', '{$d['subtotal']}')");

            $stok_baru = $d['stok'] - $d['jumlah'];
            $status_baru = ($stok_baru <= 0) ? 'habis' : 'tersedia';
            mysqli_query($conn, "UPDATE menu SET stok = '$stok_baru', status = '$status_baru' WHERE id_menu = '$id_menu'");

            // hapus HANYA item yang barusan dibayar dari keranjang, item lain yang tidak dicentang tetap ada
            unset($_SESSION['keranjang'][$id_menu]);
        }

        header("Location: riwayat.php?checkout=sukses&id=" . urlencode($id_pembayaran));
        exit;
    }
}

/* =========================================================
   4. AMBIL DATA UNTUK TAMPILAN
   ========================================================= */
$baris_keranjang = array();
if (!empty($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
        $query = mysqli_query($conn, "SELECT menu.*, kantin.nama_kantin FROM menu JOIN kantin ON menu.id_kantin = kantin.id_kantin WHERE menu.id_menu = '$id_menu'");
        $data = mysqli_fetch_assoc($query);
        if (!$data) continue;
        $data['jumlah']   = $jumlah;
        $data['subtotal'] = $data['harga'] * $jumlah;
        $baris_keranjang[$id_menu] = $data;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="../assets/css/siswa-style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="halaman-judul">🛒 Keranjang Belanja</h2>
        <p class="halaman-subjudul">Centang pesanan yang mau dibayar, sisanya boleh disimpan dulu di keranjang.</p>

        <div class="kotak">
            <div class="tabel-geser">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="pilih-semua" title="Pilih Semua"></th>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Stand Kantin</th>
                            <th>Nama Menu</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($baris_keranjang)): ?>
                            <?php $no = 1; foreach ($baris_keranjang as $id_menu => $data): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox"
                                               class="cek-item"
                                               name="item_dipilih[]"
                                               value="<?php echo $id_menu; ?>"
                                               data-subtotal="<?php echo $data['subtotal']; ?>"
                                               form="form-checkout"
                                               checked
                                               onchange="hitungTotal()">
                                    </td>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <?php if (!empty($data['foto_menu'])): ?>
                                            <img src="../uploads/menu/<?php echo htmlspecialchars($data['foto_menu']); ?>" alt="<?php echo htmlspecialchars($data['nama_menu']); ?>" class="menu-thumb-kecil">
                                        <?php else: ?>
                                            <div class="menu-thumb-kosong-kecil">🍽️</div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($data['nama_kantin']); ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_menu']); ?></td>
                                    <td>Rp <?php echo number_format($data['harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <div class="qty-control">
                                            <a href="keranjang.php?kurang_qty=<?php echo $id_menu; ?>" class="btn-qty">-</a>
                                            <span class="qty-angka"><?php echo $data['jumlah']; ?></span>
                                            <a href="keranjang.php?tambah_qty=<?php echo $id_menu; ?>" class="btn-qty">+</a>
                                        </div>
                                    </td>
                                    <td>Rp <?php echo number_format($data['subtotal'], 0, ',', '.'); ?></td>
                                    <td>
                                        <a href="keranjang.php?hapus=<?php echo $id_menu; ?>"
                                           class="btn-hapus"
                                           data-konfirmasi="Hapus '<?php echo htmlspecialchars($data['nama_menu'], ENT_QUOTES); ?>' dari keranjang?"
                                           data-judul-konfirmasi="Hapus Item"
                                           data-bahaya="1">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" align="center">Keranjang Belanja Masih Kosong.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($baris_keranjang)): ?>
            <div class="kotak">

                <form action="" method="POST" id="form-checkout">
                    <input type="hidden" name="total_harga" id="input-total-harga" value="0">
                    <input type="hidden" name="metode_pembayaran" id="input-metode-bayar" value="QRIS">

                    <div class="total-bayar">
                        <span>Total Pembayaran <small id="jumlah-terpilih-teks">(0 item dipilih)</small></span>
                        <strong id="tampil-total-bayar">Rp 0</strong>
                    </div>

                    <button type="button" class="btn btn-hijau" id="btn-lanjut-bayar" onclick="bukaModalBayar()" disabled>
                        Lanjut ke Pembayaran →
                    </button>
                </form>

                <a href="keranjang.php?kosongkan=1"
                   class="btn-hapus"
                   style="margin-left:14px;"
                   data-konfirmasi="Kosongkan seluruh keranjang belanja?"
                   data-judul-konfirmasi="Kosongkan Keranjang"
                   data-bahaya="1">
                    Kosongkan Keranjang
                </a>
            </div>

            <!-- ================= MODAL PILIH METODE PEMBAYARAN ================= -->
            <div class="modal-overlay" id="modal-bayar">
                <div class="modal-box">
                    <button type="button" class="modal-close" onclick="tutupModalBayar()">&times;</button>

                    <h3>Pilih Metode Pembayaran</h3>
                    <p class="halaman-subjudul" style="margin-bottom:14px;">
                        Total yang harus dibayar: <strong id="modal-total-bayar">Rp 0</strong>
                    </p>

                    <div class="metode-pilihan">
                        <label>
                            <input type="radio" name="pilih_metode" value="QRIS" checked onchange="pilihMetode(this)">
                            <span>QRIS</span>
                        </label>
                        <label>
                            <input type="radio" name="pilih_metode" value="GoPay" onchange="pilihMetode(this)">
                            <span>GoPay</span>
                        </label>
                        <label>
                            <input type="radio" name="pilih_metode" value="OVO" onchange="pilihMetode(this)">
                            <span>OVO</span>
                        </label>
                        <label>
                            <input type="radio" name="pilih_metode" value="DANA" onchange="pilihMetode(this)">
                            <span>DANA</span>
                        </label>
                        <label>
                            <input type="radio" name="pilih_metode" value="ShopeePay" onchange="pilihMetode(this)">
                            <span>ShopeePay</span>
                        </label>
                    </div>

                    <div class="kotak-qris">
                        <h4>Scan QRIS pakai <span id="modal-nama-metode">QRIS</span></h4>
                        <p>Support: DANA, GoPay, OVO, ShopeePay, LinkAja, dan Mobile Banking</p>

                        <!-- ganti src dengan link/path gambar QRIS kantin
                         <img src="" alt="QRIS Kantin"> -->

                        <p>
                            Scan kode QRIS di atas sesuai nominal
                            <strong id="modal-total-bayar-2">Rp 0</strong>,
                            lalu tekan tombol konfirmasi di bawah.
                        </p>
                    </div>

                    <div class="struk-aksi">
                        <button type="submit" form="form-checkout" name="checkout" class="btn btn-hijau"
                                data-konfirmasi="Apakah Anda sudah melakukan pembayaran sesuai nominal di atas?"
                                data-judul-konfirmasi="Konfirmasi Pembayaran">
                            ✅ Konfirmasi & Pesan Sekarang
                        </button>
                        <button type="button" class="btn" onclick="tutupModalBayar()">Batal</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function formatRupiah(angka) {
            return "Rp " + angka.toLocaleString("id-ID");
        }

        // hitung ulang total berdasarkan checkbox yang dicentang saja
        function hitungTotal() {
            var checkboxes = document.querySelectorAll(".cek-item");
            var total = 0;
            var jumlahDipilih = 0;

            checkboxes.forEach(function (cb) {
                if (cb.checked) {
                    total += parseInt(cb.dataset.subtotal, 10);
                    jumlahDipilih++;
                }
            });

            var elTotal = document.getElementById("tampil-total-bayar");
            var elJumlah = document.getElementById("jumlah-terpilih-teks");
            var elInputTotal = document.getElementById("input-total-harga");
            var btnLanjut = document.getElementById("btn-lanjut-bayar");

            if (elTotal) elTotal.textContent = formatRupiah(total);
            if (elJumlah) elJumlah.textContent = "(" + jumlahDipilih + " item dipilih)";
            if (elInputTotal) elInputTotal.value = total;
            if (btnLanjut) btnLanjut.disabled = (jumlahDipilih === 0);

            // sinkronkan checkbox "pilih semua"
            var pilihSemua = document.getElementById("pilih-semua");
            if (pilihSemua) {
                pilihSemua.checked = (checkboxes.length > 0 && jumlahDipilih === checkboxes.length);
            }

            // update angka total di dalam modal juga
            var modalTotal1 = document.getElementById("modal-total-bayar");
            var modalTotal2 = document.getElementById("modal-total-bayar-2");
            if (modalTotal1) modalTotal1.textContent = formatRupiah(total);
            if (modalTotal2) modalTotal2.textContent = formatRupiah(total);
        }

        var pilihSemuaEl = document.getElementById("pilih-semua");
        if (pilihSemuaEl) {
            pilihSemuaEl.addEventListener("change", function () {
                document.querySelectorAll(".cek-item").forEach(function (cb) {
                    cb.checked = pilihSemuaEl.checked;
                });
                hitungTotal();
            });
        }

        function bukaModalBayar() {
            var total = parseInt(document.getElementById("input-total-harga").value, 10);
            if (!total || total <= 0) {
                alert("Pilih minimal 1 item untuk dibayar.");
                return;
            }
            document.getElementById("modal-bayar").classList.add("aktif");
        }

        function tutupModalBayar() {
            document.getElementById("modal-bayar").classList.remove("aktif");
        }

        function pilihMetode(radio) {
            document.getElementById("input-metode-bayar").value = radio.value;
            document.getElementById("modal-nama-metode").textContent = radio.value;
        }

        // hitung total pertama kali halaman dibuka (semua item default tercentang)
        hitungTotal();
    </script>
</body>
</html>
