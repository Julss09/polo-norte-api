<?php

namespace App\Services;

use App\Repositories\DataRepository;
use App\Adapters\MeteoAdapter;
use Illuminate\Support\Facades\Validator;

class MantenimientoService
{
    public function __construct(
        private DataRepository $repo,
        private MeteoAdapter $meteo
    ) {
    }

    public function listar($zona = null)
    {
        return $this->repo->listar(
            'mantenimientos', 'zona', $zona
        );
    }

    public function crear($datos)
    {
        $datos = Validator::make($datos, [
            'zona' => 'required|in:PISTA_DESPEGUE,ESTABLO,BODEGA_REGALOS,HANGAR_TRINEO',
            'tipo' => 'required|in:LIMPIEZA_NIEVE,REPARACION,INSPECCION',
            'fechaProgramada' => 'required|date_format:Y-m-d|after_or_equal:today',
            'responsable' => 'required|string|max:255'
        ])->validate();

        $datos['estado'] = 'PROGRAMADO';
        $codigo = 201;

        if ($datos['zona'] === 'PISTA_DESPEGUE') {
            $clima = $this->meteo->obtenerPronostico(
                $datos['zona'],
                $datos['fechaProgramada']
            );

            abort_if(
                $clima === 'VENTISCA',
                422,
                'No se puede programar mantenimiento durante una ventisca'
            );

            if ($clima === null) {
                $datos['estado'] = 'PENDIENTE_CLIMA';
                $codigo = 202;
            }
        }

        $registro = $this->repo->crear(
            'mantenimientos', $datos
        );

        return [
            'codigo' => $codigo,
            'datos' => $registro
        ];
    }

    public function cambiarEstado($id, $datos)
    {
        $registro = $this->repo->buscar('mantenimientos', $id);

        abort_if(!$registro, 404, 'Mantenimiento no encontrado');

        $datos = Validator::make($datos, [
            'estado' => 'required|in:PROGRAMADO,EN_CURSO,COMPLETADO,CANCELADO,PENDIENTE_CLIMA'
        ])->validate();

        $transiciones = [
            'PROGRAMADO' => ['EN_CURSO', 'CANCELADO'],
            'EN_CURSO' => ['COMPLETADO']
        ];

        abort_unless(
            in_array(
                $datos['estado'],
                $transiciones[$registro->estado] ?? []
            ),
            409,
            'Transicion de mantenimiento invalida'
        );

        return $this->repo->actualizar(
            'mantenimientos', $id, $datos
        );
    }
}
