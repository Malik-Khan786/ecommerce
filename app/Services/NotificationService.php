<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a WhatsApp message to the given phone number.
     *
     * TODO: Integrate with WATI or Twilio WhatsApp API.
     */
    public function sendWhatsApp(string $phone, string $message): void
    {
        Log::info("[WhatsApp] To: {$phone} | Message: {$message}");

        // ─── WATI Integration Example ───────────────────────────────────────
        // $client = new \GuzzleHttp\Client();
        // $client->post('https://live-mt-server.wati.io/{account_id}/api/v1/sendSessionMessage/' . $phone, [
        //     'headers' => ['Authorization' => 'Bearer ' . config('services.wati.token')],
        //     'json'    => ['messageText' => $message],
        // ]);

        // ─── Twilio WhatsApp Example ─────────────────────────────────────────
        // $twilio = new \Twilio\Rest\Client(config('services.twilio.sid'), config('services.twilio.token'));
        // $twilio->messages->create('whatsapp:' . $phone, [
        //     'from' => 'whatsapp:' . config('services.twilio.whatsapp_from'),
        //     'body' => $message,
        // ]);
    }

    /**
     * Send an SMS to the given phone number.
     *
     * TODO: Integrate with Twilio or a local Pakistan SMS gateway (Zong, Jazz).
     */
    public function sendSMS(string $phone, string $message): void
    {
        Log::info("[SMS] To: {$phone} | Message: {$message}");

        // ─── Twilio SMS Example ──────────────────────────────────────────────
        // $twilio = new \Twilio\Rest\Client(config('services.twilio.sid'), config('services.twilio.token'));
        // $twilio->messages->create($phone, [
        //     'from' => config('services.twilio.from'),
        //     'body' => $message,
        // ]);

        // ─── Pakistan SMS Gateways ───────────────────────────────────────────
        // Zong (via their REST API):
        // $client = new \GuzzleHttp\Client();
        // $client->post('https://api.zong.com.pk/sms', [
        //     'json' => ['api_key' => config('services.zong.key'), 'to' => $phone, 'message' => $message]
        // ]);

        // Jazz SMS Gateway:
        // $client = new \GuzzleHttp\Client();
        // $client->post('https://sms.jazz.com.pk/api/send', [
        //     'json' => ['username' => config('services.jazz.username'), 'password' => config('services.jazz.password'), 'to' => $phone, 'text' => $message]
        // ]);
    }
}
