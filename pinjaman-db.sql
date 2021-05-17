-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 17 Bulan Mei 2021 pada 20.36
-- Versi server: 10.4.17-MariaDB
-- Versi PHP: 7.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pinjaman-db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `nik` char(16) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `tenggat_waktu` date NOT NULL,
  `biaya_admin` int(11) NOT NULL,
  `total_pinjaman` int(11) NOT NULL,
  `bank` enum('BCA','BNI','BRI','') NOT NULL,
  `no_rekening` varchar(20) NOT NULL,
  `status` enum('belum dibayar','menunggu konfirmasi','dibayar') NOT NULL DEFAULT 'belum dibayar',
  `waktu_pembayaran` datetime DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `transactions`
--

INSERT INTO `transactions` (`id`, `nik`, `jumlah`, `tenggat_waktu`, `biaya_admin`, `total_pinjaman`, `bank`, `no_rekening`, `status`, `waktu_pembayaran`, `date_created`) VALUES
(9, '1234567890123456', 1500000, '2021-06-08', 1500, 1500000, 'BCA', '00000000', 'dibayar', '2021-05-18 01:29:28', '2021-05-17 18:32:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `nik` char(16) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `no_telepon` varchar(14) NOT NULL,
  `email` varchar(50) NOT NULL,
  `nama_orang_tua` varchar(50) NOT NULL,
  `pendidikan_terakhir` enum('sd','smp','sma','s1','s2','s3') NOT NULL,
  `status_perkawinan` enum('belum kawin','kawin','cerai hidup','cerai mati') NOT NULL,
  `alamat` text NOT NULL,
  `nama_perusahaan` varchar(30) NOT NULL,
  `status_pekerjaan` enum('belum bekerja','pekerja tetap','pekerja tidak tetap','') NOT NULL,
  `posisi` varchar(30) NOT NULL,
  `lama_bekerja` int(11) NOT NULL,
  `penghasilan_per_bulan` int(11) NOT NULL,
  `pin` varchar(255) NOT NULL,
  `role` enum('user','admin','master admin','') NOT NULL,
  `limit_pinjaman` int(11) NOT NULL DEFAULT 0,
  `sisa_limit` int(11) NOT NULL DEFAULT 0,
  `status` enum('accepted','waiting','rejected','') NOT NULL DEFAULT 'waiting',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`nik`, `nama`, `no_telepon`, `email`, `nama_orang_tua`, `pendidikan_terakhir`, `status_perkawinan`, `alamat`, `nama_perusahaan`, `status_pekerjaan`, `posisi`, `lama_bekerja`, `penghasilan_per_bulan`, `pin`, `role`, `limit_pinjaman`, `sisa_limit`, `status`, `date_created`) VALUES
('1234567890123456', 'uzumaki naruto', '081367631999', '93rezao@gmail.com', 'minato', 's3', 'kawin', 'desa konoha, uzumaki naruto', 'kantor hokage konoha', 'pekerja tetap', 'hokage', 2, 10000000, '101010', 'user', 2000000, 500000, 'accepted', '2021-05-17 18:29:00');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nik` (`nik`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`nik`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`nik`) REFERENCES `users` (`nik`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
