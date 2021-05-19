-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2021 at 11:19 AM
-- Server version: 10.4.18-MariaDB
-- PHP Version: 8.0.5

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
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`email`, `password`, `name`) VALUES
('adminpinjaman@gmail.com', 'admin_PinjaMAN', 'Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
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
  `status` enum('belum dibayar','menunggu konfirmasi','dibayar','pembayaran ditolak') NOT NULL DEFAULT 'belum dibayar',
  `waktu_pembayaran` datetime DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `nik`, `jumlah`, `tenggat_waktu`, `biaya_admin`, `total_pinjaman`, `bank`, `no_rekening`, `status`, `waktu_pembayaran`, `date_created`) VALUES
(9, '1234567890123456', 1500000, '2021-06-08', 1500, 1500000, 'BCA', '00000000', 'dibayar', '2021-05-18 01:29:28', '2021-05-17 18:32:11'),
(11, '3275111608990002', 1000000, '2020-09-10', 25000, 1025000, 'BCA', '12437594592834', 'pembayaran ditolak', '2021-05-18 05:36:12', '2021-05-18 03:02:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
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
  `status` enum('accepted','waiting','rejected') NOT NULL DEFAULT 'waiting',
  `otp` varchar(6) DEFAULT NULL,
  `otp_expired` datetime DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`nik`, `nama`, `no_telepon`, `email`, `nama_orang_tua`, `pendidikan_terakhir`, `status_perkawinan`, `alamat`, `nama_perusahaan`, `status_pekerjaan`, `posisi`, `lama_bekerja`, `penghasilan_per_bulan`, `pin`, `role`, `limit_pinjaman`, `sisa_limit`, `alasan_penolakan`, `status`, `date_created`) VALUES
('1234567890123456', 'uzumaki naruto', '081367631999', '93rezao@gmail.com', 'minato', 's3', 'kawin', 'desa konoha, uzumaki naruto', 'kantor hokage konoha', 'pekerja tetap', 'hokage', 2, 10000000, '101010', 'user', 2000000, 500000, NULL, 'accepted', '2021-05-17 18:29:00'),
('3275111608990002', 'Riwandy', '085156342195', 'riwandys@gmail.com', 'Niko Amandus', 's1', 'belum kawin', 'Jl. Mustikasari', 'Tokopedia', 'pekerja tetap', 'Software Engineer', 2, 10000000, '$2y$10$3C10BA0umjKWntvqkwSSi.FsjjQpm25Gwm64rRsSOuKjTLOPcA.Qu', 'user', 0, 0, 'Identitas tidak jelas', 'rejected', '2021-05-18 02:58:39');
INSERT INTO `users` (`nik`, `nama`, `no_telepon`, `email`, `nama_orang_tua`, `pendidikan_terakhir`, `status_perkawinan`, `alamat`, `nama_perusahaan`, `status_pekerjaan`, `posisi`, `lama_bekerja`, `penghasilan_per_bulan`, `pin`, `role`, `limit_pinjaman`, `sisa_limit`, `status`, `otp`, `otp_expired`, `date_created`) VALUES
('1234567890123456', 'uzumaki naruto', '081367631999', '93rezao@gmail.com', ' minato', 's3', 'kawin', 'desa konoha uzumaki naruto', 'kantor hokage konoha', 'pekerja tetap', 'hokage', 2, 10000000, '000000', 'user', 2000000, 500000, 'accepted', '804506', '2021-05-18 12:04:56', '2021-05-17 19:32:34'),
('2234567890123456', 'sasuke', '081367631998', '94rezao@gmail.com', 'itachi', 's3', 'kawin', 'desa konoha sasuke', 'rumah orochimaru', 'pekerja tetap', 'berkelana', 3, 8000000, '$2y$10$lDvYQiv88E9msKLjg3/db.B5sZOadBJCo4fzZOmtcS8z4s5CNscBe', 'admin', 0, 0, 'accepted', NULL, NULL, '2021-05-17 20:07:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nik` (`nik`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`nik`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`nik`) REFERENCES `users` (`nik`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
