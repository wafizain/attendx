<?php

namespace App\Services;

use App\Models\Pertemuan;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    /**
     * Send email notification
     */
    public function sendEmail($to, $subject, $message)
    {
        try {
            Mail::raw($message, function($mail) use ($to, $subject) {
                $mail->to($to)
                     ->subject($subject);
            });
            return true;
        } catch (\Exception $e) {
            \Log::error('Email notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send SMS notification (requires SMS gateway)
     */
    public function sendSMS($phone, $message)
    {
        // Example using Twilio or local SMS gateway
        // Configure in .env: SMS_GATEWAY_URL, SMS_API_KEY
        
        try {
            $response = Http::post(config('services.sms.gateway_url'), [
                'api_key' => config('services.sms.api_key'),
                'phone' => $phone,
                'message' => $message,
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('SMS notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Telegram notification
     */
    public function sendTelegram($chatId, $message)
    {
        $botToken = config('services.telegram.bot_token');
        
        if (!$botToken) {
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Telegram notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification (requires FCM)
     */
    public function sendPushNotification($deviceToken, $title, $body)
    {
        $serverKey = config('services.fcm.server_key');
        
        if (!$serverKey) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                ],
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Push notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify dosen when pertemuan opened
     */
    public function notifyPertemuanOpened(Pertemuan $pertemuan)
    {
        $dosen = $pertemuan->jadwal->dosen;
        $message = "Pertemuan {$pertemuan->jadwal->mataKuliah->nama_mk} (Minggu {$pertemuan->minggu_ke}) telah dibuka.";
        
        // Email
        if ($dosen->email) {
            $this->sendEmail($dosen->email, 'Pertemuan Dibuka', $message);
        }
        
        // Telegram (if configured)
        if ($dosen->telegram_chat_id) {
            $this->sendTelegram($dosen->telegram_chat_id, $message);
        }
    }

    /**
     * Notify mahasiswa about attendance
     */
    public function notifyAttendanceRecorded($mahasiswa, $pertemuan, $status)
    {
        $message = "Absensi Anda untuk {$pertemuan->jadwal->mataKuliah->nama_mk} telah tercatat dengan status: {$status}";
        
        // Push notification (if has device token)
        if ($mahasiswa->fcm_token) {
            $this->sendPushNotification(
                $mahasiswa->fcm_token,
                'Absensi Tercatat',
                $message
            );
        }
    }

    /**
     * Send reminder before pertemuan starts
     */
    public function sendPertemuanReminder(Pertemuan $pertemuan)
    {
        $dosen = $pertemuan->jadwal->dosen;
        $message = "Reminder: Pertemuan {$pertemuan->jadwal->mataKuliah->nama_mk} akan dimulai dalam 30 menit.";
        
        if ($dosen->email) {
            $this->sendEmail($dosen->email, 'Reminder Pertemuan', $message);
        }
        
        if ($dosen->telegram_chat_id) {
            $this->sendTelegram($dosen->telegram_chat_id, $message);
        }
    }
}
