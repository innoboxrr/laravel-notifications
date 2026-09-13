<?php

namespace Innoboxrr\LaravelNotifications\Http\Controllers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function getAllNotifications(Request $request)
    {
        return response()->json($this->limited($request, $request->user()->notifications()));
    }

    public function getUnreadNotifications(Request $request)
    {
        return response()->json($this->limited($request, $request->user()->unreadNotifications()));
    }

    /**
     * Para el indicador de la barra superior: contar no necesita traer las
     * notificaciones.
     */
    public function countUnreadNotifications(Request $request)
    {
        return response()->json(['count' => $request->user()->unreadNotifications()->count()]);
    }

    public function markAsRead(Request $request)
    {
        // Una sola consulta en lugar de cargar y guardar cada notificacion.
        $count = $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'count' => $count]);
        }

        return response('Notifications marked as read');
    }

    public function deleteNotifications(Request $request)
    {
        $count = $request->user()->notifications()->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'count' => $count]);
        }

        return response('Notifications deleted');
    }

    /**
     * La usan dos clientes distintos: el enlace de un correo, que es una
     * navegacion y tiene que acabar en la accion de la notificacion, y la SPA,
     * que pide por XHR y necesita JSON. Una redireccion a una peticion XHR la
     * sigue el navegador en silencio y devuelve el HTML de la pagina destino.
     */
    public function markNotificationAsRead(Request $request, $notificationId)
    {
        $notification = $request->user()->notifications()->where('id', $notificationId)->first();

        if (! $notification) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
            }

            return response('Notification not found', 404);
        }

        $notification->markAsRead();

        $action = $notification->data['action'] ?? null;

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'id' => $notification->id,
                'action' => $action,
                'read_at' => $notification->read_at?->toJSON(),
            ]);
        }

        return Redirect::to($action ?? '/');
    }

    /**
     * `limit` es opcional y trae las N mas recientes. Sin el se devuelven todas,
     * como siempre, y la respuesta sigue siendo un arreglo: los clientes que ya
     * existen no notan nada.
     */
    protected function limited(Request $request, Relation $notifications)
    {
        $validated = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1'],
        ]);

        if (isset($validated['limit'])) {
            $notifications->limit((int) $validated['limit']);
        }

        return $notifications->get();
    }
}
