<?php

namespace Innoboxrr\LaravelNotifications\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function getAllNotifications()
    {
        $user = auth()->user();
        return response()->json($user->notifications);
    }

    public function getUnreadNotifications()
    {
        $user = auth()->user();
        return response()->json($user->unreadNotifications);
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
}
