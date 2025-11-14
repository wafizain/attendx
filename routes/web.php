<?php
use App\Http\Controllers\FirstPasswordChangeController;
// Password first change routes
Route::get('/password/first-change', [FirstPasswordChangeController::class, 'showForm'])->name('password.first-change.form');
Route::post('/password/first-change', [FirstPasswordChangeController::class, 'update'])->name('password.first-change.update');

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\SesiAbsensiController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TestController;

Route::get('/', function () {
    return view('auth.login');
});


// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard route (accessible by all authenticated users)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin Only Routes
    Route::middleware(['role:admin'])->group(function () {
        // Data Master - Program Studi
        Route::resource('prodi', App\Http\Controllers\ProdiController::class);
        Route::post('/prodi/{id}/toggle-status', [App\Http\Controllers\ProdiController::class, 'toggleStatus'])->name('prodi.toggle-status');
        Route::post('/prodi/{id}/rotate-kaprodi', [App\Http\Controllers\ProdiController::class, 'rotateKaprodi'])->name('prodi.rotate-kaprodi');
        Route::post('/prodi/bulk-action', [App\Http\Controllers\ProdiController::class, 'bulkAction'])->name('prodi.bulk-action');
        Route::post('/prodi/import', [App\Http\Controllers\ProdiController::class, 'import'])->name('prodi.import');
        Route::get('/prodi/export/data', [App\Http\Controllers\ProdiController::class, 'export'])->name('prodi.export');
        Route::get('/prodi/template/download', [App\Http\Controllers\ProdiController::class, 'downloadTemplate'])->name('prodi.download-template');
        
        // CRUD Admin
        Route::resource('admin', App\Http\Controllers\AdminController::class)->names([
            'index' => 'admin.index',
            'create' => 'admin.create',
            'store' => 'admin.store',
            'show' => 'admin.show',
            'edit' => 'admin.edit',
            'update' => 'admin.update',
            'destroy' => 'admin.destroy'
        ]);
        Route::post('/admin/{id}/reset-password', [App\Http\Controllers\AdminController::class, 'resetPassword'])->name('admin.reset-password');

        // CRUD Dosen
        Route::resource('data-dosen', App\Http\Controllers\DosenController::class)->names([
            'index' => 'admin.dosen.index',
            'create' => 'admin.dosen.create',
            'store' => 'admin.dosen.store',
            'show' => 'admin.dosen.show',
            'edit' => 'admin.dosen.edit',
            'update' => 'admin.dosen.update',
            'destroy' => 'admin.dosen.destroy'
        ]);
        Route::post('/data-dosen/{id}/toggle-status', [App\Http\Controllers\DosenController::class, 'toggleStatus'])->name('admin.dosen.toggle-status');
        Route::post('/data-dosen/{id}/reset-password', [App\Http\Controllers\DosenController::class, 'resetPassword'])->name('admin.dosen.reset-password');
        Route::post('/data-dosen/{id}/reset-username', [App\Http\Controllers\DosenController::class, 'resetUsername'])->name('admin.dosen.reset-username');
        Route::post('/data-dosen/{id}/log-password-view', [App\Http\Controllers\DosenController::class, 'logPasswordView'])->name('admin.dosen.log-password-view');
        Route::post('/data-dosen/{id}/archive', [App\Http\Controllers\DosenController::class, 'archive'])->name('admin.dosen.archive');

        // Data Master - Mahasiswa
        Route::resource('mahasiswa', App\Http\Controllers\Admin\MahasiswaManagementController::class);
        Route::post('/mahasiswa/{id}/toggle-status', [App\Http\Controllers\Admin\MahasiswaManagementController::class, 'toggleStatus'])->name('mahasiswa.toggle-status');
        Route::post('/mahasiswa/{id}/create-account', [App\Http\Controllers\Admin\MahasiswaManagementController::class, 'createAccount'])->name('mahasiswa.create-account');
        Route::post('/mahasiswa/{id}/reset-password', [App\Http\Controllers\Admin\MahasiswaManagementController::class, 'resetPassword'])->name('mahasiswa.reset-password');
        Route::post('/mahasiswa/{id}/reset-username', [App\Http\Controllers\Admin\MahasiswaManagementController::class, 'resetUsername'])->name('mahasiswa.reset-username');
        Route::delete('/mahasiswa/{id}/delete-account', [App\Http\Controllers\Admin\MahasiswaManagementController::class, 'deleteAccount'])->name('mahasiswa.delete-account');
        Route::post('/mahasiswa/{id}/archive', [App\Http\Controllers\Admin\MahasiswaManagementController::class, 'archive'])->name('mahasiswa.archive');
        Route::post('/mahasiswa/bulk-action', [App\Http\Controllers\Admin\MahasiswaManagementController::class, 'bulkAction'])->name('mahasiswa.bulk-action');
        Route::post('/mahasiswa/import', [App\Http\Controllers\Admin\MahasiswaManagementController::class, 'import'])->name('mahasiswa.import');
        Route::get('/mahasiswa/export/data', [App\Http\Controllers\Admin\MahasiswaManagementController::class, 'export'])->name('mahasiswa.export');
        Route::get('/mahasiswa/template/download', [App\Http\Controllers\Admin\MahasiswaManagementController::class, 'downloadTemplate'])->name('mahasiswa.download-template');
        
        // Data Master - Arsip
        Route::get('/arsip', [App\Http\Controllers\Admin\ArsipController::class, 'index'])->name('arsip.index');
        Route::post('/arsip/{type}/{id}/restore', [App\Http\Controllers\Admin\ArsipController::class, 'restore'])->name('arsip.restore');
        Route::delete('/arsip/{type}/{id}/permanent-delete', [App\Http\Controllers\Admin\ArsipController::class, 'permanentDelete'])->name('arsip.permanent-delete');
        Route::post('/arsip/bulk-action', [App\Http\Controllers\Admin\ArsipController::class, 'bulkAction'])->name('arsip.bulk-action');
        
        // Mahasiswa Biometrik Routes
        Route::get('/mahasiswa/{id}/biometrik', [App\Http\Controllers\Admin\BiometrikController::class, 'index'])->name('mahasiswa.biometrik');
        Route::post('/mahasiswa/{id}/biometrik/enroll', [App\Http\Controllers\Admin\BiometrikController::class, 'enroll'])->name('mahasiswa.biometrik.enroll');
        Route::post('/biometrik/{id}/revoke', [App\Http\Controllers\Admin\BiometrikController::class, 'revoke'])->name('biometrik.revoke');
        Route::delete('/biometrik/{id}', [App\Http\Controllers\Admin\BiometrikController::class, 'destroy'])->name('biometrik.destroy');

        // Log Aktivitas Routes
        Route::get('/logs', [App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index');
        Route::get('/logs/{id}', [App\Http\Controllers\Admin\LogController::class, 'show'])->name('logs.show');
        Route::delete('/logs/{id}', [App\Http\Controllers\Admin\LogController::class, 'destroy'])->name('logs.destroy');
        Route::delete('/logs-clear', [App\Http\Controllers\Admin\LogController::class, 'clear'])->name('logs.clear');
        Route::get('/logs-export', [App\Http\Controllers\Admin\LogController::class, 'export'])->name('logs.export');

        // Mata Kuliah Routes
        Route::post('/mata-kuliah/bulk-action', [App\Http\Controllers\Admin\MataKuliahManagementController::class, 'bulkAction'])->name('mata-kuliah.bulk-action');
        Route::post('/mata-kuliah/import', [App\Http\Controllers\Admin\MataKuliahManagementController::class, 'import'])->name('mata-kuliah.import');
        Route::get('/mata-kuliah/export/data', [App\Http\Controllers\Admin\MataKuliahManagementController::class, 'export'])->name('mata-kuliah.export');
        Route::get('/mata-kuliah/template/download', [App\Http\Controllers\Admin\MataKuliahManagementController::class, 'downloadTemplate'])->name('mata-kuliah.download-template');
        Route::get('/mata-kuliah/{id}/duplicate', [App\Http\Controllers\Admin\MataKuliahManagementController::class, 'duplicate'])->name('mata-kuliah.duplicate');
        Route::post('/mata-kuliah/{id}/duplicate', [App\Http\Controllers\Admin\MataKuliahManagementController::class, 'storeDuplicate'])->name('mata-kuliah.store-duplicate');
        Route::post('/mata-kuliah/{id}/toggle-status', [App\Http\Controllers\Admin\MataKuliahManagementController::class, 'toggleStatus'])->name('mata-kuliah.toggle-status');
        Route::resource('mata-kuliah', App\Http\Controllers\Admin\MataKuliahManagementController::class);
        
        // Mata Kuliah Pengampu Routes
        Route::post('/mata-kuliah/{id}/pengampu', [App\Http\Controllers\Admin\MataKuliahManagementController::class, 'addPengampu'])->name('mata-kuliah.add-pengampu');
        Route::delete('/mata-kuliah/{id}/pengampu/{pengampuId}', [App\Http\Controllers\Admin\MataKuliahManagementController::class, 'removePengampu'])->name('mata-kuliah.remove-pengampu');

        // Device Routes
        Route::resource('devices', DeviceController::class);
        Route::get('/devices/pairing/list', [App\Http\Controllers\Admin\DeviceManagementController::class, 'pairing'])->name('devices.pairing');
        Route::post('/devices/{id}/approve-pairing', [App\Http\Controllers\Admin\DeviceManagementController::class, 'approvePairing'])->name('devices.approve-pairing');
        Route::post('/devices/{id}/reject-pairing', [App\Http\Controllers\Admin\DeviceManagementController::class, 'rejectPairing'])->name('devices.reject-pairing');
        Route::get('/devices/heartbeat/monitor', [App\Http\Controllers\Admin\DeviceManagementController::class, 'heartbeat'])->name('devices.heartbeat');
        Route::post('/devices/{id}/reset-api-key', [App\Http\Controllers\Admin\DeviceManagementController::class, 'resetApiKey'])->name('devices.reset-api-key');
        Route::post('/devices/{id}/deactivate', [App\Http\Controllers\Admin\DeviceManagementController::class, 'deactivate'])->name('devices.deactivate');
        Route::post('/devices/{id}/reactivate', [App\Http\Controllers\Admin\DeviceManagementController::class, 'reactivate'])->name('devices.reactivate');

        // Ruangan Routes
        Route::resource('ruangan', App\Http\Controllers\Admin\RuanganController::class);
        Route::post('/ruangan/{id}/toggle-status', [App\Http\Controllers\Admin\RuanganController::class, 'toggleStatus'])->name('ruangan.toggle-status');
        Route::post('/ruangan/bulk-action', [App\Http\Controllers\Admin\RuanganController::class, 'bulkAction'])->name('ruangan.bulk-action');
        Route::post('/ruangan/check-availability', [App\Http\Controllers\Admin\RuanganController::class, 'checkAvailability'])->name('ruangan.check-availability');

        // Jadwal Kuliah Routes
        Route::resource('jadwal', App\Http\Controllers\Admin\JadwalKuliahController::class);
        Route::post('/jadwal/{id}/enroll', [App\Http\Controllers\Admin\JadwalKuliahController::class, 'enrollMahasiswa'])->name('jadwal.enroll');
        Route::post('/jadwal/{id}/enroll-kelas', [App\Http\Controllers\Admin\JadwalKuliahController::class, 'enrollKelas'])->name('jadwal.enroll-kelas');
        Route::delete('/jadwal/{id}/mahasiswa/{mahasiswaId}', [App\Http\Controllers\Admin\JadwalKuliahController::class, 'removeMahasiswa'])->name('jadwal.remove-mahasiswa');

        // Pertemuan (Sesi) Routes - Admin Only (moved to dosen group)
        Route::prefix('admin/pertemuan')->name('admin.pertemuan.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PertemuanController::class, 'index'])->name('index');
            Route::get('/jadwal/{idJadwal}', [\App\Http\Controllers\PertemuanController::class, 'index'])->name('by-jadwal');
            Route::get('/{id}', [\App\Http\Controllers\PertemuanController::class, 'show'])->name('show');
            Route::post('/{id}/cancel', [\App\Http\Controllers\PertemuanController::class, 'cancel'])->name('cancel');
            Route::post('/{id}/reschedule', [\App\Http\Controllers\PertemuanController::class, 'reschedule'])->name('reschedule');
            Route::post('/{id}/update-window', [\App\Http\Controllers\PertemuanController::class, 'updateWindow'])->name('update-window');
            Route::post('/batch-reschedule', [\App\Http\Controllers\PertemuanController::class, 'batchReschedule'])->name('batch-reschedule');
            Route::get('/{id}/export', [\App\Http\Controllers\PertemuanController::class, 'export'])->name('export');
        });
        
        // Backward-compat config routes used by sidebar
        Route::get('/pertemuan/config', [\App\Http\Controllers\Admin\PertemuanController::class, 'config'])->name('pertemuan.config');
        Route::post('/pertemuan/config', [\App\Http\Controllers\Admin\PertemuanController::class, 'updateConfig'])->name('pertemuan.update-config');

        // Absensi Management Routes
        Route::get('/absensi/rekap-harian', [App\Http\Controllers\Admin\AbsensiManagementController::class, 'rekapHarian'])->name('absensi.rekap-harian');
        Route::get('/absensi/rekap-kelas', [App\Http\Controllers\Admin\AbsensiManagementController::class, 'rekapKelas'])->name('absensi.rekap-kelas');
        Route::get('/absensi/koreksi', [App\Http\Controllers\Admin\AbsensiManagementController::class, 'koreksi'])->name('absensi.koreksi');
        Route::put('/absensi/{id}/koreksi', [App\Http\Controllers\Admin\AbsensiManagementController::class, 'updateKoreksi'])->name('absensi.update-koreksi');
        Route::get('/absensi/foto', [App\Http\Controllers\Admin\AbsensiManagementController::class, 'foto'])->name('absensi.foto');
        Route::delete('/absensi/{id}/foto', [App\Http\Controllers\Admin\AbsensiManagementController::class, 'deleteFoto'])->name('absensi.delete-foto');

        // Audit & Log Routes
        Route::get('/logs/activity', [App\Http\Controllers\Admin\AuditController::class, 'activity'])->name('logs.activity');
        Route::get('/logs/password', [App\Http\Controllers\Admin\AuditController::class, 'password'])->name('logs.password');
        Route::get('/logs/device', [App\Http\Controllers\Admin\AuditController::class, 'device'])->name('logs.device');

        // Reports Routes
        Route::get('/reports/export', [ReportController::class, 'index'])->name('reports.export');
        Route::get('/reports/statistik', [ReportController::class, 'statistik'])->name('reports.statistik');

        // Settings Routes
        Route::get('/settings/branding', [App\Http\Controllers\Admin\SettingsController::class, 'branding'])->name('settings.branding');
        Route::post('/settings/branding', [App\Http\Controllers\Admin\SettingsController::class, 'updateBranding'])->name('settings.update-branding');
    });

    // Admin & Dosen Routes
    Route::middleware(['role:admin,dosen'])->group(function () {
        // Kelas Routes (New Management)
        Route::resource('kelas', App\Http\Controllers\Admin\KelasManagementController::class);
        Route::post('/kelas/{id}/toggle-status', [App\Http\Controllers\Admin\KelasManagementController::class, 'toggleStatus'])->name('kelas.toggle-status');
        Route::post('/kelas/{id}/rotate-wali', [App\Http\Controllers\Admin\KelasManagementController::class, 'rotateWali'])->name('kelas.rotate-wali');
        Route::post('/kelas/bulk-action', [App\Http\Controllers\Admin\KelasManagementController::class, 'bulkAction'])->name('kelas.bulk-action');
        Route::post('/kelas/import', [App\Http\Controllers\Admin\KelasManagementController::class, 'import'])->name('kelas.import');
        Route::get('/kelas/export/data', [App\Http\Controllers\Admin\KelasManagementController::class, 'export'])->name('kelas.export');
        Route::get('/kelas/template/download', [App\Http\Controllers\Admin\KelasManagementController::class, 'downloadTemplate'])->name('kelas.download-template');
        
        // Kelas Members Routes
        Route::get('/kelas/{id}/members', [App\Http\Controllers\Admin\KelasManagementController::class, 'members'])->name('kelas.members');
        Route::post('/kelas/{id}/members', [App\Http\Controllers\Admin\KelasManagementController::class, 'addMember'])->name('kelas.add-member');
        Route::post('/kelas/{id}/members/{memberId}/keluar', [App\Http\Controllers\Admin\KelasManagementController::class, 'removeMember'])->name('kelas.remove-member');
        Route::post('/kelas/{id}/members/import', [App\Http\Controllers\Admin\KelasManagementController::class, 'importMembers'])->name('kelas.import-members');
        Route::get('/kelas/{id}/members/export', [App\Http\Controllers\Admin\KelasManagementController::class, 'exportMembers'])->name('kelas.export-members');

        // Pertemuan Routes - Dosen & Admin
        Route::prefix('pertemuan')->name('pertemuan.')->group(function () {
            Route::post('/{id}/open', [\App\Http\Controllers\PertemuanController::class, 'open'])->name('open');
            Route::post('/{id}/close', [\App\Http\Controllers\PertemuanController::class, 'close'])->name('close');
            Route::post('/{id}/extend', [\App\Http\Controllers\PertemuanController::class, 'extend'])->name('extend');
            Route::post('/{id}/update-notes', [\App\Http\Controllers\PertemuanController::class, 'updateNotes'])->name('update-notes');
            Route::post('/{id}/correct-attendance', [\App\Http\Controllers\PertemuanController::class, 'correctAttendance'])->name('correct-attendance');
            Route::delete('/{pertemuanId}/attendance/{absensiId}', [\App\Http\Controllers\PertemuanController::class, 'deleteAttendance'])->name('delete-attendance');
        });

        // Sesi Absensi Routes
        Route::resource('sesi-absensi', SesiAbsensiController::class);
        Route::post('/sesi-absensi/{id}/start', [SesiAbsensiController::class, 'start'])->name('sesi-absensi.start');
        Route::post('/sesi-absensi/{id}/close', [SesiAbsensiController::class, 'close'])->name('sesi-absensi.close');
        Route::put('/sesi-absensi/{sesiId}/mahasiswa/{mahasiswaId}', [SesiAbsensiController::class, 'updateAbsensi'])->name('sesi-absensi.update-absensi');

        // Report Routes
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/by-class', [ReportController::class, 'byClass'])->name('reports.by-class');
        Route::get('/reports/by-student', [ReportController::class, 'byStudent'])->name('reports.by-student');
        Route::get('/reports/export-class-csv', [ReportController::class, 'exportClassCSV'])->name('reports.export-class-csv');
        Route::get('/reports/export-student-csv', [ReportController::class, 'exportStudentCSV'])->name('reports.export-student-csv');
    });

    // Dosen Only Routes - Fixed structure
    Route::prefix('dosen')->middleware(['role:dosen'])->group(function () {
        // Jadwal Mengajar Routes
        Route::get('/jadwal-mengajar', [App\Http\Controllers\Dosen\JadwalMengajarController::class, 'index'])->name('dosen.jadwal-mengajar.index');
        Route::get('/jadwal-mengajar/{id}', [App\Http\Controllers\Dosen\JadwalMengajarController::class, 'show'])->name('dosen.jadwal-mengajar.show');
        Route::post('/jadwal-mengajar/{id}/mulai-kelas', [App\Http\Controllers\Dosen\JadwalMengajarController::class, 'mulaiKelas'])->name('dosen.jadwal-mengajar.mulai-kelas');
        Route::get('/sesi-absensi/{id}/kelola', [App\Http\Controllers\Dosen\JadwalMengajarController::class, 'kelolaAbsensi'])->name('dosen.jadwal-mengajar.sesi');
        Route::put('/sesi-absensi/{sesiId}/mahasiswa/{mahasiswaId}', [App\Http\Controllers\Dosen\JadwalMengajarController::class, 'updateAbsensi'])->name('dosen.jadwal-mengajar.update-absensi');
        Route::post('/sesi-absensi/{id}/tutup', [App\Http\Controllers\Dosen\JadwalMengajarController::class, 'tutupSesi'])->name('dosen.jadwal-mengajar.tutup-sesi');
        Route::post('/sesi-absensi/{id}/absen-manual', [App\Http\Controllers\Dosen\JadwalMengajarController::class, 'absenManual'])->name('dosen.jadwal-mengajar.absen-manual');
        
        // Rekap Absensi Mahasiswa Routes
        Route::get('/rekap-absensi', [App\Http\Controllers\Dosen\RekapAbsensiController::class, 'index'])->name('dosen.rekap-absensi.index');
        Route::get('/rekap-absensi/{jadwalId}', [App\Http\Controllers\Dosen\RekapAbsensiController::class, 'show'])->name('dosen.rekap-absensi.show');
        Route::get('/rekap-absensi/{jadwalId}/export/{format}', [App\Http\Controllers\Dosen\RekapAbsensiController::class, 'export'])->name('dosen.rekap-absensi.export');
        
        // Riwayat Pertemuan Routes
        Route::get('/riwayat-pertemuan', [App\Http\Controllers\Dosen\RiwayatPertemuanController::class, 'index'])->name('dosen.riwayat-pertemuan.index');
        Route::get('/riwayat-pertemuan/{id}', [App\Http\Controllers\Dosen\RiwayatPertemuanController::class, 'show'])->name('dosen.riwayat-pertemuan.show');
        Route::get('/riwayat-pertemuan/{id}/download/{format}', [App\Http\Controllers\Dosen\RiwayatPertemuanController::class, 'download'])->name('dosen.riwayat-pertemuan.download');
        
        // Absen Manual Routes (Legacy)
        Route::get('/absen-manual', [App\Http\Controllers\Dosen\AbsenManualController::class, 'index'])->name('dosen.absen-manual');
        Route::get('/absen-manual/get-sesi', [App\Http\Controllers\Dosen\AbsenManualController::class, 'getSesiByKelas'])->name('dosen.absen-manual.get-sesi');
        Route::get('/absen-manual/get-mahasiswa', [App\Http\Controllers\Dosen\AbsenManualController::class, 'getMahasiswaBySesi'])->name('dosen.absen-manual.get-mahasiswa');
        
        // Pertemuan Routes
        Route::get('/pertemuan', [\App\Http\Controllers\Dosen\PertemuanController::class, 'index'])->name('pertemuan.index');
        Route::get('/pertemuan/{id}', [\App\Http\Controllers\Dosen\PertemuanController::class, 'show'])->name('pertemuan.show');
        Route::post('/absen-manual/update', [App\Http\Controllers\Dosen\AbsenManualController::class, 'updateAbsensi'])->name('dosen.absen-manual.update');
    });

    // Mahasiswa Only Routes
    Route::middleware(['role:mahasiswa'])->prefix('mahasiswa')->group(function () {
        Route::get('/kelas', [App\Http\Controllers\Mahasiswa\MahasiswaViewController::class, 'kelas'])->name('mahasiswa.kelas');
        Route::get('/absensi', [App\Http\Controllers\Mahasiswa\MahasiswaViewController::class, 'absensi'])->name('mahasiswa.absensi');
        
        // Fingerprint Registration Routes
        Route::get('/fingerprint/register', [App\Http\Controllers\FingerprintRegistrationController::class, 'index'])->name('fingerprint.register');
    });
});