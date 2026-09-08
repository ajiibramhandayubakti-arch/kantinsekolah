-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Sep 2026 pada 03.16
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kantin`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kantin`
--

CREATE TABLE `kantin` (
  `id_kantin` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nama_kantin` varchar(100) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kantin`
--

INSERT INTO `kantin` (`id_kantin`, `id_user`, `nama_kantin`, `deskripsi`) VALUES
(1, 2, 'Kantin Pak Budi', 'Masakan rumahan, porsi kenyang'),
(2, 3, 'Kantin Bu Sari', 'Nasi padang & sayur segar');

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu`
--

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL,
  `id_kantin` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `status` enum('tersedia','habis') DEFAULT 'tersedia',
  `foto_menu` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menu`
--

INSERT INTO `menu` (`id_menu`, `id_kantin`, `nama_menu`, `harga`, `stok`, `status`, `foto_menu`) VALUES
(9, 1, 'Air Putih Aqua (Botol)', 4000, 100, 'tersedia', '291839103_Aqua (Product) __ Behance.jpeg'),
(10, 1, 'Mie Ayam + Bakso', 13000, 15, 'tersedia', '1164942328_2181499816956521.jpeg'),
(11, 1, 'Soto Ayam', 10000, 10, 'tersedia', '747671499_Indonesian Food_ Soto Ayam (Chicken Eggs Noodles Soup Recipe).jpeg'),
(12, 1, 'Tempe Goreng', 1500, 99, 'tersedia', '2134194280_Ternyata campuran tepung ini wajib kita gunakan….jpeg'),
(13, 1, 'Tahu Isi', 1500, 49, 'tersedia', '167942053_Tahu Isi Sayur.jpeg'),
(14, 1, 'Bakwan', 1500, 50, 'tersedia', '1321143528_gorengan indonesia.jpeg'),
(15, 1, 'Nasi Goreng Spesial', 15000, 19, 'tersedia', '503963458_44332377578864430.jpeg'),
(16, 2, 'Risoles Mayonaise', 3000, 30, 'tersedia', '1670850490_465630048996250123.jpeg'),
(17, 2, 'Es Lemon Tea', 5000, 50, 'tersedia', '849199690_6896205673467178.jpeg'),
(18, 2, 'Nasi Uduk Betawi', 10000, 50, 'tersedia', '1140312518_Nasi Uduk Betawi.jpeg'),
(19, 2, 'Spaghetti', 12000, 24, 'tersedia', '1597405370_187673509469625583.jpeg'),
(20, 2, 'Air Putih Aqua (Gelas)', 1000, 49, 'tersedia', '429839754_Aqua Air Mineral Gelas 200 ml 1 Dus Isi 48 Gelas_Pcs Air Kemasan.jpeg'),
(21, 2, 'Es Cincau', 5000, 50, 'tersedia', '2090874386_40673202879174942.jpeg'),
(22, 2, 'Nasi Ayam Sambal Hijau', 13000, 43, 'tersedia', '1289422558_307370743342761052.jpeg'),
(23, 1, 'Bakso Urat', 15000, 34, 'tersedia', '840447970_566679565638023310.jpeg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `total_harga` int(11) NOT NULL,
  `nomor_antrean` varchar(30) NOT NULL,
  `status_pesanan` enum('diproses','siap diambil','selesai','dibatalkan') DEFAULT 'diproses',
  `metode_pembayaran` varchar(20) DEFAULT 'QRIS',
  `tanggal_pesan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id_order`, `id_user`, `total_harga`, `nomor_antrean`, `status_pesanan`, `metode_pembayaran`, `tanggal_pesan`) VALUES
(18, 4, 78000, 'PAY-260901-0018', 'selesai', 'ShopeePay', '2026-09-01 06:49:47'),
(19, 4, 59000, 'PAY-260908-0019', 'selesai', 'QRIS', '2026-09-08 00:27:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_kantin` int(11) NOT NULL,
  `id_menu` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_order`, `id_kantin`, `id_menu`, `jumlah`, `subtotal`) VALUES
(22, 18, 2, 22, 6, 78000),
(23, 19, 2, 20, 1, 1000),
(24, 19, 1, 13, 1, 1500),
(25, 19, 1, 12, 1, 1500),
(26, 19, 2, 19, 1, 12000),
(27, 19, 2, 22, 1, 13000),
(28, 19, 1, 23, 1, 15000),
(29, 19, 1, 15, 1, 15000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama_user` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','penjual','siswa') NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama_user`, `username`, `password`, `role`, `dibuat_pada`) VALUES
(1, 'Admin Sekolah', 'admin', 'admin123', 'admin', '2026-08-11 03:47:54'),
(2, 'Pak Budi', 'budi', 'kantin123', 'penjual', '2026-08-11 03:47:54'),
(3, 'Bu Sari', 'sari', 'kantin123', 'penjual', '2026-08-11 03:47:54'),
(4, 'Dinda Amelia', 'dinda', 'siswa123', 'siswa', '2026-08-11 03:47:54'),
(5, 'Raka Pratama', 'raka', 'siswa123', 'siswa', '2026-08-11 03:47:54');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kantin`
--
ALTER TABLE `kantin`
  ADD PRIMARY KEY (`id_kantin`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`),
  ADD KEY `id_kantin` (`id_kantin`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_kantin` (`id_kantin`),
  ADD KEY `transaksi_ibfk_3` (`id_menu`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kantin`
--
ALTER TABLE `kantin`
  MODIFY `id_kantin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `kantin`
--
ALTER TABLE `kantin`
  ADD CONSTRAINT `kantin_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`id_kantin`) REFERENCES `kantin` (`id_kantin`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_kantin`) REFERENCES `kantin` (`id_kantin`),
  ADD CONSTRAINT `transaksi_ibfk_3` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
