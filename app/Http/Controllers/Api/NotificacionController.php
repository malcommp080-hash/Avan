<?php

namespace App\Http\Controllers\Api;

use App\Models\Notificacion;

class NotificacionController extends ApiController
{
    public function index()
    {
        $lista = Notificacion::para($this->empleado()->Id_empleado)->orderByDesc('Id_notificacion')->limit(50)->get()
            ->map(fn ($n) => [
                'id' => $n->Id_notificacion, 'tipo' => $n->Tipo, 'mensaje' => $n->Mensaje,
                'leida' => $n->Leida, 'fecha' => $n->Creado_en?->format('d/m/Y H:i'),
            ]);

        return response()->json(['notificaciones' => $lista]);
    }

    public function leer(Notificacion $notificacion)
    {
        $id = $this->empleado()->Id_empleado;
        abort_unless($notificacion->Id_empleado === null || (int) $notificacion->Id_empleado === $id, 403);
        $notificacion->update(['Leida' => true]);
        return $this->ok('Notificación leída.');
    }

    public function leerTodas()
    {
        Notificacion::para($this->empleado()->Id_empleado)->where('Leida', false)->update(['Leida' => true]);
        return $this->ok('Todas las notificaciones se marcaron como leídas.');
    }
}
