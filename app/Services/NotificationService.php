<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Simulate sending an SMS/WhatsApp notification to the client when their order is ready.
     *
     * @param Order $order
     * @return string
     */
    public function sendReadyNotification(Order $order)
    {
        $client = $order->client;
        $clientPhone = $client->phone ?: 'Non renseigné';
        $clientName = $client->name;
        
        $balanceText = $order->balance_amount > 0 
            ? "Reste à régler : {$order->balance_amount} DA." 
            : "Commande entièrement réglée.";

        $message = "Bonjour {$clientName}, vos vêtements du ticket #{$order->ticket_number} sont prêts à être retirés chez MSK DRY PLUS. {$balanceText} Merci pour votre confiance !";

        // Log the simulated notification
        Log::info("SIMULATED SMS SENT TO {$clientName} ({$clientPhone}) : {$message}");

        return $message;
    }
}
