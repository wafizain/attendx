<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Halaman branding (logo, nama sistem)
     */
    public function branding()
    {
        $settings = [
            'app_name' => config('app.name', 'Sistem Absensi'),
            'app_logo' => config('app.logo', null),
            'app_favicon' => config('app.favicon', null),
            'app_description' => config('app.description', ''),
        ];

        return view('admin.settings.branding', compact('settings'));
    }

    /**
     * Update branding
     */
    public function updateBranding(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'app_description' => 'nullable|string|max:500',
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'app_favicon' => 'nullable|image|mimes:png,ico|max:512',
        ]);

        // Upload logo
        if ($request->hasFile('app_logo')) {
            // Hapus logo lama
            if (config('app.logo') && Storage::disk('public')->exists(config('app.logo'))) {
                Storage::disk('public')->delete(config('app.logo'));
            }

            $logoPath = $request->file('app_logo')->store('branding', 'public');
            $this->updateEnvFile('APP_LOGO', $logoPath);
        }

        // Upload favicon
        if ($request->hasFile('app_favicon')) {
            // Hapus favicon lama
            if (config('app.favicon') && Storage::disk('public')->exists(config('app.favicon'))) {
                Storage::disk('public')->delete(config('app.favicon'));
            }

            $faviconPath = $request->file('app_favicon')->store('branding', 'public');
            $this->updateEnvFile('APP_FAVICON', $faviconPath);
        }

        // Update nama aplikasi
        $this->updateEnvFile('APP_NAME', $request->app_name);
        
        // Update deskripsi
        if ($request->filled('app_description')) {
            $this->updateEnvFile('APP_DESCRIPTION', $request->app_description);
        }

        LogHelper::update(auth()->id(), 'settings', "Mengupdate branding sistem");

        return redirect()->back()->with('success', 'Branding berhasil diupdate. Silakan refresh halaman.');
    }

    /**
     * Helper untuk update .env file
     */
    private function updateEnvFile($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $value = str_replace('"', '', $value);
            $value = '"' . $value . '"';

            file_put_contents($path, preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                file_get_contents($path)
            ));
        }
    }
}
