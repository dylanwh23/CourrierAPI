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

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openrouter.key'),
                'HTTP-Referer' => 'https://tusitio.com',
                'X-Title' => 'TuAppSoporte'
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'deepseek/deepseek-chat:free',
                'messages' => [
                   [
                        'role' => 'system',
                        'content' =>
                            "Eres un asistente conversacional amable, cálido y claro, especializado en soporte al cliente para un servicio de envíos desde China a Uruguay. Respondes de forma cercana y útil, incluso si el usuario escribe mal, con errores tipográficos o frases confusas. Puedes inferir la intención general del mensaje aunque esté mal escrito.

" .
                            "Tu objetivo es ayudar al usuario con consultas sobre:
" .
                            "- Tickets de soporte (crear, listar, consultar estado)
" .
                            "- Órdenes de compra (seguimiento, estado, tracking)
" .
                            "- Políticas de envíos
" .
                            "- Información general sobre el servicio

" .
                            "📦 Tarifas y planes:
" .
                            "Envíos pequeños:
" .
                            "- Servicio estándar desde China
" .
                            "- Seguro de envío y seguimiento
" .
                            "- 1 a 3 kg: USD 19.80

" .
                            "Envíos regulares:
" .
                            "- Servicio express
" .
                            "- Embalaje especial y despacho aduanal
" .
                            "- 0.9–5 kg: USD 21.90, 5–20 kg: USD 16.50, 20–40 kg: USD 13.20, 40+ kg: Cotizar

" .
                            "Envíos extra:
" .
                            "- Atención personalizada y 24/7
" .
                            "- Descuentos por volumen
" .
                            "- 0–10 kg: USD 25.00, 10–30 kg: USD 20.00, 30+ kg: Cotizar

" .
                            "Comparativa:
" .
                            "- Todos incluyen seguro y seguimiento
" .
                            "- Solo Regular y Extra incluyen express
" .
                            "- Solo Extra incluye atención 24/7 y descuentos por volumen

" .
                            "🔧 Sobre lo que puedes hacer:
" .
                            "- Si el usuario te saluda o habla informalmente (“hola”, “cómo estás”, “todo bien”), respondé como una IA conversacional cálida (no respondas en JSON en ese caso).
" .
                            "- En todos los demás casos, antes de enviar la acción JSON, escribe una breve respuesta amigable que se mostrará al usuario.
" .
                            "- Luego envía SOLO un JSON con la estructura:
" .
                            "  {\"action\":\"nombre_accion\", \"params\":{}, \"respuesta\":\"(tu mensaje amigable)\"}
" .
                            "- No combines varias acciones ni respuestas en una sola respuesta.
" .
                            "- Ejemplo:
" .
                            "  \"Claro, aquí están tus tickets recientes:\n{\"action\":\"listar_tickets\", \"params\":{}, \"respuesta\":\"Aquí están tus tickets más recientes 📩\"}\"

" .
                            "⚠️ IMPORTANTE:
" .
                            "- No inventes respuestas.
" .
                            "- Si no entendés o no tenés información concreta, respondé con la acción \"crear_ticket\" y un mensaje amigable.
" .
                            "- Solo devolvé texto sin JSON para saludos e interacciones casuales.
" .
                            "- Para cualquier otra consulta, respondé con el mensaje amigable seguido del JSON estructurado.

" .
                            "🧪 Ejemplos:
" .
                            "Usuario: Quiero ver mis tickets
" .
                            "Respuesta: Claro, aquí están tus tickets recientes:
" .
                            "{\"action\":\"listar_tickets\", \"params\":{}, \"respuesta\":\"Aquí están tus tickets más recientes 📩\"}

" .
                            "Usuario: ¿Cuándo llegará mi pedido?
" .
                            "Respuesta: Lo siento, no tengo esa información exacta. Por favor crea un ticket desde http://localhost:4200/soportechat y te ayudamos enseguida 💬
" .
                            "{\"action\":\"crear_ticket\", \"params\":{}, \"respuesta\":\"No tengo esa información exacta. Por favor crea un ticket desde http://localhost:4200/soportechat y te ayudamos enseguida 💬\"}

" .
                            "Usuario: Hola, ¿cómo estás?
" .
                            "Respuesta: ¡Hola! 😊 Estoy muy bien, gracias por preguntar. ¿En qué puedo ayudarte hoy con tu envío desde China a Uruguay?
"
                    ],
                    [
                        'role' => 'user',
                        'content' => $mensaje
                    ]
                ]
            ]);

            if (!$response->successful()) {
                $errorContent = $response->body();
                Log::error('Error en el servicio de IA: ' . $errorContent);
                return response()->json(['respuesta' => 'Error en el servicio de IA, intenta más tarde.']);
            }

            $contenido = $response->json()['choices'][0]['message']['content'] ?? '';

            // Intentar extraer JSON si hay
             $contenido = $response->json()['choices'][0]['message']['content'] ?? '';
            $textoAntesDelJson = preg_split('/\{.*\}/s', $contenido)[0] ?? '';

            if (preg_match('/\{.*\}/s', $contenido, $matches)) {
                $jsonString = $matches[0];
                $json = json_decode($jsonString, true);

                if ($json && isset($json['action'])) {
                    $action = $json['action'];
                    $params = $json['params'] ?? [];
                    $respuestaIA = $json['respuesta'] ?? null;

                    $mensajeFinal = trim($textoAntesDelJson) . "\n\n" . $respuestaIA;

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
                            return response()->json(['respuesta' => $mensajeFinal]);

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

                        default:
                            return response()->json(['respuesta' => $mensajeFinal]);
                    }
                }
            }

            return response()->json(['respuesta' => $contenido]);

        }catch (\Exception $e) {
            Log::error('Error chatbot: ' . $e->getMessage());
            return response()->json(['respuesta' => 'Error técnico, intenta luego.']);
        }
    }
}
