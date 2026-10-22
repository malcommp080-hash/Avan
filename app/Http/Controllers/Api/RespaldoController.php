<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\RespaldoController as RespaldoWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Respaldar (descarga .sql, heredada del panel) y restaurar la BD desde la app móvil. */
class RespaldoController extends RespaldoWeb
{
    public function restaurar(Request $request)
    {
        $request->validate(['archivo' => 'required|file|max:51200'], $this->mensajes, ['archivo' => 'archivo']);

        $archivo = $request->file('archivo');
        if (strtolower($archivo->getClientOriginalExtension()) !== 'sql') {
            return response()->json(['message' => 'El archivo debe tener extensión .sql'], 422);
        }

        try {
            DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
            $n = 0;
            foreach ($this->separarSentencias(file_get_contents($archivo->getRealPath())) as $sql) {
                DB::unprepared($sql);
                $n++;
            }
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Throwable $e) {
            try { DB::unprepared('SET FOREIGN_KEY_CHECKS=1'); } catch (\Throwable $x) {}
            return response()->json(['message' => 'Error al restaurar: ' . $e->getMessage()], 422);
        }

        return response()->json(['message' => "Base de datos restaurada ({$n} sentencias ejecutadas)."]);
    }
}
