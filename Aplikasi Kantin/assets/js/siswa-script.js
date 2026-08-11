/* =========================================================
   UI.JS - Notifikasi & interaksi tambahan E-Kantin Siswa
   Di-include sekali lewat navbar.php, otomatis aktif di semua halaman.

   Isi file ini:
   1. Toast notifikasi (pengganti alert())
   2. Modal konfirmasi custom (pengganti confirm())
   3. Overlay animasi sukses (dipakai setelah checkout)
   4. Helper animasi goyang untuk validasi input
   ========================================================= */


/* =========================================================
   1. TOAST NOTIFIKASI
   ========================================================= */
function pastikanToastContainer() {
    var kontainer = document.getElementById('toast-container');
    if (!kontainer) {
        kontainer = document.createElement('div');
        kontainer.id = 'toast-container';
        document.body.appendChild(kontainer);
    }
    return kontainer;
}

function showToast(pesan, tipe) {
    tipe = tipe || 'info';
    var kontainer = pastikanToastContainer();
    var ikon = { sukses: '✅', error: '⚠️', info: 'ℹ️' }[tipe] || 'ℹ️';

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + tipe;
    toast.innerHTML = '<span class="toast-ikon">' + ikon + '</span><span class="toast-teks">' + pesan + '</span>';
    kontainer.appendChild(toast);

    requestAnimationFrame(function () {
        toast.classList.add('tampil');
    });

    setTimeout(function () {
        toast.classList.remove('tampil');
        toast.classList.add('keluar');
        setTimeout(function () { toast.remove(); }, 300);
    }, 3200);
}

// baca parameter ?notif=sukses/error&pesan=... di URL, tampilkan toast otomatis,
// lalu bersihkan URL supaya kalau di-refresh toast tidak muncul lagi berulang
(function jalankanNotifDariURL() {
    var params = new URLSearchParams(window.location.search);
    if (params.has('notif')) {
        var tipe  = params.get('notif');
        var pesan = params.get('pesan') || '';

        if (pesan) {
            document.addEventListener('DOMContentLoaded', function () {
                showToast(decodeURIComponent(pesan), tipe);
            });
        }

        params.delete('notif');
        params.delete('pesan');
        var sisa = params.toString();
        window.history.replaceState({}, '', window.location.pathname + (sisa ? '?' + sisa : ''));
    }
})();


/* =========================================================
   2. MODAL KONFIRMASI CUSTOM (pengganti confirm() bawaan)
   ========================================================= */
function konfirmasi(opsi) {
    return new Promise(function (resolve) {
        var judul     = (opsi && opsi.judul) || 'Konfirmasi';
        var teks      = (opsi && opsi.teks) || 'Apakah Anda yakin?';
        var teksYa    = (opsi && opsi.teksYa) || 'Ya, Lanjutkan';
        var teksTidak = (opsi && opsi.teksTidak) || 'Batal';
        var bahaya    = !!(opsi && opsi.bahaya);

        var overlay = document.createElement('div');
        overlay.className = 'modal-overlay aktif';
        overlay.innerHTML =
            '<div class="modal-box modal-konfirmasi">' +
                '<h3>' + judul + '</h3>' +
                '<p class="halaman-subjudul" style="margin:8px 0 18px;">' + teks + '</p>' +
                '<div class="struk-aksi">' +
                    '<button type="button" class="btn ' + (bahaya ? 'btn-bahaya' : 'btn-hijau') + '" id="btn-konfirmasi-ya">' + teksYa + '</button>' +
                    '<button type="button" class="btn" id="btn-konfirmasi-tidak">' + teksTidak + '</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(overlay);

        overlay.querySelector('#btn-konfirmasi-ya').addEventListener('click', function () {
            overlay.remove();
            resolve(true);
        });
        overlay.querySelector('#btn-konfirmasi-tidak').addEventListener('click', function () {
            overlay.remove();
            resolve(false);
        });
    });
}

// otomatis dipasang ke semua elemen yang punya atribut data-konfirmasi="...pertanyaan..."
// tinggal tambahkan atribut ini di HTML, tanpa perlu tulis JS baru tiap kali
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-konfirmasi]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (el.dataset.terkonfirmasi === '1') return; // sudah dikonfirmasi, biarkan aksi asli jalan

            e.preventDefault();
            konfirmasi({
                judul: el.dataset.judulKonfirmasi || 'Konfirmasi',
                teks: el.dataset.konfirmasi,
                bahaya: el.dataset.bahaya === '1'
            }).then(function (setuju) {
                if (!setuju) return;

                el.dataset.terkonfirmasi = '1';

                if (el.tagName === 'A') {
                    window.location.href = el.href;
                } else if (el.tagName === 'BUTTON' && el.form) {
                    el.form.requestSubmit(el);
                } else {
                    el.click();
                }
            });
        });
    });
});


/* =========================================================
   3. OVERLAY ANIMASI SUKSES (dipakai setelah checkout berhasil)
   ========================================================= */
function tampilkanSukses(opsi) {
    var judul       = (opsi && opsi.judul) || 'Berhasil!';
    var teks        = (opsi && opsi.teks) || '';
    var redirect    = (opsi && opsi.redirect) || null;
    var teksTombol  = (opsi && opsi.teksTombol) || 'Lanjut';

    var overlay = document.createElement('div');
    overlay.className = 'sukses-overlay';
    overlay.innerHTML =
        '<div class="sukses-box">' +
            '<svg class="sukses-centang" viewBox="0 0 52 52">' +
                '<circle class="sukses-lingkaran" cx="26" cy="26" r="24" fill="none"/>' +
                '<path class="sukses-tanda" fill="none" d="M14 27l7 7 16-16"/>' +
            '</svg>' +
            '<h3>' + judul + '</h3>' +
            (teks ? '<p>' + teks + '</p>' : '') +
            '<button type="button" class="btn btn-hijau" id="btn-sukses-lanjut">' + teksTombol + '</button>' +
        '</div>';

    document.body.appendChild(overlay);
    requestAnimationFrame(function () { overlay.classList.add('tampil'); });

    function lanjut() {
        if (redirect) {
            window.location.href = redirect;
        } else {
            overlay.remove();
        }
    }

    overlay.querySelector('#btn-sukses-lanjut').addEventListener('click', lanjut);

    if (opsi && opsi.autoRedirectDetik) {
        setTimeout(lanjut, opsi.autoRedirectDetik * 1000);
    }
}

// baca parameter ?checkout=sukses&id=... untuk otomatis tampilkan animasi sukses pembayaran
(function jalankanSuksesCheckoutDariURL() {
    var params = new URLSearchParams(window.location.search);
    if (params.get('checkout') === 'sukses') {
        var idBayar = params.get('id') || '';

        document.addEventListener('DOMContentLoaded', function () {
            tampilkanSukses({
                judul: 'Pesanan Berhasil Dibuat!',
                teks: idBayar ? 'ID Pembayaran kamu: <strong>' + idBayar + '</strong>' : '',
                teksTombol: 'Lihat Riwayat Pesanan'
            });
        });

        params.delete('checkout');
        params.delete('id');
        var sisa = params.toString();
        window.history.replaceState({}, '', window.location.pathname + (sisa ? '?' + sisa : ''));
    }
})();


/* =========================================================
   4. ANIMASI GOYANG (dipakai untuk validasi jumlah, dsb)
   ========================================================= */
function goyangkanElemen(el) {
    if (!el) return;
    el.classList.remove('goyang');
    void el.offsetWidth; // trik supaya animasi bisa diulang meski class sama
    el.classList.add('goyang');
}