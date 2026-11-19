-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: baptwo
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alamat`
--

DROP TABLE IF EXISTS `alamat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alamat` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provinsi_kode` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provinsi_nama` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kabupaten_kode` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kabupaten_nama` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kecamatan_kode` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kecamatan_nama` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `desa_kode` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `desa_nama` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rt` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rw` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_detail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `perkara_id` bigint unsigned DEFAULT NULL,
  `berkas_perkara_id` bigint unsigned DEFAULT NULL,
  `aksi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tabel` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` bigint unsigned NOT NULL,
  `data_lama` json DEFAULT NULL,
  `data_baru` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_log_user_id_foreign` (`user_id`),
  KEY `audit_log_perkara_id_foreign` (`perkara_id`),
  KEY `audit_log_berkas_perkara_id_index` (`berkas_perkara_id`),
  KEY `audit_log_tabel_index` (`tabel`),
  KEY `audit_log_record_id_index` (`record_id`),
  KEY `audit_log_created_at_index` (`created_at`),
  CONSTRAINT `audit_log_perkara_id_foreign` FOREIGN KEY (`perkara_id`) REFERENCES `perkara` (`id`) ON DELETE CASCADE,
  CONSTRAINT `audit_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `berkas_pasal`
--

DROP TABLE IF EXISTS `berkas_pasal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `berkas_pasal` (
  `berkas_perkara_id` bigint unsigned NOT NULL,
  `pasal_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`berkas_perkara_id`,`pasal_id`),
  KEY `berkas_pasal_pasal_id_foreign` (`pasal_id`),
  CONSTRAINT `berkas_pasal_berkas_perkara_id_foreign` FOREIGN KEY (`berkas_perkara_id`) REFERENCES `berkas_perkara` (`id`) ON DELETE CASCADE,
  CONSTRAINT `berkas_pasal_pasal_id_foreign` FOREIGN KEY (`pasal_id`) REFERENCES `pasal` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `berkas_perkara`
--

DROP TABLE IF EXISTS `berkas_perkara`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `berkas_perkara` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `perkara_id` bigint unsigned NOT NULL,
  `tersangka_id` bigint unsigned NOT NULL,
  `nomor_berkas` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_spdp` date DEFAULT NULL,
  `status_formal` enum('selesai','tidak_selesai') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_proses` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Baru',
  `progress_persen` tinyint unsigned NOT NULL DEFAULT '0',
  `is_dpo` tinyint(1) NOT NULL DEFAULT '0',
  `sla_deadline` date DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `berkas_perkara_tersangka_id_unique` (`tersangka_id`),
  KEY `berkas_perkara_perkara_id_foreign` (`perkara_id`),
  KEY `berkas_perkara_created_by_foreign` (`created_by`),
  KEY `berkas_perkara_updated_by_foreign` (`updated_by`),
  KEY `berkas_perkara_nomor_berkas_index` (`nomor_berkas`),
  KEY `berkas_perkara_tanggal_spdp_index` (`tanggal_spdp`),
  KEY `berkas_perkara_status_formal_index` (`status_formal`),
  KEY `berkas_perkara_status_proses_index` (`status_proses`),
  KEY `berkas_perkara_is_dpo_index` (`is_dpo`),
  KEY `berkas_perkara_sla_deadline_index` (`sla_deadline`),
  CONSTRAINT `berkas_perkara_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `berkas_perkara_perkara_id_foreign` FOREIGN KEY (`perkara_id`) REFERENCES `perkara` (`id`) ON DELETE CASCADE,
  CONSTRAINT `berkas_perkara_tersangka_id_foreign` FOREIGN KEY (`tersangka_id`) REFERENCES `tersangka` (`id`) ON DELETE CASCADE,
  CONSTRAINT `berkas_perkara_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bidang`
--

DROP TABLE IF EXISTS `bidang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bidang` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_bidang` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bidang_nama_bidang_unique` (`nama_bidang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dokumen`
--

DROP TABLE IF EXISTS `dokumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dokumen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `perkara_id` bigint unsigned DEFAULT NULL,
  `berkas_perkara_id` bigint unsigned DEFAULT NULL,
  `dpo_id` bigint unsigned DEFAULT NULL,
  `tahapan_perkara_id` bigint unsigned DEFAULT NULL,
  `kategori` enum('lapkej','sprindik','spdp','tahapan','dpo','sp3','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_dokumen` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_file_asli` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` bigint unsigned NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dokumen_perkara_id_foreign` (`perkara_id`),
  KEY `dokumen_created_by_foreign` (`created_by`),
  KEY `dokumen_updated_by_foreign` (`updated_by`),
  KEY `dokumen_berkas_perkara_id_index` (`berkas_perkara_id`),
  KEY `dokumen_tahapan_perkara_id_index` (`tahapan_perkara_id`),
  KEY `dokumen_kategori_index` (`kategori`),
  KEY `dokumen_dpo_id_foreign` (`dpo_id`),
  CONSTRAINT `dokumen_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dokumen_dpo_id_foreign` FOREIGN KEY (`dpo_id`) REFERENCES `dpo` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dokumen_perkara_id_foreign` FOREIGN KEY (`perkara_id`) REFERENCES `perkara` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dokumen_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dpo`
--

DROP TABLE IF EXISTS `dpo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dpo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `berkas_perkara_id` bigint unsigned NOT NULL,
  `nomor_surat` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_penetapan` date DEFAULT NULL,
  `tanggal_pencabutan` date DEFAULT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `alasan_pencabutan` text COLLATE utf8mb4_unicode_ci,
  `nomor_surat_penangkapan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_surat_penangkapan` date DEFAULT NULL,
  `tanggal_penangkapan` date DEFAULT NULL,
  `nomor_surat_penahanan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_penahanan` date DEFAULT NULL,
  `dokumen_penangkapan_id` bigint unsigned DEFAULT NULL,
  `keterangan_penangkapan` text COLLATE utf8mb4_unicode_ci,
  `dokumen_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dpo_berkas_perkara_id_foreign` (`berkas_perkara_id`),
  KEY `dpo_dokumen_id_foreign` (`dokumen_id`),
  KEY `dpo_created_by_foreign` (`created_by`),
  KEY `dpo_updated_by_foreign` (`updated_by`),
  KEY `dpo_status_index` (`status`),
  KEY `dpo_dokumen_penangkapan_id_foreign` (`dokumen_penangkapan_id`),
  CONSTRAINT `dpo_berkas_perkara_id_foreign` FOREIGN KEY (`berkas_perkara_id`) REFERENCES `berkas_perkara` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dpo_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dpo_dokumen_id_foreign` FOREIGN KEY (`dokumen_id`) REFERENCES `dokumen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dpo_dokumen_penangkapan_id_foreign` FOREIGN KEY (`dokumen_penangkapan_id`) REFERENCES `dokumen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dpo_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `instansi_hukum`
--

DROP TABLE IF EXISTS `instansi_hukum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `instansi_hukum` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jenis` enum('kejaksaan','pengadilan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wilayah` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jabatan`
--

DROP TABLE IF EXISTS `jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jabatan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jabatan_nama_jabatan_unique` (`nama_jabatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `panggol`
--

DROP TABLE IF EXISTS `panggol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `panggol` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pangkat` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `golongan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pasal`
--

DROP TABLE IF EXISTS `pasal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pasal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_pasal` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pasal` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isi` text COLLATE utf8mb4_unicode_ci,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `perkara`
--

DROP TABLE IF EXISTS `perkara`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perkara` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nomor_laporan_kejadian` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_laporan_kejadian` date DEFAULT NULL,
  `tahap_laporan_status` enum('draft','final') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `laporan_locked_at` timestamp NULL DEFAULT NULL,
  `laporan_finalized_by` bigint unsigned DEFAULT NULL,
  `nomor_surat_perintah` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_surat_perintah` date DEFAULT NULL,
  `tahap_sprindik_status` enum('draft','final') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sprindik_locked_at` timestamp NULL DEFAULT NULL,
  `sprindik_finalized_by` bigint unsigned DEFAULT NULL,
  `tahap_perkara_status` enum('draft','final') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perkara_locked_at` timestamp NULL DEFAULT NULL,
  `perkara_finalized_by` bigint unsigned DEFAULT NULL,
  `tkp_id` bigint unsigned DEFAULT NULL,
  `penuntut_umum_instansi_id` bigint unsigned DEFAULT NULL,
  `penuntut_umum_nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penuntut_umum_nip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penuntut_umum_pangkat` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `perkara_nomor_laporan_kejadian_unique` (`nomor_laporan_kejadian`),
  UNIQUE KEY `perkara_nomor_surat_perintah_unique` (`nomor_surat_perintah`),
  KEY `perkara_laporan_finalized_by_foreign` (`laporan_finalized_by`),
  KEY `perkara_sprindik_finalized_by_foreign` (`sprindik_finalized_by`),
  KEY `perkara_created_by_foreign` (`created_by`),
  KEY `perkara_updated_by_foreign` (`updated_by`),
  KEY `perkara_tahap_laporan_status_index` (`tahap_laporan_status`),
  KEY `perkara_tahap_sprindik_status_index` (`tahap_sprindik_status`),
  KEY `perkara_tkp_id_index` (`tkp_id`),
  KEY `perkara_perkara_finalized_by_foreign` (`perkara_finalized_by`),
  KEY `perkara_tahap_perkara_status_index` (`tahap_perkara_status`),
  KEY `perkara_penuntut_umum_instansi_id_foreign` (`penuntut_umum_instansi_id`),
  CONSTRAINT `perkara_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `perkara_laporan_finalized_by_foreign` FOREIGN KEY (`laporan_finalized_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `perkara_penuntut_umum_instansi_id_foreign` FOREIGN KEY (`penuntut_umum_instansi_id`) REFERENCES `instansi_hukum` (`id`) ON DELETE SET NULL,
  CONSTRAINT `perkara_perkara_finalized_by_foreign` FOREIGN KEY (`perkara_finalized_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `perkara_sprindik_finalized_by_foreign` FOREIGN KEY (`sprindik_finalized_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `perkara_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `perkara_ppns`
--

DROP TABLE IF EXISTS `perkara_ppns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perkara_ppns` (
  `perkara_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `peran` enum('utama','anggota') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'anggota',
  PRIMARY KEY (`perkara_id`,`user_id`),
  KEY `perkara_ppns_user_id_foreign` (`user_id`),
  CONSTRAINT `perkara_ppns_perkara_id_foreign` FOREIGN KEY (`perkara_id`) REFERENCES `perkara` (`id`) ON DELETE CASCADE,
  CONSTRAINT `perkara_ppns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sp3`
--

DROP TABLE IF EXISTS `sp3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sp3` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `berkas_perkara_id` bigint unsigned NOT NULL,
  `nomor_surat` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_sp3` date NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `dokumen_id` bigint unsigned DEFAULT NULL,
  `penyidik_utama_id` bigint unsigned DEFAULT NULL,
  `penandatangan_nama` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penandatangan_jabatan` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sp3_berkas_perkara_id_foreign` (`berkas_perkara_id`),
  KEY `sp3_dokumen_id_foreign` (`dokumen_id`),
  KEY `sp3_created_by_foreign` (`created_by`),
  KEY `sp3_updated_by_foreign` (`updated_by`),
  KEY `sp3_nomor_surat_index` (`nomor_surat`),
  KEY `sp3_tanggal_sp3_index` (`tanggal_sp3`),
  KEY `sp3_penyidik_utama_id_foreign` (`penyidik_utama_id`),
  CONSTRAINT `sp3_berkas_perkara_id_foreign` FOREIGN KEY (`berkas_perkara_id`) REFERENCES `berkas_perkara` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sp3_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sp3_dokumen_id_foreign` FOREIGN KEY (`dokumen_id`) REFERENCES `dokumen` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sp3_penyidik_utama_id_foreign` FOREIGN KEY (`penyidik_utama_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sp3_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sp3_ppns_anggota`
--

DROP TABLE IF EXISTS `sp3_ppns_anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sp3_ppns_anggota` (
  `sp3_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`sp3_id`,`user_id`),
  KEY `sp3_ppns_anggota_user_id_foreign` (`user_id`),
  CONSTRAINT `sp3_ppns_anggota_sp3_id_foreign` FOREIGN KEY (`sp3_id`) REFERENCES `sp3` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sp3_ppns_anggota_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tahapan_perkara`
--

DROP TABLE IF EXISTS `tahapan_perkara`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tahapan_perkara` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `berkas_perkara_id` bigint unsigned NOT NULL,
  `jenis_tahap` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_surat` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `pengadilan_instansi_id` bigint unsigned DEFAULT NULL,
  `amar_putusan` text COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','final') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `finalized_at` timestamp NULL DEFAULT NULL,
  `finalized_by` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tahapan_perkara_finalized_by_foreign` (`finalized_by`),
  KEY `tahapan_perkara_created_by_foreign` (`created_by`),
  KEY `tahapan_perkara_updated_by_foreign` (`updated_by`),
  KEY `tahapan_perkara_berkas_perkara_id_jenis_tahap_index` (`berkas_perkara_id`,`jenis_tahap`),
  KEY `tahapan_perkara_jenis_tahap_index` (`jenis_tahap`),
  KEY `tahapan_perkara_status_index` (`status`),
  KEY `tahapan_perkara_pengadilan_instansi_id_foreign` (`pengadilan_instansi_id`),
  CONSTRAINT `tahapan_perkara_berkas_perkara_id_foreign` FOREIGN KEY (`berkas_perkara_id`) REFERENCES `berkas_perkara` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tahapan_perkara_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tahapan_perkara_finalized_by_foreign` FOREIGN KEY (`finalized_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tahapan_perkara_pengadilan_instansi_id_foreign` FOREIGN KEY (`pengadilan_instansi_id`) REFERENCES `instansi_hukum` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tahapan_perkara_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tersangka`
--

DROP TABLE IF EXISTS `tersangka`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tersangka` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `perkara_id` bigint unsigned NOT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alias` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bin_binti` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('laki-laki','perempuan') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agama` enum('islam','kristen','katolik','hindu','buddha','konghucu','lainnya') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pekerjaan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kewarganegaraan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'WNI',
  `alamat_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tersangka_perkara_id_foreign` (`perkara_id`),
  KEY `tersangka_alamat_id_foreign` (`alamat_id`),
  KEY `tersangka_created_by_foreign` (`created_by`),
  KEY `tersangka_updated_by_foreign` (`updated_by`),
  KEY `tersangka_nik_index` (`nik`),
  CONSTRAINT `tersangka_alamat_id_foreign` FOREIGN KEY (`alamat_id`) REFERENCES `alamat` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tersangka_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tersangka_perkara_id_foreign` FOREIGN KEY (`perkara_id`) REFERENCES `perkara` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tersangka_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tkp`
--

DROP TABLE IF EXISTS `tkp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tkp` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alamat_id` bigint unsigned DEFAULT NULL,
  `klasifikasi` enum('retailer','distributor','produksi') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legalitas` enum('legal','ilegal') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modus` enum('online','offline') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tkp_alamat_id_foreign` (`alamat_id`),
  KEY `tkp_created_by_foreign` (`created_by`),
  KEY `tkp_updated_by_foreign` (`updated_by`),
  CONSTRAINT `tkp_alamat_id_foreign` FOREIGN KEY (`alamat_id`) REFERENCES `alamat` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tkp_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tkp_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role` enum('administrator','penyidik','umum') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'penyidik',
  `jabatan_id` bigint unsigned DEFAULT NULL,
  `bidang_id` bigint unsigned DEFAULT NULL,
  `panggol_id` bigint unsigned DEFAULT NULL,
  `alamat_id` bigint unsigned DEFAULT NULL,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto_profil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('laki-laki','perempuan') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_pernikahan` enum('belum_kawin','kawin','cerai_hidup','cerai_mati') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telp` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pns','pppk','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pns',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_email_unique` (`email`),
  UNIQUE KEY `user_nip_unique` (`nip`),
  KEY `user_jabatan_id_foreign` (`jabatan_id`),
  KEY `user_bidang_id_foreign` (`bidang_id`),
  KEY `user_panggol_id_foreign` (`panggol_id`),
  KEY `user_alamat_id_foreign` (`alamat_id`),
  CONSTRAINT `user_alamat_id_foreign` FOREIGN KEY (`alamat_id`) REFERENCES `alamat` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_bidang_id_foreign` FOREIGN KEY (`bidang_id`) REFERENCES `bidang` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_jabatan_id_foreign` FOREIGN KEY (`jabatan_id`) REFERENCES `jabatan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_panggol_id_foreign` FOREIGN KEY (`panggol_id`) REFERENCES `panggol` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'baptwo'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-19 10:40:49
