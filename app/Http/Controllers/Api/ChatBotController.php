<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Orden;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function procesarMensaje(Request $request)
    {
        $mensaje = $request->input('mensaje');
        $user = Auth::user();

        // Primero: mandamos el mensaje a la IA para que nos devuelva acción + respuesta
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openrouter.key'),
                'HTTP-Referer' => 'https://tusitio.com',
                'X-Title' => 'TuAppSoporte'
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'google/gemma-3-12b-it:free',
                'messages' => [
                     [
                                            'role' => 'system',
                                            'content' =>
                                                "Eres un asistente conversacional amable, cálido y claro, especializado en soporte al cliente para un servicio de envíos desde China a Uruguay. Respondes de forma cercana y útil, y también sabes devolver acciones específicas en JSON.\n\n" .

                                                "Tu objetivo es ayudar al usuario con consultas sobre:\n" .
                                                "- Tickets de soporte (crear, listar, consultar estado)\n" .
                                                "- Órdenes de compra (seguimiento, estado, tracking)\n" .
                                                "- Políticas de envíos\n" .
                                                "- Información general sobre el servicio\n\n" .

                                                "🔐 Políticas clave que debes tener en cuenta:\n" .
                                                "- Si un pedido llega dañado, debe enviarse foto del producto y empaque a soporte en 7 días.\n" .
                                                "- Para cambiar dirección antes del despacho: desde “Mi cuenta” o contactando a soporte.\n" .
                                                "- Entregas:\n" .
                                                "  - Estándar: 10–15 días hábiles\n" .
                                                "  - Exprés: 3–5 días hábiles\n" .
                                                "- El usuario puede estimar el costo del envío con la calculadora online o ver la tabla de tarifas.\n" .
                                                "- Cancelación: solo antes del despacho.\n" .
                                                "- Si no hay movimiento en el tracking por más de 15 días hábiles, debe contactarse con soporte.\n" .
                                                "- Planes disponibles: Pequeño, Regular y Extra.\n\n" .

                                                "🔧 Sobre lo que puedes hacer:\n" .
                                                "- Si el usuario te saluda o habla informalmente (“hola”, “cómo estás”, “todo bien”), respondé como una IA conversacional cálida (no respondas en JSON en ese caso).\n" .
                                                "- En todos los demás casos, antes de enviar la acción JSON, escribe una breve respuesta amigable que se mostrará al usuario.\n" .
                                                "- Luego envía SOLO un JSON con la estructura:\n" .
                                                "  {\"action\":\"nombre_accion\", \"params\":{}, \"respuesta\":\"(tu mensaje amigable)\"}\n" .
                                                "- No combines varias acciones ni respuestas en una sola respuesta.\n" .
                                                "- Ejemplo:\n" .
                                                "  \"Claro, aquí están tus tickets recientes:\\n{\"action\":\"listar_tickets\", \"params\":{}, \"respuesta\":\"Aquí están tus tickets más recientes 📩\"}\"\n\n" .

                                                "⚠️ IMPORTANTE:\n" .
                                                "- No inventes respuestas.\n" .
                                                "- Si no entendés o no tenés información concreta, respondé con la acción \"crear_ticket\" y un mensaje amigable.\n" .
                                                "- Solo devolvé texto sin JSON para saludos e interacciones casuales.\n" .
                                                "- Para cualquier otra consulta, respondé con el mensaje amigable seguido del JSON estructurado.\n\n" .

                                                "🧪 Ejemplos:\n" .
                                                "Usuario: Quiero ver mis tickets\n" .
                                                "Respuesta: Claro, aquí están tus tickets recientes:\n" .
                                                "{\"action\":\"listar_tickets\", \"params\":{}, \"respuesta\":\"Aquí están tus tickets más recientes 📩\"}\n\n" .
                                                "Usuario: ¿Cuándo llegará mi pedido?\n" .
                                                "Respuesta: Lo siento, no tengo esa información exacta. Por favor crea un ticket desde http://localhost:4200/soportechat y te ayudamos enseguida 💬\n" .
                                                "{\"action\":\"crear_ticket\", \"params\":{}, \"respuesta\":\"No tengo esa información exacta. Por favor crea un ticket desde http://localhost:4200/soportechat y te ayudamos enseguida 💬\"}\n\n" .
                                                "Usuario: Hola, ¿cómo estás?\n" .
                                                "Respuesta: ¡Hola! 😊 Estoy muy bien, gracias por preguntar. ¿En qué puedo ayudarte hoy con tu envío desde China a Uruguay?\n"

                                        ],
                    [
                        'role' => 'user',
                        'content' => $mensaje
                    ]
                ]
            ]);

            if (!$response->successful()) {
                $errorContent = $response->body(); // o $response->json() si esperás JSON
                \Log::error('Error en el servicio de IA: ' . $errorContent);
                return response()->json(['respuesta' => 'Error en el servicio de IA, intenta más tarde.']);
            }

            $contenido = $response->json()['choices'][0]['message']['content'] ?? '';

            // Intentamos decodificar JSON de la IA
            $json = json_decode($contenido, true);

            if (!$json || !isset($json['action'])) {
                // Si no devuelve JSON con acción, respondemos con el texto directo
                return response()->json(['respuesta' => $contenido]);
            }

            $action = $json['action'];
            $params = $json['params'] ?? [];
            $respuestaIA = $json['respuesta'] ?? null;

            // Ahora manejamos las acciones
            switch ($action) {
                case 'listar_tickets':
                    if (!$user) {
                        return response()->json(['respuesta' => 'Para ver tus tickets necesitas iniciar sesión.']);
                    }
                    $tickets = Ticket::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();

                    if ($tickets->isEmpty()) {
                        return response()->json(['respuesta' => 'No tienes tickets abiertos actualmente.']);
                    }

                    $respuesta = "Tus tickets:\n";
                    foreach ($tickets as $ticket) {
                        $respuesta .= "- Ticket #{$ticket->id}: {$ticket->asunto} (Estado: {$ticket->estado})\n";
                    }
                    return response()->json(['respuesta' => $respuesta]);

                case 'crear_ticket':
                    return response()->json(['respuesta' => 'Para crear un ticket, por favor ingresa a http://localhost:4200/soportechat y abre un nuevo ticket desde allí.']);

                case 'listar_ordenes':
                    if (!$user) {
                        return response()->json(['respuesta' => 'Para ver tus órdenes necesitas iniciar sesión.']);
                    }
                    $ordenes = Orden::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();

                    if ($ordenes->isEmpty()) {
                        return response()->json(['respuesta' => 'No tienes órdenes registradas actualmente.']);
                    }

                    $respuesta = "Tus órdenes:\n";
                    foreach ($ordenes as $orden) {
                        $respuesta .= "- Orden #{$orden->id} (Tracking: {$orden->tracking_id}) - Estado: {$orden->status}\n";
                    }
                    return response()->json(['respuesta' => $respuesta]);

                case 'general':
                default:
                    // Si la IA ya dio una respuesta, la devolvemos
                    if ($respuestaIA) {
                        return response()->json(['respuesta' => $respuestaIA]);
                    }
                    // Si no, devolvemos el texto bruto
                    return response()->json(['respuesta' => $contenido]);
            }

        } catch (\Exception $e) {
            Log::error('Error chatbot: ' . $e->getMessage());
            return response()->json(['respuesta' => 'Error técnico, intenta luego.']);
        }
    }
}
