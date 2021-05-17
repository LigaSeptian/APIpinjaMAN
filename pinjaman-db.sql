-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2021 at 04:15 PM
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
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `nik` char(16) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `payment_frequency` int(11) NOT NULL DEFAULT 1,
  `tenggat_waktu` date NOT NULL,
  `biaya_admin` int(11) NOT NULL,
  `total_pinjaman` int(11) NOT NULL,
  `bank` enum('BCA','BNI','BRI','') NOT NULL,
  `no_rekening` varchar(20) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  `status` enum('accepted','waiting','rejected','') NOT NULL DEFAULT 'waiting',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
