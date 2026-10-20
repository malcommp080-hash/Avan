<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;

class NotificacionController extends Controller
{
    public function index()
    {
        return view('htmla.notificaciones', [
            'notificaciones' => Notificacion::para((int) session('admin_id'))->orderByDesc('Id_notificacion')->paginate(30),
        ]);
    }

    /** Marca como leída y, si tiene enlace, lleva a él. */
    public function abrir(Notificacion $notificacion)
    {
        abort_unless($notificacion->Id_empleado === null || (int) $notificacion->Id_empleado === (int) session('admin_id'), 403);
        $notificacion->update(['Leida' => true]);
        return $notificacion->Url ? redirect($notificacion->Url) : redirect()->route('admin.notificaciones.index');
    }

    public function leerTodas()
    {
        Notificacion::para((int) session('admin_id'))->where('Leida', false)->update(['Leida' => true]);
        return redirect()->route('admin.notificaciones.index')->with('ok', 'Todas las notificaciones se marcaron como leídas.');
    }
}
