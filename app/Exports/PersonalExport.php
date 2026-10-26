<?php

namespace App\Exports;

use App\Models\Persona;
use App\Support\Busqueda;
use App\Support\Orden;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PersonalExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private array $filtros = []) {}

    public function query(): QueryBuilder|EloquentBuilder|Relation
    {
        $query = Persona::query()->select([
            'id', 'paterno', 'materno', 'nombres', 'ci', 'ci_expedicion', 'sexo', 'fecha_nacimiento',
            'lugar_nacimiento_ciudad', 'lugar_nacimiento_provincia', 'estado_civil', 'email', 'telefono',
            'celular', 'direccion_actual', 'fecha_ingreso_fundacion', 'cargo_actual', 'oficina_actual',
        ]);

        Busqueda::filtrar($query, Persona::COLUMNAS_BUSQUEDA, $this->filtros['search'] ?? null);

        if (isset($this->filtros['estado'])) {
            $query->where('estado', $this->filtros['estado']);
        }

        Orden::aplicar($query, $this->filtros['orden'] ?? null, $this->filtros['dir'] ?? null, Persona::COLUMNAS_ORDEN, 'id', 'asc');

        return $query;
    }

    public function headings(): array
    {
        return [
            'Paterno', 'Materno', 'Nombres', 'CI', 'Sexo',
            'Fecha Nac.', 'Lugar de Nacimiento', 'Estado Civil',
            'Email', 'Teléfono', 'Celular', 'Dirección', 'Fecha Ingreso',
            'Cargo',
            'Oficina Actual',
        ];
    }

    public function map($persona): array
    {
        return [
            $persona->paterno,
            $persona->materno,
            $persona->nombres,
            $persona->ci.' - '.$persona->ci_expedicion,
            $persona->sexo,
            $persona->fecha_nacimiento ? Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y') : '',
            $persona->lugar_nacimiento_ciudad.' , '.$persona->lugar_nacimiento_provincia,
            $persona->estado_civil,
            $persona->email,
            $persona->telefono,
            $persona->celular,
            $persona->direccion_actual,
            $persona->fecha_ingreso_fundacion ? Carbon::parse($persona->fecha_ingreso_fundacion)->format('d/m/Y') : '',
            $persona->cargo_actual,
            $persona->oficina_actual,
            // mb_strtoupper($persona->estado), // Estandarizamos el texto del estado a mayúsculas
        ];
    }
}
