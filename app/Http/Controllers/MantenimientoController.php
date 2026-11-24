<?php

namespace App\Http\Controllers;

use App\Services\MantenimientoService;
use Illuminate\Http\Request;

class MantenimientoController extends Controller
{
    public function __construct(
        private MantenimientoService $service
    ) {
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->service->listar($request->query('zona'))
        );
    }

    public function store(Request $request)
    {
        $resultado = $this->service->crear($request->all());

        return response()->json([
            'mensaje' => $resultado['codigo'] === 202
                ? 'Mantenimiento pendiente de confirmacion climatica'
                : 'Mantenimiento programado',
            'datos' => $resultado['datos']
        ], $resultado['codigo']);
    }

    public function cambiarEstado(Request $request, $id)
    {
        return response()->json(
            $this->service->cambiarEstado($id, $request->all())
        );
    }
}
