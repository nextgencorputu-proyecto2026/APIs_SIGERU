<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function enviarMensaje(string $mensaje): bool
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (empty($token) || empty($chatId)) {
            Log::warning('Telegram no esta configurado.');
            return false;
        }

        try {
            $respuesta = Http::timeout(10)->post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $mensaje,
                ]
            );

            if ($respuesta->successful() && $respuesta->json('ok') === true) {
                return true;
            }

            Log::warning('Telegram rechazo el mensaje.', [
                'status' => $respuesta->status(),
                'error_code' => $respuesta->json('error_code'),
            ]);

            return false;

        } catch (\Throwable $e) {
            Log::error('Error al enviar mensaje a Telegram.', [
                'tipo' => get_class($e),
            ]);

            return false;
        }
    }
}
