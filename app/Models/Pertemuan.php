<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pertemuan extends Model
{
    use HasFactory;

    protected $table = 'pertemuan';

    protected $fillable = [
        'id_jadwal',
        'minggu_ke',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'id_ruangan',
        'status_sesi',
        'open_at',
        'late_after',
        'close_at',
        'dibuka_oleh',
        'dibuka_pada',
        'ditutup_pada',
        'materi',
        'catatan',
        'void_reason',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'open_at' => 'datetime',
        'late_after' => 'datetime',
        'close_at' => 'datetime',
        'dibuka_pada' => 'datetime',
        'ditutup_pada' => 'datetime',
    ];

    /**
     * Relasi ke jadwal kuliah
     */
    public function jadwal()
    {
        return $this->belongsTo(JadwalKuliah::class, 'id_jadwal');
    }

    /**
     * Relasi ke ruangan
     */
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan');
    }

    /**
     * Relasi ke user yang membuka
     */
    public function pembuka()
    {
        return $this->belongsTo(User::class, 'dibuka_oleh');
    }

    /**
     * Relasi ke absensi
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'id_pertemuan');
    }

    /**
     * Relasi ke log aktivitas
     */
    public function logs()
    {
        return $this->hasMany(PertemuanLog::class, 'id_pertemuan');
    }

    /**
     * Scope untuk pertemuan hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', today());
    }

    /**
     * Scope untuk pertemuan aktif (berjalan)
     */
    public function scopeBerjalan($query)
    {
        return $query->where('status_sesi', 'berjalan');
    }

    /**
     * Check if absensi window is open
     */
    public function isAbsensiOpen()
    {
        if ($this->status_sesi !== 'berjalan') {
            return false;
        }

        $now = Carbon::now();
        $jamMulai = Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $this->jam_mulai);
        $openTime = $jamMulai->copy()->subMinutes($this->jadwal->absen_open_min ?? 10);
        $closeTime = $jamMulai->copy()->addMinutes($this->jadwal->absen_close_min ?? 30);

        return $now->between($openTime, $closeTime);
    }

    /**
     * Get absensi window times (with override support)
     */
    public function getAbsensiWindow()
    {
        // Use override if set, otherwise calculate from jadwal
        if ($this->open_at && $this->late_after && $this->close_at) {
            return [
                'open' => $this->open_at,
                'late' => $this->late_after,
                'close' => $this->close_at,
            ];
        }

        $jamMulai = Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $this->jam_mulai);
        
        return [
            'open' => $jamMulai->copy()->subMinutes($this->jadwal->absen_open_min ?? 10),
            'late' => $jamMulai->copy()->addMinutes($this->jadwal->grace_late_min ?? 15),
            'close' => $jamMulai->copy()->addMinutes($this->jadwal->absen_close_min ?? 30),
        ];
    }

    /**
     * Set custom window override
     */
    public function setCustomWindow($openMin = null, $lateMin = null, $closeMin = null)
    {
        $jamMulai = Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $this->jam_mulai);
        
        $this->update([
            'open_at' => $openMin !== null ? $jamMulai->copy()->subMinutes($openMin) : null,
            'late_after' => $lateMin !== null ? $jamMulai->copy()->addMinutes($lateMin) : null,
            'close_at' => $closeMin !== null ? $jamMulai->copy()->addMinutes($closeMin) : null,
        ]);
    }

    /**
     * Determine attendance status based on scan time
     */
    public function determineStatus($scanTime = null)
    {
        $scanTime = $scanTime ? Carbon::parse($scanTime) : Carbon::now();
        $window = $this->getAbsensiWindow();

        if ($scanTime->lt($window['open'])) {
            return 'terlalu_awal';
        }

        if ($scanTime->gt($window['close'])) {
            return 'terlambat_tutup';
        }

        if ($scanTime->lte($window['late'])) {
            return 'hadir';
        }

        return 'telat';
    }

    /**
     * Open session (with logging)
     */
    public function openSession($userId, $openNow = false)
    {
        $oldStatus = $this->status_sesi;
        
        $updateData = [
            'status_sesi' => 'berjalan',
            'dibuka_oleh' => $userId,
            'dibuka_pada' => now(),
        ];

        // If open now, set custom window
        if ($openNow) {
            $now = Carbon::now();
            $jamMulai = Carbon::parse($this->tanggal->format('Y-m-d') . ' ' . $this->jam_mulai);
            
            $updateData['open_at'] = $now;
            $updateData['late_after'] = $jamMulai->copy()->addMinutes($this->jadwal->grace_late_min ?? 15);
            $updateData['close_at'] = $now->copy()->addMinutes($this->jadwal->absen_close_min ?? 30);
        }

        $this->update($updateData);

        // Log activity
        $this->logActivity('open', 'Sesi dibuka', $userId, [
            'old_status' => $oldStatus,
            'new_status' => 'berjalan',
            'open_now' => $openNow,
        ]);
    }

    /**
     * Close session (with logging)
     */
    public function closeSession($userId = null, $closeNow = false)
    {
        $oldStatus = $this->status_sesi;
        
        $updateData = [
            'status_sesi' => 'selesai',
            'ditutup_pada' => now(),
        ];

        // If close now, override close_at
        if ($closeNow) {
            $updateData['close_at'] = now();
        }

        $this->update($updateData);

        // Log activity
        $this->logActivity('close', 'Sesi ditutup', $userId, [
            'old_status' => $oldStatus,
            'new_status' => 'selesai',
            'close_now' => $closeNow,
        ]);
    }

    /**
     * Cancel session
     */
    public function cancelSession($userId, $reason)
    {
        $oldStatus = $this->status_sesi;
        
        $this->update([
            'status_sesi' => 'dibatalkan',
            'void_reason' => $reason,
        ]);

        // Mark all absensi as void
        $this->absensi()->update(['verified_by' => 'system']);

        // Log activity
        $this->logActivity('cancel', 'Sesi dibatalkan: ' . $reason, $userId, [
            'old_status' => $oldStatus,
            'new_status' => 'dibatalkan',
            'reason' => $reason,
        ]);
    }

    /**
     * Reschedule pertemuan
     */
    public function reschedule($userId, $newDate, $newJamMulai = null, $newJamSelesai = null, $newRuanganId = null)
    {
        $oldData = [
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'jam_mulai' => $this->jam_mulai,
            'jam_selesai' => $this->jam_selesai,
            'id_ruangan' => $this->id_ruangan,
        ];

        $updateData = ['tanggal' => $newDate];
        
        if ($newJamMulai) $updateData['jam_mulai'] = $newJamMulai;
        if ($newJamSelesai) $updateData['jam_selesai'] = $newJamSelesai;
        if ($newRuanganId) $updateData['id_ruangan'] = $newRuanganId;

        // Reset window overrides when rescheduling
        $updateData['open_at'] = null;
        $updateData['late_after'] = null;
        $updateData['close_at'] = null;

        $this->update($updateData);

        // Log activity
        $this->logActivity('reschedule', 'Pertemuan dijadwal ulang', $userId, [
            'old' => $oldData,
            'new' => $updateData,
        ]);
    }

    /**
     * Log activity
     */
    protected function logActivity($action, $description, $userId = null, $data = [])
    {
        PertemuanLog::create([
            'id_pertemuan' => $this->id,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'old_data' => $data['old'] ?? null,
            'new_data' => $data['new'] ?? $data,
        ]);
    }

    /**
     * Get attendance statistics
     */
    public function getStatistikKehadiran()
    {
        $totalPeserta = $this->jadwal->mahasiswa()->count();
        $hadir = $this->absensi()->where('status', 'hadir')->count();
        $telat = $this->absensi()->where('status', 'telat')->count();
        $izin = $this->absensi()->where('status', 'izin')->count();
        $sakit = $this->absensi()->where('status', 'sakit')->count();
        $pendingFace = $this->absensi()->where('status', 'pending_face')->count();
        $alfa = $totalPeserta - ($hadir + $telat + $izin + $sakit + $pendingFace);

        return [
            'total_peserta' => $totalPeserta,
            'hadir' => $hadir,
            'telat' => $telat,
            'izin' => $izin,
            'sakit' => $sakit,
            'pending_face' => $pendingFace,
            'alfa' => max(0, $alfa),
            'persentase_hadir' => $totalPeserta > 0 ? round((($hadir + $telat) / $totalPeserta) * 100, 2) : 0,
        ];
    }

    /**
     * Check if auto-open/close should apply
     */
    public function checkAutoStatus()
    {
        if ($this->status_sesi === 'dibatalkan') {
            return;
        }

        $now = Carbon::now();
        $window = $this->getAbsensiWindow();

        // Auto-open if within window and still planned
        if ($this->status_sesi === 'direncanakan' && $now->gte($window['open']) && $now->lte($window['close'])) {
            $this->update(['status_sesi' => 'berjalan']);
        }

        // Auto-close if past close time and still running
        if ($this->status_sesi === 'berjalan' && $now->gt($window['close'])) {
            $this->update(['status_sesi' => 'selesai', 'ditutup_pada' => $window['close']]);
        }
    }

    /**
     * Scope for active sessions (auto-check status)
     */
    public function scopeActiveToday($query)
    {
        return $query->today()
            ->whereIn('status_sesi', ['direncanakan', 'berjalan'])
            ->where('status_sesi', '!=', 'dibatalkan');
    }
}
