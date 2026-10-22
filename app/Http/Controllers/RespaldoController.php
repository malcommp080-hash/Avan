<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RespaldoController extends Controller
{
    public function index()
    {
        return view('htmla.respaldarbd');
    }

    /** Genera un .sql (estructura + datos) de todas las tablas y lo descarga. */
    public function descargar()
    {
        $pdo   = DB::connection()->getPdo();
        $base  = DB::connection()->getDatabaseName();
        $nombre = 'respaldo_' . now()->format('Y-m-d_H-i-s') . '.sql';

        return response()->streamDownload(function () use ($pdo, $base) {
            echo "-- Respaldo de base de datos: {$base}\n-- Fecha: " . now()->format('Y-m-d H:i:s') . "\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach (DB::select('SHOW TABLES') as $fila) {
                $tabla = array_values((array) $fila)[0];
                $create = (array) DB::selectOne("SHOW CREATE TABLE `{$tabla}`");
                echo "DROP TABLE IF EXISTS `{$tabla}`;\n";
                echo array_values($create)[1] . ";\n\n";

                foreach (DB::table($tabla)->cursor() as $reg) {
                    $vals = array_map(function ($v) use ($pdo) {
                        if ($v === null) return 'NULL';
                        if (is_int($v) || is_float($v)) return (string) $v;
                        // binarios (p. ej. blob) -> hexadecimal
                        if (!mb_check_encoding($v, 'UTF-8')) return '0x' . bin2hex($v);
                        return $pdo->quote($v);
                    }, (array) $reg);
                    echo "INSERT INTO `{$tabla}` VALUES (" . implode(', ', $vals) . ");\n";
                }
                echo "\n";
            }
            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, $nombre, ['Content-Type' => 'application/octet-stream']);
    }

    public function restaurarForm()
    {
        return view('htmla.restaurarbd');
    }

    public function restaurar(Request $request)
    {
        $request->validate(['archivo' => 'required|file|max:51200'], $this->mensajes, ['archivo' => 'archivo']);

        $archivo = $request->file('archivo');
        if (strtolower($archivo->getClientOriginalExtension()) !== 'sql') {
            return back()->with('err', 'El archivo debe tener extensión .sql');
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
            return back()->with('err', 'Error al restaurar: ' . $e->getMessage());
        }

        return redirect()->route('admin.restaurar.index')->with('ok', "Base de datos restaurada ({$n} sentencias ejecutadas).");
    }

    /** Divide un script SQL en sentencias respetando comillas y comentarios de línea. */
    protected function separarSentencias(string $sql): array
    {
        $sentencias = [];
        $actual = '';
        $comilla = null;
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $c = $sql[$i];

            if ($comilla) {
                $actual .= $c;
                if ($c === '\\' && $i + 1 < $len) { $actual .= $sql[++$i]; continue; }
                if ($c === $comilla) $comilla = null;
                continue;
            }
            if ($c === "'" || $c === '"' || $c === '`') { $comilla = $c; $actual .= $c; continue; }
            if ($c === '-' && ($sql[$i + 1] ?? '') === '-' && in_array($sql[$i + 2] ?? ' ', [' ', "\t", "\n", "\r"], true)) {
                while ($i < $len && $sql[$i] !== "\n") $i++;
                continue;
            }
            if ($c === '#') { while ($i < $len && $sql[$i] !== "\n") $i++; continue; }
            if ($c === ';') {
                if (trim($actual) !== '') $sentencias[] = trim($actual);
                $actual = '';
                continue;
            }
            $actual .= $c;
        }
        if (trim($actual) !== '') $sentencias[] = trim($actual);

        return $sentencias;
    }
}
