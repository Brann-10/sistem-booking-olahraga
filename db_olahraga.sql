-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 27 Okt 2025 pada 12.41
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
-- Database: `db_olahraga`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengunjung`
--

CREATE TABLE `pengunjung` (
  `id_pengunjung` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tanggal_daftar` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengunjung`
--

INSERT INTO `pengunjung` (`id_pengunjung`, `nama`, `alamat`, `no_telepon`, `email`, `tanggal_daftar`) VALUES
(1, 'Ijat', 'Jakbar', '08123456789', 'ijat@email.com', '2025-10-27'),
(3, 'Suandy', 'Batu Ceper', '08123456790', 'suandy@email.com', '2025-10-27'),
(4, 'Rendi', 'Ciledug', '08123456791', 'rendi@email.com', '2025-10-27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id_kegiatan` int(11) NOT NULL,
  `nama_kegiatan` varchar(255) NOT NULL,
  `pelatih` varchar(100) NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `batas_pengunjung` int(11) NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kegiatan`
--

INSERT INTO `kegiatan` (`id_kegiatan`, `nama_kegiatan`, `pelatih`, `waktu_mulai`, `waktu_selesai`, `batas_pengunjung`, `lokasi`, `status`) VALUES
(2, 'Futsal', 'Coach Andi', '18:00:00', '20:00:00', 20, 'Lapangan Futsal A', 'aktif'),
(3, 'Basket', 'Coach Budi', '19:00:00', '21:00:00', 15, 'Lapangan Basket', 'aktif'),
(4, 'Badminton', 'Coach Sari', '17:00:00', '19:00:00', 8, 'Lapangan Badminton', 'aktif');

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_kegiatan`
--

CREATE TABLE `booking_kegiatan` (
  `id_booking` int(11) NOT NULL,
  `id_pengunjung` int(11) NOT NULL,
  `id_kegiatan` int(11) NOT NULL,
  `tanggal_booking` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `status_kehadiran` enum('hadir','tidak_hadir') DEFAULT NULL,
  `waktu_checkin` datetime DEFAULT NULL,
  `waktu_checkout` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `booking_kegiatan`
--

INSERT INTO `booking_kegiatan` (`id_booking`, `id_pengunjung`, `id_kegiatan`, `tanggal_booking`, `waktu_mulai`, `waktu_selesai`, `status_kehadiran`, `waktu_checkin`, `waktu_checkout`) VALUES
(1, 1, 2, '2025-10-27', '18:00:00', '20:00:00', 'hadir', '2025-10-27 18:05:00', '2025-10-27 19:45:00'),
(2, 1, 2, '2025-10-27', '18:00:00', '20:00:00', NULL, NULL, NULL),
(4, 4, 4, '2025-10-27', '17:00:00', '19:00:00', 'hadir', '2025-10-27 17:10:00', NULL),
(5, 3, 3, '2025-10-27', '19:00:00', '21:00:00', 'hadir', '2025-10-27 19:15:00', '2025-10-27 20:30:00'),
(7, NULL, 3, '2025-10-27', '19:00:00', '21:00:00', 'tidak_hadir', NULL, NULL),
(8, NULL, 3, '2025-10-27', '19:00:00', '21:00:00', 'hadir', '2025-10-27 19:20:00', '2025-10-27 20:40:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_booking`
--

CREATE TABLE `detail_booking` (
  `id_detail` int(11) NOT NULL,
  `id_booking` int(11) NOT NULL,
  `jumlah_orang` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_booking`
--

INSERT INTO `detail_booking` (`id_detail`, `id_booking`, `jumlah_orang`) VALUES
(1, 1, 5),
(2, 2, 5),
(4, 4, 32),
(5, 5, 5),
(7, 7, 60),
(8, 8, 60);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `pengunjung`
--
ALTER TABLE `pengunjung`
  ADD PRIMARY KEY (`id_pengunjung`);

--
-- Indeks untuk tabel `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id_kegiatan`);

--
-- Indeks untuk tabel `booking_kegiatan`
--
ALTER TABLE `booking_kegiatan`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `id_pengunjung` (`id_pengunjung`),
  ADD KEY `id_kegiatan` (`id_kegiatan`);

--
-- Indeks untuk tabel `detail_booking`
--
ALTER TABLE `detail_booking`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_booking` (`id_booking`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `pengunjung`
--
ALTER TABLE `pengunjung`
  MODIFY `id_pengunjung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id_kegiatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `detail_booking`
--
ALTER TABLE `detail_booking`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `booking_kegiatan`
--
ALTER TABLE `booking_kegiatan`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_booking`
--
ALTER TABLE `detail_booking`
  ADD CONSTRAINT `detail_booking_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `booking_kegiatan` (`id_booking`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;