/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
DROP TABLE IF EXISTS `approval_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `approval_type` enum('pembayaran','penagihan') NOT NULL,
  `approval_id` bigint(20) unsigned NOT NULL,
  `pengiriman_id` bigint(20) unsigned NOT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `role` enum('staff','manager_keuangan','direktur','superadmin') NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL DEFAULT 'approved',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_history_pengiriman_id_foreign` (`pengiriman_id`),
  KEY `approval_history_invoice_id_foreign` (`invoice_id`),
  KEY `approval_history_approval_type_approval_id_index` (`approval_type`,`approval_id`),
  KEY `approval_history_user_id_foreign` (`user_id`),
  CONSTRAINT `approval_history_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoice_penagihan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_history_pengiriman_id_foreign` FOREIGN KEY (`pengiriman_id`) REFERENCES `pengiriman` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `approval_pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_pembayaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pengiriman_id` bigint(20) unsigned NOT NULL,
  `staff_id` bigint(20) unsigned DEFAULT NULL,
  `staff_approved_at` timestamp NULL DEFAULT NULL,
  `manager_id` bigint(20) unsigned DEFAULT NULL,
  `manager_approved_at` timestamp NULL DEFAULT NULL,
  `superadmin_id` bigint(20) unsigned DEFAULT NULL,
  `superadmin_approved_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','staff_approved','manager_approved','completed') NOT NULL DEFAULT 'pending',
  `catatan_piutang_id` bigint(20) unsigned DEFAULT NULL,
  `piutang_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `piutang_notes` text DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `refraksi_type` enum('qty','rupiah') DEFAULT NULL,
  `refraksi_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `refraksi_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `qty_before_refraksi` decimal(15,2) DEFAULT NULL,
  `qty_after_refraksi` decimal(15,2) DEFAULT NULL,
  `amount_before_refraksi` decimal(15,2) DEFAULT NULL,
  `amount_after_refraksi` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_pembayaran_pengiriman_id_unique` (`pengiriman_id`),
  KEY `approval_pembayaran_staff_id_foreign` (`staff_id`),
  KEY `approval_pembayaran_manager_id_foreign` (`manager_id`),
  KEY `approval_pembayaran_superadmin_id_foreign` (`superadmin_id`),
  KEY `approval_pembayaran_catatan_piutang_id_foreign` (`catatan_piutang_id`),
  CONSTRAINT `approval_pembayaran_catatan_piutang_id_foreign` FOREIGN KEY (`catatan_piutang_id`) REFERENCES `catatan_piutangs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_pembayaran_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_pembayaran_pengiriman_id_foreign` FOREIGN KEY (`pengiriman_id`) REFERENCES `pengiriman` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_pembayaran_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_pembayaran_superadmin_id_foreign` FOREIGN KEY (`superadmin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `approval_penagihan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `approval_penagihan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `pengiriman_id` bigint(20) unsigned NOT NULL,
  `staff_id` bigint(20) unsigned DEFAULT NULL,
  `staff_approved_at` timestamp NULL DEFAULT NULL,
  `manager_id` bigint(20) unsigned DEFAULT NULL,
  `manager_approved_at` timestamp NULL DEFAULT NULL,
  `superadmin_id` bigint(20) unsigned DEFAULT NULL,
  `superadmin_approved_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','staff_approved','manager_approved','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_penagihan_invoice_id_unique` (`invoice_id`),
  KEY `approval_penagihan_pengiriman_id_foreign` (`pengiriman_id`),
  KEY `approval_penagihan_staff_id_foreign` (`staff_id`),
  KEY `approval_penagihan_manager_id_foreign` (`manager_id`),
  KEY `approval_penagihan_superadmin_id_foreign` (`superadmin_id`),
  CONSTRAINT `approval_penagihan_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoice_penagihan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_penagihan_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_penagihan_pengiriman_id_foreign` FOREIGN KEY (`pengiriman_id`) REFERENCES `pengiriman` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_penagihan_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `approval_penagihan_superadmin_id_foreign` FOREIGN KEY (`superadmin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bahan_baku_klien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bahan_baku_klien` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `klien_id` bigint(20) unsigned DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `satuan` varchar(255) DEFAULT NULL,
  `spesifikasi` text DEFAULT NULL,
  `harga_approved` decimal(15,2) DEFAULT NULL COMMENT 'Client approved price per unit',
  `approved_at` timestamp NULL DEFAULT NULL COMMENT 'When price was approved',
  `approved_by_marketing` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `post` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Post checkmark status',
  `present` enum('NotUsed','Ready','Not Reasonable Price','Pos Closed','Not Qualified Raw','Not Updated Yet','Didnt Have Supplier','Factory No Need Yet','Confirmed','Sample Sent','Hold','Negotiate') NOT NULL DEFAULT 'NotUsed' COMMENT 'Present status dropdown',
  `cause` text DEFAULT NULL COMMENT 'Note explaining Present status',
  `jenis` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Category tags: Aqua, Poultry, Ruminansia (can have multiple)' CHECK (json_valid(`jenis`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_bahan_baku_klien_klien` (`klien_id`),
  KEY `idx_klien_status` (`klien_id`,`status`),
  KEY `idx_approved_by` (`approved_by_marketing`),
  CONSTRAINT `bahan_baku_klien_approved_by_marketing_foreign` FOREIGN KEY (`approved_by_marketing`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bahan_baku_klien_klien_id_foreign` FOREIGN KEY (`klien_id`) REFERENCES `kliens` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bahan_baku_supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bahan_baku_supplier` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `harga_per_satuan` decimal(15,2) DEFAULT NULL,
  `satuan` varchar(255) DEFAULT NULL,
  `stok` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_bahan_baku_unique` (`supplier_id`,`nama`),
  UNIQUE KEY `bahan_baku_supplier_slug_unique` (`slug`),
  CONSTRAINT `bahan_baku_supplier_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `catatan_piutang_pabriks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `catatan_piutang_pabriks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `klien_id` bigint(20) unsigned NOT NULL,
  `no_invoice` varchar(255) NOT NULL,
  `tanggal_invoice` date NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `jumlah_piutang` decimal(15,2) NOT NULL,
  `jumlah_dibayar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sisa_piutang` decimal(15,2) NOT NULL,
  `status` enum('belum_jatuh_tempo','jatuh_tempo','terlambat','cicilan','lunas') NOT NULL DEFAULT 'belum_jatuh_tempo',
  `hari_keterlambatan` int(11) NOT NULL DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `bukti_transaksi` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `catatan_piutang_pabriks_no_invoice_unique` (`no_invoice`),
  KEY `catatan_piutang_pabriks_created_by_foreign` (`created_by`),
  KEY `catatan_piutang_pabriks_updated_by_foreign` (`updated_by`),
  KEY `catatan_piutang_pabriks_klien_id_foreign` (`klien_id`),
  CONSTRAINT `catatan_piutang_pabriks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `catatan_piutang_pabriks_klien_id_foreign` FOREIGN KEY (`klien_id`) REFERENCES `kliens` (`id`),
  CONSTRAINT `catatan_piutang_pabriks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `catatan_piutangs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `catatan_piutangs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `tanggal_piutang` date NOT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `jumlah_piutang` decimal(15,2) NOT NULL,
  `jumlah_dibayar` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sisa_piutang` decimal(15,2) NOT NULL,
  `status` enum('belum_lunas','cicilan','lunas') NOT NULL DEFAULT 'belum_lunas',
  `keterangan` text DEFAULT NULL,
  `bukti_transaksi` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `catatan_piutangs_created_by_foreign` (`created_by`),
  KEY `catatan_piutangs_updated_by_foreign` (`updated_by`),
  KEY `catatan_piutangs_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `catatan_piutangs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `catatan_piutangs_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `catatan_piutangs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `company_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL,
  `company_address` text NOT NULL,
  `company_phone` varchar(255) NOT NULL,
  `company_email` varchar(255) NOT NULL,
  `company_website` varchar(255) DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(255) DEFAULT NULL,
  `bank_account_name` varchar(255) DEFAULT NULL,
  `tax_number` varchar(255) DEFAULT NULL,
  `invoice_terms_conditions` text DEFAULT NULL,
  `invoice_footer_notes` text DEFAULT NULL,
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT 11.00,
  `invoice_due_days` int(11) NOT NULL DEFAULT 30,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `forecast_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `forecast_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `forecast_id` bigint(20) unsigned NOT NULL,
  `purchase_order_bahan_baku_id` bigint(20) unsigned NOT NULL,
  `bahan_baku_supplier_id` bigint(20) unsigned NOT NULL,
  `qty_forecast` decimal(15,2) NOT NULL,
  `harga_satuan_forecast` decimal(15,2) NOT NULL,
  `total_harga_forecast` decimal(15,2) NOT NULL,
  `harga_satuan_po` decimal(15,2) DEFAULT NULL,
  `total_harga_po` decimal(15,2) DEFAULT NULL,
  `catatan_detail` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `forecast_details_purchase_order_bahan_baku_id_foreign` (`purchase_order_bahan_baku_id`),
  KEY `forecast_details_forecast_id_bahan_baku_supplier_id_index` (`forecast_id`,`bahan_baku_supplier_id`),
  KEY `forecast_details_bahan_baku_supplier_id_foreign` (`bahan_baku_supplier_id`),
  CONSTRAINT `forecast_details_bahan_baku_supplier_id_foreign` FOREIGN KEY (`bahan_baku_supplier_id`) REFERENCES `bahan_baku_supplier` (`id`),
  CONSTRAINT `forecast_details_forecast_id_foreign` FOREIGN KEY (`forecast_id`) REFERENCES `forecasts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forecast_details_purchase_order_bahan_baku_id_foreign` FOREIGN KEY (`purchase_order_bahan_baku_id`) REFERENCES `order_details` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `forecasts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `forecasts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `purchasing_id` bigint(20) unsigned DEFAULT NULL,
  `no_forecast` varchar(255) NOT NULL,
  `tanggal_forecast` date NOT NULL,
  `hari_kirim_forecast` varchar(255) NOT NULL,
  `total_qty_forecast` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_harga_forecast` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','sukses','gagal') NOT NULL DEFAULT 'pending',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `forecasts_no_forecast_unique` (`no_forecast`),
  KEY `forecasts_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `forecasts_purchasing_id_foreign` (`purchasing_id`),
  CONSTRAINT `forecasts_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forecasts_purchasing_id_foreign` FOREIGN KEY (`purchasing_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invoice_penagihan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_penagihan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pengiriman_id` bigint(20) unsigned NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_address` text NOT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items`)),
  `refraksi_type` enum('qty','rupiah') DEFAULT NULL,
  `refraksi_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `refraksi_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `qty_before_refraksi` decimal(15,2) DEFAULT NULL,
  `qty_after_refraksi` decimal(15,2) DEFAULT NULL,
  `amount_before_refraksi` decimal(15,2) DEFAULT NULL,
  `amount_after_refraksi` decimal(15,2) DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT 11.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_account_number` varchar(255) DEFAULT NULL,
  `bank_account_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','overdue') NOT NULL DEFAULT 'unpaid',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_penagihan_pengiriman_id_unique` (`pengiriman_id`),
  UNIQUE KEY `invoice_penagihan_invoice_number_unique` (`invoice_number`),
  KEY `invoice_penagihan_created_by_foreign` (`created_by`),
  CONSTRAINT `invoice_penagihan_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoice_penagihan_pengiriman_id_foreign` FOREIGN KEY (`pengiriman_id`) REFERENCES `pengiriman` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kliens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kliens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `cabang` varchar(255) NOT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `contact_person_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kliens_contact_person_id_foreign` (`contact_person_id`),
  CONSTRAINT `kliens_contact_person_id_foreign` FOREIGN KEY (`contact_person_id`) REFERENCES `kontak_klien` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kontak_klien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kontak_klien` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `klien_nama` varchar(255) NOT NULL,
  `nomor_hp` varchar(255) DEFAULT NULL,
  `jabatan` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kontak_klien_klien_nama_index` (`klien_nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  KEY `notifications_notifiable_type_notifiable_id_read_at_index` (`notifiable_type`,`notifiable_id`,`read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `bahan_baku_klien_id` bigint(20) unsigned NOT NULL,
  `nama_material_po` varchar(255) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `cheapest_price` decimal(12,2) DEFAULT NULL,
  `most_expensive_price` decimal(12,2) DEFAULT NULL,
  `recommended_price` decimal(12,2) DEFAULT NULL,
  `harga_jual` decimal(12,2) NOT NULL,
  `total_harga` decimal(15,2) NOT NULL,
  `best_margin_percentage` decimal(5,2) DEFAULT NULL,
  `worst_margin_percentage` decimal(5,2) DEFAULT NULL,
  `recommended_margin_percentage` decimal(5,2) DEFAULT NULL,
  `available_suppliers_count` int(11) NOT NULL DEFAULT 0,
  `recommended_supplier_id` bigint(20) unsigned DEFAULT NULL,
  `qty_shipped` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_shipped_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remaining_quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `suppliers_used_count` int(11) NOT NULL DEFAULT 0,
  `supplier_options_populated` tinyint(1) NOT NULL DEFAULT 0,
  `options_populated_at` timestamp NULL DEFAULT NULL,
  `status` enum('menunggu','diproses','sebagian_dikirim','selesai') NOT NULL DEFAULT 'menunggu',
  `spesifikasi_khusus` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_details_order_id_status_index` (`order_id`,`status`),
  KEY `order_details_bahan_baku_klien_id_supplier_id_index` (`bahan_baku_klien_id`),
  KEY `order_details_supplier_id_status_index` (`status`),
  KEY `order_details_recommended_supplier_id_foreign` (`recommended_supplier_id`),
  KEY `order_details_nama_material_po_index` (`nama_material_po`),
  CONSTRAINT `order_details_bahan_baku_klien_id_foreign` FOREIGN KEY (`bahan_baku_klien_id`) REFERENCES `bahan_baku_klien` (`id`),
  CONSTRAINT `order_details_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_details_recommended_supplier_id_foreign` FOREIGN KEY (`recommended_supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_detail_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `bahan_baku_supplier_id` bigint(20) unsigned NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `shipped_quantity` decimal(15,2) NOT NULL DEFAULT 0.00,
  `shipped_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `calculated_margin` decimal(8,4) DEFAULT NULL,
  `potential_profit` decimal(15,2) DEFAULT NULL,
  `is_recommended` tinyint(1) NOT NULL DEFAULT 0,
  `price_rank` int(11) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `has_been_used` tinyint(1) NOT NULL DEFAULT 0,
  `price_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_suppliers_order_detail_id_supplier_id_unique` (`order_detail_id`,`supplier_id`),
  KEY `order_suppliers_order_detail_id_is_available_index` (`order_detail_id`,`is_available`),
  KEY `order_suppliers_supplier_id_has_been_used_index` (`supplier_id`,`has_been_used`),
  KEY `order_suppliers_bahan_baku_supplier_id_index` (`bahan_baku_supplier_id`),
  KEY `order_suppliers_price_rank_calculated_margin_index` (`price_rank`,`calculated_margin`),
  CONSTRAINT `order_suppliers_bahan_baku_supplier_id_foreign` FOREIGN KEY (`bahan_baku_supplier_id`) REFERENCES `bahan_baku_supplier` (`id`),
  CONSTRAINT `order_suppliers_order_detail_id_foreign` FOREIGN KEY (`order_detail_id`) REFERENCES `order_details` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_suppliers_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_winners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_winners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_winners_order_id_foreign` (`order_id`),
  KEY `order_winners_user_id_foreign` (`user_id`),
  CONSTRAINT `order_winners_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_winners_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_order` varchar(255) NOT NULL,
  `klien_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `tanggal_order` date NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('draft','dikonfirmasi','diproses','selesai','dibatalkan') DEFAULT 'draft',
  `priority` enum('rendah','normal','tinggi','mendesak') NOT NULL DEFAULT 'normal',
  `po_number` varchar(255) DEFAULT NULL,
  `po_start_date` date DEFAULT NULL,
  `po_end_date` date DEFAULT NULL,
  `po_document_path` varchar(255) DEFAULT NULL,
  `po_document_original_name` varchar(255) DEFAULT NULL,
  `priority_calculated_at` timestamp NULL DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_items` int(11) NOT NULL DEFAULT 0,
  `total_qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `dikonfirmasi_at` timestamp NULL DEFAULT NULL,
  `selesai_at` timestamp NULL DEFAULT NULL,
  `dibatalkan_at` timestamp NULL DEFAULT NULL,
  `alasan_pembatalan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_no_order_unique` (`no_order`),
  KEY `orders_klien_id_status_index` (`klien_id`,`status`),
  KEY `orders_created_by_tanggal_order_index` (`created_by`,`tanggal_order`),
  KEY `orders_status_created_at_index` (`status`,`created_at`),
  KEY `orders_no_order_index` (`no_order`),
  KEY `orders_po_number_index` (`po_number`),
  KEY `orders_po_end_date_index` (`po_end_date`),
  CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_klien_id_foreign` FOREIGN KEY (`klien_id`) REFERENCES `kliens` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pembayaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pengiriman_id` bigint(20) unsigned NOT NULL,
  `jumlah_pembayaran` decimal(15,2) NOT NULL,
  `tanggal_pembayaran` date NOT NULL,
  `metode_pembayaran` varchar(255) NOT NULL,
  `status` enum('pending','lunas','gagal') NOT NULL DEFAULT 'pending',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pembayaran_pengiriman_id_foreign` (`pengiriman_id`),
  CONSTRAINT `pembayaran_pengiriman_id_foreign` FOREIGN KEY (`pengiriman_id`) REFERENCES `pengiriman` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pembayaran_piutang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran_piutang` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `catatan_piutang_id` bigint(20) unsigned NOT NULL,
  `no_pembayaran` varchar(255) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `metode_pembayaran` enum('tunai','transfer','cek','giro','potong_pembayaran') NOT NULL DEFAULT 'transfer',
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pembayaran_piutang_no_pembayaran_unique` (`no_pembayaran`),
  KEY `pembayaran_piutang_created_by_foreign` (`created_by`),
  KEY `pembayaran_piutang_catatan_piutang_id_foreign` (`catatan_piutang_id`),
  CONSTRAINT `pembayaran_piutang_catatan_piutang_id_foreign` FOREIGN KEY (`catatan_piutang_id`) REFERENCES `catatan_piutangs` (`id`),
  CONSTRAINT `pembayaran_piutang_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pembayaran_piutang_pabriks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pembayaran_piutang_pabriks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_penagihan_id` bigint(20) unsigned NOT NULL,
  `no_pembayaran` varchar(255) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `metode_pembayaran` enum('tunai','transfer','cek','giro') NOT NULL,
  `catatan` text DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pembayaran_piutang_pabriks_no_pembayaran_unique` (`no_pembayaran`),
  KEY `pembayaran_piutang_pabriks_created_by_foreign` (`created_by`),
  KEY `pembayaran_piutang_pabriks_invoice_penagihan_id_foreign` (`invoice_penagihan_id`),
  CONSTRAINT `pembayaran_piutang_pabriks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pembayaran_piutang_pabriks_invoice_penagihan_id_foreign` FOREIGN KEY (`invoice_penagihan_id`) REFERENCES `invoice_penagihan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `penawaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `penawaran` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_penawaran` varchar(50) NOT NULL COMMENT 'Format: PNW-YYYY-XXXX',
  `klien_id` bigint(20) unsigned NOT NULL,
  `tanggal_penawaran` date NOT NULL,
  `tanggal_berlaku_sampai` date NOT NULL,
  `status` enum('draft','menunggu_verifikasi','disetujui','ditolak','expired') NOT NULL DEFAULT 'draft',
  `total_revenue` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total client price',
  `total_cost` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total supplier cost',
  `total_profit` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total profit',
  `margin_percentage` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Overall margin %',
  `created_by` bigint(20) unsigned NOT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `catatan` text DEFAULT NULL COMMENT 'General notes',
  `alasan_penolakan` text DEFAULT NULL COMMENT 'Rejection reason if status=ditolak',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `penawaran_nomor_penawaran_unique` (`nomor_penawaran`),
  KEY `penawaran_verified_by_foreign` (`verified_by`),
  KEY `penawaran_nomor_penawaran_index` (`nomor_penawaran`),
  KEY `penawaran_klien_id_index` (`klien_id`),
  KEY `penawaran_status_index` (`status`),
  KEY `penawaran_tanggal_penawaran_index` (`tanggal_penawaran`),
  KEY `penawaran_created_by_index` (`created_by`),
  CONSTRAINT `penawaran_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `penawaran_klien_id_foreign` FOREIGN KEY (`klien_id`) REFERENCES `kliens` (`id`),
  CONSTRAINT `penawaran_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `penawaran_alternative_suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `penawaran_alternative_suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `penawaran_detail_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `bahan_baku_supplier_id` bigint(20) unsigned NOT NULL,
  `harga_supplier` decimal(15,2) NOT NULL COMMENT 'Alternative supplier price at time of quotation',
  `notes` text DEFAULT NULL COMMENT 'Why this alternative was not chosen',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_detail_supplier` (`penawaran_detail_id`,`supplier_id`),
  KEY `penawaran_alternative_suppliers_penawaran_detail_id_index` (`penawaran_detail_id`),
  KEY `penawaran_alternative_suppliers_supplier_id_index` (`supplier_id`),
  KEY `penawaran_alternative_suppliers_bahan_baku_supplier_id_foreign` (`bahan_baku_supplier_id`),
  CONSTRAINT `penawaran_alternative_suppliers_bahan_baku_supplier_id_foreign` FOREIGN KEY (`bahan_baku_supplier_id`) REFERENCES `bahan_baku_supplier` (`id`),
  CONSTRAINT `penawaran_alternative_suppliers_penawaran_detail_id_foreign` FOREIGN KEY (`penawaran_detail_id`) REFERENCES `penawaran_detail` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penawaran_alternative_suppliers_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `penawaran_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `penawaran_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `penawaran_id` bigint(20) unsigned NOT NULL,
  `bahan_baku_klien_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `bahan_baku_supplier_id` bigint(20) unsigned DEFAULT NULL,
  `nama_material` varchar(255) NOT NULL COMMENT 'Material name at time of quotation',
  `satuan` varchar(50) NOT NULL COMMENT 'Unit (kg, pcs, m, etc.)',
  `quantity` decimal(10,2) NOT NULL,
  `harga_klien` decimal(15,2) NOT NULL COMMENT 'Client price per unit',
  `harga_supplier` decimal(15,2) DEFAULT NULL,
  `is_custom_price` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If custom client price was used',
  `subtotal_revenue` decimal(15,2) NOT NULL COMMENT 'quantity * harga_klien',
  `subtotal_cost` decimal(15,2) DEFAULT NULL,
  `subtotal_profit` decimal(15,2) DEFAULT NULL,
  `margin_percentage` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL COMMENT 'Item-specific notes',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `penawaran_detail_penawaran_id_index` (`penawaran_id`),
  KEY `penawaran_detail_bahan_baku_klien_id_index` (`bahan_baku_klien_id`),
  KEY `penawaran_detail_supplier_id_index` (`supplier_id`),
  KEY `penawaran_detail_bahan_baku_supplier_id_index` (`bahan_baku_supplier_id`),
  CONSTRAINT `penawaran_detail_bahan_baku_klien_id_foreign` FOREIGN KEY (`bahan_baku_klien_id`) REFERENCES `bahan_baku_klien` (`id`),
  CONSTRAINT `penawaran_detail_bahan_baku_supplier_id_foreign` FOREIGN KEY (`bahan_baku_supplier_id`) REFERENCES `bahan_baku_supplier` (`id`) ON DELETE SET NULL,
  CONSTRAINT `penawaran_detail_penawaran_id_foreign` FOREIGN KEY (`penawaran_id`) REFERENCES `penawaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penawaran_detail_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pengiriman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengiriman` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `purchasing_id` bigint(20) unsigned DEFAULT NULL,
  `forecast_id` bigint(20) unsigned NOT NULL,
  `no_pengiriman` varchar(255) DEFAULT NULL,
  `tanggal_kirim` date DEFAULT NULL,
  `hari_kirim` varchar(255) DEFAULT NULL,
  `total_qty_kirim` decimal(15,2) DEFAULT 0.00,
  `total_harga_kirim` decimal(15,2) DEFAULT 0.00,
  `bukti_foto_bongkar` varchar(255) DEFAULT NULL,
  `bukti_foto_bongkar_uploaded_at` timestamp NULL DEFAULT NULL,
  `foto_tanda_terima` varchar(255) DEFAULT NULL,
  `foto_tanda_terima_uploaded_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','menunggu_verifikasi','berhasil','gagal') NOT NULL DEFAULT 'pending',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `rating` int(11) DEFAULT NULL COMMENT 'Rating pengiriman (1-5 bintang)',
  `ulasan` text DEFAULT NULL COMMENT 'Ulasan/review pengiriman',
  PRIMARY KEY (`id`),
  KEY `pengiriman_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `pengiriman_forecast_id_foreign` (`forecast_id`),
  KEY `pengiriman_purchasing_id_foreign` (`purchasing_id`),
  CONSTRAINT `pengiriman_forecast_id_foreign` FOREIGN KEY (`forecast_id`) REFERENCES `forecasts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengiriman_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengiriman_purchasing_id_foreign` FOREIGN KEY (`purchasing_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pengiriman_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengiriman_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pengiriman_id` bigint(20) unsigned NOT NULL,
  `purchase_order_bahan_baku_id` bigint(20) unsigned NOT NULL,
  `bahan_baku_supplier_id` bigint(20) unsigned NOT NULL,
  `qty_kirim` decimal(15,2) DEFAULT NULL,
  `harga_satuan` decimal(15,2) DEFAULT NULL,
  `total_harga` decimal(15,2) DEFAULT NULL,
  `catatan_detail` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pengiriman_details_purchase_order_bahan_baku_id_foreign` (`purchase_order_bahan_baku_id`),
  KEY `pengiriman_details_pengiriman_id_bahan_baku_supplier_id_index` (`pengiriman_id`,`bahan_baku_supplier_id`),
  KEY `pengiriman_details_bahan_baku_supplier_id_foreign` (`bahan_baku_supplier_id`),
  CONSTRAINT `pengiriman_details_bahan_baku_supplier_id_foreign` FOREIGN KEY (`bahan_baku_supplier_id`) REFERENCES `bahan_baku_supplier` (`id`),
  CONSTRAINT `pengiriman_details_pengiriman_id_foreign` FOREIGN KEY (`pengiriman_id`) REFERENCES `pengiriman` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengiriman_details_purchase_order_bahan_baku_id_foreign` FOREIGN KEY (`purchase_order_bahan_baku_id`) REFERENCES `order_details` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `riwayat_harga_bahan_baku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `riwayat_harga_bahan_baku` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bahan_baku_supplier_id` bigint(20) unsigned NOT NULL,
  `harga_lama` decimal(15,2) DEFAULT NULL COMMENT 'Harga sebelum update, null jika data pertama',
  `harga_baru` decimal(15,2) NOT NULL COMMENT 'Harga setelah update',
  `selisih_harga` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Selisih harga (harga_baru - harga_lama)',
  `persentase_perubahan` decimal(8,4) NOT NULL DEFAULT 0.0000 COMMENT 'Persentase perubahan harga',
  `tipe_perubahan` enum('naik','turun','tetap','awal') NOT NULL DEFAULT 'awal',
  `keterangan` text DEFAULT NULL COMMENT 'Keterangan tambahan untuk perubahan harga',
  `tanggal_perubahan` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Waktu perubahan harga',
  `updated_by` varchar(255) DEFAULT NULL COMMENT 'User yang melakukan update',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `riwayat_harga_supplier_tanggal_idx` (`bahan_baku_supplier_id`,`tanggal_perubahan`),
  KEY `riwayat_harga_tanggal_idx` (`tanggal_perubahan`),
  KEY `riwayat_harga_tipe_idx` (`tipe_perubahan`),
  CONSTRAINT `riwayat_harga_bahan_baku_bahan_baku_supplier_id_foreign` FOREIGN KEY (`bahan_baku_supplier_id`) REFERENCES `bahan_baku_supplier` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `riwayat_harga_klien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `riwayat_harga_klien` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bahan_baku_klien_id` bigint(20) unsigned NOT NULL,
  `harga_lama` decimal(15,2) DEFAULT NULL COMMENT 'Previous approved price, null for first record',
  `harga_approved_baru` decimal(15,2) NOT NULL COMMENT 'New approved price',
  `selisih_harga` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Price difference (new - old)',
  `persentase_perubahan` decimal(8,4) NOT NULL DEFAULT 0.0000 COMMENT 'Percentage change',
  `tipe_perubahan` enum('naik','turun','tetap','awal') NOT NULL DEFAULT 'awal',
  `keterangan` text DEFAULT NULL COMMENT 'Notes about price change',
  `tanggal_perubahan` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When price was changed',
  `updated_by_marketing` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `riwayat_harga_klien_material_tanggal_idx` (`bahan_baku_klien_id`,`tanggal_perubahan`),
  KEY `riwayat_harga_klien_tanggal_idx` (`tanggal_perubahan`),
  KEY `riwayat_harga_klien_tipe_idx` (`tipe_perubahan`),
  KEY `riwayat_harga_klien_marketing_idx` (`updated_by_marketing`),
  CONSTRAINT `riwayat_harga_klien_bahan_baku_klien_id_foreign` FOREIGN KEY (`bahan_baku_klien_id`) REFERENCES `bahan_baku_klien` (`id`) ON DELETE CASCADE,
  CONSTRAINT `riwayat_harga_klien_updated_by_marketing_foreign` FOREIGN KEY (`updated_by_marketing`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supplier_evaluation_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_evaluation_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_evaluation_id` bigint(20) unsigned NOT NULL,
  `kriteria` varchar(255) NOT NULL COMMENT 'Harga, Kualitas, Kuantitas, dst',
  `sub_kriteria` varchar(255) NOT NULL,
  `penilaian` int(11) NOT NULL COMMENT '1-5',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_evaluation_details_supplier_evaluation_id_index` (`supplier_evaluation_id`),
  CONSTRAINT `supplier_evaluation_details_supplier_evaluation_id_foreign` FOREIGN KEY (`supplier_evaluation_id`) REFERENCES `supplier_evaluations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supplier_evaluations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_evaluations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pengiriman_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `evaluated_by` bigint(20) unsigned DEFAULT NULL,
  `total_score` decimal(5,2) DEFAULT NULL COMMENT 'Total skor rata-rata (1-5)',
  `rating` int(11) DEFAULT NULL COMMENT 'Rating bintang 1-5',
  `ulasan` text DEFAULT NULL COMMENT 'Kesimpulan ulasan',
  `catatan_tambahan` text DEFAULT NULL,
  `evaluated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_evaluations_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_evaluations_evaluated_by_foreign` (`evaluated_by`),
  KEY `supplier_evaluations_pengiriman_id_supplier_id_index` (`pengiriman_id`,`supplier_id`),
  CONSTRAINT `supplier_evaluations_evaluated_by_foreign` FOREIGN KEY (`evaluated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_evaluations_pengiriman_id_foreign` FOREIGN KEY (`pengiriman_id`) REFERENCES `pengiriman` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_evaluations_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `no_hp` varchar(255) DEFAULT NULL,
  `pic_purchasing_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suppliers_pic_purchasing_id_foreign` (`pic_purchasing_id`),
  CONSTRAINT `suppliers_pic_purchasing_id_foreign` FOREIGN KEY (`pic_purchasing_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `target_omset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `target_omset` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tahun` year(4) NOT NULL,
  `target_tahunan` decimal(20,2) NOT NULL,
  `target_bulanan` decimal(20,2) NOT NULL,
  `target_mingguan` decimal(20,2) NOT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `updated_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `target_omset_tahun_unique` (`tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `target_omset_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `target_omset_snapshots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `target_omset_id` bigint(20) unsigned NOT NULL,
  `tahun` year(4) NOT NULL,
  `bulan` tinyint(4) DEFAULT NULL,
  `minggu` tinyint(4) DEFAULT NULL,
  `periode_type` varchar(255) NOT NULL,
  `target_amount` decimal(20,2) NOT NULL,
  `actual_omset` decimal(20,2) NOT NULL,
  `progress_percentage` decimal(5,2) NOT NULL,
  `selisih` decimal(20,2) NOT NULL,
  `status` varchar(255) NOT NULL,
  `snapshot_at` timestamp NOT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `target_omset_snapshots_target_omset_id_foreign` (`target_omset_id`),
  KEY `target_omset_snapshots_tahun_bulan_index` (`tahun`,`bulan`),
  KEY `target_omset_snapshots_tahun_minggu_index` (`tahun`,`minggu`),
  KEY `target_omset_snapshots_periode_type_index` (`periode_type`),
  CONSTRAINT `target_omset_snapshots_target_omset_id_foreign` FOREIGN KEY (`target_omset_id`) REFERENCES `target_omset` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('direktur','marketing','manager_purchasing','staff_purchasing','staff_accounting','manager_accounting') NOT NULL DEFAULT 'direktur',
  `foto_profil` varchar(255) DEFAULT NULL,
  `status` enum('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

/*M!999999\- enable the sandbox mode */ 
set autocommit=0;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2024_01_01_000001_create_klien_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2024_01_01_000002_create_suppliers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2024_01_01_000003_create_raw_materials_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2024_01_01_000010_create_bahan_baku_supplier_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2024_10_17_000001_create_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2024_10_17_000002_create_order_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2024_10_17_000003_create_forecasts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2024_10_17_000004_create_forecast_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2024_10_17_000005_create_pengiriman_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2024_10_17_000006_create_pengiriman_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2024_10_17_000007_create_pembayaran_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2024_11_22_create_supplier_evaluations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_01_15_000001_simplify_order_status_remove_sebagian_dikirim',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_09_21_100000_create_riwayat_harga_bahan_baku_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_09_21_110000_add_slug_to_bahan_baku_supplier_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_09_25_100001_add_klien_fields_to_bahan_baku_klien_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_09_25_100002_create_riwayat_harga_klien_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_10_11_000001_create_penawaran_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_10_11_000002_create_penawaran_detail_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_10_11_000003_create_penawaran_alternative_suppliers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_10_21_085219_add_review_fields_to_pengiriman_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_10_24_000001_make_penawaran_details_supplier_nullable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_10_24_190014_create_approval_pembayaran_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_10_24_190042_create_company_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_10_24_190109_create_invoices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_10_24_190143_create_approval_penagihan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_10_24_190302_create_approval_history_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_10_24_215147_add_refraksi_fields_to_invoice_penagihan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_10_24_222752_add_refraksi_fields_to_approval_pembayaran_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_10_30_131133_add_bukti_pembayaran_to_approval_pembayaran_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_10_31_171424_create_order_suppliers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_10_31_172516_modify_order_details_for_multiple_suppliers',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_11_05_000001_modify_pengiriman_columns_nullable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_11_07_150122_add_new_fields_to_bahan_baku_klien_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_11_07_152837_create_kontak_klien_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_11_07_154457_add_soft_deletes_to_kontak_klien_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_11_08_020500_remove_legacy_order_detail_columns',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_11_08_100000_add_po_fields_to_orders_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_11_14_053115_create_catatan_piutangs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_11_14_053523_create_pembayaran_piutang_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_11_14_073254_add_piutang_fields_to_approval_pembayaran_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_11_14_100602_add_contact_person_to_kliens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_11_14_125233_add_amount_fields_to_invoice_penagihan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_11_14_125555_update_role_enum_in_approval_history_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_11_21_000001_drop_no_piutang_from_catatan_piutangs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_11_21_125852_add_nama_material_po_to_order_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_11_21_131000_add_alamat_lengkap_to_kliens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_11_21_141500_alter_metode_pembayaran_enum_on_pembayaran_piutang_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_11_21_143559_create_order_winners_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_11_21_162600_create_catatan_piutang_pabriks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_11_22_015717_add_bank_info_to_invoice_penagihan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_12_03_120140_create_pembayaran_piutang_pabriks_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_12_10_000001_create_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_12_04_000001_add_soft_deletes_to_critical_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2024_12_01_000001_create_target_omset_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2024_12_01_000002_create_target_omset_snapshots_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2025_12_15_000001_fix_cascade_delete_risks',4);
commit;
