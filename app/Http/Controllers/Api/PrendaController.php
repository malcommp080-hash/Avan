<?php

namespace App\Http\Controllers\Api;

use App\Models\Prenda;
use Illuminate\Http\Request;

class PrendaController extends ApiController
{
    private function reglas(): array
    {
        return [
            'prendas'    => 'required|string|max:10',
            'num_piezas' => 'required|integer|min:0',
        ];
    }

    public function index()
    {
        return response()->json(['prendas' => Prenda::orderBy('Id_prendas')->get()->map(fn ($p) => [
            'id' => $p->Id_prendas, 'prendas' => $p->Prendas, 'num_piezas' => (int) $p->Num_piezas,
        ])]);
    }

    public function store(Request $request)
    {
        $d = $this->validar($request, $this->reglas());
        Prenda::create(['Prendas' => $d['prendas'], 'Num_piezas' => $d['num_piezas']]);
        return $this->ok('Prenda guardada correctamente.', [], 201);
    }

    public function update(Request $request, Prenda $prenda)
    {
        $d = $this->validar($request, $this->reglas());
        $prenda->update(['Prendas' => $d['prendas'], 'Num_piezas' => $d['num_piezas']]);
        return $this->ok('Prenda actualizada correctamente.');
    }

    public function destroy(Prenda $prenda)
    {
        return $this->borrarJson(fn () => $prenda->delete(), 'Prenda eliminada.');
    }
}
