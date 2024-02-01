-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Sep 2021 pada 06.36
-- Versi server: 10.4.17-MariaDB
-- Versi PHP: 7.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_absensi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `company`
--

CREATE TABLE `company` (
  `ID` int(11) NOT NULL,
  `Number` varchar(200) DEFAULT NULL,
  `Name` varchar(200) NOT NULL,
  `FilePic` text DEFAULT NULL,
  `NPPKP` varchar(100) DEFAULT NULL,
  `NPWP` varchar(100) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Address` text NOT NULL,
  `Phone` varchar(20) NOT NULL,
  `Fax` varchar(20) DEFAULT NULL,
  `Email` varchar(100) NOT NULL,
  `IsPKP` varchar(1) DEFAULT 'y',
  `IsConfirmed` varchar(1) DEFAULT 'n',
  `AddedTime` int(11) NOT NULL,
  `AddedByIP` varchar(64) NOT NULL,
  `EditedTime` int(11) DEFAULT NULL,
  `EditedByIP` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `people`
--

CREATE TABLE `people` (
  `ID` int(18) NOT NULL,
  `IDUser` bigint(20) UNSIGNED NOT NULL,
  `IDCompany` int(11) DEFAULT NULL,
  `Email` varchar(200) NOT NULL,
  `Password` mediumtext DEFAULT NULL,
  `IsVerified` char(1) DEFAULT 'n',
  `IsStarUser` char(1) NOT NULL DEFAULT 'n',
  `IsNewsletterSubscriber` char(1) DEFAULT 'n',
  `CompanyName` varchar(80) DEFAULT NULL,
  `Name` varchar(120) DEFAULT NULL,
  `Details` mediumtext DEFAULT NULL,
  `IDCardType` varchar(50) DEFAULT NULL,
  `IDCardNo` varchar(100) DEFAULT NULL,
  `Sex` varchar(1) DEFAULT NULL,
  `BirthDate` int(18) DEFAULT 0,
  `PhoneNo` varchar(40) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `AddedTime` int(18) NOT NULL,
  `AddedByIP` varchar(64) NOT NULL,
  `EditedTime` int(18) DEFAULT NULL,
  `EditedByIP` varchar(64) DEFAULT NULL,
  `LastLoggedIn` int(18) DEFAULT NULL,
  `IDRole` int(18) NOT NULL,
  `VendorType` varchar(8) DEFAULT NULL,
  `StartPeriod` int(18) DEFAULT NULL,
  `EndPeriod` int(18) DEFAULT NULL,
  `IsOwnVenue` char(1) DEFAULT 'n',
  `IsTCApproved` char(1) DEFAULT NULL,
  `IDReferral` int(18) DEFAULT NULL,
  `ReferralNo` varchar(6) DEFAULT NULL,
  `DeleteStatus` int(1) NOT NULL DEFAULT 0,
  `IDVendorGroup` int(11) DEFAULT NULL,
  `IsChatActive` varchar(20) DEFAULT '0',
  `ChatPrice` decimal(18,2) DEFAULT NULL,
  `ChatTax` decimal(18,2) DEFAULT NULL,
  `ChatCommision` decimal(18,2) DEFAULT NULL,
  `Foto` mediumtext DEFAULT NULL,
  `IsGuess` varchar(20) DEFAULT '0',
  `CategoryVendor` mediumtext DEFAULT NULL,
  `IDProvince` int(18) DEFAULT NULL,
  `IDCity` int(18) DEFAULT NULL,
  `ZipCode` varchar(20) DEFAULT NULL,
  `IDSubdistrict` int(18) DEFAULT 2102,
  `Keyword` text DEFAULT NULL,
  `KTP` text DEFAULT NULL,
  `KK` text DEFAULT NULL,
  `NPWP` text DEFAULT NULL,
  `OTPCode` varchar(7) DEFAULT NULL,
  `IMEI` varchar(255) DEFAULT NULL,
  `HasToken` tinytext DEFAULT NULL,
  `GmailToken` varchar(225) NOT NULL DEFAULT 'n'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `ID` int(18) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `AddedTime` int(18) NOT NULL,
  `AddedByIP` varchar(64) NOT NULL,
  `EditedTime` int(18) DEFAULT NULL,
  `EditedByIP` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`ID`, `Name`, `AddedTime`, `AddedByIP`, `EditedTime`, `EditedByIP`) VALUES
(1, 'Administrator', 1441947151, '::1', NULL, NULL),
(2, 'Management', 1441947151, '::1', NULL, NULL),
(3, 'Customer Service', 1441947151, '::1', NULL, NULL),
(4, 'Content Writer', 1441947151, '::1', NULL, NULL),
(5, 'Accounting & Finance', 1441947151, '::1', NULL, NULL),
(6, 'Member', 1441947151, '::1', NULL, NULL),
(7, 'Vendor', 1441947151, '::1', NULL, NULL),
(8, 'Supervisor', 1606185535, '127.0.0.1', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tasks`
--

CREATE TABLE `tasks` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `start_date` int(18) NOT NULL,
  `end_date` int(18) NOT NULL,
  `report_path` text DEFAULT NULL,
  `detail` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `task_attachments`
--

CREATE TABLE `task_attachments` (
  `id` int(11) NOT NULL,
  `task_id` bigint(20) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `task_reply`
--

CREATE TABLE `task_reply` (
  `id` bigint(20) NOT NULL,
  `task_id` bigint(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reply` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `task_reply_attachments`
--

CREATE TABLE `task_reply_attachments` (
  `id` int(11) NOT NULL,
  `task_reply_id` bigint(20) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `todo`
--

CREATE TABLE `todo` (
  `id` bigint(20) NOT NULL,
  `task_id` bigint(20) NOT NULL,
  `todo` varchar(255) NOT NULL,
  `is_done` char(1) NOT NULL DEFAULT 'n',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `todo_reply`
--

CREATE TABLE `todo_reply` (
  `id` bigint(20) NOT NULL,
  `todo_id` bigint(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `reply` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `todo_reply_attachments`
--

CREATE TABLE `todo_reply_attachments` (
  `id` int(11) NOT NULL,
  `todo_reply_id` bigint(20) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `path` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int(11) NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `nip`, `email`, `phone`, `email_verified_at`, `password`, `role_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 'Admin', '12345', 'admin@gmail.com', NULL, NULL, '$2a$12$1/jShikwWzm5Dsz4E1ruP.ys9lSXNVtw.4ItuexC1yhAHphVf46..', 1, NULL, '2021-08-26 01:45:41', NULL),
(6, 'Hanif', '54321', 'yukizumarushinzuru@gmail.com', NULL, NULL, '$2a$12$1/jShikwWzm5Dsz4E1ruP.ys9lSXNVtw.4ItuexC1yhAHphVf46..', 6, 'xI73cnP7A8TkyXpq4zei4blNNt3KjVgmtMyNjCQpK14HhihXTfsTCjr8lh74', '2021-08-26 02:53:43', '2021-08-25 20:01:09'),
(9, 'Supervisor', '10001', 'testingsuper@visor.com', '4232323', NULL, '$2a$12$1/jShikwWzm5Dsz4E1ruP.ys9lSXNVtw.4ItuexC1yhAHphVf46..', 8, NULL, '2021-09-06 01:13:49', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`ID`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indeks untuk tabel `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `IDRole` (`IDRole`),
  ADD KEY `IDUser` (`IDUser`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Name` (`Name`);

--
-- Indeks untuk tabel `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indeks untuk tabel `task_reply`
--
ALTER TABLE `task_reply`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `task_reply_attachments`
--
ALTER TABLE `task_reply_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_reply_id` (`task_reply_id`);

--
-- Indeks untuk tabel `todo`
--
ALTER TABLE `todo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indeks untuk tabel `todo_reply`
--
ALTER TABLE `todo_reply`
  ADD PRIMARY KEY (`id`),
  ADD KEY `todo_id` (`todo_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `todo_reply_attachments`
--
ALTER TABLE `todo_reply_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `todo_reply_id` (`todo_reply_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `company`
--
ALTER TABLE `company`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `people`
--
ALTER TABLE `people`
  MODIFY `ID` int(18) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `ID` int(18) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `task_attachments`
--
ALTER TABLE `task_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `task_reply`
--
ALTER TABLE `task_reply`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `task_reply_attachments`
--
ALTER TABLE `task_reply_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `todo`
--
ALTER TABLE `todo`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `todo_reply`
--
ALTER TABLE `todo_reply`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `todo_reply_attachments`
--
ALTER TABLE `todo_reply_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `people`
--
ALTER TABLE `people`
  ADD CONSTRAINT `people_ibfk_1` FOREIGN KEY (`IDUser`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD CONSTRAINT `task_attachments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `task_reply`
--
ALTER TABLE `task_reply`
  ADD CONSTRAINT `task_reply_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_reply_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `task_reply_attachments`
--
ALTER TABLE `task_reply_attachments`
  ADD CONSTRAINT `task_reply_attachments_ibfk_1` FOREIGN KEY (`task_reply_id`) REFERENCES `task_reply` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `todo`
--
ALTER TABLE `todo`
  ADD CONSTRAINT `todo_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `todo_reply`
--
ALTER TABLE `todo_reply`
  ADD CONSTRAINT `todo_reply_ibfk_1` FOREIGN KEY (`todo_id`) REFERENCES `todo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `todo_reply_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `todo_reply_attachments`
--
ALTER TABLE `todo_reply_attachments`
  ADD CONSTRAINT `todo_reply_attachments_ibfk_1` FOREIGN KEY (`todo_reply_id`) REFERENCES `todo_reply` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
