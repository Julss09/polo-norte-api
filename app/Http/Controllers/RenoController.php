<?php

namespace App\Http\Controllers;

use App\Services\RenoService;
use Illuminate\Http\Request;

class RenoController extends Controller
{
    public function __construct(private RenoService $service)
    {
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->service->listar($request->query('estado'))
        );
    }

    public function store(Request $request)
    {
        return response()->json(
            $this->service->crear($request->all()), 201
        );
    }

    public function show($id)
    {
        return response()->json($this->service->buscar($id));
    }

    public function update(Request $request, $id)
    {
        return response()->json(
            $this->service->actualizar($id, $request->all())
        );
    }

    public function destroy($id)
    {
        $this->service->eliminar($id);

        return response()->noContent();
    }

    public function asignar($id)
    {
        return response()->json($this->service->asignar($id));
    }
}
