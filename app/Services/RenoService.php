<?php

namespace App\Services;

use App\Repositories\DataRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RenoService
{
    public function __construct(private DataRepository $repo)
    {
    }

    public function listar($estado = null)
    {
        return $this->repo->listar('renos', 'estado', $estado);
    }

    public function buscar($id)
    {
        $reno = $this->repo->buscar('renos', $id);

        abort_if(!$reno, 404, 'Reno no encontrado');

        return $reno;
    }

    private function validar($datos, $id = null)
    {
        $reglaNombre = 'required|string|max:255|unique:renos,nombre';

        if ($id !== null) {
            $reglaNombre .= ',' . $id;
        }

        return Validator::make($datos, [
            'nombre' => $reglaNombre,
            'edad' => 'required|integer|min:1|max:25',
            'nivelEnergia' => 'required|integer|min:0|max:100',
            'estado' => 'sometimes|in:DISPONIBLE,EN_DESCANSO,EN_REVISION,ASIGNADO_VUELO'
        ])->validate();
    }

    public function crear($datos)
    {
        $datos = $this->validar($datos);
        $datos['estado'] ??= 'DISPONIBLE';

        abort_if(
            $datos['estado'] === 'ASIGNADO_VUELO',
            409,
            'Utilice el endpoint de asignacion al vuelo'
        );

        return $this->repo->crear('renos', $datos);
    }

    public function actualizar($id, $datos)
    {
        $reno = $this->buscar($id);
        $datos = $this->validar($datos, $id);

        abort_if(
            ($datos['estado'] ?? null) === 'ASIGNADO_VUELO'
                && $reno->estado !== 'ASIGNADO_VUELO',
            409,
            'Utilice el endpoint de asignacion al vuelo'
        );

        return $this->repo->actualizar('renos', $id, $datos);
    }

    public function eliminar($id)
    {
        $reno = $this->buscar($id);

        abort_if(
            $reno->estado === 'ASIGNADO_VUELO',
            409,
            'No se puede eliminar un reno asignado al vuelo'
        );

        $this->repo->eliminar('renos', $id);
    }

    public function asignar($id)
    {
        return DB::transaction(function () use ($id) {
            $renos = DB::table('renos')->lockForUpdate()->get();
            $reno = $renos->firstWhere('id', (int) $id);

            abort_if(!$reno, 404, 'Reno no encontrado');

            abort_if(
                $reno->nivelEnergia < 70 ||
                $reno->estado !== 'DISPONIBLE',
                409,
                'El reno debe estar disponible y tener energia minima de 70'
            );

            abort_if(
                $renos->where('estado', 'ASIGNADO_VUELO')->count() >= 9,
                409,
                'Maximo nueve renos asignados al vuelo'
            );

            return $this->repo->actualizar(
                'renos',
                $id,
                ['estado' => 'ASIGNADO_VUELO']
            );
        });
    }
}
