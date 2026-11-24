<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class DataRepository
{
    public function listar($tabla, $campo = null, $valor = null)
    {
        $query = DB::table($tabla);

        if ($campo && $valor !== null) {
            $query->where($campo, $valor);
        }

        return $query->get();
    }

    public function buscar($tabla, $id)
    {
        return DB::table($tabla)->where('id', $id)->first();
    }

    public function crear($tabla, $datos)
    {
        $id = DB::table($tabla)->insertGetId($datos);

        return $this->buscar($tabla, $id);
    }

    public function actualizar($tabla, $id, $datos)
    {
        DB::table($tabla)->where('id', $id)->update($datos);

        return $this->buscar($tabla, $id);
    }

    public function eliminar($tabla, $id)
    {
        DB::table($tabla)->where('id', $id)->delete();
    }
}
