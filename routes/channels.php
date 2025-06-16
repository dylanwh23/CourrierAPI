<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
Broadcast::channel('ticket.{ticketId}', function ($user, $ticketId) {
    Log::info("Intentando unirse al canal ticket.{$ticketId}", ['user' => $user?->id]);
    return true;
});