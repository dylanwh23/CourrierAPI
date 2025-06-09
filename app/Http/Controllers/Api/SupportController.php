<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactReceived;

class SupportController extends Controller
{
    public function contact(Request $request)
    {
        // 1) validamos
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        Log::info('[CONTACT] raw payload:', ['raw' => $request->getContent()]);
        Log::info('[CONTACT] validated data:', $data);

        // 3) enviamos email al usuario
        
        Mail::to($data['email'])
            ->send(new ContactReceived($data['name'], $data['message']));

        // 4) logueamos la consulta
        Log::info('[CONTACT] mail sent to user:', ['email' => $data['email']]);

        // 5) devolvemos JSON OK
        return response()->json([
            'status'  => 'success',
            'message' => 'Consulta recibida. Revisa tu correo para confirmacion.'
        ]);
    }
}
