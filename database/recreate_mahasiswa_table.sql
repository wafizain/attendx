-- RECREATE tabel mahasiswa dari awal
-- BACKUP DATA DULU jika ada data penting!

-- 1. Backup data mahasiswa (opsional)
CREATE TABLE IF NOT EXISTS `mahasiswa_backup` AS SELECT * FROM `mahasiswa`;

-- 2. Drop tabel lama
DROP TABLE IF EXISTS `mahasiswa_biometrik`;
DROP TABLE IF EXISTS `kelas_members`;
DROP TABLE IF EXISTS `mahasiswa`;

-- 3. Buat tabel mahasiswa baru dengan struktur yang benar
CREATE TABLE `mahasiswa` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_user` BIGINT UNSIGNED NULL UNIQUE COMMENT 'FK ke users.id (NULLABLE)',
  `nim` VARCHAR(32) NOT NULL UNIQUE,
  `nama` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NULL,
  `no_hp` VARCHAR(32) NULL,
  `id_prodi` BIGINT UNSIGNED NOT NULL COMMENT 'FK ke program_studi.id',
  `id_kelas` BIGINT UNSIGNED NULL COMMENT 'FK ke kelas.id (opsional)',
  `angkatan` YEAR NOT NULL,
  `status_akademik` ENUM('aktif','cuti','lulus','nonaktif','do') DEFAULT 'aktif',
  `foto_path` VARCHAR(255) NULL,
  `fp_enrolled` TINYINT(1) DEFAULT 0 COMMENT 'Fingerprint enrolled',
  `face_enrolled` TINYINT(1) DEFAULT 0 COMMENT 'Face enrolled',
  `last_enrolled_at` DATETIME NULL,
  `alamat` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  
  INDEX `idx_nim` (`nim`),
  INDEX `idx_id_prodi` (`id_prodi`),
  INDEX `idx_id_kelas` (`id_kelas`),
  INDEX `idx_angkatan` (`angkatan`),
  INDEX `idx_status_akademik` (`status_akademik`),
  INDEX `idx_deleted_at` (`deleted_at`),
  
  FOREIGN KEY (`id_user`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`id_prodi`) REFERENCES `program_studi`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`id_kelas`) REFERENCES `kelas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Buat tabel kelas_members
CREATE TABLE `kelas_members` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `id_kelas` BIGINT UNSIGNED NOT NULL,
  `nim` VARCHAR(32) NOT NULL,
  `tanggal_masuk` DATE NOT NULL,
  `tanggal_keluar` DATE NULL,
  `keterangan` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  
  INDEX `idx_id_kelas` (`id_kelas`),
  INDEX `idx_nim` (`nim`),
  INDEX `idx_tanggal_masuk` (`tanggal_masuk`),
  UNIQUE KEY `uniq_active_member` (`id_kelas`, `nim`, `tanggal_keluar`),
  
  FOREIGN KEY (`id_kelas`) REFERENCES `kelas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`nim`) REFERENCES `mahasiswa`(`nim`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Buat tabel mahasiswa_biometrik
CREATE TABLE `mahasiswa_biometrik` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `nim` VARCHAR(32) NOT NULL,
  `tipe` ENUM('fingerprint', 'face') NOT NULL,
  `ext_ref` VARCHAR(64) NULL COMMENT 'ID template di sensor/alat',
  `template_path` VARCHAR(255) NULL COMMENT 'Path file template fingerprint',
  `face_embedding_path` VARCHAR(255) NULL COMMENT 'Path embedding face',
  `quality_score` INT NULL COMMENT 'Skor kualitas template',
  `enrolled_at` DATETIME NOT NULL,
  `revoked_at` DATETIME NULL COMMENT 'Tanggal dicabut/diganti',
  
  INDEX `idx_nim` (`nim`),
  INDEX `idx_tipe` (`tipe`),
  INDEX `idx_enrolled_at` (`enrolled_at`),
  UNIQUE KEY `uniq_biometric` (`nim`, `tipe`, `ext_ref`),
  
  FOREIGN KEY (`nim`) REFERENCES `mahasiswa`(`nim`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Insert sample data (opsional - hapus jika tidak perlu)
INSERT INTO `mahasiswa` (`nim`, `nama`, `email`, `id_prodi`, `angkatan`, `status_akademik`) 
VALUES 
('2021001', 'Budi Santoso', 'budi@student.ac.id', 1, 2021, 'aktif'),
('2021002', 'Siti Aminah', 'siti@student.ac.id', 1, 2021, 'aktif'),
('2022001', 'Ahmad Fauzi', 'ahmad@student.ac.id', 1, 2022, 'aktif');

-- 7. Tandai migration sebagai sudah dijalankan
INSERT INTO `migrations` (`migration`, `batch`) 
VALUES 
('2025_11_09_000000_update_mahasiswa_system', 999),
('2025_11_09_000001_fix_dosen_and_mahasiswa_tables', 999),
('2025_11_09_190000_fix_mahasiswa_table_safe', 999),
('2025_11_09_200000_fix_kelas_column_mahasiswa', 999)
ON DUPLICATE KEY UPDATE migration = migration;

-- SELESAI! Tabel mahasiswa sudah siap digunakan.
