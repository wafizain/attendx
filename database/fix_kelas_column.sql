-- Fix kolom 'kelas' di tabel mahasiswa
-- Kolom ini sepertinya kolom lama yang tidak dipakai

-- Opsi 1: Buat nullable (aman)
ALTER TABLE `mahasiswa` MODIFY `kelas` VARCHAR(50) NULL;

-- Atau Opsi 2: Hapus kolom jika tidak dipakai (lebih bersih)
-- ALTER TABLE `mahasiswa` DROP COLUMN `kelas`;
