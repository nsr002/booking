<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineService
{
    protected $channelAccessToken;
    protected $channelSecret;
    protected $liffId;

    public function __construct()
    {
        $this->channelAccessToken = config('line.channel_access_token');
        $this->channelSecret = config('line.channel_secret');
        $this->liffId = config('line.liff_id');
    }

    /**
     * Verify LINE access token.
     */
    public function verifyAccessToken(string $accessToken): ?array
    {
        try {
            $response = Http::get('https://api.line.me/oauth2/v2.1/verify', [
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('LINE token verification failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get LINE user profile.
     */
    public function getUserProfile(string $accessToken): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get('https://api.line.me/v2/profile');

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get LINE user profile: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send push message to user.
     */
    public function sendPushMessage(string $lineUserId, array $messages): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->channelAccessToken,
                'Content-Type' => 'application/json',
            ])->post('https://api.line.me/v2/bot/message/push', [
                'to' => $lineUserId,
                'messages' => $messages,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to send LINE push message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send booking confirmation message.
     */
    public function sendBookingConfirmation(string $lineUserId, $booking): bool
    {
        $schedule = $booking->schedule;
        $trainer = $schedule->trainer;

        $message = [
            'type' => 'text',
            'text' => "🎉 การจองของคุณได้รับการยืนยันแล้ว!\n\n" .
                      "📅 วันที่: " . $schedule->date->format('d/m/Y') . "\n" .
                      "⏰ เวลา: " . $schedule->start_time . " - " . $schedule->end_time . "\n" .
                      "👤 เทรนเนอร์: " . $trainer->name . "\n\n" .
                      "หากต้องการยกเลิก กรุณาติดต่อผ่าน Admin"
        ];

        return $this->sendPushMessage($lineUserId, [$message]);
    }

    /**
     * Send booking rejection message.
     */
    public function sendBookingRejection(string $lineUserId, $booking, string $reason = null): bool
    {
        $message = [
            'type' => 'text',
            'text' => "❌ การจองของคุณถูกปฏิเสธ\n\n" .
                      ($reason ? "เหตุผล: " . $reason . "\n\n" : "") .
                      "กรุณาติดต่อ Admin หากมีข้อสงสัย"
        ];

        return $this->sendPushMessage($lineUserId, [$message]);
    }
}
