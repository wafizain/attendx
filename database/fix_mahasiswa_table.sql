-- Fix tabel mahasiswa untuk sistem absensi
-- Jalankan SQL ini di phpMyAdmin atau MySQL console
-- Copy paste satu per satu, skip jika ada error "Duplicate column"

-- 0. Hapus kolom lama yang tidak dipakai (PENTING!)
ALTER TABLE `mahasiswa` DROP COLUMN `kelas`;
ALTER TABLE `mahasiswa` DROP COLUMN `prodi`;

-- 1. Pastikan id_user nullable (penting!)
ALTER TABLE `mahasiswa` MODIFY `id_user` BIGINT UNSIGNED NULL;

-- 2. Tambahkan kolom status_akademik jika belum ada
ALTER TABLE `mahasiswa` ADD `status_akademik` ENUM('aktif','cuti','lulus','nonaktif','do') DEFAULT 'aktif' AFTER `angkatan`;

-- 3. Tambahkan kolom foto_path
ALTER TABLE `mahasiswa` ADD `foto_path` VARCHAR(255) NULL AFTER `status_akademik`;

-- 4. Tambahkan kolom biometrik
ALTER TABLE `mahasiswa` ADD `fp_enrolled` TINYINT(1) DEFAULT 0 AFTER `foto_path`;
ALTER TABLE `mahasiswa` ADD `face_enrolled` TINYINT(1) DEFAULT 0 AFTER `fp_enrolled`;
ALTER TABLE `mahasiswa` ADD `last_enrolled_at` DATETIME NULL AFTER `face_enrolled`;

-- 5. Tambahkan kolom alamat
ALTER TABLE `mahasiswa` ADD `alamat` TEXT NULL AFTER `last_enrolled_at`;

-- 6. Tambahkan kolom deleted_at (untuk soft delete)
ALTER TABLE `mahasiswa` ADD `deleted_at` TIMESTAMP NULL AFTER `updated_at`;

-- 7. Buat tabel mahasiswa_biometrik
CREATE TABLE IF NOT EXISTS `mahasiswa_biometrik` (
  `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  `nim` VARCHAR(32) NOT NULL,
  `tipe` ENUM('fingerprint', 'face') NOT NULL,
  `ext_ref` VARCHAR(64) NULL,
  `template_path` VARCHAR(255) NULL,
  `face_embedding_path` VARCHAR(255) NULL,
  `quality_score` INT NULL,
  `enrolled_at` DATETIME NOT NULL,
  `revoked_at` DATETIME NULL,
  INDEX `idx_nim` (`nim`),
  INDEX `idx_tipe` (`tipe`),
  INDEX `idx_enrolled_at` (`enrolled_at`),
  UNIQUE KEY `uniq_biometric` (`nim`, `tipe`, `ext_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
