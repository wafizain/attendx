<?php

namespace App\Services;

use App\Models\JadwalKuliah;
use App\Models\Pertemuan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PertemuanGeneratorService
{
    /**
     * Generate pertemuan for a jadwal
     * 
     * @param JadwalKuliah $jadwal
     * @param int $jumlahPertemuan
     * @param array $skipDates Array of dates to skip (holidays)
     * @return array
     */
    public function generate(JadwalKuliah $jadwal, $jumlahPertemuan = 14, $skipDates = [])
    {
        // Check if already has pertemuan
        $existingCount = $jadwal->pertemuan()->count();
        if ($existingCount > 0) {
            return [
                'success' => false,
                'message' => "Jadwal ini sudah memiliki {$existingCount} pertemuan. Hapus terlebih dahulu jika ingin generate ulang.",
            ];
        }

        $pertemuanList = [];
        $skippedDates = [];
        $currentDate = Carbon::parse($jadwal->tanggal_mulai);
        $endDate = Carbon::parse($jadwal->tanggal_selesai);
        $mingguKe = 1;

        // Find first occurrence of the target day
        while ($currentDate->dayOfWeek !== $jadwal->hari && $currentDate->lte($endDate)) {
            $currentDate->addDay();
        }

        // Generate pertemuan
        while ($mingguKe <= $jumlahPertemuan && $currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            
            // Check if date should be skipped (holiday)
            if (in_array($dateString, $skipDates)) {
                $skippedDates[] = [
                    'tanggal' => $dateString,
                    'minggu_ke' => $mingguKe,
                    'reason' => 'holiday',
                ];
                $currentDate->addWeek();
                continue; // Don't increment minggu_ke, just skip
            }

            $pertemuanList[] = [
                'id_jadwal' => $jadwal->id,
                'minggu_ke' => $mingguKe,
                'tanggal' => $dateString,
                'jam_mulai' => $jadwal->jam_mulai,
                'jam_selesai' => $jadwal->jam_selesai,
                'id_ruangan' => $jadwal->id_ruangan,
                'status_sesi' => 'direncanakan',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $currentDate->addWeek();
            $mingguKe++;
        }

        if (empty($pertemuanList)) {
            return [
                'success' => false,
                'message' => 'Tidak ada pertemuan yang dapat di-generate. Periksa tanggal mulai dan selesai.',
            ];
        }

        // Insert in transaction
        DB::beginTransaction();
        try {
            Pertemuan::insert($pertemuanList);
            DB::commit();

            return [
                'success' => true,
                'message' => 'Pertemuan berhasil di-generate',
                'generated' => count($pertemuanList),
                'skipped' => count($skippedDates),
                'skipped_dates' => $skippedDates,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal menyimpan pertemuan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Preview pertemuan before generating
     * 
     * @param JadwalKuliah $jadwal
     * @param int $jumlahPertemuan
     * @param array $skipDates
     * @return array
     */
    public function preview(JadwalKuliah $jadwal, $jumlahPertemuan = 14, $skipDates = [])
    {
        $preview = [];
        $currentDate = Carbon::parse($jadwal->tanggal_mulai);
        $endDate = Carbon::parse($jadwal->tanggal_selesai);
        $mingguKe = 1;

        // Find first occurrence of the target day
        while ($currentDate->dayOfWeek !== $jadwal->hari && $currentDate->lte($endDate)) {
            $currentDate->addDay();
        }

        // Generate preview
        while ($mingguKe <= $jumlahPertemuan && $currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $isHoliday = in_array($dateString, $skipDates);

            $preview[] = [
                'minggu_ke' => $mingguKe,
                'tanggal' => $dateString,
                'hari_nama' => $currentDate->locale('id')->isoFormat('dddd'),
                'jam_mulai' => $jadwal->jam_mulai,
                'jam_selesai' => $jadwal->jam_selesai,
                'is_holiday' => $isHoliday,
                'will_skip' => $isHoliday,
            ];

            $currentDate->addWeek();
            if (!$isHoliday) {
                $mingguKe++;
            }
        }

        return $preview;
    }

    /**
     * Delete all pertemuan for a jadwal
     * 
     * @param JadwalKuliah $jadwal
     * @param bool $onlyPlanned Only delete planned sessions
     * @return array
     */
    public function deleteAll(JadwalKuliah $jadwal, $onlyPlanned = true)
    {
        $query = $jadwal->pertemuan();

        if ($onlyPlanned) {
            $query->where('status_sesi', 'direncanakan');
        }

        $count = $query->count();
        
        if ($count === 0) {
            return [
                'success' => false,
                'message' => 'Tidak ada pertemuan yang dapat dihapus',
            ];
        }

        DB::beginTransaction();
        try {
            $query->delete();
            DB::commit();

            return [
                'success' => true,
                'message' => "{$count} pertemuan berhasil dihapus",
                'deleted' => $count,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Gagal menghapus pertemuan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get default holiday dates (Indonesian national holidays)
     * You can customize this or fetch from database
     * 
     * @param int $year
     * @return array
     */
    public function getHolidayDates($year = null)
    {
        $year = $year ?? date('Y');
        
        // Example: Indonesian national holidays 2025
        // In production, this should be fetched from a holidays table
        $holidays = [
            "{$year}-01-01", // Tahun Baru
            "{$year}-03-29", // Nyepi (example)
            "{$year}-03-31", // Isra Mi'raj (example)
            "{$year}-04-18", // Wafat Isa Al-Masih
            "{$year}-05-01", // Hari Buruh
            "{$year}-05-29", // Kenaikan Isa Al-Masih
            "{$year}-06-01", // Hari Pancasila
            "{$year}-06-17", // Idul Fitri (example)
            "{$year}-06-18", // Idul Fitri (example)
            "{$year}-08-17", // Hari Kemerdekaan
            "{$year}-08-24", // Idul Adha (example)
            "{$year}-09-14", // Tahun Baru Islam (example)
            "{$year}-11-23", // Maulid Nabi (example)
            "{$year}-12-25", // Natal
        ];

        return $holidays;
    }

    /**
     * Regenerate specific pertemuan (after reschedule or cancel)
     * 
     * @param Pertemuan $pertemuan
     * @param array $data
     * @return bool
     */
    public function regenerate(Pertemuan $pertemuan, $data)
    {
        try {
            $pertemuan->update($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
